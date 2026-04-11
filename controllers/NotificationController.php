<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Notification.php';

class NotificationController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // API: Lấy TẤT CẢ thông báo (đã đọc + chưa đọc) + unread_count
    public function fetchAll() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['unread_count' => 0, 'items' => []]);
            exit;
        }

        $model = new Notification($this->db);
        $unreadCount = $model->countUnread($_SESSION['user_id']);
        $items = $model->getAllForUser($_SESSION['user_id']);

        echo json_encode(['unread_count' => (int)$unreadCount, 'items' => $items]);
        exit;
    }

    // Giữ lại tên cũ cho backwards compatibility
    public function fetchUnread() {
        return $this->fetchAll();
    }

    // API: Đánh dấu TẤT CẢ đã đọc
    public function markAllRead() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit;
        }

        $model = new Notification($this->db);
        $model->markAllAsRead($_SESSION['user_id']);
        echo json_encode(['success' => true]);
        exit;
    }

    // API: Đánh dấu 1 thông báo đã đọc
    public function markOneRead() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit;
        }

        $notiId = $_POST['notification_id'] ?? 0;
        $model = new Notification($this->db);
        $model->markAsRead($notiId, $_SESSION['user_id']);
        echo json_encode(['success' => true]);
        exit;
    }

    // Helper tĩnh
    public static function pushNotification($db, $type, $triggerUserId, $content, $targetUrl, $recipientIds) {
        $model = new Notification($db);
        return $model->create($type, $triggerUserId, $content, $targetUrl, $recipientIds);
    }
}
