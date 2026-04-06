<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="alert border-0 bg-primary bg-opacity-10 relioo-card p-4 d-flex align-items-center gap-4 mb-0">
            <div class="fs-1 text-primary bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                <i class="bi bi-emoji-smile"></i>
            </div>
            <div>
                <h4 class="fw-bold text-primary mb-1">Chào mừng quay trở lại, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h4>
                <p class="mb-0 text-muted">Bắt đầu phiên làm việc mới thật hiệu quả với Relioo nhé.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="relioo-card p-4 text-center h-100 d-flex flex-column justify-content-center">
            <div class="icon-box bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                <i class="bi bi-kanban text-primary fs-3"></i>
            </div>
            <h6 class="text-muted text-uppercase fw-bold mb-2">Công việc của bạn</h6>
            <h2 class="fw-bold mb-0">0</h2>
            <p class="text-muted small">Cần giải quyết hôm nay</p>
            <a href="index.php?action=tasks" class="btn btn-outline-primary btn-sm mt-3 rounded-pill px-4 mx-auto" style="width: fit-content;">Đi tới Kanban</a>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="relioo-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="text-muted text-uppercase fw-bold mb-0">Thông báo mới nhất</h6>
                <button class="btn btn-sm btn-light border rounded-pill"><i class="bi bi-arrow-clockwise"></i> Load lại</button>
            </div>
            <div class="text-center py-5">
                <i class="bi bi-envelope-paper text-muted opacity-50" style="font-size: 3.5rem;"></i>
                <p class="mt-3 text-muted fw-medium">Bạn không có thông báo nào mới.</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
