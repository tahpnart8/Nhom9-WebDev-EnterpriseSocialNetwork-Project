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
    public function create($task_id, $assignee_id, $title, $description, $deadline) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (task_id, assignee_id, title, description, deadline)
                  VALUES (:task_id, :assignee_id, :title, :desc, :deadline)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $task_id);
        $stmt->bindParam(':assignee_id', $assignee_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':deadline', $deadline);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật trạng thái subtask (kéo thả hoặc nút bấm)
    public function updateStatus($subtask_id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
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
}
?>
