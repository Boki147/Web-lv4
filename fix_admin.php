<?php
$conn = new mysqli('localhost', 'root', '', 'movie_videoteka');

// Generate fresh hash for admin123
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

// Update admin user
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $hash);
$stmt->execute();

echo "Admin password hash updated!\n";
echo "Username: admin\n";
echo "Password: admin123\n";
echo "Hash: " . $hash . "\n";

$conn->close();
?>
