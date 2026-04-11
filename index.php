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
require_once 'controllers/ProfileController.php';

$authController = new AuthController();
$dashboardController = new DashboardController();
$adminController = new AdminController();
$socialController = new SocialController();
$taskController = new TaskController();
$notiController = new NotificationController();
$chatController = new ChatController();
$profileController = new ProfileController();

$routes = [
    'login' => [$authController, 'showLogin'],
    'login_submit' => [$authController, 'login'],
    'logout' => [$authController, 'logout'],
    'dashboard' => [$dashboardController, 'index'],

    'admin_users' => [$adminController, 'users'],
    'admin_departments' => [$adminController, 'departments'],

    'social' => [$socialController, 'index'],
    'api_create_post' => [$socialController, 'createPost'],
    'api_delete_post' => [$socialController, 'deletePost'],
    'api_edit_post' => [$socialController, 'editPost'],
    'api_toggle_post_reaction' => [$socialController, 'togglePostReaction'],
    'api_fetch_comments' => [$socialController, 'fetchComments'],
    'api_add_comment' => [$socialController, 'addComment'],
    'api_toggle_comment_reaction' => [$socialController, 'toggleCommentReaction'],
    'api_edit_comment' => [$socialController, 'editComment'],
    'api_delete_comment' => [$socialController, 'deleteComment'],
    'api_fetch_post_likers' => [$socialController, 'fetchPostLikers'],
    'api_fetch_comment_likers' => [$socialController, 'fetchCommentLikers'],

    'tasks' => [$taskController, 'index'],
    'api_create_task' => [$taskController, 'createTask'],
    'api_create_subtask' => [$taskController, 'createSubtask'],
    'api_update_subtask_status' => [$taskController, 'updateSubtaskStatus'],
    'api_submit_evidence' => [$taskController, 'submitEvidence'],
    'api_approve_subtask' => [$taskController, 'approveSubtask'],
    'api_reject_subtask' => [$taskController, 'rejectSubtask'],
    'api_delete_subtask' => [$taskController, 'deleteSubtask'],
    'api_subtask_detail' => [$taskController, 'getSubtaskDetail'],
    'api_check_evidence' => [$taskController, 'checkEvidence'],
    'api_task_detail' => [$taskController, 'getTaskDetail'],
    'api_delete_task' => [$taskController, 'deleteTask'],
    'api_extend_subtask' => [$taskController, 'extendSubtask'],
    'api_save_evidence' => [$taskController, 'saveEvidence'],
    'api_generate_subtask_report' => [$taskController, 'generateSubtaskReport'],
    'api_save_subtask_report' => [$taskController, 'saveSubtaskReport'],
    'api_generate_task_summary' => [$taskController, 'generateTaskSummary'],
    'api_save_task_summary' => [$taskController, 'saveTaskSummary'],

    'api_notifications' => [$notiController, 'fetchUnread'],
    'api_mark_all_read' => [$notiController, 'markAllRead'],
    'api_mark_one_read' => [$notiController, 'markOneRead'],

    'chat' => [$chatController, 'index'],
    'api_send_message' => [$chatController, 'sendMessage'],
    'api_fetch_messages' => [$chatController, 'fetchMessages'],

    'profile' => [$profileController, 'index'],
    'api_update_profile' => [$profileController, 'updateProfile'],
];

if (array_key_exists($action, $routes)) {
    call_user_func($routes[$action]);
} else {
    // 404 Route
    echo "<h1>404 Not Found!</h1>";
}
?>