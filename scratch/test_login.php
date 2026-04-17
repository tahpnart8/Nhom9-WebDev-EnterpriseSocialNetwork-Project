<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$username = 'admin';
$password = 'admin123';

$stmt = $db->query("SELECT * FROM users WHERE username = 'admin'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo "Found user: " . print_r($row, true) . "\n";
    echo "Hash test: " . (password_verify('admin123', $row['password_hash']) ? 'true' : 'false') . "\n";
} else {
    echo "User not found\n";
    print_r($db->errorInfo());
}
