# BÀI KIỂM TRA HỆ THỐNG RELIOO - PHIÊN TEST #1

**Ngày kiểm tra:** 2026-04-07
**Phiên bản:** Local XAMPP (MySQL)
**URL gốc:** `http://localhost/relioo/index.php`
**Tài khoản test:** Tất cả mật khẩu mặc định là `password123`

| Tài khoản | Role | Phòng ban |
|---|---|---|
| `ceo_user` | CEO (Role 1) | Board of Directors |
| `leader_it` | Leader (Role 2) | IT & Development |
| `staff_it1` | Staff (Role 3) | IT & Development |
| `admin` | Admin (Role 4) | Không có |

---

## MODULE 1: XÁC THỰC & PHÂN QUYỀN (Authentication & Authorization)

### TEST-01: Đăng nhập thành công với tài khoản CEO
- **Hành vi:** Mở trang Login → Nhập `ceo_user` / `password123` → Bấm Đăng nhập.
- **Kỳ vọng:** Chuyển hướng tới Dashboard, hiển thị tên "Nguyễn Ban Giám Đốc", sidebar hiện menu "Master Dashboard", "Nhân sự", "Phòng ban".
- **Kết quả:** ✅ **PASS** — Đăng nhập thành công. Chuyển hướng đúng sang Master Dashboard. Sidebar hiện đầy đủ menu: Bảng tin nội bộ, Quản lý công việc, Tin nhắn, Master Dashboard, Nhân sự, Phòng ban. Tên hiển thị: "Nguyễn Ban Giám Đốc", role: "CEO / Giám đốc".

### TEST-02: Đăng nhập thành công với tài khoản Staff
- **Hành vi:** Mở trang Login → Nhập `staff_it1` / `password123` → Bấm Đăng nhập.
- **Kỳ vọng:** Chuyển hướng tới Dashboard (Tổng quan cá nhân), sidebar KHÔNG hiện menu "Master Dashboard", "Nhân sự", "Phòng ban".
- **Kết quả:** ✅ **PASS** — Chuyển hướng đúng trang "Tổng quan cá nhân". Sidebar chỉ hiện: Bảng tin nội bộ, Quản lý công việc, Tin nhắn, Tổng quan cá nhân. KHÔNG có menu Master Dashboard, Nhân sự, Phòng ban.

### TEST-03: Đăng nhập sai mật khẩu
- **Hành vi:** Mở trang Login → Nhập `ceo_user` / `wrongpassword` → Bấm Login.
- **Kỳ vọng:** Hiển thị thông báo lỗi, KHÔNG chuyển hướng.
- **Kết quả:** ✅ **PASS** — Hiện alert đỏ nội dung: "Tên đăng nhập hoặc mật khẩu không chính xác." Không bị chuyển hướng.

### TEST-04: Truy cập trái phép trang Admin khi là Staff
- **Hành vi:** Đăng nhập bằng `staff_it1`, sau đó truy cập thẳng URL `index.php?action=admin_users`.
- **Kỳ vọng:** Bị redirect về Dashboard, KHÔNG thấy trang quản lý nhân sự.
- **Kết quả:** ✅ **PASS** — Hệ thống tự động redirect về Dashboard. Staff không thể truy cập trang Admin.

### TEST-05: Chức năng Đăng xuất
- **Hành vi:** Đăng nhập, sau đó bấm nút "Thoát".
- **Kỳ vọng:** Quay về trang Login, truy cập lại Dashboard bị chặn.
- **Kết quả:** ✅ **PASS** — "Thoát" hoạt động đúng. Session bị hủy. Quay về trang đăng nhập.

---

## MODULE 2: MASTER DASHBOARD (Phiên 7 vừa xây dựng)

### TEST-06: Dashboard CEO hiển thị thẻ KPI và biểu đồ Chart.js
- **Hành vi:** Đăng nhập CEO → Xem trang Dashboard.
- **Kỳ vọng:** Hiện 3 thẻ KPI (Dự án đang chạy, Báo động trễ hạn, Tỉ lệ hoàn thành). Hiện 2 biểu đồ (Pie Chart tiến độ, Bar Chart khối lượng phòng ban).
- **Kết quả:** ✅ **PASS** — 3 thẻ KPI hiển thị: "1 / 1 tổng" dự án, "0 việc" trễ hạn, "50%" hoàn thành. Pie Chart (Doughnut) và Bar Chart render chính xác. Bar Chart hiện 3 phòng ban (Board of Directors, Marketing, IT & Development).

