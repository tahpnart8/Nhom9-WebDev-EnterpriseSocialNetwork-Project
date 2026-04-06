<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

class DashboardController {
    public function index() {
        // Yêu cầu đăng nhập
        if(!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $pageTitle = "Tổng quan cá nhân"; // Page title cho Header
        
        // Trả giao diện
        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}
?>
