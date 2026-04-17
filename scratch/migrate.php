<?php
require_once dirname(__DIR__) . '/config/database.php';
$db = new Database();
$conn = $db->getConnection();

$sql = file_get_contents(dirname(__DIR__) . '/database/06_multi_tenant.sql');
try {
    $conn->exec($sql);
    echo "Migration successful.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
