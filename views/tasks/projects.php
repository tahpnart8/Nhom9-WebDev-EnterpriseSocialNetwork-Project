<?php
$roleId = $_SESSION['role_id'];
// Fetch departments for CEO's Create Project modal
$departments = [];
if ($roleId == 1) {
    require_once __DIR__ . '/../../models/Department.php';
    $deptModel = new Department($this->db);
    $departments = $deptModel->getAll($_SESSION['company_id']);
}
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-briefcase text-primary me-2"></i>Quản lý Dự án</h1>
            <p class="text-muted mb-0">Quản lý và giám sát các dự án trong công ty</p>
        </div>
        <div>
            <?php if ($roleId == 1): ?>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                    <i class="bi bi-plus-lg me-1"></i> Dự án mới
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Danh sách Dự án -->
    <div class="row g-4">
        <?php if (empty($projects)): ?>
            <div class="col-12 text-center py-5">
                <img src="https://cdni.iconscout.com/illustration/premium/thumb/folder-is-empty-4064360-3363921.png" width="150" class="mb-3 opacity-50" alt="Empty">
                <h5 class="text-muted">Chưa có dự án nào!</h5>
            </div>
        <?php else: ?>
            <?php foreach ($projects as $proj): 
                $totalTasks = isset($proj['task_count']) ? $proj['task_count'] : ($proj['total_dept_tasks'] ?? 0);
                $approvedTasks = isset($proj['approved_task_count']) ? $proj['approved_task_count'] : ($proj['approved_dept_tasks'] ?? 0);
                $progress = $totalTasks > 0 ? round(($approvedTasks / $totalTasks) * 100) : 0;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; transition: transform 0.2s;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title fw-bold text-dark mb-0 text-truncate" title="<?= htmlspecialchars($proj['title']) ?>">
                                <?= htmlspecialchars($proj['title']) ?>
                            </h5>
                            <?php if ($proj['status'] == 'Completed'): ?>
                                <span class="badge bg-success rounded-pill px-3">Hoàn thành</span>
                            <?php else: ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">Đang chạy</span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="text-muted small mb-3" style="min-height: 40px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($proj['description']) ?>
                        </p>

                        <!-- Progress -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Tiến độ (Duyệt: <?= $approvedTasks ?>/<?= $totalTasks ?>)</span>
                                <span class="fw-bold"><?= $progress ?>%</span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 3px;">
                                <div class="progress-bar bg-<?= $progress == 100 ? 'success' : 'primary' ?>" role="progressbar" style="width: <?= $progress ?>%;"></div>
                            </div>
                        </div>

                        <!-- Departments List -->
                        <div class="mb-3">
                            <i class="bi bi-diagram-3 text-muted me-1"></i>
                            <span class="small text-muted">
                                <?php 
                                    if (isset($proj['assigned_departments'])) {
                                        echo htmlspecialchars($proj['assigned_departments']);
                                    } elseif (isset($_SESSION['department_name'])) {
                                        echo htmlspecialchars($_SESSION['department_name']);
                                    }
                                ?>
                            </span>
                        </div>

                        <div class="d-flex gap-2 mt-auto pt-3 border-top">
                            <a href="index.php?action=tasks&project_id=<?= $proj['id'] ?>" class="btn btn-light btn-sm w-100 fw-medium text-primary">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Vào dự án
                            </a>
                            
                            <?php if ($roleId == 1): ?>
                                <?php if ($proj['status'] != 'Completed'): ?>
                                    <button class="btn btn-sm px-3 d-flex align-items-center gap-1 fw-bold text-white border-0 shadow-sm" 
                                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px;" 
                                            onclick="completeProject(<?= $proj['id'] ?>)" 
                                            title="Tổng kết dự án bằng AI">
                                        <i class="bi bi-magic"></i> Done
                                    </button>
                                <?php endif; ?>
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm px-2 text-muted" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm" style="border-radius: 8px;">
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="editProject(<?= $proj['id'] ?>)"><i class="bi bi-pencil me-2 text-warning"></i> Chỉnh sửa</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteProject(<?= $proj['id'] ?>)"><i class="bi bi-trash me-2"></i> Xóa</a></li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php if ($roleId == 1): ?>
<!-- Modal Create/Edit Project -->
<div class="modal fade" id="createProjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="projectForm" class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="projectModalTitle">Tạo Dự án Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="project_id" name="project_id">
                <div class="mb-3">
                    <label class="form-label fw-medium">Tên dự án <span class="text-danger">*</span></label>
                    <input type="text" class="form-control bg-light" id="project_title" name="title" required placeholder="Ví dụ: Chiến dịch Marketing Q3">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Phòng ban tham gia <span class="text-danger">*</span></label>
                    <select class="form-select bg-light" id="project_depts" multiple size="4" required>
                        <?php foreach($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Giữ Ctrl (Windows) hoặc Cmd (Mac) để chọn nhiều</small>
                    <input type="hidden" name="department_ids" id="department_ids_input">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Mô tả mục tiêu dự án <span class="text-danger">*</span></label>
                    <textarea class="form-control bg-light" id="project_desc" name="description" rows="3" required placeholder="Mục tiêu của dự án này là gì?"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Lưu Dự án</button>
            </div>
        </form>
    </div>
</div>
<!-- Modal Tổng kết Dự án (CEO Preview) -->
<div class="modal fade" id="projectSummaryModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.2rem;">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-robot me-2"></i>Tổng Kết Dự Án (CEO & Relioo AI)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <input type="hidden" id="summaryProjectId">
                <div id="projectSummaryLoading" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-muted">AI đang thu thập dữ liệu từ các phòng ban và soạn thảo báo cáo tổng thể...</p>
                </div>
                <div id="projectSummaryPreview" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2"><i class="bi bi-magic me-1"></i> Dự thảo báo cáo CEO hoàn tất</span>
                        <small class="text-muted">CEO có thể chỉnh sửa lại nội dung trước khi xuất bản</small>
                    </div>
                    <textarea id="projectAiSummaryContent" class="form-control mb-3 border-0 shadow-sm" rows="15" style="border-radius: 12px; font-size: 0.95rem; line-height: 1.6;"></textarea>
                    
                    <div class="alert alert-warning py-3 small mb-4 shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #f59e0b !important;">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i> Bài viết này sẽ được đăng lên <b>Bảng tin chung</b>. Dự án sẽ chuyển sang trạng thái <b>Hoàn thành</b> và không thể chỉnh sửa các công việc bên trong nữa.
                    </div>
                    <button class="btn btn-primary w-100 fw-bold py-3 shadow" onclick="finalizeCompleteProject()" id="btnConfirmComplete">
                        <i class="bi bi-check2-circle me-1"></i> XÁC NHẬN & HOÀN THÀNH DỰ ÁN
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('projectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const pid = document.getElementById('project_id').value;
    const url = pid ? 'index.php?action=api_update_project' : 'index.php?action=api_create_project';
    
    // Get multiple select values
    const selectedDepts = Array.from(document.getElementById('project_depts').selectedOptions).map(o => o.value);
    if(selectedDepts.length === 0) {
        Swal.fire('Lỗi', 'Vui lòng chọn ít nhất 1 phòng ban!', 'error');
        return;
    }
    document.getElementById('department_ids_input').value = selectedDepts.join(',');
    
    const formData = new FormData(this);
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang lưu...';
    
    fetch(url, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Thành công', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Lỗi', data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i> Lưu Dự án';
        }
    })
    .catch(err => {
        Swal.fire('Lỗi', 'Không thể kết nối máy chủ', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Lưu Dự án';
    });
});

