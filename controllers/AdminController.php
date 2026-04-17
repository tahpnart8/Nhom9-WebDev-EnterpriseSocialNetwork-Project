<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../models/AuditLog.php';

class AdminController extends BaseController {

    public function superAdminDashboard() {
        $this->checkSuperAdminAccess();
        $pageTitle = "Bảng điều khiển Hệ thống (SaaS)";
        
        require_once __DIR__ . '/../models/Company.php';
        $companyModel = new Company($this->db);
        $pendingCompanies = $companyModel->getPendingCompanies();
        $stats = $companyModel->getSystemStats();
        
        require_once __DIR__ . '/../views/admin_super/dashboard.php';
    }

    public function manageCompanies() {
        $this->checkSuperAdminAccess();
        $pageTitle = "Quản lý Không gian Doanh nghiệp";
        
        require_once __DIR__ . '/../models/Company.php';
        $companyModel = new Company($this->db);
        
        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        $companies = $companyModel->getAllCompanies($limit, $offset);
        $totalCount = $companyModel->getTotalCompaniesCount();
        $totalPages = ceil($totalCount / $limit);
        
        require_once __DIR__ . '/../views/admin_super/companies.php';
    }

    public function apiUpdateCompany() {
        $this->checkSuperAdminAccess();
        header('Content-Type: application/json');
        
        $id = $_POST['id'] ?? null;
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
            exit;
        }
        
        $data = [
            'company_name' => $_POST['company_name'] ?? '',
            'industry' => $_POST['industry'] ?? '',
            'max_users' => $_POST['max_users'] ?? 100,
            'max_projects' => $_POST['max_projects'] ?? 50,
            'max_departments' => $_POST['max_departments'] ?? 10,
            'status' => $_POST['status'] ?? 'approved'
        ];
        
