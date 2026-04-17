<?php
require_once 'config/database.php';
require_once 'models/Project.php';
require_once 'models/Task.php';
require_once 'models/Department.php';
require_once 'models/Post.php';

class ProjectController {
    private $db;
    private $projectModel;
    private $taskModel;
    private $deptModel;
    private $postModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->projectModel = new Project($this->db);
        $this->taskModel = new Task($this->db);
        $this->deptModel = new Department($this->db);
        $this->postModel = new Post($this->db);
    }

    private function getEnvVar($key) {
        return getenv($key) ?: ($_ENV[$key] ?? '');
    }

    // ========== API: TẠO NHÁP TỔNG KẾT DỰ ÁN (CEO xem trước) ==========
    public function apiGenerateProjectSummary() {
        header('Content-Type: application/json');
        if ($_SESSION['role_id'] != 1) { // Chỉ CEO
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện!']);
            return;
        }

        $projectId = $_POST['project_id'] ?? '';
        $companyId = $_SESSION['company_id'];
        $projectData = $this->projectModel->getById($projectId, $companyId);
        if (!$projectData) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy dự án!']);
            return;
        }

        // Lấy tất cả Task của dự án để làm ngữ cảnh
        $tasks = $this->taskModel->getAll($companyId, $projectId);
        $taskContext = "";
        foreach ($tasks as $t) {
            // Lấy thêm summary AI của từng task nếu có
            $stmt = $this->db->prepare("SELECT ai_generated_content FROM task_reports WHERE task_id = ? AND subtask_id IS NULL ORDER BY id DESC LIMIT 1");
            $stmt->execute([$t['id']]);
            $taskSummary = $stmt->fetchColumn();
            
            $taskContext .= "### Task: " . $t['title'] . " (Phòng: " . $t['dept_name'] . ")\n";
            if ($taskSummary) {
                $taskContext .= "Báo cáo nội bộ: " . $taskSummary . "\n";
            } else {
                $taskContext .= "Mô tả: " . $t['description'] . "\n";
            }
            $taskContext .= "\n";
        }

        $apiKey = $this->getEnvVar('GROQ_API_KEY');
        if (!$apiKey) {
            echo json_encode(['success' => false, 'message' => 'Hệ thống chưa cấu hình GROQ_API_KEY. Vui lòng liên hệ kỹ thuật để sử dụng AI.']);
            return;
        }

        $prompt = "Bạn là CEO của công ty. Bạn chuẩn bị viết một bài đăng tổng kết cực kỳ ấn tượng lên bảng tin nội bộ để chúc mừng việc hoàn thành dự án lớn: '{$projectData['title']}'.
Dữ liệu dự án:
- Mô tả mục tiêu: {$projectData['description']}
- Chi tiết các hạng mục đã hoàn thành:
{$taskContext}

