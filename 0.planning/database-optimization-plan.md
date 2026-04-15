# Kế hoạch tối ưu hóa Database bằng Stored Procedures

Dự án: **Enterprise Social Network (Relioo)**
Mục tiêu: Chuyển đổi các logic truy vấn phức tạp, các thao tác mang tính giao dịch (transaction) và các thống kê nặng từ Backend (PHP/PDO) sang Stored Procedures trong MySQL để tối ưu hiệu suất và bảo mật.

---

## 1. Phân hệ Social Core (Mạng xã hội nội bộ)

### sp_GetFeed
- **Mục đích:** Lấy danh sách bài đăng cho Newsfeed dựa trên quyền hạn (Visibility) và bộ lọc.
- **Logic:**
    - JOIN các bảng: `posts`, `users`, `roles`, `post_media`.
    - Tính toán số lượng Like/Comment bằng subquery hoặc JOIN với GROUP BY.
    - Kiểm tra trạng thái "đã thích" của người dùng hiện tại (`is_liked`).
    - Hỗ trợ lọc theo: `Public`, `Announcement`, `Department`.
    - Hỗ trợ tìm kiếm theo nội dung bài viết hoặc tên tác giả.
- **Thay thế:** `Post::getFeed()`

### sp_TogglePostReaction
- **Mục đích:** Xử lý việc Like/Unlike một bài viết.
- **Logic:**
    - Kiểm tra nếu đã tồn tại bản ghi trong `post_reactions`.
    - Nếu có: DELETE (Unlike).
    - Nếu chưa: INSERT (Like).
    - Trả về hành động vừa thực hiện (`added` hoặc `deleted`).
- **Thay thế:** `Post::toggleReaction()`

### sp_GetPostComments
- **Mục đích:** Lấy danh sách bình luận của một bài viết kèm thông tin người dùng và phản ứng.
- **Logic:**
    - JOIN `comments` với `users`.
    - Sắp xếp theo cây (nếu có hỗ trợ reply) hoặc theo thời gian.

---

## 2. Phân hệ Quản lý Công việc (Task & Subtask)

### sp_GetTaskStatistics
- **Mục đích:** Thống kê tổng quan công việc cho Dashboard.
- **Logic:**
    - Đếm tổng số Task, Subtask.
    - Thống kê theo trạng thái: `Done`, `In Progress`, `To Do`, `Pending`, `Overdue`.
    - Lọc theo `department_id` hoặc `assignee_id`.
- **Thay thế:** `Task::getTaskStats()` và `Subtask::getSubtaskStats()`

### sp_GetUrgentSubtasks
- **Mục đích:** Lấy danh sách công việc khẩn cấp (gần đến hạn) cho Sidebar.
- **Logic:**
    - Lọc `subtasks` theo `assignee_id` và trạng thái chưa hoàn thành.
    - JOIN với `tasks` để lấy tiêu đề Task cha và tiến độ tổng thể.
    - Sắp xếp theo `deadline` ASC.
- **Thay thế:** `Subtask::getUrgentSubtasksByUser()`

### sp_SubmitSubtaskEvidence
- **Mục đích:** Xử lý việc nộp minh chứng công việc.
- **Logic:**
    - Chạy trong một TRANSACTION.
    - Cập nhật trạng thái Subtask sang `Pending`.
    - INSERT vào bảng `subtask_attachments`.
- **Thay thế:** `Subtask::submitEvidence()`

### sp_GetWorkloadStats
- **Mục đích:** Thống kê khối lượng công việc (Workload) để vẽ biểu đồ.
- **Logic:**
    - Case 1 (CEO): GROUP BY `department_id`.
    - Case 2 (Leader): GROUP BY `user_id` trong một phòng ban.
- **Thay thế:** `Subtask::getWorkloadByDepartment()` và `getWorkloadByAssignee()`

