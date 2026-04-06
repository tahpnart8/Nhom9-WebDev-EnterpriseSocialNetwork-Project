<?php
// Front Controller Pattern (Bộ định tuyến chính)
session_start();

// Lấy action từ Query String, nếu không có mặc định về dashboard
$action = $_GET['action'] ?? 'dashboard';

// Import Các Controller liên quan
require_once 'controllers/AuthController.php';
require_once 'controllers/DashboardController.php';
require_once 'controllers/AdminController.php';
require_once 'controllers/SocialController.php';

$authController = new AuthController();
$dashboardController = new DashboardController();
$adminController = new AdminController();
$socialController = new SocialController();

// Simple Router
switch($action) {
    case 'login':
        $authController->showLogin();
        break;
    case 'login_submit':
        $authController->login();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'dashboard':
        $dashboardController->index();
        break;
    case 'admin_users':
        $adminController->users();
        break;
    case 'admin_departments':
        $adminController->departments();
        break;
    case 'social':
        $socialController->index();
        break;
    case 'api_create_post':
        $socialController->createPost();
        break;
    default:
        // 404 Route
        echo "<h1>404 Not Found!</h1>";
        break;
}
?>
