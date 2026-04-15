<?php
class Comment {
    private $conn;
    private $table_name = "comments";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách bình luận của một bài viết (kèm thông tin user và số lượt tim)
    public function getByPostId($post_id, $current_user_id) {
        $query = "SELECT c.*, u.full_name, u.avatar_url,
                  (SELECT COUNT(*) FROM comment_reactions WHERE comment_id = c.id) as like_count,
                  CASE WHEN EXISTS(SELECT 1 FROM comment_reactions WHERE comment_id = c.id AND user_id = :current_user) THEN 1 ELSE 0 END as is_liked
                  FROM comments c
                  JOIN users u ON c.user_id = u.id
                  WHERE c.post_id = :post_id
                  ORDER BY c.created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':current_user', $current_user_id);
        $stmt->execute();
        
        $all_comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        // Phân cấp: Parent -> Children
        $parents = [];
        $children = [];
        foreach ($all_comments as $c) {
            if ($c['parent_comment_id'] == NULL) {
                $parents[$c['id']] = $c;
                $parents[$c['id']]['replies'] = [];
            } else {
                $children[] = $c;
            }
        }
        
        foreach ($children as $child) {
            if (isset($parents[$child['parent_comment_id']])) {
                $parents[$child['parent_comment_id']]['replies'][] = $child;
            }
        }
        
        return array_values($parents);
    }

    // Thêm bình luận mới
    public function create($post_id, $user_id, $content, $parent_id = NULL) {
        $query = "INSERT INTO " . $this->table_name . " (post_id, user_id, content, parent_comment_id) 
                  VALUES (:post_id, :user_id, :content, :parent_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':content', $content);
        
        if ($parent_id == NULL) {
            $stmt->bindValue(':parent_id', NULL, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':parent_id', $parent_id);
        }
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Lấy bình luận theo ID
    public function getById($comment_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $comment_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật nội dung bình luận
    public function update($comment_id, $content) {
        $query = "UPDATE " . $this->table_name . " SET content = :content WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':id', $comment_id);
        return $stmt->execute();
    }

    // Xóa bình luận
    public function delete($comment_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $comment_id);
        return $stmt->execute();
    }

    public function toggleReaction($comment_id, $user_id) {
        $checkQuery = "SELECT id FROM comment_reactions WHERE comment_id = :cid AND user_id = :uid";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->execute([':cid' => $comment_id, ':uid' => $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $delQuery = "DELETE FROM comment_reactions WHERE comment_id = :cid AND user_id = :uid";
            $delStmt = $this->conn->prepare($delQuery);
            return $delStmt->execute([':cid' => $comment_id, ':uid' => $user_id]);
        } else {
            $insQuery = "INSERT INTO comment_reactions (comment_id, user_id) VALUES (:cid, :uid)";
            $insStmt = $this->conn->prepare($insQuery);
            return $insStmt->execute([':cid' => $comment_id, ':uid' => $user_id]);
        }
    }

    // Lấy danh sách người đã thích bình luận
    public function getReactions($comment_id) {
        $query = "SELECT u.full_name, u.avatar_url, r.role_name
                  FROM comment_reactions cr
                  JOIN users u ON cr.user_id = u.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  WHERE cr.comment_id = :cid
                  ORDER BY cr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cid', $comment_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
