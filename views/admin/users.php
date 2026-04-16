<?php include __DIR__ . '/../layouts/header.php'; ?>

<style>
/* Toast Notification Center Component */
.toast-center-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    pointer-events: none;
}
.toast-center {
    background: #ffffff !important;
    padding: 2rem 3rem;
    border-radius: 12px;
    box-shadow: 0 15px 50px rgba(0,0,0,0.2) !important;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
    min-width: 350px;
    border: 2px solid #eeeeee !important; /* Solid border */
    animation: toastBounceIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    pointer-events: auto;
}
.toast-center i {
    font-size: 3rem;
}
.toast-center.success i { color: #2ecc71; }
.toast-center.error i { color: #e74c3c; }
.toast-center .toast-msg {
    font-weight: 600;
    color: var(--text-main);
    text-align: center;
    font-size: 1.1rem;
}

@keyframes toastBounceIn {
    0% { transform: scale(0.5); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
@keyframes toastFadeOut {
    0% { transform: scale(1); opacity: 1; }
    100% { transform: scale(0.9); opacity: 0; }
}

/* Cân chỉnh màu phân trang theo tone đỏ giao diện */
.pagination .page-link {
    color: var(--primary-color) !important;
}
.pagination .page-item.active .page-link {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    color: white !important;
}
.pagination .page-link:hover {
    background-color: var(--primary-light) !important;
}
</style>

<div class="glass-panel-scrollable h-100 p-3 pt-4">
<div class="row g-4">
    <div class="col-12">
        <div class="relioo-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="fw-bold mb-0">Danh sách nhân sự (Hệ thống)</h5>
                    <a href="index.php?action=admin_users" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="userSearchInput" class="form-control" placeholder="Tìm kiếm">
                        <button class="btn btn-outline-secondary" type="button" id="btnUserSearch">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-person-plus"></i> Thêm
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle border-top">
                    <thead>
                        <tr class="text-muted small">
                            <th>ID</th>
                            <th>HỌ VÀ TÊN</th>
                            <th>TÀI KHOẢN</th>
                            <th>EMAIL</th>
                            <th>PHÒNG BAN</th>
                            <th>VAI TRÒ</th>
                            <th>TRẠNG THÁI</th>
                            <th class="text-end">HÀNH ĐỘNG</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($usersList as $u): ?>
                        <tr>
                            <td class="text-muted fw-medium">#<?php echo $u['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle shadow-sm overflow-hidden" style="width:34px; height:34px; font-size:12px;">
                                        <?php if(!empty($u['avatar_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($u['avatar_url']); ?>" class="w-100 h-100" style="object-fit:cover">
                                        <?php else: ?>
                                            <?php echo mb_substr(trim($u['full_name']), 0, 1, 'UTF-8'); ?>
                                        <?php endif; ?>
                                    </div>
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($u['full_name']); ?></span>
                                </div>
                            </td>
                            <td><span class="text-muted"><?php echo htmlspecialchars($u['username']); ?></span></td>
                            <td><span class="text-muted"><?php echo htmlspecialchars($u['email'] ?? '-'); ?></span></td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="bi bi-building text-muted me-1"></i>
                                    <?php echo htmlspecialchars($dName = $u['dept_name'] ?? 'Chưa phân bổ'); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    $roleClass = 'bg-secondary';
                                    if($u['role_name'] == 'CEO') $roleClass = 'bg-danger';
                                    if($u['role_name'] == 'Leader') $roleClass = 'bg-warning text-dark';
                                    if($u['role_name'] == 'Admin') $roleClass = 'bg-dark';
                                    if($u['role_name'] == 'Staff') $roleClass = 'bg-info text-dark';
                                ?>
                                <span class="badge <?php echo $roleClass; ?> px-2 py-1"><?php echo htmlspecialchars($u['role_name']); ?></span>
                            </td>
                            <td>
                                <?php if($u['is_active'] == 1): ?>
                                    <span class="text-success small fw-medium"><i class="bi bi-circle-fill me-1" style="font-size:0.6rem;"></i>Active</span>
                                <?php else: ?>
                                    <span class="text-danger small fw-medium"><i class="bi bi-circle-fill me-1" style="font-size:0.6rem;"></i>Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <button class="btn btn-sm btn-light border text-primary btn-edit-user" 
                                        data-id="<?php echo $u['id']; ?>"
                                        data-fullname="<?php echo htmlspecialchars($u['full_name']); ?>"
                                        data-username="<?php echo htmlspecialchars($u['username']); ?>"
                                        data-email="<?php echo htmlspecialchars($u['email'] ?? ''); ?>"
                                        data-dept="<?php echo $u['department_id']; ?>"
                                        data-role="<?php echo $u['role_id']; ?>"
                                        data-active="<?php echo $u['is_active']; ?>"
                                        title="Chỉnh sửa">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light border text-danger btn-delete-user" 
                                        data-id="<?php echo $u['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($u['full_name']); ?>"
                                        title="Xóa">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($usersList)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Chưa có dữ liệu</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php 
                    $searchQuery = $_GET['q'] ?? '';
                    $searchParam = $searchQuery ? '&q=' . urlencode($searchQuery) : '';
                    ?>
                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=admin_users&page=<?php echo $currentPage - 1; echo $searchParam; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link" href="?action=admin_users&page=<?php echo $i; echo $searchParam; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=admin_users&page=<?php echo $currentPage + 1; echo $searchParam; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<!-- Modal Thêm Nhân Sư -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold">Tạo tài khoản mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-4">
        <form id="formAddUser">
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small fw-bold">TÀI KHOẢN <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control bg-light border-0" placeholder="vd: nva_it" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small fw-bold">HỌ VÀ TÊN <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control bg-light border-0" placeholder="Nguyễn Văn A" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">EMAIL</label>
                <input type="email" name="email" class="form-control bg-light border-0" placeholder="email@relioo.com">
            </div>
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small fw-bold">PHÒNG BAN</label>
                    <select name="department_id" class="form-select bg-light border-0">
                        <option value="">-- Chọn phòng ban --</option>
                        <?php foreach($deptList as $d): ?>
                            <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['dept_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small fw-bold">VAI TRÒ <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-select bg-light border-0" required>
                        <?php foreach($rolesList as $r): ?>
                            <option value="<?php echo $r['id']; ?>" <?php echo $r['role_name'] == 'Staff' ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($r['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted small fw-bold">TRẠNG THÁI</label>
                <select name="is_active" class="form-select bg-light border-0">
                    <option value="1" selected>Đang hoạt động (Active)</option>
                    <option value="0">Ngưng hoạt động (Inactive)</option>
                </select>
            </div>
            
            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 py-2 d-flex align-items-center gap-2 mb-0">
                <i class="bi bi-info-circle text-warning fs-5"></i>
                <span class="small text-muted">Mật khẩu mặc định sẽ là <b>123456</b></span>
            </div>
        </form>
      </div>
      <div class="modal-footer border-top-0 pt-3">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Trở về</button>
        <button type="button" form="formAddUser" id="btnSubmitAddUser" class="btn btn-primary rounded-pill px-4">Lưu Dữ Liệu</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Chỉnh sửa Nhân Sự -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold">Chỉnh sửa thông tin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-4">
        <form id="formEditUser">
            <input type="hidden" name="id" id="edit_user_id">
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small fw-bold">TÀI KHOẢN</label>
                    <input type="text" id="edit_username" class="form-control bg-light border-0" disabled>
                    <div class="form-text small">Không thể thay đổi tài khoản.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small fw-bold">HỌ VÀ TÊN <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" id="edit_full_name" class="form-control bg-light border-0" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">EMAIL</label>
                <input type="email" name="email" id="edit_email" class="form-control bg-light border-0">
            </div>
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small fw-bold">PHÒNG BAN</label>
                    <select name="department_id" id="edit_department_id" class="form-select bg-light border-0">
                        <option value="">-- Chọn phòng ban --</option>
                        <?php foreach($deptList as $d): ?>
                            <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['dept_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted small fw-bold">VAI TRÒ <span class="text-danger">*</span></label>
                    <select name="role_id" id="edit_role_id" class="form-select bg-light border-0" required>
                        <?php foreach($rolesList as $r): ?>
                            <option value="<?php echo $r['id']; ?>">
                                <?php echo htmlspecialchars($r['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-0">
                <label class="form-label text-muted small fw-bold">TRẠNG THÁI</label>
                <select name="is_active" id="edit_is_active" class="form-select bg-light border-0">
                    <option value="1">Đang hoạt động (Active)</option>
                    <option value="0">Ngưng hoạt động (Inactive)</option>
                </select>
            </div>
        </form>
      </div>
      <div class="modal-footer border-top-0 pt-3">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
        <button type="button" form="formEditUser" id="btnSubmitEditUser" class="btn btn-primary rounded-pill px-4 shadow-sm">Cập nhật</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="toast-center-container"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Toast System (Premium SweetAlert2 version)
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    function showToast(message, type = 'success') {
        Toast.fire({
            icon: type,
            title: message
        });
    }

    // 2. Add User Logic
    const formAdd = document.getElementById('formAddUser');
    const btnSubmitAdd = document.getElementById('btnSubmitAddUser');

    btnSubmitAdd.addEventListener('click', function() {
        // Validation thủ công để hiện Pop-up cao cấp (Yêu cầu của bạn)
        const username = formAdd.querySelector('[name="username"]').value.trim();
        const fullName = formAdd.querySelector('[name="full_name"]').value.trim();
        const roleId = formAdd.querySelector('[name="role_id"]').value;

        if (!username || !fullName || !roleId) {
            Swal.fire({
                title: 'Thông tin chưa đầy đủ',
                text: 'Vui lòng nhập đầy đủ các thông tin bắt buộc: Tài khoản, Họ tên và Vai trò!',
                icon: 'warning',
                confirmButtonColor: '#ff4d4f',
                confirmButtonText: 'Đã hiểu'
            });
            return;
        }

        const formData = new FormData(formAdd);
        btnSubmitAdd.disabled = true;
        btnSubmitAdd.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...';

        fetch('index.php?action=api_add_user', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast(data.message, 'error');
                btnSubmitAdd.disabled = false;
                btnSubmitAdd.innerHTML = 'Lưu Dữ Liệu';
            }
        })
        .catch(err => {
            showToast('Lỗi kết nối hệ thống', 'error');
            btnSubmitAdd.disabled = false;
        });
    });

    // 3. Edit User Logic
    const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    const formEdit = document.getElementById('formEditUser');
    const btnSubmitEdit = document.getElementById('btnSubmitEditUser');

    document.querySelectorAll('.btn-edit-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const d = this.dataset;
            document.getElementById('edit_user_id').value = d.id;
            document.getElementById('edit_username').value = d.username;
            document.getElementById('edit_full_name').value = d.fullname;
            document.getElementById('edit_email').value = d.email;
            document.getElementById('edit_department_id').value = d.dept || "";
            document.getElementById('edit_role_id').value = d.role;
            document.getElementById('edit_is_active').value = d.active;
            editModal.show();
        });
    });

    btnSubmitEdit.addEventListener('click', function() {
        // Validation thủ công cho Edit
        const fullName = formEdit.querySelector('[name="full_name"]').value.trim();
        const roleId = formEdit.querySelector('[name="role_id"]').value;

        if (!fullName || !roleId) {
            Swal.fire({
                title: 'Thông tin chưa đầy đủ',
                text: 'Vui lòng nhập đầy đủ các thông tin bắt buộc: Họ tên và Vai trò!',
                icon: 'warning',
                confirmButtonColor: '#ff4d4f',
                confirmButtonText: 'Đã hiểu'
            });
            return;
        }

        const formData = new FormData(formEdit);
        btnSubmitEdit.disabled = true;
        btnSubmitEdit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...';

        fetch('index.php?action=api_update_user', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast(data.message, 'error');
                btnSubmitEdit.disabled = false;
                btnSubmitEdit.innerHTML = 'Cập nhật';
            }
        })
        .catch(err => {
            showToast('Lỗi hệ thống', 'error');
            btnSubmitEdit.disabled = false;
        });
    });

    // 4. Delete User Logic
    document.querySelectorAll('.btn-delete-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: `Bạn có chắc chắn muốn xóa nhân viên "${name}"? Hành động này không thể hoàn tác!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4d4f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xác nhận xóa',
                cancelButtonText: 'Hủy bỏ',
                reverseButtons: true,
                borderRadius: '12px'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);

                    fetch('index.php?action=api_delete_user', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(err => {
                        showToast('Lỗi hệ thống khi xóa', 'error');
                    });
                }
            });
    });
});

    // Xử lý tìm kiếm nhân viên - dùng AJAX không reload trang
    document.getElementById('btnUserSearch').addEventListener('click', function() {
        performUserSearch();
    });

    document.getElementById('userSearchInput').addEventListener('keypress', function(e) {
        if (e.which === 13) {
            performUserSearch();
        }
    });

    function performUserSearch() {
        const keyword = document.getElementById('userSearchInput').value.trim();
        if (!keyword) {
            // Nếu rỗng, reload trang để xóa tham số
            window.location.href = 'index.php?action=admin_users';
            return;
        }

        // Hiển thị loading
        showSearchLoading();
        
        // Dùng AJAX tìm kiếm không reload trang
        fetch(`index.php?action=admin_users&q=${encodeURIComponent(keyword)}`)
            .then(response => response.text())
            .then(html => {
                // Replace nội dung table và pagination
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                const newTable = tempDiv.querySelector('.table-responsive');
                const newPagination = tempDiv.querySelector('.pagination');
                const currentTable = document.querySelector('.table-responsive');
                const currentPagination = document.querySelector('.pagination');
                
                // Đếm kết quả
                const tbody = newTable ? newTable.querySelector('tbody') : null;
                const resultCount = tbody ? tbody.querySelectorAll('tr:not([style*="display: none"])').length : 0;
                
                if (newTable && currentTable) {
                    currentTable.innerHTML = newTable.innerHTML;
                }
                
                if (newPagination && currentPagination) {
                    currentPagination.outerHTML = newPagination.outerHTML;
                }
                
                // Update URL mà không reload
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('q', keyword);
                newUrl.searchParams.set('page', '1');
                window.history.pushState({}, '', newUrl);
                
                // Ẩn pagination nếu kết quả <= 5, tự động chuyển trang 2 nếu > 5
                if (resultCount > 5) {
                    setTimeout(() => {
                        const page2Link = document.querySelector('.pagination a[href*="page=2"]');
                        if (page2Link) {
                            showSearchLoading();
                            page2Link.click();
                        }
                    }, 500);
                } else {
                    // Ẩn pagination nếu kết quả <= 5
                    const paginationContainer = document.querySelector('.pagination');
                    if (paginationContainer) {
                        paginationContainer.style.display = 'none';
                    }
                }
                
                // Gắn lại event listeners cho các nút edit/delete mới
                setTimeout(() => {
                    attachEditDeleteEvents();
                }, 100);
            })
            .catch(error => {
                console.error('Lỗi tìm kiếm:', error);
                hideSearchLoading();
                // Fallback: reload trang
                window.location.href = `index.php?action=admin_users&q=${encodeURIComponent(keyword)}`;
            })
            .finally(() => {
                hideSearchLoading();
            });
    }

    function showSearchLoading() {
        const searchInput = document.getElementById('userSearchInput');
        const searchBtn = document.getElementById('btnUserSearch');
        
        if (searchInput) {
            searchInput.style.opacity = '0.6';
            searchInput.disabled = true;
        }
        
        if (searchBtn) {
            searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang tìm...';
            searchBtn.disabled = true;
        }
    }

    function hideSearchLoading() {
        const searchInput = document.getElementById('userSearchInput');
        const searchBtn = document.getElementById('btnUserSearch');
        
        if (searchInput) {
            searchInput.style.opacity = '1';
            searchInput.disabled = false;
        }
        
        if (searchBtn) {
            searchBtn.innerHTML = '<i class="bi bi-search"></i>';
            searchBtn.disabled = false;
        }
    }

    // Function để gắn lại event listeners cho các nút edit/delete
    function attachEditDeleteEvents() {
        // Gắn lại event cho nút edit
        document.querySelectorAll('.btn-edit-user').forEach(btn => {
            btn.onclick = function() {
                const id = this.dataset.id;
                const fullname = this.dataset.fullname;
                const username = this.dataset.username;
                const email = this.dataset.email;
                const deptId = this.dataset.dept;
                const roleId = this.dataset.role;
                const isActive = this.dataset.active;
                
                // Fill modal with user data
                document.getElementById('edit_user_id').value = id;
                document.getElementById('edit_username').value = username;
                document.getElementById('edit_full_name').value = fullname;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_department_id').value = deptId;
                document.getElementById('edit_role_id').value = roleId;
                document.getElementById('edit_is_active').value = isActive;
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
            };
        });
        
        // Gắn lại event cho nút delete
        document.querySelectorAll('.btn-delete-user').forEach(btn => {
            btn.onclick = function() {
                const id = this.dataset.id;
                const fullname = this.dataset.fullname;
                
                Swal.fire({
                    title: 'Xác nhận xóa?',
                    text: `Bạn có chắc chắn muốn xóa nhân viên "${fullname}"? Thao tác này không thể hoàn tác!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ff4d4f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Xác nhận xóa',
                    cancelButtonText: 'Hủy bỏ',
                    reverseButtons: true,
                    borderRadius: '12px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('id', id);

                        fetch('index.php?action=api_delete_user', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.message, 'success');
                                setTimeout(() => location.reload(), 1200);
                            } else {
                                showToast(data.message, 'error');
                            }
                        })
                        .catch(err => {
                            showToast('Lỗi hệ thống khi xóa', 'error');
                        });
                    }
                });
            };
        });
    }

    // Khi trang load, kiểm tra có tham số tìm kiếm không
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('q');
        if (searchQuery) {
            document.getElementById('userSearchInput').value = searchQuery;
        }
        
        // Gán events cho các nút có sẵn
        attachEditDeleteEvents();
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
