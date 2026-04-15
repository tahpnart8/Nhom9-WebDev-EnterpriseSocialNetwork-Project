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
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/public/css/style.css">
    <style>
        /* Modern Modal for Post Detail */
        .modal-xl-custom { max-width: 1000px; }
        .post-modal-body { display: flex; height: 85vh; padding: 0; border-radius: 1rem; overflow: hidden; }
        .post-modal-left { flex: 1.2; background: #000; display: flex; align-items: center; justify-content: center; position: relative; }
        .post-modal-left img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .post-modal-right { flex: 0.8; background: #fff; display: flex; flex-direction: column; border-left: 1px solid #eee; overflow: hidden; position: relative; }
        .post-modal-body.no-media .post-modal-left { display: none; }
        .post-modal-body.no-media .post-modal-right { flex: 1; border-left: none; }
        .post-modal-header { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #fff; z-index: 10; }
        .post-modal-scroll { flex: 1; overflow-y: auto; padding: 1.25rem; background: #fff; scroll-behavior: smooth; }
        .post-modal-footer { padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9; background: #fff; margin-top: auto; }
        .comment-actions { display: flex; gap: 12px; margin-top: 4px; font-size: 0.75rem; font-weight: 700; color: #64748b; }
        .comment-actions span { cursor: pointer; transition: color 0.2s; }
        .comment-actions span:hover { color: var(--primary-color); }
        .comment-actions .active-like { color: #dc3545; }
        .comment-highlight { background-color: #fff9c4 !important; transition: background 2s; }
        @media (max-width: 992px) {
            .post-modal-body { flex-direction: column; height: auto; }
            .post-modal-left { height: 300px; }
            .post-modal-right { height: 500px; }
        }
    </style>
    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body>
    <div id="app-wrapper">
        <!-- Include Sidebar via PHP -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Include Right Sidebar via PHP -->
        <?php include 'right_sidebar.php'; ?>
        
        <!-- Main Content Wrapper -->
        <main id="main-content">
            <!-- Topbar (Search, Notifications, Chat, Logout) - Balanced 3-Section Layout -->
            <div class="top-bar-sticky">
                <!-- Left: Page Title / Channel Name -->
                <div class="flex-shrink-0" style="min-width: 220px;">
                    <h3 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($pageTitle ?? 'Tổng quan'); ?></h3>
                </div>
                
                <!-- Middle: Search Bar (Centered) -->
                <?php 
                    $currentAction = $_GET['action'] ?? 'dashboard';
                    $hideSearch = in_array($currentAction, ['dashboard', 'profile']);
                ?>
                <?php if (!$hideSearch): ?>
                <div class="search-wrapper d-none d-md-block">
                    <div class="topbar-search-form">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" placeholder="Tìm kiếm nội dung, dự án..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                        <i class="bi bi-x-circle-fill clear-search-input" style="display: <?php echo isset($_GET['q']) && $_GET['q'] !== '' ? 'block' : 'none'; ?>;"></i>
                    </div>
                    
                    <!-- Search Results / History Dropdown -->
                    <div class="search-history-dropdown shadow-lg">
                        <div class="history-header">
                            <span>TÌM KIẾM GẦN ĐÂY</span>
                            <button id="clearHistoryBtn">Xóa tất cả</button>
                        </div>
                        <div id="historyList">
                            <!-- JS dynamic render -->
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="flex-grow-1"></div> <!-- Spacer when search is hidden -->
                <?php endif; ?>
                
                <!-- Right: Utility Actions -->
                <div class="topbar-utility-actions">
                    <!-- Nút Chat -->
                    <a href="index.php?action=chat" class="btn btn-light rounded-circle border shadow-sm d-flex align-items-center justify-content-center position-relative" style="width: 40px; height: 40px;" title="Tin nhắn">
                        <i class="bi bi-chat-dots text-muted fs-5"></i>
                        <span class="badge bg-primary rounded-pill position-absolute" style="top:-4px;right:-4px;font-size:0.6rem;display:none;" id="chatBadge">0</span>
                    </a>
                    
                    <!-- Nút Thông báo + Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light rounded-circle border shadow-sm position-relative d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" data-bs-toggle="dropdown" id="notiBtn">
                            <i class="bi bi-bell text-muted fs-5"></i>
                            <span class="badge bg-danger rounded-pill position-absolute" style="top:-4px;right:-4px;font-size:0.6rem;display:none;" id="notiBadge">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0 mt-2" style="width:360px;max-height:500px;overflow-y:auto;border-radius:1rem;">
                            <div class="p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-bold">Thông báo</h6>
                                    <button class="btn btn-sm btn-light border-0 text-primary p-0" id="markAllRead">Đọc hết</button>
                                </div>
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary active w-50" id="btnNotiTask" onclick="event.stopPropagation(); switchNotiTab('TASK')">Công việc</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary w-50" id="btnNotiSocial" onclick="event.stopPropagation(); switchNotiTab('SOCIAL')">Mạng xã hội</button>
                                </div>
                            </div>
                            <div id="notiList" class="p-2">
                                <p class="text-center text-muted py-3 small mb-0">Không có thông báo mới</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="index.php?action=logout" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-1 shadow-sm d-flex align-items-center gap-2 border-0 bg-danger bg-opacity-10 text-danger fw-600">
                        <i class="bi bi-box-arrow-right"></i> <span class="d-none d-lg-inline">Thoát</span>
                    </a>
                </div>
            </div>

            <!-- Global Post Detail Modal -->
            <div class="modal fade" id="postDetailModal" tabindex="-1" style="z-index: 2050;">
                <div class="modal-dialog modal-dialog-centered modal-xl modal-xl-custom">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3 bg-white p-2 rounded-circle shadow-sm" data-bs-dismiss="modal" title="Thoát"></button>
                        <div class="post-modal-body" id="postModalContent">
                            <!-- Content loaded via AJAX -->
                        </div>
                    </div>
                </div>
            </div>

<!-- Notification & Chat Polling Script (Global) -->
<script>
var _lastNotiCount = 0;
// Lấy tab cuối cùng từ sessionStorage, mặc định là TASK
var _currentNotiTab = sessionStorage.getItem('lastNotiTab') || 'TASK';
var _allNotiItems = [];
toastr.options = { positionClass: 'toast-bottom-right', timeOut: 4000, progressBar: true };

function switchNotiTab(tab) {
    _currentNotiTab = tab;
    sessionStorage.setItem('lastNotiTab', tab); // Lưu lại lựa chọn
    $('#btnNotiTask').toggleClass('active', tab === 'TASK');
    $('#btnNotiSocial').toggleClass('active', tab === 'SOCIAL');
    renderNotiList();
}

function renderNotiList() {
    var $list = $('#notiList');
    var filtered = _allNotiItems.filter(function(n) {
        var isSocial = n.type && n.type.indexOf('SOCIAL_') === 0;
        return _currentNotiTab === 'SOCIAL' ? isSocial : !isSocial;
    });

    if (filtered.length > 0) {
        var html = '';
        filtered.forEach(function(n) {
            var isRead = parseInt(n.is_read) === 1;
            var opacity = isRead ? 'opacity:0.6;' : '';
            var notiId = n.notification_id || n.id;
            html += '<a href="#" class="d-flex gap-3 p-2 rounded text-decoration-none text-dark noti-item" '
                + 'style="transition:background 0.15s;' + opacity + '" '
                + 'data-noti-id="' + notiId + '" data-url="' + (n.target_url || '#') + '" '
                + 'data-is-read="' + n.is_read + '" '
                + 'onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'transparent\'">';
            
            var isSocial = n.type && n.type.indexOf('SOCIAL_') === 0;
            var iconClass = isRead ? 'bg-secondary bg-opacity-10 text-secondary' : (isSocial ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary');
            
            var icon = 'bi-bell';
            if (n.type === 'SOCIAL_LIKE') icon = 'bi-heart-fill';
            if (n.type === 'SOCIAL_COMMENT') icon = 'bi-chat-left-text-fill';
            
            html += '<div class="rounded-circle ' + iconClass + ' d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;"><i class="bi ' + icon + '"></i></div>';
            var fontWeight = isRead ? '' : 'fw-semibold';
            html += '<div class="flex-grow-1"><p class="mb-0 small ' + fontWeight + '">' + n.content + '</p><span class="text-muted" style="font-size:0.7rem;">' + n.created_at + '</span></div></a>';
        });
        $list.html(html);
    } else {
        $list.html('<p class="text-center text-muted py-4 small mb-0">Không có thông báo ' + (_currentNotiTab === 'SOCIAL' ? 'mạng xã hội' : 'công việc') + '</p>');
    }
}

function pollNotifications() {
    $.getJSON('index.php?action=api_notifications', function(data) {
        var unread = data.unread_count || 0;
        _allNotiItems = data.items || [];
        
        if (unread > 0) {
            $('#notiBadge').text(unread).show();
            if (unread > _lastNotiCount && _lastNotiCount >= 0) {
                // Tìm thông báo CÔNG VIỆC mới nhất trong các mục chưa đọc
                var latestWork = null;
                var newCount = unread - _lastNotiCount;
                for (var i = 0; i < Math.min(newCount, _allNotiItems.length); i++) {
                    var item = _allNotiItems[i];
                    var isSocial = item.type && item.type.indexOf('SOCIAL_') === 0;
                    if (!isSocial) {
                        latestWork = item;
                        break; // Lấy cái mới nhất (đầu danh sách)
                    }
                }
                
                if (latestWork && latestWork.is_read == 0) {
                    toastr.info(latestWork.content, latestWork.trigger_name || 'Hệ thống');
                }
            }
        } else {
            $('#notiBadge').hide();
        }
        
        renderNotiList();
        _lastNotiCount = unread;
    });
}

function pollChatCount() {
    $.getJSON('index.php?action=api_unread_chat_count', function(data) {
        var count = data.unread_conversations || 0;
        if (count > 0) {
            $('#chatBadge').text(count).show();
        } else {
            $('#chatBadge').hide();
        }
    });
}

pollNotifications();
switchNotiTab(_currentNotiTab); // Đồng bộ UI Tab ngay lập tức

// === SMART HEARTBEAT SYSTEM ===
// Thay vì 2 setInterval nặng (30s noti + 15s chat), dùng 1 heartbeat nhẹ mỗi 5 giây
// Heartbeat chỉ trả về 2 số (noti_count, chat_count) → siêu nhẹ (~100 bytes)
// Chỉ gọi full fetch khi count thay đổi
var _hbNotiCount = -1;
var _hbChatCount = -1;
var _hbInterval = 5000; // 5 giây
var _hbIdleInterval = 15000; // 15 giây khi tab ẩn

function heartbeatPoll() {
    $.getJSON('index.php?action=api_heartbeat', function(data) {
        var newNotiCount = data.noti_count || 0;
        var newChatCount = data.chat_count || 0;
        
        // Cập nhật badge chat ngay lập tức
        if (newChatCount > 0) {
            $('#chatBadge').text(newChatCount).show();
        } else {
            $('#chatBadge').hide();
        }
        
        // Chỉ gọi full notification fetch khi count thay đổi
        if (newNotiCount !== _hbNotiCount) {
            pollNotifications();
        } else {
            // Chỉ cập nhật badge nhẹ (không fetch)
            if (newNotiCount > 0) {
                $('#notiBadge').text(newNotiCount).show();
            } else {
                $('#notiBadge').hide();
            }
        }
        
        _hbNotiCount = newNotiCount;
        _hbChatCount = newChatCount;
    });
}

// Adaptive heartbeat: nhanh khi focus, chậm khi tab ẩn
var _hbTimer = null;
function scheduleHeartbeat() {
    if (_hbTimer) clearTimeout(_hbTimer);
    var interval = document.hidden ? _hbIdleInterval : _hbInterval;
    _hbTimer = setTimeout(function() {
        heartbeatPoll();
        scheduleHeartbeat();
    }, interval);
}

document.addEventListener('visibilitychange', function() {
    scheduleHeartbeat(); // Reschedule khi tab thay đổi visibility
});

heartbeatPoll(); // Lần đầu
scheduleHeartbeat();

// Click 1 thông báo
$(document).on('click', '.noti-item', function(e) {
    e.preventDefault();
    var $el = $(this);
    var notiId = $el.data('noti-id');
    var url = $el.data('url') || '#';
    var isRead = parseInt($el.data('is-read'));

    if (!isRead) {
        // Bỏ in đậm ngay lập tức trên UI
        $el.find('p').removeClass('fw-semibold');
        $el.css('opacity', '0.6').data('is-read', 1);

        $.post('index.php?action=api_mark_one_read', { notification_id: notiId }, function() {
            pollNotifications();
        });
    }
    if (url && url !== '#') {
        // Nếu là thông báo bài viết/bình luận, mở Modal thay vì chuyển trang
        var postIdMatch = url.match(/post_id=(\d+)/);
        var commentIdMatch = url.match(/#comment-(\d+)/);
        
        if (postIdMatch) {
            openPostModal(postIdMatch[1], commentIdMatch ? commentIdMatch[1] : null);
            return;
        }

        if (url.indexOf('index.php') === 0) {
            window.location.href = '<?php echo $basePath; ?>/' + url;
        } else {
            window.location.href = url;
        }
    }
});

function openPostModal(postId, highlightCommentId) {
    var $content = $('#postModalContent');
    // Chỉ hiện spinner khi mở lần đầu
    $content.html('<div class="d-flex w-100 align-items-center justify-content-center bg-white" style="height:85vh;"><div class="spinner-border text-primary"></div></div>');
    var modal = new bootstrap.Modal(document.getElementById('postDetailModal'));
    modal.show();
    refreshPostModal(postId, highlightCommentId);
}

function refreshPostModal(postId, highlightCommentId) {
    var $content = $('#postModalContent');
    $.getJSON('index.php?action=api_get_post_details&post_id=' + postId, function(res) {
        if (!res.success) {
            $content.html('<div class="p-5 text-center bg-white w-100"><h5>' + res.message + '</h5></div>');
            return;
        }
        
        var p = res.post;
        var curUid = res.current_user_id;
        var isNoMedia = !p.media_url;
        var mediaSideHtml = isNoMedia ? '' : '<div class="post-modal-left"><img src="' + p.media_url + '"></div>';
        
        function renderActions(comment) {
            var likeCls = comment.is_liked == 1 ? 'active-like' : '';
            var html = '<div class="comment-actions">' +
                '<span class="btn-modal-comment-like ' + likeCls + '" data-id="' + comment.id + '">Thích ' + (comment.like_count || 0) + '</span>' +
                '<span class="btn-modal-comment-reply" data-id="' + comment.id + '" data-name="' + comment.full_name + '">Trả lời</span>';
            if (comment.user_id == curUid) {
                html += '<span class="btn-modal-comment-edit" data-id="' + comment.id + '" data-content="' + comment.content + '">Sửa</span>' +
                        '<span class="btn-modal-comment-delete text-danger" style="opacity:0.7;" data-id="' + comment.id + '">Xóa</span>';
            }
            html += '</div>';
            return html;
        }

        var commentsHtml = '';
        if (res.comments.length === 0) {
            commentsHtml = '<div class="text-center text-muted py-5 small bg-white rounded-3 border-dashed mb-3"><i class="bi bi-chat-dots fs-3 opacity-25 d-block mb-2"></i>Chưa có bình luận nào. Hãy là người đầu tiên!</div>';
        } else {
            res.comments.forEach(function(c) {
                var isHighlighted = highlightCommentId && c.id == highlightCommentId;
                var hClass = isHighlighted ? 'comment-highlight' : '';
                commentsHtml += '<div class="mb-3 p-3 bg-white rounded-3 shadow-sm ' + hClass + '" id="modal-comment-' + c.id + '">' +
                    '<div class="d-flex gap-2">' +
                    '<img src="' + (c.avatar_url || 'https://placehold.co/40x40') + '" class="rounded-circle border" style="width:36px;height:36px;object-fit:cover;">' +
                    '<div class="flex-grow-1"><div class="d-flex justify-content-between align-items-start"><b class="small d-block text-primary">' + c.full_name + '</b><span class="text-muted" style="font-size:0.6rem;">' + c.created_at + '</span></div>' +
                    '<p class="mb-0 small text-dark mt-1">' + c.content + '</p>' +
                    renderActions(c) + '</div>' +
                    '</div>' +
                    '</div>';
                
                if (c.replies && c.replies.length > 0) {
                    c.replies.forEach(function(r) {
                        var isRHighlighted = highlightCommentId && r.id == highlightCommentId;
                        var rhClass = isRHighlighted ? 'comment-highlight' : '';
                        commentsHtml += '<div class="mb-2 ms-5 p-2 bg-white rounded-3 border-start border-3 border-primary shadow-sm ' + rhClass + '" id="modal-comment-' + r.id + '">' +
                        '<div class="d-flex gap-2 opacity-85">' +
                        '<img src="' + (r.avatar_url || 'https://placehold.co/30x30') + '" class="rounded-circle border" style="width:28px;height:28px;object-fit:cover;">' +
                        '<div class="flex-grow-1"><div class="d-flex justify-content-between"><b class="small d-block text-primary" style="font-size:0.7rem;">' + r.full_name + '</b><span class="text-muted" style="font-size:0.55rem;">' + r.created_at + '</span></div>' +
                        '<p class="mb-0 small" style="font-size:0.75rem;">' + r.content + '</p>' +
                        renderActions(r) + '</div>' +
                        '</div></div>';
                    });
                }
            });
        }

        var html = mediaSideHtml +
            '<div class="post-modal-right">' +
                '<div class="post-modal-header">' +
                    '<div class="d-flex align-items-center gap-3">' +
                        '<img src="' + (p.avatar_url || 'https://placehold.co/40x44') + '" class="rounded-circle border" style="width:44px;height:44px;object-fit:cover;">' +
                        '<div><h6 class="mb-0 fw-bold">' + p.full_name + '</h6><small class="text-muted d-flex align-items-center gap-1"><i class="bi bi-clock"></i> ' + p.created_at + '</small></div>' +
                    '</div>' +
                '</div>' +
                '<div class="post-modal-scroll" id="modalCommentContainer">' +
                    '<div class="post-main-content mb-4 pb-4 border-bottom">' +
                        '<div class="text-dark" style="line-height:1.6; font-size: 0.95rem;">' + p.content_html + '</div>' +
                    '</div>' +
                    '<div class="fw-bold small mb-3 text-muted text-uppercase" style="letter-spacing:1px;">Bình luận ' + (p.comment_count || 0) + '</div>' +
                    commentsHtml +
                '</div>' +
                '<div class="post-modal-footer">' +
                    '<div class="d-flex gap-3 mb-3 border-bottom pb-3">' +
                        '<button class="btn btn-sm ' + (p.is_liked == 1 ? 'btn-danger' : 'btn-light') + ' fw-bold px-3 rounded-pill btn-modal-like" data-id="' + p.id + '">' +
                            '<i class="bi bi-heart' + (p.is_liked == 1 ? '-fill' : '') + ' me-1"></i> Tim ' + (p.like_count || 0) + 
                        '</button>' +
                        '<span class="small text-muted d-flex align-items-center"><i class="bi bi-chat-left-text me-1"></i> Bình luận ' + (p.comment_count || 0) + '</span>' +
                    '</div>' +
                    '<div class="d-flex flex-column gap-1">' +
                        '<div id="modalReplyLabel" class="small text-primary fw-bold ms-2 mb-1" style="display:none; font-size:0.75rem;">Đang trả lời: <span id="modalReplyName"></span> <i class="bi bi-x-circle ms-1 cursor-pointer" onclick="cancelModalReply()"></i></div>' +
                        '<input type="hidden" id="modalParentId" value="">' +
                        '<div class="d-flex gap-2">' +
                            '<input type="text" class="form-control form-control-sm rounded-pill border-0 bg-light px-3" id="modalCommentInput" placeholder="Viết bình luận của bạn..." style="font-size: 0.9rem;">' +
                            '<button class="btn btn-sm btn-primary rounded-circle shadow-sm btn-modal-send-comment" data-id="' + p.id + '" style="width:36px;height:36px;padding:0;"><i class="bi bi-send-fill"></i></button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        
        $content.html(html);
        if (isNoMedia) $content.addClass('no-media'); else $content.removeClass('no-media');

        // Gắn lại sự kiện cho các nút mới render
        $content.find('.btn-modal-like').on('click', function() {
            var pid = $(this).data('id');
            $.post('index.php?action=api_toggle_post_reaction', { post_id: pid }, function() {
                refreshPostModal(pid, null);
            });
        });

        $content.find('.btn-modal-comment-like').on('click', function() {
            var cid = $(this).data('id');
            $.post('index.php?action=api_toggle_comment_reaction', { comment_id: cid }, function() {
                refreshPostModal(p.id, null);
            });
        });

        $content.find('.btn-modal-comment-reply').on('click', function() {
            var cid = $(this).data('id'); var name = $(this).data('name');
            $('#modalParentId').val(cid);
            $('#modalReplyName').text(name);
            $('#modalReplyLabel').show();
            $('#modalCommentInput').focus().attr('placeholder', 'Trả lời ' + name + '...');
        });

        $content.find('.btn-modal-comment-edit').on('click', function() {
            var cid = $(this).data('id'); var oldVal = $(this).data('content');
            Swal.fire({
                title: 'Sửa bình luận',
                input: 'textarea',
                inputValue: oldVal,
                showCancelButton: true,
                confirmButtonText: 'Lưu',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $.post('index.php?action=api_edit_comment', { comment_id: cid, content: result.value }, function() {
                        refreshPostModal(p.id, null);
                    });
                }
            });
        });

        $content.find('.btn-modal-comment-delete').on('click', function() {
            var cid = $(this).data('id');
            Swal.fire({
                title: 'Xóa bình luận?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('index.php?action=api_delete_comment', { comment_id: cid }, function() {
                        refreshPostModal(p.id, null);
                    });
                }
            });
        });

        $content.find('.btn-modal-send-comment').on('click', function() {
            var pid = $(this).data('id');
            var content = $('#modalCommentInput').val().trim();
            var parentId = $('#modalParentId').val();
            if (!content) return;
            $.post('index.php?action=api_add_comment', { post_id: pid, content: content, parent_id: parentId }, function() {
                refreshPostModal(pid, null);
            });
        });
        $content.find('#modalCommentInput').on('keypress', function(e) { if(e.which === 13) $content.find('.btn-modal-send-comment').click(); });

        if (highlightCommentId) {
            setTimeout(function() {
                var $mc = $('#modal-comment-' + highlightCommentId);
                if ($mc.length) {
                    var container = document.getElementById('modalCommentContainer');
                    container.scrollTop = $mc.offset().top - $(container).offset().top + container.scrollTop - 20;
                    setTimeout(function() { $mc.removeClass('comment-highlight'); }, 3000);
                }
            }, 500);
        }
    });
}

function cancelModalReply() {
    $('#modalParentId').val('');
    $('#modalReplyLabel').hide();
    $('#modalCommentInput').attr('placeholder', 'Viết bình luận của bạn...');
}

$('#markAllRead').on('click', function() {
    $.post('index.php?action=api_mark_all_read', function() { pollNotifications(); });
});

// ================= GLOBAL SEARCH & HISTORY LOGIC =================
$(document).ready(function() {
    const $searchInput = $('.topbar-search-form input');
    const $historyDropdown = $('.search-history-dropdown');
    const $historyList = $('#historyList');
    let searchDebounceTimer;

    // Cookie Helpers
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            let date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        let nameEQ = name + "=";
        let ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function getCookieName() {
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action') || 'dashboard';
        return 'relioo_history_' + action;
    }

    function getHistory() {
        const ckName = getCookieName();
        const data = getCookie(ckName);
        if (data) {
            try { return JSON.parse(data); } catch (e) { return []; }
        }
        return [];
    }

    function saveHistory(history) {
        const ckName = getCookieName();
        setCookie(ckName, JSON.stringify(history), 30);
    }

    function addToHistory(keyword) {
        if (!keyword) return;
        keyword = keyword.trim();
        if (keyword === "") return;

        let history = getHistory();
        // Explicitly remove existing entry if found to avoid duplicates
        history = history.filter(item => item.toLowerCase() !== keyword.toLowerCase());
        
        // Add new item to front
        history.unshift(keyword);
        
        // Cap history size
        if (history.length > 8) history.pop();
        saveHistory(history);
    }

    function renderHistory() {
        const history = getHistory();
        if (history.length === 0) {
            $historyList.html('<div class="p-4 text-center text-muted small">Chưa có tìm kiếm nào gần đây</div>');
            return;
        }

        let html = '';
        history.forEach((item, index) => {
            html += `<div class="history-item" data-keyword="${item}">
                        <div class="history-item-content">
                            <i class="bi bi-clock-history"></i>
                            <span class="keyword">${item}</span>
                        </div>
                        <i class="bi bi-x remove-history" data-index="${index}" title="Xóa"></i>
                    </div>`;
        });
        $historyList.html(html);
    }

    function renderCombinedResults(users, tasks) {
        let html = '<div class="p-2 border-bottom text-muted x-small fw-bold">ĐỀ XUẤT TÌM KIẾM</div>';
        let found = false;

        if (users && users.length > 0) {
            found = true;
            html += '<div class="px-3 py-2 text-primary x-small fw-bold mt-1 bg-light">ĐỒNG NGHIỆP</div>';
            users.forEach(u => {
                html += `<div class="history-item suggestion-item" onclick="location.href='index.php?action=profile&id=${u.id}'">
                            <div class="keyword-group">
                                <img src="${u.avatar_url || 'public/img/default-avatar.png'}" class="rounded-circle me-2" width="24" height="24" style="object-fit:cover;">
                                <span class="keyword">${u.full_name} <small class="text-muted">(${u.dept_name})</small></span>
                            </div>
                        </div>`;
            });
        }

        if (tasks && tasks.length > 0) {
            found = true;
            html += '<div class="px-3 py-2 text-primary x-small fw-bold mt-1 bg-light">CÔNG VIỆC</div>';
            tasks.forEach(t => {
                html += `<div class="history-item suggestion-item" onclick="executeSearch('${t.title.replace(/'/g, "\\'")}')">
                            <div class="history-item-content">
                                <i class="bi bi-briefcase me-2 text-muted"></i>
                                <span class="keyword">${t.title}</span>
                            </div>
                        </div>`;
            });
        }

        if (!found) {
            html = '<div class="p-4 text-center text-muted small">Không tìm thấy gợi ý nào</div>';
        }

        $historyList.html(html);
    }

    function executeSearch(keyword) {
        keyword = keyword.trim();
        if (!keyword) return;
        addToHistory(keyword);
        
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action') || 'dashboard';

        if (action === 'social') {
            if (typeof window.searchSocialFeed === 'function') {
                window.searchSocialFeed(keyword);
            } else {
                window.location.href = 'index.php?action=social&q=' + encodeURIComponent(keyword);
            }
        } else if (action === 'tasks' || action === 'project_tasks') {
            if (typeof window.highlightTasks === 'function') {
                window.highlightTasks(keyword);
            }
        } else {
            window.location.href = 'index.php?action=social&q=' + encodeURIComponent(keyword);
        }
    }

    // Event Listeners
    $searchInput.on('focus', function() {
        if ($(this).val().trim().length === 0) {
            renderHistory();
        }
        $historyDropdown.fadeIn(200);
    });

    $searchInput.on('input', function() {
        const keyword = $(this).val().trim();
        if (keyword.length > 0) {
            $('.clear-search-input').show();
        } else {
            $('.clear-search-input').hide();
        }

        clearTimeout(searchDebounceTimer);
        if (keyword.length >= 2) {
            searchDebounceTimer = setTimeout(() => {
                $.when(
                    $.ajax({ url: 'index.php?action=api_search_users&q=' + encodeURIComponent(keyword), dataType: 'json' }),
                    $.ajax({ url: 'index.php?action=api_search_tasks&q=' + encodeURIComponent(keyword), dataType: 'json' })
                ).done(function(uRes, tRes) {
                    renderCombinedResults(uRes[0], tRes[0]);
                });
            }, 300);
        } else if (keyword.length === 0) {
            renderHistory();
        }
    });

    $searchInput.on('keypress', function(e) {
        if (e.which === 13) {
            executeSearch($(this).val());
            $historyDropdown.hide();
        }
    });

    $('.clear-search-input').on('click', function(e) {
        e.stopPropagation();
        $searchInput.val('').focus();
        $(this).hide();
        renderHistory();
        
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action') || 'dashboard';
        if (action === 'tasks' && typeof window.clearTaskHighlights === 'function') window.clearTaskHighlights();
        if (action === 'social' && typeof window.clearSocialSearch === 'function') window.clearSocialSearch();
    });

    $(document).on('click', '.history-item', function(e) {
        if ($(e.target).hasClass('remove-history')) return;
        const kw = $(this).data('keyword');
        if (kw) {
            $searchInput.val(kw);
            executeSearch(kw);
            $historyDropdown.hide();
        }
    });

    $(document).on('click', '.remove-history', function(e) {
        e.stopPropagation();
        const index = $(this).data('index');
        let history = getHistory();
        history.splice(index, 1);
        saveHistory(history);
        renderHistory();
    });

    $('#clearHistoryBtn').on('click', function(e) {
        e.stopPropagation();
        saveHistory([]);
        renderHistory();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-wrapper').length) {
            $historyDropdown.fadeOut(200);
            
            // Nếu click ra ngoài và input rỗng, clear highlight (cho task)
            if ($searchInput.val().trim() === '') {
                const urlParams = new URLSearchParams(window.location.search);
                const action = urlParams.get('action') || 'dashboard';
                if (action === 'tasks' && typeof window.clearTaskHighlights === 'function') window.clearTaskHighlights();
            }
        }
    });
});
</script>
