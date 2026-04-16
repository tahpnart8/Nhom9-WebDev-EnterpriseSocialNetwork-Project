<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/CloudStorage.php';

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

        $isAjaxNav = isset($_GET['ajax']) && $_GET['ajax'] === 'spanav';
        
        $activeConvId = $_GET['conv_id'] ?? null;
        $activeMessages = [];
        $activeGroupMembers = [];
        $activeConv = null;
        
        if ($activeConvId) {
            $activeMessages = $msgModel->getMessages($activeConvId, $_SESSION['user_id']);
            $activeConv = $msgModel->getConversationDetail($activeConvId);
            if ($activeConv && $activeConv['type'] === 'Group') {
                $activeGroupMembers = $msgModel->getGroupMembers($activeConvId);
            }
        }
        
        $withUserId = $_GET['with'] ?? null;
        if ($withUserId && !$activeConvId) {
            $activeConvId = $msgModel->getOrCreateConversation($_SESSION['user_id'], $withUserId);
            $activeMessages = $msgModel->getMessages($activeConvId, $_SESSION['user_id']);
            $activeConv = $msgModel->getConversationDetail($activeConvId);
            $msgModel->updateLastRead($activeConvId, $_SESSION['user_id']);
        }

        // Tối ưu hóa tải cho SPA
        $conversations = $msgModel->getConversations($_SESSION['user_id']);
        $allUsers = [];
        if (!$isAjaxNav) {
            $allUsersStmt = $userModel->getAllUsersWithDetails();
            $allUsers = $allUsersStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        require_once __DIR__ . '/../views/chat/index.php';
    }

    // API: Tạo nhóm
    public function api_create_group() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) exit;
        $name = $_POST['name'] ?? 'Nhóm mới';
        $members = $_POST['members'] ?? [];
        $msgModel = new Message($this->db);
        $convId = $msgModel->createGroup($name, $_SESSION['user_id'], $members);
        echo json_encode(['success' => (bool)$convId, 'conv_id' => $convId]);
        exit;
    }

    // API: Lấy thông tin nhóm & Yêu cầu chờ duyệt
    public function api_get_group_info() {
        header('Content-Type: application/json');
        $convId = $_GET['conv_id'] ?? 0;
        $msgModel = new Message($this->db);
        $conv = $msgModel->getConversationDetail($convId);
        $members = $msgModel->getGroupMembers($convId);
        $requests = [];
        if ($conv['created_by'] == $_SESSION['user_id']) $requests = $msgModel->getPendingRequests($convId);
        echo json_encode(['success' => true, 'info' => $conv, 'members' => $members, 'requests' => $requests]);
        exit;
    }

    // API: Thêm thành viên
    public function api_manage_members() {
        header('Content-Type: application/json');
        $convId = $_POST['conversation_id'] ?? 0;
        $memberIds = $_POST['members'] ?? [];
        $msgModel = new Message($this->db);
        $result = $msgModel->addOrRequestMembers($convId, $memberIds, $_SESSION['user_id']);
        echo json_encode(['success' => $result]);
        exit;
    }

    // API: Duyệt/Từ chối yêu cầu
    public function api_handle_membership_request() {
        header('Content-Type: application/json');
        $requestId = $_POST['request_id'] ?? 0;
        $status = $_POST['status'] ?? 'approved';
        $msgModel = new Message($this->db);
        $result = $msgModel->handleMembershipRequest($requestId, $status);
        echo json_encode(['success' => $result]);
        exit;
    }

    // API: Cập nhật cài đặt nhóm
    public function api_update_group_settings() {
        header('Content-Type: application/json');
        $convId = $_POST['conversation_id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $approval = $_POST['requires_approval'] ?? 0;
        $avatarBase64 = $_POST['avatar_base64'] ?? null;

        $msgModel = new Message($this->db);
        $avatarUrl = null;
        if ($avatarBase64 && strpos($avatarBase64, 'data:image') === 0) {
            $cloudStorage = new CloudStorage();
            $avatarUrl = $cloudStorage->uploadBase64Image($avatarBase64);
        }

        $result = $msgModel->updateGroupSettings($convId, $name, $approval, $avatarUrl);
        echo json_encode(['success' => $result]);
        exit;
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
        
        if (isset($_POST['type']) && $_POST['type'] === 'image' && !empty($_POST['base64'])) {
            $cloudStorage = new CloudStorage();
            $imageUrl = $cloudStorage->uploadBase64Image($_POST['base64']);
            if ($imageUrl) {
                $content = '[IMAGE:' . $imageUrl . ']';
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi upload ảnh đám mây.']);
                exit;
            }
        } else {
            $content = htmlspecialchars(trim($_POST['content'] ?? ''));
        }
        
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
        $messages = $msgModel->getMessages($convId, $_SESSION['user_id']);
        
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

    // API: Delta fetching - Chỉ lấy tin nhắn MỚI hơn since_id (Real-time)
    public function fetchNewMessages() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([]);
            exit;
        }
        
        $msgModel = new Message($this->db);
        $convId = $_GET['conv_id'] ?? 0;
        $sinceId = (int)($_GET['since_id'] ?? 0);
        
        $messages = $msgModel->getNewMessages($convId, $sinceId, $_SESSION['user_id']);
        
        // Cập nhật trạng thái đã đọc khi có tin mới
        if ($convId && !empty($messages)) {
            $msgModel->updateLastRead($convId, $_SESSION['user_id']);
        }

        echo json_encode($messages);
        exit;
    }

    // API: Heartbeat - Trả về counts nhanh cho notification + chat badges (Siêu nhẹ)
    public function heartbeat() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['noti_count' => 0, 'chat_count' => 0]);
            exit;
        }

        $query = "CALL sp_Heartbeat(:uid)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uid', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        echo json_encode([
            'noti_count' => (int)($result['noti_count'] ?? 0),
            'chat_count' => (int)($result['chat_count'] ?? 0),
            'last_msg_id' => (int)($result['last_msg_id'] ?? 0)
        ]);
        exit;
    }

    // API: Lazy Loading - Tải thêm tin nhắn cũ
    public function fetchOlderMessages() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([]); exit;
        }
        
        $msgModel = new Message($this->db);
        $convId = $_GET['conv_id'] ?? 0;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        
        // Tải 30 bản ghi cũ hơn mỗi lần
        $messages = $msgModel->getMessages($convId, $_SESSION['user_id'], 30, $offset);
        echo json_encode($messages);
        exit;
    }

    // API: Lấy lại danh sách cột Sidebar Trái (Dành cho Real-time Update danh sách chat)
    public function fetchSidebarChats() {
        if (!isset($_SESSION['user_id'])) exit;
        $msgModel = new Message($this->db);
        $conversations = $msgModel->getConversations($_SESSION['user_id']);
        
        // Trả về HTML đã gen từ view hoặc mảng dữ liệu (Ở đây tốt nhất là trả về danh sách dữ liệu JSON để dễ build bằng JS)
        header('Content-Type: application/json');
        echo json_encode($conversations);
        exit;
    }
}

