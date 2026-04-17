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
        body { background-color: #212529; color: #fff; }
        .navbar { background-color: #343a40; border-bottom: 1px solid #495057; }
        .card { background-color: #343a40; border: 1px solid #495057; }
        .table { color: #fff; }
        .table-hover tbody tr:hover { color: #fff; background-color: #495057; }
        .table th { border-bottom-color: #6c757d; }
        .table td, .table th { border-top-color: #495057; }
        .brand-logo { font-weight: 700; color: #17a2b8; font-size: 1.5rem; letter-spacing: -1px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand brand-logo" href="index.php?action=admin_super_dashboard">Relioo Super Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?action=admin_super_dashboard">Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=manage_companies">Quản lý Không gian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Audit Logs</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="me-3 text-light small d-none d-md-inline">Xin chào, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
                    <a href="index.php?action=logout" class="btn btn-outline-light btn-sm">Đăng xuất</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center p-3 border-info">
                    <div class="text-muted small mb-1">Tổng Công ty</div>
                    <h2 class="mb-0"><?= $stats['total_companies'] ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3 border-success">
                    <div class="text-muted small mb-1">Tổng Người dùng</div>
                    <h2 class="mb-0"><?= $stats['total_users'] ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3 border-warning">
                    <div class="text-muted small mb-1">Dự án Đang chạy</div>
                    <h2 class="mb-0"><?= $stats['total_projects'] ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center p-3 border-primary">
                    <div class="text-muted small mb-1">Tổng Công việc</div>
                    <h2 class="mb-0"><?= $stats['total_tasks'] ?></h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <h4 class="mb-3"><i class="bi bi-clock-history"></i> Đơn đăng ký Đang chờ</h4>
                <div id="action-alert" class="alert d-none" role="alert"></div>

        <div class="card">
            <div class="card-body">
                <?php if(empty($pendingCompanies)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-2 mb-0">Không có đơn đăng ký nào đang chờ duyệt.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên Công ty</th>
                                    <th>Lĩnh vực</th>
                                    <th>Thông tin CEO</th>
                                    <th>Ngày đăng ký</th>
                                    <th class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pendingCompanies as $company): ?>
                                    <tr id="row-<?= $company['id'] ?>">
                                        <td>#<?= $company['id'] ?></td>
                                        <td class="fw-bold text-info"><?= htmlspecialchars($company['company_name']) ?></td>
                                        <td><?= htmlspecialchars($company['industry'] ?: 'Không xác định') ?></td>
                                        <td>
                                            <div><i class="bi bi-person"></i> <?= htmlspecialchars($company['ceo_name']) ?></div>
                                            <div><i class="bi bi-envelope"></i> <?= htmlspecialchars($company['ceo_email']) ?></div>
                                            <div><i class="bi bi-telephone"></i> <?= htmlspecialchars($company['ceo_phone'] ?: 'Không có') ?></div>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($company['created_at'])) ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-success btn-sm btn-approve" data-id="<?= $company['id'] ?>">
                                                <i class="bi bi-check-lg"></i> Duyệt
                                            </button>
                                            <button class="btn btn-danger btn-sm btn-reject" data-id="<?= $company['id'] ?>">
                                                <i class="bi bi-x-lg"></i> Từ chối
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-lg-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-megaphone"></i> Thông báo Toàn hệ thống (Global Broadcast)
                    </div>
                    <div class="card-body">
                        <form id="broadcast-form">
                            <div class="row">
                                <div class="col-md-8">
                                    <textarea name="content" class="form-control bg-dark text-white border-secondary" placeholder="Nhập nội dung thông báo cho tất cả người dùng hoặc CEO..." rows="2" required></textarea>
                                </div>
                                <div class="col-md-2">
                                    <select name="target" class="form-select bg-dark text-white border-secondary">
                                        <option value="all">Tất cả Users</option>
                                        <option value="ceos">Chỉ CEOs</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100 h-100">
                                        <i class="bi bi-send"></i> Gửi ngay
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function showAlert(message, isSuccess) {
                let $alert = $('#action-alert');
                $alert.text(message)
                      .removeClass('d-none alert-success alert-danger')
                      .addClass(isSuccess ? 'alert-success' : 'alert-danger');
                setTimeout(() => $alert.addClass('d-none'), 5000);
            }

            $('.btn-approve').click(function() {
                if(!confirm('Bạn có chắc chắn muốn Duyệt công ty này và tự động tạo tài khoản CEO?')) return;
                
                let id = $(this).data('id');
                let $btn = $(this);
                $btn.prop('disabled', true);
                
                $.post('index.php?action=api_approve_company', { id: id }, function(res) {
                    if(res.success) {
                        showAlert(res.message, true);
                        $('#row-' + id).fadeOut();
                    } else {
                        showAlert(res.message, false);
                        $btn.prop('disabled', false);
                    }
                }, 'json').fail(function() {
                    showAlert('Lỗi kết nối máy chủ', false);
                    $btn.prop('disabled', false);
                });
            });

            $('.btn-reject').click(function() {
                if(!confirm('Bạn có chắc chắn muốn Từ chối đơn đăng ký này?')) return;
                
                let id = $(this).data('id');
                let $btn = $(this);
                $btn.prop('disabled', true);
                
                $.post('index.php?action=api_reject_company', { id: id }, function(res) {
                    if(res.success) {
                        showAlert(res.message, true);
                        $('#row-' + id).fadeOut();
                    } else {
                        showAlert(res.message, false);
                        $btn.prop('disabled', false);
                    }
                }, 'json').fail(function() {
                    showAlert('Lỗi kết nối máy chủ', false);
                    $btn.prop('disabled', false);
                });
            });

            $('#broadcast-form').submit(function(e) {
                e.preventDefault();
                let $btn = $(this).find('button');
                let data = $(this).serialize();
                
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang gửi...');
                
                $.post('index.php?action=api_broadcast', data, function(res) {
                    if(res.success) {
                        alert(res.message);
                        $('#broadcast-form')[0].reset();
                    } else {
                        alert(res.message);
                    }
                    $btn.prop('disabled', false).html('<i class="bi bi-send"></i> Gửi ngay');
                }, 'json').fail(function() {
                    alert('Lỗi kết nối!');
                    $btn.prop('disabled', false).html('<i class="bi bi-send"></i> Gửi ngay');
                });
            });
        });
    </script>
</body>
</html>