### sp_GetSubtaskStatsDetailed (Procedure 16)
- **Mục đích:** Thống kê chi tiết số lượng Subtask theo từng trạng thái (Done, Pending, Overdue...) phục vụ Dashboard.
- **Logic:** Sử dụng CASE WHEN để đếm các trạng thái khác nhau của Subtask trong cùng một query. Hỗ trợ lọc theo phòng ban hoặc theo nhân viên cụ thể.
- **Thay thế:** `Subtask::getSubtaskStats()`

---

## 3. Phân hệ Thông báo & Người dùng

### sp_GetUnreadNotifications
- **Mục đích:** Lấy danh sách thông báo chưa đọc của người dùng.
- **Logic:**
    - JOIN `notifications` với `notification_user`.
    - Lọc theo `user_id` và `is_read = 0`.
    - Sắp xếp theo `created_at` DESC.

### sp_SearchUsers
- **Mục đích:** Tìm kiếm nhân viên nhanh.
- **Logic:**
    - JOIN `users` với `departments` và `roles`.
    - Tìm kiếm theo `full_name`, `username` hoặc `email`.
- **Thay thế:** `User::getAllUsersWithDetails()` (phiên bản có filter)

---

## 4. Phân hệ Tích hợp (Socialize Tasks)

### sp_CreateReportAndSocialPost
- **Mục đích:** Tự động tạo bài đăng mạng xã hội khi hoàn thành báo cáo công việc.
- **Logic:**
    - TRANSACTION.
    - INSERT vào `task_reports`.
    - INSERT vào `posts` với `visibility = 'Department'` và nội dung AI generated (nếu có).
    - Liên kết `post.task_report_id` với báo cáo vừa tạo.
- **Thay thế:** Logic phối hợp trong `TaskController` hoặc `SocialController`.


## 3. Phân hệ Chat & Trao đổi (Messaging)

### sp_GetConversationMessages
- **Mục đích:** Tải lịch sử tin nhắn trong một cuộc hội thoại với đầy đủ thông tin người gửi.
- **Logic:** 
    - JOIN `messages` với `users`.
    - Lấy thông tin `avatar_url` và `full_name`.
    - Hỗ trợ phân trang để tối ưu tốc độ tải.
- **Thay thế:** `Message::getByConversation()`

### sp_MarkMessagesAsRead
- **Mục đích:** Cập nhật trạng thái đã xem cho tin nhắn.
- **Logic:** 
    - UPDATE cột `last_read_at` trong bảng `conversation_members` cho user cụ thể.

### sp_CreateGroupChat
- **Mục đích:** Tạo nhóm chat mới kèm theo danh sách thành viên.
- **Logic:** 
    - TRANSACTION: INSERT `conversations` -> Lấy ID -> INSERT nhiều bản ghi vào `conversation_members`.

---

## 4. Phân hệ Phân tích & Báo cáo (Analytics)

### sp_GetEmployeePerformance
- **Mục đích:** Tính toán điểm hiệu suất nhân viên.
- **Logic:** 
    - Tổng hợp số Task hoàn thành, tỷ lệ đúng hạn, và điểm đánh giá trung bình.

### sp_GetDepartmentLeaderboard
- **Mục đích:** Bảng xếp hạng nhân viên xuất sắc.
- **Logic:** 
    - GROUP BY `user_id`, tính điểm dựa trên độ khó và số lượng công việc đã xong.

---

## 5. Phân hệ Quản trị & Hệ thống (Admin)

### sp_Admin_GetSystemOverview
- **Mục đích:** Dashboard cho CEO xem toàn cảnh doanh nghiệp.
- **Logic:** 
    - Thống kê tổng nhân sự, bài đăng mới, và các dự án đang chạy trong cùng 1 query.

### sp_BatchUpdateTaskStatus
- **Mục đích:** Cập nhật trạng thái cho nhiều Task/Subtask cùng lúc theo điều kiện.

---

## 6. Phân hệ Tương tác nâng cao

### sp_ToggleCommentReaction
- **Mục đích:** Like/Unlike dành cho bình luận.
- **Logic:** 
    - Xử lý INSERT/DELETE trên bảng `comment_reactions`.

