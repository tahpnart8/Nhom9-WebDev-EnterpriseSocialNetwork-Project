<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="row g-4">
    <div class="col-12">
        <div class="relioo-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Danh sách nhân sự (Hệ thống)</h5>
                <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus"></i> Thêm Nhân Vên
                </button>
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
                                    <div class="avatar-circle shadow-sm" style="width:34px; height:34px; font-size:12px;">
                                        <?php echo mb_substr(trim($u['full_name']), 0, 1, 'UTF-8'); ?>
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
                                <button class="btn btn-sm btn-light border text-muted"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-light border text-danger"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($usersList)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Chưa có dữ liệu</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm Nhân Sư Mẫu -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold">Tạo tài khoản mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-4">
        <form id="formAddUser">
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">TÊN ĐĂNG NHẬP</label>
                <input type="text" class="form-control bg-light border-0" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted small fw-bold">HỌ VÀ TÊN</label>
                <input type="text" class="form-control bg-light border-0" required>
            </div>
        </form>
        <div class="alert alert-warning border-0 bg-warning bg-opacity-10 py-2 d-flex align-items-center gap-2 mb-0">
            <i class="bi bi-info-circle text-warning fs-5"></i>
            <span class="small text-muted">Mật khẩu mặc định sẽ là <b>password123</b></span>
        </div>
      </div>
      <div class="modal-footer border-top-0 pt-3">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Trở về</button>
        <button type="button" class="btn btn-primary rounded-pill px-4">Lưu Dữ Liệu</button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
