<?php
// /admin/product_form.php
require __DIR__ . '/../includes/admin_auth.php';
require __DIR__ . '/../db_connect.php';
include __DIR__ . '/../includes/header.php';

$errors = [];
$id        = isset($_GET['id']) ? (int)$_GET['id'] : null;
$name      = '';
$price     = '';
$image_url = '';
$description = '';
$category    = '';
$options     = [];

//if editing product, load existing product & its options from single_flowers table
if ($id) {
    $p = $pdo->prepare("SELECT * FROM single_flowers WHERE id=?");
    $p->execute([$id]);
    $row = $p->fetch();
    if ($row) {
        $name        = $row['name'];
        $price       = $row['price'];
        $image_url   = $row['image_url'];
        $description = $row['description'];
        $category    = $row['category'];
        // flower options
        $optStmt = $pdo->prepare(
          "SELECT option_name,option_value,price_modifier 
             FROM single_flower_options 
            WHERE flower_id = ?"
        );
        $optStmt->execute([$id]);
        $options = $optStmt->fetchAll();
    }
}

// submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name        = trim($_POST['name'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $image_url   = trim($_POST['image_url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = trim($_POST['category'] ?? '');

    // make sure no required field left blank
    if ($name === '')       $errors[] = 'Name is required.';
    if ($price === ''       || !is_numeric($price))   $errors[] = 'Valid price is required.';
    if ($category === '')   $errors[] = 'Category is required.';

    // file upload
    if (empty($errors) && !empty($_FILES['image_file']['tmp_name'])) {
        if ($_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $tmp  = $_FILES['image_file']['tmp_name'];
            $fn   = basename($_FILES['image_file']['name']);
            $dest = __DIR__ . '/../images/' . $fn;
            if (move_uploaded_file($tmp, $dest)) {
                $image_url = 'images/' . $fn;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }

    if (empty($errors)) {
        if ($id) {
            // update product
            $upd = $pdo->prepare("
              UPDATE single_flowers
                 SET name=?, price=?, image_url=?, description=?, category=?
               WHERE id=?
            ");
            $upd->execute([
              $name, $price, $image_url, $description, $category, $id
            ]);
        } else {
            // insert new product
            $ins = $pdo->prepare("
              INSERT INTO single_flowers
                (name,price,image_url,description,category)
              VALUES (?,?,?,?,?)
            ");
            $ins->execute([
              $name, $price, $image_url, $description, $category
            ]);
            $id = $pdo->lastInsertId();
        }

        // delete old, insert new
        $del = $pdo->prepare(
          "DELETE FROM single_flower_options WHERE flower_id = ?"
        );
        $del->execute([$id]);

        // loop through options arrays
        $names = $_POST['opt_name'] ?? [];
        $vals  = $_POST['opt_value'] ?? [];
        $mods  = $_POST['opt_price'] ?? [];
        for ($i = 0; $i < count($names); $i++) {
            $on = trim($names[$i]);
            $ov = trim($vals[$i]);
            $pm = trim($mods[$i]);
            if ($on !== '' && $ov !== '' && is_numeric($pm)) {
                $iso = $pdo->prepare("
                  INSERT INTO single_flower_options
                    (flower_id,option_name,option_value,price_modifier)
                  VALUES (?,?,?,?)
                ");
                $iso->execute([$id, $on, $ov, $pm]);
            }
        }

        header('Location: products.php');
        exit;
    }
}
?>

<main class="container">
  <h2><?= $id ? 'Edit Product' : 'Add New Product' ?></h2>

  <?php if ($errors): ?>
    <div class="alert danger">
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="form-group">

    <label>Name:
      <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
    </label>

    <label>Price:
      <input type="text" name="price" value="<?= htmlspecialchars($price) ?>" required>
    </label>

    <label>Category:
      <input type="text" name="category" value="<?= htmlspecialchars($category) ?>" required>
    </label>

    <label>Description:
      <textarea name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>
    </label>

    <label>Image URL:
      <input type="text" name="image_url" value="<?= htmlspecialchars($image_url) ?>">
    </label>
    <label>—or Upload Image:
      <input type="file" name="image_file" accept="image/*">
    </label>

    <h3>Options</h3>
    <div id="options-container">
      <?php if ($options): foreach ($options as $opt): ?>
        <div class="opt-row">
          <input type="text" name="opt_name[]"  value="<?= htmlspecialchars($opt['option_name']) ?>" placeholder="Option Name">
          <input type="text" name="opt_value[]" value="<?= htmlspecialchars($opt['option_value']) ?>" placeholder="Option Value">
          <input type="text" name="opt_price[]" value="<?= htmlspecialchars($opt['price_modifier']) ?>" placeholder="Price Modifier">
          <button type="button" class="remove-opt">×</button>
        </div>
      <?php endforeach; else: ?>

        <!-- start with one blank row -->
        <div class="opt-row">
          <input type="text" name="opt_name[]"  placeholder="Option Name">
          <input type="text" name="opt_value[]" placeholder="Option Value">
          <input type="text" name="opt_price[]" placeholder="Price Modifier">
          <button type="button" class="remove-opt">×</button>
        </div>
      <?php endif; ?>
    </div>

    <button type="button" id="add-option" class="btn secondary small">
      + Add Option
    </button>

    <div class="actions" style="margin-top:1.5rem;">
      <button type="submit" class="btn"><?= $id ? 'Save Changes' : 'Add Product' ?></button>
      <a href="products.php" class="btn secondary">Cancel</a>
    </div>
  </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- JS to add/remove option rows-->
<script>
document.getElementById('add-option').addEventListener('click', function(){
  const cont = document.getElementById('options-container');
  const div = document.createElement('div');
  div.className = 'opt-row';
  div.innerHTML = `
    <input type="text" name="opt_name[]"  placeholder="Option Name">
    <input type="text" name="opt_value[]" placeholder="Option Value">
    <input type="text" name="opt_price[]" placeholder="Price Modifier">
    <button type="button" class="remove-opt">×</button>
  `;
  cont.appendChild(div);
});
document.addEventListener('click', function(e){
  if (e.target.matches('.remove-opt')) {
    e.target.parentNode.remove();
  }
});
</script>