# Enterprise Social Network (ESN) v2.0 - Master Document
## Phần 2: Yêu cầu Chức năng & Nghiệp vụ Chi tiết

### 1. Phân hệ Xác thực & Cài đặt (Auth & Setup)
*   **Disabled Public Registration**: Không có trang đăng ký công khai.
*   **Enterprise Setup Flow**:
    *   Hệ thống khi mới cài đặt sẽ chạy trang `install.php`.
    *   Cho phép khởi tạo **Workspace** (Tên công ty) và tài khoản **Founder (Admin)** đầu tiên.
    *   Sau khi setup xong, việc tạo user mới hoàn toàn do nội bộ quyết định.
*   **Login**: Đăng nhập bằng Username/Email + Password. Phân quyền ngay sau khi đăng nhập (Lưu Session `user_role`, `department_id`).

### 2. Phân hệ Quản trị Nhân sự (HR Management)
Quy tắc phân quyền nghiêm ngặt theo ma trận sau:

| Thao tác | Admin | Manager | Employee |
| :--- | :--- | :--- | :--- |
| **Xem danh sách User** | Toàn bộ công ty | Chỉ user trong Dept của mình | Chỉ user trong Dept của mình (Read-only) |
| **Tạo User mới** | Có (Gán bất kỳ Dept/Role nào) | Có (Tự động gán vào Dept của mình, Role=Employee) | Không |
| **Sửa User** | Có | Có (Chỉ user trong Dept, không được đổi Dept khác) | Không |
| **Xóa User** | Có | Có (Chỉ user trong Dept) | Không |
| **Quản lý Phòng ban** | Có (Tạo/Sửa/Xóa Dept) | Không | Không |

### 3. Phân hệ Quản lý Công việc (Task Management)
Mô hình Kanban Board với logic nghiệp vụ Doanh nghiệp:

1.  **Quy trình Trạng thái (Status Workflow)**:
    *   `DRAFT` (Nháp) -> `ASSIGNED` (Đã giao) -> `IN_PROGRESS` (Đang làm) -> `PENDING_APPROVAL` (Chờ duyệt) -> `COMPLETED` (Hoàn thành) hoặc `REJECTED` (Từ chối).
    *   Nếu `REJECTED`, task quay về trạng thái `IN_PROGRESS`.

2.  **Quyền hạn Tạo & Giao việc**:
    *   **Admin**: Tạo task, assign cho bất kỳ ai.
    *   **Manager**: Tạo task, **chỉ assign cho nhân viên thuộc phòng mình**. (Hệ thống filter danh sách user).
    *   **Employee**: **Không được tạo task**. Chỉ nhìn thấy task được giao cho mình (My Tasks).

3.  **Quy trình Báo cáo & Duyệt**:
    *   Nhân viên khi làm xong -> Kéo sang cột Review (hoặc bấm Submit) -> Nhập Link minh chứng (Proof).
    *   Manager nhận thông báo -> Xem Task -> Bấm **Approve** (Task done) hoặc **Reject** (Ghi lý do Admin Note).

### 4. Phân hệ Mạng xã hội & Truyền thông (Social & Internal Comms)
1.  **Newsfeed (Bảng tin)**:
    *   Là không gian mở duy nhất trong hệ thống nơi **tất cả nhân viên** có thể nhìn thấy nhau.
    *   Bài viết hiển thị công khai toàn công ty.
    *   **Logic Tương tác**: Nhân viên A (Phòng Marketing) có thể thấy bài của Nhân viên B (Phòng IT) trên Feed -> Click vào Avatar để xem Profile -> Gửi lời mời kết bạn.
2.  **Kết bạn (Connections)**:
    *   Mặc dù "Danh sách nhân viên" (Menu HR) bị giới hạn theo phòng ban, nhưng tính năng "Kết bạn" cho phép mở rộng mạng lưới cá nhân.
    *   Hai người là bạn bè (Accepted) có thể nhắn tin riêng (Private Message).
3.  **Events (Lịch sự kiện)**:
    *   Loại `COMPANY`: Dành cho toàn thể nhân viên (Admin tạo).
    *   Loại `DEPARTMENT`: Dành riêng cho phòng ban (Manager/Admin tạo).

### 5. Yêu cầu Phi chức năng (Non-functional)
*   **Giao diện (UI/UX)**:
    *   Thiết kế Hiện đại, clean, không lỗi overlap header.
    *   Responsive cơ bản.
    *   Hiệu ứng Loading, thông báo Toast khi thao tác thành công.
*   **Bảo mật**:
    *   Chặn truy cập trái phép qua URL (Middleware/Check Permission trong Controller).
    *   Password phải được Hash (Bcrypt).
