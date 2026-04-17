# Nhật ký cập nhật (Update Log)

## Ngày 17/04/2026: Tái cấu trúc Hệ thống Quản lý Dự án & Tích hợp AI

### Mục tiêu đã giải quyết
Chuyển đổi quy trình quản lý "phẳng" (Task-Subtask) sang quy trình cấp bậc (Project-Task-Subtask) với hệ thống phân quyền 3 lớp: CEO -> Trưởng phòng -> Nhân viên.

### Các thay đổi chính
1. **Database Framework:**
   - Script chạy thành công để tạo các bảng `projects` và `project_departments`.
   - Cập nhật bảng `tasks`, thêm khóa ngoại `project_id`. Thêm workflow phê duyệt `approval_status` (`Pending`, `Submitted`, `Approved`, `Rejected`), `ai_report_post_id`.
   - Thêm chỉ mục (Indexes) để tối ưu query danh sách dự án.
2. **Models & Controllers:**
   - Tạo File `Project.php` Model xử lý CRUD Dự án.
   - Bổ sung `ProjectController.php` để xử lý giao tiếp REST API (Tạo, Sửa, Xoá dự án, Đánh dấu hoàn thành), kiểm tra Role ID (Chỉ CEO).
   - Tái cấu trúc `Task.php` Model bằng việc nối `project_id` khi tạo Task, nâng cấp truy vấn Batch GET để giảm tải vòng lặp N+1 khi load bảng dữ liệu.
   - Thêm các hàm vào `TaskController.php`: API Nhận xử lý "Trình duyệt CEO" và "CEO duyệt Task", đính kèm chức năng gọi Groq API để tạo báo cáo. Sửa hàm Index để chuyển hướng hiển thị từ dạng lưới `Quản lý theo Task` sang `Quản lý theo Dự án` cho CEO và Trưởng phòng.
3. **Mặt giao diện (Frontend):**
   - Đổi tên Menu Nav: "Dự án & Công việc".
   - Bổ sung trang con `views/tasks/projects.php`: Giao diện Dashboard cho Dự án. CEO tạo/gán nhiệm vụ cho phòng ban, nắm bắt tổng quan tiến độ các Tasks con đã duyệt bao nhiêu %. 
   - Đổi `views/tasks/index.php`: Nhúng Parameter `?project_id=...` để theo dõi các đầu việc (Tasks) bên trong 1 dự án. Tích hợp UI các nút Kiểm duyệt, gửi trình lên CEO, cảnh báo khóa chức năng chỉnh sửa khi bài đăng AI đã xuất bản trên bảng tin công ty.
4. **Relioo AI Core:**
   - Tối ưu hóa tích hợp API Groq (Llama 8b). 
   - Trưởng phòng kết thúc Task => Báo cáo được tạo động dựa vào subtask (gửi ẩn danh lên mạng xã hội với tag check-mark nhỏ).
   - CEO kết thúc Project => Nhả Post tổng kết vinh danh (Annoucement Post) hiển thị ra toàn doanh nghiệp trên thẻ Board.

### Ngày 17/04/2026: Giai đoạn 2 - Khôi phục View Trưởng phòng & Tích hợp Tab Dự án

### Các thay đổi bổ sung
1. **Controller (`TaskController.php`):**
   - Điều chỉnh logic để Trưởng phòng (Role 2) vẫn load đầy đủ Task/Subtask cũ ngay cả khi không có `project_id`.
   - Đồng thời tải danh sách Dự án của phòng ban để hiển thị trong Tab mới.
2. **View (`views/tasks/index.php`):**
   - Thêm tab thứ 3 "Dự án phòng ban" vào giao diện chính cho Role 2.
   - Nhúng Board hiển thị danh sách các Dự án tham gia dạng Card ngay trong Task View.
   - Cập nhật Custom JS (`switchView`) để hỗ trợ chuyển đổi mượt mà giữa 3 chế độ xem: Tiến độ, Theo Task, và Dự án.
   - Tối ưu hóa UI: Tự động điều chỉnh `overflow` của Container tùy theo tab (Cuộn ngang cho Kanban, Cuộn dọc cho danh sách Dự án).

### Ngày 17/04/2026: Giai đoạn 4 - Giải cứu nút "Tạo Task"

### Các thay đổi quan trọng
1. **Controller (`TaskController.php`):**
   - Sửa lỗi lệch tham số: Đã bổ sung việc thu thập và truyền `project_id` vào hàm `Task::create()`.
   - Đảm bảo tham số truyền vào Model đầy đủ 7 biến theo đúng thiết kế mới.
