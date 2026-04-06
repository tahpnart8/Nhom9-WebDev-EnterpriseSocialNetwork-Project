# ĐỀ BÀI: YÊU CẦU CHO ĐỒ ÁN KẾT THÚC MÔN
**CHỦ ĐỀ: XOAY QUANH 04 CHỦ ĐỀ CHÍNH - THƯƠNG MẠI ĐIỆN TỬ, MẠNG XÃ HỘI, WEBSITE TIN TỨC, DIỄN ĐÀN.**

- **Yêu cầu tối thiểu:** dùng Bootstrap cho phần thiết kế. Với Backend thì phải viết theo Hướng đối tượng. 
- Sẽ được đánh giá cao nếu viết theo **OOP+MVC** và có tích hợp **AJAX/Webservice** (mức sử dụng service, không tạo ra service). Ưu tiên dữ liệu được truyền tải khi dùng AJAX/Webservice thông qua XML/JSON. 
- Phần quản trị, chỉ cần dừng ở mức cơ bản (Thêm được danh mục, sản phẩm, bài viết, v.v.).
- Tuỳ vào ngôn ngữ backend sử dụng, các bạn có thể linh động chọn hệ quản trị CSDL phù hợp: MySQL, SQL Server, PostgreSQL, Oracle, v.v.
- Phần phân chia công việc theo nhóm: tạo shared host để truy cập database, dùng github và quản lý theo branch, dùng teams (hoặc phần mềm tương tự) để quản lý tiến độ và trao đổi nhóm. Tất cả đều có minh chứng và đưa vào báo cáo (ảnh chụp màn hình).

> ***** **Các bạn có thể đăng kí các free hosting để upload trang web. Nên làm dù không bắt buộc!** *****

---

## 🎯 Mục Tiêu Dự Án
Xây dựng một **Mạng Xã Hội Thu Nhỏ** dành riêng cho doanh nghiệp/tổ chức. Mục tiêu không chỉ là quản lý công việc, mà quan trọng hơn là xây dựng văn hóa kết nối:
- Tạo không gian để mọi thành viên cập nhật tin tức, sự kiện nóng hổi.
- Thúc đẩy sự tương tác, trao đổi cởi mở qua giao diện thân thiện như Facebook/LinkedIn.
- Tích hợp công cụ làm việc một cách tự nhiên vào luồng giao tiếp xã hội.

### 1. Tổng Quan Hệ Thống (System Overview)
Hệ thống là một nền tảng hợp nhất giữa **Quản lý công việc** (Task Management) và **Truyền thông nội bộ** (Social Networking). Điểm khác biệt cốt lõi là tính năng *"Tự động hóa truyền thông"*: Biến kết quả công việc khô khan thành những bài đăng đầy cảm hứng trên bảng tin để xây dựng văn hóa doanh nghiệp.

### 2. Mô Tả Chi Tiết Các Phân Hệ Chức Năng
Dự án *"Mạng xã hội quản lý doanh nghiệp Relioo – Tái kết nối để cùng phát triển."*

> 👉 Một nền tảng giúp gắn kết con người, đồng bộ thông tin và xây dựng sức mạnh tập thể trong doanh nghiệp.

Việc kết hợp giữa "Mạng xã hội" (tương tác, gắn kết) và "Quản lý công việc" (Kanban, Workflow), đặc biệt là điểm nhấn tích hợp AI để tự động hóa báo cáo/đăng bài.

---

## PHẦN 1: MÔ TẢ CÁC NHÓM NGƯỜI DÙNG VÀ LOGIC NGHIỆP VỤ (AUTHORIZATION & WORKFLOW)

Hệ thống được chia làm 2 không gian làm việc biệt lập nhằm đảm bảo tính bảo mật và chuyên môn hóa:
- **Front Office (Không gian làm việc & Tương tác):** Giao diện chính cho toàn bộ nhân sự (Newsfeed, Chat, Kanban Board).
- **Back Office (Phân hệ Quản trị - Module 5):** Nơi thiết lập cấu hình nền tảng, chỉ dành riêng cho Admin/CEO.

Dựa vào sơ đồ tổ chức, hệ thống chia làm 3 Nhóm quyền (Roles) với các đặc quyền và phân vùng dữ liệu nghiêm ngặt như sau:

### 1. NHÂN VIÊN (Staff) - Cấp độ Thực thi
Đây là lực lượng nòng cốt sử dụng hệ thống hàng ngày. Mỗi nhân viên được gắn cố định vào 1 Phòng ban định sẵn.

* **Phân vùng dữ liệu (Quyền xem):**
  * **Mạng xã hội:** Chỉ nhìn thấy các bài đăng được tag là *Công khai toàn công ty* (từ CEO) và các bài đăng trong phạm vi *Nội bộ phòng ban* của mình. Tuyệt đối không thấy bài đăng/báo cáo của phòng ban khác.
  * **Công việc:** Trên bảng Kanban cá nhân, chỉ nhìn thấy các Subtask (Công việc con) mà mình được Trưởng phòng đích thân giao (Assignee = User_ID).
