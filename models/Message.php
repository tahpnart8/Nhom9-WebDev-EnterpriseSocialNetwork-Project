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
        $query = "SELECT m.*, u.full_name as sender_name, u.avatar_url as sender_avatar
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
        $query = "SELECT c.id, c.type, c.name as group_name, c.avatar_url as group_avatar, c.created_by, c.requires_approval,
                  (SELECT content FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                  (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_time,
                  -- Lấy thông tin đối tác cho trò chuyện Direct
                  (SELECT u.full_name FROM conversation_members cm2 
                   JOIN users u ON cm2.user_id = u.id 
                   WHERE cm2.conversation_id = c.id AND cm2.user_id != :uid1 
                   AND c.type = 'Direct' LIMIT 1) as partner_name,
                  (SELECT u.avatar_url FROM conversation_members cm3 
                   JOIN users u ON cm3.user_id = u.id 
                   WHERE cm3.conversation_id = c.id AND cm3.user_id != :uid2 
                   AND c.type = 'Direct' LIMIT 1) as partner_avatar,
                  (SELECT cm4.user_id FROM conversation_members cm4 
                   WHERE cm4.conversation_id = c.id AND cm4.user_id != :uid3 
                   AND c.type = 'Direct' LIMIT 1) as partner_id,
                  -- Lấy avatar của các thành viên làm ảnh ghép cho Nhóm nếu chưa có ảnh
                  (SELECT u.avatar_url FROM conversation_members cma 
                   JOIN users u ON cma.user_id = u.id 
                   WHERE cma.conversation_id = c.id AND c.type = 'Group' LIMIT 1) as group_avatar_1,
                  (SELECT u.avatar_url FROM conversation_members cmb 
                   JOIN users u ON cmb.user_id = u.id 
                   WHERE cmb.conversation_id = c.id AND c.type = 'Group' LIMIT 1 OFFSET 1) as group_avatar_2,
                  (SELECT COUNT(*) FROM messages m 
                   JOIN conversation_members cm_read ON m.conversation_id = cm_read.conversation_id 
                   WHERE m.conversation_id = c.id 
                   AND cm_read.user_id = :uid_unread 
                   AND m.created_at > cm_read.last_read_at
                   AND m.sender_id != :uid_sender
                  ) as unread_count
                  FROM conversations c
                  JOIN conversation_members cm ON c.id = cm.conversation_id
                  WHERE cm.user_id = :uid4
                  ORDER BY last_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':uid1' => $userId, ':uid2' => $userId, ':uid3' => $userId,
            ':uid_unread' => $userId, ':uid_sender' => $userId, ':uid4' => $userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tạo nhóm mới
    public function createGroup($name, $creatorId, $memberIds, $avatarUrl = null) {
        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare("INSERT INTO conversations (type, name, avatar_url, created_by) VALUES ('Group', :name, :avatar, :creator)");
            $stmt->execute([':name' => $name, ':avatar' => $avatarUrl, ':creator' => $creatorId]);
            $convId = $this->conn->lastInsertId();
            $ins = $this->conn->prepare("INSERT INTO conversation_members (conversation_id, user_id, role) VALUES (:cid, :uid, 'admin')");
            $ins->execute([':cid' => $convId, ':uid' => $creatorId]);
            $insMem = $this->conn->prepare("INSERT INTO conversation_members (conversation_id, user_id, role) VALUES (:cid, :uid, 'member')");
            foreach ($memberIds as $mId) {
                if ($mId != $creatorId) $insMem->execute([':cid' => $convId, ':uid' => $mId]);
            }
            $this->conn->commit();
            return $convId;
        } catch (Exception $e) {
            $this->conn->rollBack(); return false;
        }
    }

    public function getGroupMembers($conversationId) {
        $query = "SELECT u.id, u.full_name, u.avatar_url, cm.role FROM conversation_members cm JOIN users u ON cm.user_id = u.id WHERE cm.conversation_id = :cid";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':cid' => $conversationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConversationDetail($id) {
        $stmt = $this->conn->prepare("SELECT * FROM conversations WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addOrRequestMembers($convId, $memberIds, $invitedBy) {
        $conv = $this->getConversationDetail($convId);
        $isCreator = ($conv['created_by'] == $invitedBy);
        foreach ($memberIds as $uid) {
            $check = $this->conn->prepare("SELECT 1 FROM conversation_members WHERE conversation_id = :cid AND user_id = :uid");
            $check->execute([':cid' => $convId, ':uid' => $uid]);
            if ($check->fetch()) continue;
            if (!$conv['requires_approval'] || $isCreator) {
                $ins = $this->conn->prepare("INSERT INTO conversation_members (conversation_id, user_id) VALUES (:cid, :uid)");
                $ins->execute([':cid' => $convId, ':uid' => $uid]);
            } else {
                $req = $this->conn->prepare("INSERT INTO membership_requests (conversation_id, user_id, invited_by) VALUES (:cid, :uid, :iby)");
                $req->execute([':cid' => $convId, ':uid' => $uid, ':iby' => $invitedBy]);
            }
        }
        return true;
    }

    public function getPendingRequests($convId) {
        $query = "SELECT r.*, u.full_name as user_name, u.avatar_url as user_avatar, ib.full_name as inviter_name 
                  FROM membership_requests r JOIN users u ON r.user_id = u.id JOIN users ib ON r.invited_by = ib.id 
                  WHERE r.conversation_id = :cid AND r.status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':cid' => $convId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function handleMembershipRequest($requestId, $status) {
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare("UPDATE membership_requests SET status = :status WHERE id = :rid");
            $stmt->execute([':status' => $status, ':rid' => $requestId]);
            if ($status === 'approved') {
                $req = $this->conn->prepare("SELECT conversation_id, user_id FROM membership_requests WHERE id = :rid");
                $req->execute([':rid' => $requestId]);
                $r = $req->fetch(PDO::FETCH_ASSOC);
                $this->conn->prepare("INSERT INTO conversation_members (conversation_id, user_id) VALUES (?, ?)")->execute([$r['conversation_id'], $r['user_id']]);
            }
            $this->conn->commit(); return true;
        } catch (Exception $e) { $this->conn->rollBack(); return false; }
    }

    public function updateGroupSettings($convId, $name, $requiresApproval, $avatarUrl = null) {
        $params = [':name' => $name, ':approval' => $requiresApproval, ':cid' => $convId];
        $avatarUpdate = "";
        if ($avatarUrl) {
            $params[':avatar'] = $avatarUrl;
            $avatarUpdate = ", avatar_url = :avatar";
        }
        $sql = "UPDATE conversations SET name = :name, requires_approval = :approval $avatarUpdate WHERE id = :cid";
        return $this->conn->prepare($sql)->execute($params);
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
