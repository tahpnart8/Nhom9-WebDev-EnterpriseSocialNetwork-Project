DELIMITER $$

DROP PROCEDURE IF EXISTS sp_GetFeed$$
CREATE PROCEDURE sp_GetFeed(
    IN p_current_user_id INT,
    IN p_role_id INT,
    IN p_dept_id INT,
    IN p_channel VARCHAR(20),
    IN p_search VARCHAR(100)
)
BEGIN
    SET @where_clause = ' p.visibility = "Public" ';
    
    IF p_channel = 'announcement' THEN
        SET @where_clause = ' p.visibility = "Announcement" ';
    ELSEIF p_channel = 'department' THEN
        IF p_role_id = 1 OR p_role_id = 4 THEN
            SET @where_clause = ' p.visibility = "Department" ';
        ELSE
            SET @where_clause = CONCAT(' p.visibility = "Department" AND p.department_id = ', p_dept_id);
        END IF;
    END IF;

    IF p_search IS NOT NULL AND p_search <> '' THEN
        SET @where_clause = CONCAT(@where_clause, ' AND (p.content_html LIKE ? OR u.full_name LIKE ? ) ');
        SET @search_val = CONCAT('%', p_search, '%');
    ELSE
        SET @search_val = '%%';
    END IF;

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
    IF p_search IS NOT NULL AND p_search <> '' THEN
        EXECUTE stmt USING @search_val, @search_val, @search_val, @search_val;
    ELSE
        EXECUTE stmt USING @search_val, @search_val;
    END IF;
    DEALLOCATE PREPARE stmt;
END$$

DROP PROCEDURE IF EXISTS sp_TogglePostReaction$$
CREATE PROCEDURE sp_TogglePostReaction(IN p_post_id INT, IN p_user_id INT)
BEGIN
    IF EXISTS (SELECT 1 FROM post_reactions WHERE post_id = p_post_id AND user_id = p_user_id) THEN
        DELETE FROM post_reactions WHERE post_id = p_post_id AND user_id = p_user_id;
        SELECT 'deleted' AS action;
    ELSE
        INSERT INTO post_reactions (post_id, user_id, type) VALUES (p_post_id, p_user_id, 'Heart');
        SELECT 'added' AS action;
    END IF;
END$$

DROP PROCEDURE IF EXISTS sp_GetDashboardOverview$$
CREATE PROCEDURE sp_GetDashboardOverview(IN p_user_id INT, IN p_dept_id INT, IN p_role_id INT)
BEGIN
    DECLARE v_user_count INT; DECLARE v_task_count INT; DECLARE v_pending_count INT;
    SELECT COUNT(*) INTO v_user_count FROM users WHERE is_active = 1;
    IF p_role_id = 1 OR p_role_id = 4 THEN
        SELECT COUNT(*) INTO v_task_count FROM tasks;
        SELECT COUNT(*) INTO v_pending_count FROM subtasks WHERE status = 'Pending';
    ELSE
        SELECT COUNT(*) INTO v_task_count FROM tasks WHERE department_id = p_dept_id;
        SELECT COUNT(*) INTO v_pending_count FROM subtasks s JOIN tasks t ON s.task_id = t.id 
        WHERE t.department_id = p_dept_id AND s.status = 'Pending';
    END IF;
    SELECT v_user_count as user_count, v_task_count as task_count, v_pending_count as pending_count;
END$$

DROP PROCEDURE IF EXISTS sp_GetUrgentTasks$$
CREATE PROCEDURE sp_GetUrgentTasks(IN p_user_id INT)
BEGIN
    SELECT s.*, t.title as parent_task_title, COALESCE(tc.total, 0) as parent_total, COALESCE(tc.done, 0) as parent_done
    FROM subtasks s JOIN tasks t ON s.task_id = t.id
    LEFT JOIN (SELECT task_id, COUNT(*) as total, SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done FROM subtasks GROUP BY task_id) tc ON tc.task_id = t.id
    WHERE s.assignee_id = p_user_id AND s.status IN ('To Do', 'In Progress')
    ORDER BY s.deadline ASC LIMIT 10;
END$$

