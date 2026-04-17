**Trạng thái:** `[x] Hoàn thành` |  `[ ] Tiếp tục phát triển`
**Mục tiêu:** Cấp bậc hóa quy trình quản lý (CEO -> Trưởng phòng -> Nhân viên) thông qua việc phân chia Dự án (Projects), Công việc lớn (Tasks) và Công việc nhỏ (Subtasks). Tích hợp Relioo AI báo cáo tự động và liên kết tới Social Core. Bổ sung tính năng chỉnh sửa linh hoạt.

---

## 1. Phân tích Hiện trạng và Yêu cầu
Hiện tại, `Task` và `Subtask` đang bị nhập nhằng về vai trò, CEO chưa có không gian quản lý cấp cao.
Theo yêu cầu mới, hệ thống sẽ chia 3 cấp:
*   **Cấp 1 (CEO):** Quản lý **Projects (Dự án)**. Tạo dự án, gán cho nhiều phòng ban. Trực tiếp nhận và duyệt các Tasks hoàn thành từ Trưởng phòng. Khi Dự án xong, viết báo cáo tổng kết AI.
*   **Cấp 2 (Trưởng phòng):** Nhận Project từ CEO. Tạo **Tasks (Công việc lớn)** phục vụ Project đó. Nhận evidence của nhân viên để duyệt Subtasks bằng logic cũ. Khi Task hoàn thành 100%, dùng AI tạo báo cáo và gửi duyệt Task lên CEO.
*   **Cấp 3 (Nhân viên):** Nhận **Subtasks (Công việc nhỏ)** từ Trưởng phòng. Thực thi và nộp evidence (giữ nguyên logic cũ).

---

## 2. Thiết kế Cơ sở Dữ liệu (Database Design)

