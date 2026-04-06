# KẾ HOẠCH PHÁT TRIỂN DỰ ÁN RELIOO (SPRINT PLAN)

Nhằm đảm bảo 5 thành viên trong nhóm có thể phân chia công việc độc lập trên GitHub và giảm thiểu conflict Database, quá trình phát triển được chia làm 7 Phiên (Sprints). Mỗi phiên mang lại các tính năng hoạt động được ngay (Minimal Viable Product).

---

## PHIÊN 1: KHỞI TẠO NỀN TẢNG (FOUNDATION & DATABASE SETUP)
**Thời gian lý tưởng:** 3 - 5 ngày.
**Mục tiêu:** 5 người cùng truy cập được vào 1 DB chung và đăng nhập được vào hệ thống.

* **Database (DBA Task):** 
  * Cài đặt XAMPP, mở port `3306` Firewall và cấu hình Tailscale IP ảo trên "Máy Host".
  * Dịch sơ đồ ERD thành file script `init_database.sql` và chạy trên MySQL của máy Host.
* **Backend Architecture (Lead Backend Task):**
  * Thiết lập cấu trúc thư mục MVC chuẩn cho PHP.
  * Xây dựng Class `Database.php` kết nối bằng Singleton Pattern (ngăn kết nối DB nhiều lần).
  * Xây dựng Controller `AuthController.php` và Model `User.php`.
* **Frontend UI (Front-end Task):**
  * Cài đặt Bootstrap 5 offline từ Source hoặc lấy qua CDN.
  * Code Layout trang Đăng nhập / Quên Mật Khẩu (Login Page).
* **Kết quả cần đạt:** Nhập tài khoản, password -> Bấm Login -> Gọi AJAX -> Server trả về JSON -> Lưu Session/Cookie -> Chuyển hướng (Redirect) vào trang chủ (Dashboard). Cả 5 người kiểm tra truy cập IP Host qua trình duyệt thành công.

---

## PHIÊN 2: BASE LAYOUT & MODULE RANKING/ADMIN (BACK OFFICE)
**Thời gian lý tưởng:** 4 - 5 ngày.
**Mục tiêu:** Có giao diện chính (Master Layout) có thanh điều hướng và Admin CRUD được hệ thống nhân sự cơ bản.

* **Frontend Layout:**
  * Dựng Fixed Navbar (Header chứa thông báo, avatar) và Sidebar Navigation (Social, Tasks, Dashboard).
  * Render giao diện theo Role dựa trên biến Session PHP (VD: Nếu Session Role = Staff thì ẩn menu Thêm nhân sự).
* **Admin Module (Back-office):**
  * Xây dựng trang Admin quản lý (chỉ CEO/Admin vào được).
  * Hiển thị Data Table danh sách Phòng Ban, Nhân viên.
  * Logic AJAX Thêm/Sửa/Xóa Nhân viên.
* **Kết quả cần đạt:** Có một layout giao diện rỗng, click chuyển trang MVC mượt mà. Admin tạo được sẵn 5-10 nhân sự mẫu với đủ các phòng ban.

---

## PHIÊN 3: MẠNG XÃ HỘI (SOCIAL CORE) & GOOGLE DRIVE INTEGRATION
**Thời gian lý tưởng:** 7 - 10 ngày.
**Mục tiêu:** Mọi người có thể đăng bài lên luồng tin (Newsfeed) và lấy link ảnh trên Drive hiện ra.

* **Integration Task (Google Drive):**
  * Lên Google Cloud Console tạo Service Account, lấy file credentials `.json`.
  * Viết class `DriveUpload.php` nhận data `$_FILES`, upload lên thư mục Drive dùng chung và trả về Web Content Link.
* **Backend API (Post & Comment):**
  * Viết API lấy danh sách bài viết (GET) kết hợp điều kiện: `WHERE visibility = 'public' OR department_id = $_SESSION['dept_id']`.
  * Viết API Lưu bài viết + API Lấy bình luận.
* **Frontend UI (Newsfeed):**
  * Dựng giao diện form tạo bài viết. 
  * Render các Card Bài Viết dựa trên JSON lấy từ AJAX.
  * Nút thả tim (React) bấm đổi màu xanh đỏ + tăng/giảm biến đếm không cần reload trang.
* **Kết quả cần đạt:** 1 Facebook thu nhỏ hoạt động hoàn chỉnh, có chức năng upload hình ảnh hiển thị trên mạng lưới LAN mà không bị mất hình. 

---

## PHIÊN 4: QUẢN LÝ CÔNG VIỆC KANBAN BOARD
**Thời gian lý tưởng:** 7 - 10 ngày.
**Mục tiêu:** Kéo thả thẻ công việc và tương tác duyệt tiến độ.

