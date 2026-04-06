<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="row g-4 position-relative">
    <!-- Cột 8: Feed List Chính -->
    <div class="col-lg-8" style="padding-bottom: 50px;">
        <!-- Khung Đăng Bài (Post box) -->
        <div class="relioo-card p-4 mb-4 border-top border-3 border-primary shadow-sm" style="background: linear-gradient(180deg, #fdfdff 0%, #ffffff 100%);">
            <form id="createPostForm" enctype="multipart/form-data">
                <div class="d-flex gap-3 mb-3">
                    <div class="avatar-circle shadow-sm flex-shrink-0" style="width: 44px; height: 44px; font-size: 14px;">
                        <?php echo mb_substr(trim($_SESSION['full_name'] ?? 'User'), 0, 1, 'UTF-8'); ?>
                    </div>
                    <textarea class="form-control bg-light border-0 px-3 py-3 rounded-3" id="postContent" name="content" rows="3" placeholder="Chia sẻ một ý tưởng, thông báo hoặc gửi ảnh..." required style="resize: none;"></textarea>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="d-flex gap-2">
                        <label class="btn btn-sm btn-light text-primary border rounded-pill px-3 shadow-sm" style="cursor: pointer;">
                            <i class="bi bi-images me-1"></i> Ảnh/Video
                            <input type="file" name="attachment" accept="image/*,video/mp4" class="d-none" id="attachmentFile">
                        </label>
                        <select name="visibility" class="form-select form-select-sm border bg-light rounded-pill px-3" style="width: auto; cursor:pointer;">
                            <option value="Public">🌍 Công khai</option>
                            <option value="Department">🏢 Trong phòng ban</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm" id="btn-submit-post">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="post-spinner"></span>
                        Đăng bài
                    </button>
                </div>
                <div id="fileNameDisplay" class="small text-muted mt-2 ps-2 d-none">
                    <i class="bi bi-paperclip text-primary"></i> <span class="fw-medium text-dark"></span>
                </div>
            </form>
        </div>

        <!-- Timeline Bảng Tin -->
        <div id="feedContainer">
            <?php foreach($feed as $post): ?>
            <div class="relioo-card p-0 mb-4 bg-white overflow-hidden shadow-sm border">
                <!-- Header Card -->
                <div class="p-4 pb-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-circle shadow-sm">
                                <?php echo mb_substr(trim($post['full_name']), 0, 1, 'UTF-8'); ?>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($post['full_name']); ?></h6>
                                <p class="text-muted small mb-0 d-flex gap-2 align-items-center">
                                    <span class="fw-medium"><?php echo htmlspecialchars($post['role_name']); ?></span>
                                    <span>•</span>
                                    <span><?php echo date('H:i d/m/Y', strtotime($post['created_at'])); ?></span>
                                    <span>•</span>
                                    <?php echo $post['visibility'] == 'Public' ? '<i class="bi bi-globe" title="Công khai"></i>' : '<i class="bi bi-building" title="Nội bộ"></i>'; ?>
                                </p>
                            </div>
                        </div>
                        <i class="bi bi-three-dots text-muted fs-5" style="cursor:pointer;"></i>
                    </div>
                    
                    <p class="mb-3 text-dark" style="line-height: 1.6; font-size: 0.95rem;">
                        <?php echo nl2br(htmlspecialchars($post['content_html'])); ?>
                    </p>
                </div> <!-- End Header Card -->
                
                <?php if($post['media_url']): ?>
                <!-- Khu vực hiển thị Ảnh/Video -->
                <div class="bg-light w-100 position-relative border-top border-bottom text-center">
                    <img src="<?php echo htmlspecialchars($post['media_url']); ?>" alt="Attachment" class="img-fluid object-fit-contain" style="max-height: 400px;">
                </div>
                <?php endif; ?>
                
                <!-- Card Footer Interactive -->
                <div class="p-2 px-4 bg-white">
                    <div class="d-flex justify-content-between border-top pt-2">
                        <button class="btn btn-sm btn-light border-0 text-muted fw-medium rounded py-2 px-3 flex-fill d-flex justify-content-center align-items-center gap-2">
                            <i class="bi bi-heart fs-6"></i> Tuyệt
                        </button>
                        <button class="btn btn-sm btn-light border-0 text-muted fw-medium rounded py-2 px-3 flex-fill d-flex justify-content-center align-items-center gap-2">
                            <i class="bi bi-chat fs-6"></i> Bình luận
                        </button>
                        <button class="btn btn-sm btn-light border-0 text-muted fw-medium rounded py-2 px-3 flex-fill d-flex justify-content-center align-items-center gap-2">
                            <i class="bi bi-share fs-6"></i> Chia sẻ
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($feed)): ?>
            <div class="relioo-card p-5 text-center bg-transparent border-0 opacity-50">
                <i class="bi bi-mailbox" style="font-size: 4rem;"></i>
                <h5 class="mt-3 fw-bold">Chưa có bản tin nào</h5>
                <p>Hãy là người lên tiếng đầu tiên khơi dậy phong trào!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cột 4: Analytics Trending Component -->
    <div class="col-lg-4 d-none d-lg-block">
        <div class="relioo-card p-4 sticky-top mb-4 shadow-sm" style="top: 2rem;">
            <h6 class="fw-bold mb-4 text-muted small text-uppercase tracking-wider">Cộng đồng Nổi bật</h6>
            <div class="d-flex align-items-center gap-3 mb-4 cursor-pointer hover-bg-light p-2 rounded transition">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-hash fs-5"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">DuLichHe2026</h6>
                    <p class="text-muted small mb-0">142 bài thảo luận</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 mb-4 cursor-pointer hover-bg-light p-2 rounded transition">
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-hash fs-5"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">ThaoLuanChuyenMon</h6>
                    <p class="text-muted small mb-0">89 bài viết</p>
                </div>
            </div>
            
            <hr class="opacity-10">
            <h6 class="fw-bold my-4 text-muted small text-uppercase tracking-wider">Quy định mạng nội bộ</h6>
            <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                <li>Tôn trọng đồng nghiệp.</li>
                <li>Hạn chế ngôn từ gây thù ghét.</li>
                <li>Chia sẻ tài liệu bảo mật nằm vùng đúng Department.</li>
            </ul>
        </div>
    </div>
</div>

<script>
    // JS Logic Lắng nghe upload
    $('#attachmentFile').on('change', function() {
        if(this.files.length > 0) {
            $('#fileNameDisplay span').text(this.files[0].name);
            $('#fileNameDisplay').removeClass('d-none');
        } else {
            $('#fileNameDisplay').addClass('d-none');
        }
    });

    // AJAX Call to Google Drive Backend
    $('#createPostForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let $btn = $('#btn-submit-post');
        
        $btn.prop('disabled', true);
        $('#post-spinner').removeClass('d-none');
        
        $.ajax({
            url: 'index.php?action=api_create_post',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                // Do chúng ta ko xài framework như Vue/React nên reload là cách an toàn.
                location.reload(); 
            },
            error: function() {
                alert("Đã xảy ra lỗi tệp đính kèm. Vui lòng thử lại!");
            },
            complete: function() {
                $btn.prop('disabled', false);
                $('#post-spinner').addClass('d-none');
            }
        });
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
