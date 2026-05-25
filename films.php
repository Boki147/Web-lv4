<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

// Parametri filtriranja
$genre = isset($_GET['genre']) ? sanitize_string($_GET['genre']) : '';
$year = isset($_GET['year']) ? intval($_GET['year']) : '';
$country = isset($_GET['country']) ? sanitize_string($_GET['country']) : '';
$search = isset($_GET['search']) ? sanitize_string($_GET['search']) : '';

// Paginacija
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Построение SQL upita
$sql = "SELECT id, title, genre, year, country, duration, description, image_path, 
                (SELECT AVG(rating) FROM ratings WHERE film_id = films.id) as avg_rating
        FROM films WHERE 1=1";

$count_sql = "SELECT COUNT(*) as count FROM films WHERE 1=1";
$params = array();
$types = "";

if (!empty($genre)) {
    $sql .= " AND genre LIKE ?";
    $count_sql .= " AND genre LIKE ?";
    $genre_search = "%$genre%";
    $params[] = $genre_search;
    $types .= "s";
}

if (!empty($year)) {
    $sql .= " AND year = ?";
    $count_sql .= " AND year = ?";
    $params[] = $year;
    $types .= "i";
}

if (!empty($country)) {
    $sql .= " AND country LIKE ?";
    $count_sql .= " AND country LIKE ?";
    $country_search = "%$country%";
    $params[] = $country_search;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $count_sql .= " AND (title LIKE ? OR description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Brojanje rezultata
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_films = $count_result->fetch_assoc()['count'];
$count_stmt->close();

$total_pages = ceil($total_films / $limit);

// Dohvaćanje filmova
$sql .= " ORDER BY title ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$films = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Dohvaćanje jedinstvenih žanrova i zemalja za filtere
$genres_result = $conn->query("SELECT DISTINCT genre FROM films WHERE genre IS NOT NULL ORDER BY genre");
$genres_raw = $genres_result->fetch_all(MYSQLI_ASSOC);

// Parse and deduplicate individual genres
$genres_set = array();
foreach ($genres_raw as $row) {
    $genre_list = array_map('trim', explode(',', $row['genre']));
    foreach ($genre_list as $g) {
        $genres_set[$g] = true;
    }
}
$genres = array_map(function($g) { return ['genre' => $g]; }, array_keys($genres_set));
sort($genres);

$countries_result = $conn->query("SELECT DISTINCT country FROM films WHERE country IS NOT NULL ORDER BY country");
$countries = $countries_result->fetch_all(MYSQLI_ASSOC);

$years_result = $conn->query("SELECT DISTINCT year FROM films WHERE year IS NOT NULL ORDER BY year DESC");
$years = $years_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filmovi - Videoteka</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="navbar">
            <a href="index.php" class="navbar-brand">🎬 Videoteka</a>
            <nav>
                <ul>
                    <li><a href="index.php">Početna</a></li>
                    <li><a href="films.php">Filmovi</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="myvideoteka.php">📚 Moja videoteka</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li><a href="dashboard.php">⚙️ Admin panel</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="user-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span>👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php">Odjava</a>
                <?php else: ?>
                    <a href="login.php">Prijava</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <h1 class="mb-4">Filmovi</h1>

        <!-- Filter sekcija -->
        <form method="GET" class="filter-section">
            <h3>Filtriraj filmove</h3>
            <div class="filter-row">
                <div class="form-group">
                    <label for="search">Pretraga:</label>
                    <input type="text" id="search" name="search" placeholder="Unesi naziv ili opis..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="form-group">
                    <label for="genre">Žanr:</label>
                    <select id="genre" name="genre">
                        <option value="">Svi žanrovi</option>
                        <?php foreach ($genres as $gen): ?>
                            <option value="<?php echo htmlspecialchars($gen['genre']); ?>"
                                <?php echo ($genre === $gen['genre']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($gen['genre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="year">Godina:</label>
                    <select id="year" name="year">
                        <option value="">Sve godine</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?php echo $y['year']; ?>"
                                <?php echo ($year === $y['year']) ? 'selected' : ''; ?>>
                                <?php echo $y['year']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="country">Zemlja:</label>
                    <select id="country" name="country">
                        <option value="">Sve zemlje</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?php echo htmlspecialchars($c['country']); ?>"
                                <?php echo ($country === $c['country']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['country']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="filter-buttons">
                <button type="submit" class="btn btn-primary">Filtriraj</button>
                <a href="films.php" class="btn btn-outline-primary">Očisti filtere</a>
            </div>
        </form>

        <!-- Prikaz broja rezultata -->
        <p class="mb-3">
            <strong>Pronađeno: <?php echo $total_films; ?> filmova</strong>
            <?php if (!empty($search) || !empty($genre) || !empty($year) || !empty($country)): ?>
                | <a href="films.php">Očisti filtere</a>
            <?php endif; ?>
        </p>

        <!-- Prikaz filmova -->
        <?php if (count($films) > 0): ?>
            <div class="films-container">
                <?php foreach ($films as $film): ?>
                    <div class="film-card">
                        <img src="<?php echo htmlspecialchars($film['image_path']); ?>" alt="<?php echo htmlspecialchars($film['title']); ?>" class="film-image" onerror="this.src='https://via.placeholder.com/250x300?text=No+Image'">
                        <div class="film-info">
                            <h3 class="film-title"><?php echo htmlspecialchars($film['title']); ?></h3>
                            <div class="film-meta">
                                <span>📅 <?php echo htmlspecialchars($film['year']); ?></span>
                                <span>🎭 <?php echo htmlspecialchars($film['genre']); ?></span>
                                <span>📍 <?php echo htmlspecialchars($film['country']); ?></span>
                                <span>⏱️ <?php echo htmlspecialchars($film['duration']); ?> min</span>
                            </div>
                            <?php if ($film['avg_rating']): ?>
                                <div class="film-rating"><?php echo display_stars($film['avg_rating']); ?></div>
                            <?php else: ?>
                                <div class="film-rating">Još nema ocjena</div>
                            <?php endif; ?>
                            <p class="film-description"><?php echo htmlspecialchars(substr($film['description'], 0, 80)) . '...'; ?></p>
                            <div class="film-actions">
                                <a href="film.php?id=<?php echo $film['id']; ?>" class="btn btn-primary">Detaljno</a>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="film.php?id=<?php echo $film['id']; ?>#add-to-videoteka" class="btn btn-secondary">Dodaj</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Paginacija -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $query_string = http_build_query(array_filter(['genre' => $genre, 'year' => $year, 'country' => $country, 'search' => $search]));
                    $separator = ($query_string ? '&' : '?');

                    if ($page > 1): ?>
                        <a href="films.php<?php echo $query_string ? '?' . $query_string . '&' : '?'; ?>page=1">« Početna</a>
                        <a href="films.php<?php echo $query_string ? '?' . $query_string . '&' : '?'; ?>page=<?php echo $page - 1; ?>">‹ Prethodna</a>
                    <?php endif; ?>

                    <?php
                    for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) {
                        if ($i === $page) {
                            echo "<span class='active'>$i</span>";
                        } else {
                            echo "<a href='films.php" . ($query_string ? "?$query_string&" : "?") . "page=$i'>$i</a>";
                        }
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="films.php<?php echo $query_string ? '?' . $query_string . '&' : '?'; ?>page=<?php echo $page + 1; ?>">Sljedeća ›</a>
                        <a href="films.php<?php echo $query_string ? '?' . $query_string . '&' : '?'; ?>page=<?php echo $total_pages; ?>">Posljednja »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info text-center">
                Nema filmova koji odgovaraju vašim kriterijima filtriranja.
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Videoteka. Sva prava zadržana.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>
