<?php include __DIR__ . '/../layouts/header.php'; ?>

<style>
    /* Messenger Theme */
    :root {
        --ms-blue: #0084ff;
        --ms-bg: #ffffff;
        --ms-gray-light: #f0f2f5;
        --ms-gray-bubble: #e4e6eb;
        --ms-text: #050505;
        --ms-text-muted: #65676B;
    }

    #main-content {
        padding: 0;
    }

    #main-content>.top-bar-sticky {
        margin-bottom: 16px !important;
    }

    #main-content>.top-bar-sticky .search-wrapper {
        display: none !important;
        /* Hide global search in Chat as it has its own search */
    }

    .chat-layout {
        display: flex;
        flex: 1;
        min-height: 0;
        background: #ffffff;
        overflow: hidden;
        margin: 0;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        font-family: system-ui, -apple-system, sans-serif;
    }

    /* Trái */
    .chat-sidebar {
        width: 360px;
        border-right: 1px solid #f0f2f5;
        display: flex;
        flex-direction: column;
        background: #fff;
        flex-shrink: 0;
    }

    .chat-sidebar-header {
        padding: 1.2rem 1rem 0.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-sidebar-header h4 {
        font-weight: 800;
        font-size: 1.5rem;
        margin: 0;
        color: var(--ms-text);
        letter-spacing: -0.5px;
    }

    .btn-ms-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--ms-gray-light);
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        font-size: 1.1rem;
        color: var(--ms-text);
        transition: 0.2s;
        cursor: pointer;
    }

    .btn-ms-circle:hover {
        background: #e4e6eb;
    }

    .search-input-group {
        background: var(--ms-gray-light);
        border-radius: 50px;
        padding: 8px 16px;
        margin: 0 1rem 0.8rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .search-input-group input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 0.95rem;
        flex-grow: 1;
        color: var(--ms-text);
    }

    .search-input-group input::placeholder {
        color: var(--ms-text-muted);
    }

    .chat-filter-tabs {
        display: flex;
        gap: 8px;
        padding: 0 1rem 0.8rem;
    }

    .chat-filter-tabs span {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        color: var(--ms-text-muted);
        transition: 0.2s;
    }

    .chat-filter-tabs span:hover {
        background: var(--ms-gray-light);
    }

    .chat-filter-tabs span.active {
        background: rgba(0, 132, 255, 0.1);
        color: var(--ms-blue);
    }

    .chat-list {
        flex-grow: 1;
        overflow-y: auto;
        padding: 0.2rem 0.5rem;
    }

    .chat-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0.5rem;
        cursor: pointer;
        border-radius: 8px;
        margin-bottom: 2px;
        text-decoration: none;
    }

    .chat-item:hover {
        background: var(--ms-gray-light);
    }

    .chat-item.active {
        background: rgba(0, 132, 255, 0.05);
    }

    .chat-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        position: relative;
        background: var(--ms-gray-light);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: var(--ms-text);
    }

    .chat-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .chat-item.online .chat-avatar::after,
    .cr-avatar-wrapper.online::after {
        content: '';
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 14px;
        height: 14px;
        background: #31a24c;
        border-radius: 50%;
        border: 2px solid #fff;
    }

    .chat-item-info {
        flex-grow: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .chat-item-name {
        font-weight: 500;
        font-size: 0.95rem;
        margin: 0 0 2px;
        color: var(--ms-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-item-name.unread-highlight {
        font-weight: 900 !important;
        color: #000 !important;
    }

    .chat-item-preview {
        font-size: 0.85rem;
        color: var(--ms-text-muted);
        font-weight: 400;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-item-preview.unread-highlight {
        color: #000 !important;
        font-weight: 900 !important;
    }

    /* Nhóm Avatar */
    .avatar-group-composite {
        position: relative;
        width: 56px;
        height: 56px;
        flex-shrink: 0;
    }

    .avatar-group-composite img,
    .avatar-group-composite .avatar-placeholder {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 2px solid white;
        position: absolute;
        object-fit: cover;
        background: var(--ms-gray-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: bold;
        color: var(--ms-text-muted);
    }

    .avatar-group-composite .av-1 {
        top: 0;
        right: 0;
        z-index: 2;
    }

    .avatar-group-composite .av-2 {
        bottom: 0;
        left: 0;
        z-index: 1;
    }

    /* Giữa */
    .chat-main {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
        position: relative;
    }

    .chat-main-header {
        padding: 0.5rem 1rem;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 10;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }

    .header-info-block {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        padding: 6px;
        border-radius: 8px;
        transition: 0.2s;
        text-decoration: none;
    }

    .header-info-block:hover {
        background: var(--ms-gray-light);
    }

    .chat-main-header .btn-ms-circle {
        background: transparent;
        color: var(--ms-blue);
        font-size: 1.4rem;
        padding: 0;
    }

    .chat-main-header .btn-ms-circle:hover {
        background: var(--ms-gray-light);
    }

    .chat-messages {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1.5rem 1.5rem 0.5rem;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    /* Bubble Logic */
    .msg-bubble-wrapper {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        max-width: 65%;
        margin-bottom: 12px;
    }

    .msg-mine-wrapper {
        align-self: flex-end;
        justify-content: flex-end;
    }

    .msg-other-wrapper {
        align-self: flex-start;
        justify-content: flex-start;
    }

    .msg-bubble {
        padding: 8px 12px;
        font-size: 0.95rem;
        line-height: 1.35;
        position: relative;
        word-wrap: break-word;
    }

    .msg-mine {
        background: var(--ms-blue);
        color: white;
        border-radius: 18px;
    }

    .msg-other {
        background: var(--ms-gray-bubble);
        color: var(--ms-text);
        border-radius: 18px;
    }

    /* Dynamic borderRadius classes for PHP/JS */
    .rad-top-right {
        border-top-right-radius: 4px !important;
    }

    .rad-bot-right {
        border-bottom-right-radius: 4px !important;
        margin-bottom: 2px !important;
    }

    .rad-top-left {
        border-top-left-radius: 4px !important;
    }

    .rad-bot-left {
        border-bottom-left-radius: 4px !important;
        margin-bottom: 2px !important;
    }

    .msg-sender-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        visibility: hidden;
    }

    .show-avatar .msg-sender-avatar {
        visibility: visible;
    }

    .msg-time-tooltip {
        font-size: 0.7rem;
        color: var(--ms-text-muted);
        margin-top: 4px;
        display: none;
    }

    .chat-input-bar {
        padding: 1rem;
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }

    .chat-input-utils {
        display: flex;
        gap: 4px;
        padding-bottom: 4px;
    }

    .chat-input-utils button {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: var(--ms-blue);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        cursor: pointer;
        transition: 0.2s;
    }

    .chat-input-utils button:hover {
        background: var(--ms-gray-light);
    }

    .chat-input-wrapper {
        flex-grow: 1;
        background: var(--ms-gray-light);
        border-radius: 20px;
        display: flex;
        align-items: flex-end;
        padding: 2px 8px;
        min-height: 40px;
    }

    .chat-input-wrapper input {
        flex-grow: 1;
        border: none;
        background: transparent;
        padding: 10px 8px;
        font-size: 0.95rem;
        outline: none;
    }

    .chat-input-wrapper .btn-emoji {
        border: none;
        background: transparent;
        color: var(--ms-blue);
        font-size: 1.4rem;
        padding: 6px;
        cursor: pointer;
    }

    #btnSendMsg {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: var(--ms-blue);
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.2s;
        flex-shrink: 0;
    }

    #btnSendMsg:hover {
        background: var(--ms-gray-light);
    }

    /* Phải */
    .chat-right-sidebar {
        width: 340px;
        border-left: 1px solid #e0e0e0;
        display: flex;
        flex-direction: column;
        background: #fff;
        flex-shrink: 0;
        transition: width 0.3s;
        overflow-y: auto;
    }

    .chat-right-sidebar.hidden {
        width: 0;
        border: none;
        overflow: hidden;
    }

    .cr-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.5rem 1rem 1rem;
    }

    .cr-avatar-wrapper {
        position: relative;
        margin-bottom: 0.5rem;
        cursor: pointer;
    }

    .cr-avatar-xxl {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
    }

    .cr-avatar-wrapper .overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        opacity: 0;
        transition: 0.2s;
    }

    .cr-avatar-wrapper:hover .overlay {
        opacity: 1;
    }

    .cr-name {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--ms-text);
        margin: 0;
        text-align: center;
    }

    .cr-status {
        font-size: 0.8rem;
        color: var(--ms-text-muted);
        margin-bottom: 1.2rem;
    }

    .cr-actions {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        margin-bottom: 0.5rem;
    }

    .cr-action-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        border: none;
        background: transparent;
    }

    .cr-action-item .icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--ms-gray-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: var(--ms-text);
        transition: 0.2s;
    }

    .cr-action-item:hover .icon {
        background: #e4e6eb;
    }

    .cr-action-item span {
        font-size: 0.75rem;
        color: var(--ms-text);
        font-weight: 500;
    }

    .cr-section {
        padding: 0.5rem 1rem;
    }

    .cr-menu-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.8rem;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.2s;
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--ms-text);
    }

    .cr-menu-item:hover {
        background: var(--ms-gray-light);
    }

    .cr-menu-item i {
        font-size: 1.2rem;
        margin-right: 12px;
        color: var(--ms-text);
        width: 20px;
        text-align: center;
    }

    .cr-menu-item .content {
        flex-grow: 1;
    }

    .chat-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex-grow: 1;
        color: var(--ms-text-muted);
        background: var(--ms-bg);
    }