2. **View (`views/tasks/index.php`):**
   - **Tăng cường xử lý lỗi:** Bổ sung khối `.fail()`/`error:` cho các hàm AJAX quan trọng. Bây giờ, nếu server có lỗi kĩ thuật (500), giao diện sẽ hiển thị cảnh báo "Lỗi hệ thống" thay vì im lặng.

### Ngày 17/04/2026: Giai đoạn 5 - Khắc phục gửi duyệt Subtask (Lỗi Database Constraint)

### Các thay đổi quan trọng
1. **Database (`subtask_attachments`):**
   - Đã nới lỏng ràng buộc: Chuyển cột `file_url` và `file_name` sang trạng thái `NULL` (trước đó là `NOT NULL`).
   - Việc này cho phép nhân viên có thể gửi duyệt Subtask chỉ bằng Ghi chú hoặc Link mà không bắt buộc phải tải file ảnh lên.
2. **Procedure (`sp_SubmitSubtaskEvidence`):**
   - Đã kiểm tra lại logic: Giờ đây Procedure sẽ không bị crash/rollback khi `p_file_url` truyền vào là NULL.

### Ngày 17/04/2026: Giai đoạn 6 - Nâng cấp AI Report (Preview & Edit)

### Các thay đổi quan trọng
1. **Controllers (`TaskController`, `ProjectController`):**
   - Tách quy trình báo cáo AI thành 2 giai đoạn: Giai đoạn tạo nháp (Preview) và Giai đoạn xác nhận (Finalize).
   - Nâng cấp Prompt AI: Sử dụng dữ liệu báo cáo chi tiết từ cấp dưới để tổng hợp, giúp nội dung chuyên sâu và sát thực tế hơn.
   - Chuyển sang sử dụng model `llama-3.3-70b-versatile` cho chất lượng văn bản cao cấp nhất.
2. **Views (`index.php`, `projects.php`):**
   - Bổ sung Modal xem trước bài đăng AI.
   - Cho phép người dùng (Trưởng phòng/CEO) chỉnh sửa trực tiếp nội dung AI vừa soạn thảo trước khi bấm đăng hoặc trình duyệt.
   - Cải thiện trải nghiệm người dùng với Loading Spinner và thông báo hướng dẫn rõ ràng.

### Ngày 17/04/2026: Giai đoạn 7 - Tinh chỉnh Báo cáo AI & Tag nhận diện

**Trạng thái:** `[x] Hoàn thành` |  `[ ] Đang lên kế hoạch`

### Các thay đổi quan trọng
1. **Phạm vi bài đăng (Scope):**
   - Đã giới hạn bài đăng hoàn thành Task của Trưởng phòng chỉ xuất hiện trong **Kênh Phòng Ban (Department)**. Điều này giúp CEO vẫn theo dõi được nhưng không làm loãng Kênh Công Khai của toàn công ty.
2. **Văn phong báo cáo (Tone):**
   - Cập nhật System Prompt cho AI của Trưởng phòng: Chuyển từ văn phong "Email gửi sếp" sang văn phong **"Báo cáo tổng kết chung"**. Báo cáo giờ đây hướng tới toàn thể nhân viên và ban lãnh đạo, mang tính khách quan và chuyên nghiệp hơn.
3. **Nhận diện AI (Tagging):**
   - Đã thêm Tag **"Hỗ trợ bởi Relioo AI"** (kèm icon Robot) vào cuối tất cả các bài đăng được tạo bởi AI ở cả 3 cấp độ: Nhân viên (Subtask), Trưởng phòng (Task), CEO (Project).
4. **Tối ưu Báo cáo CEO:**
   - Cải thiện ngữ cảnh cho AI của CEO bằng cách tổng hợp dữ liệu từ các báo cáo thành phần của Trưởng phòng, giúp bài đăng dự án mang tính bao quát và chính xác cao.

### Ngày 17/04/2026: Giai đoạn 8 - Tối ưu UX/UI Báo cáo AI cho CEO

### Các thay đổi quan trọng
1. **Giao diện (Frontend):**
   - Thay đổi nút "Hoàn thành" dự án của CEO: Từ một biểu tượng nhỏ thành nút bấm nổi bật có text: **"Hoàn thành & Báo cáo AI"** kèm biểu tượng phép thuật (`bi-magic`).
   - Sử dụng Gradient bắt mắt để CEO dễ dàng nhận diện tính năng báo cáo AI.
