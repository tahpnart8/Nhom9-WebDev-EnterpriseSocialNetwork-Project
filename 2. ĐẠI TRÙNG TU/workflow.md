# Quy trình Làm việc (Workflow) cho AI Agent - Dự án "ĐẠI TRÙNG TU"

Tài liệu này định nghĩa quy trình phối hợp giữa người phát triển (User) và AI Agent để đảm bảo dự án được cập nhật một cách hệ thống, có kiểm soát và tránh sai sót trong quá trình tái cấu trúc (refactoring).

## 🔄 Chu trình làm việc 5 bước

Quy trình được thực hiện theo vòng lặp khép kín cho mỗi yêu cầu mới:

### 1. Phân tích Bối cảnh (Analyze Context)
- **Đọc source code hiện tại:** AI Agent đọc file `context-of-sourcecode.md` để hiểu cấu trúc tổng thể và trạng thái hiện tại của hệ thống.
- **Đối chiếu lịch sử:** AI Agent đọc `update-log.md` để nắm bắt các thay đổi đã thực hiện trước đó, tránh xung đột hoặc làm lại các phần đã tối ưu.
- **Tiếp nhận yêu cầu:** User đưa ra yêu cầu mới hoặc mô tả lỗi cần sửa.

### 2. Thiết kế Kế hoạch (Design Plan)
- **Lập kế hoạch chi tiết:** AI Agent phân tích yêu cầu và viết phương án xử lý vào file `update-plan.md`.
- **Nội dung kế hoạch:**
    - Mục tiêu thay đổi.
    - Danh sách các file bị tác động.
    - Các bước logic thực hiện cụ thể.
    - Dự báo các rủi ro hoặc tác động phụ (nếu có).

### 3. Xác nhận Kế hoạch (Plan Approval)
- **User Review:** User kiểm tra file `update-plan.md`.
- **Confirm:** User phản hồi xác nhận kế hoạch (ví dụ: "Proceed", "OK", hoặc yêu cầu chỉnh sửa kế hoạch). 
- **Lưu ý:** AI Agent **không được** tự ý sửa code khi kế hoạch chưa được User thông qua trong file hoặc qua hội thoại.

### 4. Phát triển Source Code (Execution)
- **Thực thi:** AI Agent đọc kế hoạch đã chốt trong `update-plan.md` và tiến hành chỉnh sửa source code.
- **Kiểm soát chất lượng:** Đảm bảo tuân thủ đúng các bước đã đề ra và không làm ảnh hưởng đến các module không liên quan.

### 5. Lưu Nhật ký & Kết thúc (Logging & Cleaning)
- **Cập nhật Log:** Sau khi code xong, AI Agent viết tóm tắt các thay đổi thực tế vào file `update-log.md`.
- **Thông tin cụ thể:** Ngày thực hiện, những gì đã làm, kết quả đạt được và các vấn đề cần lưu ý cho lần sau.
- **Dọn dẹp:** Đánh dấu hoàn thành trong `update-plan.md` nhưng không xóa đi kế hoạch cũ để chuẩn bị cho yêu cầu tiếp theo và nếu nhận yêu cầu mới tiếp tiếp tục viết vào file kế hoạch đó chứ không xóa đi kế hoạch cũ, khi thực hiện chỉ cần thực hiện những kế hoạch vừa chuẩn bị, không thực hiện lại những kế hoạch đã hoàn thành.

---

## 🛠 Nguyên tắc cốt lõi
- **Tuyệt đối tuân thủ trình tự:** Không nhảy bước (ví dụ: code trước khi lập kế hoạch).
- **Minh bạch thông tin:** Mọi quyết định quan trọng đều phải được ghi lại trong `update-plan.md`.
- **Duy trì Context:** Luôn cập nhật `context-of-sourcecode.md` nếu có thay đổi lớn về kiến trúc hệ thống.

---
*Tài liệu này sẽ được tuân thủ nghiêm ngặt trong suốt quá trình phát triển dự án.*
