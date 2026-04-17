<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Comment.php';

class SocialController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
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
        $searchQuery = $_GET['q'] ?? null;
        
        $departments = [];
        if (($channel === 'department') && ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 4)) {
            require_once __DIR__ . '/../models/Department.php';
            $deptModel = new Department($this->db);
            $departments = $deptModel->getAll($_SESSION['company_id'])->fetchAll(PDO::FETCH_ASSOC);
        }

        // Fetch feed using Role ID & Department ID & Current User ID
        $companyId = $_SESSION['company_id'];
        $feed = $postModel->getFeed($_SESSION['role_id'], $_SESSION['department_id'] ?? null, $_SESSION['user_id'], $companyId, $channel, $dept_id_filter, $searchQuery);
        
        // Lấy Bảng xếp hạng (Leaderboard) sử dụng Procedure (TÍNH NĂNG MỚI)
        $leaderboard = [];
        if (isset($_SESSION['department_id'])) {
            $queryLB = "CALL sp_GetLeaderboard(:dept_id)";
            $stmtLB = $this->db->prepare($queryLB);
            $stmtLB->bindParam(':dept_id', $_SESSION['department_id']);
            $stmtLB->execute();
            $leaderboard = $stmtLB->fetchAll(PDO::FETCH_ASSOC);
            $stmtLB->closeCursor();
        }

        require_once __DIR__ . '/../views/social/index.php';
    }

    // API: Tìm kiếm bài viết (cho "Tải trang nhưng nhanh")
    public function apiSearchPosts() {
        header('Content-Type: application/json');
        if(!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }

        $postModel = new Post($this->db);
        $searchQuery = $_GET['q'] ?? '';
        $channel = $_GET['channel'] ?? 'public';
        $dept_id_filter = $_GET['dept_id'] ?? null;
        $companyId = $_SESSION['company_id'];

        $posts = $postModel->getFeed($_SESSION['role_id'], $_SESSION['department_id'] ?? null, $_SESSION['user_id'], $companyId, $channel, $dept_id_filter, $searchQuery);
        
        echo json_encode(['success' => true, 'data' => $posts]);
        exit;
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

        $postId = $postModel->create($author_id, $department_id, htmlspecialchars($content), $visibility, $_SESSION['company_id']);
        
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
        $companyId = $_SESSION['company_id'];
        $post = $postModel->getById($postId, $companyId);

        if (!$post) {
            echo json_encode(['success' => false, 'message' => 'Bài viết không tồn tại!']);
            exit;
        }
        
        $isAuthor = ($post['author_id'] == $_SESSION['user_id']);
        $isAdminOrCEO = ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 4);

        if ($isAuthor || $isAdminOrCEO) {
            if ($postModel->delete($postId, $companyId)) {
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
        $companyId = $_SESSION['company_id'];

        $post = $postModel->getById($postId, $companyId);
        if (!$post) {
            echo json_encode(['success' => false, 'message' => 'Bài viết không tồn tại!']);
            exit;
        }

        if ($post['author_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa bài viết này!']);
            exit;
        }

        if ($postModel->update($postId, htmlspecialchars($content), $companyId)) {
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
            $companyId = $_SESSION['company_id'];
            $post = $postModel->getById($postId, $companyId);
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
                        [$post['author_id']],
                        $companyId
                    );
                }
            } else if ($action === 'deleted') {
                // Gỡ thông báo nếu là bỏ thích
                $notiModel = new Notification($this->db);
                $notiModel->removeSocialNotification('SOCIAL_LIKE', $_SESSION['user_id'], $targetUrl, $companyId);
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
        $comments = $commentModel->getByPostId($postId, $_SESSION['user_id'] ?? 0, $_SESSION['company_id']);
        echo json_encode(['success' => true, 'data' => $comments]);
        exit;
    }

    // API: Gửi bình luận (Tạo bình luận)
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
        $companyId = $_SESSION['company_id'];
        $newCommentId = $commentModel->create($postId, $_SESSION['user_id'], htmlspecialchars($content), $companyId, $parentId);
        if ($newCommentId) {
            // Gửi thông báo đến chủ bài viết hoặc chủ bình luận cha
            require_once __DIR__ . '/NotificationController.php';
            $postModel = new Post($this->db);
            $post = $postModel->getById($postId, $companyId);
            
            if ($post && $post['author_id'] != $_SESSION['user_id']) {
                NotificationController::pushNotification(
                    $this->db,
                    'SOCIAL_COMMENT',
                    $_SESSION['user_id'],
                    $_SESSION['full_name'] . " đã bình luận về bài viết của bạn.",
                    "index.php?action=social&post_id=" . $postId . "#comment-" . $newCommentId,
                    [$post['author_id']],
                    $companyId
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
        $companyId = $_SESSION['company_id'];

        $comment = $commentModel->getById($commentId, $companyId);
        if (!$comment || $comment['user_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền sửa!']);
            exit;
        }

        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Nội dung trống!']);
            exit;
        }

        if ($commentModel->update($commentId, htmlspecialchars($content), $companyId)) {
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
        $companyId = $_SESSION['company_id'];

        $comment = $commentModel->getById($commentId, $companyId);
        if (!$comment) {
            echo json_encode(['success' => false, 'message' => 'Bình luận không tồn tại!']);
            exit;
        }

        $isAuthor = ($comment['user_id'] == $_SESSION['user_id']);
        $isAdminOrCEO = ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 4);

        if ($isAuthor || $isAdminOrCEO) {
            $postId = $comment['post_id'];
            $targetUrl = "index.php?action=social&post_id=" . $postId . "#comment-" . $commentId;
            
            if ($commentModel->delete($commentId, $companyId)) {
                // Gỡ thông báo bình luận nếu có
                require_once __DIR__ . '/../models/Notification.php';
                $notiModel = new Notification($this->db);
                $notiModel->removeSocialNotification('SOCIAL_COMMENT', $comment['user_id'], $targetUrl, $companyId);
                
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
        
        // 1. Lấy thông tin bài viết (Tối ưu: LEFT JOIN thay correlated subqueries)
        $query = "SELECT p.*, u.full_name, u.avatar_url, m.media_url, m.media_type, r.role_name,
                  COALESCE(pr_count.like_count, 0) as like_count,
                  CASE WHEN my_pr.post_id IS NOT NULL THEN 1 ELSE 0 END as is_liked,
                  COALESCE(cmt_count.comment_count, 0) as comment_count
                  FROM posts p
                  JOIN users u ON p.author_id = u.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  LEFT JOIN post_media m ON p.id = m.post_id
                  LEFT JOIN (
                      SELECT post_id, COUNT(*) as like_count FROM post_reactions GROUP BY post_id
                  ) pr_count ON pr_count.post_id = p.id
                  LEFT JOIN post_reactions my_pr ON my_pr.post_id = p.id AND my_pr.user_id = :current_user
                  LEFT JOIN (
                      SELECT post_id, COUNT(*) as comment_count FROM comments GROUP BY post_id
                  ) cmt_count ON cmt_count.post_id = p.id
                  WHERE p.id = :post_id";
        $stmt = $this->db->prepare($query);
        $companyId = $_SESSION['company_id'];
        $stmt->execute([':current_user' => $_SESSION['user_id'], ':post_id' => $postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post || $post['company_id'] != $companyId) {
            echo json_encode(['success' => false, 'message' => 'Bài viết đã bị xóa hoặc không thuộc công ty bạn!']);
            exit;
        }

        // 2. Lấy bình luận
        $comments = $commentModel->getByPostId($postId, $_SESSION['user_id'], $companyId);
        
        echo json_encode([
            'success' => true,
            'current_user_id' => $_SESSION['user_id'],
            'post' => $post,
            'comments' => $comments
        ]);
        exit;
    }
}
