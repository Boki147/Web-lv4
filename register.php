<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_string($_POST['username'] ?? '');
    $email = sanitize_string($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validacija
    if (!validate_username($username)) {
        $error = 'Korisničko ime mora biti između 3 i 50 karaktera i sadržavati samo alfanumeričke znakove i underscore.';
    } elseif (!validate_email($email)) {
        $error = 'Unesite validnu email adresu.';
    } elseif (!validate_password($password)) {
        $error = 'Lozinka mora biti najmanje 6 karaktera.';
    } elseif ($password !== $password_confirm) {
        $error = 'Lozinke se ne podudaraju.';
    } else {
        // Provjera da li korisnik već postoji
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Korisničko ime ili email već postoji.';
        } else {
            // Hashiranje lozinke
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Unos korisnika u bazu
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = 'Registracija uspješna! Možete se sada <a href="login.php">prijaviti</a>.';
            } else {
                $error = 'Greška pri registraciji. Pokušajte ponovno.';
            }
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
    <title>Registracija - Videoteka</title>
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
            <h1 class="text-center mb-4">Registracija</h1>

            <?php if ($error): ?>
                <?php echo display_error($error); ?>
            <?php endif; ?>

            <?php if ($success): ?>
                <?php echo display_success($success); ?>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Korisničko ime:</label>
                        <input type="text" id="username" name="username" required
                            value="<?php echo $_POST['username'] ?? ''; ?>">
                        <small class="form-error">Minimum 3 karaktera, samo alfanumerički znakovi i underscore</small>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required
                            value="<?php echo $_POST['email'] ?? ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Lozinka:</label>
                        <input type="password" id="password" name="password" required>
                        <small class="form-error">Minimum 6 karaktera</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Potvrdi lozinku:</label>
                        <input type="password" id="password_confirm" name="password_confirm" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Registriraj se</button>

                    <p class="text-center mt-3">
                        Već imaš račun? <a href="login.php">Prijavi se</a>
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Videoteka. Sva prava zadržana.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>
