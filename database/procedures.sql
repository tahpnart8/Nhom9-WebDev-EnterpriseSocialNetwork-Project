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
        SET @where_clause = CONCAT(@where_clause, ' AND (p.content_html LIKE "%', p_search, '%" OR u.full_name LIKE "%', p_search, '%") ');
    END IF;

    SET @final_query = CONCAT('
        SELECT p.*, u.full_name, u.avatar_url, m.media_url, m.media_type, r.role_name,
               COALESCE(rc.like_count, 0) as like_count,
               CASE WHEN my_r.user_id IS NOT NULL THEN 1 ELSE 0 END as is_liked,
               COALESCE(cc.comment_count, 0) as comment_count
        FROM posts p
        JOIN users u ON p.author_id = u.id
        LEFT JOIN roles r ON u.role_id = r.id
        LEFT JOIN post_media m ON p.id = m.post_id
        LEFT JOIN (SELECT post_id, COUNT(*) as like_count FROM post_reactions GROUP BY post_id) rc ON rc.post_id = p.id
        LEFT JOIN post_reactions my_r ON my_r.post_id = p.id AND my_r.user_id = ', p_current_user_id, '
        LEFT JOIN (SELECT post_id, COUNT(*) as comment_count FROM comments GROUP BY post_id) cc ON cc.post_id = p.id
        WHERE ', @where_clause, '
        ORDER BY p.created_at DESC LIMIT 50'
    );
    PREPARE stmt FROM @final_query;
    EXECUTE stmt;
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
    SELECT u.id, u.full_name, u.email, u.avatar_url, d.dept_name, r.role_name FROM users u
    LEFT JOIN departments d ON u.department_id = d.id LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.full_name LIKE CONCAT('%', p_keyword, '%') OR u.username LIKE CONCAT('%', p_keyword, '%') LIMIT 20;
END$$

DROP PROCEDURE IF EXISTS sp_UpdateTaskStatusSync$$
CREATE PROCEDURE sp_UpdateTaskStatusSync(IN p_task_id INT)
BEGIN
    DECLARE v_total INT; DECLARE v_done INT;
    SELECT COUNT(*), SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) INTO v_total, v_done FROM subtasks WHERE task_id = p_task_id;
    IF v_total > 0 AND v_total = v_done THEN UPDATE tasks SET status = 'Done' WHERE id = p_task_id;
    ELSEIF v_done > 0 THEN UPDATE tasks SET status = 'In Progress' WHERE id = p_task_id; END IF;
END$$

DROP PROCEDURE IF EXISTS sp_GetConversationMessages$$
CREATE PROCEDURE sp_GetConversationMessages(IN p_conv_id INT, IN p_limit INT, IN p_offset INT)
BEGIN
    SELECT m.*, u.full_name, u.avatar_url FROM messages m JOIN users u ON m.sender_id = u.id
    WHERE m.conversation_id = p_conv_id ORDER BY m.created_at DESC LIMIT p_limit OFFSET p_offset;
END$$

DROP PROCEDURE IF EXISTS sp_MarkMessagesAsRead$$
CREATE PROCEDURE sp_MarkMessagesAsRead(IN p_conv_id INT, IN p_user_id INT)
BEGIN
    UPDATE conversation_members SET last_read_at = NOW() WHERE conversation_id = p_conv_id AND user_id = p_user_id;
END$$

DROP PROCEDURE IF EXISTS sp_GetEmployeePerformance$$
CREATE PROCEDURE sp_GetEmployeePerformance(IN p_user_id INT)
BEGIN
    SELECT COUNT(*) as total_assigned, SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN deadline < NOW() AND status != 'Done' THEN 1 ELSE 0 END) as overdue,
    ROUND((SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as efficiency_rate
    FROM subtasks WHERE assignee_id = p_user_id;
END$$

DROP PROCEDURE IF EXISTS sp_GetLeaderboard$$
CREATE PROCEDURE sp_GetLeaderboard(IN p_dept_id INT)
BEGIN
    SELECT u.full_name, u.avatar_url, COUNT(s.id) as tasks_done FROM users u JOIN subtasks s ON u.id = s.assignee_id
    WHERE u.department_id = p_dept_id AND s.status = 'Done' GROUP BY u.id ORDER BY tasks_done DESC LIMIT 10;
END$$

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

DROP PROCEDURE IF EXISTS sp_GetSubtaskStatsDetailed$$
CREATE PROCEDURE sp_GetSubtaskStatsDetailed(
    IN p_dept_id INT,
    IN p_assignee_id INT
)
BEGIN
    SELECT 
        COUNT(s.id) as total_subtasks,
        SUM(CASE WHEN s.status = 'Done' THEN 1 ELSE 0 END) as done_subtasks,
        SUM(CASE WHEN s.deadline < NOW() AND s.status != 'Done' THEN 1 ELSE 0 END) as overdue_subtasks,
        SUM(CASE WHEN s.status = 'To Do' THEN 1 ELSE 0 END) as todo_subtasks,
        SUM(CASE WHEN s.status = 'In Progress' THEN 1 ELSE 0 END) as inprogress_subtasks,
        SUM(CASE WHEN s.status = 'Pending' THEN 1 ELSE 0 END) as pending_subtasks
    FROM subtasks s
    JOIN tasks t ON s.task_id = t.id
    WHERE (p_dept_id IS NULL OR t.department_id = p_dept_id)
      AND (p_assignee_id IS NULL OR s.assignee_id = p_assignee_id);
END$$

DELIMITER ;