* **Logic Nghiệp vụ (Quyền thao tác):**
  * **Luồng xử lý Task:** Nhận Subtask -> Làm việc -> Cập nhật trạng thái sang *In Progress* -> Khi xong, đính kèm link/file minh chứng (Nếu có) và chuyển sang *Pending* (Chờ duyệt). Nhân viên không có quyền tự chuyển Subtask sang *Done*.
  * **Tạo bài viết:** Được quyền đăng bài lên Newsfeed, nhưng hệ thống sẽ mặc định set bài đăng đó ở chế độ *"Chỉ lưu hành trong phòng ban"* (hoặc đăng công khai nếu được cấp quyền).
  * **AI Workflow:** Khi Subtask được Trưởng phòng duyệt (Done), nhân viên sẽ nhận được thông báo “Đã duyệt” và là người trực tiếp điền form trả lời câu hỏi ngắn để AI sinh ra báo cáo, review lại câu chữ do AI viết và bấm nút Đăng bài.

### 2. TRƯỞNG PHÒNG / LEADER - Cấp độ Quản lý & Phê duyệt
Mỗi Trưởng phòng quản lý 1 Phòng ban cụ thể. Vai trò chính là điều phối công việc và kiểm soát chất lượng đầu ra.

* **Phân vùng dữ liệu (Quyền xem):**
  * **Mạng xã hội:** Tương tự nhân viên, thấy tin Công khai và tin Nội bộ phòng ban mình quản lý.
  * **Công việc (Dashboard Phòng ban):** Nhìn thấy toàn cảnh tất cả Task và Subtask của tất cả nhân viên trong phòng ban mình trên Kanban Board. Không xem được tiến độ của phòng ban khác.
* **Logic Nghiệp vụ (Quyền thao tác):**
  * **Luồng Giao việc:** Tạo ra các Task tổng -> Chia nhỏ thành các Subtask -> Phân công (Assign) từng Subtask cho các nhân viên cụ thể trong phòng. *(Lưu ý: 1 Subtask chỉ giao cho 1 nhân viên duy nhất).*
  * **Luồng Phê duyệt (Gatekeeper):** Khi nhận Notification nhân viên nộp việc (Subtask ở trạng thái *Pending*), Trưởng phòng xem minh chứng.
    * *Nếu đạt:* Bấm Duyệt (Approve) -> Gửi thông báo “Đã duyệt” cho nhân viên -> Kích hoạt quyền dùng AI tạo báo cáo cho nhân viên.
    * *Nếu chưa đạt:* Bấm Từ chối (Reject) kèm lý do (Comment) -> Trạng thái quay về *In Progress* -> Nhân viên phải làm lại. Nếu việc làm lại rơi vào quá hạn, hệ thống sẽ gửi thông báo trễ deadline cho nhân viên, đồng thời Trưởng phòng có thể linh hoạt chọn tính năng Gia hạn (Update deadline).
  * **AI Workflow Task Tổng:** Khi tất cả Subtask Done, Trưởng phòng bấm hoàn thành Task Tổng, sử dụng AI tổng hợp mọi báo cáo con thành một bài Báo cáo dự án lớn và đăng lên Newsfeed.

*(Lưu ý: Trưởng phòng KHÔNG có quyền truy cập Back Office - Module 5).*

### 3. CEO (Ban Giám Đốc) - Cấp độ Giám sát toàn cục (Front Office)
Vai trò lãnh đạo cao nhất trên không gian làm việc thực tế, tập trung vào giám sát và truyền thông.

* **Phân vùng dữ liệu (Quyền xem):**
  * **Mạng xã hội:** Nhìn thấy TOÀN BỘ bài đăng của tất cả các phòng ban. Bảng tin (Newsfeed) của CEO là nơi hội tụ mọi luồng thông tin.
  * **Công việc (Dashboard Tổng hợp):** Có chế độ xem toàn cảnh (Overview) tiến độ Task của mọi phòng ban, biết được phòng nào đang trễ deadline, dự án nào đã hoàn thành.
* **Logic Nghiệp vụ (Quyền thao tác):**
  * Đăng các Thông báo, Quyết định ở chế độ *"Công khai toàn công ty"* (Ghim lên đầu Newsfeed của mọi người).
  * Tương tác (Like, Comment) khích lệ vào báo cáo công việc của bất kỳ phòng ban nào.

*(Lưu ý: CEO không tham gia vào luồng giao việc hay duyệt Task của nhân sự (việc này thuộc về Trưởng phòng)).*

### 4. ADMIN - Cấp độ Quản trị hệ thống (Back Office)
Người nắm quyền cấu hình nền tảng, hoạt động hoàn toàn ở Module hệ thống ẩn (Không can thiệp vào bảng tin mạng xã hội hay tiến độ công việc).

* **Logic Nghiệp vụ (Module 5):**
  * **Quản lý tổ chức:** Thêm/Sửa/Xóa cơ cấu Phòng ban mới.
  * **Quản lý nhân sự:** Thêm/Sửa/Xóa nhân sự, cập nhật thông tin.
  * **Phân quyền:** Gán Role (Quyền CEO, Trưởng phòng, Nhân viên), cấp tài khoản và reset mật khẩu.

