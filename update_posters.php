<?php
$conn = new mysqli('localhost', 'root', '', 'movie_videoteka');

$updates = [
    ['Inception', 'https://upload.wikimedia.org/wikipedia/en/2/2e/Inception_%282010%29_theatrical_poster.jpg'],
    ['The Matrix', 'https://m.media-amazon.com/images/M/MV5BN2NmN2VhMTQtMDNiOS00NDlhLTliMjgtODE2ZTY0ODQyNDRhXkEyXkFqcGc@._V1_.jpg'],
    ['Pulp Fiction', 'https://upload.wikimedia.org/wikipedia/en/thumb/3/3b/Pulp_Fiction_%281994%29_poster.jpg/250px-Pulp_Fiction_%281994%29_poster.jpg'],
    ['The Shawshank Redemption', 'https://m.media-amazon.com/images/M/MV5BMDAyY2FhYjctNDc5OS00MDNlLThiMGUtY2UxYWVkNGY2ZjljXkEyXkFqcGc@._V1_FMjpg_UX1000_.jpg'],
    ['The Dark Knight', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS3ekE6Hhz9gvIbiFSUPxt-FyAh4zXTXX0bjQ&s'],
    ['Forrest Gump', 'https://m.media-amazon.com/images/M/MV5BNDYwNzVjMTItZmU5YS00YjQ5LTljYjgtMjY2NDVmYWMyNWFmXkEyXkFqcGc@._V1_FMjpg_UX1000_.jpg'],
    ['Interstellar', 'https://m.media-amazon.com/images/M/MV5BYzdjMDAxZGItMjI2My00ODA1LTlkNzItOWFjMDU5ZDJlYWY3XkEyXkFqcGc@._V1_.jpg'],
    ['The Avengers', 'https://upload.wikimedia.org/wikipedia/en/thumb/8/8a/The_Avengers_%282012_film%29_poster.jpg/250px-The_Avengers_%282012_film%29_poster.jpg']
];

foreach ($updates as $update) {
    $stmt = $conn->prepare('UPDATE films SET image_path = ? WHERE title = ?');
    $stmt->bind_param('ss', $update[1], $update[0]);
    $stmt->execute();
    echo 'Updated: ' . $update[0] . "\n";
}

$conn->close();
echo "Done! Refresh the page to see new images.";
?>