### 2.1. Tạo mới bảng `projects`
Lưu trữ thông tin Dự án do CEO tạo.
*   `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
*   `title` (VARCHAR 255)
*   `description` (TEXT)
*   `created_by` (INT, FK to users) - Chỉ CEO
*   `status` (ENUM: 'Active', 'Completed') - Mặc định 'Active'
*   `created_at`, `updated_at` (DATETIME)

### 2.2. Tạo mới bảng `project_departments`
Lưu trữ mối quan hệ nhiều-nhiều (N-N) để CEO gán Project cho các phòng ban thực thi.
*   `project_id` (INT, FK to projects)
*   `department_id` (INT, FK to departments)

### 2.3. Cập nhật bảng `tasks`
Liên kết Task với Project tương ứng và trạng thái duyệt của CEO.
*   **THÊM:** `project_id` (INT, FK to projects, Nullable do các task cũ không có).
*   **THÊM:** `approval_status` (ENUM: 'Pending', 'Submitted', 'Approved', 'Rejected') - Mặc định 'Pending'. Trạng thái trình duyệt lên CEO.
*   **THÊM:** `ai_report_post_id` (INT, FK to posts, Nullable). Lưu ID của bài viết AI Report trên Social Core do Trưởng phòng đăng khi gửi duyệt Task.

---

## 3. Thiết kế Chức năng (Backend & Frontend)

### 3.1. Phân quyền Hiển thị Giao diện (Frontend Router)
Thay đổi logic hiển thị ở `views/tasks/index.php` hoặc tạo tab riêng biệt cho CEO:
*   **CEO (`role_id = 1`):** Tab mặc định là **Dự án của công ty**. Hiển thị danh sách Project (dạng Card/Kanban). Dashboard CEO cần có mục "Chờ duyệt" cho các Tasks do Trưởng phòng gửi lên.
*   **Trưởng phòng (`role_id = 2`):** Tab mặc định là **Dự án phòng ban đang tham gia**. Bấm vào Project mới thấy bảng Kanban Tasks.
*   **Nhân viên (`role_id = 3`):** Dashboard hiển thị Subtasks cá nhân. (Như cũ, nhưng giao diện cần tinh gọn lại không cho thấy toàn cục Tasks của phòng nếu không cần thiết, hoặc chỉ xem list Task readonly).

### 3.2. Luồng CEO Tạo & Quản lý Dự án (CRUD Projects)
*   CEO có nút "Tạo Dự án Mới".
*   Popup tạo dự án: Tên, Mô tả chi tiết (Rich text), Khung thời gian (Deadline). Select Multiple để chọn các phòng ban tham gia (Department 1, Department 2,...).
*   CEO có quyền Sửa/Xóa. (Xóa Project sẽ xóa/unlink các Task bên trong).

### 3.3. Luồng Lập kế hoạch của Trưởng phòng (Tasks & Subtasks)
*   Trưởng phòng chọn 1 Project được giao, bấm "Thêm Task mới". (Form tạo Task thay vì chọn Department sẽ tự động ăn theo Department của Trưởng phòng và `project_id` hiện tại).
*   Từ Task tạo ra các Subtask chia cho mem (Giữ nguyên luồng code cũ).
*   Duyệt Subtask của nhân viên (Giữ nguyên luồng code cũ).

### 3.4. Luồng Trưởng phòng Gửi duyệt Task lên CEO (AI Tích hợp)
*   Tính năng: Tính toán tiến độ Task (dựa trên Subtask Done/Total). Khi tỷ lệ = 100%, hiện nút **"Báo cáo & Gửi duyệt CEO"** tại Task Card.
*   **Gửi duyệt (Backend Logic):**
    1. Tổng hợp dữ liệu bằng PHP: Tên task, mô tả, danh sách subtask, tên người làm, evidence, thời gian hoàn thành.
    2. Gửi request sang Groq API để tạo Prompt: *"Đóng vai trưởng phòng, viết báo cáo ngắn gọn gọn gàng gửi CEO về việc hoàn thành Task này..."*.
    3. Lưu nội dung trả về thành một bài đăng (Post) mới trên dạng Social Core (đánh dấu quyền tác giả là Trưởng phòng, tag URL Task vào bài).
    4. Gán Post ID vào trường `ai_report_post_id` của Task.
    5. Chuyển `approval_status` của Task -> `Submitted`. (Bắn notification cho CEO).

### 3.5. Luồng CEO Duyệt Task và Hoàn thành Dự án (AI Tích hợp)
*   CEO nhận Notification, bấm vào xem chi tiết Task gửi duyệt. Modal hiển thị: Bản Preview bài Post báo cáo AI + Các subtask details.
*   CEO chọn **Approve** (Task done vĩnh viễn) hoặc **Reject** (Kèm lý do để trưởng phòng làm lại).
*   **Hoàn thành Dự án:** Khi CEO thấy bảng Project hiển thị Progress 100% (Mọi Task của các phòng ban đều Approved). CEO bấm nút **"Đóng & Tổng kết Dự án"**.
    1. Gọi Groq AI, mớm data của toàn bộ Project (Tựa đề, các Departments, Các Task đã Approved). Prompt: *"Là CEO, viết bài diễn văn thông báo hoàn thành dự án xuất sắc lên bản tin công ty..."*
    2. CEO có thể edit nhanh nội dung, bấm "Đăng lên Thông báo".
    3. Dự án chuyển sang trạng thái `Completed`. Bài post đăng lên Social Core.

---

## 4. Tối ưu Database & Hiệu năng
1.  **Dùng Stored Procedures:** Những con số thống kê Progress % của Project (tính từ số Subtasks/Tasks đã Approved) sẽ rất nặng nếu đếm chay. Cần viết *Stored Procedure* hoặc *View* thay vì vòng lặp trong PHP N+1 query.
2.  **Indexing:** Đánh Index vào `project_id`, `department_id`, `created_by` và `approval_status` ở bảng projects và tasks để tối ưu tìm kiếm theo bộ lọc.
3.  **Lazy Loading:** Chỉ gọi API Groq khi Trưởng phòng/CEO thực sự bấm nút "Generate & Gửi". Trong lúc đợi API, hiển thị Loading Modal (Spinner) thân thiện.
4.  **Transaction:** Khi tạo hoặc Xóa Project kèm liên kết với phòng ban (`project_departments`), phải gói trong `DB Transaction` để phòng lỗi đứt đoạn.

---

## 5. Các file bị tác động dự kiến
*   `database/relioo_db (3).sql` (Cần chạy migrate file alter `.sql`)
*   `models/Project.php` **[NEW]**
*   `models/Task.php` **[MODIFY]** (Thêm quan hệ Project, Query status)
*   `controllers/ProjectController.php` **[NEW]**
*   `controllers/TaskController.php` **[MODIFY]**
*   `views/tasks/index.php` **[MODIFY]** (Mốc UI chia nhiều role)
*   `views/layouts/header.php` **[MODIFY]** (Navigation, API gọi Groq)

---
**NẾU BẠN DUYỆT KẾ HOẠCH NÀY, VUI LÒNG PHẢN HỒI LẠI TRONG KHUNG CHAT LÀ "Proceed" HOẶC "Đồng ý", TÔI SẼ BẮT ĐẦU CODE.**
---

## 6. Cập nhật Yêu cầu Giai đoạn 2: Khôi phục & Tích hợp View Trưởng Phòng

### 6.1. Mục tiêu
Theo phản hồi của User, Trưởng phòng (Role 2) cần giữ lại các Tab quản lý công việc cũ (Tiến độ & Theo Task) đồng thời thêm một Tab thứ 3 để xem các Dự án mà phòng ban tham gia. CEO (Role 1) vẫn mặc định vào thẳng View Dự án.

### 6.2. Thay đổi Logic Controller (`TaskController.php`)
*   **Role 2:** Không `return` sớm bằng `projects.php`.
*   Truy vấn danh sách Dự án của phòng ban (`$projectModel->getByDepartment($deptId)`) và gán vào biến `$projects`.
*   Tiếp tục logic lấy Tasks và Subtasks như cũ.
*   Require file `views/tasks/index.php`.

### 6.3. Thay đổi Giao diện (`views/tasks/index.php`)
*   **Tab Switcher:** Thêm nút "Dự án phòng ban" (chỉ cho Trưởng phòng).
*   **Board Container:**
    *   Thêm `#board-projects` chứa danh sách Project Cards (tái sử dụng UI từ `projects.php`).
    *   Mặc định khi load trang, nếu không có `project_id`, Trưởng phòng vẫn thấy tab "Quản lý theo tiến độ".
