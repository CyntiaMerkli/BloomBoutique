<?php
// /BloomBoutique/admin/themes.php

//session_start();
require __DIR__ . '/../includes/admin_auth.php'; //make sure admin
require __DIR__ . '/../db_connect.php';

//redirect guests
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

//Restrict to admins
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetchColumn() !== 'admin') {
    http_response_code(403);
    echo '<p style="padding:2rem;text-align:center;color:red;">Access Denied</p>';
    exit;
}

//available themes
$themes = ['theme-default.css','theme-spring.css','theme-holiday.css'];

//submission of theme
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['theme'])) {
    $new = $_POST['theme'];
    if (in_array($new, $themes, true)) {
        $up = $pdo->prepare("
          UPDATE settings
             SET setting_value = ?
           WHERE setting_key   = 'current_theme'
        ");
        $up->execute([$new]);
        header('Location: themes.php?updated=1');
        exit;
    }
}

//fetch current theme
$stmt = $pdo->prepare("
  SELECT setting_value
    FROM settings
   WHERE setting_key = 'current_theme'
");
$stmt->execute();
$current = $stmt->fetchColumn() ?: 'theme-default.css';

//header
include __DIR__ . '/../includes/header.php';
?>

<main>
  <section class="admin-container">
    <div class="card">
      <h2 class="card-title">Site Theme Settings</h2>

      <?php if (isset($_GET['updated'])): ?>
        <div class="alert success">✅ Theme updated successfully!</div>
      <?php endif; ?>

      <form method="post" action="themes.php" class="form-group">
        <label for="theme-select">Active Theme:</label>
        <select id="theme-select" name="theme">
          <?php foreach ($themes as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>"
                    <?= $t === $current ? 'selected' : '' ?>>
              <?= htmlspecialchars(str_replace(['theme-','.css'],['',''], $t)) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <div class="actions">
          <button type="submit" class="btn">Activate Theme</button>
          <a href="index.php" class="btn secondary">← Back to Admin Page</a>
        </div>
      </form>
    </div>
  </section>
</main>
<!-- /admin/themes.php-->
<?include '../includes/admin_auth.php';?>  <!-- check for admin-->

<?php include __DIR__ . '/../includes/footer.php'; ?>