DROP PROCEDURE IF EXISTS sp_SubmitSubtaskEvidence$$
CREATE PROCEDURE sp_SubmitSubtaskEvidence(IN p_subtask_id INT, IN p_notes TEXT, IN p_file_url VARCHAR(500))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; END;
    START TRANSACTION;
        UPDATE subtasks SET status = 'Pending', is_rejected = 0 WHERE id = p_subtask_id;
        IF p_notes IS NOT NULL OR p_file_url IS NOT NULL THEN
            INSERT INTO subtask_attachments (subtask_id, file_name, file_url, notes)
            VALUES (p_subtask_id, COALESCE(NULLIF(p_file_url, ''), 'Note/Link'), p_file_url, p_notes);
        END IF;
    COMMIT;
END$$

DROP PROCEDURE IF EXISTS sp_GetWorkloadStats$$
CREATE PROCEDURE sp_GetWorkloadStats(IN p_dept_id INT)
BEGIN
    IF p_dept_id IS NULL THEN
        SELECT d.dept_name as label, COUNT(s.id) as total_tasks FROM departments d
        LEFT JOIN tasks t ON t.department_id = d.id LEFT JOIN subtasks s ON s.task_id = t.id GROUP BY d.id;
    ELSE
        SELECT u.full_name as label, COUNT(s.id) as total_tasks FROM users u
        LEFT JOIN subtasks s ON s.assignee_id = u.id LEFT JOIN tasks t ON s.task_id = t.id AND t.department_id = p_dept_id
        WHERE u.department_id = p_dept_id GROUP BY u.id;
    END IF;
END$$

DROP PROCEDURE IF EXISTS sp_CreateTaskReportPost$$
CREATE PROCEDURE sp_CreateTaskReportPost(IN p_task_id INT, IN p_subtask_id INT, IN p_content TEXT, IN p_ai_content TEXT, IN p_author_id INT, IN p_dept_id INT)
BEGIN
    DECLARE v_report_id INT; DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; END;
    START TRANSACTION;
        INSERT INTO task_reports (task_id, subtask_id, content, ai_generated_content) VALUES (p_task_id, p_subtask_id, p_content, p_ai_content);
        SET v_report_id = LAST_INSERT_ID();
        INSERT INTO posts (author_id, department_id, task_report_id, visibility, content_html, is_ai_generated)
        VALUES (p_author_id, p_dept_id, v_report_id, 'Department', COALESCE(p_ai_content, p_content), 1);
    COMMIT;
END$$

DROP PROCEDURE IF EXISTS sp_GetUnreadNotis$$
CREATE PROCEDURE sp_GetUnreadNotis(IN p_user_id INT)
BEGIN
    SELECT n.*, nu.is_read FROM notifications n JOIN notification_user nu ON n.id = nu.notification_id
    WHERE nu.user_id = p_user_id AND nu.is_read = 0 ORDER BY n.created_at DESC;
END$$

DROP PROCEDURE IF EXISTS sp_SearchUsers$$
CREATE PROCEDURE sp_SearchUsers(IN p_keyword VARCHAR(100))
BEGIN
    SELECT u.id, u.full_name, u.username, u.email, u.avatar_url, d.dept_name, r.role_name FROM users u
    LEFT JOIN departments d ON u.department_id = d.id LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.full_name LIKE CONCAT('%', p_keyword, '%') OR u.username LIKE CONCAT('%', p_keyword, '%') LIMIT 15;
END$$

DROP PROCEDURE IF EXISTS sp_SearchTasks$$
CREATE PROCEDURE sp_SearchTasks(
    IN p_user_id INT,
    IN p_role_id INT,
    IN p_dept_id INT,
    IN p_keyword VARCHAR(100)
)
BEGIN
    SELECT t.*, u.full_name as creator_name, d.dept_name
    FROM tasks t
    JOIN users u ON t.created_by_user_id = u.id
    LEFT JOIN departments d ON t.department_id = d.id
    WHERE (p_role_id IN (1, 4) OR t.department_id = p_dept_id)
      AND (t.title LIKE CONCAT('%', p_keyword, '%') OR t.description LIKE CONCAT('%', p_keyword, '%'))
    ORDER BY t.created_at DESC LIMIT 15;