*   **Javascript:**
    *   Cập nhật `switchView()` để ẩn/hiện `#board-projects`.
    *   Tích hợp các hàm `completeProject`, `deleteProject` (nếu CEO vào view này) hoặc chỉ `viewDetail` cho Trưởng phòng.

### 6.4. Các file bị tác động
*   `controllers/TaskController.php`: Sửa hàm `index()`.
*   `views/tasks/index.php`: Thêm UI Tab 3 và các card projects.

---
**NẾU BẠN DUYỆT BỔ SUNG NÀY, VUI LÒNG PHẢN HỒI "Proceed" HOẶC "Đồng ý".**

---

## 7. Giai đoạn 3: Phục hồi kết nối API & Đồng bộ Database

### 7.1. Phân tích lỗi (Bug Analysis)
*   **Lỗi:** Khi CEO tạo dự án, thông báo "Không kết nối được với máy chủ" xuất hiện.
*   **Nguyên nhân:** Qua kiểm tra (lint/debug), phát hiện `ProjectController.php` gọi `session_start()` trong khi `index.php` đã khởi tạo session. Điều này sinh ra một **PHP Notice** ("Ignoring session_start()...") chèn vào trước chuỗi JSON trả về, khiến `fetch().then(r => r.json())` bị lỗi parse và nhảy vào khối `.catch()`.
*   **Vấn đề Database:** Bảng `projects` thực tế đang có cấu hình enum `status` là `('Active', 'Done')` và thiếu cột `updated_at`, trong khi code đang dùng `'Completed'`.

### 7.2. Giải pháp thực hiện
1.  **ProjectController.php:** Xóa bỏ tất cả các dòng `session_start()` dư thừa.
2.  **Database Fix:**
    *   Chỉnh sửa enum `status` của bảng `projects` thành `('Active', 'Completed')`.
    *   Thêm cột `updated_at` (với tính năng tự động cập nhật) vào bảng `projects` để đồng bộ với thiết kế.
3.  **Project.php:** Đảm bảo phương thức `updateStatus` sử dụng đúng giá trị `'Completed'`.

### 7.3. Các file bị tác động
*   `controllers/ProjectController.php`
*   Database (Table `projects`)

---
**NẾU BẠN DUYỆT SỬA LỖI NÀY, VUI LÒNG PHẢN HỒI "Proceed".**

---

## 8. Giai đoạn 4: Khắc phục lỗi nút "Tạo Task" bị liệt

### 8.1. Phân tích lỗi (Bug Analysis)
*   **Hiện tượng:** Khi Trưởng phòng điền form tạo Task và bấm "Tạo ngay", hệ thống không phản hồi và không báo lỗi.
*   **Nguyên nhân kĩ thuật:**
    *   **Mismatch tham số:** Hàm `Task::create()` yêu cầu 7 tham số nhưng `TaskController::createTask()` chỉ truyền 6 tham số (thiếu `project_id`). Gây ra lỗi `Fatal Error: ArgumentCountError`.
    *   **Thiếu xử lý lỗi AJAX:** Hàm `saveTask()` trong JS không có bắt lỗi `fail()`. Khi server gặp lỗi Fatal (HTTP 500 hoặc nội dung không phải JSON), code JS bị dừng lại mà không hiển thị thông báo.

