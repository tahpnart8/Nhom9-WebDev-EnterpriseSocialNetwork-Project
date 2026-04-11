<?php
class Notification {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Tạo thông báo và broadcast cho danh sách user
    public function create($type, $triggerUserId, $content, $targetUrl, $recipientIds = []) {
        $query = "INSERT INTO notifications (type, trigger_user_id, content, target_url) 
                  VALUES (:type, :trigger_user_id, :content, :target_url)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':trigger_user_id', $triggerUserId);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':target_url', $targetUrl);
        
        if ($stmt->execute()) {
            $notiId = $this->conn->lastInsertId();
            foreach ($recipientIds as $uid) {
                if ($uid == $triggerUserId) continue;
                $q2 = "INSERT INTO notification_user (notification_id, user_id) VALUES (:nid, :uid)";
                $s2 = $this->conn->prepare($q2);
                $s2->bindParam(':nid', $notiId);
                $s2->bindParam(':uid', $uid);
                $s2->execute();
            }
            return $notiId;
        }
        return false;
    }

    // Lấy thông báo chưa đọc
    public function getUnread($userId) {
        $query = "SELECT n.*, nu.is_read, u.full_name as trigger_name
                  FROM notification_user nu
                  JOIN notifications n ON nu.notification_id = n.id
                  LEFT JOIN users u ON n.trigger_user_id = u.id
                  WHERE nu.user_id = :uid AND nu.is_read = 0
                  ORDER BY n.created_at DESC
                  LIMIT 20";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // MỚI: Lấy TẤT CẢ thông báo (đã đọc + chưa đọc), limit 30
    public function getAllForUser($userId) {
        $query = "SELECT n.*, nu.is_read, nu.notification_id, u.full_name as trigger_name
                  FROM notification_user nu
                  JOIN notifications n ON nu.notification_id = n.id
                  LEFT JOIN users u ON n.trigger_user_id = u.id
                  WHERE nu.user_id = :uid
                  ORDER BY n.created_at DESC
                  LIMIT 30";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Đếm chưa đọc
    public function countUnread($userId) {
        $query = "SELECT COUNT(*) as cnt FROM notification_user 
                  WHERE user_id = :uid AND is_read = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['cnt'] ?? 0;
    }

    // Đánh dấu 1 thông báo đã đọc
    public function markAsRead($notificationId, $userId) {
        $query = "UPDATE notification_user SET is_read = 1, read_at = NOW() 
                  WHERE notification_id = :nid AND user_id = :uid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nid', $notificationId);
        $stmt->bindParam(':uid', $userId);
        return $stmt->execute();
    }

    // Đánh dấu tất cả đã đọc
    public function markAllAsRead($userId) {
        $query = "UPDATE notification_user SET is_read = 1, read_at = NOW() 
                  WHERE user_id = :uid AND is_read = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        return $stmt->execute();
    }

    // Lấy danh sách user cùng phòng ban
    public function getDepartmentUserIds($departmentId) {
        $query = "SELECT id FROM users WHERE department_id = :dept_id AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $departmentId);
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');
    }
}
?>