END$$

-- 11. sp_GetConversationMessages: Lấy tin nhắn chat với đầy đủ thông tin người gửi + Kiểm tra quyền truy cập
DROP PROCEDURE IF EXISTS sp_GetConversationMessages$$
CREATE PROCEDURE sp_GetConversationMessages(
    IN p_conv_id INT, 
    IN p_limit INT,
    IN p_viewer_id INT
)
BEGIN
    -- Chỉ cho phép xem nếu viewer là thành viên của cuộc hội thoại
    IF EXISTS (SELECT 1 FROM conversation_members WHERE conversation_id = p_conv_id AND user_id = p_viewer_id) THEN
        SELECT m.*, u.full_name as sender_name, u.avatar_url as sender_avatar 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = p_conv_id 
        ORDER BY m.created_at ASC LIMIT p_limit;
    END IF;
END$$

-- 12. sp_MarkMessagesAsRead: Cập nhật trạng thái đã xem (Now)
DROP PROCEDURE IF EXISTS sp_MarkMessagesAsRead$$
CREATE PROCEDURE sp_MarkMessagesAsRead(
    IN p_conv_id INT, 
    IN p_user_id INT
)
BEGIN
    UPDATE conversation_members 
    SET last_read_at = NOW() 
    WHERE conversation_id = p_conv_id AND user_id = p_user_id;
END$$

-- 13. sp_GetPostComments: Lấy danh sách bình luận (Comment + User + Reactions)
DROP PROCEDURE IF EXISTS sp_GetPostComments$$
CREATE PROCEDURE sp_GetPostComments(
    IN p_post_id INT,
    IN p_current_user_id INT
)
BEGIN
    SELECT c.*, u.full_name, u.avatar_url,
           (SELECT COUNT(*) FROM comment_reactions WHERE comment_id = c.id) as like_count,
           CASE WHEN EXISTS (SELECT 1 FROM comment_reactions WHERE comment_id = c.id AND user_id = p_current_user_id) THEN 1 ELSE 0 END as is_liked
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = p_post_id
    ORDER BY c.created_at ASC;
END$$

-- 14. sp_ToggleCommentReaction: Like/Unlike bình luận
DROP PROCEDURE IF EXISTS sp_ToggleCommentReaction$$
CREATE PROCEDURE sp_ToggleCommentReaction(IN p_comment_id INT, IN p_user_id INT)
BEGIN
    IF EXISTS (SELECT 1 FROM comment_reactions WHERE comment_id = p_comment_id AND user_id = p_user_id) THEN
        DELETE FROM comment_reactions WHERE comment_id = p_comment_id AND user_id = p_user_id;
        SELECT 'deleted' AS action;
    ELSE
        INSERT INTO comment_reactions (comment_id, user_id) VALUES (p_comment_id, p_user_id);
        SELECT 'added' AS action;
    END IF;
END$$

-- ================================================================
-- CHAT & REAL-TIME PROCEDURES (Tối ưu lần 2 — 2026-04-15)
-- ================================================================

-- 17. Lấy tin nhắn hội thoại (CÓ alias sender_name/sender_avatar)
DROP PROCEDURE IF EXISTS sp_GetConversationMessages$$
CREATE PROCEDURE sp_GetConversationMessages(
    IN p_conv_id INT, 
    IN p_limit INT,
    IN p_viewer_id INT
)
BEGIN
    IF EXISTS (SELECT 1 FROM conversation_members WHERE conversation_id = p_conv_id AND user_id = p_viewer_id) THEN
        SELECT m.*, u.full_name as sender_name, u.avatar_url as sender_avatar 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = p_conv_id 
        ORDER BY m.created_at ASC LIMIT p_limit;
    END IF;
END$$

-- 18. Delta Fetch: Chỉ lấy tin mới hơn since_id (Real-time polling siêu nhẹ)
DROP PROCEDURE IF EXISTS sp_CheckNewMessages$$
CREATE PROCEDURE sp_CheckNewMessages(
    IN p_conv_id INT, 
    IN p_since_id INT, 
    IN p_viewer_id INT
)
BEGIN
    IF EXISTS (SELECT 1 FROM conversation_members WHERE conversation_id = p_conv_id AND user_id = p_viewer_id) THEN
        SELECT m.id, m.sender_id, m.content, m.created_at, 
               u.full_name as sender_name, u.avatar_url as sender_avatar
        FROM messages m 
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = p_conv_id AND m.id > p_since_id
        ORDER BY m.created_at ASC;
    END IF;
