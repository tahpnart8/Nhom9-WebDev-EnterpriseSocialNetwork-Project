DROP PROCEDURE IF EXISTS `sp_GetFeed`;
DELIMITER $$
CREATE PROCEDURE `sp_GetFeed`(
    IN `p_current_user_id` INT, 
    IN `p_role_id` INT, 
    IN `p_dept_id` INT, 
    IN `p_company_id` INT,
    IN `p_channel` VARCHAR(20), 
    IN `p_search` VARCHAR(100)
)
BEGIN
    -- Khởi tạo điều kiện lọc theo công ty (Cô lập dữ liệu)
    SET @where_clause = CONCAT(' p.company_id = ', p_company_id);
    
    -- Lọc theo kênh bài viết
    IF p_channel = 'announcement' THEN
        SET @where_clause = CONCAT(@where_clause, ' AND p.visibility = "Announcement" ');
    ELSEIF p_channel = 'department' THEN
        -- Nếu là CEO hoặc Admin, có thể xem toàn bộ bài viết phòng ban của công ty đó
        IF p_role_id = 1 OR p_role_id = 4 THEN
            SET @where_clause = CONCAT(@where_clause, ' AND p.visibility = "Department" ');
        ELSE
            -- Leader/Staff chỉ thấy bài viết phòng ban của mình
            SET @where_clause = CONCAT(@where_clause, ' AND p.visibility = "Department" AND p.department_id = ', p_dept_id);
        END IF;
    ELSE
        -- Mặc định (Public): Thấy bài viết công khai hoặc thông báo rộng rãi trong công ty
        SET @where_clause = CONCAT(@where_clause, ' AND (p.visibility = "Public" OR p.visibility = "Announcement")');
    END IF;

    -- Xử lý tìm kiếm (nếu có)
    IF p_search IS NOT NULL AND p_search <> '' THEN
        SET @where_clause = CONCAT(@where_clause, ' AND (p.content_html LIKE ? OR u.full_name LIKE ? ) ');
        SET @search_val = CONCAT('%', p_search, '%');
    ELSE
        SET @search_val = '%%';
    END IF;

    -- Xây dựng query hoàn chỉnh
    SET @final_query = CONCAT('
        SELECT p.*, u.full_name, u.avatar_url, m.media_url, m.media_type, r.role_name, t.title as task_title,
               (CASE 
                    WHEN u.full_name LIKE ? THEN 20
                    WHEN p.content_html LIKE ? THEN 10
                    ELSE 0 
                END) as relevance_score,
               COALESCE(rc.like_count, 0) as like_count,
               CASE WHEN my_r.user_id IS NOT NULL THEN 1 ELSE 0 END as is_liked,
               COALESCE(cc.comment_count, 0) as comment_count
        FROM posts p
        JOIN users u ON p.author_id = u.id
        LEFT JOIN roles r ON u.role_id = r.id
        LEFT JOIN tasks t ON p.task_report_id = t.id
        LEFT JOIN post_media m ON p.id = m.post_id
        LEFT JOIN (SELECT post_id, COUNT(*) as like_count FROM post_reactions GROUP BY post_id) rc ON rc.post_id = p.id
        LEFT JOIN post_reactions my_r ON my_r.post_id = p.id AND my_r.user_id = ', p_current_user_id, '
        LEFT JOIN (SELECT post_id, COUNT(*) as comment_count FROM comments GROUP BY post_id) cc ON cc.post_id = p.id
        WHERE ', @where_clause, '
        ORDER BY relevance_score DESC, p.created_at DESC LIMIT 50'
    );
    
    PREPARE stmt FROM @final_query;
    -- Thực thi với tham số tương ứng
    IF p_search IS NOT NULL AND p_search <> '' THEN
        EXECUTE stmt USING @search_val, @search_val, @search_val, @search_val;
    ELSE
        EXECUTE stmt USING @search_val, @search_val;
    END IF;
    DEALLOCATE PREPARE stmt;
END$$
DELIMITER ;
