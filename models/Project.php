<?php
require_once __DIR__ . '/BaseModel.php';

class Project extends BaseModel {
    protected string $table_name = "projects";

    // Get all projects with assigned departments (For CEO)
    public function getAll($company_id, $status = null) {
        $query = "SELECT p.*, u.full_name as creator_name,
                  (SELECT GROUP_CONCAT(d.dept_name SEPARATOR ', ') 
                   FROM project_departments pd 
                   JOIN departments d ON pd.department_id = d.id 
                   WHERE pd.project_id = p.id) as assigned_departments,
                  (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) as task_count,
                  (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.approval_status = 'Approved') as approved_task_count
                  FROM " . $this->table_name . " p
                  JOIN users u ON p.created_by = u.id
                  WHERE p.company_id = :company_id";
        
        if ($status) {
            $query .= " AND p.status = :status";
        }
        $query .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':company_id', $company_id);
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get projects assigned to a specific department (For Trưởng phòng / Staff)
    public function getByDepartment($department_id, $company_id, $status = 'Active') {
        $query = "SELECT p.*, u.full_name as creator_name,
                  (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.department_id = :dept_id) as total_dept_tasks,
                  (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.department_id = :dept_id AND t.approval_status = 'Approved') as approved_dept_tasks
                  FROM " . $this->table_name . " p
                  JOIN project_departments pd ON p.id = pd.project_id
                  JOIN users u ON p.created_by = u.id
                  WHERE pd.department_id = :dept_id AND p.company_id = :company_id";
        
        if ($status) {
            $query .= " AND p.status = :status";
        }
        $query .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $department_id);
        $stmt->bindParam(':company_id', $company_id);
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get project by ID
    public function getById($id, $company_id = null) {
        $query = "SELECT p.*, u.full_name as creator_name,
                  (SELECT GROUP_CONCAT(department_id) 
                   FROM project_departments pd 
                   WHERE pd.project_id = p.id) as department_ids
                  FROM " . $this->table_name . " p
                  JOIN users u ON p.created_by = u.id
                  WHERE p.id = :id";
        
        if ($company_id) {
            $query .= " AND p.company_id = :company_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($company_id) {
            $stmt->bindParam(':company_id', $company_id);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create new Project
    public function create($title, $description, $created_by, $department_ids, $company_id) {
        try {
            $this->conn->beginTransaction();

            // 1. Insert Project
            $query = "INSERT INTO " . $this->table_name . " (title, description, created_by, company_id, status) 
                      VALUES (:title, :description, :created_by, :company_id, 'Active')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':created_by', $created_by);
            $stmt->bindParam(':company_id', $company_id);
            $stmt->execute();
            
            $project_id = $this->conn->lastInsertId();

            // 2. Insert Departments mapping
            if (!empty($department_ids) && is_array($department_ids)) {
                $deptQuery = "INSERT INTO project_departments (project_id, department_id) VALUES (:project_id, :dept_id)";
                $deptStmt = $this->conn->prepare($deptQuery);
                foreach ($department_ids as $dept_id) {
                    $deptStmt->execute([
                        ':project_id' => $project_id,
                        ':dept_id' => $dept_id
                    ]);
                }
            }

            $this->conn->commit();
            return $project_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Update Project
    public function update($id, $title, $description, $department_ids, $company_id) {
        try {
            $this->conn->beginTransaction();

            // 1. Update Project basic info
            $query = "UPDATE " . $this->table_name . " 
                      SET title = :title, description = :description 
                      WHERE id = :id AND company_id = :company_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':company_id', $company_id);
            $stmt->execute();

            // 2. Update Departments mapping (Delete old, insert new)
            $delQuery = "DELETE pd FROM project_departments pd 
                         JOIN projects p ON pd.project_id = p.id 
                         WHERE pd.project_id = :id AND p.company_id = :company_id";
            $delStmt = $this->conn->prepare($delQuery);
            $delStmt->bindParam(':id', $id);
            $delStmt->bindParam(':company_id', $company_id);
            $delStmt->execute();

            if (!empty($department_ids) && is_array($department_ids)) {
                $deptQuery = "INSERT INTO project_departments (project_id, department_id) VALUES (:project_id, :dept_id)";
                $deptStmt = $this->conn->prepare($deptQuery);
                foreach ($department_ids as $dept_id) {
                    $deptStmt->execute([
                        ':project_id' => $id,
                        ':dept_id' => $dept_id
                    ]);
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Update Status
    public function updateStatus($id, $status, $company_id) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':company_id', $company_id);
        return $stmt->execute();
    }

    // Delete Project
    public function delete($id, $company_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':company_id', $company_id);
        return $stmt->execute();
    }
}
?>
