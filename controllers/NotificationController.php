<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Notification.php';

class NotificationController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // API: Lấy thông báo chưa đọc (JSON cho polling)
    public function fetchUnread() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['count' => 0, 'items' => []]);
            exit;
        }
        
        $model = new Notification($this->db);
        $count = $model->countUnread($_SESSION['user_id']);
        $items = $model->getUnread($_SESSION['user_id']);
        
        echo json_encode(['count' => $count, 'items' => $items]);
        exit;
    }

    // API: Đánh dấu tất cả đã đọc
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

    // Helper tĩnh: Tạo thông báo nhanh từ bất kỳ Controller nào
    public static function pushNotification($db, $type, $triggerUserId, $content, $targetUrl, $recipientIds) {
        $model = new Notification($db);
        return $model->create($type, $triggerUserId, $content, $targetUrl, $recipientIds);
    }
}
