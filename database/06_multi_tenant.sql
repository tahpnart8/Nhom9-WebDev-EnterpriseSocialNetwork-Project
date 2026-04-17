-- Migration script cho tính năng SaaS / Multi-tenant (Giai đoạn 12)

-- 1. Create `companies` table
CREATE TABLE IF NOT EXISTS `companies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_name` VARCHAR(255) NOT NULL,
    `industry` VARCHAR(255) DEFAULT NULL,
    `ceo_name` VARCHAR(255) NOT NULL,
    `ceo_email` VARCHAR(255) NOT NULL,
    `ceo_phone` VARCHAR(20) DEFAULT NULL,
    `ceo_password_hash` VARCHAR(255) NOT NULL, -- To store the desired CEO password
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add company_id to core tables
-- users
ALTER TABLE `users` ADD COLUMN `company_id` INT NULL AFTER `id`;
ALTER TABLE `users` ADD CONSTRAINT `fk_user_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- departments
ALTER TABLE `departments` ADD COLUMN `company_id` INT NULL AFTER `id`;
ALTER TABLE `departments` ADD CONSTRAINT `fk_dept_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- projects
ALTER TABLE `projects` ADD COLUMN `company_id` INT NULL AFTER `id`;
ALTER TABLE `projects` ADD CONSTRAINT `fk_proj_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- tasks
ALTER TABLE `tasks` ADD COLUMN `company_id` INT NULL AFTER `id`;
ALTER TABLE `tasks` ADD CONSTRAINT `fk_task_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- posts
ALTER TABLE `posts` ADD COLUMN `company_id` INT NULL AFTER `id`;
ALTER TABLE `posts` ADD CONSTRAINT `fk_post_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- comments
ALTER TABLE `comments` ADD COLUMN `company_id` INT NULL AFTER `id`;
ALTER TABLE `comments` ADD CONSTRAINT `fk_comment_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- notifications
ALTER TABLE `notifications` ADD COLUMN `company_id` INT NULL AFTER `id`;
ALTER TABLE `notifications` ADD CONSTRAINT `fk_notif_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- Admin user (role_id=4)
INSERT INTO `users` (`username`, `password_hash`, `email`, `role_id`, `company_id`, `full_name`, `is_active`) 
VALUES ('admin', '$2y$10$lTKlll9EgG8lie6q50DtjO4K.IZOR3IHW3ZyhxZv.VreNg2f1lIB.', 'admin@relioo.com', 4, NULL, 'Super Admin', 1);