2. **Backend (`ProjectController.php`):**
   - Tối ưu hóa việc lấy API Key và xử lý lỗi kết nối AI.
   - Nếu xảy ra lỗi (thiếu Key, lỗi API), hệ thống sẽ thông báo rõ ràng cho CEO thay vì trả về nội dung mặc định thành công, giúp minh bạch hóa hoạt động của AI.

### Kết quả
- CEO đã có thể tìm thấy và sử dụng tính năng Báo cáo AI một cách dễ dàng ngay tại danh sách Dự án.
- Luồng tương tác chuyên nghiệp, có thông báo trạng thái rõ ràng.

---
### Giai đoạn 9: Tích hợp Markdown cho Mạng xã hội [17/04/2026]
- **Mục tiêu:** Hiển thị bài đăng dưới định dạng Markdown để tối ưu hóa báo cáo AI và nâng cao thẩm mỹ.
- **Thư viện tích hợp:** 
    - `marked.js` (Parser Markdown).
    - `DOMPurify` (Sanitizer HTML).
- **Thay đổi chính:**
    - **header.php:** 
        - Thêm CDN thư viện và hệ thống CSS Markdown Premium (hỗ trợ headings, lists, blockquotes, code blocks).
        - Xây dựng đối tượng JS `ReliooMarkdown` với phương thức `render` và `renderAll` để tái sử dụng toàn cục.
        - Cập nhật logic Modal chi tiết bài viết để tự động render Markdown khi mở.
        - Tích hợp `renderAll` vào các luồng AJAX Tìm kiếm và Xóa tìm kiếm.
    - **social/index.php:**
        - Chuyển đổi cấu trúc hiển thị bài viết: Lưu nội dung raw (ẩn) và render Markdown vào container hiển thị.
        - Cập nhật logic Chỉnh sửa bài viết: Tự động lấy nội dung raw Markdown thay vì text đã bị xử lý.
        - Thêm script khởi tạo tự động render toàn bộ Newfeed khi tải trang.
- **Kết quả:** Các báo cáo AI từ CEO và Trưởng phòng giờ đây hiển thị gạch đầu dòng, tô đậm, và phân cấp tiêu đề cực kỳ chuyên nghiệp. Các bài đăng thủ công của người dùng cũng hỗ trợ Markdown đầy đủ.

---
### Giai đoạn 10: Chỉnh sửa Task và Subtask [17/04/2026]
- **Mục tiêu:** Bổ sung khả năng chỉnh sửa linh hoạt cho cấp quản lý.
- **Thay đổi chính:**
    - **Models:** Thêm phương thức `update` cho `Task` và `Subtask` để hỗ trợ cập nhật dữ liệu vào DB.
    - **TaskController:** 
        - Thêm API `apiUpdateTask` và `apiUpdateSubtask`.
        - Tích hợp thông báo tự động khi thay đổi người thực hiện (assignee).
    - **Frontend (tasks/index.php):**
        - Thêm nút Edit (biểu tượng Pencil) vào tiêu đề Task và các thẻ Subtask.
        - Xây dựng 2 Modal mới: `editTaskModal` và `editSubtaskModal`.
        - Viết JS logic để fetch dữ liệu cũ và gửi yêu cầu cập nhật qua AJAX.
- **Kết quả:** Trưởng phòng và CEO có thể dễ dàng sửa sai, thay đổi tiến độ hoặc điều chuyển công việc giữa các nhân viên mà không cần xóa đi tạo lại.

---
### Giai đoạn 11: Sửa lỗi lọc dữ liệu theo Project [17/04/2026]
- **Mục tiêu:** Khắc phục lỗi hiển thị tất cả Subtask công ty khi đang trong chế độ lọc theo Project.
- **Thay đổi chính:**
    - **Models (Subtask.php):**
        - Thêm phương thức `getByProject($project_id)` mới.
        - Cập nhật `getByDepartment` và `getByAssignee` để hỗ trợ lọc thêm theo `project_id`.
    - **Controllers (TaskController.php):**
        - Điều chỉnh hàm `index()` để truyền đúng `projectIdFilter` vào tất cả các luồng lấy dữ liệu Subtask.
- **Kết quả:** Bảng Kanban (Tiến độ) giờ đây hiển thị dữ liệu chính xác và tập trung. Khi CEO vào một dự án cụ thể, họ chỉ thấy các công việc con của dự án đó, giúp quản lý tập trung và hiệu quả hơn.
