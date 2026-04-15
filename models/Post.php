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

    public function getFeed($role_id, $department_id, $current_user_id, $channel = 'public', $dept_filter_id = null, $searchQuery = null) {
        $where = " p.visibility = 'Public' ";
        
        if ($channel === 'announcement') {
            $where = " p.visibility = 'Announcement' ";
        } else if ($channel === 'department') {
            if ($role_id == 1 || $role_id == 4) { // CEO or Admin
                if ($dept_filter_id) {
                    $where = " p.visibility = 'Department' AND p.department_id = " . intval($dept_filter_id);
                } else {
                    $where = " p.visibility = 'Department' ";
                }
            } else {
                $where = " p.visibility = 'Department' AND p.department_id = " . intval($department_id);
            }
        }

        $params = [':current_user' => $current_user_id];
        if ($searchQuery) {
            $where .= " AND (p.content_html LIKE :q OR u.full_name LIKE :q) ";
            $params[':q'] = "%$searchQuery%";
        }
        
        $query = "SELECT p.*, u.full_name, u.avatar_url, m.media_url, m.media_type, r.role_name,
                  COALESCE(rc.like_count, 0) as like_count,
                  CASE WHEN my_r.user_id IS NOT NULL THEN 1 ELSE 0 END as is_liked,
                  COALESCE(cc.comment_count, 0) as comment_count
                  FROM " . $this->table_name . " p
                  JOIN users u ON p.author_id = u.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  LEFT JOIN post_media m ON p.id = m.post_id
                  LEFT JOIN (
                      SELECT post_id, COUNT(*) as like_count FROM post_reactions GROUP BY post_id
                  ) rc ON rc.post_id = p.id
                  LEFT JOIN post_reactions my_r ON my_r.post_id = p.id AND my_r.user_id = :current_user
                  LEFT JOIN (
                      SELECT post_id, COUNT(*) as comment_count FROM comments GROUP BY post_id
                  ) cc ON cc.post_id = p.id
                  WHERE $where
                  ORDER BY p.created_at DESC LIMIT 50";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Toggle Reaction cho Bài viết
    public function toggleReaction($post_id, $user_id) {
        $check = "SELECT id FROM post_reactions WHERE post_id = :pid AND user_id = :uid";
        $stmt = $this->conn->prepare($check);
        $stmt->execute([':pid' => $post_id, ':uid' => $user_id]);
        
        $action = 'added';
        if ($stmt->rowCount() > 0) {
            $query = "DELETE FROM post_reactions WHERE post_id = :pid AND user_id = :uid";
            $action = 'deleted';
        } else {
            $query = "INSERT INTO post_reactions (post_id, user_id, type) VALUES (:pid, :uid, 'Heart')";
            $action = 'added';
        }
        
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([':pid' => $post_id, ':uid' => $user_id])) {
            return $action;
        }
        return false;
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
