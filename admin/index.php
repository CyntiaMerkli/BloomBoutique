

<?php
// /BloomBoutique/admin/index.php
require __DIR__ . '/../includes/admin_auth.php';
include __DIR__ . '/../includes/header.php';
?>
<main class="container" style="text-align:center;margin-top:2rem;">
  <h2>Admin Dashboard</h2>
  <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>. Choose an action:</p>
<!--admin control options-->
  <div class="actions-grid" style="max-width:400px;margin:2rem auto;gap:1rem;display:grid;">
    <a href="themes.php"    class="btn">Manage Themes</a>
    <a href="products.php"  class="btn">Manage Products</a>
    <a href="messages.php"  class="btn">View Customer Messages</a>
    <a href="orders.php" class="btn">View Customer Orders</a>
  </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>