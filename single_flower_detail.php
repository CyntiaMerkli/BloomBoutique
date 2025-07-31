<?php
// single_flower_detail.php

// show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB & header 
require 'db_connect.php';
include 'includes/header.php';

// validate & fetch flower
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p style='color:red;'>Invalid flower ID.</p>";
    include 'includes/footer.php';
    exit;
}
$id = (int) $_GET['id'];

try {
    $stmt = $pdo->prepare("
      SELECT id, name, description, price, image_url, category
        FROM single_flowers
       WHERE id = ?
    ");
    $stmt->execute([$id]);
    $flower = $stmt->fetch();
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error loading flower: "
       . htmlspecialchars($e->getMessage()) . "</p>";
    include 'includes/footer.php';
    exit;
}

if (!$flower) {
    echo "<p style='color:red;'>Flower not found.</p>";
    include 'includes/footer.php';
    exit;
}

// fetch options for each flower
try {
    $optStmt = $pdo->prepare("
      SELECT option_name, option_value, price_modifier
        FROM single_flower_options
       WHERE flower_id = ?
       ORDER BY option_name, option_value
    ");
    $optStmt->execute([$flower['id']]);
    $options = $optStmt->fetchAll();
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error loading options: "
       . htmlspecialchars($e->getMessage()) . "</p>";
    include 'includes/footer.php';
    exit;
}

// group by option name so no duplicate
$grouped = [];
foreach ($options as $opt) {
    $grouped[$opt['option_name']][] = $opt;
}
?>

<section class="product-detail" style="max-width:700px;margin:40px auto;">
  <div style="text-align:center;">
    <img src="<?= htmlspecialchars($flower['image_url']) ?>"
         alt="<?= htmlspecialchars($flower['name']) ?>"
         style="max-width:300px%;height:500px;
                box-shadow:0 2px 8px rgba(0,0,0,0.1);
                padding:16px;
                background:#fff;">
  </div>

  <h2><?= htmlspecialchars($flower['name']) ?></h2>
  <p><strong>Category:</strong> <?= htmlspecialchars($flower['category']) ?></p>
  <p><strong>Description:</strong><br>
    <?= nl2br(htmlspecialchars($flower['description'])) ?></p>

  <!-- price display -->
  <p>
    <strong>Total Price:</strong>
    <span id="displayPrice"
          data-base-price="<?= number_format($flower['price'],2) ?>">
      $<?= number_format($flower['price'],2) ?>
    </span>
  </p>

  <!-- options & add to cart buttons -->
  <form method="get" action="cart.php">
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="id"     value="<?= $flower['id'] ?>">

    <?php foreach ($grouped as $optName => $opts): ?>
      <div style="margin-bottom:12px;">
        <label for="opt_<?= htmlspecialchars($optName) ?>">
          <strong><?= htmlspecialchars($optName) ?>:</strong>
        </label><br>
        <select id="opt_<?= htmlspecialchars($optName) ?>"
                name="opts[<?= htmlspecialchars($optName) ?>]"
                required>
          <?php foreach ($opts as $o): ?>
            <option value="<?= htmlspecialchars($o['option_value']) ?>"
                    data-price-mod="<?= $o['price_modifier'] ?>">
              <?= htmlspecialchars($o['option_value'])
                 ?><?= $o['price_modifier'] > 0
                     ? " (+\$".number_format($o['price_modifier'],2).")"
                     : "" ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endforeach; ?>

    <button type="submit" class="btn">Add to Cart</button>
    <a href="single_flowers.php" class="btn" style="margin-left:12px;">← Back to All</a>
  </form>
</section>

<!-- include JS so that can see updates -->
<script src="scripts.js"></script>

<?php include 'includes/footer.php'; ?>