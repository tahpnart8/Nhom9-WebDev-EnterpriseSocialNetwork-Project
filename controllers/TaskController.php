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

    private function checkAuth() {
        if(!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
    }

    // ========== TRANG CHÍNH ==========
    public function index() {
        $this->checkAuth();
        $pageTitle = "Quản lý Công việc";

        $subtaskModel = new Subtask($this->db);
        $taskModel = new Task($this->db);

        $roleId = $_SESSION['role_id'];
        $userId = $_SESSION['user_id'];
        $deptId = $_SESSION['department_id'] ?? null;

        // === Phân quyền: Bảng TIẾN ĐỘ (subtask cá nhân cho staff) ===
        if ($roleId == 1 || $roleId == 4) {
            $subtasks = $subtaskModel->getAll();
            $tasks = $taskModel->getAll();
        } elseif ($roleId == 2) {
            $subtasks = $subtaskModel->getByDepartment($deptId);
            $tasks = $taskModel->getByDepartment($deptId);
        } else {
            $subtasks = $subtaskModel->getByAssignee($userId);
            $tasks = [];
        }

        // Kanban 5 cột (bảng tiến độ)
        $columns = [
            'To Do' => [], 'In Progress' => [], 'Pending' => [],
            'Done' => [], 'Overdue' => []
        ];
        $now = date('Y-m-d H:i:s');
        foreach ($subtasks as $st) {
            if (!empty($st['deadline']) && $st['deadline'] < $now && $st['status'] != 'Done') {
                $columns['Overdue'][] = $st;
            } else {
                $columns[$st['status']][] = $st;
            }
        }

        // === Bảng THEO TASK: Staff cũng thấy TẤT CẢ subtask trong Task ===
        $tasksWithSubtasks = [];
        if ($roleId == 3) {
            // Staff: tìm các Task chứa subtask của mình, rồi lấy TOÀN BỘ subtask trong Task
            $taskIds = array_unique(array_column($subtasks, 'task_id'));
            foreach ($taskIds as $tid) {
                $taskInfo = $taskModel->getById($tid);
                if (!$taskInfo) continue;
                $allSubtasksInTask = $subtaskModel->getByTaskId($tid);
                $taskInfo['subtasks'] = $allSubtasksInTask;
                $taskInfo['subtask_count'] = count($allSubtasksInTask);
                $taskInfo['done_count'] = count(array_filter($allSubtasksInTask, function($s) { return $s['status'] == 'Done'; }));
                $tasksWithSubtasks[] = $taskInfo;
            }
        } else {
            foreach ($tasks as $t) {
                $t['subtasks'] = array_values(array_filter($subtasks, function($s) use ($t) {
                    return $s['task_id'] == $t['id'];
                }));
                $tasksWithSubtasks[] = $t;
            }
        }

        // Sort: task 100% done → cuối (phải)
        usort($tasksWithSubtasks, function($a, $b) {
            $sc_a = $a['subtask_count'] ?? count($a['subtasks'] ?? []);
            $dc_a = $a['done_count'] ?? 0;
            $sc_b = $b['subtask_count'] ?? count($b['subtasks'] ?? []);
            $dc_b = $b['done_count'] ?? 0;
            $aDone = ($sc_a > 0 && $dc_a == $sc_a) ? 1 : 0;
            $bDone = ($sc_b > 0 && $dc_b == $sc_b) ? 1 : 0;
            return $aDone - $bDone;
        });

        // Staff list cho modal
        $userModel = new User($this->db);
        $staffList = [];
        if ($roleId == 1 || $roleId == 2 || $roleId == 4) {
            $staffStmt = $userModel->getAllUsersWithDetails();
            $staffList = $staffStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require_once __DIR__ . '/../views/tasks/index.php';
    }

    // ========== API: TẠO TASK (validate mạnh) ==========
    public function createTask() {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền tạo Task!']);
            exit;
        }

        $title = htmlspecialchars(trim($_POST['title'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $priority = $_POST['priority'] ?? 'Medium';
        $deadline = $_POST['deadline'] ?? null;
        $deptId = $_POST['department_id'] ?? $_SESSION['department_id'];

        // Validate
        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Tiêu đề Task không được để trống!']);
            exit;
        }
        if (empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Mô tả Task không được để trống!']);
            exit;
        }
        if ($deadline && $deadline < date('Y-m-d')) {
            echo json_encode(['success' => false, 'message' => 'Hạn chót không được là ngày trong quá khứ!']);
            exit;
        }

        // Validate ít nhất 1 subtask
        $subtaskTitles = $_POST['subtask_title'] ?? [];
        $subtaskAssignees = $_POST['subtask_assignee'] ?? [];
        $hasValidSubtask = false;
        if (is_array($subtaskTitles)) {
            for ($i = 0; $i < count($subtaskTitles); $i++) {
                if (!empty(trim($subtaskTitles[$i] ?? '')) && !empty($subtaskAssignees[$i] ?? '')) {
                    $hasValidSubtask = true;
                    break;
                }
            }
        }
        if (!$hasValidSubtask) {
            echo json_encode(['success' => false, 'message' => 'Cần ít nhất 1 công việc con (subtask) hợp lệ!']);
            exit;
        }

        $taskModel = new Task($this->db);
        $subtaskModel = new Subtask($this->db);

        $this->db->beginTransaction();
        try {
            $taskId = $taskModel->create($deptId, $_SESSION['user_id'], $title, $description, $priority, $deadline);
            if (!$taskId) throw new Exception('Lỗi tạo Task.');

            $subtaskDescs = $_POST['subtask_description'] ?? [];
            $subtaskDeadlines = $_POST['subtask_deadline'] ?? [];
            $subtaskPriorities = $_POST['subtask_priority'] ?? [];
            $notifyUserIds = [];

            for ($i = 0; $i < count($subtaskTitles); $i++) {
                $stTitle = htmlspecialchars(trim($subtaskTitles[$i] ?? ''));
                $stAssignee = $subtaskAssignees[$i] ?? 0;
                $stDesc = htmlspecialchars(trim($subtaskDescs[$i] ?? ''));
                $stDeadline = $subtaskDeadlines[$i] ?? null;
                $stPriority = $subtaskPriorities[$i] ?? 'Medium';

                if ($stDeadline && $stDeadline < date('Y-m-d')) {
                    throw new Exception("Deadline subtask '$stTitle' không được là ngày quá khứ!");
                }
                if (!empty($stTitle) && !empty($stAssignee)) {
                    $subtaskModel->create($taskId, $stAssignee, $stTitle, $stDesc, $stDeadline, $stPriority);
                    if (!in_array($stAssignee, $notifyUserIds)) $notifyUserIds[] = $stAssignee;
                }
            }

            $this->db->commit();

            foreach ($notifyUserIds as $uid) {
                NotificationController::pushNotification($this->db, 'task_assigned', $_SESSION['user_id'],
                    "Bạn được giao việc trong Task mới: " . $title,
                    "index.php?action=tasks", [$uid]);
            }

            echo json_encode(['success' => true, 'message' => 'Tạo Task thành công.', 'task_id' => $taskId]);
        } catch (Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ========== API: XÓA TASK (Leader/CEO) ==========
    public function deleteTask() {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa Task!']);
            exit;
        }

        $taskModel = new Task($this->db);
        $taskId = $_POST['task_id'] ?? 0;

        if ($taskModel->delete($taskId)) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa Task và tất cả công việc con!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa Task!']);
        }
        exit;
    }

    // ========== API: GỬI MINH CHỨNG + DUYỆT (có validate) ==========
    public function submitEvidence() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $notes = htmlspecialchars(trim($_POST['notes'] ?? ''));
        $fileUrl = null;

        $subtask = $subtaskModel->getById($subtaskId);
        if (!$subtask) { echo json_encode(['success' => false, 'message' => 'Không tìm thấy subtask!']); exit; }
        if ($subtask['assignee_id'] != $_SESSION['user_id']) { echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']); exit; }

        // Upload file nếu có
        if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
            require_once __DIR__ . '/../models/CloudStorage.php';
            $cloudStorage = new CloudStorage();
            $cloudUrl = $cloudStorage->uploadImage($_FILES['evidence_file']['tmp_name']);
            if ($cloudUrl !== false) $fileUrl = $cloudUrl;
        }

        // Validate: phải có ÍT NHẤT notes hoặc file MỚI, HOẶC đã có evidence cũ
        $hasNewEvidence = (!empty($notes) || $fileUrl !== null);
        $hasOldEvidence = $subtaskModel->hasEvidence($subtaskId);

        if (!$hasNewEvidence && !$hasOldEvidence) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập ghi chú hoặc đính kèm file minh chứng trước khi gửi duyệt!']);
            exit;
        }

        if ($subtaskModel->submitEvidence($subtaskId, $notes, $fileUrl)) {
            $taskModel = new Task($this->db);
            $task = $taskModel->getById($subtask['task_id']);
            NotificationController::pushNotification($this->db, 'task_approval', $_SESSION['user_id'],
                "Nhân viên " . $_SESSION['full_name'] . " đã gửi duyệt subtask: " . $subtask['title'],
                "index.php?action=tasks&subtask_id=" . $subtaskId,
                [$task['created_by_user_id']]);
            echo json_encode(['success' => true, 'message' => 'Đã gửi duyệt công việc!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống!']);
        }
        exit;
    }

    // ========== API: DUYỆT SUBTASK ==========
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

        if (!$subtask) { echo json_encode(['success' => false, 'message' => 'Không tìm thấy!']); exit; }

        // Chặn duyệt subtask trễ hạn
        if (!empty($subtask['deadline']) && $subtask['deadline'] < date('Y-m-d H:i:s') && $subtask['status'] != 'Done') {
            echo json_encode(['success' => false, 'message' => 'Subtask đã trễ hạn! Vui lòng gia hạn trước khi duyệt.']);
            exit;
        }

        if ($subtaskModel->approve($subtaskId)) {
            NotificationController::pushNotification($this->db, 'task_approved', $_SESSION['user_id'],
                "Subtask '" . $subtask['title'] . "' đã được DUYỆT và Hoàn thành!",
                "index.php?action=tasks&subtask_id=" . $subtaskId,
                [$subtask['assignee_id']]);
            $this->syncTaskStatus($subtask['task_id']);
            echo json_encode(['success' => true, 'message' => 'Đã duyệt!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi!']);
        }
        exit;
    }

    // ========== API: TỪ CHỐI SUBTASK ==========
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

        // Chặn từ chối subtask trễ hạn
        $subtask = $subtaskModel->getById($subtaskId);
        if (!empty($subtask['deadline']) && $subtask['deadline'] < date('Y-m-d H:i:s') && $subtask['status'] != 'Done') {
            echo json_encode(['success' => false, 'message' => 'Subtask đã trễ hạn! Vui lòng gia hạn thay vì từ chối.']);
            exit;
        }

        if ($subtaskModel->reject($subtaskId, $reason)) {
            $subtask = $subtaskModel->getById($subtaskId);
            NotificationController::pushNotification($this->db, 'task_rejected', $_SESSION['user_id'],
                "Subtask '" . $subtask['title'] . "' bị TỪ CHỐI: $reason",
                "index.php?action=tasks&subtask_id=" . $subtaskId,
                [$subtask['assignee_id']]);
            $this->syncTaskStatus($subtask['task_id']);
            echo json_encode(['success' => true, 'message' => 'Đã từ chối!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi!']);
        }
        exit;
    }

    // ========== API: XÓA SUBTASK ==========
    public function deleteSubtask() {
        $this->checkAuth();
        header('Content-Type: application/json');
        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa!']); exit;
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

    // ========== API: KÉO THẢ CẬP NHẬT STATUS ==========
    public function updateSubtaskStatus() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $status = $_POST['status'] ?? '';

        $subtask = $subtaskModel->getById($subtaskId);
        if (!$subtask) { echo json_encode(['success' => false, 'message' => 'Lỗi dữ liệu!']); exit; }

        // Khóa Done
        if ($subtask['status'] == 'Done' || $status == 'Done') {
            echo json_encode(['success' => false, 'message' => 'locked_done']); exit;
        }
        // Khóa Pending
        if ($subtask['status'] == 'Pending') {
            echo json_encode(['success' => false, 'message' => 'locked_pending']); exit;
        }

        // Kéo vào Pending
        if ($status == 'Pending') {
            if ($subtask['assignee_id'] != $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'permission_denied']); exit;
            }
            if (!$subtaskModel->hasEvidence($subtaskId)) {
                echo json_encode(['success' => false, 'message' => 'no_evidence']); exit;
            }
            if (!isset($_POST['confirm']) || $_POST['confirm'] != '1') {
                echo json_encode(['success' => false, 'message' => 'confirm_pending']); exit;
            }
            if ($subtaskModel->updateStatus($subtaskId, 'Pending')) {
                $taskModel = new Task($this->db);
                $task = $taskModel->getById($subtask['task_id']);
                NotificationController::pushNotification($this->db, 'task_approval', $_SESSION['user_id'],
                    "Nhân viên " . $_SESSION['full_name'] . " đã gửi duyệt subtask: " . $subtask['title'],
                    "index.php?action=tasks&subtask_id=" . $subtaskId,
                    [$task['created_by_user_id']]);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi DB!']);
            }
            exit;
        }

        // To Do <-> In Progress
        if ($subtask['assignee_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'permission_denied']); exit;
        }

        if ($subtaskModel->updateStatus($subtaskId, $status)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB!']);
        }
        exit;
    }

    // ========== API: CHECK EVIDENCE ==========
    public function checkEvidence() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $subtaskModel = new Subtask($this->db);
        echo json_encode(['success' => true, 'has_evidence' => $subtaskModel->hasEvidence($_GET['id'] ?? 0)]);
        exit;
    }

    // ========== SYNC TASK STATUS ==========
    private function syncTaskStatus($task_id) {
        $subtaskModel = new Subtask($this->db);
        $taskModel = new Task($this->db);
        $allSubs = $subtaskModel->getByTaskId($task_id);
        if (empty($allSubs)) return;
        $allDone = true; $anyProgress = false;
        foreach ($allSubs as $s) {
            if ($s['status'] != 'Done') $allDone = false;
            if ($s['status'] == 'In Progress' || $s['status'] == 'Pending') $anyProgress = true;
        }
        if ($allDone) $taskModel->updateStatus($task_id, 'Done');
        elseif ($anyProgress) $taskModel->updateStatus($task_id, 'In Progress');
    }

    // ========== API: TẠO SUBTASK (batch) ==========
    public function createSubtask() {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']); exit;
        }

        $subtaskModel = new Subtask($this->db);
        $taskId = $_POST['task_id'] ?? 0;
        $today = date('Y-m-d');

        // Batch mode
        $titles = $_POST['subtask_title'] ?? null;
        if (is_array($titles)) {
            $assignees = $_POST['subtask_assignee'] ?? [];
            $descs = $_POST['subtask_description'] ?? [];
            $deadlines = $_POST['subtask_deadline'] ?? [];
            $priorities = $_POST['subtask_priority'] ?? [];

            $created = 0; $notifyUserIds = [];
            for ($i = 0; $i < count($titles); $i++) {
                $t = htmlspecialchars(trim($titles[$i] ?? ''));
                $a = $assignees[$i] ?? 0;
                $d = htmlspecialchars(trim($descs[$i] ?? ''));
                $dl = $deadlines[$i] ?? null;
                $p = $priorities[$i] ?? 'Medium';
                if ($dl && $dl < $today) {
                    echo json_encode(['success' => false, 'message' => "Deadline subtask '$t' không được là ngày quá khứ!"]);
                    exit;
                }
                if (!empty($t) && !empty($a)) {
                    $subtaskModel->create($taskId, $a, $t, $d, $dl, $p);
                    $created++;
                    if (!in_array($a, $notifyUserIds)) $notifyUserIds[] = $a;
                }
            }
            $taskModel = new Task($this->db);
            $task = $taskModel->getById($taskId);
            foreach ($notifyUserIds as $uid) {
                NotificationController::pushNotification($this->db, 'task_assigned', $_SESSION['user_id'],
                    "Bạn được giao $created việc mới trong Task: " . ($task['title'] ?? ''),
                    "index.php?action=tasks", [$uid]);
            }
            $this->syncTaskStatus($taskId);
            echo json_encode(['success' => true, 'message' => "Đã tạo $created công việc con!"]);
            exit;
        }

        // Single mode
        $assigneeId = $_POST['assignee_id'] ?? 0;
        $title = htmlspecialchars(trim($_POST['title'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $deadline = $_POST['deadline'] ?? null;
        $priority = $_POST['priority'] ?? 'Medium';

        if (empty($title) || empty($taskId) || empty($assigneeId)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đủ thông tin!']); exit;
        }
        if ($deadline && $deadline < $today) {
            echo json_encode(['success' => false, 'message' => 'Deadline không được là ngày quá khứ!']); exit;
        }

        $subtaskId = $subtaskModel->create($taskId, $assigneeId, $title, $description, $deadline, $priority);
        if ($subtaskId) {
            $taskModel = new Task($this->db);
            $task = $taskModel->getById($taskId);
            NotificationController::pushNotification($this->db, 'task_assigned', $_SESSION['user_id'],
                "Bạn được giao việc mới: $title (Task: " . ($task['title'] ?? '') . ")",
                "index.php?action=tasks", [$assigneeId]);
            $this->syncTaskStatus($taskId);
            echo json_encode(['success' => true, 'message' => 'Giao việc thành công!', 'subtask_id' => $subtaskId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi Database!']);
        }
        exit;
    }

    // ========== API: SUBTASK DETAIL ==========
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

    // ========== API: TASK DETAIL ==========
    public function getTaskDetail() {
        $this->checkAuth();
        header('Content-Type: application/json');
        $taskModel = new Task($this->db);
        $subtaskModel = new Subtask($this->db);
        $taskId = $_GET['id'] ?? 0;
        $task = $taskModel->getById($taskId);
        if (!$task) { echo json_encode(['success' => false, 'message' => 'Không tìm thấy Task!']); exit; }
        $subtasks = $subtaskModel->getByTaskId($taskId);
        $task['subtasks'] = $subtasks;
        $task['subtask_count'] = count($subtasks);
        $task['done_count'] = count(array_filter($subtasks, function($s) { return $s['status'] == 'Done'; }));
        echo json_encode(['success' => true, 'data' => $task]);
        exit;
    }

    // ========== API: GIA HẠN SUBTASK TRỄ HẠN (Leader) ==========
    public function extendSubtask() {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
            exit;
        }

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $newDeadline = $_POST['new_deadline'] ?? null;

        if (empty($newDeadline) || $newDeadline < date('Y-m-d')) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ngày gia hạn hợp lệ (không trong quá khứ)!']);
            exit;
        }

        $subtask = $subtaskModel->getById($subtaskId);
        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy subtask!']);
            exit;
        }

        if ($subtaskModel->extendDeadline($subtaskId, $newDeadline)) {
            NotificationController::pushNotification($this->db, 'task_extended', $_SESSION['user_id'],
                "Subtask '" . $subtask['title'] . "' đã được GIA HẠN đến " . date('d/m/Y', strtotime($newDeadline)) . ". Hãy thực hiện lại!",
                "index.php?action=tasks&subtask_id=" . $subtaskId,
                [$subtask['assignee_id']]);
            $this->syncTaskStatus($subtask['task_id']);
            echo json_encode(['success' => true, 'message' => 'Đã gia hạn! Subtask quay về cột Cần làm.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi gia hạn!']);
        }
        exit;
    }

    // ========== API: CHỈ LƯU MINH CHỨNG (không gửi duyệt) ==========
    public function saveEvidence() {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $notes = htmlspecialchars(trim($_POST['notes'] ?? ''));
        $fileUrl = null;

        $subtask = $subtaskModel->getById($subtaskId);
        if (!$subtask) { echo json_encode(['success' => false, 'message' => 'Không tìm thấy!']); exit; }
        if ($subtask['assignee_id'] != $_SESSION['user_id']) { echo json_encode(['success' => false, 'message' => 'Không có quyền!']); exit; }

        // Upload file
        if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
            require_once __DIR__ . '/../models/CloudStorage.php';
            $cloudStorage = new CloudStorage();
            $cloudUrl = $cloudStorage->uploadImage($_FILES['evidence_file']['tmp_name']);
            if ($cloudUrl !== false) $fileUrl = $cloudUrl;
        }

        if (empty($notes) && $fileUrl === null) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập ghi chú hoặc đính kèm file!']);
            exit;
        }

        if ($subtaskModel->saveEvidenceOnly($subtaskId, $notes, $fileUrl)) {
            echo json_encode(['success' => true, 'message' => 'Đã lưu minh chứng! Bạn có thể gửi duyệt sau.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi lưu!']);
        }
        exit;
    }
}
