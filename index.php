<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

// Dohvaćanje najnovijih filmova
$stmt = $conn->prepare("SELECT id, title, genre, year, duration, description, image_path, 
                        (SELECT AVG(rating) FROM ratings WHERE film_id = films.id) as avg_rating
                        FROM films ORDER BY created_at DESC LIMIT 4");
$stmt->execute();
$result = $stmt->get_result();
$featured_films = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Dohvaćanje ukupnog broja filmova
$result = $conn->query("SELECT COUNT(*) as count FROM films");
$total_films = $result->fetch_assoc()['count'];

// Dohvaćanje ukupnog broja korisnika
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$total_users = $result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videoteka - Upravljanje filmovima</title>
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
                    <a href="register.php">Registracija</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="hero">
            <h1>🎬 Dobrodošli u Videoteku</h1>
            <p>Upravljajte svojom kolekcijom filmova, ocjenjujte i skupljajte omiljene naslove</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-secondary">Prijavi se</a>
                <a href="register.php" class="btn btn-outline-primary">Registriraj se</a>
            <?php else: ?>
                <a href="films.php" class="btn btn-secondary">Pogledaj filmove</a>
            <?php endif; ?>
        </section>

        <section class="mt-4">
            <h2 class="text-center mb-4">Statistika</h2>
            <div class="films-container" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div style="background: white; padding: 2rem; border-radius: 8px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 2.5rem; color: var(--primary-color);"><?php echo $total_films; ?></h3>
                    <p>Dostupnih filmova</p>
                </div>
                <div style="background: white; padding: 2rem; border-radius: 8px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 2.5rem; color: var(--secondary-color);"><?php echo $total_users; ?></h3>
                    <p>Registriranih korisnika</p>
                </div>
                <div style="background: white; padding: 2rem; border-radius: 8px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 2.5rem; color: #28a745;">⭐</h3>
                    <p>Ocjenjivanje filmova dostupno</p>
                </div>
            </div>
        </section>

        <section class="mt-4">
            <h2 class="text-center mb-4">Izdvojeni filmovi</h2>
            <div class="films-container">
                <?php foreach ($featured_films as $film): ?>
                    <div class="film-card">
                        <img src="<?php echo htmlspecialchars($film['image_path']); ?>" alt="<?php echo htmlspecialchars($film['title']); ?>" class="film-image" onerror="this.src='https://via.placeholder.com/250x300?text=No+Image'">
                        <div class="film-info">
                            <h3 class="film-title"><?php echo htmlspecialchars($film['title']); ?></h3>
                            <div class="film-meta">
                                <span>📅 <?php echo htmlspecialchars($film['year']); ?></span>
                                <span>🎭 <?php echo htmlspecialchars($film['genre']); ?></span>
                                <span>⏱️ <?php echo htmlspecialchars($film['duration']); ?> min</span>
                            </div>
                            <?php if ($film['avg_rating']): ?>
                                <div class="film-rating"><?php echo display_stars($film['avg_rating']); ?></div>
                            <?php else: ?>
                                <div class="film-rating">Još nema ocjena</div>
                            <?php endif; ?>
                            <p class="film-description"><?php echo htmlspecialchars(substr($film['description'], 0, 100)) . '...'; ?></p>
                            <div class="film-actions">
                                <a href="film.php?id=<?php echo $film['id']; ?>" class="btn btn-primary">Detaljno</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="mt-4 text-center">
            <a href="films.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">Pogledaj sve filmove</a>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Videoteka. Sva prava zadržana.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>
