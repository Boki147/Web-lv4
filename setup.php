<?php
// Setup skripта - Kreira bazu podataka i korisnike
// Pokreni: http://localhost/videoteka/setup.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['init'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Inicijalizacija Videoteke</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; }
            .box { background: #f0f0f0; padding: 20px; border-radius: 8px; }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>⚙️ Inicijalizacija Videoteke</h1>
            <p>Kliknite na dugme ispod da biste postavili bazu podataka.</p>
            <p><strong>Admin podaci:</strong></p>
            <ul>
                <li>Korisničko ime: <code>admin</code></li>
                <li>Lozinka: <code>admin123</code></li>
            </ul>
            <form method="POST">
                <button type="submit" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">
                    Inicijaliziraj bazu podataka
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Konekcija na MySQL
$conn = new mysqli("localhost", "root", "", "");

if ($conn->connect_error) {
    die("Greška pri povezivanju: " . $conn->connect_error);
}

// Kreiraj bazu podataka
$sql = "CREATE DATABASE IF NOT EXISTS movie_videoteka";
if (!$conn->query($sql)) {
    die("Greška pri kreiranju baze: " . $conn->error);
}

// Koristi bazu
$conn->select_db("movie_videoteka");
$conn->set_charset("utf8mb4");

// Kreiraj tablice
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS films (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        genre VARCHAR(100),
        year INT(4),
        country VARCHAR(100),
        duration INT(11),
        description TEXT,
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS ratings (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        film_id INT(11) NOT NULL,
        rating INT(1) NOT NULL CHECK(rating >= 1 AND rating <= 5),
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_film_rating (user_id, film_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS myvideoteka (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        film_id INT(11) NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_film (user_id, film_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS warnings (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        film_id INT(11) NOT NULL,
        warning_message TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($tables as $table) {
    if (!$conn->query($table)) {
        die("Greška pri kreiranju tablice: " . $conn->error);
    }
}

// Unesi admin korisnika
$username = "admin";
$email = "admin@videoteka.local";
$password_hash = password_hash("admin123", PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
$stmt->bind_param("sss", $username, $email, $password_hash);

if (!$stmt->execute()) {
    die("Greška pri unošenju admina: " . $stmt->error);
}
$stmt->close();

// Unesi test filmove
$films = [
    ["Inception", "Sci-Fi", 2010, "USA", 148, "A thief who steals corporate secrets through dream-sharing technology.", "https://via.placeholder.com/250x300?text=Inception"],
    ["The Matrix", "Sci-Fi", 1999, "USA", 136, "A computer programmer discovers that reality as he knows it is a simulation.", "https://via.placeholder.com/250x300?text=The+Matrix"],
    ["Pulp Fiction", "Crime/Drama", 1994, "USA", 154, "The lives of two mob hitmen, a boxer, a gangster and his wife intertwine.", "https://via.placeholder.com/250x300?text=Pulp+Fiction"],
    ["The Shawshank Redemption", "Drama", 1994, "USA", 142, "Two imprisoned men bond over a number of years, finding solace and eventual redemption.", "https://via.placeholder.com/250x300?text=Shawshank"],
    ["The Dark Knight", "Action/Crime", 2008, "USA", 152, "When the menace known as The Joker wreaks havoc on Gotham, Batman must face off.", "https://via.placeholder.com/250x300?text=Dark+Knight"],
    ["Forrest Gump", "Drama", 1994, "USA", 142, "The presidencies of Kennedy and Johnson unfold through the perspective of an Alabama man.", "https://via.placeholder.com/250x300?text=Forrest+Gump"],
    ["Interstellar", "Sci-Fi", 2014, "USA", 169, "A team of explorers travel through a wormhole in space in an attempt to ensure humanity survival.", "https://via.placeholder.com/250x300?text=Interstellar"],
    ["The Avengers", "Action/Adventure", 2012, "USA", 143, "Earth mightiest heroes must come together to prevent an alien invasion.", "https://via.placeholder.com/250x300?text=Avengers"]
];

$stmt = $conn->prepare("INSERT IGNORE INTO films (title, genre, year, country, duration, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");

foreach ($films as $film) {
    $stmt->bind_param("ssisiss", $film[0], $film[1], $film[2], $film[3], $film[4], $film[5], $film[6]);
    if (!$stmt->execute()) {
        die("Greška pri unošenju filma: " . $stmt->error);
    }
}
$stmt->close();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Inicijalizacija uspješna</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; }
        .box { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 20px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>✅ Baza podataka je uspješno inicijalizovana!</h1>
        <p>Početne podatke su uneseni u bazu.</p>
        <p><a href="index.php">Idi na početnu stranicu</a></p>
    </div>
</body>
</html>
<?php
$conn->close();
?>
