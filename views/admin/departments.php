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
    <div class="col-12">
        <div class="relioo-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="fw-bold mb-0">Danh sách Phòng ban (Hệ thống)</h5>
                    <a href="index.php?action=admin_departments" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="deptSearchInput" class="form-control" placeholder="Tìm kiếm">
                        <button class="btn btn-outline-secondary" type="button" id="btnDeptSearch">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addDeptModal">
                        <i class="bi bi-building"></i> Thêm
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle border-top">
                    <thead>
                        <tr class="text-muted small">
                            <th>ID</th>
                            <th>TÊN PHÒNG BAN</th>
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
                    <?php 
                    $searchQuery = $_GET['q'] ?? '';
                    $searchParam = $searchQuery ? '&q=' . urlencode($searchQuery) : '';
                    ?>
                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=admin_departments&page=<?php echo $currentPage - 1; echo $searchParam; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link" href="?action=admin_departments&page=<?php echo $i; echo $searchParam; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=admin_departments&page=<?php echo $currentPage + 1; echo $searchParam; ?>" aria-label="Next">
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

<!-- Modal Thêm Phòng ban -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-labelledby="addDeptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDeptModalLabel">Thêm Phòng ban Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAddDept">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold small">TÊN PHÒNG BAN <span class="text-danger">*</span></label>
                        <input type="text" name="dept_name" id="dept_name" class="form-control bg-light border-0 py-2" placeholder="VD: Phòng Hành Chính" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold small">TRÁCH NHIỆM CHÍNH</label>
                        <textarea name="description" id="description" class="form-control bg-light border-0 py-2" rows="4" placeholder="Nhập mô tả hoạt động của phòng..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" id="btnSubmitAddDept" class="btn btn-primary">Thêm Phòng Ban</button>
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

    // Xử lý tìm kiếm phòng ban - dùng AJAX không reload trang
    document.getElementById('btnDeptSearch').addEventListener('click', function() {
        performDeptSearch();
    });

    document.getElementById('deptSearchInput').addEventListener('keypress', function(e) {
        if (e.which === 13) {
            performDeptSearch();
        }
    });

    function performDeptSearch() {
        const keyword = document.getElementById('deptSearchInput').value.trim();
        if (!keyword) {
            // Nếu rỗng, reload trang để xóa tham số
            window.location.href = 'index.php?action=admin_departments';
            return;
        }

        // Hiển thị loading
        showSearchLoading();
        
        // Dùng AJAX tìm kiếm không reload trang
        fetch(`index.php?action=admin_departments&q=${encodeURIComponent(keyword)}`)
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
                window.location.href = `index.php?action=admin_departments&q=${encodeURIComponent(keyword)}`;
            })
            .finally(() => {
                hideSearchLoading();
            });
    }

    function showSearchLoading() {
        const searchInput = document.getElementById('deptSearchInput');
        const searchBtn = document.getElementById('btnDeptSearch');
        
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
        const searchInput = document.getElementById('deptSearchInput');
        const searchBtn = document.getElementById('btnDeptSearch');
        
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
        document.querySelectorAll('.btn-edit-dept').forEach(btn => {
            btn.onclick = function() {
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
            };
        });
        
        // Gắn lại event cho nút delete
        document.querySelectorAll('.btn-delete-dept').forEach(btn => {
            btn.onclick = function() {
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
            };
        });
    }

    // Khi trang load, kiểm tra có tham số tìm kiếm không
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('q');
        if (searchQuery) {
            document.getElementById('deptSearchInput').value = searchQuery;
        }
        
        // Gán events cho các nút có sẵn
        attachEditDeleteEvents();
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
