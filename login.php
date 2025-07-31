<?php
// login.php
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(E_ALL);

session_start();

require 'db_connect.php';

// If already logged in, redirect
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
//if field left blank
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u==='') $errors[] = 'Username is required.';
    if ($p==='') $errors[] = 'Password is required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id,password FROM users WHERE username=?");
        $stmt->execute([$u]);
        $row = $stmt->fetch();

        //verify password
        if ($row && password_verify($p, $row['password'])) {
            $_SESSION['user_id']  = $row['id'];
            $_SESSION['username'] = $u;

            // get user role i.e user or admin
            $roleStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $roleStmt->execute([$row['id']]);
            $_SESSION['role'] = $roleStmt->fetchColumn();
            //go to dashboard
            header('Location: dashboard.php');
            exit;

        } else { //if does not match table
            $errors[] = 'Invalid username or password.';
        }
    }
}

include 'includes/header.php';
?>
<!--login box-->
<main style="display:flex;justify-content:center;align-items:center;min-height:80vh;">
  <div style="
    width:100%;max-width:400px;
    background: white;
    padding:2rem;
    border-radius:0px;
    box-shadow:0 2px 12px rgba(0,0,0,0.1);;
  ">
    <h2 style="text-align:center;margin-bottom:1rem;">Log In</h2>

    <?php if ($errors): ?>
      <ul style="color:#c00;margin-bottom:1rem;">
        <?php foreach ($errors as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <form method="post" action="login.php" style="display:grid;gap:1rem;">
      <label>
        Username<br>
        <input type="text" name="username"
               value="<?= htmlspecialchars($u ?? '') ?>"
               style="width:100%;padding:0.5rem;border:1px solid #ccc;border-radius:4px;"
               required>
      </label>

      <label>
        Password<br>
        <input type="password" name="password"
               style="width:100%;padding:0.5rem;border:1px solid #ccc;border-radius:4px;"
               required>
      </label>

      <button type="submit" style="
        background:#4CAF50;
        color:white;
        padding:0.75rem;
        border:none;
        border-radius:4px;
        cursor:pointer;
        font-size:1rem;
      ">
        Log In
      </button>

      <!-- register button -->
      <p style="text-align:center;margin:0;">
        <a href="register.php" style="
          background:#2196F3;
          color:white;
          padding:0.75rem 1.5rem;
          border:none;
          border-radius:4px;
          text-decoration:none;
          display:inline-block;
        ">
          Register
        </a>
      </p>

      <!-- back to home link -->
      <p style="text-align:center;margin:0;margin-top:0.5rem;">
        <a href="home.html" style="color:#555;text-decoration:none;">
          ← Back to Home
        </a>
      </p>
    </form>
  </div>
</main>

<?php include 'includes/footer.php'; ?>