<?php
require_once __DIR__ . '/BaseModel.php';

class Post extends BaseModel {
    protected string $table_name = "posts";

    public function create($author_id, $department_id, $content, $visibility, $company_id) {
        $query = "INSERT INTO " . $this->table_name . " (author_id, department_id, content_html, visibility, company_id) 
                  VALUES (:author_id, :department_id, :content, :visibility, :company_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":author_id", $author_id);
        
        if($department_id == NULL) {
            $stmt->bindValue(":department_id", NULL, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":department_id", $department_id);
        }
        
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":visibility", $visibility);
        $stmt->bindParam(":company_id", $company_id);
        
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

    public function getFeed($role_id, $department_id, $current_user_id, $company_id, $channel = 'public', $dept_filter_id = null, $searchQuery = null) {
        $dept_id = ($role_id == 1 || $role_id == 4) ? $dept_filter_id : $department_id;
        
        // CẬP NHẬT: Thay vì gọi Procedure (khó sửa multi-tenant), ta chuyển sang dùng SQL thuần hoặc Procedure có hỗ trợ company_id
        // Giả sử sp_GetFeed đã được cập nhật nhận company_id
        $query = "CALL sp_GetFeed(:current_user, :role_id, :dept_id, :company_id, :channel, :search)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':current_user', $current_user_id);
        $stmt->bindParam(':role_id', $role_id);
        $stmt->bindValue(':dept_id', $dept_id, $dept_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $company_id);
        $stmt->bindParam(':channel', $channel);
        $stmt->bindValue(':search', $searchQuery, $searchQuery === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $results;
    }

    // Toggle Reaction cho Bài viết sử dụng Procedure
    public function toggleReaction($post_id, $user_id) {
        $query = "CALL sp_TogglePostReaction(:pid, :uid)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pid', $post_id);
        $stmt->bindParam(':uid', $user_id);
        
        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor(); // Quan trọng khi gọi procedure trả về result set
            return $result['action'];
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
    public function delete($post_id, $company_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $post_id);
        $stmt->bindParam(':company_id', $company_id);
        return $stmt->execute();
    }

    // Lấy bài viết theo ID
    public function getById($post_id, $company_id = null) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        if ($company_id) {
            $query .= " AND company_id = :company_id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $post_id);
        if ($company_id) {
            $stmt->bindParam(':company_id', $company_id);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật nội dung bài viết
    public function update($post_id, $content, $company_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET content_html = :content, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':id', $post_id);
        $stmt->bindParam(':company_id', $company_id);
        return $stmt->execute();
    }
}
?>
