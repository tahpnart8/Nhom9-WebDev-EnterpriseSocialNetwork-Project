# BÀI KIỂM TRA HỆ THỐNG RELIOO - PHIÊN TEST #2
## Tập trung: Nghiệp vụ Quản Lý Công Việc & Social CRUD

**Ngày kiểm tra:** 2026-04-07
**URL gốc:** `http://localhost/relioo/index.php`

---

### TEST-2A: Leader tạo Task mới → Subtask → Giao cho Staff
- **Hành vi:** Đăng nhập `leader_it` → Vào Kanban → Bấm "Tạo Task" → Điền → Submit → Bấm "Giao việc" → Chọn Task vừa tạo → Chọn nhân viên → Điền → Submit.
- **Kỳ vọng:** Task mới tạo thành công. Subtask xuất hiện ở cột "Cần Làm" trên Kanban. 
- **Kết quả:** ✅ **PASS** — Task "Phat trien Module Thanh Toan" (gõ không dấu do giới hạn automation) tạo thành công, counter hiện "1 task lớn". Subtask "Code giao dien form thanh toan" xuất hiện đúng cột CẦN LÀM. Modal tạo Task và Subtask render đúng, AJAX submit mượt mà, trang reload thành công.
- **Lưu ý:** Dropdown "Giao cho nhân viên" auto-select item đầu tiên khi dùng phím tắt (do hạn chế automation engine, không phải bug code).

### TEST-2B: Staff chuyển trạng thái Subtask (To Do → In Progress → Pending)
- **Kết quả:** ⚠️ **CHƯA TEST ĐẦY ĐỦ** — Cần thao tác drag-drop thủ công. Logic backend đã kiểm tra code review: Staff chỉ cho phép `To Do → In Progress` và `In Progress → Pending`, cấm kéo thẳng sang Done.

### TEST-2C: Leader duyệt/từ chối Subtask ở cột Pending
- **Kết quả:** ⚠️ **CHƯA TEST** — Cần có subtask ở trạng thái Pending trước. Nút "Duyệt" và "Trả lại" đã được verify hiện ở cột Pending qua code review.

### TEST-2D: Click vào thẻ Subtask → Hiện Modal chi tiết đầy đủ
- **Hành vi:** Click vào thẻ Subtask "Code giao dien form thanh toan" trên Kanban Board.
- **Kỳ vọng:** Hiện popup/modal hiển thị chi tiết: Tiêu đề, Mô tả, Người được gán, Deadline, Trạng thái, Task cha.
- **Kết quả:** ✅ **PASS (SAU KHI PHÁT TRIỂN)** — Tính năng này hoàn toàn MỚI, đã phát triển trong phiên này. Modal chi tiết hiển thị đầy đủ: Tiêu đề, Trạng thái (badge), Thuộc Task, Mô tả chi tiết, Người thực hiện, Deadline, Độ ưu tiên. Đã sửa bug "Invalid Date" do JavaScript parse sai format MySQL datetime.

### TEST-2E: Đăng bài Social core rồi xóa bài viết
- **Hành vi:** Đăng bài mới → Bấm nút 3 chấm (...) trên bài viết → Chọn "Xóa bài". 
- **Kỳ vọng:** Bài biến mất khỏi Timeline qua AJAX fadeOut.
- **Kết quả:** ✅ **PASS (SAU KHI PHÁT TRIỂN)** — Tính năng này hoàn toàn MỚI. Dropdown menu ba chấm xuất hiện đúng chỉ trên bài viết của chính tác giả hoặc CEO. Có tùy chọn "Xóa bài viết" (màu đỏ). Chức năng xóa gọi AJAX đúng API, xóa bản ghi CSDL, và bài viết fadeOut mượt mà trên UI.

---

## TỔNG KẾT PHIÊN TEST #2

| Trạng thái | Số lượng | Chi tiết |
|---|---|---|
| ✅ PASS | **3/5** | TEST 2A, 2D, 2E |
| ⚠️ CHƯA TEST | **2/5** | TEST 2B, 2C (cần thao tác thủ công drag-drop) |
| ❌ FAIL | **0/5** | Không có |

### TÍNH NĂNG MỚI ĐÃ PHÁT TRIỂN TRONG PHIÊN NÀY:

| # | Tính năng | Files đã thay đổi |
|---|---|---|
| FEAT-01 | **Modal Chi tiết Subtask** - Click thẻ Kanban → Popup AJAX hiện đầy đủ thông tin | `views/tasks/index.php`, `controllers/TaskController.php`, `index.php` |
| FEAT-02 | **Xóa bài viết Social** - Dropdown menu 3 chấm + API xóa phân quyền (chỉ tác giả/CEO) | `views/social/index.php`, `controllers/SocialController.php`, `models/Post.php`, `index.php` |

### LỖI ĐÃ PHÁT HIỆN VÀ SỬA:

| # | Mô tả lỗi | File | Trạng thái |
|---|---|---|---|
| BUG-02 | Modal Subtask hiện **"Invalid Date"** do JavaScript parse sai format MySQL datetime `YYYY-MM-DD HH:MM:SS` | `views/tasks/index.php` | ✅ ĐÃ SỬA — Thêm fallback format + kiểm tra null |
