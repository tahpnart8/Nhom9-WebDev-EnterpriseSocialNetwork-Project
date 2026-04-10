<?php include __DIR__ . '/../layouts/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<style>
/* ===== LAYOUT TỔNG THỂ ===== */
.tasks-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    height: calc(100vh - 100px);
}

/* ===== TABS CHUYỂN ĐỔI ===== */
.view-switcher {
    background: #e2e8f0;
    padding: 0.3rem;
    border-radius: 0.8rem;
    display: inline-flex;
}
.view-btn {
    padding: 0.5rem 1.5rem;
    border-radius: 0.6rem;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    background: transparent;
    color: #64748b;
    transition: 0.2s;
}
.view-btn.active {
    background: white;
    color: #1e293b;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* ===== BOARD CHUNG ===== */
.board-container {
    flex-grow: 1;
    overflow: auto; /* Cho phép board cuộn cả ngang và dọc */
    display: flex;
}

/* CHẾ ĐỘ 1: TIẾN ĐỘ (FIT 4 CỘT) */
.board-progress {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* Chia đều đúng 4 cột */
    gap: 1rem;
    width: 100%;
    min-width: 1000px; /* Đảm bảo grid không quá hẹp trên màn hình nhỏ */
}

/* CHẾ ĐỘ 2: THEO TASK (SCROLL BOARD) */
.board-tasks {
    display: flex;
    gap: 1rem;
    padding-bottom: 0.5rem;
}

/* Tùy chỉnh thanh cuộn ngang/dọc của Board */
.board-container::-webkit-scrollbar { height: 8px; width: 8px; }
.board-container::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
.board-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

/* CỘT CHUNG */
.column {
    background: #f8fafc;
    border-radius: 1rem;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    height: fit-content; /* Cột giãn theo nội dung */
    min-height: 100%; /* Nhưng tối thiểu vẫn cao bằng board */
}
.board-tasks .column {
    min-width: 300px; /* Cố định độ rộng để scroll ngang board */
    max-width: 320px;
}

.column-header {
    padding: 1rem;
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky; /* Giữ header luôn ở trên khi cuộn dọc board (nếu cần) */
    top: 0;
    background: #f8fafc;
    border-radius: 1rem 1rem 0 0;
    z-index: 10;
}
.column-header h6 {
    margin: 0;
    font-weight: 800;
    font-size: 0.8rem;
    text-transform: uppercase;
    color: #475569;
}
.card-list {
    flex-grow: 1;
    padding: 0.75rem;
    /* Bỏ overflow-y: auto để cuộn toàn bộ board */
}

/* THẺ CÔNG VIỆC */
.task-card {
    background: white;
    border-radius: 0.8rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    cursor: pointer;
    transition: 0.2s;
}
.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    border-color: #6366f1;
}
.task-card.rejected {
    background: #fff1f2;
    border-color: #fecaca;
}

.card-label {
    font-size: 0.65rem;
    font-weight: 800;
    color: #6366f1;
    text-transform: uppercase;
    margin-bottom: 0.3rem;
}
.card-name {
    font-weight: 700;
    font-size: 0.9rem;
    color: #1e293b;
    margin-bottom: 0.5rem;
    /* Giới hạn 2 dòng tiêu đề */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.card-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.6rem;
    border-top: 1px solid #f1f5f9;
}
.assignee {
    font-size: 0.7rem;
    color: #64748b;
    font-weight: 600;
}
.priority-indicator {
    width: 8px; height: 8px; border-radius: 50%;
}

