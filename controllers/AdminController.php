<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Department.php';

class AdminController {
    
    // Hàm bảo vệ Route Back-office (Chỉ Admin hoặc CEO vào được)
    private function checkAdminAccess() {
        if(!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 4)) {
            // Chuyển về báo lỗi hoặc dashboard
            header("Location: index.php?action=dashboard");
            exit;
        }
    }

    public function users() {
        $this->checkAdminAccess();
        $pageTitle = "Quản lý nhân sự";
        
        $database = new Database();
        $db = $database->getConnection();
        
        $userModel = new User($db);
        $stmt = $userModel->getAllUsersWithDetails();
        $usersList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin/users.php';
    }

    public function departments() {
        $this->checkAdminAccess();
        $pageTitle = "Phòng ban / Đơn vị";

        $database = new Database();
        $db = $database->getConnection();
        
        $deptModel = new Department($db);
        $stmt = $deptModel->getAll();
        $deptList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin/departments.php';
    }
}