### TEST-07: Dashboard Leader hiển thị đúng phạm vi phòng ban
- **Hành vi:** Đăng nhập `leader_it` → Xem Dashboard.
- **Kỳ vọng:** Tương tự CEO nhưng dữ liệu chỉ giới hạn trong phòng IT & Development. Bar Chart hiển thị tên nhân viên trong phòng.
- **Kết quả:** ⚠️ **CHƯA TEST** — Cần xác nhận thêm trong phiên test riêng.

### TEST-08: Dashboard Staff chỉ hiển thị tổng quan cá nhân
- **Hành vi:** Đăng nhập `staff_it1` → Xem Dashboard.
- **Kỳ vọng:** KHÔNG hiện biểu đồ. Chỉ hiện thẻ "Công việc đang làm" kèm thông tin cá nhân và cảnh báo trễ hạn.
- **Kết quả:** ✅ **PASS** — Giao diện hiện 2 card: "Công việc đang làm: 0" (Tổng số việc được giao: 1), và "Hành động cần chú ý" (Tiến độ rất tốt, không có việc trễ hạn). Không có biểu đồ Chart.js.

---

## MODULE 3: MẠNG XÃ HỘI (Social Core - Newsfeed)

### TEST-09: Tải trang Bảng tin / Newsfeed không bị lỗi
- **Hành vi:** Đăng nhập CEO → Click vào menu "Bảng tin nội bộ".
- **Kỳ vọng:** Trang hiện ra với khung đăng bài và danh sách bài đăng (hoặc thông báo "Chưa có bản tin").
- **Kết quả:** ✅ **PASS** — Trang Newsfeed hiển thị đúng. Có khung textarea đăng bài, có bài đăng hiện với hình ảnh đính kèm. Cột phải hiện "Cộng đồng Nổi bật".

### TEST-10: Đăng bài viết mới (Text only, không có ảnh)
- **Hành vi:** Tại Newsfeed, viết nội dung vào khung Textarea → Chọn "Công khai" → Bấm "Đăng bài".
- **Kỳ vọng:** Trang reload, bài mới hiện lên đầu Timeline với nội dung đã viết.
- **Kết quả:** ⚠️ **CHƯA TEST** — Cần thao tác thủ công.

### TEST-11: Đăng bài viết rỗng (Validation)
- **Hành vi:** Để trống khung Textarea → Bấm "Đăng bài".
- **Kỳ vọng:** Không cho phép đăng, hiện lỗi validate hoặc required.
- **Kết quả:** ⚠️ **CHƯA TEST** — Cần thao tác thủ công.

### TEST-12: Phân vùng hiển thị bài đăng Department
- **Hành vi:** Đăng nhập Leader IT → Đăng bài ở chế độ "Trong phòng ban". Đăng xuất → Đăng nhập lại bằng CEO → Kiểm tra Newsfeed.
- **Kỳ vọng:** CEO phải thấy được bài Department đó.
- **Kết quả:** ⚠️ **CHƯA TEST** — Cần luồng test phức tạp liên quan đa tài khoản.

---

## MODULE 4: QUẢN LÝ CÔNG VIỆC (Task Management - Kanban Board)

### TEST-13: Tải trang Kanban Board cho CEO
- **Hành vi:** Đăng nhập CEO → Click "Quản lý công việc".
- **Kỳ vọng:** Hiện giao diện Kanban với 4 cột (To Do, In Progress, Pending, Done). Không lỗi PHP, không trang trắng.
- **Kết quả:** ✅ **PASS** — Kanban Board hiện 4 cột với card công việc đúng vị trí. Không có lỗi PHP.

### TEST-14: Leader tạo Task mới
- **Kết quả:** ⚠️ **CHƯA TEST** — Cần thao tác CRUD qua modal.

### TEST-15: Leader tạo Subtask và giao cho Staff
- **Kết quả:** ⚠️ **CHƯA TEST** — Cần thao tác CRUD qua modal.

