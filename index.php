<?php
// Front Controller Pattern (Bộ định tuyến chính)
session_start();

// Lấy action từ Query String, nếu không có mặc định về dashboard
$action = $_GET['action'] ?? 'dashboard';

// Import Các Controller liên quan
require_once 'controllers/AuthController.php';

$authController = new AuthController();

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
        // Middleware bảo vệ trang, yêu cầu đăng nhập
        if(!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
        echo "<h1>Trang Dashboard (Sẽ được xây dựng ở Phiên 2)</h1>";
        echo "<h3>Xin chào: " . htmlspecialchars($_SESSION['full_name']) . "</h3>";
        echo "<hr><a href='index.php?action=logout'>Đăng xuất</a>";
        break;
    default:
        // 404 Route
        echo "<h1>404 Not Found!</h1>";
        break;
}
?>