### 8.2. Giải pháp thực hiện
1.  **Sửa TaskController.php:** Lấy `project_id` từ POST và truyền vào Model `Task::create()`.
2.  **Sửa views/tasks/index.php:** Bổ sung khối `.fail()` cho các hàm AJAX (`saveTask`, `saveMultipleSubtasks`) để hiện thông báo lỗi hệ thống nếu server gặp sự cố.

---
**NẾU BẠN DUYỆT SỬA LỖI NÀY, VUI LÒNG PHẢN HỒI "Proceed".**

---

## 9. Giai đoạn 5: Khắc phục lỗi gửi duyệt Subtask không đổi trạng thái

### 9.1. Phân tích lỗi (Bug Analysis)
*   **Hiện tượng:** Nhân viên bấm "Gửi duyệt" báo thành công nhưng trạng thái Subtask vẫn là "In Progress". Trưởng phòng không thấy việc chờ duyệt.
*   **Nguyên nhân kĩ thuật:**
    *   **Ràng buộc DB:** Bảng `subtask_attachments` có cột `file_url` là `NOT NULL`.
    *   **Transaction Rollback:** Khi nhân viên gửi duyệt mà không tải file (chỉ nhập Ghi chú), Procedure `sp_SubmitSubtaskEvidence` bị lỗi INSERT, dẫn đến `ROLLBACK` toàn bộ thay đổi (bao gồm cả trạng thái "Pending").

### 9.2. Giải pháp thực hiện
1.  **Chạy lệnh SQL:**
    ```sql
    ALTER TABLE subtask_attachments MODIFY file_url VARCHAR(500) NULL;
    ALTER TABLE subtask_attachments MODIFY file_name VARCHAR(255) NULL;
    ```
2.  **Đồng bộ Model:** Đảm bảo `Subtask.php` không bắt lỗi giả nếu URL là null.

---
**NẾU BẠN DUYỆT SỬA LỖI NÀY, VUI LÒNG PHẢN HỒI "Proceed".**

---

## 10. Giai đoạn 6: Nâng cấp AI Report (Preview & Edit)

### 10.1. Mục tiêu
- Khắc phục việc AI tự động đăng bài ngay lập tức khiến người dùng không kiểm soát được nội dung.
- Tách quy trình thành 2 bước: AI soạn thảo nháp -> Người dùng duyệt/sửa -> Lưu và Đăng bài.
- Cải thiện Prompt AI để lấy dữ liệu sâu từ các Subtasks (đối với Task) và từ các Task (đối với Project).

### 10.2. Các thành phần thay đổi
1. **Controllers:**
   - `TaskController.php`: Thêm API tạo nháp báo cáo Task; Sửa API nộp duyệt CEO.
   - `ProjectController.php`: Thêm API tạo nháp báo cáo Project; Sửa API hoàn thành dự án.
2. **Views:**
   - `views/tasks/index.php`: Thêm Modal xem trước cho Trưởng phòng; Cập nhật JS gọi API 2 bước.
   - `views/tasks/projects.php`: Thêm Modal xem trước cho CEO; Cập nhật JS gọi API 2 bước.

---
**NẾU BẠN DUYỆT SỬA LỖI NÀY, VUI LÒNG PHẢN HỒI "Proceed".**

---

## 11. Giai đoạn 7: Tinh chỉnh Báo cáo AI & Tag nhận diện

### 11.1. Mục tiêu
- Giới hạn phạm vi bài đăng của Trưởng phòng: Chỉ đăng lên Kênh Phòng Ban, tránh làm loãng Kênh Công Khai.
- Thay đổi văn phong báo cáo của Trưởng phòng: Trở thành báo cáo tổng kết chung cho mọi đối tượng, không còn là văn phong email gửi riêng cho CEO.
- Nâng cấp báo cáo của CEO: Tự động tổng hợp dữ liệu từ các báo cáo Task của Trưởng phòng để tạo báo cáo Project chất lượng cao.
- Đồng bộ Tag AI: Tất cả bài đăng có AI hỗ trợ đều được gắn tag "Hỗ trợ bởi Relioo AI".

