<?php
session_start();
require_once 'db.php';
require_once 'functions.php';
require_once 'auth.php';

require_login();

$message = '';
$error = '';
$user_id = $_SESSION['user_id'];

// Obrada POST zahtjeva
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $film_id = intval($_POST['film_id'] ?? 0);

    if ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM myvideoteka WHERE user_id = ? AND film_id = ?");
        $stmt->bind_param("ii", $user_id, $film_id);
        
        if ($stmt->execute()) {
            $message = 'Film je uklonjen iz vaše videoteke.';
        } else {
            $error = 'Greška pri uklanjanju filma.';
        }
        $stmt->close();
    }
}

// Dohvaćanje filmova iz videoteke s upozorenjima
$stmt = $conn->prepare("SELECT f.id, f.title, f.genre, f.year, f.country, f.duration, f.description, f.image_path,
                        (SELECT AVG(rating) FROM ratings WHERE film_id = f.id) as avg_rating,
                        (SELECT COUNT(*) FROM ratings WHERE film_id = f.id) as rating_count,
                        CASE WHEN (SELECT AVG(rating) FROM ratings WHERE film_id = f.id) < 5.0 
                             THEN 'warning' ELSE 'normal' END as warning_status
                        FROM myvideoteka mv
                        JOIN films f ON mv.film_id = f.id
                        WHERE mv.user_id = ?
                        ORDER BY mv.added_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$videoteka_films = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Brojanje filmova u videoteci
$count = count($videoteka_films);

// Dohvaćanje upozorenja
$stmt = $conn->prepare("SELECT w.film_id, w.warning_message, f.title FROM warnings w 
                        JOIN films f ON w.film_id = f.id 
                        WHERE w.user_id = ? 
                        ORDER BY w.timestamp DESC 
                        LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$warnings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moja videoteka - Videoteka</title>
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
                    <li><a href="myvideoteka.php">📚 Moja videoteka</a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li><a href="dashboard.php">⚙️ Admin panel</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="user-menu">
                <span>👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php">Odjava</a>
            </div>
        </div>
    </header>

    <main class="container">
        <h1 class="mb-4">📚 Moja videoteka</h1>

        <?php if ($error): ?>
            <?php echo display_error($error); ?>
        <?php endif; ?>

        <?php if ($message): ?>
            <?php echo display_success($message); ?>
        <?php endif; ?>

        <!-- Upozorenja -->
        <?php if (count($warnings) > 0): ?>
            <div style="background: #fff3cd; border: 2px solid var(--warning-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
                <h3 style="color: #856404; margin-bottom: 1rem;">⚠️ Upozorenja o niskој ocjeni</h3>
                <ul style="margin-left: 2rem; color: #856404;">
                    <?php foreach ($warnings as $warning): ?>
                        <li><strong><?php echo htmlspecialchars($warning['title']); ?></strong> - <?php echo htmlspecialchars($warning['warning_message']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Statistika -->
        <div style="background: white; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                <div style="text-align: center;">
                    <h3 style="font-size: 2rem; color: var(--primary-color);"><?php echo $count; ?></h3>
                    <p>Filmova u videoteci</p>
                </div>
                <div style="text-align: center;">
                    <h3 style="font-size: 2rem; color: var(--secondary-color);">
                        <?php 
                        $total_minutes = array_sum(array_column($videoteka_films, 'duration'));
                        echo intval($total_minutes / 60);
                        ?>
                    </h3>
                    <p>Sati gledanja</p>
                </div>
            </div>
        </div>

        <!-- Prikaz filmova -->
        <?php if (count($videoteka_films) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Naslov</th>
                            <th>Žanr</th>
                            <th>Godina</th>
                            <th>Trajanje</th>
                            <th>Ocjena</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($videoteka_films as $film): ?>
                            <tr <?php echo ($film['warning_status'] === 'warning') ? 'style="background-color: #fff3cd;"' : ''; ?>>
                                <td>
                                    <a href="film.php?id=<?php echo $film['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">
                                        <?php echo htmlspecialchars($film['title']); ?>
                                    </a>
                                    <?php if ($film['warning_status'] === 'warning'): ?>
                                        <span style="color: var(--warning-color); margin-left: 0.5rem;">⚠️</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($film['genre']); ?></td>
                                <td><?php echo htmlspecialchars($film['year']); ?></td>
                                <td><?php echo htmlspecialchars($film['duration']); ?> min</td>
                                <td>
                                    <?php if ($film['avg_rating']): ?>
                                        <strong><?php echo display_stars($film['avg_rating']); ?></strong>
                                    <?php else: ?>
                                        Nema ocjena
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="film.php?id=<?php echo $film['id']; ?>" class="btn btn-primary btn-sm">Detaljno</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="film_id" value="<?php echo $film['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Jeste li sigurni?')">Ukloni</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 2rem; text-align: center;">
                <a href="films.php" class="btn btn-primary">Dodaj više filmova</a>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <p>Vaša videoteka je prazna. <a href="films.php">Dodajte filmove</a>!</p>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Videoteka. Sva prava zadržana.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>
