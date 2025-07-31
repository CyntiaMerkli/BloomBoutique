<?php 
// cart.php 
 
//enable full error reporting 
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL); 
 
// DB & header 
 
require 'db_connect.php'; 
include 'includes/header.php'; // opens <main> 
 
//ensure session arrays exist 
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) { 
$_SESSION['cart'] = []; 
} 
if (!isset($_SESSION['custom_prices']) || !is_array($_SESSION['custom_prices'])) { 
$_SESSION['custom_prices'] = []; 
} 
 
//handle POST for Custom Bouquet added to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_custom') { 
$encoded = $_POST['bouquet_data'] ?? ''; 
$unitPrice = floatval($_POST['total_price'] ?? 0); 
$key = "custom|{$encoded}"; 
 
$_SESSION['cart'][$key] = ($_SESSION['cart'][$key] ?? 0) + 1; 
$_SESSION['custom_prices'][$key] = $unitPrice; 
header('Location: cart.php'); 
exit; 
} 
 
//handle GET for adding items 
$action = $_GET['action'] ?? ''; 
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0; 
 
// items added from extra items 
if ($action === 'add_extra' && $id > 0) { 
$opts = $_GET['opts'] ?? []; 
$encoded = base64_encode(json_encode($opts)); 
$key = "extra|{$id}|{$encoded}"; 
$_SESSION['cart'][$key] = ($_SESSION['cart'][$key] ?? 0) + 1; 
header('Location: cart.php'); 
exit; 
} 
 
// items added from Single Flowers 
if ($action === 'add' && $id > 0) { 
$opts = $_GET['opts'] ?? []; 
$encoded = base64_encode(json_encode($opts)); 
$key = "single|{$id}|{$encoded}"; 
$_SESSION['cart'][$key] = ($_SESSION['cart'][$key] ?? 0) + 1; 
header('Location: cart.php'); 
exit; 
} 
 
//when user wants to remove item
if ($action === 'remove' && isset($_GET['id'])) { 
$key = $_GET['id']; 
unset($_SESSION['cart'][$key], $_SESSION['custom_prices'][$key]); 
header('Location: cart.php'); 
exit; 
} 
if ($action === 'remove' && isset($_GET['id'])) { 
$key = $_GET['id']; 
unset($_SESSION['cart'][$key], $_SESSION['custom_prices'][$key]); 
header('Location: cart.php'); exit; 
} 
 
//cart items and total 
$items = []; 
$total = 0.0; 
foreach ($_SESSION['cart'] as $key => $qty) { 
$parts = explode('|', $key); 
$kind = $parts[0] ?? ''; 
 
 // if a custom Bouquet 
if ($kind === 'custom') { 
$encoded = $parts[1] ?? ''; 
$bouquet = json_decode(base64_decode($encoded), true) ?: []; 
$unit = $_SESSION['custom_prices'][$key] ?? 0; 
$line = $unit * $qty; 
$total += $line; 
$items[] = ['type'=>'custom','key'=>$key,'label'=>'Custom Bouquet','details'=>$bouquet,'qty'=>$qty,'unit'=>$unit,'line'=>$line]; //use values from custom bouquet
} 



//if from extra items
elseif ($kind === 'extra') { 
    $itemId = (int)($parts[1] ?? 0); 
    $optEnc = $parts[2] ?? ''; 
    $options = $optEnc ? (json_decode(base64_decode($optEnc), true) ?: []) : []; 
$stmt = $pdo->prepare('SELECT name, price FROM extras WHERE id=?'); 
$stmt->execute([$itemId]); $row = $stmt->fetch(); 
if (!$row) continue; //if there is no other option for product, continue
$modSum = 0; 
foreach ($options as $name => $value) { 
$oStmt = $pdo->prepare('SELECT price_modifier FROM extra_options WHERE extra_id=? AND option_name=? AND option_value=?'); //use modified price if another option
$oStmt->execute([$itemId, $name, $value]); 
$modSum += floatval($oStmt->fetchColumn() ?? 0); 
} 
$unit = $row['price'] + $modSum; 
$line = $unit * $qty; 
$total += $line; 
$items[]=['type'=>'extra','key'=>$key,'label'=>$row['name'],'details'=>$options,'qty'=>$qty,'unit'=>$unit,'line'=>$line]; 
} 



//if from single Flowers 
else { 

$itemId = (int)($parts[1] ?? 0); 
$optEnc = $parts[2] ?? ''; 
$options = $optEnc ? (json_decode(base64_decode($optEnc), true) ?: []) : []; 
$stmt = $pdo->prepare('SELECT name, price FROM single_flowers WHERE id=?'); //use single_flowers table
$stmt->execute([$itemId]); $row = $stmt->fetch(); 
if (!$row) continue; 
$modSum = 0; 
foreach ($options as $name => $value) { 
$oStmt = $pdo->prepare('SELECT price_modifier FROM single_flower_options WHERE flower_id=? AND option_name=? AND option_value=?'); //use modified price of option
$oStmt->execute([$itemId, $name, $value]); 
$modSum += floatval($oStmt->fetchColumn() ?? 0); 
} 
$unit = $row['price'] + $modSum; 
$line = $unit * $qty; 
$total += $line; 
$items[]=['type'=>'single','key'=>$key,'label'=>$row['name'],'details'=>$options,'qty'=>$qty,'unit'=>$unit,'line'=>$line]; 
} 
} 
?> 
 
