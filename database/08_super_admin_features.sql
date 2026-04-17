-- 1. Thêm hạn ngạch (Quotas) vào bảng companies
ALTER TABLE `companies` 
ADD COLUMN `max_users` INT DEFAULT 100,
ADD COLUMN `max_projects` INT DEFAULT 50,
ADD COLUMN `max_departments` INT DEFAULT 10;

-- 2. Tạo bảng Audit Logs để theo dõi hoạt động toàn hệ thống
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NULL, -- NULL nếu là hành động của Super Admin
    `user_id` INT NOT NULL,
    `action_type` VARCHAR(50) NOT NULL, -- CREATE_USER, DELETE_COMPANY, etc.
    `entity_type` VARCHAR(50) NOT NULL, -- User, Company, Project
    `entity_id` INT NULL,
    `details` TEXT,
    `ip_address` VARCHAR(45),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Chỉ mục cho audit logs
CREATE INDEX `idx_audit_company` ON `audit_logs` (`company_id`);
CREATE INDEX `idx_audit_user` ON `audit_logs` (`user_id`);
CREATE INDEX `idx_audit_action` ON `audit_logs` (`action_type`);
