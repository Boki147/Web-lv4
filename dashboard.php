<?php
session_start();
require_once 'db.php';
require_once 'functions.php';
require_once 'auth.php';

require_admin();

$message = '';
$error = '';
$user_id = $_SESSION['user_id'];

// Obrada POST zahtjeva
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_film') {
        $title = sanitize_string($_POST['title'] ?? '');
        $genre = sanitize_string($_POST['genre'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $country = sanitize_string($_POST['country'] ?? '');
        $duration = intval($_POST['duration'] ?? 0);
        $description = sanitize_string($_POST['description'] ?? '');
        $image_path = sanitize_string($_POST['image_path'] ?? 'https://via.placeholder.com/250x300?text=No+Image');

        // Validacija
        if (!validate_film_title($title)) {
            $error = 'Nesispravan naziv filma.';
        } elseif (!validate_year($year)) {
            $error = 'Neispravna godina.';
        } elseif (!validate_duration($duration)) {
            $error = 'Neispravno trajanje filma (1-500 minuta).';
        } else {
            $stmt = $conn->prepare("INSERT INTO films (title, genre, year, country, duration, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisiss", $title, $genre, $year, $country, $duration, $description, $image_path);

            if ($stmt->execute()) {
                $message = 'Film je uspješno dodan.';
            } else {
                $error = 'Greška pri dodavanju filma.';
            }
            $stmt->close();
        }
    } elseif ($action === 'update_film') {
        $film_id = intval($_POST['film_id'] ?? 0);
        $title = sanitize_string($_POST['title'] ?? '');
        $genre = sanitize_string($_POST['genre'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $country = sanitize_string($_POST['country'] ?? '');
        $duration = intval($_POST['duration'] ?? 0);
        $description = sanitize_string($_POST['description'] ?? '');
        $image_path = sanitize_string($_POST['image_path'] ?? '');

        if (!validate_film_title($title) || !validate_year($year) || !validate_duration($duration)) {
            $error = 'Greške u validaciji podataka.';
        } else {
            $stmt = $conn->prepare("UPDATE films SET title = ?, genre = ?, year = ?, country = ?, duration = ?, description = ?, image_path = ? WHERE id = ?");
            $stmt->bind_param("ssisissa", $title, $genre, $year, $country, $duration, $description, $image_path, $film_id);

            if ($stmt->execute()) {
                $message = 'Film je uspješno ažuriran.';
            } else {
                $error = 'Greška pri ažuriranju filma.';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete_film') {
        $film_id = intval($_POST['film_id'] ?? 0);

        $stmt = $conn->prepare("DELETE FROM films WHERE id = ?");
        $stmt->bind_param("i", $film_id);

        if ($stmt->execute()) {
            $message = 'Film je uspješno obrisan.';
        } else {
            $error = 'Greška pri brisanju filma.';
        }
        $stmt->close();
    }
}

// Dohvaćanje svih filmova
$result = $conn->query("SELECT id, title, genre, year, country, duration, image_path, created_at FROM films ORDER BY created_at DESC");
$films = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel - Videoteka</title>
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
                    <li><a href="dashboard.php">⚙️ Admin panel</a></li>
                </ul>
            </nav>
            <div class="user-menu">
                <span>👤 Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php">Odjava</a>
            </div>
        </div>
    </header>

    <main class="container">
        <h1 class="mb-4">⚙️ Admin panel</h1>

        <?php if ($error): ?>
            <?php echo display_error($error); ?>
        <?php endif; ?>

        <?php if ($message): ?>
            <?php echo display_success($message); ?>
        <?php endif; ?>

        <!-- Forma za dodavanje novog filma -->
        <section style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 2rem;">
            <h2 class="mb-3">Dodaj novi film</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_film">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="title">Naslov *</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="genre">Žanr</label>
                        <input type="text" id="genre" name="genre" placeholder="npr. Sci-Fi, Drama, Action">
                    </div>

                    <div class="form-group">
                        <label for="year">Godina *</label>
                        <input type="number" id="year" name="year" min="1800" max="2100" required>
                    </div>

                    <div class="form-group">
                        <label for="country">Zemlja</label>
                        <input type="text" id="country" name="country">
                    </div>

                    <div class="form-group">
                        <label for="duration">Trajanje (minuta) *</label>
                        <input type="number" id="duration" name="duration" min="1" max="500" required>
                    </div>

                    <div class="form-group">
                        <label for="image_path">URL slike</label>
                        <input type="text" id="image_path" name="image_path" placeholder="https://example.com/image.jpg">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Opis *</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Dodaj film</button>
            </form>
        </section>

        <!-- Prikaz svih filmova -->
        <section style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <h2 class="mb-3">Upravljanje filmovima (<?php echo count($films); ?>)</h2>

            <?php if (count($films) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Naslov</th>
                                <th>Žanr</th>
                                <th>Godina</th>
                                <th>Zemlja</th>
                                <th>Trajanje</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($films as $film): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($film['title']); ?></td>
                                    <td><?php echo htmlspecialchars($film['genre']); ?></td>
                                    <td><?php echo htmlspecialchars($film['year']); ?></td>
                                    <td><?php echo htmlspecialchars($film['country']); ?></td>
                                    <td><?php echo htmlspecialchars($film['duration']); ?> min</td>
                                    <td>
                                        <a href="#edit-<?php echo $film['id']; ?>" class="btn btn-secondary btn-sm" onclick="toggleEdit(<?php echo $film['id']; ?>)">Uredi</a>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_film">
                                            <input type="hidden" name="film_id" value="<?php echo $film['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Jeste li sigurni?')">Obriši</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr id="edit-<?php echo $film['id']; ?>" style="display: none; background: var(--light-color);">
                                    <td colspan="6">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="update_film">
                                            <input type="hidden" name="film_id" value="<?php echo $film['id']; ?>">
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                                                <input type="text" name="title" value="<?php echo htmlspecialchars($film['title']); ?>" required>
                                                <input type="text" name="genre" value="<?php echo htmlspecialchars($film['genre']); ?>">
                                                <input type="number" name="year" value="<?php echo $film['year']; ?>" required>
                                                <input type="text" name="country" value="<?php echo htmlspecialchars($film['country']); ?>">
                                                <input type="number" name="duration" value="<?php echo $film['duration']; ?>" required>
                                                <input type="text" name="image_path" value="<?php echo htmlspecialchars($film['image_path']); ?>">
                                            </div>
                                            <input type="text" name="description" style="margin-top: 1rem; width: 100%;" placeholder="Opis...">
                                            <div style="margin-top: 1rem;">
                                                <button type="submit" class="btn btn-success btn-sm">Spremi</button>
                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleEdit(<?php echo $film['id']; ?>)">Otkaži</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">Nema filmova u bazi.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Videoteka. Sva prava zadržana.</p>
    </footer>

    <script>
        function toggleEdit(filmId) {
            const row = document.getElementById('edit-' + filmId);
            row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
