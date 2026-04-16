<?php include __DIR__ . '/../layouts/header.php'; ?>

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
                                <button class="btn btn-sm btn-light border text-muted"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-light border text-danger"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($deptList)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có phòng ban nào</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="relioo-card p-4 border bg-white h-100 position-relative overflow-hidden">
            <!-- Thêm yếu tố trang trí theo pattern Soft UI -->
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
                
                <form>
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold small">TÊN PHÒNG BAN</label>
                        <input type="text" class="form-control bg-light border-0 py-2" placeholder="VD: Phòng Hành Chính">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold small">TRÁCH NHIỆM CHÍNH</label>
                        <textarea class="form-control bg-light border-0 py-2" rows="4" placeholder="Nhập mô tả hoạt động của phòng..."></textarea>
                    </div>
                    <button type="button" class="btn btn-primary w-100 py-2 fw-medium shadow-sm">
                        Khởi tạo cấu trúc
                    </button>
                    <p class="text-center text-muted small mt-3 px-3">Submits via AJAX to controllers/AdminController.php trong phân hệ xử lý Data.</p>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