### 5. NGHIỆP VỤ ĐO LƯỜNG VÀ PHÂN TÍCH (Master Dashboard - Trung tâm kiểm soát vận hành)
Bên cạnh góc nhìn Kanban Board, hệ thống cung cấp riêng cho CEO một trang Master Dashboard. Đây là trung tâm phân tích dữ liệu theo thời gian thực (Real-time Analytics), giúp CEO nắm bắt sức khỏe vận hành của toàn doanh nghiệp bằng các con số và biểu đồ trực quan, hỗ trợ ra quyết định (Data-driven decision making).

Master Dashboard được thiết kế chia làm 4 phân vùng dữ liệu chính:

* **A. Phân vùng KPI Tổng quan (High-level Metrics Cards)**
  Hiển thị dưới dạng các thẻ số liệu lớn ở trên cùng màn hình (Sử dụng Bootstrap Cards), cập nhật tức thời:
  * **Total Active Projects:** Tổng số Task Tổng (Dự án) đang chạy trong công ty.
  * **Total Workload:** Tổng số Subtask đang ở trạng thái To Do và In Progress.
  * **Overdue Alert:** Tổng số Task/Subtask đang bị trễ hạn (Deadline < Ngày hiện tại). Đây là chỉ số báo động đỏ CEO cần chú ý nhất.
  * **Completion Rate:** Tỉ lệ hoàn thành công việc toàn công ty trong tháng (Số Subtask Done / Tổng số Subtask).

* **B. Phân vùng Phân tích Tải Trọng Nhân Sự (Employee Workload Analysis)**
  CEO cần biết ai đang quá tải, ai đang rảnh rỗi để điều phối lại nhân sự. Phân vùng này hiển thị dưới dạng Biểu đồ cột (Bar Chart) hoặc danh sách:
  * **Top 5 Overloaded Employees:** Danh sách 5 nhân viên đang ôm nhiều Subtask ở trạng thái In Progress nhất (Ví dụ: Nhân viên A đang có 15 tasks đang chạy -> Báo động đỏ về quá tải).
  * **Top 5 Performers:** Danh sách 5 nhân viên có số lượng Subtask chuyển sang Done nhiều nhất trong tuần/tháng (Cơ sở để tuyên dương, thưởng nóng).
  * **Trạng thái nhân sự theo thời gian thực:** Thống kê nhanh công ty có bao nhiêu người đang Online (hoạt động trong ngày), bao nhiêu tài khoản đang Inactive.

* **C. Phân vùng Hiệu Suất Phòng Ban (Departmental Performance)**
  Dùng để so sánh năng lực vận hành giữa các phòng ban. Hiển thị dưới dạng Biểu đồ tròn (Pie Chart) hoặc Biểu đồ cột xếp chồng (Stacked Bar Chart):
  * **Task Distribution:** Tỉ lệ phân bổ công việc (Phòng IT chiếm 40% khối lượng công việc toàn công ty, Phòng Marketing chiếm 30%...).
  * **Department Bottlenecks (Điểm nghẽn):** Thống kê số lượng Subtask đang nằm ở cột Pending (Chờ duyệt) của từng phòng. Nếu Phòng A có quá nhiều task ở trạng thái Pending, chứng tỏ Trưởng phòng A đang duyệt việc quá chậm, tạo ra điểm nghẽn (bottleneck) cho hệ thống.

* **D. Phân vùng Chỉ số Tương Tác Văn Hóa (Social Engagement Metrics)**
  Vì đây là Mạng Xã Hội, CEO cần biết công cụ này có đang được sử dụng hiệu quả hay không:
  * **AI Post Generation:** Tổng số bài báo cáo đã được AI tự động tạo và đăng thành công lên Newsfeed.
  * **Engagement Rate:** Tổng số lượt Tương tác (React, Comment) trên hệ thống trong tuần. Biểu đồ đường (Line Chart) thể hiện xu hướng tương tác của nhân viên đi lên hay đi xuống.

### BỔ SUNG GÓC NHÌN KỸ THUẬT
Để hệ thống vận hành trơn tru luồng logic trên, Database và Backend cần được thiết kế như sau (Bạn có thể đưa đoạn này vào báo cáo để thể hiện tư duy thiết kế phần mềm):

* **Thiết kế Database (Khóa ngoại cốt lõi):**
  * Bảng `Users` bắt buộc phải có cột `department_id` và cột `role` (VD: 1=CEO, 2=Leader, 3=Staff).
  * Bảng `Posts` (Bài đăng) bắt buộc phải có cột `visibility` (Phạm vi hiển thị: `public` hoặc `department`) và `department_id` (Đánh dấu bài này thuộc phòng nào).
  * Bảng `Tasks` và `Subtasks` cũng phải gắn với `department_id`.
