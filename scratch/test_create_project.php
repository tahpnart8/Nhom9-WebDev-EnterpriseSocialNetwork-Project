<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mock session
session_start();
$_SESSION['role_id'] = 1;
$_SESSION['user_id'] = 1;

// Mock POST data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['title'] = 'Test Project';
$_POST['description'] = 'Test Desc';
$_POST['department_ids'] = '1,2';

require_once 'controllers/ProjectController.php';
$controller = new ProjectController();
$controller->createProject();
?>
