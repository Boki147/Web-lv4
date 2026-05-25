<?php
// Autentifikacijska provera
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funkcija za proveru da li je korisnik prijavljen
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Funkcija za proveru da li je korisnik administrator
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Funkcija za preusmjeravanje na login stranicu ako korisnik nije prijavljen
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// Funkcija za preusmjeravanje ako korisnik nije administrator
function require_admin() {
    if (!is_admin()) {
        header("Location: index.php");
        exit();
    }
}

// Funkcija za sigurnu odjavu
function logout_user() {
    session_destroy();
    header("Location: login.php");
    exit();
}

?>
