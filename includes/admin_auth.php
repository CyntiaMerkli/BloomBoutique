<?php
// includes/admin_auth.php
session_start();

//make sure logged in and redirect if not
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// get role of user
$role = $_SESSION['role'] ?? null;
if ($role !== 'admin') {
    http_response_code(403);
    echo '<p style="padding:2rem;text-align:center;color:red;">Access Denied</p>';
    exit;
}