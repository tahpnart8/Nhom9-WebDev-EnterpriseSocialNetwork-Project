# Bối cảnh Dự án (Project Context) - Relioo Enterprise Social Network

Tài liệu này cung cấp bức tranh toàn cảnh về kiến trúc, công nghệ và cấu trúc mã nguồn của dự án **Relioo**. Nó đóng vai trò là "bộ nhớ dài hạn" để AI Agent và Lập trình viên có thể hiểu dự án một cách nhanh chóng và chính xác trước khi thực hiện bất kỳ công việc bảo trì hoặc nâng cấp nào trong chu kỳ "ĐẠI TRÙNG TU".

## 1. Thông tin Tổng quan
- **Tên dự án:** Relioo - Mạng xã hội doanh nghiệp (Enterprise Social Network tích hợp Quản lý công việc).
- **Mô hình lập trình:** Custom MVC (Model-View-Controller) kết hợp Front Controller Design Pattern. Không sử dụng Framework lớn (như Laravel/Symfony) mà tự xây dựng kiến trúc MVC thuần túy.
- **Front Controller:** Tất cả các luồng truy cập đều đi qua file `index.php` ở thư mục gốc. Việc định tuyến (Routing) được xử lý dựa trên parameter `?action=tên_hành_động` qua URL.

## 2. Ngăn xếp Công nghệ (Tech Stack)
- **Backend:** PHP thuần (Vanilla PHP).
- **Cơ sở dữ liệu:** MySQL (Sử dụng PDO để kết nối cơ sở dữ liệu - tham khảo `config/database.php`). Sử dụng cả Câu lệnh SQL trực tiếp và Stored Procedures (`sp_SearchUsers`).
- **Frontend / Giao diện:**
  - HTML5, CSS3, JavaScript (Vanilla JS và jQuery 3.7.0).
  - CSS Framework: Bootstrap 5.
  - UI Components: Bootstrap Icons, SweetAlert2 (cho Popup), Toastr (cho Thông báo nhỏ).
  - Kiến trúc Client-Server tương tác: AJAX/Fetch API được sử dụng dày đặc để tải dữ liệu bất đồng bộ mà không cần tải lại trang (Single Page Application - SPA behavior ở một số phần như Chat, Notifications).

## 3. Cấu trúc Thư mục Mã nguồn (Directory Structure)
Dự án được tổ chức theo chuẩn MVC rõ ràng:

- `/config/`: Chứa file `database.php` (lớp Database kết nối PDO, đọc cấu hình từ file `.env`).
- `/controllers/`: Chứa các bộ điều khiển logic. Đây là nơi tiếp nhận request từ `index.php`, gọi đối tượng Model và trả dữ liệu ra View hoặc trả về JSON (API).
- `/models/`: Chứa các lớp đại diện cho các bảng trong cơ sở dữ liệu. Xử lý việc truy vấn, thêm, xóa, sửa (CRUD).
- `/views/`: Giao diện người dùng (HTML trộn lẫn mã PHP).
  - `/views/layouts/`: Chứa giao diện dùng chung (`header.php`, `footer.php`, `sidebar.php`, `right_sidebar.php`).
  - Phân tách tĩnh các views theo chức năng: `admin`, `auth`, `chat`, `dashboard`, `social`, `tasks`, ...
- `/database/`: Chứa file dump cơ sở dữ liệu (ví dụ: `relioo_db (3).sql`).
- `/public/`: Nơi chứa tài nguyên tĩnh (CSS, JS, Hình ảnh).
- `.env`: File cấu hình môi trường chứa thông tin bảo mật (DB config, API Keys).

## 4. Các Module Cốt lõi (Core Modules)
Dựa theo `index.php` và các Controller, hệ thống chia thành 6 module chính:

1. **Authentication (`AuthController.php`):**
   - Đăng nhập, đăng xuất. Hệ thống sử dụng cơ chế `session_start()` gốc của PHP (tham chiếu file `index.php` dòng số 6).
2. **Quản trị Hệ thống (`AdminController.php`):**
   - Quản lý Nhân viên (Users) và Phòng ban (Departments).
3. **Mạng xã hội (`SocialController.php`):**
   - Chức năng cốt lõi: Tạo bài viết (Posts), quản lý Bình luận (Comments) kiểu lồng nhau (Tree), thả cảm xúc (Reactions) qua AJAX. Liên quan chặt chẽ đến `models/Post.php` và `models/Comment.php`.
4. **Quản lý công việc (`TaskController.php`):**
   - Quản lý Project, Tasks, và Subtasks.
   - Hỗ trợ Kanban board (Kéo thả hoặc cập nhật trạng thái).
   - Có luồng (workflow) nâng cao: Nộp minh chứng (Submit Evidence), Gia hạn (Extend Subtask), Duyệt/Từ chối (Approve/Reject).
5. **Trò chuyện & Nhắn tin (`ChatController.php`):**
   - Hỗ trợ chat 1-1 và Group Chat.
   - Real-time mô phỏng: Không sử dụng WebSockets mà sử dụng hệ thống "Smart Heartbeat Polling" (Ajax call liên tục mỗi 5s) được tìm thấy trong `views/layouts/header.php`.
6. **Thông báo (`NotificationController.php`):**
   - Hệ thống thông báo tự động cho Social (like, comment) và Tasks (assign, update status). 

## 5. Quy chuẩn Kỹ thuật đặc thù trong dự án
- **API Endpoints:** Đa số các tính năng thao tác dữ liệu qua Ajax được định tuyến với action bắt đầu bằng `api_` (VD: `api_create_post`, `api_submit_evidence`). Các API này trả về JSON response.
- **Xử lý Thời gian thực (Real-time Fake):** File `header.php` chứa đoạn Script Polling nặng (`heartbeatPoll`, `pollNotifications`) chạy ngầm dưới background để kiểm tra tin nhắn và thông báo mới. 

---
*Tài liệu này nên được đọc và tham chiếu trong bước "Phân tích Bối cảnh" của quy trình Workflow để đảm bảo các thay đổi không phá vỡ cấu trúc MVC và các hệ thống Polling có sẵn.*
