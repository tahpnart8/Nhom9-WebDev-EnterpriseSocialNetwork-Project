<?php
class Subtask {
    private $conn;
    public $table_name = "subtasks";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy subtask theo Task ID (cho Leader xem tổng quan)
    public function getByTaskId($task_id) {
        $query = "SELECT s.*, u.full_name as assignee_name
                  FROM " . $this->table_name . " s
                  JOIN users u ON s.assignee_id = u.id
                  WHERE s.task_id = :task_id
                  ORDER BY s.created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $task_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Kiểm tra subtask đã có minh chứng chưa
    public function hasEvidence($subtask_id) {
        $query = "SELECT COUNT(*) as cnt FROM subtask_attachments WHERE subtask_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $subtask_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row['cnt'] > 0);
    }

    // Lấy subtask được giao cho nhân viên cụ thể (Staff view)
    public function getByAssignee($user_id) {
        $query = "SELECT s.*, t.title as task_title, t.priority, u.full_name as assignee_name
                  FROM " . $this->table_name . " s
                  JOIN tasks t ON s.task_id = t.id
                  JOIN users u ON s.assignee_id = u.id
                  WHERE s.assignee_id = :user_id
                  ORDER BY s.deadline ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tạo subtask mới (Leader gán cho Staff)
    public function create($task_id, $assignee_id, $title, $description, $deadline, $priority = 'Medium') {
        $query = "INSERT INTO " . $this->table_name . " 
                  (task_id, assignee_id, title, description, deadline, priority)
                  VALUES (:task_id, :assignee_id, :title, :desc, :deadline, :priority)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $task_id);
        $stmt->bindParam(':assignee_id', $assignee_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':deadline', $deadline);
        $stmt->bindParam(':priority', $priority);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật trạng thái subtask (kéo thả hoặc nút bấm)
    public function updateStatus($subtask_id, $status, $is_rejected = 0) {
        $query = "UPDATE " . $this->table_name . " SET status = :status, is_rejected = :is_rejected WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':is_rejected', $is_rejected, PDO::PARAM_INT);
        $stmt->bindParam(':id', $subtask_id);
        return $stmt->execute();
    }

    // Gửi minh chứng (Evidence)
    public function submitEvidence($subtask_id, $notes, $file_url = null) {
        $this->conn->beginTransaction();
        try {
            // Cập nhật trạng thái sang Pending (Chờ duyệt)
            $query = "UPDATE " . $this->table_name . " SET status = 'Pending', is_rejected = 0 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $subtask_id);
            $stmt->execute();

            // Lưu minh chứng vào bảng attachments
            if ($notes || $file_url) {
                $q2 = "INSERT INTO subtask_attachments (subtask_id, file_name, file_url, notes) 
                       VALUES (:sid, :fname, :furl, :notes)";
                $s2 = $this->conn->prepare($q2);
                $fname = $file_url ? basename($file_url) : 'Note/Link';
                $s2->execute([
                    ':sid' => $subtask_id,
                    ':fname' => $fname,
                    ':furl' => $file_url ?? '',
                    ':notes' => $notes
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Duyệt Subtask -> Đánh dấu is_approved = 1 (Vẫn giữ ở Pending để nhân viên tự kéo sang Done)
    public function approve($subtask_id) {
        $query = "UPDATE " . $this->table_name . " SET is_approved = 1, is_rejected = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $subtask_id);
        return $stmt->execute();
    }

    // Từ chối Subtask -> Về lại Cần làm (To Do)
    public function reject($subtask_id, $feedback) {
        $query = "UPDATE " . $this->table_name . " SET status = 'To Do', feedback = :feedback, is_rejected = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':feedback', $feedback);
        $stmt->bindParam(':id', $subtask_id);
        return $stmt->execute();
    }

    // Gia hạn deadline subtask trễ hạn → về To Do + nền vàng vĩnh viễn
    public function extendDeadline($subtask_id, $newDeadline) {
        $query = "UPDATE " . $this->table_name . " SET deadline = :deadline, status = 'To Do', is_extended = 1, is_rejected = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':deadline', $newDeadline);
        $stmt->bindParam(':id', $subtask_id);
        return $stmt->execute();
    }

    // Chỉ lưu minh chứng (KHÔNG đổi status sang Pending)
    public function saveEvidenceOnly($subtask_id, $notes, $file_url = null) {
        if (!$notes && !$file_url) return false;
        $query = "INSERT INTO subtask_attachments (subtask_id, file_name, file_url, notes) VALUES (:sid, :fname, :furl, :notes)";
        $stmt = $this->conn->prepare($query);
        $fname = $file_url ? basename($file_url) : 'Note/Link';
        $stmt->execute([
            ':sid' => $subtask_id,
            ':fname' => $fname,
            ':furl' => $file_url ?? '',
            ':notes' => $notes
        ]);
        return true;
    }

    // Xóa Subtask
    public function delete($subtask_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $subtask_id);
        return $stmt->execute();
    }

    // Lấy subtask theo ID
    public function getById($subtask_id) {
        $query = "SELECT s.*, t.title as task_title, t.priority, u.full_name as assignee_name
                  FROM " . $this->table_name . " s
                  JOIN tasks t ON s.task_id = t.id
                  JOIN users u ON s.assignee_id = u.id
                  WHERE s.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $subtask_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy toàn bộ subtasks thuộc phòng ban (cho Leader)
    public function getByDepartment($department_id) {
        $query = "SELECT s.*, t.title as task_title, t.priority, t.department_id, u.full_name as assignee_name
                  FROM " . $this->table_name . " s
                  JOIN tasks t ON s.task_id = t.id
                  JOIN users u ON s.assignee_id = u.id
                  WHERE t.department_id = :dept_id
                  ORDER BY 
                    CASE s.status 
                        WHEN 'To Do' THEN 1 
                        WHEN 'In Progress' THEN 2 
                        WHEN 'Pending' THEN 3 
                        WHEN 'Done' THEN 4 
                    END, s.deadline ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $department_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CEO: lấy toàn bộ subtask
    public function getAll() {
        $query = "SELECT s.*, t.title as task_title, t.priority, t.department_id, 
                  d.dept_name, u.full_name as assignee_name
                  FROM " . $this->table_name . " s
                  JOIN tasks t ON s.task_id = t.id
                  LEFT JOIN departments d ON t.department_id = d.id
                  JOIN users u ON s.assignee_id = u.id
                  ORDER BY 
                    CASE s.status 
                        WHEN 'To Do' THEN 1 
                        WHEN 'In Progress' THEN 2 
                        WHEN 'Pending' THEN 3 
                        WHEN 'Done' THEN 4 
                    END, s.deadline ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thống kê Subtask chung (Overdue, Total, Done, v.v...)
    public function getSubtaskStats($department_id = null, $assignee_id = null) {
        $query = "SELECT COUNT(s.id) as total_subtasks,
                         SUM(CASE WHEN s.status = 'Done' THEN 1 ELSE 0 END) as done_subtasks,
                         SUM(CASE WHEN s.deadline < NOW() AND s.status != 'Done' THEN 1 ELSE 0 END) as overdue_subtasks,
                         SUM(CASE WHEN s.status = 'To Do' THEN 1 ELSE 0 END) as todo_subtasks,
                         SUM(CASE WHEN s.status = 'In Progress' THEN 1 ELSE 0 END) as inprogress_subtasks,
                         SUM(CASE WHEN s.status = 'Pending' THEN 1 ELSE 0 END) as pending_subtasks
                  FROM " . $this->table_name . " s
                  JOIN tasks t ON s.task_id = t.id";
        
        $conditions = [];
        if ($department_id) { $conditions[] = "t.department_id = :dept_id"; }
        if ($assignee_id) { $conditions[] = "s.assignee_id = :assignee_id"; }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->conn->prepare($query);
        if ($department_id) { $stmt->bindParam(':dept_id', $department_id); }
        if ($assignee_id) { $stmt->bindParam(':assignee_id', $assignee_id); }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy dữ liệu Bar Chart cho CEO: Tổng lượng việc tải trên các phòng ban
    public function getWorkloadByDepartment() {
        $query = "SELECT d.dept_name, COUNT(s.id) as total_tasks
                  FROM departments d
                  LEFT JOIN tasks t ON t.department_id = d.id
                  LEFT JOIN subtasks s ON s.task_id = t.id
                  GROUP BY d.id
                  ORDER BY total_tasks DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy dữ liệu Bar Chart cho Leader: Lượng việc từng nhân viên trong phòng
    public function getWorkloadByAssignee($department_id) {
        $query = "SELECT u.full_name as assignee_name, COUNT(s.id) as total_tasks
                  FROM users u
                  JOIN subtasks s ON s.assignee_id = u.id
                  JOIN tasks t ON s.task_id = t.id
                  WHERE t.department_id = :dept_id
                  GROUP BY u.id
                  ORDER BY total_tasks DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $department_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy dữ liệu Việc gấp cần xử lý cho Right Sidebar
    public function getUrgentSubtasksByUser($user_id) {
        $query = "SELECT s.*, 
                         t.title as parent_task_title,
                         (SELECT COUNT(*) FROM subtasks WHERE task_id = t.id) as parent_total_subtasks,
                         (SELECT COUNT(*) FROM subtasks WHERE task_id = t.id AND status = 'Done') as parent_done_subtasks
                  FROM " . $this->table_name . " s
                  JOIN tasks t ON s.task_id = t.id
                  WHERE s.assignee_id = :user_id 
                    AND s.status IN ('To Do', 'In Progress')
                  ORDER BY s.deadline ASC
                  LIMIT 15"; // Lấy nhiều hơn 3 để dự phòng/cuộn nếu sau này cần
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
