<?php
require_once dirname(__DIR__) . '/config/database.php';
$db = new Database();
$conn = $db->getConnection();
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password_hash = :hash WHERE username = 'admin'");
$stmt->execute(['hash' => $hash]);
echo "Password reset to admin123. Hash: " . $hash . "\n";
