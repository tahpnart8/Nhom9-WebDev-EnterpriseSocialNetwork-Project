-- Migration script cho tính năng Quản lý Dự án (Đại trùng tu)

-- 1. Create `projects` table
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `created_by` INT NOT NULL,
    `status` ENUM('Active', 'Completed') DEFAULT 'Active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create `project_departments` table
CREATE TABLE IF NOT EXISTS `project_departments` (
    `project_id` INT NOT NULL,
    `department_id` INT NOT NULL,
    PRIMARY KEY (`project_id`, `department_id`),
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Update `tasks` table
-- Check if columns exist before adding (MySQL trick: usually done via app migrate, but here we just ALTER)
-- Lưu ý: Nếu cột đã tồn tại sẽ báo lỗi, nhưng script này dùng chạy 1 lần.
ALTER TABLE `tasks` 
ADD COLUMN `project_id` INT NULL AFTER `department_id`,
ADD COLUMN `approval_status` ENUM('Pending', 'Submitted', 'Approved', 'Rejected') DEFAULT 'Pending' AFTER `status`,
ADD COLUMN `ai_report_post_id` INT NULL AFTER `approval_status`;

ALTER TABLE `tasks` ADD CONSTRAINT `fk_task_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL;
ALTER TABLE `tasks` ADD CONSTRAINT `fk_task_post` FOREIGN KEY (`ai_report_post_id`) REFERENCES `posts`(`id`) ON DELETE SET NULL;

-- 4. Create Indexes for performance
CREATE INDEX `idx_project_created_by` ON `projects`(`created_by`);
CREATE INDEX `idx_task_project_id` ON `tasks`(`project_id`);
CREATE INDEX `idx_task_approval_status` ON `tasks`(`approval_status`);