Yêu cầu:
- Viết theo văn phong TỔNG KẾT DỰ ÁN chính thức, trang trọng và truyền cảm hứng cho toàn thể nhân viên.
- Nêu bật thành quả chung và ghi nhận sự đóng góp của các phòng ban tham gia.
- Cấu trúc rõ ràng (nên có các tiêu đề phụ hoặc gạch đầu dòng), mạch lạc.
- Độ dài khoảng 400-600 chữ.
- Chỉ trả về nội dung (raw text), không giải thích.
- Chú ý: Đây là bài đăng bảng tin chung cho mọi người cùng đọc, không phải thư gửi cá nhân.";

        $data = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => 'Bạn là Giám đốc điều hành (CEO). Hãy viết bài tổng kết dự án bằng tiếng Việt chuyên nghiệp.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.8
        ];

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối AI: ' . $error]);
            return;
        }
        curl_close($ch);

        $result = json_decode($response, true);
        if (!isset($result['choices'][0]['message']['content'])) {
            $errMsg = $result['error']['message'] ?? 'Phản hồi từ AI không hợp lệ.';
            echo json_encode(['success' => false, 'message' => 'Lỗi từ AI: ' . $errMsg]);
            return;
        }

        $aiContent = $result['choices'][0]['message']['content'];
        echo json_encode(['success' => true, 'data' => $aiContent]);
    }

    private function generateProjectSummaryAI($projectData, $tasksData) {
        // Hàm này có thể giữ lại làm fallback hoặc xóa nếu không dùng nữa, 
        // nhưng hiện tại ta dùng apiGenerateProjectSummary cho flow mới.
        return "N/A";
    }

    public function createProject() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if ($_SESSION['role_id'] != 1) { // Chỉ CEO
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
            return;
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $dept_ids = isset($_POST['department_ids']) ? explode(',', $_POST['department_ids']) : [];

        if (empty($title) || empty($dept_ids)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đủ thông tin.']);
            return;
        }

        require_once __DIR__ . '/../models/Company.php';
        $companyModel = new Company();
        if (!$companyModel->checkQuota($_SESSION['company_id'], 'projects')) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: Công ty của bạn đã đạt giới hạn số lượng dự án tối đa!']);
            exit;
        }

        $projectId = $this->projectModel->create($title, $description, $_SESSION['user_id'], $dept_ids, $_SESSION['company_id']);
        if ($projectId) {
            echo json_encode(['success' => true, 'message' => 'Tạo dự án thành công.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi tạo dự án.']);
        }
    }

    public function updateProject() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if ($_SESSION['role_id'] != 1) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
            return;
        }

        $id = $_POST['project_id'] ?? '';
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $dept_ids = isset($_POST['department_ids']) ? explode(',', $_POST['department_ids']) : [];

        if (empty($id) || empty($title) || empty($dept_ids)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đủ thông tin.']);
            return;
        }

        $result = $this->projectModel->update($id, $title, $description, $dept_ids, $_SESSION['company_id']);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật dự án thành công.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật.']);
        }
    }

    public function deleteProject() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if ($_SESSION['role_id'] != 1) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
            return;
        }

        $id = $_POST['project_id'] ?? '';
        if ($this->projectModel->delete($id, $_SESSION['company_id'])) {
            echo json_encode(['success' => true, 'message' => 'Xoá dự án thành công.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi xoá dự án.']);
        }
    }

    public function completeProject() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if ($_SESSION['role_id'] != 1) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
            return;
        }

        $projectId = $_POST['project_id'] ?? '';
        $aiContent = $_POST['ai_content'] ?? ''; // Nội dung báo cáo đã chỉnh sửa bởi CEO
        $companyId = $_SESSION['company_id'];

        if (empty($projectId)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu ID dự án.']);
            return;
        }

        if (empty($aiContent)) {
            echo json_encode(['success' => false, 'message' => 'Nội dung báo cáo không được để trống!']);
            return;
        }

        $projectData = $this->projectModel->getById($projectId, $companyId);
        if (!$projectData || $projectData['status'] == 'Completed') {
            echo json_encode(['success' => false, 'message' => 'Dự án không tồn tại hoặc đã hoàn thành.']);
            return;
        }

        // Get tasks to verify
        $tasks = $this->taskModel->getAll($companyId, $projectId);
        $allApproved = true;
        foreach ($tasks as $t) {
            if ($t['approval_status'] !== 'Approved') {
                $allApproved = false;
                break;
            }
        }

        if (!$allApproved && !isset($_POST['force'])) {
            echo json_encode(['success' => false, 'message' => 'Vẫn còn Task chưa duyệt.', 'require_force' => true]);
            return;
        }

        // 1. Update Project Status
        $this->projectModel->updateStatus($projectId, 'Completed', $companyId);

        // 2. Post to Social (channel: public)
        $aiTag = "<div class='mt-3 border-top pt-2'><small class='text-muted'><i class='bi bi-robot me-1'></i> Hỗ trợ bởi Relioo AI</small></div>";
        $postContentHtml = "<div class='ai-post project-summary-post'><h4 class='fw-bold text-success mb-3'>🎉 TỔNG KẾT HOÀN THÀNH DỰ ÁN: " . htmlspecialchars($projectData['title']) . " 🎉</h4>" . nl2br(htmlspecialchars($aiContent)) . $aiTag . "</div>";
        $this->postModel->create($_SESSION['user_id'], null, $postContentHtml, 'public', $companyId);

        echo json_encode(['success' => true, 'message' => 'Dự án đã hoàn thành và bài tổng kết đã được xuất bản!']);
    }

    public function getProjectDetail() {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false]);
            return;
        }
        $project = $this->projectModel->getById($id, $_SESSION['company_id']);
        if ($project) {
            echo json_encode(['success' => true, 'data' => $project]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}
?>
