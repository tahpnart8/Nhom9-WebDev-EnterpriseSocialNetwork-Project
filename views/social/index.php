<?php include __DIR__ . '/../layouts/header.php'; ?>

<style>
    .post-actions .btn { transition: all 0.2s ease; }
    .post-actions .btn.active { color: #ef4444 !important; }
    .post-actions .btn.active i::before { content: "\f415"; }
    .comment-section { background-color: #f8fafc; border-top: 1px solid #e2e8f0; }
    .comment-item { margin-bottom: 1rem; }
    .comment-avatar { width: 32px; height: 32px; font-size: 12px; }
    .comment-bubble { background-color: white; padding: 0.6rem 1rem; border-radius: 1rem; border: 1px solid #e2e8f0; position: relative; }
    .comment-actions { font-size: 0.75rem; margin-top: 0.25rem; padding-left: 0.5rem; }
    .comment-actions a { color: #64748b; text-decoration: none; font-weight: 600; margin-right: 1rem; }
    .comment-actions a:hover { color: #3b82f6; }
    .comment-actions a.active { color: #ef4444; }
    .reply-list { margin-left: 3rem; margin-top: 0.5rem; border-left: 2px solid #e2e8f0; padding-left: 1rem; }
    .liker-list-item:hover { background-color: #f8fafc; }
    .cursor-pointer { cursor: pointer; }
    .cursor-pointer:hover { text-decoration: underline; }
</style>

<div class="row g-4 position-relative">
    <div class="col-lg-8" style="padding-bottom: 50px;">
        <!-- Tabs Chuyển Kênh -->
        <ul class="nav nav-pills mb-4 gap-2 bg-white p-2 rounded shadow-sm border">
            <li class="nav-item">
                <a class="nav-link fw-medium px-4 <?php echo $channel === 'public' ? 'active' : 'text-dark'; ?>" href="index.php?action=social&channel=public">
                    🌍 Kênh Công Khai
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-medium px-4 <?php echo $channel === 'department' ? 'active' : 'text-dark'; ?>" href="index.php?action=social&channel=department">
                    🏢 Kênh Phòng Ban
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-medium px-4 <?php echo $channel === 'announcement' ? 'active' : 'text-dark'; ?>" href="index.php?action=social&channel=announcement">
                    📢 Thông Báo
                </a>
            </li>
        </ul>

        <?php if ($channel === 'department' && ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 4)): ?>
        <div class="mb-4">
            <form id="deptFilterForm" action="index.php" method="GET" class="d-flex align-items-center gap-2">
                <input type="hidden" name="action" value="social">
                <input type="hidden" name="channel" value="department">
                <label class="fw-bold text-muted small mb-0">Lọc phòng ban:</label>
                <select name="dept_id" class="form-select form-select-sm border shadow-sm rounded w-auto" onchange="document.getElementById('deptFilterForm').submit();">
                    <option value="">Tất cả phòng ban</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo $dept_id_filter == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['dept_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php endif; ?>

        <?php 
        $canPost = false;
        $postVisibility = 'Public';
        $postPlaceholder = "Chia sẻ một ý tưởng...";
        
        if ($channel === 'public') {
            $canPost = true;
            $postVisibility = 'Public';
        } else if ($channel === 'department') {
            if ($_SESSION['role_id'] != 1) { // Not CEO
                $canPost = true;
                $postVisibility = 'Department';
                $postPlaceholder = "Thảo luận nội bộ phòng ban...";
            }
        } else if ($channel === 'announcement') {
            if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 4) { // CEO or Admin
                $canPost = true;
                $postVisibility = 'Announcement';
                $postPlaceholder = "Ban hành thông báo...";
            }
        }
        ?>
        
        <?php if ($canPost): ?>
        <!-- Khung Đăng Bài -->
        <div class="relioo-card p-4 mb-4 border-top border-3 border-primary shadow-sm" style="background: linear-gradient(180deg, #fdfdff 0%, #ffffff 100%);">
            <form id="createPostForm" enctype="multipart/form-data">
                <input type="hidden" name="visibility" value="<?php echo $postVisibility; ?>">
                <div class="d-flex gap-3 mb-3">
                    <div class="avatar-circle shadow-sm flex-shrink-0" style="width: 44px; height: 44px; font-size: 14px;">
                        <?php if(!empty($_SESSION['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['avatar_url']); ?>" class="w-100 h-100 rounded-circle" style="object-fit:cover">
                        <?php else: ?>
                            <?php echo mb_substr(trim($_SESSION['full_name'] ?? 'User'), 0, 1, 'UTF-8'); ?>
                        <?php endif; ?>
                    </div>
                    <textarea class="form-control bg-light border-0 px-3 py-3 rounded-3" id="postContent" name="content" rows="3" placeholder="<?php echo $postPlaceholder; ?>" required style="resize: none;"></textarea>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="d-flex gap-2">
                        <label class="btn btn-sm btn-light text-primary border rounded-pill px-3 shadow-sm" style="cursor: pointer;">
                            <i class="bi bi-images me-1"></i> Ảnh/Video
                            <input type="file" name="attachment" accept="image/*,video/mp4" class="d-none" id="attachmentFile">
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm" id="btn-submit-post">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="post-spinner"></span> Đăng bài
                    </button>
                </div>
                <div id="fileNameDisplay" class="small text-muted mt-2 ps-2 d-none"><i class="bi bi-paperclip text-primary"></i> <span></span></div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Feed Container -->
        <div id="feedContainer">
            <?php foreach($feed as $post): ?>
            <div class="relioo-card p-0 mb-4 bg-white overflow-hidden shadow-sm border" data-post-id="<?php echo $post['id']; ?>">
                <div class="p-4 pb-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <?php if(!empty($post['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($post['avatar_url']); ?>" class="avatar-circle shadow-sm" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="avatar-circle shadow-sm"><?php echo mb_substr(trim($post['full_name']), 0, 1, 'UTF-8'); ?></div>
                            <?php endif; ?>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($post['full_name']); ?></h6>
                                <p class="text-muted small mb-0 d-flex gap-2 align-items-center">
                                    <span><?php echo htmlspecialchars($post['role_name']); ?></span> • <span><?php echo date('H:i d/m/Y', strtotime($post['created_at'])); ?></span> •
                                    <?php 
                                    if ($post['visibility'] == 'Public') echo '<i class="bi bi-globe" title="Công khai"></i>';
                                    elseif ($post['visibility'] == 'Department') echo '<i class="bi bi-building" title="Nội bộ phòng ban"></i>';
                                    elseif ($post['visibility'] == 'Announcement') echo '<i class="bi bi-megaphone-fill text-danger" title="Thông báo"></i>';
                                    ?>
                                    <?php if(($post['is_ai_generated'] ?? 0)): ?>
                                    <span class="badge rounded-pill ml-2" style="background-color: #8b5cf6; font-size: 0.65rem;"><i class="bi bi-stars"></i> Viết bởi Relioo AI</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <?php 
                        $isAuthor = ($post['author_id'] == $_SESSION['user_id']);
                        $isAdminOrCEO = ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 4);
                        if($isAuthor || $isAdminOrCEO): ?>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border-0 text-muted" data-bs-toggle="dropdown"><i class="bi bi-three-dots fs-5"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                                <?php if($isAuthor): ?><li><a class="dropdown-item btn-edit-post" href="#" data-id="<?php echo $post['id']; ?>"><i class="bi bi-pencil me-2"></i>Chỉnh sửa</a></li><?php endif; ?>
                                <li><a class="dropdown-item text-danger btn-delete-post" href="#" data-id="<?php echo $post['id']; ?>"><i class="bi bi-trash me-2"></i>Xóa bài viết</a></li>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                    <p class="mb-3 text-dark post-content-text" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($post['content_html'])); ?></p>
                </div>
                <?php if($post['media_url']): ?><div class="bg-light text-center border-top border-bottom"><img src="<?php echo htmlspecialchars($post['media_url']); ?>" class="img-fluid" style="max-height: 400px;"></div><?php endif; ?>
                
                <div class="p-2 px-4 bg-white post-actions">
                    <div class="d-flex justify-content-between border-top pt-2">
                        <div class="d-flex align-items-center gap-1 flex-fill justify-content-center">
                            <button class="btn btn-sm btn-light border-0 text-muted fw-medium rounded py-2 btn-toggle-react <?php echo $post['is_liked'] ? 'active' : ''; ?>" data-id="<?php echo $post['id']; ?>">
                                <i class="bi bi-heart fs-6"></i> Tim
                            </button>
                            <span class="small text-muted cursor-pointer btn-view-post-likers" data-id="<?php echo $post['id']; ?>"><span class="like-count"><?php echo $post['like_count'] ?: ''; ?></span></span>
                        </div>
                        <button class="btn btn-sm btn-light border-0 text-muted fw-medium rounded py-2 px-3 flex-fill d-flex justify-content-center align-items-center gap-2 btn-show-comments" data-id="<?php echo $post['id']; ?>">
                            <i class="bi bi-chat fs-6"></i> <span class="comment-count"><?php echo $post['comment_count'] ?: ''; ?></span> Bình luận
                        </button>
                    </div>
                </div>

                <div class="comment-section p-4 d-none" id="comment-section-<?php echo $post['id']; ?>">
                    <div class="comment-list mb-4"></div>
                    <div class="d-flex gap-2">
                        <div class="avatar-circle shadow-sm flex-shrink-0 comment-avatar">
                            <?php if(!empty($_SESSION['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['avatar_url']); ?>" class="w-100 h-100 rounded-circle" style="object-fit:cover">
                            <?php else: ?>
                                <?php echo mb_substr(trim($_SESSION['full_name'] ?? 'U'), 0, 1, 'UTF-8'); ?>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1 position-relative">
                            <input type="text" class="form-control form-control-sm rounded-pill border-0 bg-white shadow-sm px-3 input-comment" placeholder="Viết bình luận..." data-post-id="<?php echo $post['id']; ?>">
                            <button class="btn btn-link btn-sm position-absolute end-0 top-50 translate-middle-y text-primary btn-send-comment" style="padding-right: 15px;"><i class="bi bi-send-fill"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="col-lg-4 d-none d-lg-block">
        <div class="relioo-card p-4 sticky-top shadow-sm" style="top: 5rem; z-index: 1;">
            <h6 class="fw-bold mb-4 text-muted small text-uppercase">Quy định mạng nội bộ</h6>
            <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                <li>Tôn trọng đồng nghiệp.</li>
                <li>Chia sẻ tài liệu đúng phòng ban.</li>
            </ul>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa bài viết -->
<div class="modal fade" id="editPostModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.2rem;">
            <div class="modal-header border-0 pb-0"><h6 class="modal-title fw-bold text-primary">CHỈNH SỬA BÀI VIẾT</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body pt-3"><input type="hidden" id="editPostId"><textarea class="form-control bg-light border-0 px-3 py-3 rounded-3" id="editPostContent" rows="5" style="resize: none;"></textarea></div>
            <div class="modal-footer border-0"><button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button><button type="button" class="btn btn-primary rounded-pill px-4 fw-medium" id="btn-save-edit-post">Lưu thay đổi</button></div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa bình luận -->
<div class="modal fade" id="editCommentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0 pb-0"><h6 class="modal-title fw-bold text-primary">CHỈNH SỬA BÌNH LUẬN</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body pt-3"><input type="hidden" id="editCommentId"><input type="hidden" id="editCommentPostId"><textarea class="form-control bg-light border-0 px-3 py-2 rounded-3" id="editCommentContent" rows="3" style="resize: none;"></textarea></div>
            <div class="modal-footer border-0"><button type="button" class="btn btn-primary rounded-pill px-4 btn-sm" id="btn-save-edit-comment">Cập nhật</button></div>
        </div>
    </div>
</div>

<!-- Modal Danh sách người thích -->
<div class="modal fade" id="likersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-bottom-0 pb-0">
                <h6 class="modal-title fw-bold text-dark mx-auto">Người đã thích</h6>
                <button type="button" class="btn-close ms-0" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body likers-container p-0" style="max-height: 400px; overflow-y: auto;">
                <!-- List will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
    const currentUserId = <?php echo $_SESSION['user_id']; ?>;
    const isAdminOrCEO = <?php echo ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 4) ? 'true' : 'false'; ?>;

    // Logic Đăng/Sửa bài viết (Giữ nguyên)
    $('#attachmentFile').on('change', function() { if(this.files.length > 0) $('#fileNameDisplay span').text(this.files[0].name).parent().removeClass('d-none'); });
    $('#createPostForm').on('submit', function(e) {
        e.preventDefault(); let formData = new FormData(this);
        let btn = $('#btn-submit-post');
        btn.prop('disabled', true);
        $.ajax({ 
            url: 'index.php?action=api_create_post', 
            type: 'POST', 
            data: formData, 
            processData: false, 
            contentType: false, 
            dataType: 'json',
            success: function(res) { 
                if (res.success) {
                    location.reload(); 
                } else {
                    alert(res.message);
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                alert("Đã xảy ra lỗi HTTP. Vui lòng xem console.");
                btn.prop('disabled', false);
            }
        });
    });
    $(document).on('click', '.btn-delete-post', function(e) {
        e.preventDefault(); if (!confirm('Xóa bài viết này?')) return;
        $.post('index.php?action=api_delete_post', { post_id: $(this).data('id') }, function(res) { if(res.success) location.reload(); });
    });
    $(document).on('click', '.btn-edit-post', function(e) {
        e.preventDefault(); let id = $(this).data('id'); let content = $(this).closest('.relioo-card').find('.post-content-text').text().trim();
        $('#editPostId').val(id); $('#editPostContent').val(content); $('#editPostModal').modal('show');
    });
    $('#btn-save-edit-post').on('click', function() {
        $.post('index.php?action=api_edit_post', { post_id: $('#editPostId').val(), content: $('#editPostContent').val() }, function(res) { if(res.success) location.reload(); });
    });

    // Logic Tim bài viết
    $(document).on('click', '.btn-toggle-react', function() {
        let $btn = $(this); let postId = $btn.data('id'); let $count = $btn.siblings('.btn-view-post-likers').find('.like-count');
        $.post('index.php?action=api_toggle_post_reaction', { post_id: postId }, function(res) {
            if (res.success) {
                let c = parseInt($count.text() || 0);
                if ($btn.hasClass('active')) { $btn.removeClass('active'); $count.text(c > 1 ? c - 1 : ''); }
                else { $btn.addClass('active'); $count.text(c + 1); }
            }
        }, 'json');
    });

    // Logic Bình luận
    $(document).on('click', '.btn-show-comments', function() {
        let postId = $(this).data('id'); let $section = $('#comment-section-' + postId);
        $section.toggleClass('d-none'); if (!$section.hasClass('d-none')) loadComments(postId);
    });

    function loadComments(postId) {
        let $list = $('#comment-section-' + postId + ' .comment-list');
        $.getJSON('index.php?action=api_fetch_comments&post_id=' + postId, function(res) {
            if (res.success) {
                $list.empty(); if (res.data.length === 0) $list.html('<p class="text-center text-muted small py-2">Chưa có bình luận.</p>');
                res.data.forEach(c => $list.append(renderComment(c, postId)));
            }
        });
    }

    function renderComment(c, postId) {
        let isOwner = (c.user_id == currentUserId);
        let canDelete = (isOwner || isAdminOrCEO);
        let actions = `
            <a href="#" class="btn-comment-react ${parseInt(c.is_liked) == 1 ? 'active' : ''}" data-id="${c.id}">${parseInt(c.is_liked) == 1 ? 'Đã thích' : 'Thích'}</a>
            <a href="#" class="btn-comment-reply" data-id="${c.id}" data-name="${c.full_name}" data-post-id="${postId}">Trả lời</a>
            ${isOwner ? `<a href="#" class="btn-edit-comment text-primary" data-id="${c.id}" data-post-id="${postId}">Sửa</a>` : ''}
            ${canDelete ? `<a href="#" class="btn-delete-comment text-danger" data-id="${c.id}" data-post-id="${postId}">Xóa</a>` : ''}
            <span class="text-muted" style="font-size: 0.65rem;">${c.created_at}</span>
        `;

        return `
            <div class="comment-item" id="comment-${c.id}">
                <div class="d-flex gap-2">
                    ${c.avatar_url ? `<img src="${c.avatar_url}" class="avatar-circle comment-avatar shadow-sm" style="object-fit:cover;">` : `<div class="avatar-circle comment-avatar shadow-sm">${c.full_name.charAt(0)}</div>`}
                    <div class="flex-grow-1">
                        <div class="comment-bubble shadow-sm">
                            <div class="fw-bold small">${c.full_name}</div>
                            <div class="small comment-text">${c.content}</div>
                            ${parseInt(c.like_count) > 0 ? `<div class="position-absolute bottom-0 end-0 translate-middle-y me-2 bg-white shadow-sm rounded-pill px-1 small cursor-pointer btn-view-comment-likers" data-id="${c.id}" style="font-size: 0.65rem;"><i class="bi bi-heart-fill text-danger"></i> ${c.like_count}</div>` : ''}
                        </div>
                        <div class="comment-actions">${actions}</div>
                        <div class="reply-list">
                            ${c.replies.map(r => {
                                let rIsOwner = (r.user_id == currentUserId);
                                let rCanDelete = (rIsOwner || isAdminOrCEO);
                                return `
                                <div class="comment-item mt-2" id="comment-${r.id}">
                                    <div class="d-flex gap-2">
                                        ${r.avatar_url ? `<img src="${r.avatar_url}" class="avatar-circle comment-avatar shadow-sm" style="width: 24px; height: 24px; object-fit:cover;">` : `<div class="avatar-circle comment-avatar shadow-sm" style="width: 24px; height: 24px; font-size: 10px;">${r.full_name.charAt(0)}</div>`}
                                        <div class="flex-grow-1">
                                            <div class="comment-bubble py-1 px-3 shadow-sm">
                                                <div class="fw-bold small" style="font-size: 0.75rem;">${r.full_name}</div>
                                                <div class="small comment-text" style="font-size: 0.8rem;">${r.content}</div>
                                                ${parseInt(r.like_count) > 0 ? `<div class="position-absolute bottom-0 end-0 translate-middle-y me-2 bg-white shadow-sm rounded-pill px-1 small cursor-pointer btn-view-comment-likers" data-id="${r.id}" style="font-size: 0.6rem;"><i class="bi bi-heart-fill text-danger"></i> ${r.like_count}</div>` : ''}
                                            </div>
                                            <div class="comment-actions">
                                                <a href="#" class="btn-comment-react ${parseInt(r.is_liked) == 1 ? 'active' : ''}" data-id="${r.id}">${parseInt(r.is_liked) == 1 ? 'Đã thích' : 'Thích'}</a>
                                                ${rIsOwner ? `<a href="#" class="btn-edit-comment text-primary" data-id="${r.id}" data-post-id="${postId}">Sửa</a>` : ''}
                                                ${rCanDelete ? `<a href="#" class="btn-delete-comment text-danger" data-id="${r.id}" data-post-id="${postId}">Xóa</a>` : ''}
                                                <span class="text-muted" style="font-size: 0.6rem;">${r.created_at}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                            }).join('')}
                        </div>
                    </div>
                </div>
            </div>`;
    }

    $(document).on('keypress', '.input-comment', function(e) {
        if (e.which == 13) {
            let $in = $(this); let pid = $in.data('post-id'); let content = $in.val();
            if (!content.trim()) return;
            $.post('index.php?action=api_add_comment', { post_id: pid, content: content, parent_id: $in.data('parent-id') || null }, function() {
                $in.val('').data('parent-id', null).attr('placeholder', 'Viết bình luận...'); loadComments(pid);
            });
        }
    });

    $(document).on('click', '.btn-comment-reply', function(e) {
        e.preventDefault(); let pid = $(this).data('post-id');
        $('#comment-section-' + pid + ' .input-comment').data('parent-id', $(this).data('id')).attr('placeholder', 'Trả lời ' + $(this).data('name') + '...').focus();
    });

    $(document).on('click', '.btn-comment-react', function(e) {
        e.preventDefault(); let pid = $(this).closest('.comment-section').attr('id').split('-').pop();
        $.post('index.php?action=api_toggle_comment_reaction', { comment_id: $(this).data('id') }, function() { loadComments(pid); });
    });

    $(document).on('click', '.btn-delete-comment', function(e) {
        e.preventDefault(); if(!confirm('Xóa bình luận này?')) return;
        let pid = $(this).data('post-id');
        $.post('index.php?action=api_delete_comment', { comment_id: $(this).data('id') }, function() { loadComments(pid); });
    });

    $(document).on('click', '.btn-edit-comment', function(e) {
        e.preventDefault();
        let id = $(this).data('id'); let pid = $(this).data('post-id');
        let content = $(this).closest('.flex-grow-1').find('.comment-text').first().text();
        $('#editCommentId').val(id); $('#editCommentPostId').val(pid); $('#editCommentContent').val(content);
        $('#editCommentModal').modal('show');
    });

    $('#btn-save-edit-comment').on('click', function() {
        let id = $('#editCommentId').val(); let pid = $('#editCommentPostId').val(); let content = $('#editCommentContent').val();
        $.post('index.php?action=api_edit_comment', { comment_id: id, content: content }, function() {
            $('#editCommentModal').modal('hide'); loadComments(pid);
        });
    });

    // MỚI: Xem danh sách người thích
    $(document).on('click', '.btn-view-post-likers, .btn-view-comment-likers', function() {
        let id = $(this).data('id');
        let isPost = $(this).hasClass('btn-view-post-likers');
        let url = isPost ? 'index.php?action=api_fetch_post_likers&post_id=' + id : 'index.php?action=api_fetch_comment_likers&comment_id=' + id;
        
        let $container = $('.likers-container');
        $container.html('<div class="text-center py-4"><span class="spinner-border spinner-border-sm text-muted"></span></div>');
        $('#likersModal').modal('show');

        $.getJSON(url, function(res) {
            if (res.success) {
                $container.empty();
                if (res.data.length === 0) {
                    $container.html('<div class="p-4 text-center text-muted">Chưa có ai thích.</div>');
                    return;
                }
                res.data.forEach(u => {
                    $container.append(`
                        <div class="d-flex align-items-center gap-3 p-3 liker-list-item">
                            ${u.avatar_url ? `<img src="${u.avatar_url}" class="avatar-circle shadow-sm" style="width: 36px; height: 36px; object-fit:cover;">` : `<div class="avatar-circle shadow-sm" style="width: 36px; height: 36px; font-size: 14px;">${u.full_name.charAt(0)}</div>`}
                            <div>
                                <div class="fw-bold small text-dark">${u.full_name}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">${u.role_name}</div>
                            </div>
                        </div>
                    `);
                });
            }
        });
    });
    // Deep-linking xử lý khi vào từ thông báo
    $(window).on('load', function() {
        const hash = window.location.hash; // #comment-ID
        const urlParams = new URLSearchParams(window.location.search);
        const urlPostId = urlParams.get('post_id');

        if (hash && hash.startsWith('#comment-')) {
            const commentId = hash.replace('#comment-', '');
            const postItem = $(`[data-post-id="${urlPostId}"]`);
            
            if (postItem.length) {
                // 1. Cuộn đến bài viết trước
                $('html, body').animate({ scrollTop: postItem.offset().top - 100 }, 500);
                
                // 2. Mở phần bình luận
                let $section = $('#comment-section-' + urlPostId);
                $section.removeClass('d-none');
                
                // 3. Tải bình luận và cuộn đến bình luận cụ thể
                loadComments(urlPostId);
                
                // Đợi AJAX load xong thì cuộn tiếp
                setTimeout(() => {
                    const $comment = $('#comment-' + commentId);
                    if ($comment.length) {
                        $('html, body').animate({ scrollTop: $comment.offset().top - 150 }, 500);
                        $comment.find('.comment-bubble').css('background-color', '#fff9c4').animate({ backgroundColor: 'white' }, 2000);
                    }
                }, 1000);
            }
        } else if (urlPostId) {
            // Trường hợp chỉ có post_id (Like)
            const postItem = $(`[data-post-id="${urlPostId}"]`);
            if (postItem.length) {
                $('html, body').animate({ scrollTop: postItem.offset().top - 100 }, 500);
                postItem.css('border-color', '#3b82f6').css('box-shadow', '0 0 15px rgba(59,130,246,0.3)');
            }
        }
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
