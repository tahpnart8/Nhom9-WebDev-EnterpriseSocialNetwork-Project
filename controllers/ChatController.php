<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/User.php';

class ChatController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
    }

    // Trang Chat chính
    public function index() {
        $this->checkAuth();
        $pageTitle = "Tin nhắn";
        
        $msgModel = new Message($this->db);
        $userModel = new User($this->db);
        
        // Cập nhật đã đọc trước khi lấy danh sách
        $activeConvId = $_GET['conv_id'] ?? null;
        if ($activeConvId) {
            $msgModel->updateLastRead($activeConvId, $_SESSION['user_id']);
        }

        $conversations = $msgModel->getConversations($_SESSION['user_id']);
        
        // Lấy danh sách tất cả users để bắt đầu hội thoại mới
        $allUsersStmt = $userModel->getAllUsersWithDetails();
        $allUsers = $allUsersStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Nếu đang mở 1 cuộc hội thoại cụ thể
        $activeConvId = $_GET['conv_id'] ?? null;
        $activeMessages = [];
        $activePerson = null;
        
        if ($activeConvId) {
            $activeMessages = $msgModel->getMessages($activeConvId);
        }
        
        // Hoặc mở hội thoại với 1 user cụ thể
        $withUserId = $_GET['with'] ?? null;
        if ($withUserId && !$activeConvId) {
            $activeConvId = $msgModel->getOrCreateConversation($_SESSION['user_id'], $withUserId);
            $activeMessages = $msgModel->getMessages($activeConvId);
            // Đánh dấu đã đọc
            $msgModel->updateLastRead($activeConvId, $_SESSION['user_id']);
        }
        
        require_once __DIR__ . '/../views/chat/index.php';
    }

    // API: Gửi tin nhắn
    public function sendMessage() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit;
        }
        
        $msgModel = new Message($this->db);
        $convId = $_POST['conversation_id'] ?? 0;
        $content = htmlspecialchars(trim($_POST['content'] ?? ''));
        
        if (empty($content) || empty($convId)) {
            echo json_encode(['success' => false, 'message' => 'Nội dung trống!']);
            exit;
        }
        
        $result = $msgModel->send($convId, $_SESSION['user_id'], $content);
        echo json_encode(['success' => $result]);
        exit;
    }

    // API: Polling tin nhắn mới
    public function fetchMessages() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([]);
            exit;
        }
        
        $msgModel = new Message($this->db);
        $convId = $_GET['conv_id'] ?? 0;
        $messages = $msgModel->getMessages($convId);
        
        // Cập nhật trạng thái đã đọc khi polling bài viết
        if ($convId) {
            $msgModel->updateLastRead($convId, $_SESSION['user_id']);
        }

        echo json_encode($messages);
        exit;
    }

    // API: Lấy số lượng cuộc hội thoại chưa đọc (Dùng cho Header)
    public function fetchUnreadCount() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['unread_conversations' => 0]);
            exit;
        }

        $msgModel = new Message($this->db);
        $count = $msgModel->getTotalUnreadConversationCount($_SESSION['user_id']);
        echo json_encode(['unread_conversations' => (int)$count]);
        exit;
    }
}
