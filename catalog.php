<?php
// catalog.php

//enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// connect to the db
require 'db_connect.php';

//include shared header
include 'includes/header.php';

try {
    //fetch all product categories
    $stmt = $pdo->query(
        "SELECT id, name, image_url FROM products ORDER BY name"
    );
    $products = $stmt->fetchAll();

    //if error
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error retrieving products: "
       . htmlspecialchars($e->getMessage()) . "</p>";
    include 'includes/footer.php';
    exit;
}
?>

<section class="product-grid">
  <?php if (!empty($products)): ?>
    <?php foreach ($products as $p): ?>
      <?php
        //link to single_flowers.php for single flowers page, else to product.php
        if ($p['name'] === 'Single Flowers') {
            $link = "single_flowers.php?id=" . $p['id'];
        } 

        //link to build your own bouquet
        else if ($p['name'] === 'Build Your Own Bouquet') {
            $link = "build_bouquet.php?id=" . $p['id'];
        }

        //link to extra products
        else if ($p['name'] === 'Extras') {
            $link = "extras.php?id=" . $p['id'];
        }
        
        //leftover
        else {
            $link = "product.php?id=" . $p['id'];
        }
      ?>
      <div class="product-card">
        <a href="<?= htmlspecialchars($link) ?>">
          <img
            src="<?= htmlspecialchars($p['image_url']) ?>"
            alt="<?= htmlspecialchars($p['name']) ?>"
          >
          <h3><?= htmlspecialchars($p['name']) ?></h3>
        </a>
      </div>
    <?php endforeach; ?>

    <!--if empty-->
  <?php else: ?>
    <p>No products available right now. Please check back soon!</p>
  <?php endif; ?>
</section>

<?php
// shared footer
include 'includes/footer.php';
?>
