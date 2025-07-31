<?php
// api/contact_submit.php
header('Content-Type: application/json');
require __DIR__ . '/../db_connect.php';

//make sure fields filled out
$data = json_decode(file_get_contents('php://input'), true);
$name    = trim($data['name']    ?? '');
$email   = trim($data['email']   ?? '');
$message = trim($data['message'] ?? '');

$errors = [];
if ($name === '')    $errors[] = 'Name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if ($message === '') $errors[] = 'Message cannot be empty.';

if ($errors) {
  echo json_encode(['success'=>false, 'errors'=>$errors]);
  exit;
}

// insert into DB
$stmt = $pdo->prepare("
  INSERT INTO contact_messages (name,email,message)
  VALUES (?,?,?)
");
$stmt->execute([$name,$email,$message]);

echo json_encode(['success'=>true]);