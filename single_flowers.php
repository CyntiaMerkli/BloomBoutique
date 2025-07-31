<?php
// single_flowers.php

//show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB & header
require 'db_connect.php';
include 'includes/header.php';

try {
    // fetch all single flowers with price from single_flowers table
    $stmt = $pdo->query(
        "SELECT id, name, image_url FROM single_flowers ORDER BY name"
    );
    $flowers = $stmt->fetchAll();

    //if error trying to get flowers
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error retrieving single flowers: "
       . htmlspecialchars($e->getMessage()) . "</p>";
    include 'includes/footer.php';
    exit;
}
?>

<section class="product-grid">
  <?php if (!empty($flowers)): ?>
    <?php foreach ($flowers as $flower): ?>
      <div class="product-card">
        <a href="single_flower_detail.php?id=<?= htmlspecialchars($flower['id']) ?>">
          <img
            src="<?= htmlspecialchars($flower['image_url']) ?>"
            alt="<?= htmlspecialchars($flower['name']) ?>"
          >
          <h3><?= htmlspecialchars($flower['name']) ?></h3>
         
        </a>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>No single flowers available right now. Please check back soon!</p>
  <?php endif; ?>
</section>

<?php
// shared footer
include 'includes/footer.php';
?>
