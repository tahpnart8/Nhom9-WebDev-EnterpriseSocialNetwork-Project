<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | Relioo</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #1a1d21;
            --secondary-bg: #24292e;
            --accent-color: #0d6efd;
            --card-border: #30363d;
            --text-main: #e6edf3;
            --text-muted: #8b949e;
        }
        body { background-color: var(--primary-bg); color: var(--text-main); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background-color: var(--secondary-bg); border-bottom: 1px solid var(--card-border); }
        .card { background-color: var(--secondary-bg); border: 1px solid var(--card-border); border-radius: 12px; }
        .table { color: var(--text-main); }
        .table th { border-bottom: 2px solid var(--card-border); color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.8rem; }
        .table td { border-bottom: 1px solid var(--card-border); padding: 1rem 0.75rem; }
        .badge-status { font-size: 0.75rem; padding: 0.35rem 0.65rem; border-radius: 20px; }
        .brand-logo { font-weight: 800; color: var(--accent-color); font-size: 1.4rem; }
        .nav-link { color: var(--text-muted) !important; font-weight: 500; }
        .nav-link.active { color: #fff !important; }
        .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
        .modal-content { background-color: var(--secondary-bg); border: 1px solid var(--card-border); }
        .form-control, .form-select { background-color: var(--primary-bg); border-color: var(--card-border); color: var(--text-main); }
        .form-control:focus, .form-select:focus { background-color: var(--primary-bg); color: var(--text-main); border-color: var(--accent-color); box-shadow: none; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand brand-logo" href="index.php?action=admin_dashboard">Relioo Super Admin</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=admin_dashboard">Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?action=manage_companies">Quản lý Không gian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Audit Logs</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="index.php?action=logout" class="btn btn-outline-light btn-sm">Đăng xuất</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Quản lý Không gian Doanh nghiệp</h3>
        </div>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Công ty</th>
                            <th>Lĩnh vực</th>
                            <th>Hạn ngạch (User/Dept)</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($companies as $c): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($c['company_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($c['ceo_email']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($c['industry'] ?: 'N/A') ?></td>
                            <td>
                                <span class="badge bg-dark border border-secondary"><?= $c['max_users'] ?> Users</span>
                                <span class="badge bg-dark border border-secondary"><?= $c['max_departments'] ?> Depts</span>
                            </td>
                            <td>
                                <?php if($c['status'] == 'approved'): ?>
                                    <span class="badge bg-success-subtle text-success badge-status">Hoạt động</span>
                                <?php elseif($c['status'] == 'pending'): ?>
                                    <span class="badge bg-warning-subtle text-warning badge-status">Đang chờ</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger badge-status">Đã hủy</span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                            <td class="text-end">
                                <button class="btn btn-outline-primary btn-action me-1 btn-edit" 
                                    data-id="<?= $c['id'] ?>"
                                    data-name="<?= htmlspecialchars($c['company_name']) ?>"
                                    data-industry="<?= htmlspecialchars($c['industry']) ?>"
                                    data-users="<?= $c['max_users'] ?>"
                                    data-projects="<?= $c['max_projects'] ?>"
                                    data-depts="<?= $c['max_departments'] ?>"
                                    data-status="<?= $c['status'] ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-action btn-delete" data-id="<?= $c['id'] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav class="mt-4">
                <ul class="pagination pagination-sm justify-content-center">
                    <?php for($i=1; $i<=$totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link bg-dark border-secondary text-white" href="index.php?action=manage_companies&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title">Chỉnh sửa Không gian Doanh nghiệp</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="edit-form">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Tên Công ty</label>
                            <input type="text" name="company_name" id="edit-name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Lĩnh vực</label>
                            <input type="text" name="industry" id="edit-industry" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small">Max Users</label>
                                <input type="number" name="max_users" id="edit-users" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small">Max Projects</label>
                                <input type="number" name="max_projects" id="edit-projects" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted small">Max Depts</label>
                                <input type="number" name="max_departments" id="edit-depts" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Trạng thái</label>
                            <select name="status" id="edit-status" class="form-select">
                                <option value="approved">Hoạt động (Approved)</option>
                                <option value="pending">Đang chờ (Pending)</option>
                                <option value="rejected">Dừng hoạt động (Rejected)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary px-4">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.btn-edit').click(function() {
                let d = $(this).data();
                $('#edit-id').val(d.id);
                $('#edit-name').val(d.name);
                $('#edit-industry').val(d.industry);
                $('#edit-users').val(d.users);
                $('#edit-projects').val(d.projects);
                $('#edit-depts').val(d.depts);
                $('#edit-status').val(d.status);
                $('#editModal').modal('show');
            });

            $('#edit-form').submit(function(e) {
                e.preventDefault();
                $.post('index.php?action=api_update_company', $(this).serialize(), function(res) {
                    if(res.success) {
                        alert(res.message);
                        location.reload();
                    } else alert(res.message);
                }, 'json');
            });

            $('.btn-delete').click(function() {
                if(!confirm('CẢNH BÁO: Xóa không gian doanh nghiệp sẽ xóa TẤT CẢ nhân viên, phòng ban, dự án và dữ liệu liên quan. Hành động này không thể hoàn tác. Bạn có chắc chắn?')) return;
                let id = $(this).data('id');
                $.post('index.php?action=api_delete_company', { id: id }, function(res) {
                    if(res.success) location.reload();
                    else alert(res.message);
                }, 'json');
            });
        });
    </script>
</body>
</html>