function completeProject(id) {
    // Lưu lại ID dự án hiện tại để dùng cho bước sau
    document.getElementById('summaryProjectId').value = id;
    
    // Reset và hiện modal
    $('#projectSummaryLoading').removeClass('d-none');
    $('#projectSummaryPreview').addClass('d-none');
    $('#projectAiSummaryContent').val('');
    
    const summaryModal = new bootstrap.Modal(document.getElementById('projectSummaryModal'));
    summaryModal.show();

    // Call API generate nháp
    let formData = new FormData();
    formData.append('project_id', id);
    
    fetch('index.php?action=api_generate_project_summary', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            $('#projectSummaryLoading').addClass('d-none');
            $('#projectSummaryPreview').removeClass('d-none');
            $('#projectAiSummaryContent').val(data.data);
        } else {
            summaryModal.hide();
            Swal.fire('Lỗi', data.message, 'error');
        }
    })
    .catch(() => {
        summaryModal.hide();
        Swal.fire('Lỗi', 'Không thể kết nối API AI', 'error');
    });
}

function finalizeCompleteProject() {
    const id = document.getElementById('summaryProjectId').value;
    const aiContent = document.getElementById('projectAiSummaryContent').value.trim();
    
    if(!aiContent) {
        Swal.fire('Thiếu nội dung', 'Vui lòng không để trống báo cáo tổng kết!', 'warning');
        return;
    }

    const btn = document.getElementById('btnConfirmComplete');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang đăng bài & Hoàn tất...';

    let formData = new FormData();
    formData.append('project_id', id);
    formData.append('ai_content', aiContent);

    fetch('index.php?action=api_complete_project', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Thành công', data.message, 'success').then(() => location.reload());
        } else {
            if(data.require_force) {
                Swal.fire({
                    title: 'Cảnh báo: Có Task chưa được duyệt!',
                    text: "Vẫn còn công việc chưa được phê duyệt. Bạn có muốn ép buộc hoàn thành?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ép buộc Hoàn thành',
                    confirmButtonColor: '#ef4444'
                }).then((r2) => {
                    if(r2.isConfirmed) {
                        formData.append('force', '1');
                        fetch('index.php?action=api_complete_project', { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(d2 => {
                            if(d2.success) Swal.fire('Thành công', d2.message, 'success').then(() => location.reload());
                            else Swal.fire('Lỗi', d2.message, 'error');
                        });
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> XÁC NHẬN & HOÀN THÀNH DỰ ÁN';
                    }
                });
            } else {
                Swal.fire('Lỗi', data.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> XÁC NHẬN & HOÀN THÀNH DỰ ÁN';
            }
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> XÁC NHẬN & HOÀN THÀNH DỰ ÁN';
        Swal.fire('Lỗi', 'Mất kết nối server', 'error');
    });
}

function deleteProject(id) {
    Swal.fire({
        title: 'Xóa dự án?',
        text: "Tất cả các Task và Subtask liên quan sẽ bị xóa vĩnh viễn. Hành động này không thể hoàn tác!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Xóa vĩnh viễn',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('project_id', id);
            fetch('index.php?action=api_delete_project', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Đã xóa!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Lỗi', data.message, 'error');
                }
            });
        }
    });
}

function editProject(id) {
    fetch('index.php?action=api_get_project_detail&id=' + id)
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            document.getElementById('project_id').value = res.data.id;
            document.getElementById('project_title').value = res.data.title;
            document.getElementById('project_desc').value = res.data.description;
            document.getElementById('projectModalTitle').innerText = 'Chỉnh sửa Dự án';
            
            // Set multi select
            const select = document.getElementById('project_depts');
            Array.from(select.options).forEach(opt => opt.selected = false);
            
            if(res.data.department_ids) {
                const depts = res.data.department_ids.split(',');
                Array.from(select.options).forEach(opt => {
                    if(depts.includes(opt.value)) opt.selected = true;
                });
            }
            
            new bootstrap.Modal(document.getElementById('createProjectModal')).show();
        } else {
            Swal.fire('Lỗi', 'Không tải được dữ liệu', 'error');
        }
    });
}
// Reset modal on close
document.getElementById('createProjectModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('project_id').value = '';
    document.getElementById('projectForm').reset();
    document.getElementById('projectModalTitle').innerText = 'Tạo Dự án Mới';
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
