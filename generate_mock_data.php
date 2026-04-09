<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = ['subtask_attachments', 'subtasks', 'tasks', 'comment_reactions', 'comments', 'post_reactions', 'post_media', 'posts', 'notifications', 'notification_user', 'messages', 'users', 'departments', 'roles'];
    foreach ($tables as $table) { $db->exec("TRUNCATE TABLE $table"); }
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Add missing feedback column to subtasks if not exists
    try {
        $db->exec("ALTER TABLE subtasks ADD COLUMN IF NOT EXISTS feedback TEXT");
        $db->exec("ALTER TABLE subtasks ADD COLUMN IF NOT EXISTS is_rejected TINYINT(1) DEFAULT 0");
    } catch(PDOException $e) { }

    // Roles & Depts
    $db->exec("INSERT INTO roles (id, role_name) VALUES (1, 'CEO'), (2, 'Leader'), (3, 'Staff'), (4, 'Admin')");
    $db->exec("INSERT INTO departments (id, dept_name) VALUES (1, 'Ban Giám Đốc'), (2, 'Kinh Doanh'), (3, 'Kỹ Thuật')");

    // Users (Pass: 123456)
    $pass = password_hash('123456', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO users (id, role_id, department_id, username, password_hash, full_name, email) VALUES 
        (1, 1, 1, 'ceo', '$pass', 'Nguyễn Văn CEO', 'ceo@relioo.com'),
        (2, 2, 3, 'leader_it', '$pass', 'Trần IT Trưởng', 'it@relioo.com'),
        (3, 3, 3, 'staff_it1', '$pass', 'Vũ Nhân Viên 1', 'it1@relioo.com')");

    // Tasks & Subtasks (Fix column assignee_id)
    $db->exec("INSERT INTO tasks (id, created_by_user_id, department_id, title, status) VALUES (1, 1, 3, 'Dự án Hệ thống Relioo', 'In Progress')");
    
    $db->exec("INSERT INTO subtasks (task_id, assignee_id, title, description, status, deadline) VALUES 
        (1, 3, 'Thiết kế Giao diện Kanban', 'Làm giao diện giống GitHub', 'In Progress', '2026-04-30'),
        (1, 3, 'Xây dựng API Phê duyệt', 'Viết logic duyệt task', 'To Do', '2026-05-05')");

    echo "<h1>Thành công!</h1><p>Dữ liệu đã chuẩn hóa. CEO và Staff đều sẽ thấy task.</p><a href='index.php?action=tasks'>Vào Bảng Công Việc</a>";
} catch (Exception $e) { echo "Lỗi: " . $e->getMessage(); }
?>
