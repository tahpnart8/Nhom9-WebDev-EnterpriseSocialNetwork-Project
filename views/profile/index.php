<?php
$pageTitle = "Trang cá nhân";
// Sidebar và Header đã được include từ index.php hoặc layer cao hơn? 
// Thực tế index.php include Controller, Controller include View.
// Trong View này ta chỉ include Header (Header đã include Sidebar).
require_once __DIR__ . '/../layouts/header.php';

// Lấy thông tin ảnh bìa
$coverUrl = !empty($user['cover_url']) ? $user['cover_url'] : 'https://placehold.co/1200x300?text=Cover+Photo';
?>

<!-- Include Cropper.js & SweetAlert2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<style>
/* CSS cho Trang cá nhân */
.profile-container {
    max-width: 1100px;
    margin: 0 auto;
}

.profile-cover {
    width: 100%;
    height: 300px;
    background-color: #e9ecef;
    background-size: cover;
    background-position: center;
    border-radius: 0 0 1.5rem 1.5rem;
    position: relative;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.profile-info-card {
    background: white;
    border-radius: 1.5rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    margin-top: -60px;
    padding: 2rem;
    position: relative;
    z-index: 5;
    margin-bottom: 2.5rem;
}

.profile-avatar-container {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    border: 6px solid white;
    background-color: white;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
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
    color: #64748b;
    font-size: 1.3rem;
    transition: all 0.2s ease;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #f1f5f9;
}
.social-link-icon:hover { 
    background: var(--primary-light);
    color: var(--primary-color);
    transform: translateY(-2px);
}

.post-media-grid img {
    max-height: 450px;
    width: 100%;
    object-fit: contain;
    background-color: #f8f9fa;
    border-radius: 1rem;
}

/* Tooltip style cho nút ... */
.btn-edit-trigger {
    background: #f8f9fa;
    border: 1px solid #e2e8f0;
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}
.btn-edit-trigger:hover {
    background: #edf2f7;
    border-color: #cbd5e1;
    color: var(--primary-color);
}
</style>

<div class="profile-container pb-5">
    
    <!-- Cover Photo Area -->
    <div class="mb-4 position-relative">
        <div class="profile-cover" style="background-image: url('<?php echo htmlspecialchars($coverUrl); ?>');"></div>
        
        <!-- White Info Card -->
        <div class="profile-info-card">
            <!-- Avatar -->
            <div class="profile-avatar-container">
                <?php if(!empty($user['avatar_url'])): ?>
                    <img id="avatarMainPreview" src="<?php echo htmlspecialchars($user['avatar_url']); ?>">
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center bg-primary text-white w-100 h-100" style="font-size: 5rem; font-weight: 800;">
                        <?php 
                            $nameParts = explode(' ', trim($user['full_name']));
                            echo mb_substr(end($nameParts), 0, 1, 'UTF-8'); 
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Content row -->
            <div class="row align-items-center" style="padding-left: 210px; min-height: 100px;">
                <!-- Col 1: Name & Stats -->
                <div class="col-md-5">
                    <h2 class="fw-bold mb-1" style="letter-spacing: -0.5px;"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <div class="d-flex align-items-center gap-3 text-muted small mt-1">
                        <span><b class="text-dark">0</b> bạn bè</span>
                        <span class="text-warning">🔥 <b><?php echo floor((time() - strtotime($user['created_at'])) / 86400); ?></b> ngày gắn bó</span>
                        <?php if(!$isViewingSelf): ?>
                        <span class="badge bg-light text-dark border"><i class="bi bi-person-check me-1"></i> Đồng nghiệp</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Col 2: Basic Info & Social -->
                <div class="col-md-4 border-start ps-4">
                    <div class="small text-secondary fw-medium">
                        <?php if(empty($user['hide_birthdate']) && !empty($user['birthdate'])): ?>
                            <div class="mb-1"><i class="bi bi-balloon-fill me-2 text-primary opacity-75"></i>Sinh nhật: <?php echo date('d/m/Y', strtotime($user['birthdate'])); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($user['location'])): ?>
                            <div class="mb-1"><i class="bi bi-geo-alt-fill me-2 text-danger opacity-75"></i>Sống tại: <?php echo htmlspecialchars($user['location']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2 d-flex gap-2">
                        <?php if(!empty($user['link_facebook'])): ?>
                            <a href="<?php echo htmlspecialchars($user['link_facebook']); ?>" target="_blank" class="social-link-icon" title="Facebook"><i class="bi bi-facebook"></i></a>
                        <?php endif; ?>
                        <?php if(!empty($user['link_instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($user['link_instagram']); ?>" target="_blank" class="social-link-icon" title="Instagram"><i class="bi bi-instagram"></i></a>
                        <?php endif; ?>
                        <?php if(!empty($user['link_tiktok'])): ?>
                            <a href="<?php echo htmlspecialchars($user['link_tiktok']); ?>" target="_blank" class="social-link-icon" title="TikTok"><i class="bi bi-tiktok"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Col 3: Actions -->
                <div class="col-md-3 text-end">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <?php if($isViewingSelf): ?>
                        <a href="index.php?action=social" class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm border-0" style="background: linear-gradient(45deg, #0d6efd, #0b5ed7);">
                            <i class="bi bi-plus-lg me-1"></i>Đăng bài
                        </a>
                        <button type="button" onclick="handleOpenEditModal()" class="btn-edit-trigger shadow-sm" title="Chỉnh sửa trang cá nhân">
                            <i class="bi bi-three-dots fs-5"></i>
                        </button>
                        <?php else: ?>
                        <a href="index.php?action=chat&with=<?php echo $user['id']; ?>" class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm border-0">
                            <i class="bi bi-chat-dots me-2"></i>Nhắn tin
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Post History Section -->
    <div class="mt-4">
        <h5 class="fw-bold mb-4 d-flex align-items-center"><i class="bi bi-clock-history me-2 text-muted"></i>Lịch sử bài đăng</h5>
        
        <?php if (empty($userPosts)): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-dashed">
                <i class="bi bi-file-post fs-1 text-light"></i>
                <p class="mt-2 text-muted">
                    <?php echo $isViewingSelf ? "Bạn chưa chia sẻ bài đăng nào trên Bảng tin nội bộ." : "Người dùng này chưa có bài đăng công khai nào."; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 g-4">
                <?php foreach ($userPosts as $post): ?>
                    <div class="col">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center">
                                <div style="width: 48px; height: 48px" class="rounded-circle overflow-hidden bg-light me-3 flex-shrink-0">
                                    <img src="<?php echo htmlspecialchars($post['avatar_url'] ?? 'https://placehold.co/100x100?text=Avatar'); ?>" class="w-100 h-100" style="object-fit:cover">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($post['full_name']); ?></h6>
                                    <small class="text-muted"><?php echo date('H:i, d/m/Y', strtotime($post['created_at'])); ?> <span class="ms-1">· <i class="bi bi-globe"></i></span></small>
                                </div>
                            </div>
                            <div class="card-body py-2 px-4">
                                <div class="mb-3" style="font-size: 0.95rem; line-height: 1.6;"><?php echo $post['content_html']; ?></div>
                                <?php if(!empty($post['media_url'])): ?>
                                    <div class="post-media-grid text-center">
                                        <img src="<?php echo htmlspecialchars($post['media_url']); ?>" class="img-fluid">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white border-top border-light py-3 px-4">
                                <div class="d-flex text-muted fw-bold gap-4" style="font-size: 0.85rem;">
                                    <span class="cursor-pointer"><i class="bi bi-heart me-1"></i> <?php echo $post['like_count']; ?> Tim</span>
                                    <span class="cursor-pointer"><i class="bi bi-chat-left-text me-1"></i> <?php echo $post['comment_count']; ?> Bình luận</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== MODALS ===== -->

<!-- Modal Chỉnh sửa hồ sơ -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-white p-4 pb-2">
                <h5 class="modal-title fw-bold" style="font-size: 1.4rem;">Chỉnh sửa trang cá nhân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <form id="profileForm">
                    <input type="file" id="avatarInput" accept="image/*" class="d-none">
                    <input type="file" id="coverInput" accept="image/*" class="d-none">
                    <input type="hidden" name="avatar_base64" id="avatarBase64">
                    <input type="hidden" name="cover_base64" id="coverBase64">

                    <!-- Ảnh -->
                    <div class="row align-items-center mb-4 bg-light p-3 rounded-4">
                        <div class="col-md-6 d-flex align-items-center gap-3 border-end border-white">
                            <div style="width:70px;height:70px;" class="rounded-circle overflow-hidden border border-white border-4 shadow-sm flex-shrink-0">
                                <img id="miniAvatarPreview" src="<?php echo htmlspecialchars($user['avatar_url'] ?? 'https://placehold.co/100x100?text=?'); ?>" class="w-100 h-100" style="object-fit:cover">
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold small">Ảnh đại diện</h6>
                                <button type="button" class="btn btn-sm btn-white border rounded-pill shadow-sm" onclick="document.getElementById('avatarInput').click()"><i class="bi bi-pencil me-1"></i> Thay đổi</button>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center gap-3 ps-4">
                            <div style="width:110px;height:65px;" class="rounded-3 overflow-hidden border border-white border-3 shadow-sm flex-shrink-0">
                                <img id="miniCoverPreview" src="<?php echo htmlspecialchars($user['cover_url'] ?? 'https://placehold.co/200x100?text=Cover'); ?>" class="w-100 h-100" style="object-fit:cover">
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold small">Ảnh bìa</h6>
                                <button type="button" class="btn btn-sm btn-white border rounded-pill shadow-sm" onclick="document.getElementById('coverInput').click()"><i class="bi bi-image me-1"></i> Thay đổi</button>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Họ và Tên</label>
                            <input type="text" class="form-control rounded-3" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Tỉnh/Thành phố sinh sống</label>
                            <select class="form-select rounded-3" name="location">
                                <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                <?php
                                $provinces = [
                                    "Thành phố Hà Nội", "Thành phố Hồ Chí Minh", "Thành phố Hải Phòng", "Thành phố Đà Nẵng", "Thành phố Cần Thơ",
                                    "Tỉnh An Giang", "Tỉnh Bà Rịa - Vũng Tàu", "Tỉnh Bắc Giang", "Tỉnh Bắc Kạn", "Tỉnh Bạc Liêu", "Tỉnh Bắc Ninh",
                                    "Tỉnh Bến Tre", "Tỉnh Bình Định", "Tỉnh Bình Dương", "Tỉnh Bình Phước", "Tỉnh Bình Thuận", "Tỉnh Cà Mau",
                                    "Tỉnh Cao Bằng", "Tỉnh Đắk Lắk", "Tỉnh Đắk Nông", "Tỉnh Điện Biên", "Tỉnh Đồng Nai", "Tỉnh Đồng Tháp",
                                    "Tỉnh Gia Lai", "Tỉnh Hà Giang", "Tỉnh Hà Nam", "Tỉnh Hà Tĩnh", "Tỉnh Hải Dương", "Tỉnh Hậu Giang",
                                    "Tỉnh Hòa Bình", "Tỉnh Hưng Yên", "Tỉnh Khánh Hòa", "Tỉnh Kiên Giang", "Tỉnh Kon Tum", "Tỉnh Lai Châu",
                                    "Tỉnh Lâm Đồng", "Tỉnh Lạng Sơn", "Tỉnh Lào Cai", "Tỉnh Long An", "Tỉnh Nam Định", "Tỉnh Nghệ An",
                                    "Tỉnh Ninh Bình", "Tỉnh Ninh Thuận", "Tỉnh Phú Thọ", "Tỉnh Quảng Bình", "Tỉnh Quảng Nam", "Tỉnh Quảng Ngãi",
                                    "Tỉnh Quảng Ninh", "Tỉnh Quảng Trị", "Tỉnh Sóc Trăng", "Tỉnh Sơn La", "Tỉnh Tây Ninh", "Tỉnh Thái Bình",
                                    "Tỉnh Thái Nguyên", "Tỉnh Thanh Hóa", "Tỉnh Thừa Thiên Huế", "Tỉnh Tiền Giang", "Tỉnh Trà Vinh", "Tỉnh Tuyên Quang",
                                    "Tỉnh Vĩnh Long", "Tỉnh Vĩnh Phúc", "Tỉnh Yên Bái"
                                ];
                                foreach($provinces as $p) {
                                    $sel = ($user['location'] ?? '') == $p ? 'selected' : '';
                                    echo "<option value=\"$p\" $sel>$p</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Email công ty</label>
                            <input type="email" class="form-control rounded-3" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Số điện thoại</label>
                            <input type="text" class="form-control rounded-3" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Ngày sinh</label>
                            <input type="date" class="form-control rounded-3" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
                            <div class="form-check mt-2 small">
                                <input class="form-check-input" type="checkbox" name="hide_birthdate" id="hide_birthdate" <?php echo !empty($user['hide_birthdate']) ? 'checked' : ''; ?>>
                                <label class="form-check-label text-muted" for="hide_birthdate">Ẩn ngày sinh trên trang cá nhân</label>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-3 pt-3 border-top">
                            <h6 class="fw-bold mb-3 small text-uppercase" style="letter-spacing: 1px;">Liên kết mạng xã hội</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm rounded-3 overflow-hidden border">
                                        <span class="input-group-text border-0 bg-white px-2"><i class="bi bi-facebook text-primary"></i></span>
                                        <input type="text" class="form-control border-0" name="link_facebook" placeholder="Facebook URL" value="<?php echo htmlspecialchars($user['link_facebook'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm rounded-3 overflow-hidden border">
                                        <span class="input-group-text border-0 bg-white px-2"><i class="bi bi-instagram text-danger"></i></span>
                                        <input type="text" class="form-control border-0" name="link_instagram" placeholder="Instagram URL" value="<?php echo htmlspecialchars($user['link_instagram'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm rounded-3 overflow-hidden border">
                                        <span class="input-group-text border-0 bg-white px-2"><i class="bi bi-tiktok text-dark"></i></span>
                                        <input type="text" class="form-control border-0" name="link_tiktok" placeholder="TikTok URL" value="<?php echo htmlspecialchars($user['link_tiktok'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" id="btnSaveProfile" onclick="handleSaveProfile()">Lưu các thay đổi</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cropper -->
<div class="modal fade" id="cropModal" tabindex="-1" data-bs-backdrop="static" style="z-index: 2000;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-crop me-2"></i>Cắt ghép hình ảnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="handleClearInput()"></button>
            </div>
            <div class="modal-body p-0 bg-dark d-flex align-items-center justify-content-center" style="min-height: 400px;">
                <img id="imageToCrop" src="" style="max-width: 100%; display: block;">
            </div>
            <div class="modal-footer border-top p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal" onclick="handleClearInput()">Bỏ qua</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnCropSubmit">Áp dụng ảnh này</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- Scripts -->
<script>
let _editModal = null;
let _cropModal = null;
let _cropper = null;
let _currentCropType = 'avatar';

document.addEventListener('DOMContentLoaded', function() {
    _editModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editProfileModal'));
    _cropModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('cropModal'));
});

function handleOpenEditModal() {
    _editModal.show();
}

function handleClearInput() {
    document.getElementById('avatarInput').value = '';
    document.getElementById('coverInput').value = '';
    if (_cropper) { _cropper.destroy(); _cropper = null; }
}

function handleFileSelect(input, type) {
    if (!input.files || input.files.length === 0) return;
    _currentCropType = type;
    
    var reader = new FileReader();
    reader.onload = function(e) {
        var image = document.getElementById('imageToCrop');
        image.src = e.target.result;

        _editModal.hide();

        setTimeout(function() {
            _cropModal.show();
            document.getElementById('cropModal').addEventListener('shown.bs.modal', function() {
                if (_cropper) _cropper.destroy();
                _cropper = new Cropper(image, {
                    aspectRatio: type === 'avatar' ? 1 : 21/9,
                    viewMode: 1,
                    autoCropArea: 1,
                    background: false
                });
            }, { once: true });
        }, 400);
    };
    reader.readAsDataURL(input.files[0]);
}

document.getElementById('avatarInput').addEventListener('change', function() { handleFileSelect(this, 'avatar'); });
document.getElementById('coverInput').addEventListener('change', function() { handleFileSelect(this, 'cover'); });

document.getElementById('btnCropSubmit').addEventListener('click', function() {
    if (!_cropper) return;
    
    var canvas = _cropper.getCroppedCanvas({
        width: _currentCropType === 'avatar' ? 400 : 1200,
        height: _currentCropType === 'avatar' ? 400 : 514
    });
    
    var base64 = canvas.toDataURL('image/jpeg', 0.9);

    if (_currentCropType === 'avatar') {
        document.getElementById('avatarBase64').value = base64;
        document.getElementById('miniAvatarPreview').src = base64;
        if (document.getElementById('avatarMainPreview')) {
            document.getElementById('avatarMainPreview').src = base64;
        }
    } else {
        document.getElementById('coverBase64').value = base64;
        document.getElementById('miniCoverPreview').src = base64;
        document.querySelector('.profile-cover').style.backgroundImage = 'url(' + base64 + ')';
    }

    _cropModal.hide();
    document.getElementById('cropModal').addEventListener('hidden.bs.modal', function() {
        _editModal.show();
    }, { once: true });
    
    if (_cropper) { _cropper.destroy(); _cropper = null; }
});

function handleSaveProfile() {
    var btn = document.getElementById('btnSaveProfile');
    var originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...';
    btn.disabled = true;

    var fd = new FormData(document.getElementById('profileForm'));

    fetch('index.php?action=api_update_profile', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            Swal.fire({
                title: 'Thành công!',
                text: res.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Lỗi!', res.message, 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(() => {
        Swal.fire('Lỗi hệ thống!', 'Không thể gửi dữ liệu.', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>
