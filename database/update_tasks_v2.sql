-- Cập nhật cấu trúc bảng cho luồng Duyệt công việc mới
USE relioo_db;

-- Thêm cột đánh dấu subtask bị từ chối để đổi màu thẻ
ALTER TABLE subtasks ADD COLUMN IF NOT EXISTS is_rejected TINYINT(1) DEFAULT 0;

-- Đảm bảo bảng minh chứng có cột ghi chú/link
ALTER TABLE subtask_attachments ADD COLUMN IF NOT EXISTS notes TEXT;

-- Thêm cột nội dung báo cáo hoàn thành vào subtask (nếu chưa có trong task_reports)
ALTER TABLE subtasks ADD COLUMN IF NOT EXISTS report_content TEXT;
