<?php
class Comment {
    private $conn;
    private $table_name = "comments";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách bình luận của một bài viết (kèm thông tin user và số lượt tim)
    public function getByPostId($post_id, $current_user_id, $company_id = null) {
        $query = "SELECT c.*, u.full_name, u.avatar_url,
                  COALESCE(cr_count.like_count, 0) as like_count,
                  CASE WHEN my_cr.comment_id IS NOT NULL THEN 1 ELSE 0 END as is_liked
                  FROM comments c
                  JOIN users u ON c.user_id = u.id
                  LEFT JOIN (
                      SELECT comment_id, COUNT(*) as like_count FROM comment_reactions GROUP BY comment_id
                  ) cr_count ON cr_count.comment_id = c.id
                  LEFT JOIN comment_reactions my_cr ON my_cr.comment_id = c.id AND my_cr.user_id = :current_user
                  WHERE c.post_id = :post_id";
        
        if ($company_id) {
            $query .= " AND c.company_id = :company_id";
        }
        $query .= " ORDER BY c.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':current_user', $current_user_id);
        if ($company_id) {
            $stmt->bindParam(':company_id', $company_id);
        }
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
    public function create($post_id, $user_id, $content, $company_id, $parent_id = NULL) {
        $query = "INSERT INTO " . $this->table_name . " (post_id, user_id, content, parent_comment_id, company_id) 
                  VALUES (:post_id, :user_id, :content, :parent_id, :company_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':company_id', $company_id);
        
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
    public function getById($comment_id, $company_id = null) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        if ($company_id) {
            $query .= " AND company_id = :company_id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $comment_id);
        if ($company_id) {
            $stmt->bindParam(':company_id', $company_id);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật nội dung bình luận
    public function update($comment_id, $content, $company_id) {
        $query = "UPDATE " . $this->table_name . " SET content = :content WHERE id = :id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':id', $comment_id);
        $stmt->bindParam(':company_id', $company_id);
        return $stmt->execute();
    }

    // Xóa bình luận
    public function delete($comment_id, $company_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $comment_id);
        $stmt->bindParam(':company_id', $company_id);
        return $stmt->execute();
    }

    public function toggleReaction($comment_id, $user_id) {
        $query = "CALL sp_ToggleCommentReaction(:cid, :uid)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':cid' => $comment_id, ':uid' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result ? true : false;
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
