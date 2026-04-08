<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Post.php';

class SocialController {
    public function index() {
        if(!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $pageTitle = "Bảng tin Nội bộ";
        
        $database = new Database();
        $db = $database->getConnection();
        $postModel = new Post($db);
        
        // Fetch feed using Role ID & Department ID session
        $feed = $postModel->getFeed($_SESSION['role_id'], $_SESSION['department_id'] ?? null);
        
        require_once __DIR__ . '/../views/social/index.php';
    }

    public function createPost() {
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: Hết hạn phiên đăng nhập!']);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $postModel = new Post($db);
        
        $content = $_POST['content'] ?? '';
        $visibility = $_POST['visibility'] ?? 'Public';
        
        $author_id = $_SESSION['user_id'];
        $department_id = ($visibility == 'Department') ? $_SESSION['department_id'] : NULL;
        
        if (empty(trim($content))) {
            echo json_encode(['success' => false, 'message' => 'Nội dung không được để trống!']);
            exit;
        }

        // 1. Tạo bản ghi Text vào MySQL
        $postId = $postModel->create($author_id, $department_id, htmlspecialchars($content), $visibility);
        
        if ($postId) {
            // 2. Upload hình ảnh lên API ImgBB (Cloud Storage chính quy)
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                require_once __DIR__ . '/../models/CloudStorage.php';
                $cloudStorage = new CloudStorage();
                
                $cloudUrl = $cloudStorage->uploadImage($_FILES['attachment']['tmp_name']);
                
                if ($cloudUrl !== false) {
                    // Cập nhật đường link ảnh Cloud vào Database chung
                    $postModel->addMedia($postId, $cloudUrl);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Tải ảnh lên Server Cloud thất bại! API Key lỗi.']);
                    exit;
                }
            }
            echo json_encode(['success' => true, 'message' => 'Đăng tải bản tin hoàn tất.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ cơ sở dữ liệu.']);
        }
        exit;
    }

    // API: Xóa bài viết
    public function deletePost() {
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }

        header('Content-Type: application/json');
        $database = new Database();
        $db = $database->getConnection();
        $postModel = new Post($db);

        $postId = $_POST['post_id'] ?? 0;
        $post = $postModel->getById($postId);

        if (!$post) {
            echo json_encode(['success' => false, 'message' => 'Bài viết không tồn tại!']);
            exit;
        }
        
        // PHÂN QUYỀN XÓA: Tác giả bài viết HOẶC (CEO/Admin xóa bài người khác)
        $isAuthor = ($post['author_id'] == $_SESSION['user_id']);
        $isAdminOrCEO = ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 4);

        if ($isAuthor || $isAdminOrCEO) {
            if ($postModel->delete($postId)) {
                echo json_encode(['success' => true, 'message' => 'Đã xóa bài viết.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi CSDL!']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa bài viết này!']);
        }
        exit;
    }

    // API: Chỉnh sửa bài viết
    public function editPost() {
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }

        header('Content-Type: application/json');
        $database = new Database();
        $db = $database->getConnection();
        $postModel = new Post($db);

        $postId = $_POST['post_id'] ?? 0;
        $content = $_POST['content'] ?? '';

        $post = $postModel->getById($postId);
        if (!$post) {
            echo json_encode(['success' => false, 'message' => 'Bài viết không tồn tại!']);
            exit;
        }

        // CHỈ TÁC GIẢ MỚI ĐƯỢC SỬA
        if ($post['author_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa bài viết này!']);
            exit;
        }

        if (empty(trim($content))) {
            echo json_encode(['success' => false, 'message' => 'Nội dung không được để trống!']);
            exit;
        }

        if ($postModel->update($postId, htmlspecialchars($content))) {
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật bài viết.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL!']);
        }
        exit;
    }
}
?>
