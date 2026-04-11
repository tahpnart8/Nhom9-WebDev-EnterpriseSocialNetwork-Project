<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relioo - Mạng xã hội doanh nghiệp</title>
    <?php
        // Tính toán base path động để CSS hoạt động cả trên XAMPP subfolder lẫn Vercel root
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $basePath = ($scriptDir === '/' || $scriptDir === '.') ? '' : $scriptDir;
    ?>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Toastr CSS (Popup thông báo góc màn hình) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/public/css/style.css">
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
        var unread = data.unread_count || 0;

        if (unread > 0) {
            $badge.text(unread).show();
            // Toast nếu có thông báo mới
            if (unread > _lastNotiCount && _lastNotiCount >= 0) {
                var latest = data.items[0];
                if (latest && latest.is_read == 0) toastr.info(latest.content, latest.trigger_name || 'Hệ thống');
            }
        } else {
            $badge.hide();
        }

        // Render tất cả (đã đọc mờ đi)
        if (data.items && data.items.length > 0) {
            var html = '';
            data.items.forEach(function(n) {
                var isRead = parseInt(n.is_read) === 1;
                var opacity = isRead ? 'opacity:0.5;' : '';
                var notiId = n.notification_id || n.id;
                html += '<a href="#" class="d-flex gap-3 p-2 rounded text-decoration-none text-dark noti-item" '
                    + 'style="transition:background 0.15s;' + opacity + '" '
                    + 'data-noti-id="' + notiId + '" data-url="' + (n.target_url || '#') + '" '
                    + 'data-is-read="' + n.is_read + '" '
                    + 'onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'transparent\'">';
                var iconClass = isRead ? 'bg-secondary bg-opacity-10 text-secondary' : 'bg-primary bg-opacity-10 text-primary';
                html += '<div class="rounded-circle ' + iconClass + ' d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;"><i class="bi bi-bell"></i></div>';
                var fontWeight = isRead ? '' : 'fw-semibold';
                html += '<div><p class="mb-0 small ' + fontWeight + '">' + n.content + '</p><span class="text-muted" style="font-size:0.7rem;">' + n.created_at + '</span></div></a>';
            });
            $('#notiList').html(html);
        } else {
            $('#notiList').html('<p class="text-center text-muted py-3 small mb-0">Không có thông báo</p>');
        }
        _lastNotiCount = unread;
    });
}
pollNotifications();
setInterval(pollNotifications, 60000);

// Click 1 thông báo: đánh dấu đã đọc + navigate
$(document).on('click', '.noti-item', function(e) {
    e.preventDefault();
    var $el = $(this);
    var notiId = $el.data('noti-id');
    var url = $el.data('url') || '#';
    var isRead = parseInt($el.data('is-read'));

    if (!isRead) {
        // Mark as read first, then navigate
        $.post('index.php?action=api_mark_one_read', { notification_id: notiId }, function() {
            $el.css('opacity', '0.5').data('is-read', 1);
            var badge = parseInt($('#notiBadge').text()) - 1;
            if (badge <= 0) $('#notiBadge').hide(); else $('#notiBadge').text(badge);
        });
    }
    // Navigate (support deep-link)
    if (url && url !== '#') {
        window.location.href = url;
    }
});

$('#markAllRead').on('click', function() {
    $.post('index.php?action=api_mark_all_read', function() { pollNotifications(); });
});
</script>
