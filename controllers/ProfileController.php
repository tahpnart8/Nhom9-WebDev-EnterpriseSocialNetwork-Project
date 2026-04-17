<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/CloudStorage.php';

class ProfileController extends BaseController {

    public function index() {
        $this->checkAuth();
        
        $userModel = new User($this->db);
        $postModel = new Post($this->db);
        
        // Mặc định là chính mình, nếu có ID thì là xem người khác
        $viewingId = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user_id'];
        $isViewingSelf = ($viewingId == $_SESSION['user_id']);
        
        $user = $userModel->getById($viewingId);
        if (!$user) {
            header("Location: index.php?action=dashboard");
            exit;
        }

        // Lọc bài viết: Nếu xem người khác thì chỉ hiện bài Public
        $visibilityFilter = $isViewingSelf ? "" : " AND p.visibility = 'Public' ";
        
        $query = "SELECT p.*, u.full_name, u.avatar_url, m.media_url, m.media_type, r.role_name,
                  (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id) as like_count,
                  (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND user_id = :current_user) as is_liked,
                  (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
                  FROM posts p
                  JOIN users u ON p.author_id = u.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  LEFT JOIN post_media m ON p.id = m.post_id
                  WHERE p.author_id = :author_id $visibilityFilter
                  ORDER BY p.created_at DESC";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':current_user', $_SESSION['user_id']);
        $stmt->bindParam(':author_id', $viewingId);
        $stmt->execute();
        $userPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = $isViewingSelf ? "Trang cá nhân" : "Hồ sơ của " . $user['full_name'];

        // Lấy hiệu suất (KPI) sử dụng Procedure qua Subtask Model
        require_once __DIR__ . '/../models/Subtask.php';
        $subtaskModel = new Subtask($this->db);
        $performance = $subtaskModel->getPerformance($viewingId);

        require_once __DIR__ . '/../views/profile/index.php';
    }

    public function updateProfile() {
        $this->checkAuth();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request!']);
            exit;
        }

        $userModel = new User($this->db);
        $userId = $_SESSION['user_id'];
        
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $birthdate = trim($_POST['birthdate'] ?? '');
        if (empty($birthdate)) $birthdate = null;
        
        $hide_birthdate = isset($_POST['hide_birthdate']) ? 1 : 0;
        $location = trim($_POST['location'] ?? '');
        $link_facebook = trim($_POST['link_facebook'] ?? '');
        $link_instagram = trim($_POST['link_instagram'] ?? '');
        $link_tiktok = trim($_POST['link_tiktok'] ?? '');
        
        $avatarBase64 = $_POST['avatar_base64'] ?? '';
        $coverBase64 = $_POST['cover_base64'] ?? '';

        if (empty($full_name)) {
            echo json_encode(['success' => false, 'message' => 'Họ tên không được để trống!']);
            exit;
        }

        $cloudStorage = new CloudStorage();
        $avatarUrl = null;
        $coverUrl = null;

        if (!empty($avatarBase64)) {
            $avatarUrl = $cloudStorage->uploadBase64Image($avatarBase64);
            if ($avatarUrl === false) $avatarUrl = null; // Quản lý lỗi nếu ko up được
        }

        if (!empty($coverBase64)) {
            $coverUrl = $cloudStorage->uploadBase64Image($coverBase64);
            if ($coverUrl === false) $coverUrl = null;
        }

        if ($userModel->updateProfile($userId, $full_name, $email, $phone, $birthdate, $hide_birthdate, $location, $link_facebook, $link_instagram, $link_tiktok, $avatarUrl, $coverUrl)) {
            // Update session nếu đổi thông tin
            $_SESSION['full_name'] = $full_name;
            if ($avatarUrl !== null) {
                $_SESSION['avatar_url'] = $avatarUrl;
            }
            
            echo json_encode(['success' => true, 'message' => 'Cập nhật hồ sơ thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Đã có lỗi xảy ra khi lưu vào database.']);
        }
        exit;
    }

    public function apiSearchUsers() {
        header('Content-Type: application/json');
        $keyword = trim($_GET['q'] ?? '');
        if (empty($keyword)) {
            echo json_encode([]);
            exit;
        }

        $userModel = new User($this->db);
        $results = $userModel->search($keyword);
        echo json_encode($results);
        exit;
    }
}
?>
