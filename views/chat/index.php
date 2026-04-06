<?php include __DIR__ . '/../layouts/header.php'; ?>

<style>
.chat-layout { display: flex; gap: 0; height: calc(100vh - 120px); border-radius: 1rem; overflow: hidden; border: 1px solid var(--border-soft); background: white; }
.chat-sidebar { width: 300px; border-right: 1px solid var(--border-soft); display: flex; flex-direction: column; }
.chat-sidebar-header { padding: 1.2rem; border-bottom: 1px solid var(--border-soft); }
.chat-sidebar-header h6 { margin: 0; font-weight: 700; }
.chat-list { flex-grow: 1; overflow-y: auto; }
.chat-item { display: flex; align-items: center; gap: 12px; padding: 0.85rem 1.2rem; cursor: pointer; transition: background 0.15s; border-bottom: 1px solid #f8f9fa; }
.chat-item:hover, .chat-item.active { background: var(--primary-light); }
.chat-item .chat-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0; }
.chat-item-info { flex-grow: 1; overflow: hidden; }
.chat-item-name { font-weight: 600; font-size: 0.9rem; margin: 0; }
.chat-item-preview { font-size: 0.78rem; color: #94a3b8; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.chat-main { flex-grow: 1; display: flex; flex-direction: column; }
.chat-main-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-soft); display: flex; align-items: center; gap: 12px; }
.chat-messages { flex-grow: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 0.6rem; }
.msg-bubble { max-width: 65%; padding: 0.7rem 1rem; border-radius: 1rem; font-size: 0.9rem; line-height: 1.5; }
.msg-mine { background: var(--primary-color); color: white; align-self: flex-end; border-bottom-right-radius: 4px; }
.msg-other { background: #f1f5f9; color: var(--text-main); align-self: flex-start; border-bottom-left-radius: 4px; }
.msg-time { font-size: 0.65rem; opacity: 0.7; margin-top: 2px; }
.chat-input-bar { padding: 1rem 1.5rem; border-top: 1px solid var(--border-soft); display: flex; gap: 10px; align-items: center; }
.chat-input-bar input { flex-grow: 1; border: none; background: #f1f5f9; border-radius: 100px; padding: 0.7rem 1.2rem; font-size: 0.9rem; outline: none; }
.chat-input-bar button { border: none; background: var(--primary-color); color: white; width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.1s; }
.chat-input-bar button:hover { transform: scale(1.05); }
.chat-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; flex-grow: 1; color: #94a3b8; }
</style>

<div class="chat-layout shadow-sm">
    <!-- Sidebar: Danh sách hội thoại -->
    <div class="chat-sidebar">
        <div class="chat-sidebar-header d-flex justify-content-between align-items-center">
            <h6><i class="bi bi-chat-dots me-2 text-primary"></i>Tin nhắn</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-light border rounded-circle" data-bs-toggle="dropdown" style="width:32px;height:32px;">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="max-height:300px;overflow-y:auto;">
                    <?php foreach($allUsers as $u): ?>
                    <?php if($u['id'] == $_SESSION['user_id']) continue; ?>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="index.php?action=chat&with=<?php echo $u['id']; ?>">
                            <div class="chat-avatar" style="width:28px;height:28px;font-size:0.7rem;"><?php echo mb_substr(trim($u['full_name']),0,1,'UTF-8'); ?></div>
                            <?php echo htmlspecialchars($u['full_name']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="chat-list">
            <?php foreach($conversations as $conv): ?>
            <a href="index.php?action=chat&conv_id=<?php echo $conv['id']; ?>" class="chat-item text-decoration-none <?php echo ($activeConvId == $conv['id']) ? 'active' : ''; ?>">
                <div class="chat-avatar"><?php echo mb_substr(trim($conv['partner_name'] ?? '?'),0,1,'UTF-8'); ?></div>
                <div class="chat-item-info">
                    <p class="chat-item-name"><?php echo htmlspecialchars($conv['partner_name'] ?? 'Người dùng'); ?></p>
                    <p class="chat-item-preview"><?php echo htmlspecialchars($conv['last_message'] ?? 'Bắt đầu trò chuyện...'); ?></p>
                </div>
                <?php if($conv['last_time']): ?>
                <span class="text-muted" style="font-size:0.7rem;"><?php echo date('H:i', strtotime($conv['last_time'])); ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            
            <?php if(empty($conversations)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-chat-square-text" style="font-size:2.5rem;opacity:0.3;"></i>
                <p class="mt-2 small">Chưa có hội thoại nào</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="chat-main">
        <?php if($activeConvId): ?>
        <div class="chat-main-header">
            <div class="chat-avatar"><?php 
                // Tìm tên partner
                $partnerName = 'Chat';
                foreach($conversations as $c) { if($c['id'] == $activeConvId) { $partnerName = $c['partner_name'] ?? 'Chat'; break; } }
                // Nếu mở từ ?with= 
                if($withUserId) { foreach($allUsers as $au) { if($au['id'] == $withUserId) { $partnerName = $au['full_name']; break; } } }
                echo mb_substr(trim($partnerName),0,1,'UTF-8'); 
            ?></div>
            <div>
                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($partnerName); ?></h6>
                <span class="text-muted small">Trực tuyến</span>
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
            <div class="text-center text-muted my-auto">
                <i class="bi bi-emoji-smile" style="font-size:2rem;opacity:0.4;"></i>
                <p class="mt-2 small">Hãy gửi lời chào đầu tiên!</p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="chat-input-bar">
            <input type="text" id="chatInput" placeholder="Nhập tin nhắn..." autocomplete="off">
            <button id="btnSendMsg"><i class="bi bi-send-fill"></i></button>
        </div>
        
        <?php else: ?>
        <div class="chat-empty">
            <i class="bi bi-chat-heart" style="font-size:4rem;opacity:0.2;"></i>
            <h5 class="mt-3 fw-bold" style="opacity:0.4;">Chọn một cuộc hội thoại</h5>
            <p class="small" style="opacity:0.4;">Hoặc bấm nút <b>+</b> để bắt đầu nhắn tin mới.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(function() {
    var convId = <?php echo json_encode($activeConvId); ?>;
    
    // Auto scroll xuống cuối
    var $msgs = $('#chatMessages');
    if ($msgs.length) $msgs.scrollTop($msgs[0].scrollHeight);
    
    // Gửi tin nhắn
    function sendMsg() {
        var content = $('#chatInput').val().trim();
        if (!content || !convId) return;
        
        $('#chatInput').val('');
        // Append ngay lập tức (Optimistic UI)
        $msgs.append('<div class="msg-bubble msg-mine">' + $('<span>').text(content).html() + '<div class="msg-time">Vừa xong</div></div>');
        $msgs.scrollTop($msgs[0].scrollHeight);
        
        $.post('index.php?action=api_send_message', { conversation_id: convId, content: content });
    }
    
    $('#btnSendMsg').on('click', sendMsg);
    $('#chatInput').on('keypress', function(e) { if(e.which === 13) sendMsg(); });
    
    // Polling tin nhắn mới mỗi 5 giây
    if (convId) {
        setInterval(function() {
            $.getJSON('index.php?action=api_fetch_messages&conv_id=' + convId, function(msgs) {
                $msgs.empty();
                msgs.forEach(function(m) {
                    var cls = (m.sender_id == <?php echo $_SESSION['user_id']; ?>) ? 'msg-mine' : 'msg-other';
                    $msgs.append('<div class="msg-bubble ' + cls + '">' + $('<span>').text(m.content).html() + '<div class="msg-time">' + m.created_at.substr(11,5) + '</div></div>');
                });
                $msgs.scrollTop($msgs[0].scrollHeight);
            });
        }, 5000);
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