### 11.2. Các thành phần thay đổi
1. **TaskController.php**:
   - Sửa `submitTaskToCEO`: Đổi channel từ `public` sang `Department`.
   - Sửa `apiGenerateTaskReportForCEO`: Cập nhật System Prompt cho văn phong báo cáo chung.
   - Thêm Tag AI vào nội dung bài đăng.
2. **ProjectController.php**:
   - Sửa `apiGenerateProjectSummary`: Đảm bảo context lấy từ các task_reports.
   - Thêm Tag AI vào nội dung bài đăng Project.

---
**NẾU BẠN DUYỆT SỬA LỖI NÀY, VUI LÒNG PHẢN HỒI "Proceed".**

---

## Giai đoạn 9: Tích hợp Markdown cho Mạng xã hội

**Mục tiêu:** Hiển thị bài đăng dưới dạng Markdown đẹp mắt, hỗ trợ tốt cho các báo cáo AI và nâng cao trải nghiệm người dùng.

### Các hạng mục chính:
*   [x] Tích hợp `marked.js` và `DOMPurify` (CDN).
*   [x] Xây dựng hệ thống CSS Markdown phong cách Premium.
*   [x] Cập nhật Newfeed (`social/index.php`) để render Markdown.
*   [x] Cập nhật Modal chi tiết bài viết (`header.php`) để render Markdown.
*   [x] Kiểm thử hiển thị tiêu đề, danh sách, mã nguồn và bảng biểu.

---

## Giai đoạn 10: Chỉnh sửa Task và Subtask nâng cao [MỚI]

**Mục tiêu:** Bổ sung tính năng chỉnh sửa cho Trưởng phòng và CEO để linh hoạt hơn trong quản lý công việc và phân bổ lại nhân sự.

### Các hạng mục chính:
*   [x] Thêm phương thức `update` vào `Task` và `Subtask` model.
*   [x] Xây dựng API `apiUpdateTask` và `apiUpdateSubtask` trong `TaskController`.
*   [x] Thêm icon chỉnh sửa (Pencil) và Modal form tại bảng quản lý Task.
*   [x] Xử lý đồng bộ thông báo khi thay đổi người thực hiện Subtask.
*   [x] Tích hợp AJAX cập nhật nội dung tức thì không cần reload thủ công.

---

## Giai đoạn 11: Sửa lỗi lọc dữ liệu theo Project [MỚI]

**Mục tiêu:** Đảm bảo khi vào một dự án cụ thể, bảng Kanban chỉ hiển thị đúng các công việc thuộc dự án đó, không hiển thị tràn lan các công việc khác.

### Các hạng mục chính:
*   [x] Cập nhật `Subtask` model: Thêm/Sửa các phương thức lấy dữ liệu có hỗ trợ tham số `project_id`.
*   [x] Cập nhật `TaskController`: Truyền bộ lọc `projectIdFilter` vào logic lấy Subtask cho mọi phân quyền (CEO, Leader, Staff).
*   [x] Kiểm thử: Xác nhận bảng "Tiến độ" (Kanban) hiển thị chính xác theo context dự án.

---

## Giai đoạn 12: Thiết kế Phân quyền Admin Tối cao & Không gian Doanh nghiệp (Multi-tenant) [ĐANG LÊN KẾ HOẠCH]

**Mục tiêu:** Nâng cấp hệ thống từ đơn doanh nghiệp sang mô hình cung cấp dịch vụ (SaaS/Multi-tenant). Xây dựng cơ chế để CEO đăng ký không gian làm việc (Workspace) riêng, và thiết lập quyền Admin (`role_id = 4`) đóng vai trò quản lý cao nhất toàn hệ thống.

### 1. Phân tích Yêu cầu Mới
- **Luồng Đăng ký (CEO):** Trang đăng nhập sẽ có thêm nút "Đăng ký tạo không gian doanh nghiệp". Form yêu cầu: Tên công ty, Lĩnh vực, Thông tin CEO (Họ tên, Email, SĐT, Mật khẩu mong muốn).
- **Phân quyền Admin (`role_id = 4`):**
  - **Quản lý Cấp phép:** Admin duyệt các đơn đăng ký. Khi được duyệt, hệ thống tạo bản ghi Công ty, và tạo tài khoản cho CEO được gắn với Công ty đó.
  - **Thao tác Dữ liệu (Super User):** Admin có quyền lực cao nhất, không bị giới hạn bởi không gian của một công ty cụ thể, có thể xem, sửa, xóa bất kỳ dữ liệu nào (Users, Projects, Posts...) trên toàn hệ thống.
