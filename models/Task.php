<?php
class Task {
    private $conn;
    public $table_name = "tasks";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả Task thuộc phòng ban (cho Leader / Staff trong phòng đó)
    public function getByDepartment($department_id) {
        $query = "SELECT t.*, u.full_name as creator_name,
                  COALESCE(sc.subtask_count, 0) as subtask_count,
                  COALESCE(sc.done_count, 0) as done_count
                  FROM " . $this->table_name . " t
                  JOIN users u ON t.created_by_user_id = u.id
                  LEFT JOIN (
                      SELECT task_id, COUNT(*) as subtask_count,
                             SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done_count
                      FROM subtasks GROUP BY task_id
                  ) sc ON sc.task_id = t.id
                  WHERE t.department_id = :dept_id
                  ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $department_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CEO xem tất cả Task toàn công ty
    public function getAll() {
        $query = "SELECT t.*, u.full_name as creator_name, d.dept_name,
                  COALESCE(sc.subtask_count, 0) as subtask_count,
                  COALESCE(sc.done_count, 0) as done_count
                  FROM " . $this->table_name . " t
                  JOIN users u ON t.created_by_user_id = u.id
                  LEFT JOIN departments d ON t.department_id = d.id
                  LEFT JOIN (
                      SELECT task_id, COUNT(*) as subtask_count,
                             SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done_count
                      FROM subtasks GROUP BY task_id
                  ) sc ON sc.task_id = t.id
                  ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tạo Task mới (Leader / CEO)
    public function create($department_id, $created_by, $title, $description, $priority, $deadline) {
        // Kiểm tra chống trùng lặp (Duplicate Prevention) 
        // Không cho phép tạo Task mới giống hệt (tiêu đề, mô tả, deadline) nếu Task cũ vẫn đang hoạt động (chưa Done)
        $checkQuery = "SELECT id FROM " . $this->table_name . " 
                       WHERE department_id = :dept_id 
                         AND title = :title 
                         AND description = :desc 
                         AND deadline <=> :deadline
                         AND status != 'Done'";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([
            ':dept_id' => $department_id,
            ':title' => $title,
            ':desc' => $description,
            ':deadline' => $deadline
        ]);
        
        if ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
            return 'DUPLICATE'; 
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (department_id, created_by_user_id, title, description, priority, deadline) 
                  VALUES (:dept_id, :created_by, :title, :desc, :priority, :deadline)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $department_id);
        $stmt->bindParam(':created_by', $created_by);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':priority', $priority);
        $stmt->bindParam(':deadline', $deadline);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật trạng thái Task
    public function updateStatus($task_id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $task_id);
        return $stmt->execute();
    }

    // Lấy Task theo ID
    // Lấy nhiều Tasks cùng lúc (Batch — tránh N+1)
    public function getTasksByIds($ids) {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "SELECT t.*, u.full_name as creator_name, d.dept_name,
                  COALESCE(sc.subtask_count, 0) as subtask_count,
                  COALESCE(sc.done_count, 0) as done_count
                  FROM " . $this->table_name . " t
                  JOIN users u ON t.created_by_user_id = u.id
                  LEFT JOIN departments d ON t.department_id = d.id
                  LEFT JOIN (
                      SELECT task_id, COUNT(*) as subtask_count,
                             SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done_count
                      FROM subtasks GROUP BY task_id
                  ) sc ON sc.task_id = t.id
                  WHERE t.id IN ($placeholders)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($task_id) {
        $query = "SELECT t.*, u.full_name as creator_name, d.dept_name
                  FROM " . $this->table_name . " t
                  JOIN users u ON t.created_by_user_id = u.id
                  LEFT JOIN departments d ON t.department_id = d.id
                  WHERE t.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $task_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Xóa Task (cascade xóa subtasks, attachments)
    public function delete($task_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $task_id);
        return $stmt->execute();
    }

    // Lấy thống kê dự án (Active Projects)
    public function getTaskStats($department_id = null) {
        $query = "SELECT COUNT(id) as total_tasks, 
                         SUM(CASE WHEN status != 'Done' THEN 1 ELSE 0 END) as active_projects
                  FROM " . $this->table_name;
        
        if ($department_id) {
            $query .= " WHERE department_id = :dept_id";
        }
        
        $stmt = $this->conn->prepare($query);
        if ($department_id) {
            $stmt->bindParam(':dept_id', $department_id);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