* **Cơ chế Phân vùng (Controller/Model Logic):**
  * Khi load Newsfeed, nếu User đang đăng nhập là Nhân viên hoặc Leader của Phòng ban A (ID=1), câu lệnh SQL sẽ tự động lọc: 
    ```sql
    SELECT * FROM Posts WHERE visibility = 'public' OR department_id = 1
    ```
    Từ đó, các bài đăng của Phòng B (ID=2) sẽ bị ẩn hoàn toàn.
  * Nếu là CEO, câu lệnh SQL sẽ bỏ qua điều kiện `department_id`, truy xuất toàn bộ dữ liệu.

---

## PHẦN 2: MÔ TẢ CHI TIẾT CHỨC NĂNG HỆ THỐNG
Hệ thống được chia thành 5 module chính tương ứng với sơ đồ của bạn.

### MODULE 1: SOCIAL CORE (MẠNG XÃ HỘI NỘI BỘ)
**Mục tiêu:** Tạo không gian giao tiếp mở, giống Facebook/LinkedIn nhưng dành riêng cho nội bộ.
* **Trang chủ (Newsfeed):** Hiển thị luồng bài đăng (Timeline) từ các thành viên, phòng ban. Các bài đăng có thể là thông báo công ty, chia sẻ kiến thức, hoặc báo cáo hoàn thành công việc tự động. (Sử dụng AJAX để cuộn tải thêm bài viết - Infinite Scroll).
* **Quản lý bài đăng (Post Management):**
  * Tạo bài viết (chứa Text, Hình ảnh, Video, File đính kèm). *Tất cả các tài nguyên media/file đính kèm sẽ được lưu trữ đám mây thông qua **Google Drive API** để đảm bảo đồng bộ khi dùng mạng LAN ảo.*
  * Tương tác: React (Thích, Thả tim...), Comment (Bình luận nhiều cấp), Share (Chia sẻ về tường cá nhân/phòng ban). *(Mọi nhân sự nhìn thấy bài viết Public đều có quyền bình luận và xem bình luận của nhau).*
* **Nhắn tin & Trao đổi (Messenger):**
  * Chat cá nhân (1-1) và Chat nhóm (Group theo phòng ban/dự án). (Khuyến khích dùng AJAX polling hoặc Websocket nếu nhóm có khả năng).
* **Hồ sơ cá nhân (Profile):** Cập nhật Avatar, thông tin cá nhân, chức vụ, phòng ban và xem lại lịch sử các bài đã đăng/công việc đã hoàn thành.

### MODULE 2: QUẢN LÝ CÔNG VIỆC (TASK MANAGEMENT)
Đây là "trái tim" vận hành nghiệp vụ của hệ thống, được thiết kế theo mô hình quản lý Kanban Board (Tương tự Trello/Jira). Module này giúp số hóa toàn bộ luồng giao việc, thực thi và kiểm soát tiến độ.

Hệ thống phân chia cấu trúc công việc làm 2 cấp độ cốt lõi:
* **TASK (Công việc cha/Dự án nhỏ):** Được tạo bởi Trưởng phòng. Chứa các thông tin tổng quan như: Tiêu đề, Mô tả sơ bộ, Deadline tổng, và Mức độ ưu tiên (Priority).
* **SUBTASK (Công việc con):** Được chia nhỏ từ Task. Mỗi Subtask chỉ được giao (assign) cho 1 nhân viên cụ thể chịu trách nhiệm thực thi.

Để tối ưu hóa trải nghiệm người dùng theo đúng sơ đồ hệ thống, Module này được chia làm 2 không gian hoạt động biệt lập: *Taskboard Hành Động (Cá nhân)* và *Taskboard Chế Độ Xem (Phân quyền)*.

#### 2.1. Không gian 1: TASK BOARD CÁ NHÂN (Action Board - Dành cho thao tác)
Đây là không gian làm việc trực tiếp của người được giao việc (Nhân viên). Giao diện là một bảng Kanban tương tác, cho phép thực hiện các luồng hành động (Actions) nghiệp vụ:
* **Quy trình tiến độ (Workflow Cột Kanban):** Bảng hiển thị 4 cột trạng thái chuẩn: TO DO (Việc cần làm) -> IN PROGRESS (Đang thực hiện) -> PENDING (Chờ duyệt) -> DONE (Hoàn thành). Nhân viên cập nhật tiến độ bằng thao tác Kéo - Thả (Drag & Drop) thẻ Subtask giữa các cột.
* **Quản lý Chi tiết Subtask (Task Details):** Khi click vào một thẻ Subtask, một khung chi tiết (Modal) sẽ mở ra, cho phép nhân viên thao tác các tính năng sau:
  * **Cập nhật minh chứng:** Chèn File đính kèm (Lưu qua **Google Drive API**) hoặc Link tài liệu báo cáo tiến độ.
  * **Cập nhật checklist:** Đánh dấu tickbox (Dạng checklist) các đầu việc nhỏ đã hoàn thành bên trong Subtask đó.
  * **Gửi duyệt cấp trên:** Bấm nút chuyển trạng thái sang PENDING để kích hoạt thông báo gọi Trưởng phòng vào duyệt.
