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

    // Lấy tin nhắn trong hội thoại (Có kiểm tra quyền truy cập)
    public function getMessages($conversationId, $userId, $limit = 50) {
        $query = "CALL sp_GetConversationMessages(:cid, :lim, :uid)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cid', $conversationId);
        $stmt->bindParam(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Danh sách hội thoại của user (kèm tin nhắn cuối + số tin nhắn chưa đọc)
    public function getConversations($userId) {
        $query = "SELECT c.id, c.type, c.name as group_name, c.avatar_url as group_avatar, c.created_by, c.requires_approval,
                  lm.content as last_message,
                  lm.created_at as last_time,
                  partner.full_name as partner_name,
                  partner.avatar_url as partner_avatar,
                  partner.id as partner_id,
                  ga1.avatar_url as group_avatar_1,
                  ga2.avatar_url as group_avatar_2,
                  COALESCE(unread.unread_count, 0) as unread_count
                  FROM conversations c
                  JOIN conversation_members cm ON c.id = cm.conversation_id AND cm.user_id = :uid1
                  -- Last message: JOIN với derived table lấy tin nhắn mới nhất
                  LEFT JOIN (
                      SELECT m1.conversation_id, m1.content, m1.created_at
                      FROM messages m1
                      INNER JOIN (
                          SELECT conversation_id, MAX(id) as max_id FROM messages GROUP BY conversation_id
                      ) m2 ON m1.id = m2.max_id
                  ) lm ON lm.conversation_id = c.id
                  -- Partner info for Direct conversations
                  LEFT JOIN conversation_members cm_partner ON cm_partner.conversation_id = c.id 
                      AND cm_partner.user_id != :uid2 AND c.type = 'Direct'
                  LEFT JOIN users partner ON partner.id = cm_partner.user_id
                  -- Group avatars (lấy 2 thành viên đầu tiên)
                  LEFT JOIN (
                      SELECT cma.conversation_id, u.avatar_url,
                             ROW_NUMBER() OVER (PARTITION BY cma.conversation_id ORDER BY cma.user_id) as rn
                      FROM conversation_members cma
                      JOIN users u ON cma.user_id = u.id
                      JOIN conversations cv ON cma.conversation_id = cv.id AND cv.type = 'Group'
                  ) ga1 ON ga1.conversation_id = c.id AND ga1.rn = 1
                  LEFT JOIN (
                      SELECT cma.conversation_id, u.avatar_url,
                             ROW_NUMBER() OVER (PARTITION BY cma.conversation_id ORDER BY cma.user_id) as rn
                      FROM conversation_members cma
                      JOIN users u ON cma.user_id = u.id
                      JOIN conversations cv ON cma.conversation_id = cv.id AND cv.type = 'Group'
                  ) ga2 ON ga2.conversation_id = c.id AND ga2.rn = 2
                  -- Unread count
                  LEFT JOIN (
                      SELECT m.conversation_id, COUNT(*) as unread_count
                      FROM messages m
                      JOIN conversation_members cmr ON m.conversation_id = cmr.conversation_id
                          AND cmr.user_id = :uid_unread
                      WHERE m.created_at > cmr.last_read_at
                        AND m.sender_id != :uid_sender
                      GROUP BY m.conversation_id
                  ) unread ON unread.conversation_id = c.id
                  ORDER BY lm.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':uid1' => $userId, ':uid2' => $userId,
            ':uid_unread' => $userId, ':uid_sender' => $userId
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

    public function updateLastRead($conversationId, $userId) {
        $query = "CALL sp_MarkMessagesAsRead(:cid, :uid)";
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
