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
    case 'api_submit_evidence':
        $taskController->submitEvidence();
        break;
    case 'api_approve_subtask':
        $taskController->approveSubtask();
        break;
    case 'api_reject_subtask':
        $taskController->rejectSubtask();
        break;
    case 'api_complete_subtask':
        $taskController->completeSubtask();
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
    case 'api_delete_post':
        $socialController->deletePost();
        break;
    case 'api_edit_post':
        $socialController->editPost();
        break;
    case 'api_toggle_post_reaction':
        $socialController->togglePostReaction();
        break;
    case 'api_fetch_comments':
        $socialController->fetchComments();
        break;
    case 'api_add_comment':
        $socialController->addComment();
        break;
    case 'api_toggle_comment_reaction':
        $socialController->toggleCommentReaction();
        break;
    case 'api_edit_comment':
        $socialController->editComment();
        break;
    case 'api_delete_comment':
        $socialController->deleteComment();
        break;
    case 'api_fetch_post_likers':
        $socialController->fetchPostLikers();
        break;
    case 'api_fetch_comment_likers':
        $socialController->fetchCommentLikers();
        break;
    case 'api_subtask_detail':
        $taskController->getSubtaskDetail();
        break;
    default:
        // 404 Route
        echo "<h1>404 Not Found!</h1>";
        break;
}
?>