- **Kiến trúc Dữ liệu (Multi-tenant):**
  - Dữ liệu của các công ty phải được cô lập hoàn toàn. Nhân viên/CEO công ty A không thể xem dữ liệu (bài đăng, task, phòng ban) của công ty B.

### 2. Thiết kế Cơ sở Dữ liệu (Database Design)
- **Tạo bảng mới `companies` (Doanh nghiệp):**
  - `id` (INT, PK, AUTO_INCREMENT)
  - `company_name` (VARCHAR 255)
  - `industry` (VARCHAR 255)
  - `ceo_name` (VARCHAR 255), `ceo_email` (VARCHAR 255), `ceo_phone` (VARCHAR 20)
  - `status` (ENUM: 'pending', 'approved', 'rejected') - Mặc định 'pending'.
  - `created_at`, `updated_at` (DATETIME)
- **Nâng cấp Cấu trúc Đa doanh nghiệp (Multi-tenant Migration):**
  - Thêm cột `company_id` (INT, FK tới `companies`) vào toàn bộ các bảng nòng cốt: `users`, `departments`, `projects`, `tasks`, `posts`, `comments`, `notifications`.
  - Quản trị viên (Admin) sẽ có `company_id = NULL` vì họ là người của toàn hệ thống.

### 3. Thiết kế Chức năng (Backend & Frontend)
#### 3.1. Luồng Đăng ký Không gian Doanh nghiệp
- Thêm link/button "Đăng ký tạo không gian doanh nghiệp" tại `views/auth/login.php`.
- Xây dựng giao diện `views/auth/register_company.php`.
- Cập nhật `AuthController.php` thêm phương thức tiếp nhận form, lưu dữ liệu tạm vào bảng `companies` với trạng thái `pending`.

#### 3.2. Dashboard dành cho Admin & Xét duyệt
- **Cổng Đăng Nhập Ẩn (Secret Portal):** Tạo một đường dẫn riêng biệt (ví dụ: `/?action=admin_secret_portal`) trỏ tới `views/admin_super/login.php` để Admin đăng nhập. Tại trang đăng nhập thường, nếu nhập tài khoản Admin sẽ bị từ chối hoặc báo lỗi không tồn tại để tránh lộ.
- Sau khi đăng nhập thành công qua cổng ẩn, điều hướng về `/views/admin_super/dashboard.php`.
- Xây dựng bảng hiển thị các `companies` đang ở trạng thái `pending`.
- **Logic Duyệt (Approve):**
  - Đổi trạng thái `companies.status = 'approved'`.
  - Tự động tạo 1 bản ghi vào bảng `users` (`role_id = 1` - CEO, `company_id` vừa duyệt, mật khẩu lấy từ form đăng ký).

#### 3.3. Cô lập Dữ liệu (Workspace Isolation)
- Khi User đăng nhập thành công, lưu `$_SESSION['company_id']`.
- **Cập nhật SQL Models & Stored Procedures:** Rà soát và thêm điều kiện `WHERE company_id = :company_id` vào TẤT CẢ các câu lệnh SQL (chọn phòng ban, hiển thị bài viết, hiển thị dự án).
- **Đặc quyền Admin:** Nếu `$_SESSION['role_id'] == 4`, các câu lệnh SQL sẽ tự động bỏ qua điều kiện lọc `company_id`, cho phép Admin truy vấn chéo toàn hệ thống và thực hiện các thao tác quản trị cao cấp.

### 4. Các file bị tác động dự kiến
- **Database:** Script ALTER quy mô lớn (`06_multi_tenant.sql`).
- **Models:** `Company.php` (mới), cập nhật tất cả models hiện có (`User`, `Department`, `Project`, `Post`...).
- **Controllers:** `AuthController.php`, `AdminController.php` (viết lại/cập nhật), `CompanyController.php`.
- **Views:** Tạo `views/auth/register_company.php`, `views/admin_super/` (bao gồm `login.php` và `dashboard.php`) cho phân quyền Admin. Cập nhật `login.php`.
- **Stored Procedures:** Bắt buộc phải được ALTER để thêm nhận tham số `p_company_id`.

---
**NẾU BẠN DUYỆT KẾ HOẠCH NÀY, VUI LÒNG PHẢN HỒI LẠI TRONG KHUNG CHAT LÀ "Proceed" HOẶC "Đồng ý", TÔI SẼ BẮT ĐẦU CODE CÁC THAY ĐỔI.**
