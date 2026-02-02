# Enterprise Social Network (ESN) - Technical Introduction & Vision
## Tài liệu Giới thiệu Dự án (Project Manifesto)

### 1. Tổng quan Dự án (Executive Summary)
**SYNC ESN** là giải pháp phần mềm tích hợp (All-in-One Platform) dành cho doanh nghiệp vừa và nhỏ (SMEs). Dự án được thiết kế để giải quyết bài toán cốt lõi: **"Làm sao để quản lý công việc chặt chẽ theo phân cấp (Hierarchy) nhưng vẫn duy trì văn hóa doanh nghiệp cởi mở (Social Culture)?"**

Khác với các công cụ ERP thuần túy (khô khan, khó dùng) hay các mạng xã hội thuần túy (lỏng lẻo, thiếu kiểm soát), ESN là sự lai ghép (Hybrid Architecture) giữa:
*   **ERP-lite**: Quản trị Nhân sự, Phòng ban, Giao việc theo quy trình.
*   **Social Network**: Bản tin nội bộ, Tương tác, Sự kiện.

### 2. Mục tiêu Kỹ thuật & Nghiệp vụ (Core Objectives)
Dự án hướng tới 3 mục tiêu chính mà đội ngũ phát triển cần bám sát:

#### 2.1. Số hóa Cấu trúc Tổ chức (Digital Organization)
*   Hệ thống không vận hành dựa trên các cá nhân rời rạc. Nó vận hành dựa trên **Phòng ban (Departments)** và **Vai trò (Roles)**.
*   **Nguyên tắc Bất di bất dịch**:
    *   Mọi nhân viên phải thuộc về một Phòng ban.
    *   Mọi phòng ban nên có một Trưởng phòng (Manager).
    *   Dữ liệu và Quyền hạn được chia sẻ theo cấu trúc dọc (Vertical): Admin -> Manager -> Employee.

#### 2.2. Quy trình hóa Công việc (Structured Workflow)
*   Không cho phép "tạo task bừa bãi". Quy trình phải là: Cấp trên giao việc -> Cấp dưới thực hiện -> Nộp báo cáo -> Cấp trên duyệt.
*   Sử dụng mô hình **Kanban Board** nhưng có gắn ràng buộc Permission (Chỉ Manager mới được duyệt task hoàn thành).

#### 2.3. Mạng xã hội Định danh (Identity-based Social)
*   Mạng xã hội trong ESN không ẩn danh. Mọi hoạt động (Post, Comment) đều gắn liền với định danh nhân viên và chức danh của họ.
*   Mục tiêu: Tăng cường sự gắn kết nhưng vẫn giữ tính chuyên nghiệp (Professionalism).

### 3. Kiến trúc Hệ thống (System Architecture High-Level)
Hệ thống được xây dựng trên mô hình **MVC (Model-View-Controller)** cổ điển nhưng tối ưu cho hiệu năng và bảo mật:
*   **Backend**: PHP Thuần (Native PHP 8.x + OOP). Không phụ thuộc Framework nặng nề để đảm bảo team hiểu sâu về luồng chạy (Request Lifecycle).
*   **Database**: MySQL (Relational DB). Sử dụng khóa ngoại chặt chẽ để đảm bảo tính toàn vẹn dữ liệu (Data Integrity).
*   **Frontend**: HTML5/CSS3/JS (Vanilla). Giao diện tối giản, tập trung vào UX.

### 4. Định hướng Phát triển (Development Roadmap)
Đây là tài liệu khởi đầu cho Phase 1 của dự án.
*   **Phase 1 (Core)**: Hoàn thiện Auth, HR Management, Task Management, Newsfeed cơ bản. (Hiện tại).
*   **Phase 2 (Expansion)**: Tích hợp Chat Realtime (Socket), Chấm công, KPI.
*   **Phase 3 (Deploy)**: Phát triển và triển khai Web hoàn thiện dựa trên API của Backend hiện tại.

---
*Tài liệu này đóng vai trò kim chỉ nam cho toàn bộ team phát triển (Project Manager, Business Analyst, Front-end Dev, Back-end Dev). Mọi tính năng phát triển đều phải đối chiếu với các mục tiêu nêu trên.*