* **Frontend UI:**
  * Chèn thư viện `Sortable.js`, tạo 4 Cột: To Do, In Progress, Pending, Done.
  * Modal Panel khi Click vào một thẻ Task (Hiển thị form gửi minh chứng, hiện bình luận của task).
* **Backend API:**
  * Leader: CRUD Task lớn và chia nhỏ gán id cho nhân viên thành Subtask.
  * Staff: Fetch Subtasks mà mình được assign. Cập nhật state (kéo thẻ gọi AJAX Update status).
  * Luồng Duyệt Việc: Nhân viên Approve => Trạng thái sang Pending, gửi tín hiệu lên DB. Leader Approve => Tới Done. Leader Reject => Cập nhật lại ngày (Gia hạn) và đẩy về In Progress.
* **Kết quả cần đạt:** Một bảng Trello nội bộ mượt mà, phân quyền dữ liệu chặt chẽ giữa Trí và Nhân viên theo đúng sơ đồ hệ thống.

---

## PHIÊN 5: REAL-TIME NOTIFICATIONS VÀ CHAT MESSENGER CƠ BẢN
**Thời gian lý tưởng:** 5 - 7 ngày.
**Mục tiêu:** Các hành động (Thích, Bình luận, Giao task) đều có thông báo nảy chuông. 

* **Database & Logic:**
  * Thiết lập cơ chế Broadcast: Thay vì 100 dòng cho 100 người, tạo 1 dòng Notification tổng, ghi chép ai đã đọc vào bảng phụ `Notification_User`.
* **Frontend Polling:**
  * Sử dụng hàm `setInterval()` trong JavaScript để liên tục ping AJAX xuống server mỗi 10 giây hỏi xem có thông báo mới không.
  * Dùng `Toastr` chớp thông báo ở góc màn hình.
* **Chat (Optional nhưng nên có):**
  * Dựng cửa sổ tin nhắn 1-1, cũng xử lý luồng fetch dữ liệu bằng AJAX polling (vì làm Websocket sẽ quá sức với XAMPP Localhost tiêu chuẩn).

---

## PHIÊN 6: LÕI KỸ THUẬT AUTO-POSTING (LLaMA AI PIPELINE)
**Thời gian lý tưởng:** 5 - 7 ngày.
**Mục tiêu:** Mảnh ghép lớn nhất của đồ án - Biến những con số rời rạc thành bài văn cảm hứng.

* **Luồng Staff (Chuyển Done):**
  * Frontend: Khi staff chuyển Subtask sang Cột Done, bật form HTML điền nội dung làm việc.
  * Backend: Nhận Text của Staff. Chèn vào: `"Bạn là nhân vật ảo... Tôi đã làm [TEXT], viết cho tôi cái báo cáo."` -> Gọi cURL qua `Groq LLaMA 3 8B API`. -> Trả về JSON chứa bài viết. 
* **Luồng Leader (Gom Task báo cáo Dài):**
  * Backend: Câu Query SELECT toàn bộ bài đăng AI thuộc Task ID hiện tại. Nối chuỗi bằng PHP (ví dụ `$context .= $post['content']`), gửi lên LLaMA Prompt.
* **UI/UX Tối ưu hóa:**
  * Tạo Loading Bar / Skeleton từ 0-100% trong lúc chờ AJAX gọi LLaMA (tránh việc click liên tục sinh chục request rác).
  * Form Edit mở sau khi AI generate xong cho phép User chỉnh sửa nội dung bài trước khi ấn **Chốt đăng bài**. 
  * Cập nhật tính năng Lưu lịch sử Edit.

---

## PHIÊN 7: MASTER DASHBOARD CEO, POLISH & DEPLOY
**Thời gian lý tưởng:** 3 - 5 ngày.
**Mục tiêu:** Bức tranh toàn cảnh, giao diện Dashboard cho quyền Lãnh đạo cấp cao. Vẽ biểu đồ.

* **Backend Thống kê:**
  * Viết các câu SQL phức tạp: Lấy Tổng Active Projects, Subtasks Overdue (Deadline < NOW() AND status != Done). Tính tỉ lệ Completion = (Done/Total) * 100.
* **Frontend Data Visualizer:**
  * Sử dụng thư viện **Chart.js** truyền mảng số liệu JSON đã query được ở trên vào vẽ các Pie Chart và Bar Chart.
* **Testing & Finalize:**
  * Xử lý lỗi bảo mật: Lọc `htmlspecialchars()` tránh XSS, sử dụng Prepared Statements `$stmt = $conn->prepare()` của PDO để chống SQL Injection.
  * Xuất toàn bộ CSDL ra file `.sql` cuối cùng.
  * Viết báo cáo PowerPoint Word kèm Sơ đồ ERD, Activity Diagram để chuẩn bị báo cáo môn học.
