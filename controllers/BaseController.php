<?php
/**
 * BaseController — Lớp trừu tượng cha cho tất cả Controller.
 * Cung cấp: kết nối DB, kiểm tra đăng nhập, JSON response helpers, đọc biến môi trường.
 */
require_once __DIR__ . '/../config/database.php';

abstract class BaseController {
    protected PDO $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    /**
     * Kiểm tra đăng nhập — chuyển hướng về trang login nếu chưa đăng nhập.
     */
    protected function checkAuth(): void {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
    }

    /**
     * Kiểm tra quyền Admin hoặc CEO (role_id = 1 hoặc 4).
     */
    protected function checkAdminAccess(): void {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 4)) {
            header("Location: index.php?action=dashboard");
            exit;
        }
    }

    /**
     * Kiểm tra quyền Super Admin (role_id = 4).
     */
    protected function checkSuperAdminAccess(): void {
        if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
            header("Location: index.php?action=admin_secret_portal");
            exit;
        }
    }

    /**
     * Trả về JSON response và kết thúc request.
     */
    protected function jsonResponse(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Trả về JSON thành công.
     */
    protected function jsonSuccess(string $message, array $extra = []): void {
        $this->jsonResponse(array_merge(['success' => true, 'message' => $message], $extra));
    }

    /**
     * Trả về JSON lỗi.
     */
    protected function jsonError(string $message): void {
        $this->jsonResponse(['success' => false, 'message' => $message]);
    }

    /**
     * Đọc biến môi trường (ưu tiên getenv → $_ENV → fallback rỗng).
     */
    protected function getEnvVar(string $key): string {
        return getenv($key) ?: ($_ENV[$key] ?? '');
    }
}
?>
