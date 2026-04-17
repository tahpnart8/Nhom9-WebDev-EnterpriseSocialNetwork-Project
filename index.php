<?php
// Thiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Front Controller Pattern (Bộ định tuyến chính)
session_start();

// Load biến môi trường từ .env
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

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
require_once 'controllers/ProjectController.php';

$authController = new AuthController();
$dashboardController = new DashboardController();
$adminController = new AdminController();
$socialController = new SocialController();
$taskController = new TaskController();
$notiController = new NotificationController();
$chatController = new ChatController();
$profileController = new ProfileController();
$projectController = new ProjectController();

$routes = [
    'login' => [$authController, 'showLogin'],
    'login_submit' => [$authController, 'login'],
    'logout' => [$authController, 'logout'],
    
    'admin_secret_portal' => [$authController, 'showAdminLogin'],
    'admin_login_submit' => [$authController, 'adminLogin'],
    'register_company' => [$authController, 'showRegisterCompany'],
    'register_company_submit' => [$authController, 'registerCompany'],
    
    'admin_dashboard' => [$adminController, 'superAdminDashboard'],
    'manage_companies' => [$adminController, 'manageCompanies'],
    'api_approve_company' => [$adminController, 'apiApproveCompany'],
    'api_reject_company' => [$adminController, 'apiRejectCompany'],
    'api_update_company' => [$adminController, 'apiUpdateCompany'],
    'api_delete_company' => [$adminController, 'apiDeleteCompany'],
    'api_broadcast' => [$adminController, 'apiBroadcast'],
    'audit_logs' => [$adminController, 'auditLogs'],

    'dashboard' => [$dashboardController, 'index'],

    'admin_users' => [$adminController, 'users'],
    'api_add_user' => [$adminController, 'apiAddUser'],
    'api_update_user' => [$adminController, 'apiUpdateUser'],
    'api_delete_user' => [$adminController, 'apiDeleteUser'],
    'admin_departments' => [$adminController, 'departments'],
    'api_add_department' => [$adminController, 'apiAddDepartment'],
    'api_update_department' => [$adminController, 'apiUpdateDepartment'],
    'api_delete_department' => [$adminController, 'apiDeleteDepartment'],

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
    'api_get_post_details' => [$socialController, 'fetchPostDetails'],
    'api_search_posts' => [$socialController, 'apiSearchPosts'],
    'api_search_users' => [$profileController, 'apiSearchUsers'],

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
    'api_request_extension' => [$taskController, 'requestExtension'],
    'api_save_evidence' => [$taskController, 'saveEvidence'],
    'api_generate_subtask_report' => [$taskController, 'generateSubtaskReport'],
    'api_save_subtask_report' => [$taskController, 'saveSubtaskReport'],
    'api_generate_task_summary' => [$taskController, 'generateTaskSummary'],
    'api_save_task_summary' => [$taskController, 'saveTaskSummary'],
    'api_urgent_subtasks' => [$taskController, 'fetchUrgentSubtasks'],
    'api_search_tasks' => [$taskController, 'apiSearchTasks'],
    'api_submit_task_to_ceo' => [$taskController, 'submitTaskToCEO'],
    'api_generate_task_report_for_ceo' => [$taskController, 'apiGenerateTaskReportForCEO'],
    'api_ceo_approve_task' => [$taskController, 'ceoApproveTask'],
    'api_update_task' => [$taskController, 'apiUpdateTask'],
    'api_update_subtask' => [$taskController, 'apiUpdateSubtask'],

    'api_create_project' => [$projectController, 'createProject'],
    'api_update_project' => [$projectController, 'updateProject'],
    'api_delete_project' => [$projectController, 'deleteProject'],
    'api_complete_project' => [$projectController, 'completeProject'],
    'api_generate_project_summary' => [$projectController, 'apiGenerateProjectSummary'],
    'api_get_project_detail' => [$projectController, 'getProjectDetail'],

    'api_notifications' => [$notiController, 'fetchUnread'],
    'api_mark_all_read' => [$notiController, 'markAllRead'],
    'api_mark_one_read' => [$notiController, 'markOneRead'],

    'chat' => [$chatController, 'index'],
    'api_send_message' => [$chatController, 'sendMessage'],
    'api_fetch_messages' => [$chatController, 'fetchMessages'],
    'api_fetch_new_messages' => [$chatController, 'fetchNewMessages'],
    'api_fetch_older_messages' => [$chatController, 'fetchOlderMessages'],
    'api_fetch_sidebar_chats' => [$chatController, 'fetchSidebarChats'],
    'api_unread_chat_count' => [$chatController, 'fetchUnreadCount'],
    'api_heartbeat' => [$chatController, 'heartbeat'],
    'api_create_group' => [$chatController, 'api_create_group'],
    'api_get_group_info' => [$chatController, 'api_get_group_info'],
    'api_manage_members' => [$chatController, 'api_manage_members'],
    'api_handle_membership_request' => [$chatController, 'api_handle_membership_request'],
    'api_update_group_settings' => [$chatController, 'api_update_group_settings'],
    'api_delete_conversation' => [$chatController, 'deleteConversation'],

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