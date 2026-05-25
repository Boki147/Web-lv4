<?php
// Pomoćne funkcije

// Validacija filmskog naslova
function validate_film_title($title) {
    return !empty(trim($title)) && strlen(trim($title)) <= 255;
}

// Validacija godine
function validate_year($year) {
    $current_year = date('Y');
    return is_numeric($year) && $year >= 1800 && $year <= $current_year + 10;
}

// Validacija trajanja filma (u minutama)
function validate_duration($duration) {
    return is_numeric($duration) && $duration > 0 && $duration <= 500;
}

// Validacija email-a
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validacija korisničkog imena
function validate_username($username) {
    return !empty(trim($username)) && strlen(trim($username)) >= 3 && strlen(trim($username)) <= 50 && preg_match('/^[a-zA-Z0-9_]+$/', $username);
}

// Validacija lozinke (minimum 6 karaktera)
function validate_password($password) {
    return !empty($password) && strlen($password) >= 6;
}

// Validacija ocjene (1-5)
function validate_rating($rating) {
    return is_numeric($rating) && $rating >= 1 && $rating <= 5;
}

// Sanitizacija string-a
function sanitize_string($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

// Prosljeđivanje greške
function display_error($message) {
    return "<div class='alert alert-danger'>" . sanitize_string($message) . "</div>";
}

// Prosljeđivanje uspjeha
function display_success($message) {
    return "<div class='alert alert-success'>" . sanitize_string($message) . "</div>";
}

// Prosljeđivanje upozorenja
function display_warning($message) {
    return "<div class='alert alert-warning'>" . sanitize_string($message) . "</div>";
}

// Funkcija za dohvaćanje prosječne ocjene filma
function get_average_rating($conn, $film_id) {
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM ratings WHERE film_id = ?");
    $stmt->bind_param("i", $film_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['avg_rating'] ?? 0;
}

// Funkcija za provjeru da li je korisnik već ocijenio film
function has_user_rated($conn, $user_id, $film_id) {
    $stmt = $conn->prepare("SELECT id FROM ratings WHERE user_id = ? AND film_id = ?");
    $stmt->bind_param("ii", $user_id, $film_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $has_rated = $result->num_rows > 0;
    $stmt->close();
    
    return $has_rated;
}

// Funkcija za dohvaćanje korisničke ocjene za specifičan film
function get_user_rating($conn, $user_id, $film_id) {
    $stmt = $conn->prepare("SELECT rating FROM ratings WHERE user_id = ? AND film_id = ?");
    $stmt->bind_param("ii", $user_id, $film_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['rating'];
    }
    
    $stmt->close();
    return null;
}

// Funkcija za provjeru je li film već u videoteci korisnika
function is_in_videoteka($conn, $user_id, $film_id) {
    $stmt = $conn->prepare("SELECT id FROM myvideoteka WHERE user_id = ? AND film_id = ?");
    $stmt->bind_param("ii", $user_id, $film_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_in = $result->num_rows > 0;
    $stmt->close();
    
    return $is_in;
}

// Funkcija za prikaz zvjezdica (HTML)
function display_stars($rating, $size = 'md') {
    $rating = round($rating * 2) / 2; // Round to nearest 0.5
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5;
    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
    
    $stars = str_repeat("⭐", $full_stars);
    if ($half_star) {
        $stars .= "⭐"; // Semi-filled star representation
    }
    $stars .= str_repeat("☆", $empty_stars);
    
    return $stars . " (" . round($rating, 1) . "/5)";
}

?>