### sp_GetSubtaskTimeline
- **Mục đích:** Lấy lịch sử diễn biến của một công việc (Từ lúc giao đến lúc báo cáo và duyệt).
- **Logic:** 
    - Gộp dữ liệu từ `subtasks`, `subtask_attachments`, và các thông báo liên quan.

---

## 7. Danh sách Procedure chi tiết (Mở rộng)

| Tên Procedure | Tham số chính | Chức năng chính |
| :--- | :--- | :--- |
| `sp_GetFeed` | `p_user_id, p_role_id, p_dept_id, p_channel, p_search` | Lấy tin tức Newsfeed tối ưu |
| `sp_TogglePostReaction` | `p_post_id, p_user_id` | Like/Unlike bài viết |
| `sp_GetDashboardOverview` | `p_user_id, p_dept_id, p_role_id` | Thống kê tổng hợp các con số Dashboard |
| `sp_GetUrgentTasks` | `p_user_id` | Lấy việc gấp cho Sidebar |
| `sp_SubmitSubtaskEvidence`| `p_subtask_id, p_notes, p_file_url` | Nộp bài và đổi trạng thái (Transaction) |
| `sp_GetWorkloadStats` | `p_dept_id (optional)` | Dữ liệu cho biểu đồ Workload |
| `sp_CreateTaskReportPost` | `p_task_id, p_subtask_id, p_content, p_author_id` | Báo cáo công việc + Tự động đăng bài |
| `sp_GetUnreadNotis` | `p_user_id` | Lấy thông báo chưa đọc |
| `sp_SearchUsers` | `p_keyword` | Tìm kiếm đồng nghiệp |
| `sp_UpdateTaskStatus` | `p_task_id, p_status` | Cập nhật trạng thái Task cha đồng bộ |
| `sp_GetConversationMessages`| `p_conv_id, p_limit, p_offset` | Lấy tin nhắn chat kèm user info |
| `sp_MarkMessagesAsRead` | `p_conv_id, p_user_id` | Đánh dấu đã đọc |
| `sp_GetEmployeePerformance`| `p_user_id` | Thống kê KPI nhân viên |
| `sp_GetLeaderboard` | `p_dept_id` | Xếp hạng nhân viên |
| `sp_ToggleCommentReaction` | `p_comment_id, p_user_id` | Tương tác với bình luận |

---

## 8. Mã nguồn SQL chi tiết (Implementation)

