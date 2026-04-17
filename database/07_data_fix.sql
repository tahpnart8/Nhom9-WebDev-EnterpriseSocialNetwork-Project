-- Script fix dữ liệu rò rỉ sau khi nâng cấp Multi-tenant
-- Gán company_id cho các bản ghi cũ dựa trên mối quan hệ với User

-- 1. Cập nhật Departments (lấy từ users trong dept đó)
UPDATE departments d 
SET d.company_id = (SELECT u.company_id FROM users u WHERE u.department_id = d.id LIMIT 1)
WHERE d.company_id IS NULL;

-- 2. Cập nhật Projects (lấy từ creator)
UPDATE projects p 
SET p.company_id = (SELECT u.company_id FROM users u WHERE u.id = p.created_by LIMIT 1)
WHERE p.company_id IS NULL;

-- 3. Cập nhật Tasks (lấy từ projects)
UPDATE tasks t
SET t.company_id = (SELECT p.company_id FROM projects p WHERE p.id = t.project_id LIMIT 1)
WHERE t.company_id IS NULL;

-- 4. Cập nhật Subtasks (không có company_id nhưng đảm bảo t.company_id đồng bộ)
-- Subtasks table doesn't have company_id, it joins tasks.

-- 5. Cập nhật Posts (lấy từ author)
UPDATE posts p
SET p.company_id = (SELECT u.company_id FROM users u WHERE u.id = p.author_id LIMIT 1)
WHERE p.company_id IS NULL;

-- 6. Cập nhật Comments (lấy từ user_id)
UPDATE comments c
SET c.company_id = (SELECT u.company_id FROM users u WHERE u.id = c.user_id LIMIT 1)
WHERE c.company_id IS NULL;

-- 7. Cập nhật Notifications (lấy từ trigger_user_id)
UPDATE notifications n
SET n.company_id = (SELECT u.company_id FROM users u WHERE u.id = n.trigger_user_id LIMIT 1)
WHERE n.company_id IS NULL;
