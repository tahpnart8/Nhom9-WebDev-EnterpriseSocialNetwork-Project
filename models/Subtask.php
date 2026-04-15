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
        // Kiểm tra chống trùng lặp (Duplicate Prevention) 
        // Không cho phép giao Subtask giống hệt (cùng người, tiêu đề, mô tả, deadline) nếu subtask cũ chưa Done
        $checkQuery = "SELECT id FROM " . $this->table_name . " 
                       WHERE task_id = :task_id 
                         AND assignee_id = :assignee_id 
                         AND title = :title 
                         AND description = :desc 
                         AND deadline <=> :deadline
                         AND status != 'Done'";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([
            ':task_id' => $task_id,
            ':assignee_id' => $assignee_id,
            ':title' => $title,
            ':desc' => $description,
            ':deadline' => $deadline
        ]);
        
        if ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
            return 'DUPLICATE'; 
        }

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

    // Cập nhật trạng thái subtask (kéo thả hoặc nút bấm) - Tích hợp đồng bộ Task cha
    public function updateStatus($subtask_id, $status, $is_rejected = 0) {
        $query = "UPDATE " . $this->table_name . " SET status = :status, is_rejected = :is_rejected WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':is_rejected', $is_rejected, PDO::PARAM_INT);
        $stmt->bindParam(':id', $subtask_id);
        
        if ($stmt->execute()) {
            // Tự động gọi procedure đồng bộ trạng thái Task cha
            $subtask = $this->getById($subtask_id);
            if ($subtask) {
                $syncQuery = "CALL sp_UpdateTaskStatusSync(:tid)";
                $syncStmt = $this->conn->prepare($syncQuery);
                $syncStmt->bindParam(':tid', $subtask['task_id']);
                $syncStmt->execute();
            }
            return true;
        }
        return false;
    }

    // Gửi minh chứng (Evidence) sử dụng Procedure sp_SubmitSubtaskEvidence
    public function submitEvidence($subtask_id, $notes, $file_url = null) {
        $query = "CALL sp_SubmitSubtaskEvidence(:sid, :notes, :furl)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sid', $subtask_id);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':furl', $file_url);
        return $stmt->execute();
    }

    // Duyệt Subtask - Tích hợp đồng bộ Task cha
    public function approve($subtask_id) {
        $query = "UPDATE " . $this->table_name . " SET is_approved = 1, is_rejected = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $subtask_id);
        
        if ($stmt->execute()) {
            $subtask = $this->getById($subtask_id);
            if ($subtask) {
                $syncQuery = "CALL sp_UpdateTaskStatusSync(:tid)";
                $syncStmt = $this->conn->prepare($syncQuery);
                $syncStmt->bindParam(':tid', $subtask['task_id']);
                $syncStmt->execute();
            }
            return true;
        }
        return false;
    }

    // Lấy dữ liệu Bar Chart cho CEO/Leader sử dụng sp_GetWorkloadStats
    public function getWorkloadByDepartment() {
        $query = "CALL sp_GetWorkloadStats(NULL)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        // Convert format cho đồng bộ với code cũ
        return array_map(function($item) {
            return ['dept_name' => $item['label'], 'total_tasks' => $item['total_tasks']];
        }, $results);
    }

    public function getWorkloadByAssignee($department_id) {
        $query = "CALL sp_GetWorkloadStats(:dept_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $department_id);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return array_map(function($item) {
            return ['assignee_name' => $item['label'], 'total_tasks' => $item['total_tasks']];
        }, $results);
    }

    // Lấy dữ liệu Việc gấp cần xử lý sử dụng sp_GetUrgentTasks
    public function getUrgentSubtasksByUser($user_id) {
        $query = "CALL sp_GetUrgentTasks(:uid)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $user_id);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        // Convert format để đồng bộ với UI hiện tại
        return array_map(function($item) {
            $item['parent_total_subtasks'] = $item['parent_total'];
            $item['parent_done_subtasks'] = $item['parent_done'];
            return $item;
        }, $results);
    }

    // Thống kê Subtask chi tiết sử dụng sp_GetSubtaskStatsDetailed (KHẮC PHỤC LỖI)
    public function getSubtaskStats($department_id = null, $assignee_id = null) {
        $query = "CALL sp_GetSubtaskStatsDetailed(:dept_id, :uid)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':dept_id', $department_id, $department_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':uid', $assignee_id, $assignee_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result;
    }

    // Tính điểm KPI nhân viên sử dụng sp_GetEmployeePerformance (TÍNH NĂNG MỚI)
    public function getPerformance($user_id) {
        $query = "CALL sp_GetEmployeePerformance(:uid)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result;
    }
}
?>
