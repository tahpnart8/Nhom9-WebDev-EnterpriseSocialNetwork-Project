<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Company.php';

class AuthController {
    public function showLogin() {
        if(isset($_SESSION['user_id'])) {
            if($_SESSION['role_id'] == 4) {
                header("Location: index.php?action=admin_dashboard");
            } else {
                header("Location: index.php?action=dashboard");
            }
            exit;
        }
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function login() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            $database = new Database();
            $db = $database->getConnection();
            $user = new User($db);

            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if($user->login($username, $password)) {
                // Prevent Admin from logging in via standard portal
                if($user->role_id == 4) {
                    echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại.']);
                    exit;
                }

                // Define sessions
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['role_id'] = $user->role_id;
                $_SESSION['company_id'] = $user->company_id;
                $_SESSION['department_id'] = $user->department_id;
                $_SESSION['full_name'] = $user->full_name;
                $_SESSION['avatar_url'] = $user->avatar_url;
                $_SESSION['first_login_flash'] = true;

                echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công', 'redirect' => 'index.php?action=dashboard']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.']);
                exit;
            }
        }
    }

    public function showAdminLogin() {
        if(isset($_SESSION['user_id']) && $_SESSION['role_id'] == 4) {
            header("Location: index.php?action=admin_dashboard");
            exit;
        }
        require_once __DIR__ . '/../views/admin_super/login.php';
    }

    public function adminLogin() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            $database = new Database();
            $db = $database->getConnection();
            $user = new User($db);

            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if($user->login($username, $password)) {
                // Ensure only Admin can login via this portal
                if($user->role_id != 4) {
                    echo json_encode(['success' => false, 'message' => 'Tài khoản không có quyền Admin.']);
                    exit;
                }

                // Define sessions
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['role_id'] = $user->role_id;
                $_SESSION['company_id'] = $user->company_id;
                $_SESSION['full_name'] = $user->full_name;
                $_SESSION['avatar_url'] = $user->avatar_url;
                
                echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công', 'redirect' => 'index.php?action=admin_dashboard']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.']);
                exit;
            }
        }
    }

    public function showRegisterCompany() {
        require_once __DIR__ . '/../views/auth/register_company.php';
    }

    public function registerCompany() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $company = new Company();
            
            $data = [
                'company_name' => trim($_POST['company_name'] ?? ''),
                'industry' => trim($_POST['industry'] ?? ''),
                'ceo_name' => trim($_POST['ceo_name'] ?? ''),
                'ceo_email' => trim($_POST['ceo_email'] ?? ''),
                'ceo_phone' => trim($_POST['ceo_phone'] ?? ''),
                'ceo_password_hash' => password_hash($_POST['ceo_password'] ?? '', PASSWORD_DEFAULT)
            ];

            if(empty($data['company_name']) || empty($data['ceo_name']) || empty($data['ceo_email']) || empty($_POST['ceo_password'])) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ các thông tin bắt buộc.']);
                exit;
            }

            if($company->create($data)) {
                echo json_encode(['success' => true, 'message' => 'Đăng ký thành công! Đang chờ Admin xét duyệt.', 'redirect' => 'index.php?action=login']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại sau.']);
            }
            exit;
        }
    }

    public function logout() {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isAdmin = isset($_SESSION['role_id']) && $_SESSION['role_id'] == 4;
        session_destroy();
        if($isAdmin) {
            header("Location: index.php?action=admin_secret_portal");
        } else {
            header("Location: index.php?action=login");
        }
        exit;
    }
}
