# Chiến lược triển khai Stored Procedures & Tính năng mới

Dựa trên kế hoạch tối ưu hóa database, đây là các bước thực hiện cụ thể để đưa Procedure vào vận hành thực tế.

## 1. Mục tiêu
- Chuyển dịch 100% logic truy vấn phức tạp sang Database.
- Phát triển thêm các tính năng: Báo cáo hiệu suất (KPI) và Bảng xếp hạng (Leaderboard).
- Tự động hóa quy trình cập nhật trạng thái Task.

## 2. Các giai đoạn thực hiện

### Giai đoạn 1: Database Setup
- Chạy mã SQL tạo 15 Procedure đã thiết kế.
- Kiểm tra tính đúng đắn của các câu lệnh JOIN và Transaction.

### Giai đoạn 2: Refactoring Backend (PHP)
- **Post Model:** Thay thế `getFeed`, `toggleReaction`.
- **Task/Subtask Model:** Thay thế các hàm thống kê, nộp minh chứng, lấy việc gấp.
- **Message Model:** Thay thế logic chat và đánh dấu đã xem.
- **Notification Model:** Tối ưu logic lấy thông báo chưa đọc.

### Giai đoạn 3: Phát triển tính năng mới (New Features)
- **Tính năng KPI:** 
    - Controller: `ProfileController` gọi `sp_GetEmployeePerformance`.
    - View: Thêm tab "Hiệu suất" trong trang cá nhân.
- **Tính năng Leaderboard:**
    - Controller: `SocialController` hoặc `AdminController` gọi `sp_GetLeaderboard`.
    - View: Thêm widget "Nhân viên tiêu biểu" ở Sidebar hoặc một trang riêng.
- **Tính năng Automation:**
    - Tích hợp `sp_UpdateTaskStatusSync` vào quy trình duyệt Subtask.

### Giai đoạn 4: Kiểm thử & Tối ưu
- Kiểm tra tốc độ phản hồi (Response Time).
- Đảm bảo tính toàn vẹn dữ liệu khi thực hiện Transaction (nộp bài + đăng social).

## 3. Danh sách file sẽ thay đổi
- `models/Post.php`
- `models/Task.php`
- `models/Subtask.php`
- `models/User.php`
- `models/Message.php`
- `controllers/ProfileController.php`
- `controllers/TaskController.php`
- `controllers/SocialController.php`
- `views/profile/index.php` (Thêm UI KPI)
- `views/social/index.php` (Thêm UI Leaderboard)
