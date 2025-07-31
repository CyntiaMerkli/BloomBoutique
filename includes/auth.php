
<?php
// includes/auth.php
session_start();

// Only require that the user be logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}