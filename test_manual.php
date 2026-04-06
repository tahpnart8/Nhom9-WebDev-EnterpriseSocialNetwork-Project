<?php
require 'models/DriveStorage.php';
$d = new DriveStorage();
print_r($d->uploadFile('test_drive.php', 'text/plain', 'test.txt'));
