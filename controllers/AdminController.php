<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/Role.php';

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
        $pageTitle = "Quản lý Nhân sự";
        
        $database = new Database();
        $db = $database->getConnection();
        $userModel = new User($db);
        
        // Pagination Logic
        $limit = 5;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($currentPage < 1) $currentPage = 1;
        $offset = ($currentPage - 1) * $limit;
        
        // Search Logic - chỉ search khi có query, không load tất cả
        $searchQuery = $_GET['q'] ?? '';
        if (!empty($searchQuery)) {
            // Search users - chỉ search theo query được cung cấp
            $totalUsers = $userModel->getSearchCount($searchQuery);
            $totalPages = ceil($totalUsers / $limit);
            $stmt = $userModel->searchUsers($searchQuery, $limit, $offset);
        } else {
            // Get all users - chỉ khi không có search query mới load
            $totalUsers = $userModel->getTotalCount();
            $totalPages = ceil($totalUsers / $limit);
            $stmt = $userModel->getAllUsersWithDetails($limit, $offset);
        }
        
        $usersList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh sách phòng ban và vai trò cho Modal Thêm mới
        $deptModel = new Department($db);
        $depts = $deptModel->getAll(); // Modal need all depts
        $deptList = $depts->fetchAll(PDO::FETCH_ASSOC);

        $roleModel = new Role($db);
        $roles = $roleModel->getAll();
        $rolesList = $roles->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/admin/users.php';
    }

    public function apiAddUser() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $userModel = new User($db);

        $data = [
            'username' => $_POST['username'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'department_id' => $_POST['department_id'] ?? null,
            'role_id' => $_POST['role_id'] ?? null,
            'is_active' => $_POST['is_active'] ?? 1
        ];

        // Validate cơ bản
        if (empty($data['username']) || empty($data['full_name']) || empty($data['role_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ các trường bắt buộc (Tài khoản, Họ tên, Vai trò)']);
            exit;
        }

        try {
            if ($userModel->create($data)) {
                echo json_encode(['success' => true, 'message' => 'Thêm nhân viên thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể thêm nhân viên. Vui lòng thử lại.']);
            }
        } catch (\Exception $e) {
            // Check lỗi trùng username
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập (Tài khoản) đã tồn tại trong hệ thống!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
            }
        }
        exit;
    }

    public function apiUpdateUser() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID nhân viên']);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $userModel = new User($db);

        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'department_id' => $_POST['department_id'] ?? null,
            'role_id' => $_POST['role_id'] ?? null,
            'is_active' => $_POST['is_active'] ?? 1
        ];

        if (empty($data['full_name']) || empty($data['role_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ các trường bắt buộc']);
            exit;
        }

        if ($userModel->update($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật nhân viên thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật thông tin.']);
        }
        exit;
    }

    public function apiDeleteUser() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID nhân viên']);
            exit;
        }

        // Không cho phép tự xóa chính mình
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Bạn không thể tự xóa chính mình!']);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $userModel = new User($db);

        if ($userModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Xóa nhân viên thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể xóa nhân viên.']);
        }
        exit;
    }

    public function departments() {
        $this->checkAdminAccess();
        $pageTitle = "Phòng ban / Đơn vị";
        
        $database = new Database();
        $db = $database->getConnection();
        
        $deptModel = new Department($db);
        $taskModel = new Task($db);

        // Pagination Logic
        $limit = 5;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($currentPage < 1) $currentPage = 1;
        $offset = ($currentPage - 1) * $limit;

        // Search Logic
        $searchQuery = $_GET['q'] ?? '';
        if (!empty($searchQuery)) {
            // Search departments
            $totalDepts = $deptModel->getSearchCount($searchQuery);
            $totalPages = ceil($totalDepts / $limit);
            $stmt = $deptModel->searchDepartments($searchQuery, $limit, $offset);
        } else {
            // Get all departments
            $totalDepts = $deptModel->getTotalCount();
            $totalPages = ceil($totalDepts / $limit);
            $stmt = $deptModel->getAll($limit, $offset);
        }
        
        $deptList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Truyền taskModel vào view
        require_once __DIR__ . '/../views/admin/departments.php';
    }

    public function apiAddDepartment() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $deptModel = new Department($db);

        $data = [
            'dept_name' => $_POST['dept_name'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];

        if (empty($data['dept_name'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên phòng ban']);
            exit;
        }

        if ($deptModel->create($data)) {
            echo json_encode(['success' => true, 'message' => 'Khởi tạo đơn vị thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi tạo phòng ban']);
        }
        exit;
    }

    public function apiUpdateDepartment() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID phòng ban']);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $deptModel = new Department($db);

        $data = [
            'dept_name' => $_POST['dept_name'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];

        if (empty($data['dept_name'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên phòng ban']);
            exit;
        }

        if ($deptModel->update($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật phòng ban thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi cập nhật phòng ban']);
        }
        exit;
    }

    public function apiDeleteDepartment() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID phòng ban']);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        
        // B1: Kiểm tra xem phòng ban còn nhân viên không
        $userModel = new User($db);
        $empCount = $userModel->getCountByDepartment($id);

        if ($empCount > 0) {
            echo json_encode([
                'success' => false, 
                'message' => "Không thể xóa! Phòng ban này hiện đang có $empCount nhân viên. Vui lòng chuyển hoặc xóa nhân viên trước."
            ]);
            exit;
        }

        // B2: Thực hiện xóa
        $deptModel = new Department($db);
        if ($deptModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa phòng ban thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi xóa phòng ban']);
        }
        exit;
    }
}
