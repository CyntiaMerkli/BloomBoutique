<?php
// extras.php

// show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB & header
require 'db_connect.php';
include 'includes/header.php';

//fetch from extras table
$stmt   = $pdo->query("
  SELECT id, name, price, image_url AS image_url
    FROM extras
   ORDER BY name
");
$extras = $stmt->fetchAll();
?>

<h2>Extras</h2>
<!--display extra products, opens extra_detail page when product clicked on-->
<section class="product-grid">
  <?php if (count($extras)): ?>
    <?php foreach ($extras as $e): ?>
      <div class="product-card">
        <a href="extra_detail.php?id=<?= htmlspecialchars($e['id']) ?>">
          <img
            src="<?= htmlspecialchars($e['image_url']) ?>"
            alt="<?= htmlspecialchars($e['name']) ?>"
          >
          <h3><?= htmlspecialchars($e['name']) ?></h3>
          <p>$<?= number_format($e['price'], 2) ?></p>
        </a>
      </div>
    <?php endforeach; ?>

    <!--if no products available-->
  <?php else: ?>
    <p>No products available right now.</p>
  <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>