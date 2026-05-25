<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

$film_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$error = '';

if ($film_id === 0) {
    header("Location: films.php");
    exit();
}

// Dohvaćanje filma
$stmt = $conn->prepare("SELECT id, title, genre, year, country, duration, description, image_path, created_at FROM films WHERE id = ?");
$stmt->bind_param("i", $film_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: films.php");
    exit();
}

$film = $result->fetch_assoc();
$stmt->close();

// Dohvaćanje prosječne ocjene
$avg_rating = get_average_rating($conn, $film_id);

// Dohvaćanje broja ocjena
$result = $conn->query("SELECT COUNT(*) as count FROM ratings WHERE film_id = $film_id");
$rating_count = $result->fetch_assoc()['count'];

// Korisničke ocjene (ako je prijavljen)
$user_rating = null;
if (isset($_SESSION['user_id'])) {
    $user_rating = get_user_rating($conn, $_SESSION['user_id'], $film_id);
}

// Provjera je li film već u videoteki
$in_videoteka = false;
if (isset($_SESSION['user_id'])) {
    $in_videoteka = is_in_videoteka($conn, $_SESSION['user_id'], $film_id);
}

// Obrada POST zahtjeva
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $error = 'Trebate biti prijavljeni za ovu akciju.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'rate') {
            $rating = intval($_POST['rating'] ?? 0);
            
            if (!validate_rating($rating)) {
                $error = 'Neispravna ocjena.';
            } else {
                if (has_user_rated($conn, $_SESSION['user_id'], $film_id)) {
                    // Ažuriranje postojeće ocjene
                    $stmt = $conn->prepare("UPDATE ratings SET rating = ? WHERE user_id = ? AND film_id = ?");
                    $stmt->bind_param("iii", $rating, $_SESSION['user_id'], $film_id);
                    
                    if ($stmt->execute()) {
                        $message = 'Vaša ocjena je ažurirana.';
                    } else {
                        $error = 'Greška pri ažuriranju ocjene.';
                    }
                    $stmt->close();
                } else {
                    // Dodavanje nove ocjene
                    $stmt = $conn->prepare("INSERT INTO ratings (user_id, film_id, rating) VALUES (?, ?, ?)");
                    $stmt->bind_param("iii", $_SESSION['user_id'], $film_id, $rating);
                    
                    if ($stmt->execute()) {
                        $message = 'Hvala što ste ocijenili film!';
                    } else {
                        $error = 'Greška pri spremanju ocjene.';
                    }
                    $stmt->close();
                }

                // Osvježavanje korisničke ocjene
                $user_rating = get_user_rating($conn, $_SESSION['user_id'], $film_id);
                $avg_rating = get_average_rating($conn, $film_id);
                
                // Osvježavanje broja ocjena
                $result = $conn->query("SELECT COUNT(*) as count FROM ratings WHERE film_id = $film_id");
                $rating_count = $result->fetch_assoc()['count'];
            }
        } elseif ($action === 'add_videoteka') {
            if (!$in_videoteka) {
                $stmt = $conn->prepare("INSERT INTO myvideoteka (user_id, film_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $_SESSION['user_id'], $film_id);
                
                if ($stmt->execute()) {
                    $message = 'Film je dodan u vašu videoteku.';
                    $in_videoteka = true;
                    
                    // Provjera za upozorenje o niskој ocjeni
                    if ($avg_rating < 5.0 && $rating_count > 0) {
                        $warning_msg = "Film ima prosječnu ocjenu " . round($avg_rating, 1) . "/5 - niska je ocjena!";
                        $stmt_warn = $conn->prepare("INSERT INTO warnings (user_id, film_id, warning_message) VALUES (?, ?, ?)");
                        $stmt_warn->bind_param("iis", $_SESSION['user_id'], $film_id, $warning_msg);
                        $stmt_warn->execute();
                        $stmt_warn->close();
                    }
                } else {
                    $error = 'Greška pri dodavanju filma u videoteku.';
                }
                $stmt->close();
            } else {
                $message = 'Film je već u vašoj videoteci.';
            }
        } elseif ($action === 'remove_videoteka') {
            $stmt = $conn->prepare("DELETE FROM myvideoteka WHERE user_id = ? AND film_id = ?");
            $stmt->bind_param("ii", $_SESSION['user_id'], $film_id);
            
            if ($stmt->execute()) {
                $message = 'Film je uklonjen iz vaše videoteke.';
                $in_videoteka = false;
            } else {
                $error = 'Greška pri uklanjanju filma.';
            }
            $stmt->close();
        }
    }
}

