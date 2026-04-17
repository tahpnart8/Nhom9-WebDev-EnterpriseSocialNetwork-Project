<?php
class Task {
    private $conn;
    public $table_name = "tasks";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả Task thuộc phòng ban (cho Leader / Staff trong phòng đó)
    public function getByDepartment($department_id, $company_id, $project_id = null) {
        $query = "SELECT t.*, u.full_name as creator_name, p.title as project_title,
                  COALESCE(sc.subtask_count, 0) as subtask_count,
                  COALESCE(sc.done_count, 0) as done_count
                  FROM " . $this->table_name . " t
                  JOIN users u ON t.created_by_user_id = u.id
                  LEFT JOIN projects p ON t.project_id = p.id
                  LEFT JOIN (
                      SELECT task_id, COUNT(*) as subtask_count,
                             SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done_count
                      FROM subtasks GROUP BY task_id
                  ) sc ON sc.task_id = t.id
                  WHERE t.department_id = :dept_id AND t.company_id = :company_id";
        
        if ($project_id) {
            $query .= " AND t.project_id = :project_id";
        }
        $query .= " ORDER BY t.created_at DESC";
 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $department_id);
        $stmt->bindParam(':company_id', $company_id);
        if ($project_id) {
            $stmt->bindParam(':project_id', $project_id);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CEO xem tất cả Task toàn công ty (Hoặc filter theo project)
    public function getAll($company_id, $project_id = null) {
        $query = "SELECT t.*, u.full_name as creator_name, d.dept_name, p.title as project_title,
                  COALESCE(sc.subtask_count, 0) as subtask_count,
                  COALESCE(sc.done_count, 0) as done_count
                  FROM " . $this->table_name . " t
                  JOIN users u ON t.created_by_user_id = u.id
                  LEFT JOIN departments d ON t.department_id = d.id
                  LEFT JOIN projects p ON t.project_id = p.id
                  LEFT JOIN (
                      SELECT task_id, COUNT(*) as subtask_count,
                             SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done_count
                      FROM subtasks GROUP BY task_id
                  ) sc ON sc.task_id = t.id
                  WHERE t.company_id = :company_id";
        
        if ($project_id) {
            $query .= " AND t.project_id = :project_id";
        }
        $query .= " ORDER BY t.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id);
        if ($project_id) {
            $stmt->bindParam(':project_id', $project_id);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tạo Task mới (Leader / CEO)
    public function create($department_id, $project_id, $created_by, $title, $description, $priority, $deadline, $company_id) {
        $checkQuery = "SELECT id FROM " . $this->table_name . " 
                       WHERE department_id = :dept_id 
                         AND project_id <=> :project_id
                         AND title = :title 
                         AND description = :desc 
                         AND deadline <=> :deadline
                         AND company_id = :company_id
                         AND status != 'Done'";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([
            ':dept_id' => $department_id,
            ':project_id' => $project_id,
            ':title' => $title,
            ':desc' => $description,
            ':deadline' => $deadline,
            ':company_id' => $company_id
        ]);
        
        if ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
            return 'DUPLICATE'; 
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (department_id, project_id, created_by_user_id, company_id, title, description, priority, deadline) 
                  VALUES (:dept_id, :project_id, :created_by, :company_id, :title, :desc, :priority, :deadline)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $department_id);
        
        if ($project_id == NULL) {
            $stmt->bindValue(':project_id', NULL, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':project_id', $project_id);
        }

        $stmt->bindParam(':created_by', $created_by);
        $stmt->bindParam(':company_id', $company_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':priority', $priority);
        $stmt->bindParam(':deadline', $deadline);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật Task
    public function update($task_id, $title, $description, $priority, $deadline, $company_id, $project_id = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET title = :title, 
                      description = :desc, 
                      priority = :priority, 
                      deadline = :deadline,
                      project_id = :project_id,
                      updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':priority', $priority);
        $stmt->bindParam(':deadline', $deadline);
        if ($project_id == NULL) {
            $stmt->bindValue(':project_id', NULL, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':project_id', $project_id);
        }
        $stmt->bindParam(':company_id', $company_id);
        $stmt->bindParam(':id', $task_id);
        return $stmt->execute();
    }

