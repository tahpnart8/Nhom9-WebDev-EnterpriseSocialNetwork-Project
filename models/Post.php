<?php
class Post {
    private $conn;
    public $table_name = "posts";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($author_id, $department_id, $content, $visibility) {
        $query = "INSERT INTO " . $this->table_name . " (author_id, department_id, content_html, visibility) 
                  VALUES (:author_id, :department_id, :content, :visibility)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":author_id", $author_id);
        
        if($department_id == NULL) {
            $stmt->bindValue(":department_id", NULL, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":department_id", $department_id);
        }
        
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":visibility", $visibility);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function addMedia($post_id, $media_url, $media_type = 'Image') {
        $query = "INSERT INTO post_media (post_id, media_url, media_type) VALUES (:post_id, :media_url, :media_type)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":post_id", $post_id);
        $stmt->bindParam(":media_url", $media_url);
        $stmt->bindParam(":media_type", $media_type);
        return $stmt->execute();
    }

    public function getFeed($role_id, $department_id, $current_user_id) {
        // Build conditions based on role (Public OR Own Department)
        $deptCondition = "";
        if ($department_id) {
            $deptCondition = " OR (p.visibility = 'Department' AND p.department_id = $department_id) ";
        }
        $where = " p.visibility = 'Public' " . $deptCondition;
        
        $query = "SELECT p.*, u.full_name, u.avatar_url, m.media_url, m.media_type, r.role_name,
                  (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id) as like_count,
                  (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND user_id = :current_user) as is_liked,
                  (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
                  FROM " . $this->table_name . " p
                  JOIN users u ON p.author_id = u.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  LEFT JOIN post_media m ON p.id = m.post_id
                  WHERE $where
                  ORDER BY p.created_at DESC LIMIT 50";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':current_user', $current_user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Toggle Reaction cho Bài viết
    public function toggleReaction($post_id, $user_id) {
        $check = "SELECT id FROM post_reactions WHERE post_id = :pid AND user_id = :uid";
        $stmt = $this->conn->prepare($check);
        $stmt->execute([':pid' => $post_id, ':uid' => $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $query = "DELETE FROM post_reactions WHERE post_id = :pid AND user_id = :uid";
        } else {
            $query = "INSERT INTO post_reactions (post_id, user_id, type) VALUES (:pid, :uid, 'Heart')";
        }
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':pid' => $post_id, ':uid' => $user_id]);
    }

    // Lấy danh sách người đã thích bài viết
    public function getReactions($post_id) {
        $query = "SELECT u.full_name, u.avatar_url, r.role_name
                  FROM post_reactions pr
                  JOIN users u ON pr.user_id = u.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  WHERE pr.post_id = :pid
                  ORDER BY pr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pid', $post_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Xóa bài viết (Chỉ tác giả hoặc CEO mới được xóa)
    public function delete($post_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $post_id);
        return $stmt->execute();
    }

    // Lấy bài viết theo ID
    public function getById($post_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $post_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật nội dung bài viết
    public function update($post_id, $content) {
        $query = "UPDATE " . $this->table_name . " SET content_html = :content, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':id', $post_id);
        return $stmt->execute();
    }
}
?>