        require_once __DIR__ . '/../models/Company.php';
        $companyModel = new Company($this->db);
        if($companyModel->updateCompany($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
        exit;
    }

    public function apiDeleteCompany() {
        $this->checkSuperAdminAccess();
        header('Content-Type: application/json');
        
        $id = $_POST['id'] ?? null;
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID']);
            exit;
        }
        
        require_once __DIR__ . '/../models/Company.php';
        $companyModel = new Company($this->db);
        if($companyModel->deleteCompany($id)) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa không gian doanh nghiệp!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa']);
        }
        exit;
    }

    public function apiBroadcast() {
        $this->checkSuperAdminAccess();
        header('Content-Type: application/json');
        
        $content = $_POST['content'] ?? '';
        $target = $_POST['target'] ?? 'all'; // all, ceos
        
        if(empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Nội dung thông báo trống!']);
            exit;
        }
        
        $db = $this->db;

        AuditLog::log($db, 'GLOBAL_BROADCAST', 'System', null, "Gửi thông báo toàn hệ thống tới: $target");
        
        // Lấy danh sách user IDs dựa trên target
        $query = "SELECT id, company_id FROM users WHERE is_active = 1";
        if($target == 'ceos') {
            $query .= " AND role_id = 1";
        }
        $stmt = $db->query($query);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once __DIR__ . '/NotificationController.php';
        foreach($users as $user) {
            NotificationController::pushNotification(
                $db,
                'GLOBAL_BROADCAST',
                $_SESSION['user_id'],
                "[HỆ THỐNG]: " . $content,
                "index.php?action=dashboard",
                [$user['id']],
                $user['company_id']
            );
        }
        
        echo json_encode(['success' => true, 'message' => 'Đã gửi thông báo cho ' . count($users) . ' người dùng!']);
        exit;
    }

    public function auditLogs() {
        $this->checkSuperAdminAccess();
        $pageTitle = "Nhật ký Hoạt động Hệ thống";
        
        $database = new Database();
        $db = $database->getConnection();
        $logModel = new AuditLog($db);
        
        $limit = 50;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        $logs = $logModel->getAll($limit, $offset);
        $totalCount = $logModel->getTotalCount();
        $totalPages = ceil($totalCount / $limit);
        
        require_once __DIR__ . '/../views/admin_super/audit_logs.php';
    }

    public function apiApproveCompany() {
        $this->checkSuperAdminAccess();
        header('Content-Type: application/json');
        
        $id = $_POST['id'] ?? null;
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID công ty']);
            exit;
        }
        
        require_once __DIR__ . '/../models/Company.php';
        $companyModel = new Company($this->db);
        $company = $companyModel->getCompanyById($id);
        
        if(!$company) {
            echo json_encode(['success' => false, 'message' => 'Công ty không tồn tại']);
            exit;
        }
        
        // 1. Update status to approved
        if($companyModel->approve($id)) {
            // 2. Create CEO user
            $db = $this->db;
            
            $username = strtolower(explode('@', $company['ceo_email'])[0]) . '_' . $id;
            
            $query = "INSERT INTO users (username, password_hash, full_name, email, role_id, company_id, is_active) 
                      VALUES (:username, :password_hash, :full_name, :email, 1, :company_id, 1)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password_hash', $company['ceo_password_hash']);
            $stmt->bindParam(':full_name', $company['ceo_name']);
            $stmt->bindParam(':email', $company['ceo_email']);
            $stmt->bindParam(':company_id', $id);
            $stmt->execute();

            AuditLog::log($db, 'APPROVE_COMPANY', 'Company', $id, "Duyệt công ty: " . $company['company_name']);
            
            echo json_encode(['success' => true, 'message' => 'Duyệt thành công! Đã tạo tài khoản CEO: ' . $username]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi duyệt công ty']);
        }
        exit;
    }

    public function apiRejectCompany() {
        $this->checkSuperAdminAccess();
        header('Content-Type: application/json');
        
        $id = $_POST['id'] ?? null;
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID công ty']);
            exit;
        }
        
        require_once __DIR__ . '/../models/Company.php';
        $companyModel = new Company($this->db);
        if($companyModel->reject($id)) {
            echo json_encode(['success' => true, 'message' => 'Đã từ chối đơn đăng ký']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
        exit;
    }

    public function users() {
        $this->checkAdminAccess();
        $pageTitle = "Quản lý Nhân sự";
        
        $userModel = new User($this->db);
        
        // Pagination Logic
        $limit = 5;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($currentPage < 1) $currentPage = 1;
        $offset = ($currentPage - 1) * $limit;
        
        $companyId = $_SESSION['company_id'];
        // Search Logic - chỉ search khi có query, không load tất cả
        $searchQuery = $_GET['q'] ?? '';
        if (!empty($searchQuery)) {
            // Search users - chỉ search theo query được cung cấp
            $totalUsers = $userModel->getSearchCount($searchQuery, $companyId);
            $totalPages = ceil($totalUsers / $limit);
            $stmt = $userModel->searchUsers($searchQuery, $companyId, $limit, $offset);
        } else {
            // Get all users - chỉ khi không có search query mới load
            $totalUsers = $userModel->getTotalCount($companyId);
            $totalPages = ceil($totalUsers / $limit);
            $stmt = $userModel->getAllUsersWithDetails($companyId, $limit, $offset);
        }
        
        $usersList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh sách phòng ban và vai trò cho Modal Thêm mới
        $deptModel = new Department($this->db);
        $depts = $deptModel->getAll($companyId); // Modal need all depts
        $deptList = $depts->fetchAll(PDO::FETCH_ASSOC);

        $roleModel = new Role($this->db);
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

        $userModel = new User($this->db);

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
            require_once __DIR__ . '/../models/Company.php';
            $companyModel = new Company($this->db);
            if (!$companyModel->checkQuota($_SESSION['company_id'], 'users')) {
                echo json_encode(['success' => false, 'message' => 'Lỗi: Công ty của bạn đã đạt giới hạn nhân sự tối đa!']);
                exit;
            }

            if ($userModel->create($data, $_SESSION['company_id'])) {
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

        $userModel = new User($this->db);

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

        if ($userModel->update($id, $data, $_SESSION['company_id'])) {
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

        $userModel = new User($this->db);

        if ($userModel->delete($id, $_SESSION['company_id'])) {
            echo json_encode(['success' => true, 'message' => 'Xóa nhân viên thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể xóa nhân viên.']);
        }
        exit;
    }

    public function departments() {
        $this->checkAdminAccess();
        $pageTitle = "Phòng ban / Đơn vị";
        
        $deptModel = new Department($this->db);
        $taskModel = new Task($this->db);

        // Pagination Logic
        $limit = 5;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($currentPage < 1) $currentPage = 1;
        $offset = ($currentPage - 1) * $limit;

        $companyId = $_SESSION['company_id'];
        // Search Logic
        $searchQuery = $_GET['q'] ?? '';
        if (!empty($searchQuery)) {
            // Search departments
            $totalDepts = $deptModel->getSearchCount($searchQuery, $companyId);
            $totalPages = ceil($totalDepts / $limit);
            $stmt = $deptModel->searchDepartments($searchQuery, $companyId, $limit, $offset);
        } else {
            // Get all departments
            $totalDepts = $deptModel->getTotalCount($companyId);
            $totalPages = ceil($totalDepts / $limit);
            $stmt = $deptModel->getAll($companyId, $limit, $offset);
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

        $deptModel = new Department($this->db);

        $data = [
            'dept_name' => $_POST['dept_name'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];

        if (empty($data['dept_name'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên phòng ban']);
            exit;
        }

        require_once __DIR__ . '/../models/Company.php';
        $companyModel = new Company($this->db);
        if (!$companyModel->checkQuota($_SESSION['company_id'], 'departments')) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: Công ty của bạn đã đạt giới hạn số lượng phòng ban!']);
            exit;
        }

        if ($deptModel->create($data, $_SESSION['company_id'])) {
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

        $deptModel = new Department($this->db);

        $data = [
            'dept_name' => $_POST['dept_name'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];

        if (empty($data['dept_name'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên phòng ban']);
            exit;
        }

        if ($deptModel->update($id, $data, $_SESSION['company_id'])) {
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

        // B1: Kiểm tra xem phòng ban còn nhân viên không
        $userModel = new User($this->db);
        $empCount = $userModel->getCountByDepartment($id, $_SESSION['company_id']);

        if ($empCount > 0) {
            echo json_encode([
                'success' => false, 
                'message' => "Không thể xóa! Phòng ban này hiện đang có $empCount nhân viên. Vui lòng chuyển hoặc xóa nhân viên trước."
            ]);
            exit;
        }

        // B2: Thực hiện xóa
        $deptModel = new Department($this->db);
        if ($deptModel->delete($id, $_SESSION['company_id'])) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa phòng ban thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi xóa phòng ban']);
        }
        exit;
    }
}