* **Tính năng Tích hợp Giao tiếp (Micro-Communication):** Nhằm tránh việc luồng thông tin công việc bị trôi trên module Chat, hệ thống tích hợp sẵn tính năng trao đổi ngay bên trong thẻ Task. Các thành viên có thể Comment (Bình luận) thảo luận về task, hoặc dùng React (Thả biểu tượng cảm xúc) để xác nhận thông tin nhanh chóng.
* **Kích hoạt luồng AI:** (Liên kết Module 3). Khi thẻ được chuyển qua cột DONE, hệ thống tại màn hình này sẽ tự động gọi API và bật Mở Form Câu Hỏi để nhân viên điền nội dung vào các câu hỏi, kích hoạt AI sẽ thu thập dữ liệu cơ bản của task, subtask và câu trả lời của nhân viên rồi tự động diễn giải nội dung viết báo cáo đăng lên mạng xã hội.

#### 2.2. Không gian 2: TASKBOARD VIEW (Monitor Board - Chế độ giám sát)
Đây là không gian dùng để theo dõi tiến độ tổng quan. Chế độ này không dùng để kéo thả thao tác, mà tập trung vào việc hiển thị dữ liệu dựa trên vai trò (Role-Based View). Hệ thống cung cấp 2 góc nhìn (View):
* **Góc nhìn 1:** Bảng quản lý theo Task: Cột là Tên Task cha, bên dưới liệt kê các thẻ Subtask con.
* **Góc nhìn 2:** Bảng Kanban tiến độ: Cột là To do, In Progress, Pending, Done.

Tùy vào nhóm quyền truy cập, hệ thống sẽ tự động lọc dữ liệu hiển thị (Data Filtering):
1. **Khi hiển thị với NHÂN VIÊN:**
   * Hệ thống chỉ truy xuất và hiển thị các thẻ Subtask mà nhân viên đó được gắn thẻ (Tag người được giao).
   * Trên thẻ hiển thị các thông tin rút gọn: Title Subtask, Mô tả ngắn, Mức độ ưu tiên và Deadline.
2. **Khi hiển thị với TRƯỞNG PHÒNG:**
   * Cung cấp góc nhìn toàn cảnh của phòng ban. Trưởng phòng nhìn thấy toàn bộ các thẻ Subtask của tất cả nhân viên dưới quyền.
   * **Tính năng giám sát đặc thù:**
     * Trên mỗi thẻ Task/Subtask sẽ có thêm thanh tiến độ (Progress bar - % công việc đã hoàn thành).
     * Hệ thống có một phân vùng (Filter) riêng để phân loại và làm nổi bật những Subtask nhân viên vừa gửi lên (Nằm ở cột Pending), giúp Trưởng phòng dễ dàng tập trung bấm vào xem chi tiết Subtask và thực hiện nghiệp vụ Duyệt/Từ chối.
3. **Khi hiển thị với CEO:**
   * Hệ thống ẩn đi các thẻ Subtask chi tiết rườm rà, thay vào đó hiển thị dưới dạng Dashboard Tổng Hợp (Biểu đồ/Thống kê).
   * CEO có thể nắm bắt tiến độ ở cấp độ vĩ mô toàn doanh nghiệp, sử dụng bộ lọc (Filter) để xem tiến độ hoàn thành theo từng phòng ban hoặc theo từng dự án (Task tổng) cụ thể..

### MODULE 3: TÍNH NĂNG CỐT LÕI - WORKFLOW DUYỆT & TỰ ĐỘNG HÓA TẠO BÀI ĐĂNG BẰNG AI
Đây là luồng nghiệp vụ xương sống của hệ thống, nối liền phân hệ "Quản lý công việc" (Kanban) với phân hệ "Mạng xã hội nội bộ" (Social Core). Tính năng này được chia làm 2 quy trình riêng biệt ứng với cấp độ công việc và vai trò của người dùng.

