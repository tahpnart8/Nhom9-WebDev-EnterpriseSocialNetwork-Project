<?php include __DIR__ . '/../layouts/header.php'; ?>

<style>
.chat-layout { display: flex; gap: 0; height: calc(100vh - 120px); border-radius: 1.5rem; overflow: hidden; border: 1px solid var(--border-soft); background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
.chat-sidebar { width: 320px; border-right: 1px solid var(--border-soft); display: flex; flex-direction: column; background: #fff; }
.chat-sidebar-header { padding: 1.5rem; border-bottom: 1px solid var(--border-soft); }
.chat-sidebar-header h5 { margin: 0; font-weight: 800; letter-spacing: -0.5px; }
.chat-list { flex-grow: 1; overflow-y: auto; padding: 0.5rem; }
.chat-item { display: flex; align-items: center; gap: 14px; padding: 0.85rem 1rem; cursor: pointer; transition: all 0.2s; border-radius: 1rem; margin-bottom: 2px; }
.chat-item:hover { background: #f8fafc; }
.chat-item.active { background: var(--primary-light); }
.chat-avatar { width: 44px; height: 44px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; flex-shrink: 0; overflow: hidden; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.chat-avatar img { width: 100%; height: 100%; object-fit: cover; }
.chat-item-info { flex-grow: 1; overflow: hidden; }
.chat-item-name { font-weight: 700; font-size: 0.95rem; margin: 0; color: #1e293b; }
.chat-item-preview { font-size: 0.8rem; color: #64748b; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.chat-main { flex-grow: 1; display: flex; flex-direction: column; background: #fdfdfd; }
.chat-main-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-soft); display: flex; align-items: center; gap: 14px; background: white; }
.chat-messages { flex-grow: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 0.8rem; }
.msg-bubble { max-width: 70%; padding: 0.8rem 1.2rem; border-radius: 1.2rem; font-size: 0.92rem; line-height: 1.5; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
.msg-mine { background: var(--primary-color); color: white; align-self: flex-end; border-bottom-right-radius: 4px; box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2); }
.msg-other { background: #f1f5f9; color: #334155; align-self: flex-start; border-bottom-left-radius: 4px; }
.msg-time { font-size: 0.7rem; opacity: 0.6; margin-top: 4px; }
.chat-input-bar { padding: 1.2rem 1.5rem; border-top: 1px solid var(--border-soft); display: flex; gap: 12px; align-items: center; background: white; }
.chat-input-bar input { flex-grow: 1; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 100px; padding: 0.75rem 1.5rem; font-size: 0.95rem; outline: none; transition: border 0.2s; }
.chat-input-bar input:focus { border-color: var(--primary-color); background: white; }
.chat-input-bar button { border: none; background: var(--primary-color); color: white; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3); }
.chat-input-bar button:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4); }
.chat-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; flex-grow: 1; color: #94a3b8; background: #f8fafc; }
</style>

<div class="chat-layout">
    <!-- Sidebar: Danh sách hội thoại -->
    <div class="chat-sidebar">
        <div class="chat-sidebar-header d-flex justify-content-between align-items-center">
            <h5>Tin nhắn</h5>
            <div class="dropdown">
                <button class="btn btn-primary rounded-circle shadow-sm" data-bs-toggle="dropdown" style="width:36px;height:36px;padding:0;" title="Bắt đầu chat mới">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2" style="width:280px;max-height:400px;overflow-y:auto;z-index:1060;">
                    <li class="dropdown-header text-uppercase small fw-bold text-muted pb-2 border-bottom mb-2">Chọn đồng nghiệp</li>
                    <?php foreach($allUsers as $u): ?>
                    <?php if($u['id'] == $_SESSION['user_id']) continue; ?>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-3 py-2 rounded-3" href="index.php?action=chat&with=<?php echo $u['id']; ?>">
                            <div class="chat-avatar" style="width:32px;height:32px;font-size:0.75rem;">
                                <?php if(!empty($u['avatar_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($u['avatar_url']); ?>">
                                <?php else: ?>
                                    <?php echo mb_substr(trim($u['full_name']),0,1,'UTF-8'); ?>
                                <?php endif; ?>
                            </div>
                            <span class="fw-medium text-dark"><?php echo htmlspecialchars($u['full_name']); ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="chat-list">
            <?php foreach($conversations as $conv): 
                $isUnread = ($conv['unread_count'] > 0);
                $unreadWeight = $isUnread ? 'fw-bold' : '';
                $nameColor = $isUnread ? 'text-dark' : 'text-secondary';
            ?>
            <a href="index.php?action=chat&conv_id=<?php echo $conv['id']; ?>" class="chat-item text-decoration-none <?php echo ($activeConvId == $conv['id']) ? 'active' : ''; ?>">
                <div class="chat-avatar <?php echo $isUnread ? 'border-primary' : ''; ?>">
                    <?php if(!empty($conv['partner_avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($conv['partner_avatar']); ?>">
                    <?php else: ?>
                        <?php echo mb_substr(trim($conv['partner_name'] ?? '?'),0,1,'UTF-8'); ?>
                    <?php endif; ?>
                </div>
                <div class="chat-item-info">
                    <p class="chat-item-name <?php echo $unreadWeight; ?> text-dark"><?php echo htmlspecialchars($conv['partner_name'] ?? 'Người dùng'); ?></p>
                    <?php if($conv['unread_count'] > 1): ?>
                        <p class="chat-item-preview text-primary fw-bold unread-text"><?php echo $conv['unread_count']; ?> tin nhắn chưa đọc</p>
                    <?php else: ?>
                        <p class="chat-item-preview <?php echo $unreadWeight; ?> text-dark">
                            <?php echo htmlspecialchars($conv['last_message'] ?? 'Bắt đầu trò chuyện...'); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <?php if($conv['last_time']): ?>
                <span class="chat-item-time <?php echo $unreadWeight; ?> <?php echo $isUnread ? 'text-primary' : 'text-muted'; ?>" style="font-size:0.65rem;">
                    <?php echo date('H:i', strtotime($conv['last_time'])); ?>
                </span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            
            <?php if(empty($conversations)): ?>
            <div class="text-center py-5 text-muted opacity-50 mt-4">
                <i class="bi bi-chat-left-dots" style="font-size:3rem;"></i>
                <p class="mt-3 small">Bạn chưa có hội thoại nào</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="chat-main">
        <?php if($activeConvId): ?>
        <div class="chat-main-header shadow-sm position-relative" style="z-index: 5;">
            <?php 
                // Tìm partner info
                $partnerName = 'Trò chuyện';
                $partnerAvatar = null;
                foreach($conversations as $c) { 
                    if($c['id'] == $activeConvId) { 
                        $partnerName = $c['partner_name'] ?? 'Người dùng'; 
                        $partnerAvatar = $c['partner_avatar'] ?? null;
                        break; 
                    } 
                }
                // Nếu mở từ ?with= mà chưa có trong list
                if($withUserId && empty($partnerAvatar)) { 
                    foreach($allUsers as $au) { 
                        if($au['id'] == $withUserId) { 
                            $partnerName = $au['full_name']; 
                            $partnerAvatar = $au['avatar_url'] ?? null;
                            break; 
                        } 
                    } 
                }
            ?>
            <a href="index.php?action=profile&id=<?php echo $withUserId ?: ($conversations[array_search($activeConvId, array_column($conversations, 'id'))]['partner_id'] ?? ''); ?>" class="chat-avatar text-decoration-none">
                <?php if($partnerAvatar): ?>
                    <img src="<?php echo htmlspecialchars($partnerAvatar); ?>">
                <?php else: ?>
                    <?php echo mb_substr(trim($partnerName),0,1,'UTF-8'); ?>
                <?php endif; ?>
            </a>
            <div>
                <a href="index.php?action=profile&id=<?php echo $withUserId ?: ($conversations[array_search($activeConvId, array_column($conversations, 'id'))]['partner_id'] ?? ''); ?>" class="text-decoration-none text-dark">
                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($partnerName); ?></h6>
                </a>
                <div class="d-flex align-items-center gap-1">
                    <span class="bg-success rounded-circle" style="width:8px;height:8px;"></span>
                    <span class="text-muted" style="font-size:0.75rem;">Đang hoạt động</span>
                </div>
            </div>
            <div class="ms-auto">
                <button class="btn btn-light rounded-circle border-0" style="width:40px;height:40px;"><i class="bi bi-telephone text-muted"></i></button>
                <button class="btn btn-light rounded-circle border-0 ms-1" style="width:40px;height:40px;"><i class="bi bi-camera-video text-muted"></i></button>
            </div>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <?php foreach($activeMessages as $msg): ?>
            <div class="msg-bubble <?php echo ($msg['sender_id'] == $_SESSION['user_id']) ? 'msg-mine' : 'msg-other'; ?>">
                <?php echo htmlspecialchars($msg['content']); ?>
                <div class="msg-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($activeMessages)): ?>
            <div class="text-center text-muted my-auto opacity-50">
                <i class="bi bi-chat-quote" style="font-size:3rem;"></i>
                <p class="mt-3">Hãy bắt đầu câu chuyện với <b><?php echo htmlspecialchars($partnerName); ?></b></p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="chat-input-bar shadow-sm">
            <input type="text" id="chatInput" placeholder="Nhập nội dung tin nhắn..." autocomplete="off">
            <button id="btnSendMsg" title="Gửi (Enter)"><i class="bi bi-send-fill"></i></button>
        </div>
        
        <?php else: ?>
        <div class="chat-empty">
            <div class="text-center p-5">
                <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center mx-auto mb-4" style="width:100px;height:100px;">
                    <i class="bi bi-chat-right-dots text-primary opacity-25" style="font-size:3rem;"></i>
                </div>
                <h4 class="fw-bold text-dark">Ứng dụng Tin nhắn</h4>
                <p class="text-muted">Chọn một cuộc hội thoại từ danh sách bên trái hoặc nhấn nút <b>+</b> để bắt đầu trò chuyện với đồng nghiệp.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(function() {
    var convId = <?php echo json_encode($activeConvId); ?>;
    
    // Auto scroll
    var $msgs = $('#chatMessages');
    if ($msgs.length) $msgs.scrollTop($msgs[0].scrollHeight);
    
    // Xử lý click để bỏ in đậm ngay lập tức
    $('.chat-item').on('click', function() {
        var $info = $(this).find('.chat-item-name, .chat-item-preview, .chat-item-time');
        if ($info.hasClass('fw-bold')) {
            $info.removeClass('fw-bold');
            $(this).find('.unread-text').removeClass('fw-bold text-primary').addClass('text-dark');
            
            // Giảm badge chat trên header ngay lập tức
            var $badge = $('#chatBadge');
            if ($badge.length && $badge.is(':visible')) {
                var count = parseInt($badge.text()) - 1;
                if (count <= 0) $badge.hide(); else $badge.text(count);
            }
        }
    });

    function sendMsg() {
        var content = $('#chatInput').val().trim();
        if (!content || !convId) return;
        
        $('#chatInput').val('');
        // Append ngay lập tức (UI ảo)
        $msgs.append('<div class="msg-bubble msg-mine">' + $('<span>').text(content).html() + '<div class="msg-time">Vừa gửi</div></div>');
        $msgs.scrollTop($msgs[0].scrollHeight);
        
        $.post('index.php?action=api_send_message', { conversation_id: convId, content: content });
    }
    
    $('#btnSendMsg').on('click', sendMsg);
    $('#chatInput').on('keypress', function(e) { if(e.which === 13) sendMsg(); });
    
    // Polling mới (5 giây một lần)
    if (convId) {
        setInterval(function() {
            $.getJSON('index.php?action=api_fetch_messages&conv_id=' + convId, function(msgs) {
                // Chỉ render lại nếu số lượng tin nhắn thay đổi (giảm tải DOM)
                var currentCount = $msgs.find('.msg-bubble').length;
                if (msgs.length > currentCount || currentCount == 0) {
                     $msgs.empty();
                     msgs.forEach(function(m) {
                        var cls = (m.sender_id == <?php echo $_SESSION['user_id']; ?>) ? 'msg-mine' : 'msg-other';
                        $msgs.append('<div class="msg-bubble ' + cls + '">' + $('<span>').text(m.content).html() + '<div class="msg-time">' + m.created_at.substr(11,5) + '</div></div>');
                     });
                     $msgs.scrollTop($msgs[0].scrollHeight);
                }
            });
        }, 5000);
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
