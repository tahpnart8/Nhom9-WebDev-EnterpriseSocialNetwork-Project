<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Subtask.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/NotificationController.php';

class TaskController extends BaseController
{

    // ========== TRANG CHÍNH ==========
    public function index()
    {
        $this->checkAuth();
        $pageTitle = "Quản lý Công việc";

        $subtaskModel = new Subtask($this->db);
        $taskModel = new Task($this->db);

        $roleId = $_SESSION['role_id'];
        $userId = $_SESSION['user_id'];
        $deptId = $_SESSION['department_id'] ?? null;

        require_once __DIR__ . '/../models/Project.php';
        $projectModel = new Project($this->db);

        $projectIdFilter = $_GET['project_id'] ?? null;
        $projects = [];
        $companyId = $_SESSION['company_id'];

        // === Phân quyền: Bảng TIẾN ĐỘ (subtask cá nhân cho staff) ===
        if ($roleId == 1 || $roleId == 4) {
            if (!$projectIdFilter) {
                // CEO view projects
                $projects = $projectModel->getAll($companyId);
                require_once __DIR__ . '/../views/tasks/projects.php';
                return;
            } else {
                $subtasks = $subtaskModel->getByProject($projectIdFilter, $companyId); // Sửa: gọi getByProject
                $tasks = $taskModel->getAll($companyId, $projectIdFilter); // pass project_id
            }
        } elseif ($roleId == 2) {
            // Leader: Always fetch projects for the tab
            $projects = $projectModel->getByDepartment($deptId, $companyId);
            $subtasks = $subtaskModel->getByDepartment($deptId, $companyId, $projectIdFilter); // Sửa: truyền projectIdFilter
            $tasks = $taskModel->getByDepartment($deptId, $companyId, $projectIdFilter);
        } else {
            $subtasks = $subtaskModel->getByAssignee($userId, $companyId, $projectIdFilter); // Sửa: truyền projectIdFilter
            $tasks = [];
        }

        // Kanban 5 cột (bảng tiến độ)
        $columns = [
            'To Do' => [],
            'In Progress' => [],
            'Pending' => [],
            'Done' => [],
            'Overdue' => []
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
            // Staff: Batch lấy tất cả tasks cùng lúc (tránh N+1 loop)
            $taskIds = array_unique(array_column($subtasks, 'task_id'));
            if (!empty($taskIds)) {
                $allTasks = $taskModel->getTasksByIds($taskIds, $companyId);
                // Lấy TẤT CẢ subtasks trong các tasks đó bằng 1 query
                $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
                $stmtAll = $this->db->prepare("SELECT s.*, u.full_name as assignee_name
                    FROM subtasks s JOIN users u ON s.assignee_id = u.id
                    WHERE s.task_id IN ($placeholders) ORDER BY s.created_at ASC");
                $stmtAll->execute(array_values($taskIds));
                $allSubInTasks = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
                // Group subtasks theo task_id trong PHP (in-memory)
                $subtasksByTask = [];
                foreach ($allSubInTasks as $sub) {
                    $subtasksByTask[$sub['task_id']][] = $sub;
                }
                foreach ($allTasks as $taskInfo) {
                    $subs = $subtasksByTask[$taskInfo['id']] ?? [];
                    $taskInfo['subtasks'] = $subs;
                    $taskInfo['subtask_count'] = count($subs);
                    $taskInfo['done_count'] = count(array_filter($subs, function ($s) {
                        return $s['status'] == 'Done';
                    }));
                    $tasksWithSubtasks[] = $taskInfo;
                }
            }
        } else {
            foreach ($tasks as $t) {
                $t['subtasks'] = array_values(array_filter($subtasks, function ($s) use ($t) {
                    return $s['task_id'] == $t['id'];
                }));
                $tasksWithSubtasks[] = $t;
            }
        }

        // Sort: task 100% done → cuối (phải)
        usort($tasksWithSubtasks, function ($a, $b) {
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
            $staffStmt = $userModel->getAllUsersWithDetails($_SESSION['company_id']);
            $staffList = $staffStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require_once __DIR__ . '/../views/tasks/index.php';
    }

    // ========== API: TẠO TASK (validate mạnh) ==========
    public function createTask()
    {
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
        $projectId = !empty($_POST['project_id']) ? $_POST['project_id'] : null;

        // Validate
        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Tiêu đề Task không được để trống!']);
            exit;
        }
        if (empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Mô tả Task không được để trống!']);
            exit;
        }
        if (empty($deadline)) {
            echo json_encode(['success' => false, 'message' => 'Deadline không được để trống!']);
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
        $companyId = $_SESSION['company_id'];

        $this->db->beginTransaction();
        try {
            $taskId = $taskModel->create($deptId, $projectId, $_SESSION['user_id'], $title, $description, $priority, $deadline, $companyId);
            if ($taskId === 'DUPLICATE')
                throw new Exception('Lỗi: Task này đã tồn tại và chưa hoàn thành (Phát hiện trùng lặp).');
            if (!$taskId)
                throw new Exception('Lỗi tạo Task.');

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
                    $stId = $subtaskModel->create($taskId, $stAssignee, $stTitle, $stDesc, $stDeadline, $stPriority);
                    if ($stId === 'DUPLICATE')
                        throw new Exception("Lỗi trùng lặp: Công việc con '$stTitle' đã tồn tại!");
                    if (!in_array($stAssignee, $notifyUserIds))
                        $notifyUserIds[] = $stAssignee;
                }
            }

            $this->db->commit();

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
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ========== API: XÓA TASK (Leader/CEO) ==========
    public function deleteTask()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa Task!']);
            exit;
        }

        $taskModel = new Task($this->db);
        $taskId = $_POST['task_id'] ?? 0;

        if ($taskModel->delete($taskId, $_SESSION['company_id'])) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa Task và tất cả công việc con!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa Task!']);
        }
        exit;
    }

