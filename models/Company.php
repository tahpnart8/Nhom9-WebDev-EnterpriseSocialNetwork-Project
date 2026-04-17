<?php
require_once __DIR__ . '/BaseModel.php';

class Company extends BaseModel {
    protected string $table_name = "companies";

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (company_name, industry, ceo_name, ceo_email, ceo_phone, ceo_password_hash, status) 
                  VALUES (:company_name, :industry, :ceo_name, :ceo_email, :ceo_phone, :ceo_password_hash, 'pending')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':company_name', $data['company_name']);
        $stmt->bindParam(':industry', $data['industry']);
        $stmt->bindParam(':ceo_name', $data['ceo_name']);
        $stmt->bindParam(':ceo_email', $data['ceo_email']);
        $stmt->bindParam(':ceo_phone', $data['ceo_phone']);
        $stmt->bindParam(':ceo_password_hash', $data['ceo_password_hash']);
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getPendingCompanies() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'pending' ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCompanyById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function approve($id) {
        $query = "UPDATE " . $this->table_name . " SET status = 'approved' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function reject($id) {
        $query = "UPDATE " . $this->table_name . " SET status = 'rejected' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // --- NEW FEATURES FOR SUPER ADMIN ---

    public function getSystemStats() {
        $stats = [];
        $stats['total_companies'] = $this->conn->query("SELECT COUNT(*) FROM companies")->fetchColumn();
        $stats['total_users'] = $this->conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['total_projects'] = $this->conn->query("SELECT COUNT(*) FROM projects")->fetchColumn();
        $stats['total_tasks'] = $this->conn->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
        
        $stats['industry_dist'] = $this->conn->query("SELECT industry, COUNT(*) as count FROM companies GROUP BY industry")->fetchAll(PDO::FETCH_ASSOC);
        $stats['recent_registrations'] = $this->conn->query("SELECT * FROM companies ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }

    public function getAllCompanies($limit = 10, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCompaniesCount() {
        return $this->conn->query("SELECT COUNT(*) FROM " . $this->table_name)->fetchColumn();
    }

    public function updateCompany($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET company_name = :name, industry = :industry, 
                      max_users = :max_users, max_projects = :max_projects, 
                      max_departments = :max_departments, status = :status
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $data['company_name']);
        $stmt->bindParam(':industry', $data['industry']);
        $stmt->bindParam(':max_users', $data['max_users']);
        $stmt->bindParam(':max_projects', $data['max_projects']);
        $stmt->bindParam(':max_departments', $data['max_departments']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function deleteCompany($id) {
        // FK Constraints ON DELETE CASCADE will handle users, depts, etc. if set up correctly
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function checkQuota($company_id, $entity_type) {
        $company = $this->getCompanyById($company_id);
        if (!$company) return false;

        $count = 0;
        $max = 0;
        switch ($entity_type) {
            case 'users':
                $count = $this->conn->query("SELECT COUNT(*) FROM users WHERE company_id = $company_id")->fetchColumn();
                $max = $company['max_users'];
                break;
            case 'departments':
                $count = $this->conn->query("SELECT COUNT(*) FROM departments WHERE company_id = $company_id")->fetchColumn();
                $max = $company['max_departments'];
                break;
            case 'projects':
                $count = $this->conn->query("SELECT COUNT(*) FROM projects WHERE company_id = $company_id")->fetchColumn();
                $max = $company['max_projects'];
                break;
        }
        return $count < $max;
    }
}