// Dohvaćanje svih ocjena za prikaz
$ratings_result = $conn->query("SELECT r.rating, u.username, r.timestamp FROM ratings r 
                                 JOIN users u ON r.user_id = u.id 
                                 WHERE r.film_id = $film_id 
                                 ORDER BY r.timestamp DESC LIMIT 10");
$all_ratings = $ratings_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($film['title']); ?> - Videoteka</title>
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
        <a href="films.php" class="btn btn-outline-primary mb-4">← Nazad na filmove</a>

        <?php if ($error): ?>
            <?php echo display_error($error); ?>
        <?php endif; ?>

        <?php if ($message): ?>
            <?php echo display_success($message); ?>
        <?php endif; ?>

        <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <div style="display: grid; grid-template-columns: 300px 1fr; gap: 2rem; padding: 2rem;">
                <img src="<?php echo htmlspecialchars($film['image_path']); ?>" alt="<?php echo htmlspecialchars($film['title']); ?>" style="width: 100%; border-radius: 8px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/300x400?text=No+Image'">
                
                <div>
                    <h1><?php echo htmlspecialchars($film['title']); ?></h1>
                    
                    <div class="film-meta" style="margin: 1.5rem 0; font-size: 1.1rem;">
                        <span><strong>📅 Godina:</strong> <?php echo htmlspecialchars($film['year']); ?></span>
                        <span><strong>🎭 Žanr:</strong> <?php echo htmlspecialchars($film['genre']); ?></span>
                        <span><strong>📍 Zemlja:</strong> <?php echo htmlspecialchars($film['country']); ?></span>
                        <span><strong>⏱️ Trajanje:</strong> <?php echo htmlspecialchars($film['duration']); ?> minuta</span>
                    </div>

                    <div style="background: var(--light-color); padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0;">
                        <h3>Ocjena filmova</h3>
                        <div style="font-size: 2rem; color: var(--secondary-color); margin: 1rem 0;">
                            <?php echo display_stars($avg_rating); ?>
                        </div>
                        <p><strong><?php echo $rating_count; ?></strong> ocjena</p>
                    </div>

                    <p style="font-size: 1.1rem; line-height: 1.6; margin: 1.5rem 0;">
                        <?php echo htmlspecialchars($film['description']); ?>
                    </p>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                            <?php if ($in_videoteka): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="remove_videoteka">
                                    <button type="submit" class="btn btn-danger">📚 Ukloni iz videoteke</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display: inline;" id="add-to-videoteka">
                                    <input type="hidden" name="action" value="add_videoteka">
                                    <button type="submit" class="btn btn-success">📚 Dodaj u videoteku</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 2px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                <h2>Ocijeni film</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="rate">
                    <div class="form-group">
                        <label>Tvoja ocjena:</label>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin: 1rem 0;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" 
                                        <?php echo ($user_rating === $i) ? 'checked' : ''; ?> 
                                        style="margin-right: 0.5rem;">
                                    <span style="font-size: 2rem;">
                                        <?php echo str_repeat('⭐', $i) . str_repeat('☆', 5-$i); ?>
                                    </span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Pošalji ocjenu</button>
                </form>
            </div>
        <?php endif; ?>

        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 2px 15px rgba(0,0,0,0.1);">
            <h2>Sve ocjene (<?php echo count($all_ratings); ?>)</h2>
            <?php if (count($all_ratings) > 0): ?>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($all_ratings as $rating): ?>
                        <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <strong><?php echo htmlspecialchars($rating['username']); ?></strong>
                                <span style="color: var(--secondary-color); font-size: 1.1rem;">
                                    <?php echo str_repeat('⭐', $rating['rating']) . str_repeat('☆', 5-$rating['rating']); ?>
                                </span>
                            </div>
                            <small style="color: #999;"><?php echo date('d.m.Y H:i', strtotime($rating['timestamp'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center" style="color: #999;">Još nema ocjena za ovaj film.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Videoteka. Sva prava zadržana.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>
