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
    border: 2px solid #eeeeee !important;
    animation: toastBounceIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    pointer-events: auto;
}
.toast-center i { font-size: 3.5rem; }
.toast-center.success i { color: #2ecc71; }
.toast-center.error i { color: #e74c3c; }
.toast-center .toast-msg {
    font-weight: 700;
    color: var(--text-main);
    text-align: center;
    font-size: 1.2rem;
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
    <div class="col-md-8">
        <div class="relioo-card p-4 h-100">
            <h5 class="fw-bold mb-4">Danh sách Khối / Phòng ban</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle border-top">
                    <thead>
                        <tr class="text-muted small">
                            <th>ID</th>
                            <th>ĐƠN VỊ</th>
                            <th>MÔ TẢ CHI TIẾT</th>
                            <th>NGÀY THÀNH LẬP</th>
                            <th class="text-end">HÀNH ĐỘNG</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($deptList as $d): ?>
                        <tr>
                            <td class="text-muted fw-medium">#<?php echo $d['id']; ?></td>
                            <td><span class="fw-bold text-primary"><?php echo htmlspecialchars($d['dept_name']); ?></span></td>
                            <td style="max-width: 250px;" class="text-truncate text-muted" title="<?php echo htmlspecialchars($d['description']); ?>">
                                <?php echo htmlspecialchars($d['description']); ?>
                            </td>
                            <td><span class="text-muted small"><?php echo date('d/m/Y', strtotime($d['created_at'])); ?></span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-light border text-primary btn-edit-dept" 
                                    data-id="<?php echo $d['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($d['dept_name']); ?>"
                                    data-description="<?php echo htmlspecialchars($d['description']); ?>"
                                    title="Chỉnh sửa">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-light border text-danger btn-delete-dept" 
                                    data-id="<?php echo $d['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($d['dept_name']); ?>"
                                    title="Xóa">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($deptList)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có phòng ban nào</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=admin_departments&page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link" href="?action=admin_departments&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=admin_departments&page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="relioo-card p-4 border bg-white h-100 position-relative overflow-hidden">
            <div class="position-absolute opacity-10" style="top: -20px; right: -20px; font-size: 8rem; color: var(--primary-color);">
                <i class="bi bi-building"></i>
            </div>
            
            <div class="position-relative z-1">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="bi bi-node-plus-fill fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-0">Thêm đơn vị</h5>
                </div>
                
                <form id="formAddDept">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold small">TÊN PHÒNG BAN <span class="text-danger">*</span></label>
                        <input type="text" name="dept_name" id="dept_name" class="form-control bg-light border-0 py-2" placeholder="VD: Phòng Hành Chính" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold small">TRÁCH NHIỆM CHÍNH</label>
                        <textarea name="description" id="description" class="form-control bg-light border-0 py-2" rows="4" placeholder="Nhập mô tả hoạt động của phòng..."></textarea>
                    </div>
                    <button type="button" id="btnSubmitAddDept" class="btn btn-primary w-100 py-2 fw-medium shadow-sm">
                        Khởi tạo cấu trúc
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Modal Chỉnh sửa Phòng ban -->
<div class="modal fade" id="editDeptModal" tabindex="-1" aria-labelledby="editDeptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDeptModalLabel">Chỉnh sửa Phòng ban</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditDept">
                    <input type="hidden" id="edit_dept_id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold small">TÊN PHÒNG BAN <span class="text-danger">*</span></label>
                        <input type="text" name="dept_name" id="edit_dept_name" class="form-control bg-light border-0 py-2" placeholder="VD: Phòng Hành Chính" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold small">TRÁCH NHIỆM CHÍNH</label>
                        <textarea name="description" id="edit_description" class="form-control bg-light border-0 py-2" rows="4" placeholder="Nhập mô tả hoạt động của phòng..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" id="btnSubmitEditDept" class="btn btn-primary">Cập nhật</button>
            </div>
        </div>
    </div>
</div>

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

    const form = document.getElementById('formAddDept');
    const btnSubmit = document.getElementById('btnSubmitAddDept');

    btnSubmit.addEventListener('click', function() {
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...';

        fetch('index.php?action=api_add_department', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message, 'error');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Khởi tạo cấu trúc';
            }
        })
        .catch(err => {
            showToast('Lỗi kết nối máy chủ', 'error');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = 'Khởi tạo cấu trúc';
        });
    });

    // Xử lý Chỉnh sửa phòng ban
    document.querySelectorAll('.btn-edit-dept').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const description = this.dataset.description;
            
            // Fill modal with current data
            document.getElementById('edit_dept_id').value = id;
            document.getElementById('edit_dept_name').value = name;
            document.getElementById('edit_description').value = description;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editDeptModal'));
            modal.show();
        });
    });

    // Xử lý Cập nhật phòng ban
    document.getElementById('btnSubmitEditDept').addEventListener('click', function() {
        const form = document.getElementById('formEditDept');
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        const btnSubmit = this;
        
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...';

        fetch('index.php?action=api_update_department', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message, 'error');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Cập nhật';
            }
        })
        .catch(err => {
            showToast('Lỗi kết nối máy chủ', 'error');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = 'Cập nhật';
        });
    });

    // Xử lý Xóa phòng ban
    document.querySelectorAll('.btn-delete-dept').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: `Bạn có chắc chắn muốn xóa phòng ban "${name}"? Thao tác này không thể hoàn tác!`,
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

                    fetch('index.php?action=api_delete_department', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            // Hiển thị lỗi (ví dụ: vẫn còn nhân viên)
                            Swal.fire({
                                title: 'Thông báo',
                                text: data.message,
                                icon: 'error',
                                confirmButtonColor: '#ff4d4f',
                                confirmButtonText: 'Đóng'
                            });
                        }
                    })
                    .catch(err => {
                        showToast('Lỗi hệ thống khi xóa', 'error');
                    });
                }
            });
        });
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
