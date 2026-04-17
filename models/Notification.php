<?php
class Notification {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Tạo thông báo và broadcast cho danh sách user
    public function create($type, $triggerUserId, $content, $targetUrl, $company_id, $recipientIds = []) {
        // Kiểm tra chống trùng lặp (Duplicate Prevention)
        $checkQuery = "SELECT id FROM notifications 
                       WHERE type = :type 
                         AND trigger_user_id = :trigger_user_id 
                         AND content = :content 
                         AND target_url = :target_url 
                         AND company_id = :company_id
                         AND created_at >= NOW() - INTERVAL 1 MINUTE";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->execute([
            ':type' => $type,
            ':trigger_user_id' => $triggerUserId,
            ':content' => $content,
            ':target_url' => $targetUrl,
            ':company_id' => $company_id
        ]);
        if ($row = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['id'];
        }

        $query = "INSERT INTO notifications (type, trigger_user_id, content, target_url, company_id) 
                  VALUES (:type, :trigger_user_id, :content, :target_url, :company_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':trigger_user_id', $triggerUserId);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':target_url', $targetUrl);
        $stmt->bindParam(':company_id', $company_id);
        
        if ($stmt->execute()) {
            $notiId = $this->conn->lastInsertId();
            // Batch INSERT thay vì N queries riêng lẻ
            $validRecipients = array_filter($recipientIds, function($uid) use ($triggerUserId) {
                return $uid != $triggerUserId;
            });
            if (!empty($validRecipients)) {
                $values = [];
                $params = [];
                $i = 0;
                foreach ($validRecipients as $uid) {
                    $values[] = "(:nid{$i}, :uid{$i})";
                    $params[":nid{$i}"] = $notiId;
                    $params[":uid{$i}"] = $uid;
                    $i++;
                }
                $batchQuery = "INSERT INTO notification_user (notification_id, user_id) VALUES " . implode(',', $values);
                $batchStmt = $this->conn->prepare($batchQuery);
                $batchStmt->execute($params);
            }
            return $notiId;
        }
        return false;
    }

    // Lấy thông báo chưa đọc
    public function getUnread($userId, $company_id) {
        $query = "SELECT n.*, nu.is_read, u.full_name as trigger_name
                  FROM notification_user nu
                  JOIN notifications n ON nu.notification_id = n.id
                  LEFT JOIN users u ON n.trigger_user_id = u.id
                  WHERE nu.user_id = :uid AND nu.is_read = 0 AND n.company_id = :company_id
                  ORDER BY n.created_at DESC
                  LIMIT 20";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':company_id', $company_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // MỚI: Lấy TẤT CẢ thông báo (đã đọc + chưa đọc), limit 30
    public function getAllForUser($userId, $company_id) {
        $query = "SELECT n.*, nu.is_read, nu.notification_id, u.full_name as trigger_name
                  FROM notification_user nu
                  JOIN notifications n ON nu.notification_id = n.id
                  LEFT JOIN users u ON n.trigger_user_id = u.id
                  WHERE nu.user_id = :uid AND n.company_id = :company_id
                  ORDER BY n.created_at DESC
                  LIMIT 30";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':company_id', $company_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Đếm chưa đọc
    public function countUnread($userId, $company_id) {
        $query = "SELECT COUNT(*) as cnt FROM notification_user nu
                  JOIN notifications n ON nu.notification_id = n.id
                  WHERE nu.user_id = :uid AND nu.is_read = 0 AND n.company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':company_id', $company_id);
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
    public function getDepartmentUserIds($departmentId, $company_id) {
        $query = "SELECT id FROM users WHERE department_id = :dept_id AND company_id = :company_id AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dept_id', $departmentId);
        $stmt->bindParam(':company_id', $company_id);
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');
    }
    // Xóa thông báo xã hội khi rút lại tương tác (ví dụ: Unlike)
    public function removeSocialNotification($type, $triggerUserId, $targetUrl, $company_id) {
        // Tìm ID của thông báo sử dụng LIKE để tránh lỗi khớp chuỗi tuyệt đối
        $query = "SELECT id FROM notifications 
                  WHERE type = :type AND trigger_user_id = :sid AND target_url LIKE :url AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':type' => $type, ':sid' => $triggerUserId, ':url' => '%' . $targetUrl . '%', ':company_id' => $company_id]);
        $noti = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($noti) {
            $notiId = $noti['id'];
            // Xóa ở bảng quan hệ trước
            $this->conn->prepare("DELETE FROM notification_user WHERE notification_id = :nid")->execute([':nid' => $notiId]);
            // Xóa ở bảng chính
            $this->conn->prepare("DELETE FROM notifications WHERE id = :id")->execute([':id' => $notiId]);
            return true;
        }
        return false;
    }
}
?>
