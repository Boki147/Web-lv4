<?php
session_start();
require_once 'auth.php';

if (is_logged_in()) {
    session_destroy();
}

header("Location: index.php");
exit();
?>
