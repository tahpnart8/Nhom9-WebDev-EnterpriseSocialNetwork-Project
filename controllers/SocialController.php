<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Comment.php';

class SocialController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Tự động tạo bảng comment_reactions nếu chưa có (Phát triển nhanh)
        $this->checkTables();
    }

    private function checkTables() {
        $sql = "CREATE TABLE IF NOT EXISTS comment_reactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            comment_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_comment_reaction (comment_id, user_id),
            FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE
        )";
        $this->db->exec($sql);
        
        try {
            // Tự động vá DB phòng trường hợp người dùng chưa nâng cấp chuẩn ENUM
            $this->db->exec("ALTER TABLE posts MODIFY visibility ENUM('Public', 'Department', 'Private', 'Announcement') DEFAULT 'Public'");
        } catch (Exception $e) {}
    }

    public function index() {
        if(!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $pageTitle = "Bảng tin Nội bộ";
        $postModel = new Post($this->db);
        
        $channel = $_GET['channel'] ?? 'public';
        $dept_id_filter = $_GET['dept_id'] ?? null;
        
        $departments = [];
        if (($channel === 'department') && ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 4)) {
            require_once __DIR__ . '/../models/Department.php';
            $deptModel = new Department($this->db);
            $departments = $deptModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fetch feed using Role ID & Department ID & Current User ID
        $feed = $postModel->getFeed($_SESSION['role_id'], $_SESSION['department_id'] ?? null, $_SESSION['user_id'], $channel, $dept_id_filter);
        
        require_once __DIR__ . '/../views/social/index.php';
    }

    public function createPost() {
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: Hết hạn phiên đăng nhập!']);
            exit;
        }

        $postModel = new Post($this->db);
        
        $content = $_POST['content'] ?? '';
        $visibility = $_POST['visibility'] ?? 'Public';
        
        // Add validations for new channels
        if ($visibility == 'Announcement' && $_SESSION['role_id'] != 1) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: Chỉ CEO mới có quyền đăng thông báo toàn công ty!']);
            exit;
        }
        
        // Ensure user is not CEO posting into department
        if ($visibility == 'Department' && $_SESSION['role_id'] == 1) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: CEO không được đăng bài vào kênh phòng ban!']);
            exit;
        }
        
        $author_id = $_SESSION['user_id'];
        $department_id = ($visibility == 'Department') ? $_SESSION['department_id'] : NULL;
        
        if (empty(trim($content))) {
            echo json_encode(['success' => false, 'message' => 'Nội dung không được để trống!']);
            exit;
        }

        $postId = $postModel->create($author_id, $department_id, htmlspecialchars($content), $visibility);
        
        if ($postId) {
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                require_once __DIR__ . '/../models/CloudStorage.php';
                $cloudStorage = new CloudStorage();
                $cloudUrl = $cloudStorage->uploadImage($_FILES['attachment']['tmp_name']);
                if ($cloudUrl !== false) {
                    $postModel->addMedia($postId, $cloudUrl);
                }
            }
            echo json_encode(['success' => true, 'message' => 'Đăng tải bản tin hoàn tất.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ cơ sở dữ liệu.']);
        }
        exit;
    }

    public function deletePost() {
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }

        header('Content-Type: application/json');
        $postModel = new Post($this->db);
        $postId = $_POST['post_id'] ?? 0;
        $post = $postModel->getById($postId);

        if (!$post) {
            echo json_encode(['success' => false, 'message' => 'Bài viết không tồn tại!']);
            exit;
        }
        
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

    public function editPost() {
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }

        header('Content-Type: application/json');
        $postModel = new Post($this->db);
        $postId = $_POST['post_id'] ?? 0;
        $content = $_POST['content'] ?? '';

        $post = $postModel->getById($postId);
        if (!$post) {
            echo json_encode(['success' => false, 'message' => 'Bài viết không tồn tại!']);
            exit;
        }

        if ($post['author_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa bài viết này!']);
            exit;
        }

        if ($postModel->update($postId, htmlspecialchars($content))) {
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật bài viết.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL!']);
        }
        exit;
    }

    // API: Thả/Bỏ tim bài viết
    public function togglePostReaction() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) exit;
        
        $postModel = new Post($this->db);
        $postId = $_POST['post_id'] ?? 0;
        
        $action = $postModel->toggleReaction($postId, $_SESSION['user_id']);
        if ($action) {
            require_once __DIR__ . '/NotificationController.php';
            $post = $postModel->getById($postId);
            $targetUrl = "index.php?action=social&post_id=" . $postId;

            if ($action === 'added') {
                // Gửi thông báo nếu là thả tim (mới)
                if ($post && $post['author_id'] != $_SESSION['user_id']) {
                    NotificationController::pushNotification(
                        $this->db, 
                        'SOCIAL_LIKE', 
                        $_SESSION['user_id'], 
                        $_SESSION['full_name'] . " đã thích bài viết của bạn.", 
                        $targetUrl, 
                        [$post['author_id']]
                    );
                }
            } else if ($action === 'deleted') {
                // Gỡ thông báo nếu là bỏ thích
                $notiModel = new Notification($this->db);
                $notiModel->removeSocialNotification('SOCIAL_LIKE', $_SESSION['user_id'], $targetUrl);
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // API: Lấy danh sách bình luận
    public function fetchComments() {
        header('Content-Type: application/json');
        $postId = $_GET['post_id'] ?? 0;
        $commentModel = new Comment($this->db);
        $comments = $commentModel->getByPostId($postId, $_SESSION['user_id'] ?? 0);
        echo json_encode(['success' => true, 'data' => $comments]);
        exit;
    }

    // API: Gửi bình luận
    public function addComment() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Hết hạn phiên!']);
            exit;
        }
        
        $postId = $_POST['post_id'] ?? 0;
        $parentId = $_POST['parent_id'] ?? NULL;
        $content = trim($_POST['content'] ?? '');
        
        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Nội dung trống!']);
            exit;
        }
        
        $commentModel = new Comment($this->db);
        $newCommentId = $commentModel->create($postId, $_SESSION['user_id'], htmlspecialchars($content), $parentId);
        if ($newCommentId) {
            // Gửi thông báo đến chủ bài viết hoặc chủ bình luận cha
            require_once __DIR__ . '/NotificationController.php';
            $postModel = new Post($this->db);
            $post = $postModel->getById($postId);
            
            if ($post && $post['author_id'] != $_SESSION['user_id']) {
                NotificationController::pushNotification(
                    $this->db,
                    'SOCIAL_COMMENT',
                    $_SESSION['user_id'],
                    $_SESSION['full_name'] . " đã bình luận về bài viết của bạn.",
                    "index.php?action=social&post_id=" . $postId . "#comment-" . $newCommentId,
                    [$post['author_id']]
                );
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // API: Thả/Bỏ tim bình luận
    public function toggleCommentReaction() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) exit;
        
        $commentId = $_POST['comment_id'] ?? 0;
        $commentModel = new Comment($this->db);
        
        if ($commentModel->toggleReaction($commentId, $_SESSION['user_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // API: Chỉnh sửa bình luận (Chỉ tác giả)
    public function editComment() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) exit;

        $commentId = $_POST['comment_id'] ?? 0;
        $content = trim($_POST['content'] ?? '');
        $commentModel = new Comment($this->db);

        $comment = $commentModel->getById($commentId);
        if (!$comment || $comment['user_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền sửa!']);
            exit;
        }

        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Nội dung trống!']);
            exit;
        }

        if ($commentModel->update($commentId, htmlspecialchars($content))) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // API: Xóa bình luận (Tác giả/CEO/Admin)
    public function deleteComment() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) exit;

        $commentId = $_POST['comment_id'] ?? 0;
        $commentModel = new Comment($this->db);

        $comment = $commentModel->getById($commentId);
        if (!$comment) {
            echo json_encode(['success' => false, 'message' => 'Bình luận không tồn tại!']);
            exit;
        }

        $isAuthor = ($comment['user_id'] == $_SESSION['user_id']);
        $isAdminOrCEO = ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 4);

        if ($isAuthor || $isAdminOrCEO) {
            $postId = $comment['post_id'];
            $targetUrl = "index.php?action=social&post_id=" . $postId . "#comment-" . $commentId;
            
            if ($commentModel->delete($commentId)) {
                // Gỡ thông báo bình luận nếu có
                require_once __DIR__ . '/../models/Notification.php';
                $notiModel = new Notification($this->db);
                $notiModel->removeSocialNotification('SOCIAL_COMMENT', $comment['user_id'], $targetUrl);
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Không có quyền xóa!']);
        }
        exit;
    }

    // API: Lấy danh sách người đã thích bài viết
    public function fetchPostLikers() {
        header('Content-Type: application/json');
        $postId = $_GET['post_id'] ?? 0;
        $postModel = new Post($this->db);
        $likers = $postModel->getReactions($postId);
        echo json_encode(['success' => true, 'data' => $likers]);
        exit;
    }

    // API: Lấy danh sách người đã thích bình luận
    public function fetchCommentLikers() {
        header('Content-Type: application/json');
        $commentId = $_GET['comment_id'] ?? 0;
        $commentModel = new Comment($this->db);
        $likers = $commentModel->getReactions($commentId);
        echo json_encode(['success' => true, 'data' => $likers]);
        exit;
    }
    
    // API: Lấy chi tiết 1 bài viết kèm bình luận (cho Modal)
    public function fetchPostDetails() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) exit;
        
        $postId = $_GET['post_id'] ?? 0;
        $postModel = new Post($this->db);
        $commentModel = new Comment($this->db);
        
        // 1. Lấy thông tin bài viết
        $query = "SELECT p.*, u.full_name, u.avatar_url, m.media_url, m.media_type, r.role_name,
                  (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id) as like_count,
                  (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND user_id = :current_user) as is_liked,
                  (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
                  FROM posts p
                  JOIN users u ON p.author_id = u.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  LEFT JOIN post_media m ON p.id = m.post_id
                  WHERE p.id = :post_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':current_user' => $_SESSION['user_id'], ':post_id' => $postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            echo json_encode(['success' => false, 'message' => 'Bài viết đã bị xóa!']);
            exit;
        }

        // 2. Lấy bình luận
        $comments = $commentModel->getByPostId($postId, $_SESSION['user_id']);
        
        echo json_encode([
            'success' => true,
            'current_user_id' => $_SESSION['user_id'],
            'post' => $post,
            'comments' => $comments
        ]);
        exit;
    }
}
