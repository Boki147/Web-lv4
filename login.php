<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_string($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validacija
    if (empty($username) || empty($password)) {
        $error = 'Unesite korisničko ime i lozinku.';
    } else {
        // Dohvaćanje korisnika iz baze
        $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Provjera lozinke
            if (password_verify($password, $user['password'])) {
                // Postavljanje sesije
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Preusmjeravanje na početnu stranicu
                header("Location: index.php");
                exit();
            } else {
                $error = 'Neispravna lozinka.';
            }
        } else {
            $error = 'Korisnik nije pronađen.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava - Videoteka</title>
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
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <div style="max-width: 500px; margin: 3rem auto;">
            <h1 class="text-center mb-4">Prijava</h1>

            <?php if ($error): ?>
                <?php echo display_error($error); ?>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Korisničko ime:</label>
                    <input type="text" id="username" name="username" required
                        value="<?php echo $_POST['username'] ?? ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Lozinka:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Prijavi se</button>

                <p class="text-center mt-3">
                    Nemaš račun? <a href="register.php">Registriraj se</a>
                </p>
            </form>

            <div class="alert alert-info mt-3">
                <strong>Demo račun (admin):</strong><br>
                Korisničko ime: admin<br>
                Lozinka: admin123
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Videoteka. Sva prava zadržana.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>
