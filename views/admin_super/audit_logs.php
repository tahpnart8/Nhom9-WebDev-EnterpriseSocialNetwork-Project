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
            --primary-bg: #f8f9fa;
            --secondary-bg: #ffffff;
            --accent-color: #0d6efd;
            --card-border: #dee2e6;
            --text-main: #212529;
            --text-muted: #6c757d;
        }
        body { background-color: var(--primary-bg); color: var(--text-main); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background-color: var(--secondary-bg); border-bottom: 1px solid var(--card-border); }
        .card { background-color: var(--secondary-bg); border: 1px solid var(--card-border); border-radius: 12px; }
        .table { color: var(--text-main); font-size: 0.9rem; }
        .table th { border-bottom: 2px solid var(--card-border); color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; }
        .table td { border-bottom: 1px solid var(--card-border); padding: 0.75rem; vertical-align: middle; }
        .brand-logo { font-weight: 800; color: var(--accent-color); font-size: 1.4rem; }
        .nav-link { color: var(--text-muted) !important; font-weight: 500; }
        .nav-link.active { color: var(--text-main) !important; font-weight: 600; }
        .badge-action { font-size: 0.7rem; border-radius: 4px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand brand-logo" href="index.php?action=admin_dashboard">Relioo Super Admin</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php?action=admin_dashboard">Analytics</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?action=manage_companies">Quản lý Không gian</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php?action=audit_logs">Audit Logs</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="index.php?action=logout" class="btn btn-outline-danger btn-sm">Đăng xuất</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <h3 class="mb-4">Nhật ký Hoạt động Hệ thống</h3>

        <div class="card p-3 shadow-sm">
            <?php if(empty($logs)): ?>
                <div class="text-center py-5 text-muted">Chưa có hoạt động nào được ghi lại.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Người thực hiện</th>
                                <th>Công ty</th>
                                <th>Hành động</th>
                                <th>Chi tiết</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td class="text-nowrap"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($log['user_name'] ?: 'System') ?></div>
                                    <div class="small text-muted">ID: <?= $log['user_id'] ?></div>
                                </td>
                                <td><?= htmlspecialchars($log['company_name'] ?: 'System/SuperAdmin') ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle badge-action">
                                        <?= htmlspecialchars($log['action_type']) ?>
                                    </span>
                                </td>
                                <td style="max-width: 300px;" class="text-truncate" title="<?= htmlspecialchars($log['details']) ?>">
                                    <?= htmlspecialchars($log['details']) ?>
                                </td>
                                <td class="small text-muted"><?= htmlspecialchars($log['ip_address']) ?></td>
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
                                <a class="page-link bg-white border-secondary text-dark" href="index.php?action=audit_logs&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
