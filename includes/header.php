<?php
// includes/header.php

// start the session 
session_start();

//ensure cart arrays exist
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['custom_prices']) || !is_array($_SESSION['custom_prices'])) {
    $_SESSION['custom_prices'] = [];
}

// compute the current cart count
$cartCount = array_sum($_SESSION['cart']);

// determine login and role status
$loggedIn = !empty($_SESSION['user_id']);
$role     = $_SESSION['role'] ?? 'user';

// load active theme from the database
require __DIR__ . '/../db_connect.php';
$stmt  = $pdo->prepare("
    SELECT setting_value
      FROM settings
     WHERE setting_key = 'current_theme'
");
$stmt->execute();
$theme = $stmt->fetchColumn() ?: 'theme-default.css';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>BloomBoutique</title>
  
  <meta name="keywords" content="flowers, bouquets, online flower shop, BloomBoutique">

  <!-- base style -->
  <link rel="stylesheet" href="/BloomBoutique/styles.css?v=1">

  <!-- active theme -->
  <link rel="stylesheet" href="/BloomBoutique/themes/<?= htmlspecialchars($theme) ?>">
</head>
<body>
  <header>
    <h1>BloomBoutique</h1>
    <nav>
      <ul>
      <!--header buttons-->
        <li><a href="/BloomBoutique/home.html">Home</a></li>
        <li><a href="/BloomBoutique/about.html">About Us</a></li>
        <li><a href="/BloomBoutique/contact.html">Contact</a></li>
        <li><a href="/BloomBoutique/faqs.html">FAQs</a></li>
        <li><a href="/BloomBoutique/help/index.html">Help</a></li>
        <li><a href="/BloomBoutique/terms.html">Terms &amp; Privacy</a></li>
        <li><a href="/BloomBoutique/catalog.php">Shop</a></li>
        <li><a href="/BloomBoutique/dashboard.php">My Dashboard</a></li>
        

        <?php if ($loggedIn): ?>
          <!--only show admin button for admin, even though still request authorization-->
          <?php if ($role === 'admin'): ?>
            <li><a href="/BloomBoutique/admin/index.php">Admin</a></li>
          <?php endif; ?>
          <li>
            <a href="/BloomBoutique/logout.php">
              Log Out (<?= htmlspecialchars($_SESSION['username'] ?? '') ?>)
            </a>
          </li>
        <?php else: ?>
          <li><a href="/BloomBoutique/login.php">Log In</a></li>
        <?php endif; ?>

        <li><a href="/BloomBoutique/cart.php">Cart (<?= $cartCount ?>)</a></li>
      </ul>
    </nav>
  </header>
  <main>