END$$

-- 19. Danh sách hội thoại (thay thế raw SQL phức tạp)
DROP PROCEDURE IF EXISTS sp_GetConversationList$$
CREATE PROCEDURE sp_GetConversationList(IN p_user_id INT)
BEGIN
    SELECT c.id, c.type, c.name as group_name, c.avatar_url as group_avatar, 
           c.created_by, c.requires_approval,
           lm.content as last_message,
           lm.created_at as last_time,
           CASE WHEN c.type = 'Direct' THEN partner_u.full_name ELSE NULL END as partner_name,
           CASE WHEN c.type = 'Direct' THEN partner_u.avatar_url ELSE NULL END as partner_avatar,
           CASE WHEN c.type = 'Direct' THEN partner_cm.user_id ELSE NULL END as partner_id,
           (SELECT u2.avatar_url FROM conversation_members cm2 JOIN users u2 ON cm2.user_id = u2.id 
            WHERE cm2.conversation_id = c.id AND c.type = 'Group' ORDER BY cm2.user_id LIMIT 1) as group_avatar_1,
           (SELECT u3.avatar_url FROM conversation_members cm3 JOIN users u3 ON cm3.user_id = u3.id 
            WHERE cm3.conversation_id = c.id AND c.type = 'Group' ORDER BY cm3.user_id LIMIT 1 OFFSET 1) as group_avatar_2,
           COALESCE(unread.cnt, 0) as unread_count
    FROM conversations c
    JOIN conversation_members cm ON c.id = cm.conversation_id AND cm.user_id = p_user_id
    LEFT JOIN (
        SELECT m1.conversation_id, m1.content, m1.created_at
        FROM messages m1
        INNER JOIN (SELECT conversation_id, MAX(id) as max_id FROM messages GROUP BY conversation_id) m2 
        ON m1.id = m2.max_id
    ) lm ON lm.conversation_id = c.id
    LEFT JOIN conversation_members partner_cm ON partner_cm.conversation_id = c.id 
        AND partner_cm.user_id != p_user_id AND c.type = 'Direct'
    LEFT JOIN users partner_u ON partner_u.id = partner_cm.user_id
    LEFT JOIN (
        SELECT m.conversation_id, COUNT(*) as cnt
        FROM messages m
        JOIN conversation_members cmr ON m.conversation_id = cmr.conversation_id AND cmr.user_id = p_user_id
        WHERE m.created_at > cmr.last_read_at AND m.sender_id != p_user_id
        GROUP BY m.conversation_id
    ) unread ON unread.conversation_id = c.id
    ORDER BY lm.created_at DESC;
END$$

-- 20. Heartbeat: Đếm nhanh notification + chat unread (cho badge header)
DROP PROCEDURE IF EXISTS sp_Heartbeat$$
CREATE PROCEDURE sp_Heartbeat(IN p_user_id INT)
BEGIN
    SELECT
        (SELECT COUNT(*) FROM notification_user WHERE user_id = p_user_id AND is_read = 0) as noti_count,
        (SELECT COUNT(DISTINCT c.id) FROM conversations c
         JOIN conversation_members cm ON c.id = cm.conversation_id
         JOIN messages m ON c.id = m.conversation_id
         WHERE cm.user_id = p_user_id AND m.created_at > cm.last_read_at AND m.sender_id != p_user_id
        ) as chat_count;
END$$

-- 21. Đánh dấu đã đọc tin nhắn  
DROP PROCEDURE IF EXISTS sp_MarkMessagesAsRead$$
CREATE PROCEDURE sp_MarkMessagesAsRead(IN p_conv_id INT, IN p_user_id INT)
BEGIN
    UPDATE conversation_members 
    SET last_read_at = NOW() 
    WHERE conversation_id = p_conv_id AND user_id = p_user_id;
END$$

DELIMITER ;