</style>

<div class="chat-layout">
    <!-- Cột Trái -->
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <h4>Đoạn chat</h4>
            <div class="d-flex gap-2">
                <button class="btn-ms-circle bg-light d-none"><i class="bi bi-three-dots"></i></button>
                <button class="btn-ms-circle" data-bs-toggle="modal" data-bs-target="#createGroupModal"><i
                        class="bi bi-pencil-square"></i></button>
            </div>
        </div>

        <div class="search-input-group">
            <i class="bi bi-search text-muted"></i>
            <input type="text" id="chatSearchInput" placeholder="Tìm kiếm trên Relioo chat" autocomplete="off">
        </div>

        <div class="chat-filter-tabs">
            <span class="active" data-filter="ALL">Hộp thư</span>
            <span data-filter="UNREAD">Chưa đọc</span>
            <span data-filter="GROUP">Nhóm</span>
        </div>

        <div class="chat-list" id="chatSidebarList">
            <div id="activeConvsArea">
                <?php foreach ($conversations as $conv):
                    $isUnread = ($conv['unread_count'] > 0);
                    $unreadWeight = $isUnread ? 'unread-highlight' : '';
                    $itemType = strtoupper($conv['type']);

                    $displayName = $conv['partner_name'];
                    $displayAvatar = $conv['partner_avatar'];
                    if ($conv['type'] === 'Group') {
                        $displayName = $conv['group_name'] ?: 'Nhóm chưa đặt tên';
                        $displayAvatar = $conv['group_avatar'];
                    }

                    // Giả lập trạng thái Online ngẫu nhiên cho bản Demo (chỉ cho chat cá nhân)
                    $isOnlineStr = ($conv['type'] !== 'Group' && rand(0, 1)) ? 'online' : '';
                    ?>
                    <a href="index.php?action=chat&conv_id=<?php echo $conv['id']; ?>"
                        class="chat-item <?php echo ($activeConvId == $conv['id']) ? 'active' : ''; ?> <?php echo $isOnlineStr; ?>"
                        data-type="<?php echo $itemType; ?>" data-unread="<?php echo $isUnread ? 'true' : 'false'; ?>"
                        data-search-name="<?php echo htmlspecialchars(mb_strtolower($displayName)); ?>">

                        <?php if ($conv['type'] === 'Group' && empty($displayAvatar)): ?>
                            <div class="avatar-group-composite">
                                <?php if (!empty($conv['group_avatar_1'])): ?>
                                    <img src="<?php echo htmlspecialchars($conv['group_avatar_1']); ?>" class="av-1">
                                <?php else: ?>
                                    <div class="avatar-placeholder av-1"><?php echo mb_substr($displayName, 0, 1, 'UTF-8'); ?></div>
                                <?php endif; ?>

                                <?php if (!empty($conv['group_avatar_2'])): ?>
                                    <img src="<?php echo htmlspecialchars($conv['group_avatar_2']); ?>" class="av-2">
                                <?php else: ?>
                                    <div class="avatar-placeholder av-2"><i class="bi bi-people"></i></div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="chat-avatar">
                                <?php if (!empty($displayAvatar)): ?>
                                    <img src="<?php echo htmlspecialchars($displayAvatar); ?>">
                                <?php else: ?>
                                    <?php echo mb_substr(trim($displayName ?? '?'), 0, 1, 'UTF-8'); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="chat-item-info">
                            <p class="chat-item-name <?php echo $unreadWeight; ?>">
                                <?php echo htmlspecialchars($displayName); ?></p>
                            <p class="chat-item-preview <?php echo $unreadWeight; ?>">
                                <?php if ($isUnread): ?>
                                    <?php if ($conv['unread_count'] == 1): ?>
                                        <?php echo htmlspecialchars($conv['last_message'] ?? ''); ?>
                                    <?php else: ?>
                                        <span class="text-primary">Có <?php echo $conv['unread_count']; ?> tin nhắn mới</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php
                                    $txt = $conv['last_message'] ?? 'Bắt đầu trò chuyện...';
                                    echo htmlspecialchars(strpos($txt, '[IMAGE:') === 0 ? 'Đã gửi một ảnh' : $txt);
                                    ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Kết quả tìm kiếm đồng nghiệp mới -->
            <div id="globalSearchArea" style="display:none;">
                <?php foreach ($allUsers as $u): ?>
                    <?php if ($u['id'] == $_SESSION['user_id'])
                        continue; ?>
                    <a href="index.php?action=chat&with=<?php echo $u['id']; ?>" class="chat-item colleague-search-item"
                        data-search-name="<?php echo htmlspecialchars(mb_strtolower($u['full_name'])); ?>">
                        <div class="chat-avatar">
                            <?php if (!empty($u['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($u['avatar_url']); ?>">
                            <?php else: ?>
                                <?php echo mb_substr(trim($u['full_name']), 0, 1, 'UTF-8'); ?>
                            <?php endif; ?>
                        </div>
                        <div class="chat-item-info">
                            <p class="chat-item-name"><?php echo htmlspecialchars($u['full_name']); ?></p>
                            <p class="chat-item-preview">Bắt đầu trò chuyện mới</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <!-- Cột Giữa -->
    <div class="chat-main">
        <?php if ($activeConvId): ?>
            <div class="chat-main-header">
                <?php
                $isGroup = ($activeConv && $activeConv['type'] === 'Group');
                $displayHeaderName = $isGroup ? ($activeConv['name'] ?: 'Nhóm chưa đặt tên') : '';
                $displayHeaderAvatar = $isGroup ? $activeConv['avatar_url'] : null;

                if (!$isGroup) {
                    $partner = null;
                    foreach ($conversations as $c) {
                        if ($c['id'] == $activeConvId) {
                            $partner = $c;
                            break;
                        }
                    }
                    if ($partner) {
                        $displayHeaderName = $partner['partner_name'];
                        $displayHeaderAvatar = $partner['partner_avatar'];
                    } else if ($withUserId) {
                        foreach ($allUsers as $au) {
                            if ($au['id'] == $withUserId) {
                                $displayHeaderName = $au['full_name'];
                                $displayHeaderAvatar = $au['avatar_url'];
                                break;
                            }
                        }
                    }
                }
                ?>
                <div class="header-info-block" id="btnToggleRightSidebar">
                    <div class="chat-avatar" style="width: 40px; height: 40px;">
                        <?php if ($displayHeaderAvatar): ?>
                            <img src="<?php echo htmlspecialchars($displayHeaderAvatar); ?>">
                        <?php else: ?>
                            <?php echo mb_substr(trim($displayHeaderName), 0, 1, 'UTF-8'); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" style="color:var(--ms-text);">
                            <?php echo htmlspecialchars($displayHeaderName); ?></h6>
                        <div style="font-size:0.75rem; color:var(--ms-text-muted);">
                            <?php echo $isGroup ? count($activeGroupMembers) . ' thành viên' : 'Đang hoạt động'; ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-1" style="color: var(--ms-blue);">
                    <button class="btn-ms-circle" title="Bắt đầu gọi thoại"><i class="bi bi-telephone-fill"></i></button>
                    <button class="btn-ms-circle" title="Bắt đầu gọi video"><i class="bi bi-camera-video-fill"></i></button>
                    <button class="btn-ms-circle d-none d-md-flex" id="btnToggleRightSidebar2"
                        title="Thông tin về cuộc trò chuyện"><i class="bi bi-info-circle-fill"></i></button>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php
                foreach ($activeMessages as $i => $msg):
                    $isMine = ($msg['sender_id'] == $_SESSION['user_id']);

                    $nextMsg = $activeMessages[$i + 1] ?? null;
                    $prevMsg = $activeMessages[$i - 1] ?? null;

                    $isConsecutiveNext = ($nextMsg && $nextMsg['sender_id'] == $msg['sender_id']);
                    $isConsecutivePrev = ($prevMsg && $prevMsg['sender_id'] == $msg['sender_id']);

                    // Chỉ hiện avatar ở tin nhắn CUỐI CÙNG của một chuỗi tin liên tiếp
                    $showAvatar = !$isConsecutiveNext;
                    // Hiện tên ở tin nhắn ĐẦU TIÊN
                    $showName = !$isConsecutivePrev;

                    $bubbleClass = $isMine ? 'msg-mine' : 'msg-other';
                    $radClass = '';
                    if ($isMine) {
                        if ($isConsecutivePrev)
                            $radClass .= ' rad-top-right ';
                        if ($isConsecutiveNext)
                            $radClass .= ' rad-bot-right ';
                    } else {
                        if ($isConsecutivePrev)
                            $radClass .= ' rad-top-left ';
                        if ($isConsecutiveNext)
                            $radClass .= ' rad-bot-left ';
                    }
                    ?>
                    <?php if (!$isMine && $isGroup && $showName): ?>
                        <div class="w-100 mb-1"><a href="index.php?action=profile&id=<?php echo $msg['sender_id']; ?>"
                                class="text-muted text-decoration-none"
                                style="font-size:0.75rem; margin-left:36px; font-weight: 500; text-transform: capitalize;"><?php echo htmlspecialchars($msg['sender_name']); ?></a>
                        </div>
                    <?php endif; ?>
                    <div class="msg-bubble-wrapper <?php echo $isMine ? 'msg-mine-wrapper' : 'msg-other-wrapper'; ?> <?php echo $showAvatar ? 'show-avatar' : ''; ?> message-item"
                        data-id="<?php echo $msg['id']; ?>">
                        <?php if (!$isMine && $isGroup): ?>
                            <a href="index.php?action=profile&id=<?php echo $msg['sender_id']; ?>"><img
                                    src="<?php echo $msg['sender_avatar'] ?: 'https://placehold.co/32x32'; ?>"
                                    class="msg-sender-avatar" title="<?php echo htmlspecialchars($msg['sender_name']); ?>"></a>
                        <?php endif; ?>

                        <?php
                        $contentRaw = $msg['content'];
                        $isImageMatch = preg_match('/^\[IMAGE:(.*?)\]$/', $contentRaw, $match);
                        ?>
                        <div class="msg-bubble <?php echo $bubbleClass; ?> <?php echo $radClass; ?> <?php echo $isImageMatch ? 'bg-transparent p-0 shadow-none' : ''; ?>"
                            title="<?php echo date('H:i', strtotime($msg['created_at'])); ?>">
                            <?php
                            if ($isImageMatch) {
                                echo '<a href="' . htmlspecialchars($match[1]) . '" target="_blank"><img src="' . htmlspecialchars($match[1]) . '" style="max-width:200px; max-height:200px; border-radius:12px; object-fit:cover; display:block; border: 1px solid #e0e0e0;"></a>';
                            } else {
                                echo htmlspecialchars($contentRaw);
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="chat-input-bar">
                <div class="chat-input-utils">
                    <input type="file" id="chatImageFile" class="d-none" accept="image/*">
                    <button title="Đính kèm ảnh/file" onclick="$('#chatImageFile').click()"><i
                            class="bi bi-plus-circle-fill"></i></button>
                    <button title="Gửi ảnh GIF"
                        onclick="$('#chatInput').val('XIN CHÀO MỌI NGƯỜI !!!🥳').trigger('input');"><i
                            class="bi bi-filetype-gif"></i></button>
                </div>
                <div class="chat-input-wrapper">
                    <input type="text" id="chatInput" placeholder="Aa" autocomplete="off">
                    <button class="btn-emoji" title="Gửi Emoji cảm xúc"><i class="bi bi-emoji-smile-fill"></i></button>
                </div>
                <button id="btnSendMsg" title="Gửi"><i class="bi bi-send-fill d-none" id="sendIcon"></i><i
                        class="bi bi-hand-thumbs-up-fill" id="likeIcon"></i></button>
            </div>

        <?php else: ?>
            <div class="chat-empty">
                <i class="bi bi-messenger text-primary" style="font-size: 5rem; opacity: 0.1;"></i>
                <h5 class="mt-3 fw-bold text-dark">Hãy chọn một đoạn chat hoặc bắt đầu cuộc trò chuyện mới</h5>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cột Phải -->
    <?php if ($activeConvId): ?>
        <div class="chat-right-sidebar" id="rightSidebar">
            <div class="cr-header">
                <div class="cr-avatar-wrapper <?php echo (!$isGroup && rand(0, 1)) ? 'online' : ''; ?>"
                    id="btnGroupAvatarTrigger">
                    <?php if ($displayHeaderAvatar): ?>
                        <img src="<?php echo htmlspecialchars($displayHeaderAvatar); ?>" class="cr-avatar-xxl"
                            id="previewGroupAvatar">
                    <?php else: ?>
                        <div
                            class="cr-avatar-xxl d-flex align-items-center justify-content-center bg-light text-muted fs-1 fw-bold">
                            <?php echo mb_substr(trim($displayHeaderName), 0, 1, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <?php if ($isGroup): ?>
                        <div class="overlay"><i class="bi bi-camera-fill fs-4"></i></div>
                        <input type="file" id="groupAvatarFile" class="d-none" accept="image/*">
                    <?php endif; ?>
                </div>
                <h5 class="cr-name"><?php echo htmlspecialchars($displayHeaderName); ?></h5>
                <div class="cr-status"><?php echo $isGroup ? 'Nhóm riêng tư' : 'Hoạt động 5 phút trước'; ?></div>

                <div class="cr-actions">
                    <?php if (!$isGroup && isset($partner['partner_id'])): ?>
                        <a href="index.php?action=profile&id=<?php echo $partner['partner_id']; ?>"
                            class="cr-action-item text-decoration-none">
                            <div class="icon"><i class="bi bi-person-circle"></i></div><span class="text-dark">Trang cá
                                nhân</span>
                        </a>
                    <?php endif; ?>
                    <button class="cr-action-item"
                        onclick="Swal.fire('Thành công', 'Đã tắt thông báo đoạn chat này!', 'success')">
                        <div class="icon"><i class="bi bi-bell-slash-fill"></i></div><span>Tắt thông báo</span>
                    </button>
                    <button class="cr-action-item" id="btnSearchConversation">
                        <div class="icon"><i class="bi bi-search"></i></div><span>Tìm kiếm</span>
                    </button>
                </div>
            </div>

            <div class="cr-section">
                <?php if ($isGroup): ?>
                    <div class="cr-menu-item" data-bs-toggle="collapse" data-bs-target="#groupCustomCollapse">
                        <i class="bi bi-pencil-fill"></i>
                        <div class="content">Tùy chỉnh đoạn chat</div><i class="bi bi-chevron-down ms-auto"
                            style="font-size:0.8rem;"></i>
                    </div>
                    <div class="collapse p-2" id="groupCustomCollapse">
                        <input type="text" id="editGroupName" class="form-control form-control-sm mb-2"
                            value="<?php echo htmlspecialchars($activeConv['name']); ?>" placeholder="Đổi tên nhóm">
                        <?php if ($activeConv['created_by'] == $_SESSION['user_id']): ?>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="requireApprovalSwitch" <?php echo ($activeConv['requires_approval'] ? 'checked' : ''); ?>>
                                <label class="form-check-label" style="font-size:0.85rem;">Phê duyệt thành viên mới</label>
                            </div>
                        <?php endif; ?>
                        <button class="btn btn-primary btn-sm w-100 rounded-pill" id="btnSaveGroupSettings">Lưu thay
                            đổi</button>
                    </div>

                    <div class="cr-menu-item mt-2" data-bs-toggle="collapse" data-bs-target="#groupMembersCollapse">
                        <i class="bi bi-people-fill"></i>
                        <div class="content">Thành viên trong đoạn chat</div><i class="bi bi-chevron-down ms-auto"
                            style="font-size:0.8rem;"></i>
                    </div>
                    <div class="collapse p-2" id="groupMembersCollapse">
                        <div class="d-flex mb-2">
                            <button class="btn btn-light btn-sm w-100 rounded-pill text-primary fw-bold"
                                id="btnAddMemberToggle">+ Thêm người</button>
                        </div>
                        <!-- Box Thêm Người -->
                        <div id="addMemberArea" style="display:none;" class="mb-3 p-2 bg-light rounded-3 border">
                            <select id="newMembersSelect" class="form-select form-select-sm mb-2" multiple
                                style="height: 100px;">
                                <?php foreach ($allUsers as $au): ?>
                                    <option value="<?php echo $au['id']; ?>"><?php echo htmlspecialchars($au['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-primary btn-sm w-100 rounded-pill" id="btnDoAddMembers">Mời</button>
                        </div>

                        <?php foreach ($activeGroupMembers as $gm): ?>
                            <div class="d-flex align-items-center gap-2 mb-2 p-1 rounded-3" style="cursor:pointer;"
                                onmouseover="this.style.background='var(--ms-gray-light)'"
                                onmouseout="this.style.background='transparent'">
                                <img src="<?php echo $gm['avatar_url'] ?: 'https://placehold.co/32x32'; ?>" class="rounded-circle"
                                    style="width:32px;height:32px;object-fit:cover;">
                                <div class="small flex-grow-1 fw-bold"><?php echo htmlspecialchars($gm['full_name']); ?>
                                    <?php if ($gm['role'] === 'admin'): ?>
                                        <div class="text-muted fw-normal" style="font-size:0.7rem;">Quản trị viên</div><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($activeConv['created_by'] == $_SESSION['user_id']): ?>
                        <div class="cr-menu-item mt-2" data-bs-toggle="collapse" data-bs-target="#groupRequestsCollapse">
                            <i class="bi bi-person-plus-fill"></i>
                            <div class="content">Yêu cầu tham gia</div><i class="bi bi-chevron-down ms-auto"
                                style="font-size:0.8rem;"></i>
                        </div>
                        <div class="collapse p-2" id="groupRequestsCollapse">
                            <div id="pendingRequestsArea">
                                <p class="text-center text-muted py-2 small">Đang tải...</p>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

                <div class="cr-menu-item mt-2" data-bs-toggle="collapse" data-bs-target="#groupMediaCollapse">
                    <i class="bi bi-images"></i>
                    <div class="content">File & Bộ sưu tập</div><i class="bi bi-chevron-down ms-auto"
                        style="font-size:0.8rem;"></i>
                </div>
                <div class="collapse p-2" id="groupMediaCollapse">
                    <div class="text-center text-muted small p-3 bg-light rounded-3">
                        Tính năng đang được tích hợp...
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Tạo nhóm -->
<div class="modal fade" id="createGroupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Tạo nhóm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Tên nhóm</label>
                    <input type="text" id="groupNameInput" class="form-control rounded-3 bg-light"
                        placeholder="Ví dụ: Hội nhóm vui vẻ">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Chọn thành viên</label>
                    <div class="list-group list-group-flush rounded-3 border overflow-auto" style="max-height: 250px;">
                        <?php foreach ($allUsers as $u): ?>
                            <?php if ($u['id'] == $_SESSION['user_id'])
                                continue; ?>
                            <label
                                class="list-group-item d-flex align-items-center gap-3 py-2 cursor-pointer border-bottom-0">
                                <input class="form-check-input flex-shrink-0 group-member-check" type="checkbox"
                                    value="<?php echo $u['id']; ?>">
                                <img src="<?php echo $u['avatar_url'] ?: 'https://placehold.co/40x40'; ?>"
                                    class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                <span class="small fw-medium"><?php echo htmlspecialchars($u['full_name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold"
                    data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnDoCreateGroup">Tạo
                    ngay</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        window.convId = <?php echo json_encode($activeConvId); ?>;

        function scrollToBottom() {
            var $msgs = $('#chatMessages');
            if ($msgs.length) $msgs.scrollTop($msgs[0].scrollHeight);
        }
        scrollToBottom();

        // Toggle biểu tượng Send/Like
        $(document).on('input', '#chatInput', function () {
            if ($(this).val().trim().length > 0) {
                $('#sendIcon').removeClass('d-none');
                $('#likeIcon').addClass('d-none');
            } else {
                $('#sendIcon').addClass('d-none');
                $('#likeIcon').removeClass('d-none');
            }
        });

        // Bật tắt Sidebar Phải
        $(document).on('click', '#btnToggleRightSidebar, #btnToggleRightSidebar2', function () {
            $('#rightSidebar').toggleClass('hidden');
        });

        // SPA CHAT NAVIGATION
        $(document).on('click', '.chat-item', function (e) {
            var url = $(this).attr('href');
            if (!url || !url.includes('action=chat')) return;
            e.preventDefault();

            $('.chat-item').removeClass('active');
            $(this).addClass('active');

            var $info = $(this).find('.chat-item-name, .chat-item-preview');
            if ($info.hasClass('fw-bold')) {
                $(this).find('.chat-item-preview').removeClass('fw-bold text-primary').text('Đang tải...');
                $(this).attr('data-unread', 'false');
            }

            window.history.pushState({ path: url }, '', url);

            var ajaxUrl = url + (url.includes('?') ? '&' : '?') + 'ajax=spanav';
            $.get(ajaxUrl, function (data) {
                var newMain = $(data).find('.chat-main').html();
                var newRight = $(data).find('#rightSidebar').html();

                $('.chat-main').html(newMain);
                if (newRight) {
                    if ($('#rightSidebar').length === 0) {
                        $('.chat-layout').append('<div class="chat-right-sidebar" id="rightSidebar"></div>');
                    }
                    $('#rightSidebar').html(newRight);
                } else {
                    $('#rightSidebar').addClass('hidden');
                }

                var match = url.match(/conv_id=(\d+)/);
                if (match) {
                    window.convId = parseInt(match[1]);
                } else {
                    var scriptContent = $(data).find('script').text();
                    var convMatch = scriptContent.match(/window\.convId = (\d+|null);/);
                    if (convMatch && convMatch[1] !== 'null') window.convId = parseInt(convMatch[1]);
                }

                if (window.convId) fetchGroupRequests();
                scrollToBottom();
                $('#chatInput').trigger('input');
            });
        });

        window.addEventListener('popstate', function (e) {
            if (e.state && e.state.path) window.location.reload();
        });

        function sendMsg() {
            var content = $('#chatInput').val().trim();
            if (!content && !$('#sendIcon').hasClass('d-none')) return;
            if (!content) content = '👍'; // Gửi Like nếu rỗng
            if (!window.convId) return;

            $('#chatInput').val('').trigger('input');

            var $msgs = $('#chatMessages');
            var html = '<div class="msg-bubble-wrapper msg-mine-wrapper optimistic-msg">'
                + '<div class="msg-bubble msg-mine rad-bot-right rad-top-right">' + $('<span>').text(content).html() + '</div></div>';
            $msgs.append(html);
            scrollToBottom();

            $.post('index.php?action=api_send_message', { conversation_id: window.convId, content: content }, function () {
                pollNewMessages();
            });
        }

        $(document).on('click', '#btnSendMsg', sendMsg);
        $(document).on('keypress', '#chatInput', function (e) { if (e.which === 13) sendMsg(); });

        // Upload ảnh/file
        $(document).on('change', '#chatImageFile', function (e) {
            var file = e.target.files[0];
            if (!file || !window.convId) return;

            var reader = new FileReader();
            reader.onload = function (ex) {
                var img = new Image();
                img.src = ex.target.result;
                img.onload = function () {
                    var canvas = document.createElement('canvas');
                    var MAX_WIDTH = 800;
                    var scale = 1;
                    if (img.width > MAX_WIDTH) { scale = MAX_WIDTH / img.width; }
                    canvas.width = img.width * scale; canvas.height = img.height * scale;
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    var b64 = canvas.toDataURL('image/jpeg', 0.85);

                    var $msgs = $('#chatMessages');
                    var optHtml = '<div class="msg-bubble-wrapper msg-mine-wrapper optimistic-msg">'
                        + '<div class="msg-bubble msg-mine rad-bot-right rad-top-right bg-transparent p-0 shadow-none"><img src="' + b64 + '" style="max-width:200px; max-height:200px; border-radius:12px; object-fit:cover; display:block; opacity: 0.5;"></div></div>';
                    $msgs.append(optHtml);
                    scrollToBottom();

                    $.post('index.php?action=api_send_message', { conversation_id: window.convId, type: 'image', base64: b64 }, function (res) {
                        if (!res.success) alert(res.message || 'Lỗi gửi ảnh');
                        pollNewMessages();
                    });
                    $('#chatImageFile').val('');
                };
            };
            reader.readAsDataURL(file);
        });

        $(document).on('click', '.chat-filter-tabs span', function () {
            var filter = $(this).data('filter');
            $('.chat-filter-tabs span').removeClass('active text-primary').addClass('text-muted');
            $(this).addClass('active text-primary').removeClass('text-muted');

            if (filter === 'ALL') {
                $('#activeConvsArea .chat-item').show();
            } else if (filter === 'UNREAD') {
                $('#activeConvsArea .chat-item').hide();
                $('#activeConvsArea .chat-item[data-unread="true"]').show();
            } else if (filter === 'GROUP') {
                $('#activeConvsArea .chat-item').hide();
                $('#activeConvsArea .chat-item[data-type="GROUP"]').show();
            }
        });

        $(document).on('input', '#chatSearchInput', function () {
            var q = $(this).val().toLowerCase().trim();
            if (q === '') {
                $('#activeConvsArea .chat-item').show();
                $('#globalSearchArea').hide();
                $('.chat-filter-tabs').show();
            } else {
                $('.chat-filter-tabs').hide();
                $('#globalSearchArea').show();
                $('#activeConvsArea .chat-item').each(function () {
                    var name = $(this).data('search-name') || '';
                    $(this).toggle(name.indexOf(q) > -1);
                });
                $('.colleague-search-item').each(function () {
                    var name = $(this).data('search-name') || '';
                    $(this).toggle(name.indexOf(q) > -1);
                });
            }
        });

        window.avatarBase64 = null;
        $(document).on('click', '#btnGroupAvatarTrigger', function (e) {
            if (e.target.id !== 'groupAvatarFile' && $('#groupAvatarFile').length) $('#groupAvatarFile').click();
        });
        $(document).on('change', '#groupAvatarFile', function (e) {
            var file = e.target.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (ex) {
                var img = new Image();
                img.src = ex.target.result;
                img.onload = function () {
                    var canvas = document.createElement('canvas');
                    var MAX_WIDTH = 400; // Nén ảnh nhỏ lại cho Avatar Group
                    var scale = 1;
                    if (img.width > MAX_WIDTH) { scale = MAX_WIDTH / img.width; }
                    canvas.width = img.width * scale; canvas.height = img.height * scale;
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    var compressedBase64 = canvas.toDataURL('image/jpeg', 0.8);

                    $('#previewGroupAvatar').attr('src', compressedBase64);
                    window.avatarBase64 = compressedBase64;
                    var name = $('#editGroupName').val();
                    var approval = $('#requireApprovalSwitch').length ? ($('#requireApprovalSwitch').is(':checked') ? 1 : 0) : 0;
                    $.post('index.php?action=api_update_group_settings', { conversation_id: window.convId, name: name, requires_approval: approval, avatar_base64: window.avatarBase64 }, function () { location.reload(); });
                };
            };
            reader.readAsDataURL(file);
        });

        $(document).on('click', '#btnSearchConversation', function (e) {
            e.stopPropagation();
            var query = prompt("Nhập từ khoá tìm kiếm tin nhắn trong đoạn chat này:");
            if (!query) return;
            query = query.toLowerCase();
            var count = 0;
            var firstMatch = null;
            $('.msg-bubble').css('background', '').css('color', '');
            $('.msg-bubble').each(function () {
                var txt = $(this).text().toLowerCase();
                if (txt.includes(query) && txt.trim() !== '') {
                    $(this).css('background', '#ffff99').css('color', '#000');
                    if (!firstMatch) firstMatch = $(this);
                    count++;
                }
            });
            if (count === 0) Swal.fire('Thông báo', 'Không tìm thấy kết quả nào!', 'info');
            else {
                if (firstMatch) {
                    var container = document.getElementById('chatMessages');
                    var scrollPos = firstMatch.offset().top - $(container).offset().top + container.scrollTop - 50;
                    $(container).animate({ scrollTop: scrollPos }, 300);
                }
                toastr.success('Đã chọn bôi vàng ' + count + ' tin nhắn khớp (Click ngoài màn hình để tắt)');
                setTimeout(function () {
                    $(document).one('click', function () {
                        $('.msg-bubble').css('background', '').css('color', '');
                    });
                }, 100);
            }
        });

        $(document).on('click', '#btnDoCreateGroup', function () {
            var name = $('#groupNameInput').val().trim();
            var members = [];
            $('.group-member-check:checked').each(function () { members.push($(this).val()); });
            if (!name) return Swal.fire('Lỗi', 'Vui lòng nhập tên nhóm', 'error');
            $.post('index.php?action=api_create_group', { name: name, members: members }, function (res) {
                if (res.success) window.location.href = 'index.php?action=chat&conv_id=' + res.conv_id;
            });
        });

        function fetchGroupRequests() {
            if (!window.convId || $('#pendingRequestsArea').length === 0) return;
            $.getJSON('index.php?action=api_get_group_info&conv_id=' + window.convId, function (res) {
                var $area = $('#pendingRequestsArea');
                if (res.requests.length === 0) {
                    $area.html('<p class="text-center text-muted py-2 small">Không có yêu cầu chờ duyệt</p>');
                } else {
                    var html = '';
                    res.requests.forEach(function (r) {
                        html += '<div class="d-flex align-items-center gap-2 p-1 border-bottom">' +
                            '<img src="' + (r.user_avatar || 'https://placehold.co/32x32') + '" class="rounded-circle" style="width:28px;height:28px;object-fit:cover;">' +
                            '<div class="flex-grow-1"><b class="small d-block">' + r.user_name + '</b></div>' +
                            '<button class="btn btn-success btn-sm rounded-circle p-1 btn-approve" data-id="' + r.id + '"><i class="bi bi-check-lg lh-1"></i></button>' +
                            '<button class="btn btn-danger btn-sm rounded-circle p-1 btn-reject" data-id="' + r.id + '"><i class="bi bi-x-lg lh-1"></i></button></div>';
                    });
                    $area.html(html);
                }
            });
        }
        fetchGroupRequests();

        $(document).on('click', '.btn-approve, .btn-reject', function () {
            var rid = $(this).data('id');
            var status = $(this).hasClass('btn-approve') ? 'approved' : 'rejected';
            $.post('index.php?action=api_handle_membership_request', { request_id: rid, status: status }, function () { fetchGroupRequests(); });
        });

        $(document).on('click', '#btnAddMemberToggle', function () { $('#addMemberArea').slideToggle(); });
        $(document).on('click', '#btnDoAddMembers', function () {
            var members = $('#newMembersSelect').val();
            if (!members.length) return;
            $.post('index.php?action=api_manage_members', { conversation_id: window.convId, members: members }, function () { location.reload(); });
        });

        $(document).on('click', '#btnSaveGroupSettings', function () {
            var name = $('#editGroupName').val();
            var approval = $('#requireApprovalSwitch').length ? ($('#requireApprovalSwitch').is(':checked') ? 1 : 0) : 0;
            $.post('index.php?action=api_update_group_settings', { conversation_id: window.convId, name: name, requires_approval: approval, avatar_base64: window.avatarBase64 }, function () { location.reload(); });
        });

        // === DELTA POLLING: Chỉ lấy tin nhắn mới hơn lastMessageId ===
        var lastMessageId = 0;
        // Khởi tạo lastMessageId từ tin nhắn cuối cùng đã render trên trang
        $('.message-item').not('.optimistic-msg').each(function () {
            var tid = parseInt($(this).data('id'));
            if (!isNaN(tid) && tid > lastMessageId) lastMessageId = tid;
        });

        function pollNewMessages() {
            if (!window.convId) return;
            $.getJSON('index.php?action=api_fetch_new_messages&conv_id=' + window.convId + '&since_id=' + lastMessageId, function (msgs) {
                var $msgsElem = $('#chatMessages');
                if (!$msgsElem.length || !msgs.length) return;

                // Xóa optimistic messages khi có tin thật từ server
                $('.optimistic-msg').remove();

                var hasNew = false;
                msgs.forEach(function (m) {
                    hasNew = true;

                    var isMine = (m.sender_id == <?php echo $_SESSION['user_id']; ?>);
                    var isGrp = <?php echo isset($activeConv) && $activeConv['type'] === 'Group' ? 'true' : 'false'; ?>;

                    var html = '';
                    var contentEscaped = '';
                    var imgClass = '';
                    var imgMatch = m.content.match(/^\[IMAGE:(.*?)\]$/);
                    if (imgMatch) {
                        imgClass = ' bg-transparent p-0 shadow-none ';
                        var safeUrl = $('<span>').text(imgMatch[1]).html();
                        contentEscaped = '<a href="' + safeUrl + '" target="_blank"><img src="' + safeUrl + '" style="max-width:200px; max-height:200px; border-radius:12px; object-fit:cover; display:block; border: 1px solid #e0e0e0;"></a>';
                    } else {
                        contentEscaped = $('<span>').text(m.content).html();
                    }

                    if (isGrp && !isMine) {
                        var avatarUrl = m.sender_avatar ? m.sender_avatar : 'https://placehold.co/32x32';
                        var nameHtml = '<div class="w-100 mb-1"><a href="index.php?action=profile&id=' + m.sender_id + '" class="text-muted text-decoration-none" style="font-size:0.75rem; margin-left:36px; font-weight: 500; text-transform: capitalize;">' + $('<span>').text(m.sender_name).html() + '</a></div>';

                        html = nameHtml + `
                            <div class="msg-bubble-wrapper msg-other-wrapper show-avatar message-item" data-id="${m.id}">
                                <a href="index.php?action=profile&id=${m.sender_id}"><img src="${avatarUrl}" class="msg-sender-avatar" title="${$('<span>').text(m.sender_name).html()}"></a>
                                <div class="msg-bubble msg-other rad-bot-left rad-top-left ${imgClass}" title="${m.created_at.substr(11, 5)}">${contentEscaped}</div>
                            </div>
                        `;
                    } else {
                        var wrapCls = isMine ? 'msg-mine-wrapper' : 'msg-other-wrapper';
                        var bubCls = isMine ? 'msg-mine rad-top-right rad-bot-right' : 'msg-other rad-top-left rad-bot-left';
                        html = `
                            <div class="msg-bubble-wrapper ${wrapCls} message-item" data-id="${m.id}">
                                <div class="msg-bubble ${bubCls} ${imgClass}" title="${m.created_at.substr(11, 5)}">${contentEscaped}</div>
                            </div>
                        `;
                    }
                    $msgsElem.append(html);
                    // Cập nhật lastMessageId cho delta poll tiếp theo
                    var mid = parseInt(m.id);
                    if (mid > lastMessageId) lastMessageId = mid;
                });

                if (hasNew) scrollToBottom();
            });
        }

        // === SMART ADAPTIVE POLLING (Delta Fetching) ===
        var _pollTimer = null;
        var _activePollInterval = 1000;  // 1 giây khi đang chat (tab focus)
        var _idlePollInterval = 5000;    // 5 giây khi tab ẩn

        function getCurrentPollInterval() {
            return document.hidden ? _idlePollInterval : _activePollInterval;
        }

        function schedulePoll() {
            if (_pollTimer) clearTimeout(_pollTimer);
            _pollTimer = setTimeout(function () {
                pollNewMessages();
                schedulePoll(); // Lặp adaptive
            }, getCurrentPollInterval());
        }

        // Khi tab focus/blur → tự động thay đổi tốc độ
        document.addEventListener('visibilitychange', function () {
            schedulePoll(); // Reschedule ngay khi visibility thay đổi
        });

        // Bắt đầu polling
        if (window.convId) schedulePoll();
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>