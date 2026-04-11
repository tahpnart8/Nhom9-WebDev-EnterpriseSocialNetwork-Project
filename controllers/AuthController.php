<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    public function showLogin() {
        if(isset($_SESSION['user_id'])) {
            header("Location: index.php?action=dashboard");
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
                // Define sessions
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['role_id'] = $user->role_id;
                $_SESSION['department_id'] = $user->department_id;
                $_SESSION['full_name'] = $user->full_name;
                $_SESSION['avatar_url'] = $user->avatar_url;

                echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công', 'redirect' => 'index.php?action=dashboard']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.']);
                exit;
            }
        }
    }

    public function logout() {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header("Location: index.php?action=login");
        exit;
    }
}
