<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/DriveStorage.php';

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
            // 2. Upload hình ảnh lên Google Drive Storage (Nếu có)
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                // Giới hạn chỉ nhận Image/Video MP4 nếu cần
                $drive = new DriveStorage();
                $uploadedMeta = $drive->uploadFile($_FILES['attachment']['tmp_name'], $_FILES['attachment']['type'], $_FILES['attachment']['name']);
                
                if ($uploadedMeta && isset($uploadedMeta['webViewLink'])) {
                    $mediaUrl = $uploadedMeta['webViewLink'];
                    // Chuyển link Preview của Google Drive sang dạng URL 
                    $mediaUrl = str_replace('/view?usp=drivesdk', '/preview', $mediaUrl);
                    $postModel->addMedia($postId, $mediaUrl);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Bài viết đã lưu nhưng Gdrive Error. Hãy xem lại JSON token.']);
                    exit;
                }
            }
            echo json_encode(['success' => true, 'message' => 'Đăng tải bản tin hoàn tất.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ cơ sở dữ liệu.']);
        }
        exit;
    }
}
?>
