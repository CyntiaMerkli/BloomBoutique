<?php
// bouquet_preview.php

session_start();
require 'db_connect.php';
include 'includes/header.php';

//if there are no flower quantities chosen
$bouquet = $_SESSION['bouquet'] ?? [];
if (empty($bouquet)) {
    echo "<p>You have no flowers in your bouquet. <a href='build_bouquet.php'>Go back</a>.</p>";
    include 'includes/footer.php';
    exit;
}

//get details for selected flower IDs
$ids  = implode(',', array_map('intval', array_keys($bouquet)));
$stmt = $pdo->query("SELECT id,name,price FROM single_flowers WHERE id IN ($ids)"); // uses single_flowers table to use correct data
$rows = $stmt->fetchAll();

$total = 0;
?>

<!--show user their chosen products and the amt of each-->
<h2>Your Bouquet Preview</h2>
<table>
  <tr><th>Flower</th><th>Qty</th><th>Unit</th><th>Line Total</th></tr>
  <?php foreach($rows as $r): 
    $qty  = $bouquet[$r['id']];
    $line = $r['price'] * $qty;
    $total += $line;
  ?>
  <tr>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td style="text-align:center;"><?= $qty ?></td>
    <td style="text-align:right;">$<?= number_format($r['price'],2) ?></td>
    <td style="text-align:right;">$<?= number_format($line,2) ?></td>
  </tr>
  <?php endforeach; ?>
  <tr>
    <td colspan="3" style="text-align:right;"><strong>Total</strong></td>
    <td style="text-align:right;"><strong>$<?= number_format($total,2) ?></strong></td>
  </tr>
</table>



<form method="post" action="cart.php">
  <input type="hidden" name="action"      value="add_custom">
  <input type="hidden" name="bouquet_data" value="<?= htmlspecialchars(base64_encode(json_encode($bouquet))) ?>">
  <input type="hidden" name="total_price"  value="<?= number_format($total,2,'.','') ?>">
  <button type="submit" class="btn">Add Bouquet to Cart</button>
</form>

<p><a href="build_bouquet.php">Modify your bouquet</a></p>

<?php include 'includes/footer.php'; ?>