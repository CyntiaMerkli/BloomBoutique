


<?php
// /BloomBoutique/dashboard.php

//DB & header
require __DIR__ . '/includes/auth.php';//require user to be logged in
require __DIR__ . '/db_connect.php';
include __DIR__ . '/includes/header.php';

$userId   = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

// get count from orders table
$statsStmt = $pdo->prepare(
    "SELECT COUNT(*) AS order_count, COALESCE(SUM(total_price),0) AS total_spent
     FROM orders
     WHERE user_id = ?"
);
$statsStmt->execute([$userId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// get orders from table
$ordersStmt = $pdo->prepare(
    "SELECT id, total_price, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 5"
);
$ordersStmt->execute([$userId]);
$recent = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!--page setup-->
<main class="container">

  <h2>Account Dashboard</h2>
  <p>Welcome back, <?= $username ?>!</p>

  

  <h3>Account Summary</h3>
  <table>
    <thead>
      <tr>
        <th>Total Orders</th>
        <th>Total Spent</th>
        <th>Recent Orders</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= $stats['order_count'] ?></td>
        <td>$<?= number_format($stats['total_spent'], 2) ?></td>
        <td><?= count($recent) ?></td>
      </tr>
    </tbody>
  </table>

  <h3>Recent Orders</h3>
  <?php if (count($recent) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Order #</th><th>Date</th><th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $o): ?>
        <tr>
          <td><a href="order_detail.php?id=<?= $o['id'] ?>">#<?= $o['id'] ?></a></td>
          <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
          <td>$<?= number_format($o['total_price'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No recent orders. <a href="catalog.php">Start shopping now!</a></p>
  <?php endif; ?>


  <h3>Quick Links</h3>
  <p>
    <a href="catalog.php" class="btn">Shop Products</a>
    <a href="cart.php" class="btn">View Cart</a>
    <a href="logout.php" class="btn danger">Log Out</a>
  </p>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>