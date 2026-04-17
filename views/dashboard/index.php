<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="glass-panel-scrollable h-100">
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="alert border-0 bg-primary relioo-card p-4 d-flex flex-column flex-md-row align-items-center gap-3 gap-md-4 mb-0 shadow-sm text-center text-md-start" style="background-color: var(--primary-color) !important;">
            <div class="fs-1 text-primary bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 70px; height: 70px;">
                <i class="bi bi-emoji-smile"></i>
            </div>
            <div>
                <h4 class="fw-bold text-white mb-2 mb-md-1 fs-5 fs-md-4">Chào mừng quay trở lại, <?php echo htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8'); ?>!</h4>
                <p class="mb-0 text-white opacity-75 small">Bắt đầu phiên làm việc mới thật hiệu quả với Relioo nhé.</p>
            </div>
        </div>
    </div>
    
    <?php if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2): ?>
        <!-- MASTER DASHBOARD: Thẻ số liệu -->
        <div class="col-md-4">
            <div class="relioo-card p-3 shadow-sm h-100 border-start border-primary border-4">
                <h6 class="text-muted text-uppercase fw-bold mb-2">Dự Án Đang Chạy</h6>
                <h2 class="fw-bold mb-0 text-primary"><?php echo intval($taskStats['active_projects'] ?? 0); ?> <span class="text-muted fs-6 fw-normal">/ <?php echo intval($taskStats['total_tasks'] ?? 0); ?> tổng</span></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="relioo-card p-3 shadow-sm h-100 border-start border-danger border-4">
                <h6 class="text-muted text-uppercase fw-bold mb-2">Báo Động Trễ Hạn</h6>
                <h2 class="fw-bold mb-0 text-danger"><?php echo intval($subtaskStats['overdue_subtasks'] ?? 0); ?> <span class="text-muted fs-6 fw-normal">việc</span></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="relioo-card p-3 shadow-sm h-100 border-start border-success border-4">
                <h6 class="text-muted text-uppercase fw-bold mb-2">Tỉ Lệ Hoàn Thành</h6>
                <?php 
                    $total = intval($subtaskStats['total_subtasks'] ?? 0);
                    $done = intval($subtaskStats['done_subtasks'] ?? 0);
                    $percent = $total > 0 ? round(($done / $total) * 100) : 0;
                ?>
                <h2 class="fw-bold mb-0 text-success"><?php echo $percent; ?>%</h2>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percent; ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="col-md-6">
            <div class="relioo-card p-4 shadow-sm h-100">
                <h6 class="fw-bold text-uppercase mb-4 mt-2 text-center text-muted">Tiến Độ Công Việc (Subtasks)</h6>
                <div style="height: 250px; display: flex; justify-content: center;">
                    <canvas id="progressPieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="relioo-card p-4 shadow-sm h-100">
                <h6 class="fw-bold text-uppercase mb-4 mt-2 text-center text-muted">Phân Bổ Khối Lượng Công Việc</h6>
                <div style="height: 250px;">
                    <canvas id="workloadBarChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Nhúng Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Pie Chart: Subtask Progress
            const pieCtx = document.getElementById('progressPieChart').getContext('2d');
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Cần làm', 'Đang xử lý', 'Chờ duyệt', 'Hoàn thành'],
                    datasets: [{
                        data: [
                            <?php echo intval($subtaskStats['todo_subtasks'] ?? 0); ?>,
                            <?php echo intval($subtaskStats['inprogress_subtasks'] ?? 0); ?>,
                            <?php echo intval($subtaskStats['pending_subtasks'] ?? 0); ?>,
                            <?php echo intval($subtaskStats['done_subtasks'] ?? 0); ?>
                        ],
                        backgroundColor: ['#6c757d', '#0d6efd', '#ffc107', '#198754']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Bar Chart: Workload
            const barCtx = document.getElementById('workloadBarChart').getContext('2d');
            <?php 
                $labels = [];
                $data = [];
                foreach ($workloadData as $row) {
                    $labels[] = $_SESSION['role_id']==1 ? ($row['dept_name'] ?? 'Không XĐ') : ($row['assignee_name'] ?? 'Không XĐ');
                    $data[] = $row['total_tasks'];
                }
            ?>
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        label: 'Số việc đảm nhận',
                        data: <?php echo json_encode($data); ?>,
                        backgroundColor: '#0d6efd',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                    plugins: { legend: { display: false } }
                }
            });
        </script>
        
    <?php else: ?>
        <!-- Giao diện STAFF -->
        <div class="col-md-4">
            <div class="relioo-card p-4 text-center h-100 d-flex flex-column justify-content-center shadow-sm">
                <div class="icon-box bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 60px; height: 60px;">
                    <i class="bi bi-kanban text-primary fs-3"></i>
                </div>
                <h6 class="text-muted text-uppercase fw-bold mb-2">Công việc đang làm</h6>
                <h2 class="fw-bold mb-0 text-primary"><?php echo intval($subtaskStats['inprogress_subtasks'] ?? 0); ?></h2>
                <p class="text-muted small mb-1 mt-2">Tổng số việc được giao: <?php echo intval($subtaskStats['total_subtasks'] ?? 0); ?></p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-3">
                    <a href="index.php?action=tasks" class="btn btn-outline-primary rounded-pill px-4">Đi tới Kanban</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="relioo-card p-4 h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="text-muted text-uppercase fw-bold mb-0">Hành động cần chú ý</h6>
                </div>
                <?php if (($subtaskStats['overdue_subtasks'] ?? 0) > 0): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-3">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        <div>
                            <strong>Cảnh báo:</strong> Bạn đang có <?php echo intval($subtaskStats['overdue_subtasks']); ?> công việc trễ hạn! Vui lòng đẩy nhanh tiến độ.
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success opacity-75" style="font-size: 3.5rem;"></i>
                        <p class="mt-3 text-muted fw-medium">Tiến độ của bạn rất tốt, không có việc trễ hạn.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