#### Quy trình 1: Đối với SUBTASK (Cấp độ Nhân viên thực thi)
**Mục tiêu AI:** Paraphrase (Diễn đạt lại) và mở rộng ý (Data-to-Text Generation). Biến những gạch đầu dòng thô sơ, khô khan của kỹ thuật viên thành một bản báo cáo chỉn chu, chuyên nghiệp.
* **Bước 1 - Hoàn thành & Chờ duyệt:** Nhân viên hoàn thành công việc, kéo Subtask sang cột CHỜ DUYỆT (Pending). Trưởng phòng xem xét minh chứng và bấm DUYỆT. Nếu duyệt thành công, trạng thái Subtask chuyển về phía nhân viên chờ thao tác cuối.
* **Bước 2 - Xác nhận Hoàn thành (Trigger AI):** Nhân viên vào lại thẻ Subtask và bắt buộc phải tự tay ấn nút "DONE". Hành động này chính là trigger kích hoạt luồng AI.
* **Bước 3 - Thu thập Input:** Ngay khi ấn DONE, hệ thống sẽ bật lên một Pop-up chứa một Form Câu Hỏi định dạng sẵn (Ví dụ: Bạn đã thực hiện chi tiết những gì? Khó khăn gặp phải? Kết quả đạt được? Có những khó khăn gì?). Nhân viên chỉ cần gõ câu trả lời rất ngắn gọn (dạng gạch đầu dòng) vào form này.
* **Bước 4 - AI Processing (Prompt Engineering):**
  * Khi nhân viên bấm "Tạo báo cáo", giao diện (Frontend) sẽ khóa nút bấm và hiển thị thanh tiến độ (0-100%) hoặc Loading Spinner để ngăn việc click liên tục, đồng thời dùng AJAX gửi dữ liệu câu trả lời ngắn đó xuống Backend (Controller).
  * Backend sẽ đóng gói đoạn text đó vào một System Prompt được thiết kế sẵn.
    > **Ví dụ Prompt:** *"Hãy đóng vai một nhân viên chuyên nghiệp. Dựa vào các từ khóa công việc sau đây, hãy viết lại thành một bài báo cáo tiến độ ngắn gọn, mạch lạc, văn phong lịch sự để đăng lên mạng xã hội công ty: [Dữ liệu nhân viên nhập]"*
  * Backend gọi API (LLaMA/Qwen) truyền Prompt này đi và nhận kết quả về.
* **Bước 5 - Đăng bài:** AI trả về một đoạn văn bản báo cáo hoàn chỉnh. Hệ thống chuyển đoạn văn bản này vào Form Đăng Bài theo mẫu (Preview). Tại đây, nhân viên có thể đọc lại, chỉnh sửa câu chữ, thêm hình ảnh nếu muốn, và ấn Đăng bài. Đặc biệt, nếu đăng xong vẫn phát hiện sai sót, người dùng được quyền thao tác **Chỉnh sửa bài viết**, hệ thống sẽ cập nhật lại tức thì và lưu lại lịch sử chỉnh sửa.

#### Quy trình 2: Đối với TASK TỔNG (Cấp độ Leader / Quản lý)
**Mục tiêu AI:** Multi-Document Summarization (Tổng hợp đa văn bản) & Context Injection (Tiêm ngữ cảnh). AI sẽ đọc hàng loạt báo cáo con để đúc kết thành một bài tổng kết dự án mang tính vĩ mô, truyền cảm hứng.
* **Bước 1 - Kích hoạt quy trình:** Khi toàn bộ các Subtask con trực thuộc một Task đều đã hoàn thành, Trưởng phòng tiến hành bấm nút hoàn thành Task Tổng. Lúc này, giao diện sẽ xuất hiện một nút bấm đặc thù: "Tổng hợp thông tin và tạo bài viết tự động".
* **Bước 2 - Data Retrieval (Truy xuất dữ liệu truyền thống):** Khi Trưởng phòng click vào nút trên, code Backend sẽ tự động chạy một câu truy vấn CSDL. Điểm mấu chốt ở đây là hệ thống CHỈ truy xuất lại nội dung các bài đăng MXH của những Subtask thuộc về chính Task cụ thể đó.
  ```sql
  SELECT post_content FROM Posts WHERE task_id = 'ID_Task_Hiện_Tại'
  ```
  *(Tuyệt đối không lấy lẫn lộn dữ liệu của Task khác).*
* **Bước 3 - Context Construction (Gom ngữ cảnh):** Backend gom nối (concatenate) tất cả các bài báo cáo Subtask vừa lấy được thành một khối văn bản "Ngữ cảnh". 
* **Bước 4 - AI Processing (Tổng hợp thông tin):**
  * Backend đưa khối văn bản ngữ cảnh này vào một System Prompt dành riêng cho Quản lý.
    > **Ví dụ Prompt:** `"Dưới đây là các báo cáo chi tiết từ nhân viên về từng hạng mục của dự án X. Hãy đóng vai một Quản lý dự án, viết một bài đăng tổng kết dự án khoảng 300 chữ. Yêu cầu: Nêu bật kết quả đạt được, biểu dương nỗ lực của team, văn phong truyền cảm hứng và tự hào để đăng lên mạng xã hội nội bộ. Dữ liệu ngữ cảnh: [Khối văn bản đã gom ở Bước 3]"`*
* **Bước 5 - Đăng bài Tổng kết:** AI phân tích khối dữ liệu lớn và trả về một bài viết tổng kết mượt mà. Leader nhận được bài draft này trên giao diện, thực hiện review, chỉnh sửa câu chữ cho phù hợp với ý đồ cá nhân và ấn Đăng bài.

### MODULE 4: HỆ THỐNG THÔNG BÁO (NOTIFICATION)
Giao diện thông báo (chuông) được phân loại bằng 2 tab (bộ lọc) rõ ràng để người dùng không bị nhiễu thông tin:
* **Phân loại 1 - Thông báo về Task:** Các thông báo mang tính chất hành động công việc (Được giao Task mới, Nhắc nhở deadline, Subtask được Duyệt/Bị từ chối,...).
* **Phân loại 2 - Thông báo từ MXH:** Các thông báo mang tính tương tác xã hội (Có thông báo mới từ công ty, ai đó React (thích/thả tim) hoặc Comment vào bài đăng báo cáo của bạn),....

