<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relioo - Mạng xã hội doanh nghiệp</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Toastr CSS (Popup thông báo góc màn hình) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/public/css/style.css">
    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body>
    <div id="app-wrapper">
        <!-- Include Sidebar via PHP -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content Wrapper -->
        <main id="main-content">
            <!-- Topbar (Search, Notifications, Chat, Logout) -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($pageTitle ?? 'Tổng quan'); ?></h3>
                
                <div class="d-flex align-items-center gap-2">
                    <div class="position-relative d-none d-md-block">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" class="form-control rounded-pill ps-5 bg-white border" placeholder="Tìm kiếm..." style="width: 250px;">
                    </div>
                    
                    <!-- Nút Chat -->
                    <a href="index.php?action=chat" class="btn btn-light rounded-circle border shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="Tin nhắn">
                        <i class="bi bi-chat-dots text-muted fs-5"></i>
                    </a>
                    
                    <!-- Nút Thông báo + Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light rounded-circle border shadow-sm position-relative d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" data-bs-toggle="dropdown" id="notiBtn">
                            <i class="bi bi-bell text-muted fs-5"></i>
                            <span class="badge bg-danger rounded-pill position-absolute" style="top:-4px;right:-4px;font-size:0.6rem;display:none;" id="notiBadge">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0" style="width:340px;max-height:400px;overflow-y:auto;border-radius:1rem;">
                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                <h6 class="mb-0 fw-bold">Thông báo</h6>
                                <button class="btn btn-sm btn-light border-0 text-primary" id="markAllRead">Đọc hết</button>
                            </div>
                            <div id="notiList" class="p-2">
                                <p class="text-center text-muted py-3 small mb-0">Không có thông báo mới</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="index.php?action=logout" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-1">
                        <i class="bi bi-box-arrow-right"></i> Thoát
                    </a>
                </div>
            </div>

<!-- Notification Polling Script (Global) -->
<script>
var _lastNotiCount = 0;
toastr.options = { positionClass: 'toast-bottom-right', timeOut: 4000, progressBar: true };

function pollNotifications() {
    $.getJSON('index.php?action=api_notifications', function(data) {
        var $badge = $('#notiBadge');
        if (data.count > 0) {
            $badge.text(data.count).show();
            // Nếu có thông báo mới hơn lần trước → Toast
            if (data.count > _lastNotiCount && _lastNotiCount >= 0) {
                var latest = data.items[0];
                if (latest) toastr.info(latest.content, latest.trigger_name || 'Hệ thống');
            }
            // Render danh sách
            var html = '';
            data.items.forEach(function(n) {
                html += '<a href="' + (n.target_url || '#') + '" class="d-flex gap-3 p-2 rounded text-decoration-none text-dark" style="transition:background 0.15s;" onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'transparent\'">';
                html += '<div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;"><i class="bi bi-bell"></i></div>';
                html += '<div><p class="mb-0 small fw-medium">' + n.content + '</p><span class="text-muted" style="font-size:0.7rem;">' + n.created_at + '</span></div></a>';
            });
            $('#notiList').html(html);
        } else {
            $badge.hide();
            $('#notiList').html('<p class="text-center text-muted py-3 small mb-0">Không có thông báo mới</p>');
        }
        _lastNotiCount = data.count;
    });
}
pollNotifications();
// Tăng thời gian polling lên 60 giây (60000ms) để tránh quá tải Vercel Serverless & Supabase Connection
setInterval(pollNotifications, 60000);

$('#markAllRead').on('click', function() {
    $.post('index.php?action=api_mark_all_read', function() { pollNotifications(); });
});
</script>
