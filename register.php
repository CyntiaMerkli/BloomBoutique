<?php
// register.php

//show erros
ini_set('display_errors',1);
error_reporting(E_ALL);
session_start();

//DB
require 'db_connect.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $u = trim($_POST['username'] ?? '');
    $e = trim($_POST['email']    ?? '');
    $p = $_POST['password']     ?? '';
    $c = $_POST['confirm']      ?? '';

    // make sure field filled in
    if ($u==='') $errors[] = 'Username required.';
    if (!filter_var($e, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (strlen($p) < 6) $errors[] = 'Password must be ≥ 6 chars.';
    if ($p !== $c)    $errors[] = 'Passwords must match.';

    if (empty($errors)) {
        // make sure username/email does not exist in table already
        $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=? OR email=?");
        $chk->execute([$u,$e]);
        if ($chk->fetchColumn() > 0) {
            $errors[] = 'Username or email already taken.';
        } else {
            // insert new user
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $ins  = $pdo->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
            $ins->execute([$u,$e,$hash]);
            // log them in
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $u;
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!--register form-->
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Register</title></head><body>
  <h2>Register</h2>
  <?php if ($errors): ?>
    <ul style="color:red;">
      <?php foreach($errors as $err): ?><li><?=htmlspecialchars($err)?></li><?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <form method="post" action="register.php">
    <label>Username:<br><input name="username" value="<?=htmlspecialchars($u ?? '')?>" required></label><br><br>
    <label>Email:<br><input type="email" name="email" value="<?=htmlspecialchars($e ?? '')?>" required></label><br><br>
    <label>Password:<br><input type="password" name="password" required></label><br><br>
    <label>Confirm Password:<br><input type="password" name="confirm" required></label><br><br>
    <button type="submit">Register</button>
  </form>
  <p>Already have an account? <a href="login.php">Login here</a>.</p>
</body></html>