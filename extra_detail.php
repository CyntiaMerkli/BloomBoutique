<?php
// extra_detail.php
// detail page for extra product with image & price swapping for options and add to cart

//show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB & header 
require 'db_connect.php';
include 'includes/header.php';  // opens <main>

// validate id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p style='color:red;'>Invalid product ID.</p>";
    include 'includes/footer.php';
    exit;
}
$id = (int) $_GET['id'];

//data from extras table for the extra products
try {
    $stmt = $pdo->prepare(
        "SELECT id, name, description, price AS base_price, image_url
         FROM extras
         WHERE id = ?"
    );
    $stmt->execute([$id]);
    $extra = $stmt->fetch();

    //error with getting data
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error loading product: " . htmlspecialchars($e->getMessage()) . "</p>";
    include 'includes/footer.php';
    exit;
}
if (!$extra) {
    echo "<p style='color:red;'>Product not found.</p>";
    include 'includes/footer.php';
    exit;
}

//getch all options for the product from extra_options table
try {
    $optStmt = $pdo->prepare(
        "SELECT option_name, option_value, option_image_url, price_modifier
         FROM extra_options
         WHERE extra_id = ?
         ORDER BY option_name, option_value"
    );
    $optStmt->execute([$id]);
    $options = $optStmt->fetchAll();

    //if error getting data
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error loading options: " . htmlspecialchars($e->getMessage()) . "</p>";
    include 'includes/footer.php';
    exit;
}

//group options by name so not duplicated option sections
$grouped = [];
foreach ($options as $o) {
    $grouped[$o['option_name']][] = $o;
}
?>

<!--details for displayed product-->
<section class="product-detail" style="max-width:700px;margin:40px auto;">
  <h2><?= htmlspecialchars($extra['name']) ?></h2>
  <div style="text-align:center;">
    <img id="mainExtraImage"
         src="<?= htmlspecialchars($extra['image_url'] ?? '') ?>"
         alt="<?= htmlspecialchars($extra['name']) ?>"
         style="max-width:300px;height:auto;margin-bottom:20px;">
  </div>
  <p><?= nl2br(htmlspecialchars($extra['description'] ?? '')) ?></p>
  
  <!-- price -->
  <p>
    <strong>Total Price:</strong>
    <span id="displayExtraPrice" data-base-price="<?= number_format($extra['base_price'],2) ?>">
      $<?= number_format($extra['base_price'],2) ?>
    </span>
  </p>
  
  <!-- options form -->
  <form method="get" action="cart.php">
    <input type="hidden" name="action" value="add_extra">
    <input type="hidden" name="id"     value="<?= $extra['id'] ?>">

    <?php foreach ($grouped as $optName => $opts): ?>
      <div style="margin-bottom:16px;">
        <label for="opt_<?= htmlspecialchars($optName) ?>">
          <strong><?= htmlspecialchars($optName) ?>:</strong>
        </label><br>
        <select id="opt_<?= htmlspecialchars($optName) ?>"
                name="opts[<?= htmlspecialchars($optName) ?>]"
                class="extra-option-select"
                required>
          <option value="" disabled selected
                  data-image="<?= htmlspecialchars($extra['image_url'] ?? '') ?>"
                  data-price-mod="0">
            -- select <?= htmlspecialchars($optName) ?> --
          </option>
          <?php foreach ($opts as $o): ?>
            <option value="<?= htmlspecialchars($o['option_value']) ?>"
                    data-image="<?= htmlspecialchars($o['option_image_url']) ?>"
                    data-price-mod="<?= number_format($o['price_modifier'],2) ?>">
              <?= htmlspecialchars($o['option_value']) ?><?= $o['price_modifier'] != 0 ? " (+$" . number_format($o['price_modifier'],2) . ")" : "" ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endforeach; ?>

    <button type="submit" class="btn">Add to Cart</button>
  </form>

  <p style="margin-top:20px;">
    <a href="extras.php" class="btn">&larr; Back to Extras</a>
  </p>
</section>

<!-- JS to switch image and update price -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const priceEl = document.getElementById('displayExtraPrice');
    const basePrice = parseFloat(priceEl.dataset.basePrice);
    const img = document.getElementById('mainExtraImage');
    const selects = document.querySelectorAll('.extra-option-select');

    function updateExtra() {
      let total = basePrice;
      selects.forEach(sel => {
        const option = sel.options[sel.selectedIndex];
        const mod = parseFloat(option.dataset.priceMod) || 0;
        const src = option.dataset.image;
        if (src) img.src = src;
        total += mod;
      });
      priceEl.textContent = '$' + total.toFixed(2);
    }

    selects.forEach(sel => sel.addEventListener('change', updateExtra));
    updateExtra();
  });
</script>

<?php include 'includes/footer.php'; ?>