### TEST-16: Staff cập nhật trạng thái Subtask (To Do → In Progress)
- **Kết quả:** ⚠️ **CHƯA TEST** — Cần thao tác drag & drop.

### TEST-17: Staff không được tự chuyển sang Done
- **Kết quả:** ⚠️ **CHƯA TEST** — Cần thao tác drag & drop.

---

## MODULE 5: ADMIN PANEL (Quản trị Hệ thống)

### TEST-18: Hiển thị danh sách Nhân sự
- **Hành vi:** Đăng nhập Admin → Click menu "Nhân sự".
- **Kỳ vọng:** Hiện Data Table chứa danh sách users mẫu.
- **Kết quả:** ✅ **PASS** — Bảng dữ liệu nhân sự hiển thị chính xác, các nút thêm nhân viên có phản hồi.

### TEST-19: Hiển thị danh sách Phòng Ban
- **Hành vi:** Đăng nhập Admin → Click menu "Phòng ban".
- **Kỳ vọng:** Hiện danh sách 3 phòng ban mẫu.
- **Kết quả:** ✅ **PASS** — Danh sách 3 phòng ban hiển thị đúng.

---

## MODULE 6: NHẮN TIN & THÔNG BÁO (Chat & Notifications)

### TEST-20: Trang Chat hiển thị không lỗi
- **Hành vi:** Đăng nhập CEO → Click menu "Tin nhắn".
- **Kỳ vọng:** Giao diện Chat hiển thị đúng, không trang trắng, không lỗi PHP.
- **Kết quả:** ✅ **PASS** — Trang Chat render đúng khung giao diện. Chưa có hội thoại (đúng dữ liệu mẫu).

---

## KIỂM TRA PHI CHỨC NĂNG

### TEST-21: Kiểm tra CSS Layout có bị bể không
- **Hành vi:** Duyệt qua TẤT CẢ các trang (Dashboard, Social, Tasks, Chat, Admin Users, Admin Departments).
- **Kỳ vọng:** Sidebar cố định bên trái, nội dung chính bên phải, Bootstrap grid hoạt động.
- **Kết quả:** ✅ **PASS (SAU KHI SỬA)** — Giao diện ban đầu bị bể hoàn toàn (sidebar hiện dạng bullet list) do lỗi đường dẫn CSS tuyệt đối `/public/css/style.css` không hoạt động khi dự án nằm trong subfolder `/relioo/` trên XAMPP. Đã sửa bằng PHP base path computation. Sau khi sửa, tất cả trang đều hiển thị layout chuyên nghiệp.

---

## TỔNG KẾT (SUMMARY)

| Trạng thái | Số lượng | Chi tiết |
|---|---|---|
| ✅ PASS | **13/21** | TEST 01-06, 08, 09, 13, 18-21 |
| ⚠️ CHƯA TEST | **7/21** | TEST 07, 10-12, 14-17 |
| ❌ FAIL | **0/21** | Không có |

### LỖI ĐÃ PHÁT HIỆN VÀ SỬA TRONG PHIÊN NÀY:

| # | Mô tả lỗi | File | Nguyên nhân gốc | Trạng thái |
|---|---|---|---|---|
| BUG-01 | **Sidebar bị bể thành bullet list**, CSS custom không load | `views/layouts/header.php` | Đường dẫn CSS dùng `/public/css/style.css` (tuyệt đối root), khi chạy trên XAMPP subfolder `/relioo/`, trình duyệt tìm file ở `localhost/public/css/...` thay vì `localhost/relioo/public/css/...` → 404 | ✅ ĐÃ SỬA — Dùng PHP `$basePath` tính toán động từ `$_SERVER['SCRIPT_NAME']` |

### CÁC BÀI TEST CẦN CHẠY TIẾP Ở PHIÊN SAU:
1. TEST-07: Kiểm tra Dashboard Leader phạm vi phòng ban.
2. TEST-10, 11, 12: Đăng bài viết (text, rỗng, phân vùng phòng ban).
3. TEST-14, 15: Leader CRUD Task/Subtask.
4. TEST-16, 17: Staff drag-drop và validate luồng duyệt.
