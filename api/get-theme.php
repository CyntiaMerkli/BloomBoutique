<?php
// /BloomBoutique/api/get_theme.php

//tell the browser returning JSON
header('Content-Type: application/json');

// DB 
require __DIR__ . '/../db_connect.php';

//fetch the current theme setting
$stmt = $pdo->prepare("
  SELECT setting_value
    FROM settings
   WHERE setting_key = 'current_theme'
");
$stmt->execute();
$theme = $stmt->fetchColumn() ?: 'theme-default.css';

// send as JSON
echo json_encode(['theme' => $theme]);