```sql
DELIMITER $$

-- 1. sp_GetFeed: Lấy danh sách bài đăng Newsfeed tối ưu
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

-- 2. sp_TogglePostReaction: Like/Unlike bài viết
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

-- 3. sp_GetDashboardOverview: Thống kê tổng hợp Dashboard
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

-- 4. sp_GetUrgentTasks: Lấy việc gấp cho Sidebar
CREATE PROCEDURE sp_GetUrgentTasks(IN p_user_id INT)
BEGIN
    SELECT s.*, t.title as parent_task_title, COALESCE(tc.total, 0) as parent_total, COALESCE(tc.done, 0) as parent_done
    FROM subtasks s JOIN tasks t ON s.task_id = t.id
    LEFT JOIN (SELECT task_id, COUNT(*) as total, SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done FROM subtasks GROUP BY task_id) tc ON tc.task_id = t.id
    WHERE s.assignee_id = p_user_id AND s.status IN ('To Do', 'In Progress')
    ORDER BY s.deadline ASC LIMIT 10;
END$$

-- 5. sp_SubmitSubtaskEvidence: Nộp minh chứng (Transaction)
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

-- 6. sp_GetWorkloadStats: Thống kê biểu đồ Workload
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

-- 7. sp_CreateTaskReportPost: Báo cáo + Tự động đăng bài
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

-- 8. sp_GetUnreadNotis: Lấy thông báo chưa đọc
CREATE PROCEDURE sp_GetUnreadNotis(IN p_user_id INT)
BEGIN
    SELECT n.*, nu.is_read FROM notifications n JOIN notification_user nu ON n.id = nu.notification_id
    WHERE nu.user_id = p_user_id AND nu.is_read = 0 ORDER BY n.created_at DESC;
END$$

-- 9. sp_SearchUsers: Tìm kiếm đồng nghiệp
CREATE PROCEDURE sp_SearchUsers(IN p_keyword VARCHAR(100))
BEGIN
    SELECT u.id, u.full_name, u.email, u.avatar_url, d.dept_name, r.role_name FROM users u
    LEFT JOIN departments d ON u.department_id = d.id LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.full_name LIKE CONCAT('%', p_keyword, '%') OR u.username LIKE CONCAT('%', p_keyword, '%') LIMIT 20;
END$$

-- 10. sp_UpdateTaskStatusSync: Đồng bộ trạng thái Task cha
CREATE PROCEDURE sp_UpdateTaskStatusSync(IN p_task_id INT)
BEGIN
    DECLARE v_total INT; DECLARE v_done INT;
    SELECT COUNT(*), SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) INTO v_total, v_done FROM subtasks WHERE task_id = p_task_id;
    IF v_total > 0 AND v_total = v_done THEN UPDATE tasks SET status = 'Done' WHERE id = p_task_id;
    ELSEIF v_done > 0 THEN UPDATE tasks SET status = 'In Progress' WHERE id = p_task_id; END IF;
END$$

-- 11. sp_GetConversationMessages: Lấy tin nhắn chat
CREATE PROCEDURE sp_GetConversationMessages(IN p_conv_id INT, IN p_limit INT, IN p_offset INT)
BEGIN
    SELECT m.*, u.full_name, u.avatar_url FROM messages m JOIN users u ON m.sender_id = u.id
    WHERE m.conversation_id = p_conv_id ORDER BY m.created_at DESC LIMIT p_limit OFFSET p_offset;
END$$

-- 12. sp_MarkMessagesAsRead: Đánh dấu đã xem
CREATE PROCEDURE sp_MarkMessagesAsRead(IN p_conv_id INT, IN p_user_id INT)
BEGIN
    UPDATE conversation_members SET last_read_at = NOW() WHERE conversation_id = p_conv_id AND user_id = p_user_id;
END$$

-- 13. sp_GetEmployeePerformance: KPI nhân viên
CREATE PROCEDURE sp_GetEmployeePerformance(IN p_user_id INT)
BEGIN
    SELECT COUNT(*) as total_assigned, SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN deadline < NOW() AND status != 'Done' THEN 1 ELSE 0 END) as overdue,
    ROUND((SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as efficiency_rate
    FROM subtasks WHERE assignee_id = p_user_id;
END$$

-- 14. sp_GetLeaderboard: Xếp hạng phòng ban
CREATE PROCEDURE sp_GetLeaderboard(IN p_dept_id INT)
BEGIN
    SELECT u.full_name, u.avatar_url, COUNT(s.id) as tasks_done FROM users u JOIN subtasks s ON u.id = s.assignee_id
    WHERE u.department_id = p_dept_id AND s.status = 'Done' GROUP BY u.id ORDER BY tasks_done DESC LIMIT 10;
END$$

-- 15. sp_ToggleCommentReaction: Like/Unlike bình luận
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

-- 16. sp_GetSubtaskStatsDetailed: Thống kê chi tiết Subtask cho Dashboard
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
```

## Lợi ích đạt được:
1. **Tốc độ:** Giảm thiểu số lượng query từ PHP sang MySQL (ví dụ: gộp các subquery count vào một Procedure).
2. **Bảo mật:** Giấu kín cấu trúc database thực tế, Backend chỉ gọi hàm.
3. **Tính toàn vẹn:** Sử dụng TRANSACTION ngay trong DB cho các thao tác quan trọng (Báo cáo + Đăng bài).
4. **Dễ bảo trì:** Khi cần thay đổi logic truy vấn, chỉ cần sửa Procedure, không cần deploy lại code Backend.