*(Về mặt CSDL, định hướng tối ưu bằng cách tạo 1 dòng thông báo chung cho các bản tin Broadcast và dùng bảng mapping `User_Read_Notification` kết hợp khóa ngoại để quản lý trạng thái hiển thị của từng User. Hệ thống dùng AJAX polling định kỳ lấy JSON).*

### MODULE 5: HỆ THỐNG QUẢN TRỊ & NHÂN SỰ (ADMIN PANEL)
Đóng vai trò là "Phần quản trị mức cơ bản" theo yêu cầu đề bài.
* **Quản lý cơ cấu tổ chức:** Thêm/Sửa/Xóa (CRUD) Phòng ban.
* **Quản lý nhân sự:** Thêm/Sửa/Xóa Nhân viên, cấp tài khoản đăng nhập.
* **Phân quyền (Authorization):** Gán role CEO, Trưởng phòng, Nhân viên để điều khiển việc hiển thị dữ liệu ở các module trên.

---

## PHẦN 3: ĐỀ XUẤT TECH STACK (CÔNG NGHỆ SỬ DỤNG)
Để đáp ứng tuyệt đối yêu cầu: OOP, MVC, Bootstrap, AJAX/Webservice truyền tải JSON, tôi đề xuất bộ stack sau (rất phù hợp cho sinh viên làm đồ án web):

### 1. Frontend (Giao diện người dùng)
* **UI Framework:** Bootstrap 5 (Bắt buộc theo đề) để dựng Layout, Grid, Card, Modal (cho các form đăng bài, chi tiết Task).
* **Cơ chế tương tác:** Vanilla JS hoặc jQuery (Tùy mức độ quen thuộc của nhóm) để bắt sự kiện (Click, Submit).
* **Giao tiếp Server:** Bắt buộc dùng AJAX (Fetch API hoặc jQuery AJAX) gọi xuống backend. Dữ liệu nhận về/gửi đi phải ép định dạng JSON.
* **Thư viện hỗ trợ thêm (Bonus points):**
  * **Sortable.js hoặc jQuery UI:** Làm chức năng Kéo thả (Drag & Drop) cho thẻ Kanban Board.
  * **SweetAlert2:** Làm các thông báo popup đẹp mắt (Thành công/Thất bại).
  * **Toastr:** Hiện thông báo góc màn hình (như Facebook).

### 2. Backend (Xử lý nghiệp vụ & Database)
Tùy vào ngôn ngữ nhóm bạn học, nhưng phổ biến nhất là PHP hoặc Node.js/C#. Dưới đây tôi ví dụ với PHP (rất dễ tìm hosting free):
* **Ngôn ngữ/Kiến trúc:** PHP thuần viết theo mô hình OOP + MVC (Tạo các thư mục Controllers, Models, Views).
  * **Controller:** Tiếp nhận AJAX Request, gọi Model.
  * **Model (OOP):** Viết các Class User, Task, Post chứa các hàm tương tác với Database. Trả kết quả về dạng JSON (sử dụng `json_encode()`) -> Thỏa mãn yêu cầu dùng WebService/AJAX.
* **Database:** MySQL (Hoàn hảo để deploy lên Share Host). Thiết kế các bảng có khóa ngoại chặt chẽ (Users, Departments, Tasks, Subtasks, Posts, Comments, Notifications).
* **AI Integration (Tích hợp AI Text-Generation):**
  * **Mô hình sử dụng:** LLaMA 3 (Meta) / Qwen (Alibaba) - Các mô hình ngôn ngữ lớn (LLM) mã nguồn mở, cho chất lượng văn bản tiếng Việt/tiếng Anh rất tốt.
  * **Cơ chế hoạt động:** Thay vì triển khai mô hình cục bộ (Local deployment) gây tốn tài nguyên phần cứng, hệ thống sử dụng kiến trúc Cloud-based API Inference.
  * **Công cụ/Nền tảng:** Sử dụng dịch vụ API trung gian (như OpenRouter API hoặc Groq Cloud API) để gọi các mô hình LLaMA/Qwen. Các nền tảng này cung cấp Tier miễn phí, phù hợp với quy mô dự án sinh viên.
* **Kỹ thuật giao tiếp (PHP):**
  * Sử dụng thư viện cURL mặc định của PHP (hoặc thư viện Guzzle HTTP) để tạo các HTTP POST Request.
  * Backend PHP sẽ đóng gói System Prompt (chứa yêu cầu đóng vai) và User Input (chứa dữ liệu truy xuất từ Database) thành định dạng JSON Payload.
  * Gửi payload này đến Endpoint API của nhà cung cấp, chờ xử lý và nhận phản hồi (Response) dạng JSON chứa đoạn văn bản báo cáo đã được AI tạo tự động, sau đó trả ngược về Frontend bằng AJAX.

