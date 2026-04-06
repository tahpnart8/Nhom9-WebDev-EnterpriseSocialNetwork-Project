-- =============================================
-- Relioo Enterprise Social Network
-- PostgreSQL Schema (Supabase)
-- =============================================

-- Bảng phòng ban
CREATE TABLE IF NOT EXISTS departments (
    id SERIAL PRIMARY KEY,
    dept_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng vai trò
CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL
);

-- Mặc định Roles
INSERT INTO roles (id, role_name) VALUES 
(1, 'CEO'),
(2, 'Leader'),
(3, 'Staff'),
(4, 'Admin')
ON CONFLICT (id) DO NOTHING;

-- Reset sequence để tránh trùng ID
SELECT setval('roles_id_seq', (SELECT MAX(id) FROM roles));

-- Bảng người dùng
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    department_id INT,
    role_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    avatar_url VARCHAR(255),
    birthdate DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active SMALLINT DEFAULT 1,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Bảng công việc lớn (Tasks)
CREATE TABLE IF NOT EXISTS tasks (
    id SERIAL PRIMARY KEY,
    department_id INT NOT NULL,
    created_by_user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority VARCHAR(10) DEFAULT 'Medium' CHECK (priority IN ('Low', 'Medium', 'High')),
    deadline TIMESTAMP,
    status VARCHAR(20) DEFAULT 'To Do' CHECK (status IN ('To Do', 'In Progress', 'Done')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id)
);

-- Bảng công việc con (Subtasks)
CREATE TABLE IF NOT EXISTS subtasks (
    id SERIAL PRIMARY KEY,
    task_id INT NOT NULL,
    assignee_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    deadline TIMESTAMP,
    status VARCHAR(20) DEFAULT 'To Do' CHECK (status IN ('To Do', 'In Progress', 'Pending', 'Done')),
    completion_rating REAL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (assignee_id) REFERENCES users(id)
);

-- File đính kèm subtask
CREATE TABLE IF NOT EXISTS subtask_attachments (
    id SERIAL PRIMARY KEY,
    subtask_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subtask_id) REFERENCES subtasks(id) ON DELETE CASCADE
);

-- Báo cáo
CREATE TABLE IF NOT EXISTS task_reports (
    id SERIAL PRIMARY KEY,
    subtask_id INT NULL,
    task_id INT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subtask_id) REFERENCES subtasks(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- Bài đăng
CREATE TABLE IF NOT EXISTS posts (
    id SERIAL PRIMARY KEY,
    author_id INT NOT NULL,
    department_id INT NULL,
    task_report_id INT NULL,
    visibility VARCHAR(20) DEFAULT 'Public' CHECK (visibility IN ('Public', 'Department', 'Private')),
    content_html TEXT NOT NULL,
    is_ai_generated SMALLINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (task_report_id) REFERENCES task_reports(id) ON DELETE SET NULL
);

-- Lịch sử chỉnh sửa bài viết
CREATE TABLE IF NOT EXISTS post_edit_history (
    id SERIAL PRIMARY KEY,
    post_id INT NOT NULL,
    old_content TEXT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Media bài viết
CREATE TABLE IF NOT EXISTS post_media (
    id SERIAL PRIMARY KEY,
    post_id INT NOT NULL,
    media_type VARCHAR(10) DEFAULT 'Image' CHECK (media_type IN ('Image', 'Video')),
    media_url VARCHAR(500) NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Bình luận
CREATE TABLE IF NOT EXISTS comments (
    id SERIAL PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_comment_id INT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_comment_id) REFERENCES comments(id) ON DELETE CASCADE
);

-- Reactions
CREATE TABLE IF NOT EXISTS post_reactions (
    id SERIAL PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    type VARCHAR(10) DEFAULT 'Like' CHECK (type IN ('Like', 'Heart', 'Haha', 'Wow', 'Sad', 'Angry')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Hội thoại
CREATE TABLE IF NOT EXISTS conversations (
    id SERIAL PRIMARY KEY,
    type VARCHAR(10) DEFAULT 'Direct' CHECK (type IN ('Direct', 'Group')),
    name VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Thành viên hội thoại
CREATE TABLE IF NOT EXISTS conversation_members (
    conversation_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (conversation_id, user_id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tin nhắn
CREATE TABLE IF NOT EXISTS messages (
    id SERIAL PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Thông báo
CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    trigger_user_id INT NULL,
    content TEXT NOT NULL,
    target_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trigger_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Bảng liên kết user-notification (đọc/chưa đọc)
CREATE TABLE IF NOT EXISTS notification_user (
    notification_id INT NOT NULL,
    user_id INT NOT NULL,
    is_read SMALLINT DEFAULT 0,
    read_at TIMESTAMP NULL,
    PRIMARY KEY (notification_id, user_id),
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- DATA MẪU
-- Password mặc định cho tất cả: password123
-- =============================================

INSERT INTO departments (id, dept_name, description) VALUES
(1, 'Board of Directors', 'Ban Giám Đốc điều hành công ty'),
(2, 'Marketing', 'Phòng Truyền thông và Marketing'),
(3, 'IT & Development', 'Phòng Phát triển sản phẩm')
ON CONFLICT (id) DO NOTHING;

SELECT setval('departments_id_seq', (SELECT MAX(id) FROM departments));

INSERT INTO users (id, department_id, role_id, username, password_hash, full_name, email) VALUES
(1, 1, 1, 'ceo_user', '$2y$10$T6zI6YrUi8KSW8/cKvHfpuLjlnTEvbj4buO8MjJH/CzpMi9p65Qu6', 'Nguyễn Ban Giám Đốc', 'ceo@relioo.com'),
(2, 3, 2, 'leader_it', '$2y$10$T6zI6YrUi8KSW8/cKvHfpuLjlnTEvbj4buO8MjJH/CzpMi9p65Qu6', 'Trần IT Trưởng', 'leader_it@relioo.com'),
(3, 3, 3, 'staff_it1', '$2y$10$T6zI6YrUi8KSW8/cKvHfpuLjlnTEvbj4buO8MjJH/CzpMi9p65Qu6', 'Lê Kỹ Thuật Viên 1', 'staff_it1@relioo.com'),
(4, NULL, 4, 'admin', '$2y$10$T6zI6YrUi8KSW8/cKvHfpuLjlnTEvbj4buO8MjJH/CzpMi9p65Qu6', 'Hệ Thống Admin', 'admin@relioo.com')
ON CONFLICT (id) DO NOTHING;

SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));
