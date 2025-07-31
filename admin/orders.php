<?php
// /BloomBoutique/admin/orders.php

require __DIR__ . '/../includes/admin_auth.php';
require __DIR__ . '/../db_connect.php';
include __DIR__ . '/../includes/header.php';

//delete using POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    //remove all order items for that order
    $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$delId]);
    //remove the order
    $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$delId]);

    echo "<p style='color:green;'>Order #{$delId} deleted.</p>";
}

// fetch orders from orders table
$stmt   = $pdo->query(
    "SELECT
         id,
         user_id,
         customer_name,
         email,
         address,
         total_price,
         created_at
     FROM orders
     ORDER BY id DESC"
);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!--page setup-->
<main class="container">
  <h2>Manage Customer Orders</h2>

  <?php if (count($orders) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Customer</th>
          <th>Email</th>
          <th>Address</th>
          <th>Total</th>
          <th>Items</th>
          <th>Ordered At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
          <td><?= $order['id'] ?></td>
          <td><?= htmlspecialchars($order['customer_name']) ?></td>
          <td>
            <a href="mailto:<?= htmlspecialchars($order['email']) ?>">
              <?= htmlspecialchars($order['email']) ?>
            </a>
          </td>
          <td style="white-space:pre-wrap;">
            <?= nl2br(htmlspecialchars($order['address'])) ?>
          </td>
          <td>$<?= number_format($order['total_price'], 2) ?></td>
          <td style="white-space:pre-wrap;">
            <?php
            // Fetch items for this order
            $stmtItems = $pdo->prepare(
                "SELECT product_type, product_id, options, unit_price, quantity, line_total
                 FROM order_items
                 WHERE order_id = ?"
            );
            $stmtItems->execute([$order['id']]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <ul style="padding-left:1rem; margin:0;">
              <?php foreach ($items as $it): ?>
                <li>
                  <?= htmlspecialchars($it['product_type']) ?>
                  #<?= (int)$it['product_id'] ?>
                  <?php if ($it['options']): ?>
                    (<?= htmlspecialchars($it['options']) ?>)
                  <?php endif; ?>
                  x <?= (int)$it['quantity'] ?>
                  @ $<?= number_format($it['unit_price'], 2) ?>
                  = $<?= number_format($it['line_total'], 2) ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </td>
          <td>
            <?= date('M j, Y g:ia', strtotime($order['created_at'])) ?>
          </td>
          <td>
            <form method="post" style="display:inline; margin:0;">
              <input type="hidden" name="delete_id" value="<?= $order['id'] ?>">
              <button type="submit" class="btn danger small"
                      onclick="return confirm('Delete this order and its items?');">
                Delete
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No orders found.</p>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>