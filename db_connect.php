<?php
// db_connect.php

$host    = 'localhost';
$db      = 'merklic_bloomboutique';    // DB name
$user    = 'merklic_bloomboutique';    // DB username
$pass    = '9bz8uXHjjUpCgTJWnCBL';     // DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // throws exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // fetch rows as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                   // use real prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}