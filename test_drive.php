<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'models/DriveStorage.php';

$drive = new DriveStorage();

$reflection = new ReflectionClass($drive);
$method = $reflection->getMethod('getAccessToken');
$method->setAccessible(true);
$token = $method->invoke($drive);

if ($token) {
    echo "TOKEN_SUCCESS\n";
    // Let's create a test file and upload it
    file_put_contents('test.txt', 'Hello Drive!');
    $result = $drive->uploadFile('test.txt', 'text/plain', 'test.txt');
    echo "UPLOAD_RESULT:\n";
    var_dump($result);
    unlink('test.txt');
} else {
    echo "TOKEN_FAILED\n";
    // Print the raw curl response if possible
}
?>
