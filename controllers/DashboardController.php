<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Subtask.php';

class DashboardController extends BaseController {
    public function index() {
        // Yêu cầu đăng nhập
        if(!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $pageTitle = "Master Dashboard"; // Page title cho Header
        if ($_SESSION['role_id'] == 3) {
            $pageTitle = "Tổng quan cá nhân";
        }
        
        $taskModel = new Task($this->db);
        $subtaskModel = new Subtask($this->db);

        $roleId = $_SESSION['role_id'];
        $userId = $_SESSION['user_id'];
        $deptId = $_SESSION['department_id'] ?? null;

        $taskStats = [];
        $subtaskStats = [];
        $workloadData = [];

        if ($roleId == 1) { // CEO
            $taskStats = $taskModel->getTaskStats();
            $subtaskStats = $subtaskModel->getSubtaskStats();
            $workloadData = $subtaskModel->getWorkloadByDepartment();
        } elseif ($roleId == 2) { // Leader
            $taskStats = $taskModel->getTaskStats($deptId);
            $subtaskStats = $subtaskModel->getSubtaskStats($deptId);
            $workloadData = $subtaskModel->getWorkloadByAssignee($deptId);
        } else { // Staff
            $subtaskStats = $subtaskModel->getSubtaskStats(null, $userId);
        }

        // Trả giao diện
        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}