### 3. Quản lý Dự án, Teamwork & Vận hành Môi trường CSDL (Environment Setup)
Để thỏa mãn tuyệt đối yêu cầu của Đề bài là *"Sử dụng hệ quản trị CSDL chạy trên môi trường XAMPP Local"* đồng thời giải quyết bài toán đồng bộ dữ liệu (Data Synchronization) cho nhóm 5 thành viên code cùng lúc, nhóm áp dụng mô hình Mạng Nội Bộ Ảo (Virtual LAN) kết hợp Quản lý Mã nguồn tập trung.

Chi tiết triển khai quy trình làm việc như sau:

#### 3.1. Quản lý Source Code & Tiến độ:
* **Version Control:** Quản lý mã nguồn qua GitHub/GitLab. Áp dụng chiến lược phân nhánh (Branching): Có nhánh `main` (chứa code hoàn thiện), nhánh `dev` (code đang phát triển chung) và các nhánh tính năng (VD: `feature/kanban`, `feature/social`). Có minh chứng lịch sử commit và Pull Request.
* **Quản lý tiến độ:** Sử dụng Microsoft Teams / Trello để giao việc (Ai code Frontend, ai code Backend API, ai làm Database). Mọi thảo luận thiết kế được lưu vết làm minh chứng báo cáo.

#### 3.2. Triển khai Môi trường Cơ sở dữ liệu dùng chung (Shared Local Database):
Thay vì đẩy Database lên Cloud (sai yêu cầu) hoặc mỗi người tự chạy một Database riêng (gây xung đột hệ thống), nhóm sử dụng công cụ Tailscale để tạo một mạng LAN ảo, biến máy tính của một thành viên thành "Local Server" cho cả nhóm. Cụ thể:
* **Thiết lập Mạng ảo (Virtual Network):** 5 thành viên cùng cài đặt Tailscale và tham gia vào chung một mạng (Network). Hệ thống sẽ cấp cho mỗi máy một địa chỉ IP ảo tĩnh (Ví dụ dải `100.x.x.x`). Lúc này, 5 máy tính kết nối với nhau như đang dùng chung một đường truyền Wifi.
* **Cấu hình Máy chủ Cơ sở dữ liệu (Host Machine):** Cử ra một máy tính đóng vai trò là "Máy Chủ" (Máy A). Trên Máy A:
  * Khởi chạy XAMPP (Apache & MySQL).
  * Cấu hình file `my.ini` của MySQL: Chỉnh sửa tham số `bind-address = 0.0.0.0` và mở cổng port `3306` trên Firewall để cho phép MySQL nhận kết nối từ các máy tính khác trong mạng Tailscale.
  * Truy cập phpMyAdmin tạo một User Database cấp quyền truy cập từ xa (Host: `%` - Any host) để các máy khác có thể can thiệp.
* **Kết nối từ các máy thành viên (Client Machines):** 4 thành viên còn lại thao tác code trên máy cá nhân, cấu hình chuỗi kết nối (Connection String) trong file `config.php` trỏ thẳng vào IP ảo của Máy A.
  * *Ví dụ:* 
    ```php
    $conn = new mysqli('100.115.x.x', 'db_user', 'password', 'esn_database');
    ```
* **Hiệu quả đạt được:** Cả 5 người cùng đọc/ghi (Read/Write) trên một Database thực tế (Real-time database). Bất kỳ ai thêm bảng, đổi tên cột hay thêm dữ liệu mẫu, 4 người còn lại đều nhận được kết quả lập tức. Tránh hoàn toàn lỗi conflict Database khi gộp source code.

#### 3.3. Đóng gói và Bàn giao (Deployment):
Dù trong quá trình làm việc nhóm sử dụng mạng ảo, nhưng CSDL bản chất vẫn nằm trên XAMPP. Khi hoàn thiện đồ án:
* Nhóm sẽ xuất (Export) toàn bộ cấu trúc bảng và dữ liệu mẫu ra một file `database_final.sql`.
* File này được đính kèm cùng Source code.
* Khi Giảng viên chấm bài, chỉ cần Import file `.sql` này vào phpMyAdmin trên máy của Thầy/Cô, đổi IP trong file config về lại `localhost` là hệ thống chạy hoàn hảo, tuân thủ đúng 100% quy chế môn học.

> 💡 **Lời khuyên khi triển khai:**
> Với quy mô đồ án môn học, khối lượng công việc của sơ đồ này là rất lớn. Bạn nên ưu tiên theo chiến thuật "Mảnh ghép":
> 1. Làm xong bộ khung Admin/Đăng nhập (Phân quyền).
> 2. Làm xong Mạng xã hội cơ bản (Đăng bài, Newsfeed).
> 3. Làm xong Kanban cơ bản (Kéo thả, Tạo Task).
> 4. Cuối cùng mới ghép luồng AI vào (Tính năng ăn điểm). Phần RAG ở Task cấp cao nếu khó quá có thể làm AI prompt cơ bản (gửi toàn bộ text subtask lên AI kêu nó tóm tắt), không cần cài đặt vector database phức tạp.