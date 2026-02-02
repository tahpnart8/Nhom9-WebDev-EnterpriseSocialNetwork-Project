# Enterprise Social Network (ESN) - Master Document
## Phần 1: Tổng quan & User Stories

### 1. Giới thiệu
Hệ thống **ESN** là nền tảng quản trị doanh nghiệp kết hợp mạng xã hội nội bộ. Hệ thống tập trung vào việc số hóa quy trình quản lý nhân sự, công việc và truyền thông nội bộ theo mô hình phân cấp chặt chẽ (Founder -> Manager -> Employee).

### 2. Các nhóm người dùng (Actors)
Hệ thống được thiết kế cho 3 nhóm đối tượng chính:
1.  **Admin (Quản trị viên / Founder)**: Người có quyền lực cao nhất, khởi tạo không gian làm việc (Workspace) và nắm toàn quyền kiểm soát.
2.  **Manager (Trưởng phòng / Quản lý)**: Người đứng đầu các phòng ban, chịu trách nhiệm quản lý nhân sự và công việc trong phạm vi phòng ban của mình.
3.  **Employee (Nhân viên)**: Người dùng cuối, thực hiện công việc được giao và tham gia tương tác xã hội.

### 3. User Stories (Câu chuyện người dùng)

#### 3.1. Dành cho Admin
- **US01**: Là Admin, tôi muốn khởi tạo cấu trúc tổ chức (Tạo phòng ban, chỉ định Trưởng phòng) để thiết lập bộ máy hoạt động.
- **US02**: Là Admin, tôi muốn tạo tài khoản cho nhân viên và các Trưởng phòng vì hệ thống không cho phép đăng ký tự do.
- **US03**: Là Admin, tôi muốn giao việc cho bất kỳ nhân sự nào trong công ty để điều phối hoạt động chung.
- **US04**: Là Admin, tôi muốn xem toàn bộ lịch sử hoạt động và dữ liệu của tất cả phòng ban.

#### 3.2. Dành cho Manager
- **US05**: Là Manager, tôi muốn xem danh sách nhân viên **chỉ thuộc phòng ban tôi** để dễ dàng quản lý.
- **US06**: Là Manager, tôi muốn thêm nhân viên mới vào phòng ban của mình (Role mặc định là Employee) mà không cần nhờ Admin.
- **US07**: Là Manager, tôi muốn tạo và giao công việc (Task) cho nhân viên cấp dưới trong phòng ban. Tôi không được phép giao việc cho nhân viên phòng khác.
- **US08**: Là Manager, tôi muốn duyệt (Approve) hoặc từ chối (Reject) báo cáo công việc của nhân viên.

#### 3.3. Dành cho Employee
- **US09**: Là Employee, tôi muốn xem danh sách đồng nghiệp trong cùng phòng ban để biết ai đang làm việc cùng mình (Read-only).
- **US10**: Là Employee, tôi muốn nhận thông báo khi được giao việc và cập nhật tiến độ công việc (Kéo thả Kanban, đổi trạng thái).
- **US11**: Là Employee, tôi muốn nộp báo cáo (Link minh chứng) khi hoàn thành công việc để cấp trên duyệt.
- **US12**: Là Employee, tôi muốn đăng bài viết, bình luận, like trên Newsfeed công ty để tương tác với đồng nghiệp.
- **US13**: Là Employee, tôi **không được phép** tự ý tạo Task mới (chỉ được làm task được giao).

### 4. Phạm vi Nghiệp vụ Chính
1.  **Quản trị Tổ chức (HR Core)**: Phân cấp Admin - Manager - Employee.
2.  **Quản lý Công việc (Task Management)**: Quy trình Assign -> In Progress -> Submit -> Review (Approve/Reject).
3.  **Lịch & Sự kiện (Calendar)**: Sự kiện toàn ty (Company) và sự kiện nội bộ (Department).
4.  **Mạng xã hội (Social Newsfeed)**: Bảng tin nội bộ, tương tác, profile cá nhân.
