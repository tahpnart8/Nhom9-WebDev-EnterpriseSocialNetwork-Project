# Enterprise Social Network (ESN) - Master Document
## Phần 3: Đặc tả Kỹ thuật Hệ thống (Architecture Specifications)

Tài liệu này dùng để hướng dẫn đội ngũ phát triển xây dựng các sơ đồ kỹ thuật (UML Class, ERD, Functional).

### 1. Sơ đồ Quan hệ Thực thể (ERD - Entity Relationship Diagram)
Mô tả các bảng dữ liệu (Entities) và mối quan hệ giữa chúng trong cơ sở dữ liệu.

*   **Users (Người dùng)**:
    *   Thuộc tính: `id, username, password, email, full_name, role (ADMIN/MANAGER/EMPLOYEE), department_id, avatar_url`.
    *   Quan hệ:
        *   N-1 với `Departments` (Thuộc về 1 phòng ban).
        *   1-N với `Tasks (Tasks Created)` (Người tạo task).
        *   1-N với `Tasks (Tasks Assigned)` (Người được giao task).
        *   1-N với `Posts/Comments/Likes` (Tương tác xã hội).

*   **Departments (Phòng ban)**:
    *   Thuộc tính: `id, name, description, manager_id`.
    *   Quan hệ:
        *   1-N với `Users` (Có nhiều nhân viên).
        *   1-1 với `Users` (Manager - Trưởng phòng).

*   **Tasks (Công việc)**:
    *   Thuộc tính: `id, title, description, status (DRAFT...COMPLETED), priority, creator_id, assignee_id, department_id, proof_link`.
    *   Quan hệ:
        *   N-1 với `Users` (Creator).
        *   N-1 với `Users` (Assignee).
        *   N-1 với `Departments` (Thuộc về phòng ban nào - dùng để filter).

*   **Events (Sự kiện)**:
    *   Thuộc tính: `id, title, start_time, end_time, type (COMPANY/DEPARTMENT)`.
    *   Quan hệ: N-1 với `Departments` (Nếu là sự kiện phòng ban).

*   **Social Entities (Posts, Comments, Likes)**:
    *   Quan hệ tiêu chuẩn của mạng xã hội (User tạo Post, User Comment Post...).

---

### 2. Sơ đồ Lớp (OOP Class Diagram)
Mô tả cấu trúc mã nguồn theo mô hình MVC (chủ yếu là Controller và Model).

*   **Core Classes**:
    *   `BaseController`: Lớp cha, chứa phương thức `render()`, `redirect()`.
    *   `BaseModel`: Lớp cha, chứa kết nối Database (`PDO`), phương thức `find`, `findAll`, `create`, `delete`.

*   **Controllers (Xử lý Logic)**:
    *   `AuthController`: Xử lý `login()`, `logout()`. Không có `register()` (Disabled).
    *   `Admin\UserController`:
        *   Thuộc tính: `userModel`, `deptModel`.
        *   Phương thức: `checkPermission()` (Kiểm tra Admin/Manager), `index()` (List users), `create/store` (Thêm user - có validate quyền Manager), `edit/update`.
    *   `TasksController`:
        *   Phương thức: `index()` (Hiển thị Kanban, filter theo Role), `create()` (Check quyền Admin/Manager), `submit()` (Employee nộp bài), `review()` (Manager duyệt bài).

*   **Models (Truy xuất Dữ liệu)**:
    *   `UserModel`: `findByUsername()`, `updateProfile()`.
    *   `TaskModel`: `getByScope($scope, $userId)` (Lấy task theo context Cá nhân/Phòng/Công ty), `reviewTask()`.

---

### 3. Sơ đồ Chức năng (Functional Diagram)
Mô tả luồng dữ liệu và phân cấp chức năng.

*   **Luồng Admin (Tổng quản)**:
    *   Đăng nhập -> Dashboard -> Quản lý Phòng ban (Tạo Dept) -> Quản lý Nhân sự (Tạo Manager/Employee) -> Phân công công việc (Toàn công ty).

*   **Luồng Manager (Quản lý cấp trung)**:
    *   Đăng nhập -> Dashboard -> Xem Nhân sự (Filter list Department) -> Thêm Nhân viên (Auto-assign Dept) -> Tạo Task (Assign cho nhân viên này) -> Duyệt báo cáo (Kanban Review).

*   **Luồng Employee (Nhân viên)**:
    *   Đăng nhập -> Dashboard -> Xem Newsfeed -> Xem My Tasks (To-do list) -> Cập nhật tiến độ -> Nộp báo cáo (Submit Proof).
