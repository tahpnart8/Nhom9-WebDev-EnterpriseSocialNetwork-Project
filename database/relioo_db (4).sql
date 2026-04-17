-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 100.72.177.39
-- Generation Time: Apr 17, 2026 at 02:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `relioo_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CheckNewMessages` (IN `p_conv_id` INT, IN `p_since_id` INT, IN `p_viewer_id` INT)   BEGIN
    IF EXISTS (SELECT 1 FROM conversation_members WHERE conversation_id = p_conv_id AND user_id = p_viewer_id) THEN
        SELECT m.id, m.sender_id, m.content, m.created_at, 
               u.full_name as sender_name, u.avatar_url as sender_avatar
        FROM messages m 
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = p_conv_id AND m.id > p_since_id
        ORDER BY m.created_at ASC;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CreateTaskReportPost` (IN `p_task_id` INT, IN `p_subtask_id` INT, IN `p_content` TEXT, IN `p_ai_content` TEXT, IN `p_author_id` INT, IN `p_dept_id` INT)   BEGIN
    DECLARE v_report_id INT; DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; END;
    START TRANSACTION;
        INSERT INTO task_reports (task_id, subtask_id, content, ai_generated_content) VALUES (p_task_id, p_subtask_id, p_content, p_ai_content);
        SET v_report_id = LAST_INSERT_ID();
        INSERT INTO posts (author_id, department_id, task_report_id, visibility, content_html, is_ai_generated)
        VALUES (p_author_id, p_dept_id, v_report_id, 'Department', COALESCE(p_ai_content, p_content), 1);
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetConversationList` (IN `p_user_id` INT)   BEGIN
    SELECT c.id, c.type, c.name as group_name, c.avatar_url as group_avatar, 
           c.created_by, c.requires_approval,
           (SELECT content FROM messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_message,
           (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_time,
           CASE WHEN c.type = 'Direct' THEN partner_u.full_name ELSE NULL END as partner_name,
           CASE WHEN c.type = 'Direct' THEN partner_u.avatar_url ELSE NULL END as partner_avatar,
           CASE WHEN c.type = 'Direct' THEN partner_cm.user_id ELSE NULL END as partner_id,
           (SELECT u2.avatar_url FROM conversation_members cm2 JOIN users u2 ON cm2.user_id = u2.id 
            WHERE cm2.conversation_id = c.id AND c.type = 'Group' ORDER BY cm2.user_id LIMIT 1) as group_avatar_1,
           (SELECT u3.avatar_url FROM conversation_members cm3 JOIN users u3 ON cm3.user_id = u3.id 
            WHERE cm3.conversation_id = c.id AND c.type = 'Group' ORDER BY cm3.user_id LIMIT 1 OFFSET 1) as group_avatar_2,
           COALESCE(
               (SELECT COUNT(*) FROM messages 
                WHERE conversation_id = c.id AND sender_id != p_user_id AND created_at > cm.last_read_at)
           , 0) as unread_count
    FROM conversations c
    JOIN conversation_members cm ON c.id = cm.conversation_id AND cm.user_id = p_user_id
    LEFT JOIN conversation_members partner_cm ON partner_cm.conversation_id = c.id 
        AND partner_cm.user_id != p_user_id AND c.type = 'Direct'
    LEFT JOIN users partner_u ON partner_u.id = partner_cm.user_id
    ORDER BY last_time DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetConversationMessages` (IN `p_conv_id` INT, IN `p_limit` INT, IN `p_offset` INT, IN `p_viewer_id` INT)   BEGIN
    IF EXISTS (SELECT 1 FROM conversation_members WHERE conversation_id = p_conv_id AND user_id = p_viewer_id) THEN
        SELECT * FROM (
            SELECT m.*, u.full_name as sender_name, u.avatar_url as sender_avatar 
            FROM messages m 
            JOIN users u ON m.sender_id = u.id
            WHERE m.conversation_id = p_conv_id 
            ORDER BY m.id DESC LIMIT p_limit OFFSET p_offset
        ) AS tmp
        ORDER BY tmp.id ASC;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetDashboardOverview` (IN `p_user_id` INT, IN `p_dept_id` INT, IN `p_role_id` INT)   BEGIN
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

CREATE DEFINER=`Nhom9`@`%` PROCEDURE `sp_GetEmployeePerformance` (IN `p_user_id` INT)   BEGIN
    SELECT COUNT(*) as total_assigned, SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN deadline < NOW() AND status != 'Done' THEN 1 ELSE 0 END) as overdue,
    ROUND((SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as efficiency_rate
    FROM subtasks WHERE assignee_id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetFeed` (IN `p_current_user_id` INT, IN `p_role_id` INT, IN `p_dept_id` INT, IN `p_company_id` INT, IN `p_channel` VARCHAR(20), IN `p_search` VARCHAR(100))   BEGIN
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

CREATE DEFINER=`Nhom9`@`%` PROCEDURE `sp_GetLeaderboard` (IN `p_dept_id` INT)   BEGIN
    SELECT u.full_name, u.avatar_url, COUNT(s.id) as tasks_done FROM users u JOIN subtasks s ON u.id = s.assignee_id
    WHERE u.department_id = p_dept_id AND s.status = 'Done' GROUP BY u.id ORDER BY tasks_done DESC LIMIT 10;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetPostComments` (IN `p_post_id` INT, IN `p_current_user_id` INT)   BEGIN
    SELECT c.*, u.full_name, u.avatar_url,
           (SELECT COUNT(*) FROM comment_reactions WHERE comment_id = c.id) as like_count,
           CASE WHEN EXISTS (SELECT 1 FROM comment_reactions WHERE comment_id = c.id AND user_id = p_current_user_id) THEN 1 ELSE 0 END as is_liked
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = p_post_id
    ORDER BY c.created_at ASC;
END$$

CREATE DEFINER=`Nhom9`@`%` PROCEDURE `sp_GetSubtaskStatsDetailed` (IN `p_dept_id` INT, IN `p_assignee_id` INT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetUnreadNotis` (IN `p_user_id` INT)   BEGIN
    SELECT n.*, nu.is_read FROM notifications n JOIN notification_user nu ON n.id = nu.notification_id
    WHERE nu.user_id = p_user_id AND nu.is_read = 0 ORDER BY n.created_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetUrgentTasks` (IN `p_user_id` INT, IN `p_role_id` INT, IN `p_dept_id` INT)   BEGIN
    IF p_role_id = 3 THEN
        
        SELECT s.*, t.title as parent_task_title, COALESCE(tc.total, 0) as parent_total, COALESCE(tc.done, 0) as parent_done
        FROM subtasks s JOIN tasks t ON s.task_id = t.id
        LEFT JOIN (SELECT task_id, COUNT(*) as total, SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done FROM subtasks GROUP BY task_id) tc ON tc.task_id = t.id
        WHERE s.assignee_id = p_user_id 
          AND s.status IN ('To Do', 'In Progress')
          AND s.deadline >= NOW() 
          AND s.deadline <= DATE_ADD(NOW(), INTERVAL 4 DAY)
        ORDER BY s.deadline ASC LIMIT 10;
        
    ELSEIF p_role_id = 2 THEN
        
        SELECT s.*, t.title as parent_task_title, COALESCE(tc.total, 0) as parent_total, COALESCE(tc.done, 0) as parent_done
        FROM subtasks s JOIN tasks t ON s.task_id = t.id
        LEFT JOIN (SELECT task_id, COUNT(*) as total, SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done FROM subtasks GROUP BY task_id) tc ON tc.task_id = t.id
        WHERE 
            (s.assignee_id = p_user_id AND s.status IN ('To Do', 'In Progress') AND s.deadline >= NOW() AND s.deadline <= DATE_ADD(NOW(), INTERVAL 4 DAY))
            OR
            (t.department_id = p_dept_id AND (s.status = 'Pending' OR s.extension_requested_at IS NOT NULL))
        ORDER BY s.deadline ASC LIMIT 10;
        
    ELSE
        
        SELECT s.*, t.title as parent_task_title, COALESCE(tc.total, 0) as parent_total, COALESCE(tc.done, 0) as parent_done
        FROM subtasks s JOIN tasks t ON s.task_id = t.id
        LEFT JOIN (SELECT task_id, COUNT(*) as total, SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done FROM subtasks GROUP BY task_id) tc ON tc.task_id = t.id
        WHERE 
            (s.assignee_id = p_user_id AND s.status IN ('To Do', 'In Progress') AND s.deadline >= NOW() AND s.deadline <= DATE_ADD(NOW(), INTERVAL 4 DAY))
            OR
            (s.status = 'Pending' OR s.extension_requested_at IS NOT NULL)
        ORDER BY s.deadline ASC LIMIT 10;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetWorkloadStats` (IN `p_dept_id` INT)   BEGIN
    IF p_dept_id IS NULL THEN
        SELECT d.dept_name as label, COUNT(s.id) as total_tasks FROM departments d
        LEFT JOIN tasks t ON t.department_id = d.id LEFT JOIN subtasks s ON s.task_id = t.id GROUP BY d.id;
    ELSE
        SELECT u.full_name as label, COUNT(s.id) as total_tasks FROM users u
        LEFT JOIN subtasks s ON s.assignee_id = u.id LEFT JOIN tasks t ON s.task_id = t.id AND t.department_id = p_dept_id
        WHERE u.department_id = p_dept_id GROUP BY u.id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_Heartbeat` (IN `p_user_id` INT)   BEGIN
    SELECT
        (SELECT COUNT(*) FROM notification_user WHERE user_id = p_user_id AND is_read = 0) as noti_count,
        (SELECT COUNT(DISTINCT c.id) FROM conversations c
         JOIN conversation_members cm ON c.id = cm.conversation_id
         JOIN messages m ON c.id = m.conversation_id
         WHERE cm.user_id = p_user_id AND m.created_at > cm.last_read_at AND m.sender_id != p_user_id
        ) as chat_count,
        (SELECT MAX(m.id) FROM messages m 
         JOIN conversation_members cm ON m.conversation_id = cm.conversation_id 
         WHERE cm.user_id = p_user_id
        ) as last_msg_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_MarkMessagesAsRead` (IN `p_conv_id` INT, IN `p_user_id` INT)   BEGIN
    UPDATE conversation_members 
    SET last_read_at = NOW() 
    WHERE conversation_id = p_conv_id AND user_id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_SearchTasks` (IN `p_user_id` INT, IN `p_role_id` INT, IN `p_dept_id` INT, IN `p_keyword` VARCHAR(100))   BEGIN
    SELECT t.*, u.full_name as creator_name, d.dept_name
    FROM tasks t
    JOIN users u ON t.created_by_user_id = u.id
    LEFT JOIN departments d ON t.department_id = d.id
    WHERE (p_role_id IN (1, 4) OR t.department_id = p_dept_id)
      AND (t.title LIKE CONCAT('%', p_keyword, '%') OR t.description LIKE CONCAT('%', p_keyword, '%'))
    ORDER BY t.created_at DESC LIMIT 15;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_SearchUsers` (IN `p_keyword` VARCHAR(100))   BEGIN
    SELECT u.id, u.full_name, u.username, u.email, u.avatar_url, d.dept_name, r.role_name FROM users u
    LEFT JOIN departments d ON u.department_id = d.id LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.full_name LIKE CONCAT('%', p_keyword, '%') OR u.username LIKE CONCAT('%', p_keyword, '%') LIMIT 15;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_SubmitSubtaskEvidence` (IN `p_subtask_id` INT, IN `p_notes` TEXT, IN `p_file_url` VARCHAR(500))   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; END;
    START TRANSACTION;
        UPDATE subtasks SET status = 'Pending', is_rejected = 0 WHERE id = p_subtask_id;
        IF p_notes IS NOT NULL OR p_file_url IS NOT NULL THEN
            INSERT INTO subtask_attachments (subtask_id, file_name, file_url, notes)
            VALUES (p_subtask_id, COALESCE(NULLIF(p_file_url, ''), 'Note/Link'), p_file_url, p_notes);
        END IF;
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ToggleCommentReaction` (IN `p_comment_id` INT, IN `p_user_id` INT)   BEGIN
    IF EXISTS (SELECT 1 FROM comment_reactions WHERE comment_id = p_comment_id AND user_id = p_user_id) THEN
        DELETE FROM comment_reactions WHERE comment_id = p_comment_id AND user_id = p_user_id;
        SELECT 'deleted' AS action;
    ELSE
        INSERT INTO comment_reactions (comment_id, user_id) VALUES (p_comment_id, p_user_id);
        SELECT 'added' AS action;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TogglePostReaction` (IN `p_post_id` INT, IN `p_user_id` INT)   BEGIN
    IF EXISTS (SELECT 1 FROM post_reactions WHERE post_id = p_post_id AND user_id = p_user_id) THEN
        DELETE FROM post_reactions WHERE post_id = p_post_id AND user_id = p_user_id;
        SELECT 'deleted' AS action;
    ELSE
        INSERT INTO post_reactions (post_id, user_id, type) VALUES (p_post_id, p_user_id, 'Heart');
        SELECT 'added' AS action;
    END IF;
END$$

CREATE DEFINER=`Nhom9`@`%` PROCEDURE `sp_UpdateTaskStatusSync` (IN `p_task_id` INT)   BEGIN
    DECLARE v_total INT; DECLARE v_done INT;
    SELECT COUNT(*), SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) INTO v_total, v_done FROM subtasks WHERE task_id = p_task_id;
    IF v_total > 0 AND v_total = v_done THEN UPDATE tasks SET status = 'Done' WHERE id = p_task_id;
    ELSEIF v_done > 0 THEN UPDATE tasks SET status = 'In Progress' WHERE id = p_task_id; END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `action_type`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES
(1, NULL, 1, 'GLOBAL_BROADCAST', 'System', NULL, 'Gửi thông báo toàn hệ thống tới: ceos', '::1', '2026-04-17 18:24:02');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_comment_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comment_reactions`
--

CREATE TABLE `comment_reactions` (
  `id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `ceo_name` varchar(255) NOT NULL,
  `ceo_email` varchar(255) NOT NULL,
  `ceo_phone` varchar(20) DEFAULT NULL,
  `ceo_password_hash` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `max_users` int(11) DEFAULT 100,
  `max_projects` int(11) DEFAULT 50,
  `max_departments` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `company_name`, `industry`, `ceo_name`, `ceo_email`, `ceo_phone`, `ceo_password_hash`, `status`, `created_at`, `updated_at`, `max_users`, `max_projects`, `max_departments`) VALUES
(1, 'TechMinds Global', 'Technology', 'Phạm Nhật Vượng', 'ceo@techminds.com', NULL, '$2y$10$gF7F0tvZQ61adXUqg5jW7Ou8MAHAJbSqEW3E7xx13LrwzwmY/gHOG', 'approved', '2026-04-17 18:17:00', '2026-04-17 18:17:00', 50, 20, 10);

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `type` enum('Direct','Group') DEFAULT 'Direct',
  `name` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `avatar_url` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `requires_approval` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_members`
--

CREATE TABLE `conversation_members` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_read_at` datetime DEFAULT current_timestamp(),
  `role` enum('admin','member') DEFAULT 'member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `dept_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `company_id`, `dept_name`, `description`, `created_at`) VALUES
(1, 1, 'Phòng Kỹ thuật', 'Nghiên cứu và phát triển phần mềm', '2026-04-17 18:17:00'),
(2, 1, 'Phòng Nhân sự', 'Quản lý con người và văn hóa công ty', '2026-04-17 18:17:00'),
(3, 1, 'Phòng Marketing', 'Truyền thông và quảng bá sản phẩm', '2026-04-17 18:17:00'),
(4, 1, 'Phòng Kinh doanh', 'Phát triển thị trường và doanh số', '2026-04-17 18:17:00'),
(5, 1, 'Phòng CSKH', 'Hỗ trợ và chăm sóc khách hàng', '2026-04-17 18:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `membership_requests`
--

CREATE TABLE `membership_requests` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invited_by` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `trigger_user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `company_id`, `type`, `trigger_user_id`, `content`, `target_url`, `created_at`) VALUES
(1, 1, 'GLOBAL_BROADCAST', 1, '[HỆ THỐNG]: hello', 'index.php?action=dashboard', '2026-04-17 18:24:02');

-- --------------------------------------------------------

--
-- Table structure for table `notification_user`
--

CREATE TABLE `notification_user` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_user`
--

INSERT INTO `notification_user` (`notification_id`, `user_id`, `is_read`, `read_at`) VALUES
(1, 2, 1, '2026-04-17 18:26:24');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `task_report_id` int(11) DEFAULT NULL,
  `visibility` enum('Public','Department','Private','Announcement') DEFAULT 'Public',
  `content_html` text NOT NULL,
  `is_ai_generated` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `company_id`, `author_id`, `department_id`, `task_report_id`, `visibility`, `content_html`, `is_ai_generated`, `created_at`, `updated_at`) VALUES
(1, 1, 2, NULL, NULL, 'Public', 'Chào mừng toàn thể anh chị em đến với ngôi nhà chung TechMinds Global! 🚀', 0, '2026-04-17 18:17:00', '2026-04-17 18:17:00'),
(2, 1, 3, NULL, NULL, 'Public', 'Dự án AI Assistant đang tiến triển rất tốt, cảm ơn team Kỹ thuật nhé!', 0, '2026-04-17 18:17:00', '2026-04-17 18:17:00'),
(3, 1, 2, NULL, NULL, 'Announcement', '🔈 Thông báo: Thứ 2 tuần sau chúng ta sẽ có buổi Teambuilding tại văn phòng.', 0, '2026-04-17 18:17:00', '2026-04-17 18:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `post_edit_history`
--

CREATE TABLE `post_edit_history` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `old_content` text NOT NULL,
  `changed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_media`
--

CREATE TABLE `post_media` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `media_type` enum('Image','Video') DEFAULT 'Image',
  `media_url` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_reactions`
--

CREATE TABLE `post_reactions` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('Like','Heart','Haha','Wow','Sad','Angry') DEFAULT 'Like',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Completed') DEFAULT 'Active',
  `created_by` int(11) NOT NULL,
  `deadline` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `company_id`, `title`, `description`, `status`, `created_by`, `deadline`, `created_at`, `updated_at`) VALUES
(1, 1, 'Hệ thống AI Assistant', 'Phát triển trợ lý AI nội bộ cho doanh nghiệp', 'Active', 2, '2026-12-31 00:00:00', '2026-04-17 18:17:00', '2026-04-17 18:17:00'),
(2, 1, 'Tái cấu trúc Database', 'Nâng cấp hệ thống lên Multi-tenant và tối ưu hóa truy vấn', 'Active', 2, '2026-06-30 00:00:00', '2026-04-17 18:17:00', '2026-04-17 18:17:00'),
(3, 1, 'Chiến dịch \"Green Office\"', 'Hệ thống giảm thiểu rác thải và tiết kiệm điện văn phòng', 'Active', 2, '2026-05-15 00:00:00', '2026-04-17 18:17:00', '2026-04-17 18:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `project_departments`
--

CREATE TABLE `project_departments` (
  `project_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_departments`
--

INSERT INTO `project_departments` (`project_id`, `department_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'CEO'),
(2, 'Leader'),
(3, 'Staff'),
(4, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `subtasks`
--

CREATE TABLE `subtasks` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `assignee_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `status` enum('To Do','In Progress','Pending','Done') DEFAULT 'To Do',
  `completion_rating` float DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `feedback` text DEFAULT NULL,
  `is_extended` tinyint(1) DEFAULT 0,
  `is_rejected` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 0,
  `extension_requested_at` datetime DEFAULT NULL,
  `requested_deadline` datetime DEFAULT NULL,
  `extension_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subtasks`
--

INSERT INTO `subtasks` (`id`, `task_id`, `assignee_id`, `title`, `description`, `deadline`, `priority`, `status`, `completion_rating`, `created_at`, `updated_at`, `feedback`, `is_extended`, `is_rejected`, `is_approved`, `extension_requested_at`, `requested_deadline`, `extension_reason`) VALUES
(1, 1, 8, 'Công việc con 0 cho Task 1 của Project 1', NULL, '2026-04-30 00:00:00', 'Medium', 'Done', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(2, 1, 10, 'Công việc con 1 cho Task 1 của Project 1', NULL, '2026-04-30 00:00:00', 'Low', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(3, 1, 8, 'Công việc con 2 cho Task 1 của Project 1', NULL, '2026-04-30 00:00:00', 'Medium', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(4, 2, 7, 'Công việc con 0 cho Task 2 của Project 1', NULL, '2026-04-30 00:00:00', 'Low', 'Done', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(5, 2, 12, 'Công việc con 1 cho Task 2 của Project 1', NULL, '2026-04-30 00:00:00', 'Low', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(6, 2, 4, 'Công việc con 2 cho Task 2 của Project 1', NULL, '2026-04-30 00:00:00', 'Low', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(7, 3, 3, 'Công việc con 0 cho Task 3 của Project 1', NULL, '2026-04-30 00:00:00', 'Low', 'Done', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(8, 3, 13, 'Công việc con 1 cho Task 3 của Project 1', NULL, '2026-04-30 00:00:00', 'Low', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(9, 3, 20, 'Công việc con 2 cho Task 3 của Project 1', NULL, '2026-04-30 00:00:00', 'Medium', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(10, 4, 4, 'Công việc con 0 cho Task 1 của Project 2', NULL, '2026-04-30 00:00:00', 'Low', 'Done', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(11, 4, 20, 'Công việc con 1 cho Task 1 của Project 2', NULL, '2026-04-30 00:00:00', 'High', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(12, 4, 17, 'Công việc con 2 cho Task 1 của Project 2', NULL, '2026-04-30 00:00:00', 'Low', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(13, 5, 6, 'Công việc con 0 cho Task 2 của Project 2', NULL, '2026-04-30 00:00:00', 'High', 'Done', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(14, 5, 8, 'Công việc con 1 cho Task 2 của Project 2', NULL, '2026-04-30 00:00:00', 'Medium', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(15, 5, 22, 'Công việc con 2 cho Task 2 của Project 2', NULL, '2026-04-30 00:00:00', 'Low', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(16, 6, 22, 'Công việc con 0 cho Task 3 của Project 2', NULL, '2026-04-30 00:00:00', 'Low', 'Done', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(17, 6, 10, 'Công việc con 1 cho Task 3 của Project 2', NULL, '2026-04-30 00:00:00', 'High', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(18, 6, 11, 'Công việc con 2 cho Task 3 của Project 2', NULL, '2026-04-30 00:00:00', 'High', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(19, 7, 20, 'Công việc con 0 cho Task 1 của Project 3', NULL, '2026-04-30 00:00:00', 'High', 'Done', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(20, 7, 7, 'Công việc con 1 cho Task 1 của Project 3', NULL, '2026-04-30 00:00:00', 'Medium', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(21, 7, 22, 'Công việc con 2 cho Task 1 của Project 3', NULL, '2026-04-30 00:00:00', 'Medium', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(22, 8, 4, 'Công việc con 0 cho Task 2 của Project 3', NULL, '2026-04-30 00:00:00', 'Low', 'Done', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(23, 8, 19, 'Công việc con 1 cho Task 2 của Project 3', NULL, '2026-04-30 00:00:00', 'Medium', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(24, 8, 3, 'Công việc con 2 cho Task 2 của Project 3', NULL, '2026-04-30 00:00:00', 'Low', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(25, 9, 20, 'Công việc con 0 cho Task 3 của Project 3', NULL, '2026-04-30 00:00:00', 'Medium', 'Done', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(26, 9, 14, 'Công việc con 1 cho Task 3 của Project 3', NULL, '2026-04-30 00:00:00', 'Low', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL),
(27, 9, 5, 'Công việc con 2 cho Task 3 của Project 3', NULL, '2026-04-30 00:00:00', 'Medium', 'In Progress', NULL, '2026-04-17 18:17:00', '2026-04-17 18:17:00', NULL, 0, 0, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subtask_attachments`
--

CREATE TABLE `subtask_attachments` (
  `id` int(11) NOT NULL,
  `subtask_id` int(11) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_url` varchar(500) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `assigned_leader_id` int(11) DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `deadline` datetime DEFAULT NULL,
  `status` enum('To Do','In Progress','Pending_CEO','Done') DEFAULT 'To Do',
  `approval_status` enum('Pending','Submitted','Approved','Rejected') DEFAULT 'Pending',
  `ai_report_post_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `company_id`, `project_id`, `assigned_leader_id`, `department_id`, `created_by_user_id`, `title`, `description`, `priority`, `deadline`, `status`, `approval_status`, `ai_report_post_id`, `created_at`) VALUES
(1, 1, 1, NULL, 1, 2, 'Task 1 của Project 1', 'Mô tả chi tiết cho Task 1 của Project 1', 'Medium', '2026-05-01 00:00:00', 'In Progress', 'Pending', NULL, '2026-04-17 18:17:00'),
(2, 1, 1, NULL, 2, 2, 'Task 2 của Project 1', 'Mô tả chi tiết cho Task 2 của Project 1', 'Medium', '2026-05-01 00:00:00', 'In Progress', 'Pending', NULL, '2026-04-17 18:17:00'),
(3, 1, 1, NULL, 3, 2, 'Task 3 của Project 1', 'Mô tả chi tiết cho Task 3 của Project 1', 'Medium', '2026-05-01 00:00:00', 'In Progress', 'Pending', NULL, '2026-04-17 18:17:00'),
(4, 1, 2, NULL, 1, 2, 'Task 1 của Project 2', 'Mô tả chi tiết cho Task 1 của Project 2', 'Medium', '2026-05-01 00:00:00', 'In Progress', 'Pending', NULL, '2026-04-17 18:17:00'),
(5, 1, 2, NULL, 2, 2, 'Task 2 của Project 2', 'Mô tả chi tiết cho Task 2 của Project 2', 'Medium', '2026-05-01 00:00:00', 'In Progress', 'Pending', NULL, '2026-04-17 18:17:00'),
(6, 1, 2, NULL, 3, 2, 'Task 3 của Project 2', 'Mô tả chi tiết cho Task 3 của Project 2', 'Medium', '2026-05-01 00:00:00', 'In Progress', 'Pending', NULL, '2026-04-17 18:17:00'),
(7, 1, 3, NULL, 1, 2, 'Task 1 của Project 3', 'Mô tả chi tiết cho Task 1 của Project 3', 'Medium', '2026-05-01 00:00:00', 'In Progress', 'Pending', NULL, '2026-04-17 18:17:00'),
(8, 1, 3, NULL, 2, 2, 'Task 2 của Project 3', 'Mô tả chi tiết cho Task 2 của Project 3', 'Medium', '2026-05-01 00:00:00', 'In Progress', 'Pending', NULL, '2026-04-17 18:17:00'),
(9, 1, 3, NULL, 3, 2, 'Task 3 của Project 3', 'Mô tả chi tiết cho Task 3 của Project 3', 'Medium', '2026-05-01 00:00:00', 'In Progress', 'Pending', NULL, '2026-04-17 18:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `task_reports`
--

CREATE TABLE `task_reports` (
  `id` int(11) NOT NULL,
  `subtask_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `q1_answer` text DEFAULT NULL,
  `q2_answer` text DEFAULT NULL,
  `q3_answer` text DEFAULT NULL,
  `ai_generated_content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `birthdate` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `cover_url` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `link_tiktok` varchar(255) DEFAULT NULL,
  `link_facebook` varchar(255) DEFAULT NULL,
  `link_instagram` varchar(255) DEFAULT NULL,
  `link_telegram` varchar(255) DEFAULT NULL,
  `hide_birthdate` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `company_id`, `department_id`, `role_id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `avatar_url`, `birthdate`, `created_at`, `is_active`, `cover_url`, `location`, `link_tiktok`, `link_facebook`, `link_instagram`, `link_telegram`, `hide_birthdate`) VALUES
(1, NULL, NULL, 4, 'admin', '$2y$10$K5csXtRW3QdKApg0XTse3.dfw4Pul0MyxZ1U91ZLGB55MP1aMNkq6', 'Super Administrator', 'admin@relioo.com', NULL, NULL, NULL, '2026-04-17 18:16:59', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2, 1, NULL, 1, 'ceo_techminds', '$2y$10$ZG1xT/E4DXFjQGUeSake1ugaAVi3Md8bAyzh/5Sy2CHEB9Gem94jS', 'Phạm Nhật Vượng (CEO)', 'ceo@techminds.com', NULL, NULL, NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(3, 1, 1, 2, 'emp_1', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Hoàng Anh', 'emp_1@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_1', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(4, 1, 2, 2, 'emp_2', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Vũ Bình', 'emp_2@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_2', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(5, 1, 3, 2, 'emp_3', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Trần Chi', 'emp_3@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_3', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(6, 1, 4, 2, 'emp_4', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Hoàng Dũng', 'emp_4@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_4', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(7, 1, 5, 2, 'emp_5', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Phạm Em', 'emp_5@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_5', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(8, 1, 1, 3, 'emp_6', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Trần Hùng', 'emp_6@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_6', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(9, 1, 2, 3, 'emp_7', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Bùi Hải', 'emp_7@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_7', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(10, 1, 3, 3, 'emp_8', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Lê Lâm', 'emp_8@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_8', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(11, 1, 4, 3, 'emp_9', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Hoàng Minh', 'emp_9@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_9', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(12, 1, 5, 3, 'emp_10', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Đặng Nam', 'emp_10@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_10', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(13, 1, 1, 3, 'emp_11', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Lê Oanh', 'emp_11@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_11', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(14, 1, 2, 3, 'emp_12', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Nguyễn Phương', 'emp_12@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_12', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(15, 1, 3, 3, 'emp_13', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Đỗ Quân', 'emp_13@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_13', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(16, 1, 4, 3, 'emp_14', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Trần Sơn', 'emp_14@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_14', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(17, 1, 5, 3, 'emp_15', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Hoàng Tuấn', 'emp_15@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_15', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(18, 1, 1, 3, 'emp_16', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Trần Vân', 'emp_16@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_16', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(19, 1, 2, 3, 'emp_17', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Phạm Yến', 'emp_17@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_17', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(20, 1, 3, 3, 'emp_18', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Trần Khoa', 'emp_18@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_18', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(21, 1, 4, 3, 'emp_19', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Nguyễn Thịnh', 'emp_19@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_19', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(22, 1, 5, 3, 'emp_20', '$2y$10$.MuWbsyaeUyzYlDPf1yh4e0inZfxR40nQ2SbTdL7tofUBha3vDEqq', 'Đỗ Đạt', 'emp_20@techminds.com', NULL, 'https://i.pravatar.cc/150?u=emp_20', NULL, '2026-04-17 18:17:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_company` (`company_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action_type`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_comment_id` (`parent_comment_id`),
  ADD KEY `idx_comments_post_date` (`post_id`,`created_at`),
  ADD KEY `fk_comment_company` (`company_id`);

--
-- Indexes for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_comment_reaction` (`comment_id`,`user_id`),
  ADD KEY `idx_comment_reactions_comment` (`comment_id`,`user_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversation_members`
--
ALTER TABLE `conversation_members`
  ADD PRIMARY KEY (`conversation_id`,`user_id`),
  ADD KEY `idx_conv_members_user` (`user_id`,`conversation_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dept_company` (`company_id`);

--
-- Indexes for table `membership_requests`
--
ALTER TABLE `membership_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `idx_messages_conv_date` (`conversation_id`,`created_at`),
  ADD KEY `idx_messages_conv_id` (`conversation_id`,`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trigger_user_id` (`trigger_user_id`),
  ADD KEY `fk_notif_company` (`company_id`);

--
-- Indexes for table `notification_user`
--
ALTER TABLE `notification_user`
  ADD PRIMARY KEY (`notification_id`,`user_id`),
  ADD KEY `idx_noti_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `task_report_id` (`task_report_id`),
  ADD KEY `idx_posts_visibility_date` (`visibility`,`created_at`),
  ADD KEY `idx_posts_dept_visibility` (`department_id`,`visibility`),
  ADD KEY `fk_post_company` (`company_id`);

--
-- Indexes for table `post_edit_history`
--
ALTER TABLE `post_edit_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `post_media`
--
ALTER TABLE `post_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `post_reactions`
--
ALTER TABLE `post_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_post_reaction` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_post_reactions_post` (`post_id`,`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_created_by` (`created_by`),
  ADD KEY `fk_proj_company` (`company_id`);

--
-- Indexes for table `project_departments`
--
ALTER TABLE `project_departments`
  ADD PRIMARY KEY (`project_id`,`department_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subtasks`
--
ALTER TABLE `subtasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subtasks_assignee_status` (`assignee_id`,`status`),
  ADD KEY `idx_subtasks_task_status` (`task_id`,`status`);

--
-- Indexes for table `subtask_attachments`
--
ALTER TABLE `subtask_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subtask_id` (`subtask_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `created_by_user_id` (`created_by_user_id`),
  ADD KEY `fk_task_post` (`ai_report_post_id`),
  ADD KEY `idx_task_project_id` (`project_id`),
  ADD KEY `idx_task_approval_status` (`approval_status`),
  ADD KEY `fk_task_company` (`company_id`);

--
-- Indexes for table `task_reports`
--
ALTER TABLE `task_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subtask_id` (`subtask_id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `fk_user_company` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `membership_requests`
--
ALTER TABLE `membership_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `post_edit_history`
--
ALTER TABLE `post_edit_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_media`
--
ALTER TABLE `post_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_reactions`
--
ALTER TABLE `post_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subtasks`
--
ALTER TABLE `subtasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `subtask_attachments`
--
ALTER TABLE `subtask_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `task_reports`
--
ALTER TABLE `task_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comment_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  ADD CONSTRAINT `comment_reactions_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_members`
--
ALTER TABLE `conversation_members`
  ADD CONSTRAINT `conversation_members_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_dept_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`trigger_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notification_user`
--
ALTER TABLE `notification_user`
  ADD CONSTRAINT `notification_user_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_post_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`task_report_id`) REFERENCES `task_reports` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `post_edit_history`
--
ALTER TABLE `post_edit_history`
  ADD CONSTRAINT `post_edit_history_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_media`
--
ALTER TABLE `post_media`
  ADD CONSTRAINT `post_media_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_reactions`
--
ALTER TABLE `post_reactions`
  ADD CONSTRAINT `post_reactions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_proj_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_departments`
--
ALTER TABLE `project_departments`
  ADD CONSTRAINT `project_departments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_departments_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subtasks`
--
ALTER TABLE `subtasks`
  ADD CONSTRAINT `subtasks_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subtasks_ibfk_2` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `subtask_attachments`
--
ALTER TABLE `subtask_attachments`
  ADD CONSTRAINT `subtask_attachments_ibfk_1` FOREIGN KEY (`subtask_id`) REFERENCES `subtasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_task_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_task_post` FOREIGN KEY (`ai_report_post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `task_reports`
--
ALTER TABLE `task_reports`
  ADD CONSTRAINT `task_reports_ibfk_1` FOREIGN KEY (`subtask_id`) REFERENCES `subtasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_reports_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