    // ========== API: CẬP NHẬT TASK (Leader/CEO) ==========
    public function apiUpdateTask()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa Task!']);
            exit;
        }

        $taskId = $_POST['task_id'] ?? 0;
        $title = htmlspecialchars(trim($_POST['title'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $priority = $_POST['priority'] ?? 'Medium';
        $deadline = $_POST['deadline'] ?? null;
        $projectId = !empty($_POST['project_id']) ? $_POST['project_id'] : null;

        if (empty($title) || empty($description) || empty($deadline)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
            exit;
        }

        $taskModel = new Task($this->db);
        if ($taskModel->update($taskId, $title, $description, $priority, $deadline, $_SESSION['company_id'], $projectId)) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật Task thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật Task!']);
        }
        exit;
    }

    // ========== API: CẬP NHẬT SUBTASK (Leader/CEO) ==========
    public function apiUpdateSubtask()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa Subtask!']);
            exit;
        }

        $subtaskId = $_POST['subtask_id'] ?? 0;
        $title = htmlspecialchars(trim($_POST['title'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $assigneeId = $_POST['assignee_id'] ?? 0;
        $deadline = $_POST['deadline'] ?? null;
        $priority = $_POST['priority'] ?? 'Medium';

        if (empty($title) || empty($assigneeId) || empty($deadline)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
            exit;
        }

        $subtaskModel = new Subtask($this->db);
        $companyId = $_SESSION['company_id'];
        $oldSubtask = $subtaskModel->getById($subtaskId, $companyId);

        if ($subtaskModel->update($subtaskId, $title, $description, $assigneeId, $deadline, $priority)) {
            // Nếu đổi người thực hiện, thông báo cho người mới
            if ($oldSubtask && $oldSubtask['assignee_id'] != $assigneeId) {
                NotificationController::pushNotification(
                    $this->db,
                    'task_assigned',
                    $_SESSION['user_id'],
                    "Bạn được giao việc mới (thay thế): " . $title,
                    "index.php?action=tasks",
                    [$assigneeId]
                );
            }
            echo json_encode(['success' => true, 'message' => 'Cập nhật Subtask thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật Subtask!']);
        }
        exit;
    }

    // ========== API: GỬI MINH CHỨNG + DUYỆT (có validate) ==========
    public function submitEvidence()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $notes = htmlspecialchars(trim($_POST['notes'] ?? ''));
        $fileUrl = null;
        $companyId = $_SESSION['company_id'];

        $subtask = $subtaskModel->getById($subtaskId, $companyId);
        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy subtask!']);
            return;
        }
        if ($subtask['assignee_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
            exit;
        }

        // Kiểm tra trễ hạn (Overdue check) - Nhân viên/Quản lý được giao phải gia hạn mới được gửi duyệt
        $now = date('Y-m-d H:i:s');
        if (!empty($subtask['deadline']) && $subtask['deadline'] < $now && $subtask['status'] != 'Done') {
            echo json_encode(['success' => false, 'message' => 'Công việc đã trễ hạn! Bạn không thể gửi duyệt, hãy gửi "Yêu cầu gia hạn" tới cấp trên.']);
            exit;
        }

        // Upload file nếu có
        if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
            require_once __DIR__ . '/../models/CloudStorage.php';
            $cloudStorage = new CloudStorage();
            $cloudUrl = $cloudStorage->uploadImage($_FILES['evidence_file']['tmp_name']);
            if ($cloudUrl !== false)
                $fileUrl = $cloudUrl;
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
            $task = $taskModel->getById($subtask['task_id'], $companyId);
            NotificationController::pushNotification(
                $this->db,
                'task_approval',
                $_SESSION['user_id'],
                "Nhân viên " . $_SESSION['full_name'] . " đã gửi duyệt subtask: " . $subtask['title'],
                "index.php?action=tasks&subtask_id=" . $subtaskId,
                [$task['created_by_user_id']],
                $companyId
            );
            echo json_encode(['success' => true, 'message' => 'Đã gửi duyệt công việc!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống!']);
        }
        exit;
    }

    // ========== API: DUYỆT SUBTASK ==========
    public function approveSubtask()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền duyệt!']);
            exit;
        }

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $companyId = $_SESSION['company_id'];
        $subtask = $subtaskModel->getById($subtaskId, $companyId);

        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy!']);
            return;
        }

        // Chặn duyệt subtask trễ hạn
        if (!empty($subtask['deadline']) && $subtask['deadline'] < date('Y-m-d H:i:s') && $subtask['status'] != 'Done') {
            echo json_encode(['success' => false, 'message' => 'Subtask đã trễ hạn! Vui lòng gia hạn trước khi duyệt.']);
            exit;
        }

        if ($subtaskModel->approve($subtaskId)) {
            NotificationController::pushNotification(
                $this->db,
                'task_approved',
                $_SESSION['user_id'],
                "Subtask '" . $subtask['title'] . "' đã được DUYỆT! Vui lòng kéo subtask sang cột Hoàn thành và viết báo cáo AI.",
                "index.php?action=tasks",
                [$subtask['assignee_id']],
                $companyId
            );
            echo json_encode(['success' => true, 'message' => 'Đã duyệt! (Chờ nhân viên viết báo cáo)']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi!']);
        }
        exit;
    }

    // ========== API: TỪ CHỐI SUBTASK ==========
    public function rejectSubtask()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
            exit;
        }

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $reason = htmlspecialchars($_POST['reason'] ?? 'Cần thực hiện lại.');
        $companyId = $_SESSION['company_id'];

        // Chặn từ chối subtask trễ hạn
        $subtask = $subtaskModel->getById($subtaskId, $companyId);
        if (!empty($subtask['deadline']) && $subtask['deadline'] < date('Y-m-d H:i:s') && $subtask['status'] != 'Done') {
            echo json_encode(['success' => false, 'message' => 'Subtask đã trễ hạn! Vui lòng gia hạn thay vì từ chối.']);
            return;
        }

        if ($subtaskModel->reject($subtaskId, $reason)) {
            $subtask = $subtaskModel->getById($subtaskId);
            NotificationController::pushNotification(
                $this->db,
                'task_rejected',
                $_SESSION['user_id'],
                "Subtask '" . $subtask['title'] . "' bị TỪ CHỐI: $reason",
                "index.php?action=tasks&subtask_id=" . $subtaskId,
                [$subtask['assignee_id']],
                $companyId
            );
            $this->syncTaskStatus($subtask['task_id']);
            echo json_encode(['success' => true, 'message' => 'Đã từ chối!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi!']);
        }
        exit;
    }

    // ========== API: XÓA SUBTASK ==========
    public function deleteSubtask()
    {
        $this->checkAuth();
        header('Content-Type: application/json');
        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa!']);
            exit;
        }
        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $companyId = $_SESSION['company_id'];
        $subtask = $subtaskModel->getById($subtaskId, $companyId);
        if ($subtask && $subtaskModel->delete($subtaskId)) {
            $this->syncTaskStatus($subtask['task_id']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi xóa!']);
        }
        exit;
    }

    // ========== API: KÉO THẢ CẬP NHẬT STATUS ==========
    public function updateSubtaskStatus()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $companyId = $_SESSION['company_id'];

        $subtask = $subtaskModel->getById($subtaskId, $companyId);
        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Lỗi dữ liệu!']);
            return;
        }

        // Kiểm tra trễ hạn (Overdue check)
        $now = date('Y-m-d H:i:s');
        if (!empty($subtask['deadline']) && $subtask['deadline'] < $now && $subtask['status'] != 'Done') {
            // Nếu là người thực hiện, chặn lại
            if ($subtask['assignee_id'] == $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'overdue_locked']);
                exit;
            }
        }

        // Khóa Done
        if ($subtask['status'] == 'Done' || $status == 'Done') {
            echo json_encode(['success' => false, 'message' => 'locked_done']);
            exit;
        }
        // Khóa Pending
        if ($subtask['status'] == 'Pending') {
            echo json_encode(['success' => false, 'message' => 'locked_pending']);
            exit;
        }

        // Kéo vào Pending
        if ($status == 'Pending') {
            if ($subtask['assignee_id'] != $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'permission_denied']);
                exit;
            }
            if (!$subtaskModel->hasEvidence($subtaskId)) {
                echo json_encode(['success' => false, 'message' => 'no_evidence']);
                exit;
            }
            if (!isset($_POST['confirm']) || $_POST['confirm'] != '1') {
                echo json_encode(['success' => false, 'message' => 'confirm_pending']);
                exit;
            }
            if ($subtaskModel->updateStatus($subtaskId, 'Pending', $companyId)) {
                $taskModel = new Task($this->db);
                $task = $taskModel->getById($subtask['task_id'], $companyId);
                NotificationController::pushNotification(
                    $this->db,
                    'task_approval',
                    $_SESSION['user_id'],
                    "Nhân viên " . $_SESSION['full_name'] . " đã gửi duyệt subtask: " . $subtask['title'],
                    "index.php?action=tasks&subtask_id=" . $subtaskId,
                    [$task['created_by_user_id']],
                    $companyId
                );
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi DB!']);
            }
            exit;
        }

        // To Do <-> In Progress
        if ($subtask['assignee_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'permission_denied']);
            exit;
        }

        if ($subtaskModel->updateStatus($subtaskId, $status, $companyId)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi DB!']);
        }
        exit;
    }

    // ========== API: CHECK EVIDENCE ==========
    public function checkEvidence()
    {
        $this->checkAuth();
        header('Content-Type: application/json');
        $subtaskModel = new Subtask($this->db);
        echo json_encode(['success' => true, 'has_evidence' => $subtaskModel->hasEvidence($_GET['id'] ?? 0)]);
        exit;
    }

    // ========== SYNC TASK STATUS ==========
    private function syncTaskStatus($task_id)
    {
        $subtaskModel = new Subtask($this->db);
        $taskModel = new Task($this->db);
        $companyId = $_SESSION['company_id'];
        $allSubs = $subtaskModel->getByTaskId($task_id, $companyId);
        if (empty($allSubs))
            return;
        $allDone = true;
        $anyProgress = false;
        foreach ($allSubs as $s) {
            if ($s['status'] != 'Done')
                $allDone = false;
            if ($s['status'] == 'In Progress' || $s['status'] == 'Pending')
                $anyProgress = true;
        }
        if ($allDone)
            $taskModel->updateStatus($task_id, 'Done', $_SESSION['company_id']);
        elseif ($anyProgress)
            $taskModel->updateStatus($task_id, 'In Progress', $_SESSION['company_id']);
    }

    // ========== API: TẠO SUBTASK (batch) ==========
    public function createSubtask()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
            exit;
        }

        $subtaskModel = new Subtask($this->db);
        $taskId = $_POST['task_id'] ?? 0;
        $today = date('Y-m-d');
        $companyId = $_SESSION['company_id'];

        // Batch mode
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
                if ($dl && $dl < $today) {
                    echo json_encode(['success' => false, 'message' => "Deadline subtask '$t' không được là ngày quá khứ!"]);
                    exit;
                }
                if (!empty($t) && !empty($a)) {
                    $res = $subtaskModel->create($taskId, $a, $t, $d, $dl, $p);
                    if ($res === 'DUPLICATE') {
                        echo json_encode(['success' => false, 'message' => "Lỗi trùng lặp: Việc '$t' đã tồn tại!"]);
                        exit;
                    }
                    $created++;
                    if (!in_array($a, $notifyUserIds))
                        $notifyUserIds[] = $a;
                }
            }
            $taskModel = new Task($this->db);
            $task = $taskModel->getById($taskId, $companyId);
            foreach ($notifyUserIds as $uid) {
                NotificationController::pushNotification(
                    $this->db,
                    'task_assigned',
                    $_SESSION['user_id'],
                    "Bạn được giao $created việc mới trong Task: " . ($task['title'] ?? ''),
                    "index.php?action=tasks",
                    [$uid],
                    $companyId
                );
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
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đủ thông tin!']);
            exit;
        }
        if ($deadline && $deadline < $today) {
            echo json_encode(['success' => false, 'message' => 'Deadline không được là ngày quá khứ!']);
            exit;
        }

        $subtaskId = $subtaskModel->create($taskId, $assigneeId, $title, $description, $deadline, $priority);
        if ($subtaskId === 'DUPLICATE') {
            echo json_encode(['success' => false, 'message' => 'Lỗi trùng lặp: Công việc này đã tồn tại và đang hoạt động!']);
            exit;
        }
        if ($subtaskId) {
            $taskModel = new Task($this->db);
            $task = $taskModel->getById($taskId, $companyId);
            NotificationController::pushNotification(
                $this->db,
                'task_assigned',
                $_SESSION['user_id'],
                "Bạn được giao việc mới: $title (Task: " . ($task['title'] ?? '') . ")",
                "index.php?action=tasks",
                [$assigneeId],
                $companyId
            );
            $this->syncTaskStatus($taskId);
            echo json_encode(['success' => true, 'message' => 'Giao việc thành công!', 'subtask_id' => $subtaskId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi Database!']);
        }
        exit;
    }

    // ========== API: SUBTASK DETAIL ==========
    public function getSubtaskDetail()
    {
        $this->checkAuth();
        header('Content-Type: application/json');
        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_GET['id'] ?? 0;
        $subtask = $subtaskModel->getById($subtaskId, $_SESSION['company_id']);
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
    public function getTaskDetail()
    {
        $this->checkAuth();
        header('Content-Type: application/json');
        $taskModel = new Task($this->db);
        $subtaskModel = new Subtask($this->db);
        $taskId = $_GET['id'] ?? 0;
        $companyId = $_SESSION['company_id'];
        $task = $taskModel->getById($taskId, $companyId);
        if (!$task) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy Task!']);
            exit;
        }
        $subtasks = $subtaskModel->getByTaskId($taskId);
        $task['subtasks'] = $subtasks;
        $task['subtask_count'] = count($subtasks);
        $task['done_count'] = count(array_filter($subtasks, function ($s) {
            return $s['status'] == 'Done';
        }));
        echo json_encode(['success' => true, 'data' => $task]);
        exit;
    }

    // ========== API: GIA HẠN SUBTASK TRỄ HẠN (Leader) ==========
    public function extendSubtask()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
            exit;
        }

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $newDeadline = $_POST['new_deadline'] ?? null;
        $companyId = $_SESSION['company_id'];

        if (empty($newDeadline) || $newDeadline < date('Y-m-d')) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ngày gia hạn hợp lệ (không trong quá khứ)!']);
            return;
        }

        $subtask = $subtaskModel->getById($subtaskId, $companyId);
        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy subtask!']);
            exit;
        }

        if ($subtaskModel->extendDeadline($subtaskId, $newDeadline)) {
            NotificationController::pushNotification(
                $this->db,
                'task_extended',
                $_SESSION['user_id'],
                "Subtask '" . $subtask['title'] . "' đã được GIA HẠN đến " . date('d/m/Y', strtotime($newDeadline)) . ". Hãy thực hiện lại!",
                "index.php?action=tasks&subtask_id=" . $subtaskId,
                [$subtask['assignee_id']]
            );
            $this->syncTaskStatus($subtask['task_id']);
            echo json_encode(['success' => true, 'message' => 'Đã gia hạn! Subtask quay về cột Cần làm.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi gia hạn!']);
        }
        exit;
    }

    // ========== API: YÊU CẦU GIA HẠN SUBTASK (Staff) ==========
    public function requestExtension()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskId = $_POST['subtask_id'] ?? 0;
        $targetDate = $_POST['target_date'] ?? null;
        $reason = htmlspecialchars(trim($_POST['reason'] ?? ''));

        if (empty($targetDate) || empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập lý do và ngày mong muốn!']);
            exit;
        }

        $subtaskModel = new Subtask($this->db);
        $subtask = $subtaskModel->getById($subtaskId);
        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy công việc!']);
            exit;
        }

        if ($subtask['assignee_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Bạn không phải là người thực hiện công việc này!']);
            exit;
        }

        if ($subtaskModel->requestExtension($subtaskId, $targetDate, $reason)) {
            $taskModel = new Task($this->db);
            $task = $taskModel->getById($subtask['task_id']);
            $notifyIds = [];
            if ($task['created_by_user_id'])
                $notifyIds[] = $task['created_by_user_id'];

            // Trưởng phòng cũng nhận thông báo
            $stmt = $this->db->prepare("SELECT id FROM users WHERE department_id = ? AND role_id = 2");
            $stmt->execute([$task['department_id']]);
            $leaders = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($leaders as $l) {
                if (!in_array($l, $notifyIds))
                    $notifyIds[] = $l;
            }

            NotificationController::pushNotification(
                $this->db,
                'extension_request',
                $_SESSION['user_id'],
                $_SESSION['full_name'] . " yêu cầu gia hạn Subtask '" . $subtask['title'] . "' đến " . date('d/m/Y', strtotime($targetDate)) . ". Lý do: " . $reason,
                "index.php?action=tasks&subtask_id=" . $subtaskId,
                $notifyIds
            );

            echo json_encode(['success' => true, 'message' => 'Đã gửi yêu cầu gia hạn tới Quản lý/CEO!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi gửi yêu cầu!']);
        }
        exit;
    }

    // ========== API: CHỈ LƯU MINH CHỨNG (không gửi duyệt) ==========
    public function saveEvidence()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskModel = new Subtask($this->db);
        $subtaskId = $_POST['subtask_id'] ?? 0;
        $notes = htmlspecialchars(trim($_POST['notes'] ?? ''));
        $fileUrl = null;

        $subtask = $subtaskModel->getById($subtaskId);
        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy!']);
            exit;
        }
        if ($subtask['assignee_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền!']);
            exit;
        }

        // Upload file
        if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
            require_once __DIR__ . '/../models/CloudStorage.php';
            $cloudStorage = new CloudStorage();
            $cloudUrl = $cloudStorage->uploadImage($_FILES['evidence_file']['tmp_name']);
            if ($cloudUrl !== false)
                $fileUrl = $cloudUrl;
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



    // ========== API: GENERATE REPORT BẰNG AI (LLaMA via Groq) ==========
    public function generateSubtaskReport()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskId = $_POST['subtask_id'] ?? 0;
        $q1 = $_POST['q1'] ?? '';
        $q2 = $_POST['q2'] ?? '';
        $q3 = $_POST['q3'] ?? '';

        $subtaskModel = new Subtask($this->db);
        $subtask = $subtaskModel->getById($subtaskId);

        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy subtask!']);
            exit;
        }

        $apiKey = $this->getEnvVar('GROQ_API_KEY');
        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'message' => 'Chưa cấu hình GROQ_API_KEY!']);
            exit;
        }

        $systemPrompt = "Bạn là nhân viên chuyên nghiệp tại công ty. Dựa vào mô tả công việc và câu trả lời của nhân viên, hãy viết lại thành một bài báo cáo tiến độ thật ngắn gọn, mạch lạc, văn phong chuyên nghiệp để đăng mạng xã hội công ty. CHỈ TRẢ VỀ nội dung bài đăng, không trả lời lan man.";

        $userPrompt = "Tiêu đề công việc: " . $subtask['title'] . "\n"
            . "Mô tả: " . $subtask['description'] . "\n\n"
            . "Câu trả lời của tôi (người thực hiện):\n"
            . "- Cách thực hiện: " . $q1 . "\n"
            . "- Kinh nghiệm rút ra: " . $q2 . "\n"
            . "- Lưu ý lần sau: " . $q3 . "\n\n"
            . "Hãy viết bài báo cáo ngắn gọn gọn nhẹ.";

        require_once __DIR__ . '/../models/GroqAIService.php';
        $groqService = new GroqAIService($apiKey);
        
        $result = $groqService->generate(
            $systemPrompt,
            $userPrompt,
            0.7,
            500,
            30
        );

        if (!$result['success']) {
            echo json_encode(['success' => false, 'message' => $result['error']]);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $result['content']]);
        exit;
    }

    // ========== API: LƯU BÁO CÁO VÀ CHUYỂN SANG DONE, ĐĂNG SOCIAL ==========
    public function saveSubtaskReport()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskId = $_POST['subtask_id'] ?? 0;
        $q1 = $_POST['q1'] ?? '';
        $q2 = $_POST['q2'] ?? '';
        $q3 = $_POST['q3'] ?? '';
        $aiContent = $_POST['ai_content'] ?? '';

        $subtaskModel = new Subtask($this->db);
        $subtask = $subtaskModel->getById($subtaskId);

        if (!$subtask) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy subtask!']);
            exit;
        }

        // Lưu vào task_reports
        $stmt = $this->db->prepare("INSERT INTO task_reports (subtask_id, task_id, content, q1_answer, q2_answer, q3_answer, ai_generated_content) VALUES (:sid, :tid, :content, :q1, :q2, :q3, :ai)");
        $stmt->execute([
            ':sid' => $subtaskId,
            ':tid' => $subtask['task_id'],
            ':content' => $subtask['title'] . ' Report',
            ':q1' => $q1,
            ':q2' => $q2,
            ':q3' => $q3,
            ':ai' => $aiContent
        ]);
        $reportId = $this->db->lastInsertId();

        // Đổi trạng thái subtask sang DONE
        $this->db->prepare("UPDATE subtasks SET status = 'Done' WHERE id = ?")->execute([$subtaskId]);
        $this->syncTaskStatus($subtask['task_id']);

        // Đăng mạng xã hội (vào Department)
        require_once __DIR__ . '/../models/Post.php';
        $postModel = new Post($this->db);
        $aiTag = "<div class='mt-3 border-top pt-2'><small class='text-muted'><i class='bi bi-robot me-1'></i> Hỗ trợ bởi Relioo AI</small></div>";
        $postContentHtml = "<div class='ai-post'><h6 class='fw-bold text-primary mb-2'>🚀 Báo cáo tiến độ: " . htmlspecialchars($subtask['title']) . "</h6>" . nl2br(htmlspecialchars($aiContent)) . $aiTag . "</div>";

        $postId = $postModel->create($_SESSION['user_id'], $_SESSION['department_id'], $postContentHtml, 'Department', $_SESSION['company_id']);
        if ($postId) {
            $this->db->prepare("UPDATE posts SET is_ai_generated = 1, task_report_id = ? WHERE id = ?")->execute([$reportId, $postId]);
        }

        echo json_encode(['success' => true, 'message' => 'Đã hoàn thành công việc và đăng bài báo cáo!']);
        exit;
    }

    // ========== API: GENERATE TASK SUMMARY BẰNG AI (LLaMA via Groq) ==========
    public function generateTaskSummary()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
            exit;
        }

        $taskId = $_POST['task_id'] ?? 0;

        $taskModel = new Task($this->db);
        $task = $taskModel->getById($taskId);

        if (!$task) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy task!']);
            exit;
        }

        // Lấy tất cả nội dung AI content từ các subtasks
        $stmt = $this->db->prepare("SELECT ai_generated_content FROM task_reports WHERE task_id = ? AND ai_generated_content IS NOT NULL");
        $stmt->execute([$taskId]);
        $reports = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($reports)) {
            echo json_encode(['success' => false, 'message' => 'Không có dữ liệu báo cáo nào từ các công việc con để tổng hợp!']);
            exit;
        }

        $context = implode("\n\n---\n\n", $reports);

        $apiKey = $this->getEnvVar('GROQ_API_KEY');
        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'message' => 'Chưa cấu hình GROQ_API_KEY!']);
            exit;
        }

        $systemPrompt = "Bạn là Trưởng phòng. Dưới đây là các báo cáo chi tiết từ nhân viên về từng hạng mục của dự án. Hãy tổng hợp thành một bài đăng tổng kết dự án dài không quá 1000 chữ. Yêu cầu: Nêu bật kết quả, biểu dương team, văn phong truyền cảm hứng để đăng lên mạng xã hội nội bộ.";
        $userPrompt = "Tên dự án: " . $task['title'] . "\n\nDữ liệu báo cáo chi tiết:\n" . $context;

        require_once __DIR__ . '/../models/GroqAIService.php';
        $groqService = new GroqAIService($apiKey);
        
        $result = $groqService->generate(
            $systemPrompt,
            $userPrompt,
            0.7,
            1500,
            60
        );

        if (!$result['success']) {
            echo json_encode(['success' => false, 'message' => $result['error']]);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $result['content']]);
        exit;
    }

    public function saveTaskSummary()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2 && $_SESSION['role_id'] != 4) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
            exit;
        }

        $taskId = $_POST['task_id'] ?? 0;
        $aiContent = $_POST['ai_content'] ?? '';

        $taskModel = new Task($this->db);
        $task = $taskModel->getById($taskId);
        if (!$task) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy task!']);
            exit;
        }

        // Lưu bản báo cáo của Project vào bảng task_reports (ko có subtaskId)
        $stmt = $this->db->prepare("INSERT INTO task_reports (subtask_id, task_id, content, ai_generated_content) VALUES (NULL, :tid, :content, :ai)");
        $stmt->execute([
            ':tid' => $taskId,
            ':content' => $task['title'] . ' Tổng kết (AI)',
            ':ai' => $aiContent
        ]);
        $reportId = $this->db->lastInsertId();

        require_once __DIR__ . '/../models/Post.php';
        $postModel = new Post($this->db);
        $aiTag = "<div class='mt-3 border-top pt-2'><small class='text-muted'><i class='bi bi-robot me-1'></i> Hỗ trợ bởi Relioo AI</small></div>";
        $postContentHtml = "<div class='ai-post'><h5 class='fw-bold text-success mb-2'>🏆 Tổng kết dự án: " . htmlspecialchars($task['title']) . "</h5>" . nl2br(htmlspecialchars($aiContent)) . $aiTag . "</div>";

        // Đăng vào Department
        $postId1 = $postModel->create($_SESSION['user_id'], $_SESSION['department_id'], $postContentHtml, 'Department', $_SESSION['company_id']);
        if ($postId1) {
            $this->db->prepare("UPDATE posts SET is_ai_generated = 1, task_report_id = ? WHERE id = ?")->execute([$reportId, $postId1]);
        }

        echo json_encode(['success' => true, 'message' => 'Đã lưu và đăng tải tổng kết dự án (Public & Department)!']);
        exit;
    }
    // ========== API: FETCH URGENT SUBTASKS (Right Sidebar) ==========
    public function fetchUrgentSubtasks()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        $subtaskModel = new Subtask($this->db);
        $urgentSubtasks = $subtaskModel->getUrgentSubtasks($_SESSION['user_id'], $_SESSION['role_id'], $_SESSION['department_id'] ?? null);

        $result = [];
        $now = time();
        foreach ($urgentSubtasks as $st) {
            $progress = 0;
            if ($st['parent_total_subtasks'] > 0) {
                $progress = round(($st['parent_done_subtasks'] / $st['parent_total_subtasks']) * 100);
            }

            $deadlineTime = !empty($st['deadline']) && $st['deadline'] != '0000-00-00 00:00:00' ? strtotime($st['deadline']) : false;
            if ($deadlineTime === false) {
                $secondsLeft = PHP_INT_MAX; // Không có hạn
            } else {
                $secondsLeft = $deadlineTime - $now;
            }

            // Computed badge color base on seconds left (user requested color logic: computed by time)
            if ($secondsLeft < 86400) { // < 24h or negative
                $colorPrefix = 'danger'; // red
            } elseif ($secondsLeft < 259200) { // < 72h (3 days)
                $colorPrefix = 'warning'; // yellow
            } else {
                $colorPrefix = 'success'; // green
            }

            $result[] = [
                'id' => $st['id'],
                'subtask_title' => $st['title'],
                'task_title' => $st['parent_task_title'],
                'priority_label' => $st['priority'], // Native priority from DB
                'badge_color' => $colorPrefix,
                'seconds_left' => $secondsLeft,
                'progress_percent' => $progress
            ];
        }

        echo json_encode(['success' => true, 'data' => $result]);
        exit;
    }

    // ========== API: TẠO NHÁP BÁO CÁO TASK (Trưởng phòng xem trước) ==========
    public function apiGenerateTaskReportForCEO()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 2) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
            exit;
        }

        $taskId = $_POST['task_id'] ?? 0;
        $taskModel = new Task($this->db);
        $task = $taskModel->getById($taskId);

        if (!$task) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy dữ liệu Task!']);
            exit;
        }

        // Thu thập dữ liệu từ các subtasks
        $subtaskModel = new Subtask($this->db);
        $subtasks = $subtaskModel->getByTaskId($taskId);
        
        // Lấy thêm các báo cáo chi tiết/minh chứng của nhân viên (nếu có)
        $stmt = $this->db->prepare("SELECT content FROM task_reports WHERE task_id = ? AND subtask_id IS NOT NULL");
        $stmt->execute([$taskId]);
        $subtaskNotes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $context = "Tiêu đề Task: " . $task['title'] . "\nMô tả: " . $task['description'] . "\n\nDanh sách công việc con đã hoàn thành:\n";
        foreach ($subtasks as $s) {
            $context .= "- " . $s['title'] . " (Nhân viên: " . $s['assignee_name'] . ")\n";
        }
        if (!empty($subtaskNotes)) {
            $context .= "\nChi tiết báo cáo từ nhân viên:\n" . implode("\n", $subtaskNotes);
        }

        $apiKey = $this->getEnvVar('GROQ_API_KEY');
        if (empty($apiKey)) {
            echo json_encode(['success' => false, 'message' => 'Chưa cấu hình GROQ_API_KEY!']);
            exit;
        }

        $systemPrompt = "Bạn là một Trưởng phòng chuyên nghiệp, có năng lực lãnh đạo và hành văn sắc sảo. Nhiệm vụ của bạn là viết một BÁO CÁO TỔNG KẾT CÔNG VIỆC cho toàn thể nhân viên cấp dưới và ban lãnh đạo (CEO) cùng nắm bắt tình hình.
