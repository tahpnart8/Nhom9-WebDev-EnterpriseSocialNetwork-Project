<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Subtask.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

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

    // Trang Quản lý Công việc chính
    public function index() {
        $this->checkAuth();
        $pageTitle = "Quản lý Công việc";
        
        $subtaskModel = new Subtask($this->db);
        $taskModel = new Task($this->db);
        
        $roleId = $_SESSION['role_id'];
        $userId = $_SESSION['user_id'];
        $deptId = $_SESSION['department_id'] ?? null;

        // Phân quyền xem dữ liệu
        if ($roleId == 1 || $roleId == 4) { // CEO/Admin: xem tất cả
            $subtasks = $subtaskModel->getAll();
            $tasks = $taskModel->getAll();
        } elseif ($roleId == 2) { // Leader: xem theo phòng ban
            $subtasks = $subtaskModel->getByDepartment($deptId);
            $tasks = $taskModel->getByDepartment($deptId);
        } else { // Staff: chỉ xem subtask của mình
            $subtasks = $subtaskModel->getByAssignee($userId);
            $tasks = []; // Staff có thể xem danh sách task lớn để biết bối cảnh nếu cần, nhưng hiện tại để trống
        }

        // Dữ liệu cho Bảng "Quản lý theo tiến độ" (Kanban truyền thống)
        $columns = [
            'To Do'       => [],
            'In Progress' => [],
            'Pending'     => [],
            'Done'        => []
        ];
        foreach ($subtasks as $st) {
            $columns[$st['status']][] = $st;
        }

        // Dữ liệu cho Bảng "Quản lý theo Task" (Group by Task)
        $tasksWithSubtasks = [];
        if (!empty($tasks)) {
            foreach ($tasks as $t) {
                $t['subtasks'] = array_filter($subtasks, function($s) use ($t) {
                    return $s['task_id'] == $t['id'];
                });
                $tasksWithSubtasks[] = $t;
            }
        } else {
            // Nếu là Staff, lấy các Task mà Staff đó có Subtask
            $taskIds = array_unique(array_column($subtasks, 'task_id'));
            foreach ($taskIds as $tid) {
                $taskInfo = $taskModel->getById($tid);
                $taskInfo['subtasks'] = array_filter($subtasks, function($s) use ($tid) {
                    return $s['task_id'] == $tid;
                });
                $tasksWithSubtasks[] = $taskInfo;
            }
        }

        // Lấy danh sách nhân viên trong phòng ban (cho modal tạo subtask)
        $userModel = new User($this->db);
        $staffList = [];
        if ($roleId == 1 || $roleId == 2 || $roleId == 4) {
            $staffStmt = $userModel->getAllUsersWithDetails();
            $staffList = $staffStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require_once __DIR__ . '/../views/tasks/index.php';
    }

    // API: Tạo Task mới
    public function createTask() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
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

        // API: Gửi minh chứng duyệt (Staff)
    public function submitEvidence() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $notes = htmlspecialchars(trim($_POST['notes'] ?? ''));
        $fileUrl = $_POST['file_url'] ?? null;
        
        $subtask = $subtaskModel->getById($subtaskId);
        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy subtask!']);
            exit;
        }

        if ($subtask['assignee_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
            exit;
        }

        if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
            require_once __DIR__ . '/../models/CloudStorage.php';
            $cloudStorage = new CloudStorage();
            $cloudUrl = $cloudStorage->uploadImage($_FILES['evidence_file']['tmp_name']);
            if ($cloudUrl !== false) {
                $fileUrl = $cloudUrl;
            }
        }

        if ($subtaskModel->submitEvidence($subtaskId, $notes, $fileUrl)) {
            $taskModel = new Task($this->db);
            $task = $taskModel->getById($subtask['task_id']);
            
            NotificationController::pushNotification(
                $this->db, 
                'task_approval', 
                $_SESSION['user_id'], 
                "Nhân viên " . $_SESSION['full_name'] . " đã gửi duyệt subtask: " . $subtask['title'],
                "index.php?action=tasks", 
                [$task['created_by_user_id']]
            );
            
            echo json_encode(['success' => true, 'message' => 'Đã gửi duyệt công việc!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống!']);
        }
        exit;
    }


        // API: Duyệt Subtask (Leader/CEO)
    public function approveSubtask() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền duyệt!']);
            exit;
        }
        
        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        
        $subtask = $subtaskModel->getById($subtaskId);
        if ($subtask && $subtaskModel->approve($subtaskId)) {
            NotificationController::pushNotification(
                $this->db, 
                'task_approved', 
                $_SESSION['user_id'], 
                "Subtask '" . $subtask['title'] . "' đã được DUYỆT và Hoàn thành!",
                "index.php?action=tasks", 
                [$subtask['assignee_id']]
            );
            $this->syncTaskStatus($subtask['task_id']);
            echo json_encode(['success' => true, 'message' => 'Đã duyệt! Task lọt vào cột Hoàn thành.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi!']);
        }
        exit;
    }

        // API: Từ chối Subtask (Leader/CEO)
    public function rejectSubtask() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
            exit;
        }
        
        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $reason = htmlspecialchars($_POST['reason'] ?? 'Cần thực hiện lại.');

        if ($subtaskModel->reject($subtaskId, $reason)) {
            $subtask = $subtaskModel->getById($subtaskId);
            NotificationController::pushNotification(
                $this->db, 
                'task_rejected', 
                $_SESSION['user_id'], 
                "Subtask '" . $subtask['title'] . "' bị TỪ CHỐI: $reason",
                "index.php?action=tasks", 
                [$subtask['assignee_id']]
            );
            $this->syncTaskStatus($subtask['task_id']);
            echo json_encode(['success' => true, 'message' => 'Đã từ chối!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi!']);
        }
        exit;
    }

        // API: Xóa Subtask
    public function deleteSubtask() {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa!']);
            exit;
        }

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;

        $subtask = $subtaskModel->getById($subtaskId);
        if ($subtask && $subtaskModel->delete($subtaskId)) {
            $this->syncTaskStatus($subtask['task_id']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa!']);
        }
        exit;
    }

    // API: Cập nhật trạng thái bằng kéo thả
    public function updateSubtaskStatus() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        $subtask = $subtaskModel->getById($subtaskId);
        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Lỗi dữ liệu!']);
            exit;
        }

        if ($subtask['assignee_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Chỉ có người được giao mới có quyền kéo thẻ!']);
            exit;
        }

        // Chặn kéo VÀO cột Pending, Done
        if ($status == 'Pending' || $status == 'Done') {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhấn vào thẻ để Gửi Duyệt qua Form! Không cho phép kéo thả.']);
            exit;
        }

        // Chặn kéo TỪ cột Pending, Done
        if ($subtask['status'] == 'Pending' || $subtask['status'] == 'Done') {
            echo json_encode(['success' => false, 'message' => 'Thẻ đang chờ duyệt hoặc hoàn thành không thể kéo đi nơi khác!']);
            exit;
        }

        // Mặc định gọi updateStatus không truyền argument thứ 3 -> is_rejected = 0 
        // Giúp thẻ bị từ chối sẽ trở thành 1 thẻ In Progress bình thường
        if ($subtaskModel->updateStatus($subtaskId, $status)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB!']);
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

    // API: Tạo Subtask mới
    public function createSubtask() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
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
            echo json_encode(['success' => true, 'message' => 'Giao việc thành công!', 'subtask_id' => $subtaskId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi Database!']);
        }
        exit;
    }

    public function getSubtaskDetail() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_GET['id'] ?? 0;
        $subtask = $subtaskModel->getById($subtaskId);

        if ($subtask) {
            $stmt = $this->db->prepare("SELECT * FROM subtask_attachments WHERE subtask_id = :id ORDER BY uploaded_at DESC");
            $stmt->execute([':id' => $subtaskId]);
            $subtask['attachments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $subtask]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy subtask!']);
        }
        exit;
    }
}