<h2>Your Cart</h2> 
 
 <!--if cart empty-->
<?php if (empty($items)): ?> 
<p>Your cart is empty.</p> 


<!--not emptyy, show cart items with their details-->
<?php else: ?> 
<table> 
<thead> 
<tr><th>Item</th><th>Details</th><th>Qty</th><th>Unit Price</th><th>Line Total</th><th></th></tr> 
</thead> 
<tbody> 
<?php foreach ($items as $i): ?> 
<tr> 
<td><?= htmlspecialchars($i['label']) ?></td> 
<td> 
<?php if ($i['type']==='custom'): ?> 
<?php foreach ($i['details'] as $fid => $q): 
$nstmt=$pdo->prepare('SELECT name FROM single_flowers WHERE id=?'); 
$nstmt->execute([$fid]); 
echo htmlspecialchars($nstmt->fetchColumn()) . " x$q<br>"; 
endforeach; ?> 
<?php else: ?> 
<?php foreach ($i['details'] as $name=>$value): ?> 
<strong><?= htmlspecialchars($name) ?>:</strong> <?= htmlspecialchars($value) ?><br> 
<?php endforeach; ?> 
<?php endif; ?> 
</td> 
<td style="text-align:center;"><?= $i['qty'] ?></td> 
<td style="text-align:right;">$<?= number_format($i['unit'],2) ?></td> 
<td style="text-align:right;">$<?= number_format($i['line'],2) ?></td> 
<td> 
<a href="cart.php?action=remove&id=<?= urlencode($i['key']) ?>">Remove</a> 
<?php if ($i['type']==='custom'): ?> | <a href="build_bouquet.php?edit=<?= urlencode(explode('|',$i['key'])[1]) ?>">Edit</a><?php endif; ?> 
</td> 
</tr> 
<?php endforeach; ?> 
<tr> 
<td colspan="4" style="text-align:right;"><strong>Total:</strong></td> 
<td style="text-align:right;"><strong>$<?= number_format($total,2) ?></strong></td> 
<td></td> 
</tr> 
</tbody> 
</table> 
<?php endif; ?> 
 
<p style="margin-top:20px;"> 
<a href="checkout.php" class="btn" style="background-color: purple; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block;"> 
Proceed to Checkout 
</a> 
</p> 
<p> 
<a href="catalog.php" class="btn"> 
&larr; Back to Catalog 
</a> 
</p> 
 
<?php include 'includes/footer.php'; ?> 
 