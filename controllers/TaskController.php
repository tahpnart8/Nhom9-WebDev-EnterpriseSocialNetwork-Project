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
            $tasks = [];
        }

        // Dữ liệu cho Bảng "Quản lý theo tiến độ" (Kanban) - 5 cột bao gồm Trễ hạn
        $columns = [
            'To Do'       => [],
            'In Progress' => [],
            'Pending'     => [],
            'Done'        => [],
            'Overdue'     => []
        ];
        $now = date('Y-m-d H:i:s');
        foreach ($subtasks as $st) {
            // Nếu subtask trễ hạn (deadline < now && status != Done) => chuyển sang cột Overdue
            if (!empty($st['deadline']) && $st['deadline'] < $now && $st['status'] != 'Done') {
                $columns['Overdue'][] = $st;
            } else {
                $columns[$st['status']][] = $st;
            }
        }

        // Dữ liệu cho Bảng "Quản lý theo Task" (Group by Task)
        $tasksWithSubtasks = [];
        if (!empty($tasks)) {
            foreach ($tasks as $t) {
                $t['subtasks'] = array_values(array_filter($subtasks, function($s) use ($t) {
                    return $s['task_id'] == $t['id'];
                }));
                $tasksWithSubtasks[] = $t;
            }
        } else {
            // Nếu là Staff, lấy các Task mà Staff đó có Subtask
            $taskIds = array_unique(array_column($subtasks, 'task_id'));
            foreach ($taskIds as $tid) {
                $taskInfo = $taskModel->getById($tid);
                $taskInfo['subtasks'] = array_values(array_filter($subtasks, function($s) use ($tid) {
                    return $s['task_id'] == $tid;
                }));
                $tasksWithSubtasks[] = $taskInfo;
            }
        }

        // Sắp xếp: Task 100% done -> cuối mảng (bên phải board)
        usort($tasksWithSubtasks, function($a, $b) {
            $aDone = (isset($a['subtask_count']) && $a['subtask_count'] > 0 && $a['done_count'] == $a['subtask_count']) ? 1 : 0;
            $bDone = (isset($b['subtask_count']) && $b['subtask_count'] > 0 && $b['done_count'] == $b['subtask_count']) ? 1 : 0;
            return $aDone - $bDone;
        });

        // Lấy danh sách nhân viên trong phòng ban (cho modal tạo subtask)
        $userModel = new User($this->db);
        $staffList = [];
        if ($roleId == 1 || $roleId == 2 || $roleId == 4) {
            $staffStmt = $userModel->getAllUsersWithDetails();
            $staffList = $staffStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require_once __DIR__ . '/../views/tasks/index.php';
    }

    // API: Tạo Task mới (hỗ trợ tạo kèm subtasks)
    public function createTask() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền tạo Task!']);
            exit;
        }
        
        $taskModel = new Task($this->db);
        $subtaskModel = new Subtask($this->db);
        $deptId = $_POST['department_id'] ?? $_SESSION['department_id'];
        $title = htmlspecialchars(trim($_POST['title'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $priority = $_POST['priority'] ?? 'Medium';
        $deadline = $_POST['deadline'] ?? null;
        
        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Tiêu đề Task không được để trống!']);
            exit;
        }
        
        $this->db->beginTransaction();
        try {
            $taskId = $taskModel->create($deptId, $_SESSION['user_id'], $title, $description, $priority, $deadline);
            
            if (!$taskId) {
                throw new Exception('Lỗi tạo Task trong database.');
            }

            // Tạo subtasks kèm theo (nếu có)
            $subtaskTitles = $_POST['subtask_title'] ?? [];
            $subtaskDescs = $_POST['subtask_description'] ?? [];
            $subtaskAssignees = $_POST['subtask_assignee'] ?? [];
            $subtaskDeadlines = $_POST['subtask_deadline'] ?? [];
            $subtaskPriorities = $_POST['subtask_priority'] ?? [];

            $notifyUserIds = [];
            if (is_array($subtaskTitles)) {
                for ($i = 0; $i < count($subtaskTitles); $i++) {
                    $stTitle = htmlspecialchars(trim($subtaskTitles[$i] ?? ''));
                    $stDesc = htmlspecialchars(trim($subtaskDescs[$i] ?? ''));
                    $stAssignee = $subtaskAssignees[$i] ?? 0;
                    $stDeadline = $subtaskDeadlines[$i] ?? null;
                    $stPriority = $subtaskPriorities[$i] ?? 'Medium';

                    if (!empty($stTitle) && !empty($stAssignee)) {
                        $subtaskModel->create($taskId, $stAssignee, $stTitle, $stDesc, $stDeadline, $stPriority);
                        if (!in_array($stAssignee, $notifyUserIds)) {
                            $notifyUserIds[] = $stAssignee;
                        }
                    }
                }
            }

            $this->db->commit();

            // Gửi thông báo cho nhân viên được giao việc
            foreach ($notifyUserIds as $uid) {
                NotificationController::pushNotification(
                    $this->db,
                    'task_assigned',
                    $_SESSION['user_id'],
                    "Bạn được giao việc trong Task mới: " . $title,
                    "index.php?action=tasks",
                    [$uid]
                );
            }

            echo json_encode(['success' => true, 'message' => 'Tạo Task thành công.', 'task_id' => $taskId]);
        } catch (Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
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

        // Chặn kéo TỪ cột Done (khóa cứng)
        if ($subtask['status'] == 'Done') {
            echo json_encode(['success' => false, 'message' => 'locked_done']);
            exit;
        }

        // Chặn kéo VÀO cột Done (chỉ Leader duyệt mới được Done)
        if ($status == 'Done') {
            echo json_encode(['success' => false, 'message' => 'locked_done']);
            exit;
        }

        // Xử lý kéo VÀO cột Pending
        if ($status == 'Pending') {
            // Chỉ người được giao mới kéo được
            if ($subtask['assignee_id'] != $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'permission_denied']);
                exit;
            }
            // Kiểm tra minh chứng
            if (!$subtaskModel->hasEvidence($subtaskId)) {
                echo json_encode(['success' => false, 'message' => 'no_evidence']);
                exit;
            }
            // Có minh chứng -> trả confirm để frontend hiện SweetAlert
            // Frontend sẽ gửi lại request với confirm=1
            if (!isset($_POST['confirm']) || $_POST['confirm'] != '1') {
                echo json_encode(['success' => false, 'message' => 'confirm_pending']);
                exit;
            }
            // Đã xác nhận -> chuyển sang Pending + gửi thông báo
            if ($subtaskModel->updateStatus($subtaskId, 'Pending')) {
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
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi DB!']);
            }
            exit;
        }

        // Kéo giữa To Do <-> In Progress: chỉ người được giao
        if ($subtask['assignee_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'permission_denied']);
            exit;
        }

        // Chặn kéo từ Pending về (chỉ Leader reject mới quay lại)
        if ($subtask['status'] == 'Pending') {
            echo json_encode(['success' => false, 'message' => 'locked_pending']);
            exit;
        }

        if ($subtaskModel->updateStatus($subtaskId, $status)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB!']);
        }
        exit;
    }

    // API: Kiểm tra minh chứng của subtask
    public function checkEvidence() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_GET['id'] ?? 0;

        $hasEvidence = $subtaskModel->hasEvidence($subtaskId);
        echo json_encode(['success' => true, 'has_evidence' => $hasEvidence]);
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

    // API: Tạo Subtask mới (hỗ trợ batch)
    public function createSubtask() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền tạo Subtask!']);
            exit;
        }
        
        $subtaskModel = new Subtask($this->db);
        $taskId = $_POST['task_id'] ?? 0;

        // Kiểm tra batch mode (multiple subtasks)
        $titles = $_POST['subtask_title'] ?? null;
        if (is_array($titles)) {
            $assignees = $_POST['subtask_assignee'] ?? [];
            $descs = $_POST['subtask_description'] ?? [];
            $deadlines = $_POST['subtask_deadline'] ?? [];
            $priorities = $_POST['subtask_priority'] ?? [];

            $created = 0;
            $notifyUserIds = [];
            for ($i = 0; $i < count($titles); $i++) {
                $t = htmlspecialchars(trim($titles[$i] ?? ''));
                $a = $assignees[$i] ?? 0;
                $d = htmlspecialchars(trim($descs[$i] ?? ''));
                $dl = $deadlines[$i] ?? null;
                $p = $priorities[$i] ?? 'Medium';
                if (!empty($t) && !empty($a)) {
                    $subtaskModel->create($taskId, $a, $t, $d, $dl, $p);
                    $created++;
                    if (!in_array($a, $notifyUserIds)) {
                        $notifyUserIds[] = $a;
                    }
                }
            }
            // Thông báo
            $taskModel = new Task($this->db);
            $task = $taskModel->getById($taskId);
            foreach ($notifyUserIds as $uid) {
                NotificationController::pushNotification(
                    $this->db,
                    'task_assigned',
                    $_SESSION['user_id'],
                    "Bạn được giao $created việc mới trong Task: " . ($task['title'] ?? ''),
                    "index.php?action=tasks",
                    [$uid]
                );
            }
            $this->syncTaskStatus($taskId);
            echo json_encode(['success' => true, 'message' => "Đã tạo $created công việc con!"]);
            exit;
        }

        // Single subtask mode (backwards compatible)
        $assigneeId = $_POST['assignee_id'] ?? 0;
        $title = htmlspecialchars(trim($_POST['title'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $deadline = $_POST['deadline'] ?? null;
        $priority = $_POST['priority'] ?? 'Medium';
        
        if (empty($title) || empty($taskId) || empty($assigneeId)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đủ thông tin!']);
            exit;
        }
        
        $subtaskId = $subtaskModel->create($taskId, $assigneeId, $title, $description, $deadline, $priority);
        
        if ($subtaskId) {
            // Thông báo nhân viên
            $taskModel = new Task($this->db);
            $task = $taskModel->getById($taskId);
            NotificationController::pushNotification(
                $this->db,
                'task_assigned',
                $_SESSION['user_id'],
                "Bạn được giao việc mới: " . $title . " (Task: " . ($task['title'] ?? '') . ")",
                "index.php?action=tasks",
                [$assigneeId]
            );
            $this->syncTaskStatus($taskId);
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
            $subtask['has_evidence'] = count($subtask['attachments']) > 0;
            echo json_encode(['success' => true, 'data' => $subtask]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy subtask!']);
        }
        exit;
    }

    // API: Lấy chi tiết Task + danh sách subtasks cho modal
    public function getTaskDetail() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $taskModel = new Task($this->db);
        $subtaskModel = new Subtask($this->db);
        $taskId = $_GET['id'] ?? 0;

        $task = $taskModel->getById($taskId);
        if (!$task) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy Task!']);
            exit;
        }

        $subtasks = $subtaskModel->getByTaskId($taskId);
        $task['subtasks'] = $subtasks;
        $task['subtask_count'] = count($subtasks);
        $task['done_count'] = count(array_filter($subtasks, function($s) { return $s['status'] == 'Done'; }));

        echo json_encode(['success' => true, 'data' => $task]);
        exit;
    }
}