.status-todo { border-top: 4px solid #94a3b8; }
.status-progress { border-top: 4px solid #3b82f6; }
.status-pending { border-top: 4px solid #f59e0b; }
.status-done { border-top: 4px solid #10b981; }
</style>

<div class="tasks-wrapper">
    <!-- Header bar -->
    <div class="d-flex justify-content-between align-items-center">
        <div class="view-switcher">
            <button class="view-btn active" onclick="switchView('progress', this)">Quản lý theo tiến độ</button>
            <button class="view-btn" onclick="switchView('tasks', this)">Quản lý theo Task</button>
        </div>
        
        <?php if($_SESSION['role_id'] != 3): ?>
        <button class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createTaskModal">
            <i class="bi bi-plus-lg me-1"></i> Tạo Task mới
        </button>
        <?php endif; ?>
    </div>

    <div class="board-container">
        <!-- VIEW 1: TIẾN ĐỘ (GRID 4 CỘT) -->
        <div id="board-progress" class="board-progress">
            <?php 
            $metas = [
                'To Do' => ['label' => 'Cần làm', 'class' => 'status-todo'],
                'In Progress' => ['label' => 'Đang làm', 'class' => 'status-progress'],
                'Pending' => ['label' => 'Chờ duyệt', 'class' => 'status-pending'],
                'Done' => ['label' => 'Hoàn thành', 'class' => 'status-done']
            ];
            foreach ($metas as $status => $m):
            ?>
            <div class="column <?php echo $m['class']; ?>">
                <div class="column-header">
                    <h6><?php echo $m['label']; ?></h6>
                    <span class="badge bg-light text-muted rounded-pill"><?php echo count($columns[$status]); ?></span>
                </div>
                <div class="card-list" data-status="<?php echo $status; ?>">
                    <?php foreach ($columns[$status] as $c): ?>
                    <div class="task-card <?php echo (($c['is_rejected'] ?? 0) && ($c['status'] ?? '') == 'To Do') ? 'rejected' : ''; ?>" 
                         data-id="<?php echo $c['id']; ?>" onclick="openSubtaskDetail(<?php echo $c['id']; ?>)">
                        <div class="card-label text-truncate"><?php echo htmlspecialchars($c['task_title']); ?></div>
                        <div class="card-name"><?php echo htmlspecialchars($c['title']); ?></div>
                        <div class="card-info">
                            <span class="assignee"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($c['assignee_name']); ?></span>
                            <div class="priority-indicator bg-<?php echo strtolower($c['priority'] ?? 'secondary'); ?>"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- VIEW 2: THEO TASK (SCROLL BOARD) -->
        <div id="board-tasks" class="board-tasks d-none">
            <?php foreach ($tasksWithSubtasks as $task): ?>
            <div class="column">
                <div class="column-header pointer" onclick="openTaskDetail(<?php echo $task['id']; ?>)">
                    <h6 class="text-truncate" style="max-width: 80%;"><i class="bi bi-folder2 me-2"></i><?php echo htmlspecialchars($task['title']); ?></h6>
                    <span class="badge bg-light text-muted rounded-pill"><?php echo count($task['subtasks']); ?></span>
                </div>
                <div class="card-list">
                    <?php foreach ($task['subtasks'] as $c): ?>
                    <div class="task-card <?php echo (($c['is_rejected'] ?? 0) && ($c['status'] ?? '') == 'To Do') ? 'rejected' : ''; ?>" 
                         onclick="openSubtaskDetail(<?php echo $c['id']; ?>)">
                        <div class="card-name"><?php echo htmlspecialchars($c['title']); ?></div>
                        <div class="card-info">
                            <span class="assignee"><?php echo htmlspecialchars($c['assignee_name']); ?></span>
                            <span class="badge bg-light text-dark border extra-small" style="font-size: 0.6rem;"><?php echo $c['status']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if($_SESSION['role_id'] != 3): ?>
                    <button class="btn btn-link btn-sm w-100 text-muted mt-2 fw-bold text-decoration-none" onclick="openTaskDetail(<?php echo $task['id']; ?>)">+ Thêm việc...</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- MODALS -->
<div class="modal fade" id="subtaskDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.2rem;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-primary">CHI TIẾT CÔNG VIỆC</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3" id="subtaskBody"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="taskDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.2rem;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">DỰ ÁN / TASK LỚN</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3" id="taskBody"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0"><h6 class="modal-title fw-bold">TẠO TASK MỚI</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="formTask">
                    <div class="mb-3"><label class="form-label small fw-bold">TIÊU ĐỀ</label><input type="text" name="title" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="form-label small fw-bold">MÔ TẢ</label><textarea name="description" class="form-control rounded-3" rows="3"></textarea></div>
                    <div class="row">
                        <div class="col-6"><label class="form-label small fw-bold">ƯU TIÊN</label><select name="priority" class="form-select rounded-3"><option value="Low">Thấp</option><option value="Medium" selected>Trung bình</option><option value="High">Cao</option></select></div>
                        <div class="col-6"><label class="form-label small fw-bold">HẠN CHÓT</label><input type="date" name="deadline" class="form-control rounded-3"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0"><button class="btn btn-primary w-100 rounded-pill fw-bold" onclick="saveTask()">TẠO NGAY</button></div>
        </div>
    </div>
</div>

<script>
function switchView(view, btn) {
    $('.view-btn').removeClass('active');
    $(btn).addClass('active');
    if(view === 'progress') {
        $('#board-progress').removeClass('d-none');
        $('#board-tasks').addClass('d-none');
    } else {
        $('#board-progress').addClass('d-none');
        $('#board-tasks').removeClass('d-none');
    }
}

$(function() {
    // Sortable
    document.querySelectorAll('.card-list').forEach(el => {
        new Sortable(el, {
            group: 'kanban',
            animation: 150,
            onEnd: function(evt) {
                let id = evt.item.dataset.id;
                let status = evt.to.dataset.status;
                if(!id || !status) return;
                $.post('index.php?action=api_update_subtask_status', {subtask_id: id, status: status}, function(res) {
                    if(!res.success) { alert(res.message); location.reload(); }
                }, 'json');
            }
        });
    });
});

function saveTask() {
    $.post('index.php?action=api_create_task', $('#formTask').serialize(), function(res) {
        if(res.success) location.reload(); else alert(res.message);
    }, 'json');
}

function openSubtaskDetail(id) {
    var modal = new bootstrap.Modal(document.getElementById('subtaskDetailModal'));
    modal.show();
    $.getJSON('index.php?action=api_subtask_detail&id='+id, function(res) {
        if(res.success) {
            let s = res.data;
            let feedbackHtml = '';
            if (s.is_rejected == 1 && s.feedback) {
                feedbackHtml = `<div class="alert alert-danger py-2 small fw-bold"><i class="bi bi-info-circle me-1"></i>Lý do từ chối: ${s.feedback}</div>`;
            }
            
            let attachmentsHtml = '';
            if (s.attachments && s.attachments.length > 0) {
                attachmentsHtml = '<div class="mb-3"><h6 class="extra-small fw-bold text-muted mb-2 uppercase">Minh chứng đính kèm</h6>';
                s.attachments.forEach(att => {
                    attachmentsHtml += `<div class="p-2 border rounded-3 bg-light mb-2 small">`;
                    if (att.file_url) {
                        attachmentsHtml += `<div class="mb-2"><img src="${att.file_url}" alt="Attachment" class="img-fluid rounded" style="max-height: 150px;"></div>`;
                    }
                    if (att.notes) {
                        attachmentsHtml += `<div>Ghi chú: <span class="fw-bold">${att.notes}</span></div>`;
                    }
                    attachmentsHtml += `</div>`;
                });
                attachmentsHtml += '</div>';
            }
            
            $('#subtaskBody').html(`
                <h5 class="fw-bold mb-1">${s.title}</h5>
                <p class="text-muted small mb-3">${s.task_title}</p>
                ${feedbackHtml}
                <div class="bg-light p-3 rounded-3 mb-3 small">
                    <div class="d-flex justify-content-between mb-2"><span>Người làm:</span><span class="fw-bold">${s.assignee_name}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Trạng thái:</span><span class="badge bg-primary">${s.status}</span></div>
                    <div class="d-flex justify-content-between">
                        <span>Hạn chót:</span>
                        <span class="fw-bold text-danger">${s.deadline ? s.deadline : 'Không có'}</span>
                    </div>
                </div>
                <div class="mb-4 small" style="white-space: pre-wrap;">${s.description || 'Không có mô tả.'}</div>
                ${attachmentsHtml}
                <div id="sub-actions">${renderSubActions(s)}</div>
            `);
        }
    });
}

function renderSubActions(s) {
    let uid = <?php echo $_SESSION['user_id']; ?>;
    let rid = <?php echo $_SESSION['role_id']; ?>;
    if(s.assignee_id == uid) {
        if(s.status == 'To Do') return `<button class="btn btn-primary w-100 rounded-pill fw-bold" onclick="doStatus(${s.id}, 'In Progress')">BẮT ĐẦU LÀM</button>`;
        if(s.status == 'In Progress') {
            return `
                <form id="evidForm" onsubmit="submitEv(event, ${s.id})">
                    <label class="small fw-bold mb-1">Gửi thông tin minh chứng</label>
                    <textarea name="notes" class="form-control mb-2" placeholder="Nhập Link hoặc Ghi chú..."></textarea>
                    <input type="file" name="evidence_file" class="form-control mb-3" accept="image/*">
                    <button type="submit" class="btn btn-warning w-100 rounded-pill fw-bold">GỬI DUYỆT</button>
                </form>
            `;
        }
    }
    if((rid == 1 || rid == 2 || rid == 4) && s.status == 'Pending') {
        return `<div class="d-flex gap-2"><button type="button" class="btn btn-success flex-fill rounded-pill fw-bold" onclick="approve(${s.id})">DUYỆT</button><button type="button" class="btn btn-danger flex-fill rounded-pill fw-bold" onclick="reject(${s.id})">TỪ CHỐI</button></div>`;
    }
    return s.status == 'Done' ? `<div class="alert alert-success py-2 text-center fw-bold">Đã hoàn thành!</div>` : '';
}

function openTaskDetail(tid) {
    var modal = new bootstrap.Modal(document.getElementById('taskDetailModal'));
    modal.show();
    let tasks = <?php echo json_encode($tasksWithSubtasks); ?>;
    let task = tasks.find(t => t.id == tid);
    let staff = <?php echo json_encode($staffList); ?>;

    let list = task.subtasks.map(s => {
        let delBtn = (<?php echo $_SESSION['role_id']; ?> <= 2 || <?php echo $_SESSION['role_id']; ?> == 4) ? `<i class="bi bi-trash text-danger ms-2 pointer" onclick="deleteSub(${s.id})" title="Xóa"></i>` : '';
        return `<div class="p-2 border-bottom small d-flex justify-content-between align-items-center"><span>${s.title}</span><div class="text-muted">${s.assignee_name} ${delBtn}</div></div>`;
    }).join('');

    $('#taskBody').html(`
        <div class="row">
            <div class="col-md-5 border-end">
                <h6 class="fw-bold mb-3">${task.title}</h6>
                <div class="small text-muted mb-3">${task.description || 'N/A'}</div>
                <p class="small fw-bold text-primary"><i class="bi bi-person me-1"></i>${task.creator_name || 'Trưởng phòng'}</p>
            </div>
            <div class="col-md-7">
                <p class="extra-small fw-bold text-muted mb-2 uppercase">DANH SÁCH VIỆC CON</p>
                <div class="border rounded-3 mb-3" style="max-height: 200px; overflow-y: auto;">${list || '<p class="p-3 text-muted">Chưa có việc.</p>'}</div>
                <?php if($_SESSION['role_id'] != 3): ?>
                <div class="bg-light p-3 rounded-3">
                    <form id="quickS">
                        <input type="hidden" name="task_id" value="${task.id}">
                        <input type="text" name="title" class="form-control form-control-sm mb-2" placeholder="Tên việc mới..." required>
                        <select name="assignee_id" class="form-select form-select-sm mb-2" required>
                            <option value="">-- Giao cho --</option>
                            ${staff.map(u => `<option value="${u.id}">${u.full_name}</option>`).join('')}
                        </select>
                        <button type="button" class="btn btn-primary btn-sm w-100 fw-bold" onclick="quickSub()">GIAO VIỆC</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    `);
}

function quickSub() { $.post('index.php?action=api_create_subtask', $('#quickS').serialize(), function(r) { if(r.success) location.reload(); }); }
function doStatus(id, s) { $.post('index.php?action=api_update_subtask_status', {subtask_id: id, status: s}, function(res) { if(!res.success) alert(res.message); else location.reload(); }, 'json'); }
function submitEv(e, id) { 
    e.preventDefault();
    let fd = new FormData(document.getElementById('evidForm'));
    fd.append('subtask_id', id);
    $.ajax({
        url: 'index.php?action=api_submit_evidence',
        type: 'POST', data: fd, processData: false, contentType: false,
        success: function(res) {
            if(res.success) location.reload(); else alert(res.message);
        }
    });
}
function approve(id) { $.post('index.php?action=api_approve_subtask', {subtask_id: id}, function(res) { if(res.success) location.reload(); else alert(res.message); }, 'json'); }
function reject(id) { 
    let r = prompt('Lý do từ chối:');
    if(r) $.post('index.php?action=api_reject_subtask', {subtask_id: id, reason: r}, function(res) { if(res.success) location.reload(); else alert(res.message); }, 'json'); 
}
function deleteSub(id) {
    if(confirm('Bạn có chắc muốn xóa công việc con này không? Mọi thông tin minh chứng sẽ bị mất.')) {
        $.post('index.php?action=api_delete_subtask', {subtask_id: id}, function(res) {
            if(res.success) location.reload(); else alert(res.message);
        }, 'json');
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
