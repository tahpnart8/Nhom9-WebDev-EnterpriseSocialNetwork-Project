<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Subtask.php';
require_once __DIR__ . '/../models/User.php';

class TaskController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Kiểm tra đăng nhập
    private function checkAuth() {
        if(!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
    }

    // Trang Kanban Board chính
    public function index() {
        $this->checkAuth();
        $pageTitle = "Quản lý Công việc";
        
        $subtaskModel = new Subtask($this->db);
        $taskModel = new Task($this->db);
        
        $roleId = $_SESSION['role_id'];
        $userId = $_SESSION['user_id'];
        $deptId = $_SESSION['department_id'] ?? null;

        // Phân quyền xem dữ liệu
        if ($roleId == 1) { // CEO: xem tất cả
            $subtasks = $subtaskModel->getAll();
            $tasks = $taskModel->getAll();
        } elseif ($roleId == 2) { // Leader: xem theo phòng ban
            $subtasks = $subtaskModel->getByDepartment($deptId);
            $tasks = $taskModel->getByDepartment($deptId);
        } else { // Staff: chỉ xem subtask của mình
            $subtasks = $subtaskModel->getByAssignee($userId);
            $tasks = []; // Staff không cần xem danh sách task lớn
        }

        // Phân loại subtasks theo 4 cột Kanban
        $columns = [
            'To Do'       => [],
            'In Progress' => [],
            'Pending'     => [],
            'Done'        => []
        ];
        foreach ($subtasks as $st) {
            $columns[$st['status']][] = $st;
        }

        // Lấy danh sách nhân viên trong phòng ban (cho modal tạo subtask)
        $userModel = new User($this->db);
        $staffList = [];
        if ($roleId == 1 || $roleId == 2) {
            $staffStmt = $userModel->getAllUsersWithDetails();
            $staffList = $staffStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require_once __DIR__ . '/../views/tasks/index.php';
    }

    // API: Tạo Task mới (Leader/CEO)
    public function createTask() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền tạo Task!']);
            exit;
        }
        
        $taskModel = new Task($this->db);
        $deptId = $_POST['department_id'] ?? $_SESSION['department_id'];
        $title = htmlspecialchars(trim($_POST['title'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $priority = $_POST['priority'] ?? 'Medium';
        $deadline = $_POST['deadline'] ?? null;
        
        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Tiêu đề Task không được để trống!']);
            exit;
        }
        
        $taskId = $taskModel->create($deptId, $_SESSION['user_id'], $title, $description, $priority, $deadline);
        
        if ($taskId) {
            echo json_encode(['success' => true, 'message' => 'Tạo Task thành công.', 'task_id' => $taskId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu.']);
        }
        exit;
    }

    // API: Tạo Subtask mới (Leader gán cho Nhân viên)
    public function createSubtask() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền tạo Subtask!']);
            exit;
        }
        
        $subtaskModel = new Subtask($this->db);
        $taskId = $_POST['task_id'] ?? 0;
        $assigneeId = $_POST['assignee_id'] ?? 0;
        $title = htmlspecialchars(trim($_POST['title'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $deadline = $_POST['deadline'] ?? null;
        
        if (empty($title) || empty($taskId) || empty($assigneeId)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đủ thông tin!']);
            exit;
        }
        
        $subtaskId = $subtaskModel->create($taskId, $assigneeId, $title, $description, $deadline);
        
        if ($subtaskId) {
            echo json_encode(['success' => true, 'message' => 'Giao việc thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi Database!']);
        }
        exit;
    }

    // API: Cập nhật trạng thái Subtask (Kéo thả / Nút bấm)
    public function updateSubtaskStatus() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $newStatus = $_POST['status'] ?? '';
        $roleId = $_SESSION['role_id'];
        
        $validStatuses = ['To Do', 'In Progress', 'Pending', 'Done'];
        if (!in_array($newStatus, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ!']);
            exit;
        }
        
        // Luồng duyệt việc:
        // - Staff chỉ được chuyển: To Do → In Progress, In Progress → Pending (nộp bài)
        // - Leader/CEO: Pending → Done (duyệt) hoặc Pending → In Progress (trả lại)
        $subtask = $subtaskModel->getById($subtaskId);
        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Subtask không tồn tại!']);
            exit;
        }
        
        $currentStatus = $subtask['status'];
        
        if ($roleId == 3) { // Staff
            $allowed = [
                'To Do' => ['In Progress'],
                'In Progress' => ['Pending']
            ];
            if (!isset($allowed[$currentStatus]) || !in_array($newStatus, $allowed[$currentStatus])) {
                echo json_encode(['success' => false, 'message' => 'Nhân viên chỉ có thể chuyển từ "Cần làm → Đang xử lý → Nộp duyệt"!']);
                exit;
            }
        }
        
        $result = $subtaskModel->updateStatus($subtaskId, $newStatus);
        
        if ($result) {
            // Tự động cập nhật trạng thái task lớn dựa trên tiến độ subtasks
            $this->syncTaskStatus($subtask['task_id']);
            echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật!']);
        }
        exit;
    }
    
    // Tự động đồng bộ trạng thái Task lớn dựa vào các subtask con
    private function syncTaskStatus($task_id) {
        $subtaskModel = new Subtask($this->db);
        $taskModel = new Task($this->db);
        $allSubtasks = $subtaskModel->getByTaskId($task_id);
        
        if (empty($allSubtasks)) return;
        
        $allDone = true;
        $anyInProgress = false;
        foreach ($allSubtasks as $s) {
            if ($s['status'] != 'Done') $allDone = false;
            if ($s['status'] == 'In Progress' || $s['status'] == 'Pending') $anyInProgress = true;
        }
        
        if ($allDone) {
            $taskModel->updateStatus($task_id, 'Done');
        } elseif ($anyInProgress) {
            $taskModel->updateStatus($task_id, 'In Progress');
        }
    }

    // API: Lấy chi tiết 1 subtask (dành cho Modal chi tiết khi click thẻ Kanban)
    public function getSubtaskDetail() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_GET['id'] ?? 0;
        $subtask = $subtaskModel->getById($subtaskId);

        if ($subtask) {
            echo json_encode(['success' => true, 'data' => $subtask]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy subtask!']);
        }
        exit;
    }
}
?>