Yêu cầu văn phong:
- Đây là một BÁO CÁO TỔNG KẾT CHUNG (không phải email gửi riêng cho CEO, tránh dùng các từ như 'Kính gửi sếp', 'Em báo cáo', 'Mong sếp duyệt').
- Trình bày mạch lạc, nêu bật những thành tựu và nỗ lực của các nhân viên được nêu tên.
- Văn phong trang trọng, đầy đủ, mạch lạc nhưng vẫn mang tính khích lệ team.
- Chỉ trả về nội dung báo cáo chính thức (raw text), không thêm lời chào của AI.";

        require_once __DIR__ . '/../models/GroqAIService.php';
        $groqService = new GroqAIService($apiKey);
        
        $result = $groqService->generate(
            $systemPrompt,
            $context,
            0.7
        );

        if (!$result['success']) {
            echo json_encode(['success' => false, 'message' => $result['error']]);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $result['content']]);
        exit;
    }

    // ========== API: GỬI DUYỆT TASK LÊN CEO (Đã qua bước edit) ==========
    public function submitTaskToCEO()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 2) {
            echo json_encode(['success' => false, 'message' => 'Chỉ Trưởng phòng mới có thể gửi duyệt Task!']);
            exit;
        }

        $taskId = $_POST['task_id'] ?? 0;
        $aiContent = $_POST['ai_content'] ?? ''; // Nội dung báo cáo đã chỉnh sửa

        if (empty($aiContent)) {
            echo json_encode(['success' => false, 'message' => 'Nội dung báo cáo không được để trống!']);
            exit;
        }

        $taskModel = new Task($this->db);
        $task = $taskModel->getById($taskId);

        if (!$task) {
            echo json_encode(['success' => false, 'message' => 'Lỗi dữ liệu!']);
            exit;
        }

        // Kiểm tra đạt 100%
        if ($task['subtask_count'] == 0 || $task['subtask_count'] != $task['done_count']) {
            echo json_encode(['success' => false, 'message' => 'Công việc chưa hoàn thành 100%.']);
            exit;
        }

        if ($task['approval_status'] == 'Submitted' || $task['approval_status'] == 'Approved') {
            echo json_encode(['success' => false, 'message' => 'Công việc đã được gửi duyệt hoặc đã duyệt.']);
            exit;
        }

        // Tạo bài bài báo cáo chính thức
        require_once __DIR__ . '/../models/Post.php';
        $postModel = new Post($this->db);
        $aiTag = "<div class='mt-3 border-top pt-2'><small class='text-muted'><i class='bi bi-robot me-1'></i> Hỗ trợ bởi Relioo AI</small></div>";
        $postContentHtml = "<div class='ai-post'><h6 class='fw-bold text-primary mb-2'><i class='bi bi-file-earmark-check'></i> BÁO CÁO CÔNG VIỆC: " . htmlspecialchars($task['title']) . "</h6>" . nl2br(htmlspecialchars($aiContent)) . $aiTag . "</div>";
        
        // Trưởng phòng hoàn thành task thì bài đăng CHỈ đăng lên kênh phòng ban (Department)
        $postId = $postModel->create($_SESSION['user_id'], $_SESSION['department_id'], $postContentHtml, 'Department', $_SESSION['company_id']);

        // Cập nhật trạng thái Task
        $taskModel->updateApprovalStatus($taskId, 'Submitted', $postId);

        // Thông báo CEO (Id role 1)
        NotificationController::pushNotification($this->db, 'task_approval_ceo', $_SESSION['user_id'], "Trưởng phòng {$_SESSION['full_name']} vừa gửi duyệt Task: {$task['title']}", "index.php?action=tasks", [1]); 

        echo json_encode(['success' => true, 'message' => 'Đã gửi duyệt Task và xuất bản báo cáo thành công!']);
        exit;
    }

    // ========== API: CEO DUYỆT HOẶC TỪ CHỐI TASK ==========
    public function ceoApproveTask()
    {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SESSION['role_id'] != 1) { // Chỉ CEO
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện!']);
            exit;
        }

        $taskId = $_POST['task_id'] ?? 0;
        $status = $_POST['status'] ?? ''; // 'Approved' hoặc 'Rejected'
        $taskModel = new Task($this->db);
        $task = $taskModel->getById($taskId);

        if (!$task || !in_array($status, ['Approved', 'Rejected'])) {
             echo json_encode(['success' => false, 'message' => 'Lỗi dữ liệu!']);
             exit;
        }

        if ($taskModel->updateApprovalStatus($taskId, $status)) {
             $msg = $status == 'Approved' ? "Task '{$task['title']}' đã được CEO DUYỆT!" : "Task '{$task['title']}' BỊ TỪ CHỐI bởi CEO.";
             NotificationController::pushNotification($this->db, 'task_approval_ceo', $_SESSION['user_id'], $msg, "index.php?action=tasks", [$task['created_by_user_id']]);
             echo json_encode(['success' => true, 'message' => 'Đã xử lý quyết định.']);
        } else {
             echo json_encode(['success' => false, 'message' => 'Lỗi DB.']);
        }
        exit;
    }

    public function apiSearchTasks()
    {
        header('Content-Type: application/json');
        $keyword = trim($_GET['q'] ?? '');
        if (empty($keyword)) {
            echo json_encode([]);
            exit;
        }

        $taskModel = new Task($this->db);
        $results = $taskModel->search($keyword, $_SESSION['role_id'], $_SESSION['department_id'] ?? null, $_SESSION['user_id']);
        echo json_encode($results);
        exit;
    }
}
