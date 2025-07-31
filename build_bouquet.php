<?php
// build_bouquet.php

//Show errors
ini_set('display_errors',1);
error_reporting(E_ALL);

// coonect to db and include common header

require 'db_connect.php';
include 'includes/header.php';

//if coming from edit link, decode the bouquet quantities, so that shows same quantities as there was in cart
$editEncoded = $_GET['edit'] ?? '';
$bouquet     = [];
if ($editEncoded) {
    $bouquet = json_decode(base64_decode($editEncoded), true) ?: [];
}

// preview form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bouquet = [];
    foreach ($_POST['quantity'] as $flowerId => $qty) {
        $qty = (int)$qty;
        if ($qty > 0) {
            $bouquet[$flowerId] = $qty;
        }
    }
    if (!empty($bouquet)) {
        $_SESSION['bouquet'] = $bouquet;
        header('Location: bouquet_preview.php');
        exit;

    //make sure at least one flower selected
    } else {
        echo "<p style='color:red;'>Please select at least one flower.</p>";
    }
}

//get all single flowers
$stmt    = $pdo->query("SELECT id, name, price, image_url FROM single_flowers ORDER BY name");
$flowers = $stmt->fetchAll();
?>

<!--show all flower options-->
<h2>Build Your Own Bouquet</h2>

<form 
  method="post" 
  action="build_bouquet.php<?= $editEncoded ? '?edit=' . urlencode($editEncoded) : '' ?>"
>
  <section class="product-grid">
    <?php foreach ($flowers as $f): ?>
      <div class="product-card">
        <img src="<?= htmlspecialchars($f['image_url']) ?>"
             alt="<?= htmlspecialchars($f['name']) ?>">
        <h3><?= htmlspecialchars($f['name']) ?></h3>
        <p>$<?= number_format($f['price'],2) ?></p>
        <label>
          Qty:
          <input 
            type="number" 
            name="quantity[<?= $f['id'] ?>]" 
            value="<?= $bouquet[$f['id']] ?? 0 ?>" 
            min="0" 
            style="width:50px;"
          >
        </label>
      </div>
    <?php endforeach; ?>
  </section>

  <button type="submit" class="btn">Preview Bouquet</button>
</form>

<?php include 'includes/footer.php'; ?>