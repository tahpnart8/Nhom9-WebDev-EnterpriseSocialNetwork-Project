<?php
class Message {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy hoặc tạo cuộc hội thoại Direct giữa 2 người
    public function getOrCreateConversation($userId1, $userId2) {
        // Tìm cuộc hội thoại Direct đã tồn tại
        $query = "SELECT cm1.conversation_id 
                  FROM conversation_members cm1
                  JOIN conversation_members cm2 ON cm1.conversation_id = cm2.conversation_id
                  JOIN conversations c ON cm1.conversation_id = c.id
                  WHERE cm1.user_id = :u1 AND cm2.user_id = :u2 AND c.type = 'Direct'
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':u1', $userId1);
        $stmt->bindParam(':u2', $userId2);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) return $row['conversation_id'];
        
        // Tạo mới
        $this->conn->prepare("INSERT INTO conversations (type) VALUES ('Direct')")->execute();
        $convId = $this->conn->lastInsertId();
        
        $ins = $this->conn->prepare("INSERT INTO conversation_members (conversation_id, user_id) VALUES (:cid, :uid)");
        $ins->execute([':cid' => $convId, ':uid' => $userId1]);
        $ins->execute([':cid' => $convId, ':uid' => $userId2]);
        
        return $convId;
    }

    // Gửi tin nhắn
    public function send($conversationId, $senderId, $content) {
        $query = "INSERT INTO messages (conversation_id, sender_id, content) 
                  VALUES (:cid, :sid, :content)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cid', $conversationId);
        $stmt->bindParam(':sid', $senderId);
        $stmt->bindParam(':content', $content);
        return $stmt->execute();
    }

    // Lấy tin nhắn trong hội thoại
    public function getMessages($conversationId, $limit = 50) {
        $query = "SELECT m.*, u.full_name as sender_name
                  FROM messages m
                  JOIN users u ON m.sender_id = u.id
                  WHERE m.conversation_id = :cid
                  ORDER BY m.created_at ASC
                  LIMIT :lim";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cid', $conversationId);
        $stmt->bindParam(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Danh sách hội thoại của user (kèm tin nhắn cuối + số tin nhắn chưa đọc)
    public function getConversations($userId) {
        $query = "SELECT c.id, c.type,
                  (SELECT content FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                  (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_time,
                  (SELECT full_name FROM users WHERE id = 
                    (SELECT user_id FROM conversation_members WHERE conversation_id = c.id AND user_id != :uid1 LIMIT 1)
                  ) as partner_name,
                  (SELECT avatar_url FROM users WHERE id = 
                    (SELECT user_id FROM conversation_members WHERE conversation_id = c.id AND user_id != :uid_avatar LIMIT 1)
                  ) as partner_avatar,
                  (SELECT user_id FROM conversation_members WHERE conversation_id = c.id AND user_id != :uid2 LIMIT 1) as partner_id,
                  (SELECT COUNT(*) FROM messages m 
                   JOIN conversation_members cm_read ON m.conversation_id = cm_read.conversation_id 
                   WHERE m.conversation_id = c.id 
                   AND cm_read.user_id = :uid_unread 
                   AND m.created_at > cm_read.last_read_at
                   AND m.sender_id != :uid_sender
                  ) as unread_count
                  FROM conversations c
                  JOIN conversation_members cm ON c.id = cm.conversation_id
                  WHERE cm.user_id = :uid3
                  ORDER BY last_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid1', $userId);
        $stmt->bindParam(':uid_avatar', $userId);
        $stmt->bindParam(':uid2', $userId);
        $stmt->bindParam(':uid_unread', $userId);
        $stmt->bindParam(':uid_sender', $userId);
        $stmt->bindParam(':uid3', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cập nhật thời điểm đọc tin nhắn cuối
    public function updateLastRead($conversationId, $userId) {
        $query = "UPDATE conversation_members SET last_read_at = NOW() 
                  WHERE conversation_id = :cid AND user_id = :uid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cid', $conversationId);
        $stmt->bindParam(':uid', $userId);
        return $stmt->execute();
    }

    // Đếm số lượng cuộc hội thoại có tin nhắn chưa đọc
    public function getTotalUnreadConversationCount($userId) {
        $query = "SELECT COUNT(*) as cnt FROM (
                    SELECT c.id
                    FROM conversations c
                    JOIN conversation_members cm ON c.id = cm.conversation_id
                    JOIN messages m ON c.id = m.conversation_id
                    WHERE cm.user_id = :uid 
                    AND m.created_at > cm.last_read_at
                    AND m.sender_id != :uid_sender
                    GROUP BY c.id
                  ) as t";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':uid_sender', $userId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['cnt'] ?? 0;
    }
}
?>
