<?php
// /BloomBoutique/admin/products.php

require __DIR__ . '/../includes/admin_auth.php';
require __DIR__ . '/../db_connect.php';
include __DIR__ . '/../includes/header.php';

// delete using POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    //remove all options for that product
    $pdo->prepare("DELETE FROM single_flower_options WHERE flower_id = ?")
        ->execute([$delId]);
    // remove the product
    $pdo->prepare("DELETE FROM single_flowers WHERE id = ?")
        ->execute([$delId]);
    //message that deleted
    echo "<p style='color:green;'>Product #{$delId} deleted.</p>";
}

//fetch remaining products
$stmt     = $pdo->query("SELECT id,name,price,image_url FROM single_flowers ORDER BY id");
$products = $stmt->fetchAll();
?>

<main class="container">
  <h2>Manage Products</h2>

  <p>
    <a href="product_form.php" class="btn">+ Add New Product</a>
  </p>

  <table>
    <thead>
      <tr>
        <th>ID</th><th>Name</th><th>Price</th><th>Image</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
      <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td>$<?= number_format($p['price'],2) ?></td>
        <td>
          <?php if ($p['image_url']): ?>
            <img src="/BloomBoutique/<?= htmlspecialchars($p['image_url']) ?>" width="60" alt="">
          <?php endif; ?>
        </td>
        <td>
          <!-- edit button -->
          <a href="product_form.php?id=<?= $p['id'] ?>"
             class="btn small">Edit</a>

          <!-- delete button -->
          <form method="post" style="display:inline;margin:0;">
            <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
            <button type="submit"
                    class="btn danger small"
                    onclick="return confirm('Delete this product and all its options?');">
              Delete
            </button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>