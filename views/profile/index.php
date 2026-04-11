<?php
$pageTitle = "Trang cá nhân";
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Include Cropper.js -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<style>
.profile-cover {
    width: 100%;
    height: 300px;
    background-color: #e9ecef;
    background-size: cover;
    background-position: center;
    border-radius: 0 0 15px 15px;
    position: relative;
    overflow: hidden;
}

/* Thẻ Thông tin đè lên ảnh Cover */
.profile-info-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    margin-top: -60px; /* Đẩy thẻ lên đè vào ảnh bìa */
    padding: 30px;
    position: relative;
    z-index: 5;
    margin-bottom: 2rem;
}

/* Avatar to 200px */
.profile-avatar-container {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    border: 6px solid white;
    background-color: white;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    position: absolute;
    top: -90px;
    left: 40px;
    z-index: 10;
}
.profile-avatar-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.social-link-icon {
    color: #495057;
    font-size: 1.25rem;
    margin-right: 10px;
    transition: 0.2s;
}
.social-link-icon:hover { color: #0d6efd; }

/* Grid ảnh Lịch sử bài đăng */
.post-media-grid img {
    max-height: 400px;
    width: 100%;
    object-fit: contain;
    background-color: #f8f9fa; 
}
.img-container { max-height: 400px; width: 100%; }
</style>

<div class="row w-100 m-0">
    <!-- Left Sidebar Space -->
    <div class="col-md-2 p-0 d-none d-md-block"></div>
    
    <div class="col-12 col-md-10 p-0" style="background-color: #f8f9fa; min-height: 100vh;">
        <div class="container pb-5">
            <!-- Cover Photo Area -->
            <div class="mb-4 position-relative">
                <div class="profile-cover shadow-sm" style="background-image: url('<?php echo htmlspecialchars($coverUrl); ?>');"></div>
                
                <!-- White Info Card -->
                <div class="profile-info-card">
                    <div class="profile-avatar-container">
                        <?php if(!empty($user['avatar_url'])): ?>
                            <img id="avatarPreview" src="<?php echo htmlspecialchars($user['avatar_url']); ?>">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center bg-primary text-white w-100 h-100" style="font-size: 5rem; font-weight: bold;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex w-100 flex-column flex-md-row position-relative">
                        <!-- Cột ẩn để né Avatar -->
                        <div style="width: 200px;" class="flex-shrink-0 d-none d-md-block"></div>

                        <!-- Vùng nội dung chia 3 cột linh hoạt -->
                        <div class="flex-grow-1 d-flex flex-column flex-md-row justify-content-between align-items-md-center pt-md-0 pt-5 mt-md-0 mt-3 gap-3">
                            
                            <!-- Cột 1: Tên & Thông tin cơ bản -->
                            <div class="mb-2 mb-md-0">
                                <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                                <p class="text-muted mb-1" style="font-size: 0.95rem;">0 người bạn</p>
                                <?php
                                $daysJoined = floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24));
                                ?>
                                <p class="text-muted mb-0" style="font-size: 0.95rem;">🔥 <?php echo $daysJoined; ?> ngày đi làm chưa off</p>
                            </div>
                            
                            <!-- Cột 2: Nơi sống & Sinh nhật -->
                            <div class="mb-2 mb-md-0 border-md-start ps-md-4">
                                <h6 class="fw-bold mb-2">Thông tin cá nhân:</h6>
                                <div class="mb-1 text-dark" style="font-size: 0.9rem;">
                                    <?php if(empty($user['hide_birthdate']) && !empty($user['birthdate'])): ?>
                                        <i class="bi bi-cake2 me-2"></i> <?php echo date('d tháng m, Y', strtotime($user['birthdate'])); ?> <br>
                                    <?php endif; ?>
                                    <?php if(!empty($user['location'])): ?>
                                        <i class="bi bi-geo-alt me-2"></i> Sống ở <?php echo htmlspecialchars($user['location']); ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Social Links -->
                                <div class="mt-2">
                                    <?php if(!empty($user['link_tiktok'])): ?>
                                        <a href="<?php echo htmlspecialchars($user['link_tiktok']); ?>" target="_blank" class="social-link-icon"><i class="bi bi-tiktok border rounded-circle px-1 bg-dark text-white"></i></a>
                                    <?php endif; ?>
                                    <?php if(!empty($user['link_instagram'])): ?>
                                        <a href="<?php echo htmlspecialchars($user['link_instagram']); ?>" target="_blank" class="social-link-icon"><i class="bi bi-instagram"></i></a>
                                    <?php endif; ?>
                                    <?php if(!empty($user['link_facebook'])): ?>
                                        <a href="<?php echo htmlspecialchars($user['link_facebook']); ?>" target="_blank" class="social-link-icon"><i class="bi bi-facebook"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                                <a href="index.php?action=social" class="btn btn-danger rounded-pill fw-bold shadow-sm px-3 d-flex align-items-center">
                                    Đăng bài +
                                </a>
                                <button class="btn btn-light rounded-circle shadow-sm fw-bold border" data-bs-toggle="modal" data-bs-target="#editProfileModal" style="width: 40px; height: 40px;">
                                    ...
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area (Post History) -->
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <h5 class="fw-bold mb-4">Lịch sử bài đăng</h5>
                    
                    <?php if (empty($userPosts)): ?>
                        <div class="text-center py-5 text-muted bg-white rounded-4 shadow-sm">
                            <i class="bi bi-folder-x fs-1"></i>
                            <p class="mt-2">Chưa có bài đăng nào.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($userPosts as $post): ?>
                            <!-- Post Card -->
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                                    <div style="width: 45px; height: 45px" class="rounded-circle overflow-hidden bg-primary text-white d-flex align-items-center justify-content-center me-3 shadow-sm flex-shrink-0">
                                        <?php if(!empty($post['avatar_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($post['avatar_url']); ?>" class="w-100 h-100" style="object-fit:cover">
                                        <?php else: ?>
                                            <b class="fs-5"><?php echo strtoupper(substr($post['full_name'],0,1)); ?></b>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($post['full_name']); ?></h6>
                                        </div>
                                        <small class="text-muted d-flex align-items-center">
                                            <i class="bi bi-clock me-1"></i> 
                                            <?php echo date('H:i d/m/Y', strtotime($post['created_at'])); ?>
                                            <span class="mx-1">·</span> <i class="bi bi-globe-americas"></i>
                                        </small>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0 text-muted rounded-circle" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border">
                                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Xóa (Chỉ trên feed)</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    <div class="post-content mb-3" style="font-size: 0.95rem;">
                                        <?php echo $post['content_html']; ?>
                                    </div>
                                    <?php if(!empty($post['media_url'])): ?>
                                        <div class="post-media-grid rounded-4 overflow-hidden shadow-sm">
                                            <img src="<?php echo htmlspecialchars($post['media_url']); ?>" class="img-fluid w-100">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-light border-top border-light py-2 px-4 rounded-bottom-4">
                                    <div class="d-flex text-muted fw-bold justify-content-center gap-5" style="font-size: 0.9rem;">
                                        <div class="cursor-pointer d-flex align-items-center"><i class="bi bi-heart me-1"></i> <?php echo $post['like_count']; ?> Tim</div>
                                        <div class="cursor-pointer d-flex align-items-center"><i class="bi bi-chat-left-text me-1"></i> <?php echo $post['comment_count']; ?> Bình luận</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa hồ sơ -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Chỉnh sửa hồ sơ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <form id="profileForm">
                    <input type="file" id="avatarInput" accept="image/*" class="d-none">
                    <input type="file" id="coverInput" accept="image/*" class="d-none">
                    <input type="hidden" name="avatar_base64" id="avatarBase64">
                    <input type="hidden" name="cover_base64" id="coverBase64">

                    <div class="row align-items-center mb-4 bg-white p-3 rounded-3 border">
                        <div class="col-md-6 d-flex align-items-center gap-3 border-end">
                            <div style="width: 60px; height: 60px;" class="rounded-circle overflow-hidden border">
                                <img id="miniAvatarPreview" src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'https://placehold.co/100x100?text=Avatar'); ?>" class="w-100 h-100" style="object-fit:cover">
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">Ảnh đại diện</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="document.getElementById('avatarInput').click()"><i class="bi bi-upload me-1"></i> Tải ảnh lên</button>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center gap-3 ps-4">
                            <div style="width: 100px; height: 60px;" class="rounded-2 overflow-hidden border">
                                <img id="miniCoverPreview" src="<?php echo htmlspecialchars($user['cover_url'] ?? 'https://placehold.co/200x100?text=Cover'); ?>" class="w-100 h-100" style="object-fit:cover">
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">Ảnh bìa</h6>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill" onclick="document.getElementById('coverInput').click()"><i class="bi bi-image me-1"></i> Đổi ảnh bìa</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Tên đăng nhập (Chỉ Đọc)</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Số điện thoại</label>
                            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Ngày sinh</label>
                            <input type="date" class="form-control" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="hide_birthdate" id="hide_birthdate" <?php echo !empty($user['hide_birthdate']) ? 'checked' : ''; ?>>
                                <label class="form-check-label text-muted small" for="hide_birthdate">Ẩn ngày sinh trên trang cá nhân</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Tỉnh/Thành phố sinh sống</label>
                            <select class="form-select" name="location">
                                <option value="">Chưa chọn</option>
                                <?php
                                $provinces = ["Thành phố Hà Nội", "Thành phố Hồ Chí Minh", "Thành phố Đà Nẵng", "Thành phố Hải Phòng", "Thành phố Cần Thơ", 
                                "Tỉnh An Giang", "Tỉnh Bà Rịa - Vũng Tàu", "Tỉnh Bắc Giang", "Tỉnh Bắc Kạn", "Tỉnh Bạc Liêu", "Tỉnh Bắc Ninh", 
                                "Tỉnh Bến Tre", "Tỉnh Bình Định", "Tỉnh Bình Dương", "Tỉnh Bình Phước", "Tỉnh Bình Thuận", "Tỉnh Cà Mau", 
                                "Tỉnh Cao Bằng", "Tỉnh Đắk Lắk", "Tỉnh Đắk Nông", "Tỉnh Điện Biên", "Tỉnh Đồng Nai", "Tỉnh Đồng Tháp", 
                                "Tỉnh Gia Lai", "Tỉnh Hà Giang", "Tỉnh Hà Nam", "Tỉnh Hà Tĩnh", "Tỉnh Hải Dương", "Tỉnh Hậu Giang", 
                                "Tỉnh Hòa Bình", "Tỉnh Hưng Yên", "Tỉnh Khánh Hòa", "Tỉnh Kiên Giang", "Tỉnh Kon Tum", "Tỉnh Lai Châu", 
                                "Tỉnh Lâm Đồng", "Tỉnh Lạng Sơn", "Tỉnh Lào Cai", "Tỉnh Long An", "Tỉnh Nam Định", "Tỉnh Nghệ An", 
                                "Tỉnh Ninh Bình", "Tỉnh Ninh Thuận", "Tỉnh Phú Thọ", "Tỉnh Quảng Bình", "Tỉnh Quảng Nam", "Tỉnh Quảng Ngãi", 
                                "Tỉnh Quảng Ninh", "Tỉnh Quảng Trị", "Tỉnh Sóc Trăng", "Tỉnh Sơn La", "Tỉnh Tây Ninh", "Tỉnh Thái Bình", 
                                "Tỉnh Thái Nguyên", "Tỉnh Thanh Hóa", "Tỉnh Thừa Thiên Huế", "Tỉnh Tiền Giang", "Tỉnh Trà Vinh", "Tỉnh Tuyên Quang", 
                                "Tỉnh Vĩnh Long", "Tỉnh Vĩnh Phúc", "Tỉnh Yên Bái"];
                                foreach($provinces as $p) {
                                    $selected = ($user['location'] == $p) ? 'selected' : '';
                                    echo "<option value=\"$p\" $selected>$p</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-12 mt-2 border-top pt-3">
                            <h6 class="fw-bold mb-3"><i class="bi bi-link-45deg"></i> Mạng xã hội</h6>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small text-muted"><i class="bi bi-facebook text-primary"></i> Link Facebook</label>
                            <input type="text" class="form-control" name="link_facebook" placeholder="https://fb.com/..." value="<?php echo htmlspecialchars($user['link_facebook'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small text-muted"><i class="bi bi-instagram text-danger"></i> Link Instagram</label>
                            <input type="text" class="form-control" name="link_instagram" placeholder="https://instagram.com/..." value="<?php echo htmlspecialchars($user['link_instagram'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small text-muted"><i class="bi bi-tiktok text-dark"></i> Link TikTok</label>
                            <input type="text" class="form-control" name="link_tiktok" placeholder="https://tiktok.com/@..." value="<?php echo htmlspecialchars($user['link_tiktok'] ?? ''); ?>">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" id="btnSaveProfile" onclick="saveProfile()">
                    <i class="bi bi-save me-1"></i> Lưu Thay Đổi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cropper -->
<div class="modal fade" id="cropModal" tabindex="-1" data-bs-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-crop me-2"></i>Chỉnh sửa hình ảnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="clearInput()"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="img-container d-flex justify-content-center">
                    <img id="imageToCrop" src="" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal" onclick="clearInput()">Hủy</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnCrop"><i class="bi bi-check2-circle me-1"></i> Cắt & Dùng ảnh này</button>
            </div>
        </div>
    </div>
</div>

<script>
let cropper;
let currentCropType = 'avatar'; 

function clearInput() {
    $('#avatarInput').val('');
    $('#coverInput').val('');
    if(cropper) {
        cropper.destroy();
        cropper = null;
    }
}

function handleFileSelect(input, type) {
    let files = input.files;
    if (files && files.length > 0) {
        currentCropType = type;
        let file = files[0];
        let reader = new FileReader();

        reader.onload = function(e) {
            let image = document.getElementById('imageToCrop');
            image.src = e.target.result;
            
            let cropModal = new bootstrap.Modal(document.getElementById('cropModal'));
            cropModal.show();

            document.getElementById('cropModal').addEventListener('shown.bs.modal', function () {
                if(cropper) { cropper.destroy(); }
                let ratio = (type === 'avatar') ? 1 / 1 : 21 / 9;
                
                cropper = new Cropper(image, {
                    aspectRatio: ratio,
                    viewMode: 1,
                    autoCropArea: 1,
                    zoomable: true,
                    background: false
                });
            }, {once: true});
        };
        reader.readAsDataURL(file);
    }
}

$('#avatarInput').on('change', function() { handleFileSelect(this, 'avatar'); });
$('#coverInput').on('change', function() { handleFileSelect(this, 'cover'); });

$('#btnCrop').click(function() {
    if (!cropper) return;
    
    let canvas = cropper.getCroppedCanvas({
        width: currentCropType === 'avatar' ? 400 : 1200,
        height: currentCropType === 'avatar' ? 400 : 514
    });

    let base64 = canvas.toDataURL('image/jpeg', 0.8);

    if (currentCropType === 'avatar') {
        $('#avatarBase64').val(base64);
        let avatarPreview = document.getElementById('avatarPreview');
        if(avatarPreview) avatarPreview.src = base64;
        else $('.profile-avatar-container').html(`<img id="avatarPreview" src="${base64}">`);
        
        $('#miniAvatarPreview').attr('src', base64);
    } else if (currentCropType === 'cover') {
        $('#coverBase64').val(base64);
        $('.profile-cover').css('background-image', `url(${base64})`);
        $('#miniCoverPreview').attr('src', base64);
    }

    bootstrap.Modal.getInstance(document.getElementById('cropModal')).hide();
});

function saveProfile() {
    let btn = $('#btnSaveProfile');
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Đang lưu...').prop('disabled', true);

    let fd = new FormData(document.getElementById('profileForm'));

    $.ajax({
        url: 'index.php?action=api_update_profile',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            btn.html('<i class="bi bi-save me-1"></i> Lưu Thay Đổi').prop('disabled', false);
            if(res.success) {
                Swal.fire({
                    title: 'Thành công!', 
                    text: res.message, 
                    icon: 'success', 
                    timer: 2000, 
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire({title: 'Lỗi', text: res.message, icon: 'error'});
            }
        },
        error: function(xhr) {
            btn.html('<i class="bi bi-save me-1"></i> Lưu Thay Đổi').prop('disabled', false);
            Swal.fire({title: 'Lỗi hệ thống!', text: 'Không thể xử lý yêu cầu.', icon: 'error'});
        }
    });
}
</script>
