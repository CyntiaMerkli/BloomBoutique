<?php
// checkout.php

// error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB & header
session_start();
require 'db_connect.php';
include 'includes/header.php';  // opens <main>

// if just normal user
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// make sure cart exists
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['custom_prices']) || !is_array($_SESSION['custom_prices'])) {
    $_SESSION['custom_prices'] = [];
}

//build cart summary
function getCartSummary($pdo, $sessionCart, $sessionCustomPrices, &$items, &$total) {
    $items = [];
    $total = 0.00;
    foreach ($sessionCart as $key => $qty) {
        $parts = explode('|', $key);
        $kind  = $parts[0] ?? '';

        //if a custom bouquet
        if ($kind === 'custom') {
            $enc    = $parts[1] ?? '';
            $bouquet= json_decode(base64_decode($enc), true) ?: [];
            $unit   = $sessionCustomPrices[$key] ?? 0;
            $line   = $unit * $qty;
            $total += $line;
            $items[] = [
              'type'=>'custom',
              'label'=>'Custom Bouquet',
              'details'=>$bouquet,
              'qty'=>$qty,
              'unit'=>$unit,
              'line'=>$line,
              'key'=>$key
            ];

            //if from single flowers or extra items
        } else {
            
            list(, $id, $optEnc) = array_pad(explode('|', $key), 3, '');
            $opts = $optEnc
                  ? (json_decode(base64_decode($optEnc), true) ?: [])
                  : [];
                  //extra
            if ($kind === 'extra') {
                $table = 'extras';
                $idcol = 'id';
                $namecol = 'name';
                $pricecol= 'price';
                $optTbl = 'extra_options';
                $optFk  = 'extra_id';

                //single flowers
            } else {
                $table = 'single_flowers';
                $idcol = 'id';
                $namecol = 'name';
                $pricecol= 'price';
                $optTbl = 'single_flower_options';
                $optFk  = 'flower_id';
            }
            $stmt = $pdo->prepare("SELECT {$namecol} AS nm, {$pricecol} AS pr FROM {$table} WHERE {$idcol}=?");
            $stmt->execute([(int)$id]);
            $row = $stmt->fetch();

            //if item another option, use modified price, otherwise skip
            if (!$row) continue;
            $mod = 0.00;
            foreach ($opts as $n => $v) {
                $o = $pdo->prepare(
                  "SELECT price_modifier
                     FROM {$optTbl}
                    WHERE {$optFk}=? AND option_name=? AND option_value=?"
                );
                $o->execute([(int)$id, $n, $v]);
                $mod += floatval($o->fetchColumn() ?? 0);
            }
            $unit = $row['pr'] + $mod;
            $line = $unit * $qty;
            $total += $line;
            $items[] = [
              'type'=>$kind,
              'label'=>$row['nm'],
              'details'=>$opts,
              'qty'=>$qty,
              'unit'=>$unit,
              'line'=>$line,
              'key'=>$key
            ];
        }
    }
}

//build cart data
getCartSummary($pdo, $_SESSION['cart'], $_SESSION['custom_prices'], $items, $grandTotal);

//form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $address = trim($_POST['address'] ?? '');

    //if field left empty
    if ($name === '')    $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($address === '') $errors[] = 'Shipping address is required.';

    //if cart empty
    if (empty($items)) {
        $errors[] = 'Your cart is empty.';
    }


    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            //insert order with user_id
            $oStmt = $pdo->prepare(
              "INSERT INTO orders
                 (user_id, customer_name, email, address, total_price, created_at)
               VALUES (?,?,?,?,?,NOW())"
            );
            $oStmt->execute([
              $_SESSION['user_id'],
              $name,
              $email,
              $address,
              $grandTotal
            ]);
            $orderId = $pdo->lastInsertId();

            // insert order items
            $iStmt = $pdo->prepare(
              "INSERT INTO order_items
                 (order_id, product_type, product_id, options, unit_price, quantity, line_total)
               VALUES (?,?,?,?,?,?,?)"
            );
            foreach ($items as $i) {

                //for custom bouquets
                if ($i['type'] === 'custom') {
                    $iStmt->execute([
                      $orderId,
                      'custom',
                      0,
                      json_encode($i['details']),
                      $i['unit'],
                      $i['qty'],
                      $i['line']
                    ]);

                    //othyer items
                } else {
                    list(, $pid) = explode('|', $i['key'], 3);
                    $iStmt->execute([
                      $orderId,
                      $i['type'],
                      $pid,
                      json_encode($i['details']),
                      $i['unit'],
                      $i['qty'],
                      $i['line']
                    ]);
                }
            }

            $pdo->commit();
            // clear cart
            unset($_SESSION['cart'], $_SESSION['custom_prices']);
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Order failed: ' . htmlspecialchars($e->getMessage());
        }
    }
}


?>

<h2>Checkout</h2>


<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)): ?>
<!--order confirmation -->
  <p>Your order #<?= $orderId ?> has been placed! Thank you.</p>
  <p><a href="home.html">Return to Home</a> | <a href="catalog.php">Shop More</a></p>

<?php else: ?>

  <?php if ($errors): ?>
    <ul style="color:red;">
      <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <?php if (empty($items)): ?>
    <p>Your cart is empty. <a href="catalog.php">Go back to shop</a>.</p>
  <?php else: ?>

    <!-- show cart summary -->
    <table>
      <thead>
        <tr>
          <th>Item</th><th>Details</th><th>Qty</th><th>Unit Price</th><th>Line Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i): ?>
          <tr>
            <td><?= htmlspecialchars($i['label']) ?></td>
            <td>
              <?php if ($i['type'] === 'custom'): ?>
                <?php foreach ($i['details'] as $fid=>$q):
                  $n = $pdo->prepare("SELECT name FROM single_flowers WHERE id=?");
                  $n->execute([$fid]);
                  echo htmlspecialchars($n->fetchColumn()) . " x$q<br>";
                endforeach; ?>
              <?php else: ?>
                <?php foreach ($i['details'] as $n=>$v): ?>
                  <strong><?= htmlspecialchars($n) ?>:</strong> <?= htmlspecialchars($v) ?><br>
                <?php endforeach; ?>
              <?php endif; ?>
            </td>
            <td style="text-align:center;"><?= $i['qty'] ?></td>
            <td style="text-align:right;">$<?= number_format($i['unit'],2) ?></td>
            <td style="text-align:right;">$<?= number_format($i['line'],2) ?></td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td colspan="4" style="text-align:right;"><strong>Total:</strong></td>
          <td style="text-align:right;"><strong>$<?= number_format($grandTotal,2) ?></strong></td>
        </tr>
      </tbody>
    </table>

    <!-- shipping & payment form -->
    <form method="post" action="checkout.php" style="margin-top:2rem;">
      <h3>Your Information</h3>
      <label>
        Full Name:<br>
        <input type="text" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
      </label><br><br>
      <label>
        Email Address:<br>
        <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
      </label><br><br>
      <label>
        Shipping Address:<br>
        <textarea name="address" rows="4" required><?= htmlspecialchars($address ?? '') ?></textarea>
      </label><br><br>

      <button type="submit" class="btn" style="
        background: green; color:white; padding:0.75rem 1.5rem;
        border:none; border-radius:4px; cursor:pointer;
      ">
        Place Order
      </button>
      <a href="cart.php" style="margin-left:1rem;">← Back to Cart</a>
    </form>

  <?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>