    // Cập nhật trạng thái Task
    public function updateStatus($task_id, $status, $company_id) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $task_id);
        $stmt->bindParam(':company_id', $company_id);
        return $stmt->execute();
    }

    // Cập nhật trạng thái Approval của Task (Trình CEO)
    public function updateApprovalStatus($task_id, $approval_status, $company_id, $ai_post_id = null) {
        $query = "UPDATE " . $this->table_name . " SET approval_status = :approval_status";
        if ($ai_post_id !== null) {
             $query .= ", ai_report_post_id = :post_id";
        }
        $query .= " WHERE id = :id AND company_id = :company_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':approval_status', $approval_status);
        if ($ai_post_id !== null) {
            $stmt->bindParam(':post_id', $ai_post_id);
        }
        $stmt->bindParam(':id', $task_id);
        $stmt->bindParam(':company_id', $company_id);
        return $stmt->execute();
    }

    // Lấy Task theo ID
    // Lấy nhiều Tasks cùng lúc (Batch — tránh N+1)
    public function getTasksByIds($ids, $company_id) {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "SELECT t.*, u.full_name as creator_name, d.dept_name, p.title as project_title,
                  COALESCE(sc.subtask_count, 0) as subtask_count,
                  COALESCE(sc.done_count, 0) as done_count
                  FROM " . $this->table_name . " t
                  JOIN users u ON t.created_by_user_id = u.id
                  LEFT JOIN departments d ON t.department_id = d.id
                  LEFT JOIN projects p ON t.project_id = p.id
                  LEFT JOIN (
                      SELECT task_id, COUNT(*) as subtask_count,
                             SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done_count
                      FROM subtasks GROUP BY task_id
                  ) sc ON sc.task_id = t.id
                  WHERE t.id IN ($placeholders) AND t.company_id = ?";
        $stmt = $this->conn->prepare($query);
        $params = array_values($ids);
        $params[] = $company_id;
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($task_id, $company_id = null) {
        $query = "SELECT t.*, u.full_name as creator_name, d.dept_name, p.title as project_title,
                  COALESCE(sc.subtask_count, 0) as subtask_count,
                  COALESCE(sc.done_count, 0) as done_count
                  FROM " . $this->table_name . " t
                  JOIN users u ON t.created_by_user_id = u.id
                  LEFT JOIN departments d ON t.department_id = d.id
                  LEFT JOIN projects p ON t.project_id = p.id
                  LEFT JOIN (
                      SELECT task_id, COUNT(*) as subtask_count,
                             SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done_count
                      FROM subtasks GROUP BY task_id
                  ) sc ON sc.task_id = t.id
                  WHERE t.id = :id";
        
        if ($company_id) {
            $query .= " AND t.company_id = :company_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $task_id);
        if ($company_id) {
            $stmt->bindParam(':company_id', $company_id);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Xóa Task (cascade xóa subtasks, attachments)
    public function delete($task_id, $company_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $task_id);
        $stmt->bindParam(':company_id', $company_id);
        return $stmt->execute();
    }

    // Lấy thống kê dự án sử dụng Procedure sp_GetDashboardOverview
    public function getTaskStats($role_id = null, $department_id = null) {
        // Nếu không truyền role_id, mặc định lấy từ Session hoặc gán là 1 (CEO) để lấy toàn bộ
        $r_id = $role_id ?? ($_SESSION['role_id'] ?? 1);
        $d_id = $department_id ?? ($_SESSION['department_id'] ?? null);

        $query = "CALL sp_GetDashboardOverview(:uid, :dept_id, :role_id)";
        $stmt = $this->conn->prepare($query);
        $dummy_uid = 0; 
        $stmt->bindParam(':uid', $dummy_uid);
        $stmt->bindValue(':dept_id', $d_id, $d_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(':role_id', $r_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return [
            'total_tasks' => $result['task_count'] ?? 0,
            'active_projects' => $result['task_count'] ?? 0,
            'pending_approvals' => $result['pending_count'] ?? 0
        ];
    }

    public function search($keyword, $role_id, $dept_id, $user_id) {
        $query = "CALL sp_SearchTasks(:user_id, :role_id, :dept_id, :keyword)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':role_id', $role_id);
        $stmt->bindValue(':dept_id', $dept_id, $dept_id === null ? PDO::PARAM_INT : PDO::PARAM_INT);
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $results;
    }

    public function getUrgentTasks() {
        $query = "SELECT t.*, u.full_name as assignee_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.created_by_user_id = u.id
                  WHERE t.priority = 'High' 
                     OR (t.deadline < NOW() AND t.status != 'Done')
                  ORDER BY t.priority DESC, t.deadline ASC
                  LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
