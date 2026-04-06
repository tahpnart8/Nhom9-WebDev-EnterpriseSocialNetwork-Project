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
require_once 'controllers/TaskController.php';
require_once 'controllers/NotificationController.php';
require_once 'controllers/ChatController.php';

$authController = new AuthController();
$dashboardController = new DashboardController();
$adminController = new AdminController();
$socialController = new SocialController();
$taskController = new TaskController();
$notiController = new NotificationController();
$chatController = new ChatController();

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
    case 'tasks':
        $taskController->index();
        break;
    case 'api_create_task':
        $taskController->createTask();
        break;
    case 'api_create_subtask':
        $taskController->createSubtask();
        break;
    case 'api_update_subtask_status':
        $taskController->updateSubtaskStatus();
        break;
    case 'api_notifications':
        $notiController->fetchUnread();
        break;
    case 'api_mark_all_read':
        $notiController->markAllRead();
        break;
    case 'chat':
        $chatController->index();
        break;
    case 'api_send_message':
        $chatController->sendMessage();
        break;
    case 'api_fetch_messages':
        $chatController->fetchMessages();
        break;
    default:
        // 404 Route
        echo "<h1>404 Not Found!</h1>";
        break;
}
?>
