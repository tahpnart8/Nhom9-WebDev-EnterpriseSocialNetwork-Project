-- ============================================================
-- RELIOO DB: OPTIMIZE INDEXES FOR REMOTE DATABASE ACCESS
-- Chạy file này 1 lần trên phpMyAdmin để thêm indexes
-- ============================================================

USE relioo_db;

-- notification_user: Lọc thông báo chưa đọc cho user
CREATE INDEX idx_noti_user_read ON notification_user (user_id, is_read);

-- posts: Lọc bài theo kênh (Public/Department) + sắp xếp mới nhất
CREATE INDEX idx_posts_visibility_date ON posts (visibility, created_at DESC);
CREATE INDEX idx_posts_dept_visibility ON posts (department_id, visibility);

-- subtasks: Kanban board phân cột theo status cho từng user
CREATE INDEX idx_subtasks_assignee_status ON subtasks (assignee_id, status);
-- subtasks: Đếm subtask_count / done_count theo task
CREATE INDEX idx_subtasks_task_status ON subtasks (task_id, status);

-- messages: Lấy tin nhắn gần nhất trong conversation
CREATE INDEX idx_messages_conv_date ON messages (conversation_id, created_at DESC);

-- comments: Lấy bình luận theo bài viết
CREATE INDEX idx_comments_post_date ON comments (post_id, created_at);

-- post_reactions: Đếm lượt thích theo post
CREATE INDEX idx_post_reactions_post ON post_reactions (post_id, user_id);

-- comment_reactions: Đếm lượt thích bình luận
CREATE INDEX idx_comment_reactions_comment ON comment_reactions (comment_id, user_id);

-- conversation_members: Tìm conversations của user
CREATE INDEX idx_conv_members_user ON conversation_members (user_id, conversation_id);
