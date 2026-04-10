<?php include __DIR__ . '/../layouts/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    overflow: auto;
    display: flex;
}

/* CHẾ ĐỘ 1: TIẾN ĐỘ (FIT 5 CỘT) */
.board-progress {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.8rem;
    width: 100%;
    min-width: 1200px;
}

/* CHẾ ĐỘ 2: THEO TASK (SCROLL BOARD) */
.board-tasks {
    display: flex;
    gap: 1rem;
    padding-bottom: 0.5rem;
}

/* Tùy chỉnh thanh cuộn */
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
    height: fit-content;
    min-height: 100%;
}
.board-tasks .column {
    min-width: 300px;
    max-width: 340px;
}

.column-header {
    padding: 0.85rem 1rem;
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: #f8fafc;
    border-radius: 1rem 1rem 0 0;
    z-index: 10;
}
.column-header h6 {
    margin: 0;
    font-weight: 800;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #475569;
}
.card-list {
    flex-grow: 1;
    padding: 0.65rem;
    min-height: 60px;
}

/* THẺ CÔNG VIỆC */
.task-card {
    background: white;
    border-radius: 0.8rem;
    padding: 0.85rem;
    margin-bottom: 0.6rem;
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
/* Thẻ Done bị khóa cứng */
.task-card.locked-card {
    cursor: default;
    opacity: 0.75;
    user-select: none;
}
.task-card.locked-card:hover {
    transform: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    border-color: #e2e8f0;
}

.card-label {
    font-size: 0.6rem;
    font-weight: 800;
    color: #6366f1;
    text-transform: uppercase;
    margin-bottom: 0.2rem;
}
.card-name {
    font-weight: 700;
    font-size: 0.85rem;
    color: #1e293b;
    margin-bottom: 0.4rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.card-desc-preview {
    font-size: 0.7rem;
    color: #94a3b8;
    margin-bottom: 0.4rem;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.card-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.5rem;
    border-top: 1px solid #f1f5f9;
    gap: 0.3rem;
}
.assignee {
    font-size: 0.65rem;
    color: #64748b;
    font-weight: 600;
}
.priority-badge {
    font-size: 0.55rem;
    font-weight: 800;
    padding: 0.15rem 0.45rem;
    border-radius: 0.3rem;
    text-transform: uppercase;
}
.priority-high { background: #fee2e2; color: #dc2626; }
.priority-medium { background: #fef3c7; color: #d97706; }
.priority-low { background: #dcfce7; color: #16a34a; }
.deadline-text {
    font-size: 0.6rem;
    color: #94a3b8;
}
.deadline-overdue {
    color: #dc2626 !important;
    font-weight: 700;
}

/* KIỂU CỘT TIẾN ĐỘ */
.status-todo { border-top: 4px solid #94a3b8; }
.status-progress { border-top: 4px solid #3b82f6; }
.status-pending { border-top: 4px solid #f59e0b; }
.status-done { border-top: 4px solid #10b981; }
.status-overdue { border-top: 4px solid #ef4444; background: #fef2f2; }

/* PROGRESS BAR */
.task-progress {
    padding: 0.4rem 1rem 0.5rem;
    background: #f8fafc;
}
.progress-bar-wrap {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
    transition: width 0.4s ease;
}
.progress-bar-fill.complete {
    background: linear-gradient(90deg, #10b981, #34d399);
}
.progress-text {
    font-size: 0.6rem;
    color: #94a3b8;
    font-weight: 700;
    margin-top: 0.2rem;
    text-align: right;
}

/* Task hoàn thành 100% */
.task-completed-column {
    border: 2px solid #10b981 !important;
    background: #f0fdf4 !important;
}
.task-completed-column .column-header {
    background: #f0fdf4;
}

/* SUBTASK LIST TRONG MODAL (MÀU SẮC) */
.subtask-list-item {
    padding: 0.5rem 0.7rem;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.82rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: background 0.15s;
    border-left: 3px solid transparent;
}
.subtask-list-item:hover { background: #f8fafc; }
.subtask-list-item.st-done { border-left-color: #10b981; background: #f0fdf4; }
.subtask-list-item.st-progress { border-left-color: #f59e0b; background: #fffbeb; }
.subtask-list-item.st-overdue { border-left-color: #ef4444; background: #fef2f2; }
.subtask-list-item.st-todo { border-left-color: #94a3b8; }

/* DYNAMIC SUBTASK ROWS */
.subtask-row {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.6rem;
    padding: 0.7rem;
    margin-bottom: 0.5rem;
    position: relative;
}
.subtask-row .remove-row {
    position: absolute;
    top: 0.4rem;
    right: 0.5rem;
    color: #ef4444;
    cursor: pointer;
    font-size: 1rem;
}
.pointer { cursor: pointer; }
.uppercase { text-transform: uppercase; }
.extra-small { font-size: 0.7rem; }
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
        <!-- VIEW 1: TIẾN ĐỘ (GRID 5 CỘT) -->
        <div id="board-progress" class="board-progress">
            <?php 
            $metas = [
                'To Do'       => ['label' => 'Cần làm',    'class' => 'status-todo'],
                'In Progress' => ['label' => 'Đang làm',   'class' => 'status-progress'],
                'Pending'     => ['label' => 'Chờ duyệt',  'class' => 'status-pending'],
                'Done'        => ['label' => 'Hoàn thành',   'class' => 'status-done'],
                'Overdue'     => ['label' => 'Trễ hạn',    'class' => 'status-overdue']
            ];
            $now = date('Y-m-d H:i:s');
            foreach ($metas as $status => $m):
                $isDone = ($status === 'Done');
                $isOverdue = ($status === 'Overdue');
            ?>
            <div class="column <?php echo $m['class']; ?>">
                <div class="column-header">
                    <h6><?php echo $m['label']; ?></h6>
                    <span class="badge bg-light text-muted rounded-pill"><?php echo count($columns[$status]); ?></span>
                </div>
                <div class="card-list <?php echo $isDone ? 'done-column' : ''; ?> <?php echo $isOverdue ? 'overdue-column' : ''; ?>" 
                     data-status="<?php echo $status === 'Overdue' ? '' : $status; ?>">
                    <?php foreach ($columns[$status] as $c): 
                        $isCardOverdue = (!empty($c['deadline']) && $c['deadline'] < $now && $c['status'] != 'Done');
                    ?>
                    <div class="task-card <?php echo (($c['is_rejected'] ?? 0) && ($c['status'] ?? '') == 'To Do') ? 'rejected' : ''; ?> <?php echo $isDone ? 'locked-card' : ''; ?>" 
                         data-id="<?php echo $c['id']; ?>"
                         onclick="openSubtaskDetail(<?php echo $c['id']; ?>)">
                        <div class="card-label text-truncate"><?php echo htmlspecialchars($c['task_title']); ?></div>
                        <div class="card-name"><?php echo htmlspecialchars($c['title']); ?></div>
                        <?php if(!empty($c['description'])): ?>
                        <div class="card-desc-preview"><?php echo htmlspecialchars($c['description']); ?></div>
                        <?php endif; ?>
                        <div class="card-info">
                            <span class="assignee"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($c['assignee_name']); ?></span>
                            <div class="d-flex align-items-center gap-1">
                                <?php if(!empty($c['deadline'])): ?>
                                <span class="deadline-text <?php echo $isCardOverdue ? 'deadline-overdue' : ''; ?>">
                                    <i class="bi bi-clock me-1"></i><?php echo date('d/m', strtotime($c['deadline'])); ?>
                                </span>
                                <?php endif; ?>
                                <?php 
                                    $pClass = 'priority-medium';
                                    $pLabel = 'TB';
                                    $prio = $c['priority'] ?? 'Medium';
                                    if ($prio == 'High') { $pClass = 'priority-high'; $pLabel = 'Cao'; }
                                    elseif ($prio == 'Low') { $pClass = 'priority-low'; $pLabel = 'Thấp'; }
                                ?>
                                <span class="priority-badge <?php echo $pClass; ?>"><?php echo $pLabel; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- VIEW 2: THEO TASK (SCROLL BOARD) -->
        <div id="board-tasks" class="board-tasks d-none">
            <?php foreach ($tasksWithSubtasks as $task): 
                $sCount = isset($task['subtask_count']) ? (int)$task['subtask_count'] : count($task['subtasks']);
                $dCount = isset($task['done_count']) ? (int)$task['done_count'] : count(array_filter($task['subtasks'], function($s) { return $s['status'] == 'Done'; }));
                $pct = $sCount > 0 ? round(($dCount / $sCount) * 100) : 0;
                $isComplete = ($sCount > 0 && $pct == 100);
            ?>
            <div class="column <?php echo $isComplete ? 'task-completed-column' : ''; ?>">
                <div class="column-header pointer" onclick="openTaskDetail(<?php echo $task['id']; ?>)">
                    <h6 class="text-truncate" style="max-width: 75%;"><i class="bi bi-folder2 me-2"></i><?php echo htmlspecialchars($task['title']); ?></h6>
                    <span class="badge bg-light text-muted rounded-pill"><?php echo $sCount; ?></span>
                </div>
                <!-- Progress bar -->
                <div class="task-progress">
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill <?php echo $isComplete ? 'complete' : ''; ?>" style="width: <?php echo $pct; ?>%"></div>
                    </div>
                    <div class="progress-text"><?php echo $dCount; ?>/<?php echo $sCount; ?> (<?php echo $pct; ?>%)</div>
                </div>
                <div class="card-list">
                    <?php foreach ($task['subtasks'] as $c): 
                        $isCardOverdue = (!empty($c['deadline']) && $c['deadline'] < $now && $c['status'] != 'Done');
                    ?>
                    <div class="task-card <?php echo (($c['is_rejected'] ?? 0) && ($c['status'] ?? '') == 'To Do') ? 'rejected' : ''; ?>" 
                         onclick="openSubtaskDetail(<?php echo $c['id']; ?>)">
                        <div class="card-name"><?php echo htmlspecialchars($c['title']); ?></div>
                        <?php if(!empty($c['description'])): ?>
                        <div class="card-desc-preview"><?php echo htmlspecialchars($c['description']); ?></div>
                        <?php endif; ?>
                        <div class="card-info">
                            <span class="assignee"><?php echo htmlspecialchars($c['assignee_name']); ?></span>
                            <div class="d-flex align-items-center gap-1">
                                <?php if(!empty($c['deadline'])): ?>
                                <span class="deadline-text <?php echo $isCardOverdue ? 'deadline-overdue' : ''; ?>">
                                    <i class="bi bi-clock"></i> <?php echo date('d/m', strtotime($c['deadline'])); ?>
                                </span>
                                <?php endif; ?>
                                <?php
                                    $stClass = 'bg-secondary';
                                    if ($c['status'] == 'Done') $stClass = 'bg-success';
                                    elseif ($c['status'] == 'In Progress') $stClass = 'bg-primary';
                                    elseif ($c['status'] == 'Pending') $stClass = 'bg-warning text-dark';
                                    if ($isCardOverdue) $stClass = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $stClass; ?>" style="font-size: 0.55rem;"><?php echo $isCardOverdue ? 'Trễ hạn' : $c['status']; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if($_SESSION['role_id'] != 3): ?>
                    <button class="btn btn-link btn-sm w-100 text-muted mt-2 fw-bold text-decoration-none" onclick="openAddSubtaskModal(<?php echo $task['id']; ?>)">+ Thêm việc...</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ==================== MODALS ==================== -->

<!-- MODAL: Chi tiết Subtask -->
<div class="modal fade" id="subtaskDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.2rem;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-primary">CHI TIẾT CÔNG VIỆC</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3" id="subtaskBody"></div>
        </div>
    </div>
</div>

<!-- MODAL: Chi tiết Task -->
<div class="modal fade" id="taskDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.2rem;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">DỰ ÁN / TASK LỚN</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3" id="taskBody"></div>
        </div>
    </div>
</div>

<!-- MODAL: Tạo Task mới (nâng cấp - có subtask kèm) -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">TẠO TASK MỚI</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTask">
                    <!-- Thông tin Task -->
                    <div class="mb-3"><label class="form-label small fw-bold">TIÊU ĐỀ TASK</label><input type="text" name="title" class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="form-label small fw-bold">MÔ TẢ</label><textarea name="description" class="form-control rounded-3" rows="2"></textarea></div>
                    <div class="row mb-3">
                        <div class="col-6"><label class="form-label small fw-bold">ƯU TIÊN</label><select name="priority" class="form-select rounded-3"><option value="Low">Thấp</option><option value="Medium" selected>Trung bình</option><option value="High">Cao</option></select></div>
                        <div class="col-6"><label class="form-label small fw-bold">HẠN CHÓT</label><input type="date" name="deadline" class="form-control rounded-3"></div>
                    </div>
                    
                    <!-- Phần tạo subtask kèm -->
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold mb-0">CÔNG VIỆC CON (TÙY CHỌN)</label>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="addSubtaskRow('taskSubtaskList')">
                            <i class="bi bi-plus-lg me-1"></i>Thêm
                        </button>
                    </div>
                    <div id="taskSubtaskList"></div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-primary w-100 rounded-pill fw-bold" onclick="saveTask()">TẠO NGAY</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Thêm nhiều Subtask vào Task (trong board Quản lý theo Task) -->
<div class="modal fade" id="addSubtaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">THÊM CÔNG VIỆC CON</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAddSubtasks">
                    <input type="hidden" name="task_id" id="addSubtask_taskId">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold mb-0">DANH SÁCH VIỆC CẦN THÊM</label>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="addSubtaskRow('addSubtaskList')">
                            <i class="bi bi-plus-lg me-1"></i>Thêm dòng
                        </button>
                    </div>
                    <div id="addSubtaskList"></div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-primary w-100 rounded-pill fw-bold" onclick="saveMultipleSubtasks()">GIAO VIỆC</button>
            </div>
        </div>
    </div>
</div>


<script>
// ===== GLOBAL DATA =====
var STAFF_LIST = <?php echo json_encode($staffList); ?>;
var USER_ID = <?php echo $_SESSION['user_id']; ?>;
var ROLE_ID = <?php echo $_SESSION['role_id']; ?>;

// ===== VIEW SWITCH =====
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

// ===== SORTABLE SETUP =====
$(function() {
    // Sortable chỉ cho bảng tiến độ (board-progress)
    document.querySelectorAll('#board-progress .card-list').forEach(el => {
        let isDoneCol = el.classList.contains('done-column');
        let isOverdueCol = el.classList.contains('overdue-column');
        
        new Sortable(el, {
            group: 'kanban',
            animation: 150,
            // Khóa cứng cột Done & Overdue: không cho kéo ra
            filter: isDoneCol || isOverdueCol ? '.task-card' : '',
            onMove: function(evt) {
                // Chặn thả vào cột Done hoặc cột Overdue
                let toStatus = evt.to.dataset.status;
                if (toStatus === 'Done' || toStatus === '') return false;
                // Chặn kéo thẻ locked
                if (evt.dragged.classList.contains('locked-card')) return false;
            },
            onEnd: function(evt) {
                let id = evt.item.dataset.id;
                let status = evt.to.dataset.status;
                if(!id || !status) { location.reload(); return; }

                // Nếu kéo vào cột Pending -> xử lý đặc biệt
                if (status === 'Pending') {
                    handleDragToPending(id, evt);
                    return;
                }

                // Kéo bình thường (To Do <-> In Progress)
                $.post('index.php?action=api_update_subtask_status', {subtask_id: id, status: status}, function(res) {
                    if(!res.success) {
                        handleDragError(res.message);
                        location.reload();
                    }
                }, 'json').fail(function() { location.reload(); });
            }
        });
    });
});

function handleDragToPending(subtaskId, evt) {
    // Kiểm tra minh chứng trước
    $.getJSON('index.php?action=api_check_evidence&id=' + subtaskId, function(res) {
        if (res.has_evidence) {
            // Có minh chứng -> hiện confirm
            Swal.fire({
                title: 'Gửi duyệt công việc?',
                text: 'Bạn đã có minh chứng đính kèm. Xác nhận gửi duyệt cho Trưởng phòng?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Gửi duyệt',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('index.php?action=api_update_subtask_status', {subtask_id: subtaskId, status: 'Pending', confirm: '1'}, function(res) {
                        if(res.success) {
                            Swal.fire({title: 'Thành công!', text: 'Đã gửi duyệt công việc.', icon: 'success', timer: 1500, showConfirmButton: false}).then(() => location.reload());
                        } else { location.reload(); }
                    }, 'json');
                } else {
                    location.reload();
                }
            });
        } else {
            // Chưa có minh chứng
            Swal.fire({
                title: 'Chưa có minh chứng!',
                html: 'Bạn cần điền minh chứng làm việc <b>trước khi</b> gửi duyệt.<br>Hãy bấm vào thẻ công việc để bổ sung.',
                icon: 'warning',
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Đã hiểu'
            }).then(() => location.reload());
        }
    }).fail(function() { location.reload(); });
}

function handleDragError(msg) {
    if (msg === 'locked_done') {
        Swal.fire({title: 'Không thể thao tác!', text: 'Công việc đã hoàn thành không thể thay đổi trạng thái.', icon: 'error', confirmButtonColor: '#6366f1'});
    } else if (msg === 'no_evidence') {
        Swal.fire({title: 'Chưa có minh chứng!', text: 'Vui lòng bổ sung minh chứng trước khi gửi duyệt.', icon: 'warning', confirmButtonColor: '#f59e0b'});
    } else if (msg === 'permission_denied') {
        Swal.fire({title: 'Không có quyền!', text: 'Chỉ có người được giao mới có thể thao tác thẻ này.', icon: 'error', confirmButtonColor: '#6366f1'});
    } else if (msg === 'locked_pending') {
        Swal.fire({title: 'Đang chờ duyệt!', text: 'Thẻ đang chờ Trưởng phòng phê duyệt, không thể kéo đi.', icon: 'info', confirmButtonColor: '#6366f1'});
    } else if (msg === 'confirm_pending') {
        // handled separately
    } else {
        Swal.fire({title: 'Lỗi!', text: msg || 'Có lỗi xảy ra.', icon: 'error'});
    }
}

// ===== SAVE TASK (WITH SUBTASKS) =====
function saveTask() {
    let form = document.getElementById('formTask');
    let fd = new FormData(form);
    
    $.ajax({
        url: 'index.php?action=api_create_task',
        type: 'POST', data: fd, processData: false, contentType: false,
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                Swal.fire({title: 'Thành công!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false}).then(() => location.reload());
            } else {
                Swal.fire({title: 'Lỗi!', text: res.message, icon: 'error'});
            }
        }
    });
}

// ===== DYNAMIC SUBTASK ROWS =====
function addSubtaskRow(containerId) {
    let staffOptions = STAFF_LIST.map(u => `<option value="${u.id}">${u.full_name}${u.dept_name ? ' (' + u.dept_name + ')' : ''}</option>`).join('');
    let html = `
        <div class="subtask-row">
            <i class="bi bi-x-lg remove-row" onclick="this.closest('.subtask-row').remove()"></i>
            <div class="row g-2 mb-2">
                <div class="col-md-6"><input type="text" name="subtask_title[]" class="form-control form-control-sm" placeholder="Tên công việc con *" required></div>
                <div class="col-md-6"><select name="subtask_assignee[]" class="form-select form-select-sm" required><option value="">-- Giao cho --</option>${staffOptions}</select></div>
            </div>
            <div class="row g-2 mb-1">
                <div class="col-md-5"><input type="text" name="subtask_description[]" class="form-control form-control-sm" placeholder="Mô tả ngắn..."></div>
                <div class="col-md-4"><input type="date" name="subtask_deadline[]" class="form-control form-control-sm"></div>
                <div class="col-md-3"><select name="subtask_priority[]" class="form-select form-select-sm"><option value="Low">Thấp</option><option value="Medium" selected>TB</option><option value="High">Cao</option></select></div>
            </div>
        </div>
    `;
    document.getElementById(containerId).insertAdjacentHTML('beforeend', html);
}

// ===== OPEN ADD SUBTASK MODAL (FROM TASK BOARD) =====
function openAddSubtaskModal(taskId) {
    document.getElementById('addSubtask_taskId').value = taskId;
    document.getElementById('addSubtaskList').innerHTML = '';
    addSubtaskRow('addSubtaskList'); // Pre-add one row
    var modal = new bootstrap.Modal(document.getElementById('addSubtaskModal'));
    modal.show();
}

function saveMultipleSubtasks() {
    let form = document.getElementById('formAddSubtasks');
    let fd = new FormData(form);

    $.ajax({
        url: 'index.php?action=api_create_subtask',
        type: 'POST', data: fd, processData: false, contentType: false,
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                Swal.fire({title: 'Thành công!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false}).then(() => location.reload());
            } else {
                Swal.fire({title: 'Lỗi!', text: res.message, icon: 'error'});
            }
        }
    });
}

// ===== SUBTASK DETAIL MODAL =====
function openSubtaskDetail(id) {
    var modal = new bootstrap.Modal(document.getElementById('subtaskDetailModal'));
    modal.show();
    $('#subtaskBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');

    $.getJSON('index.php?action=api_subtask_detail&id='+id, function(res) {
        if(res.success) {
            let s = res.data;
            let now = new Date();
            let isOverdue = s.deadline && new Date(s.deadline) < now && s.status !== 'Done';
            
            let feedbackHtml = '';
            if (s.is_rejected == 1 && s.feedback) {
                feedbackHtml = `<div class="alert alert-danger py-2 small fw-bold"><i class="bi bi-exclamation-triangle me-1"></i>Lý do từ chối: ${s.feedback}</div>`;
            }
            
            let attachmentsHtml = '';
            if (s.attachments && s.attachments.length > 0) {
                attachmentsHtml = '<div class="mb-3"><h6 class="extra-small fw-bold text-muted mb-2 uppercase">MINH CHỨNG ĐÍNH KÈM</h6>';
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

            // Priority badge
            let pClass = 'priority-medium', pLabel = 'Trung bình';
            let prio = s.priority || 'Medium';
            if (prio == 'High') { pClass = 'priority-high'; pLabel = 'Cao'; }
            else if (prio == 'Low') { pClass = 'priority-low'; pLabel = 'Thấp'; }

            // Status badge
            let statusBadge = `<span class="badge bg-secondary">${s.status}</span>`;
            if (s.status == 'Done') statusBadge = '<span class="badge bg-success">Hoàn thành</span>';
            else if (s.status == 'In Progress') statusBadge = '<span class="badge bg-primary">Đang làm</span>';
            else if (s.status == 'Pending') statusBadge = '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
            if (isOverdue) statusBadge += ' <span class="badge bg-danger">Trễ hạn</span>';
            
            $('#subtaskBody').html(`
                <h5 class="fw-bold mb-1">${s.title}</h5>
                <p class="text-muted small mb-3"><i class="bi bi-folder me-1"></i>${s.task_title}</p>
                ${feedbackHtml}
                <div class="bg-light p-3 rounded-3 mb-3 small">
                    <div class="d-flex justify-content-between mb-2"><span>Người làm:</span><span class="fw-bold">${s.assignee_name}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Trạng thái:</span><span>${statusBadge}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Ưu tiên:</span><span class="priority-badge ${pClass}">${pLabel}</span></div>
                    <div class="d-flex justify-content-between">
                        <span>Hạn chót:</span>
                        <span class="fw-bold ${isOverdue ? 'text-danger' : ''}">${s.deadline ? new Date(s.deadline).toLocaleDateString('vi-VN') : 'Không có'}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="extra-small fw-bold text-muted mb-1 uppercase">MÔ TẢ</h6>
                    <div class="small p-2 bg-light rounded-3" style="white-space: pre-wrap;">${s.description || '<span class=\"text-muted\">Không có mô tả.</span>'}</div>
                </div>
                ${attachmentsHtml}
                <div id="sub-actions">${renderSubActions(s)}</div>
            `);
        }
    });
}

function renderSubActions(s) {
    if(s.assignee_id == USER_ID) {
        if(s.status == 'To Do') return `<button class="btn btn-primary w-100 rounded-pill fw-bold" onclick="doStatus(${s.id}, 'In Progress')">BẮT ĐẦU LÀM</button>`;
        if(s.status == 'In Progress') {
            return `
                <form id="evidForm" onsubmit="submitEv(event, ${s.id})">
                    <label class="small fw-bold mb-1">Gửi thông tin minh chứng</label>
                    <textarea name="notes" class="form-control mb-2" placeholder="Nhập Link hoặc Ghi chú..." rows="2"></textarea>
                    <input type="file" name="evidence_file" class="form-control mb-3" accept="image/*">
                    <button type="submit" class="btn btn-warning w-100 rounded-pill fw-bold" ${!s.has_evidence ? '' : ''}>GỬI DUYỆT</button>
                </form>
            `;
        }
    }
    if((ROLE_ID == 1 || ROLE_ID == 2 || ROLE_ID == 4) && s.status == 'Pending') {
        return `<div class="d-flex gap-2"><button type="button" class="btn btn-success flex-fill rounded-pill fw-bold" onclick="approve(${s.id})">DUYỆT</button><button type="button" class="btn btn-danger flex-fill rounded-pill fw-bold" onclick="reject(${s.id})">TỪ CHỐI</button></div>`;
    }
    if (s.status == 'Done') return `<div class="alert alert-success py-2 text-center fw-bold mb-0"><i class="bi bi-check-circle me-1"></i>Đã hoàn thành!</div>`;
    return '';
}

// ===== TASK DETAIL MODAL (NÂNG CẤP) =====
function openTaskDetail(tid) {
    var modal = new bootstrap.Modal(document.getElementById('taskDetailModal'));
    modal.show();
    $('#taskBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>');

    $.getJSON('index.php?action=api_task_detail&id=' + tid, function(res) {
        if (!res.success) return;
        let task = res.data;
        let now = new Date();
        let sCount = task.subtask_count || 0;
        let dCount = task.done_count || 0;
        let pct = sCount > 0 ? Math.round((dCount / sCount) * 100) : 0;

        // Priority badge
        let pClass = 'priority-medium', pLabel = 'Trung bình';
        if (task.priority == 'High') { pClass = 'priority-high'; pLabel = 'Cao'; }
        else if (task.priority == 'Low') { pClass = 'priority-low'; pLabel = 'Thấp'; }

        // Subtask list with colors
        let subtaskListHtml = '';
        if (task.subtasks && task.subtasks.length > 0) {
            task.subtasks.forEach(s => {
                let isOverdue = s.deadline && new Date(s.deadline) < now && s.status !== 'Done';
                let colorClass = 'st-todo';
                if (s.status === 'Done') colorClass = 'st-done';
                else if (isOverdue) colorClass = 'st-overdue';
                else if (s.status === 'In Progress' || s.status === 'Pending') colorClass = 'st-progress';

                let statusBadge = `<span class="badge bg-secondary" style="font-size:0.6rem">${s.status}</span>`;
                if (s.status == 'Done') statusBadge = '<span class="badge bg-success" style="font-size:0.6rem">Hoàn thành</span>';
                else if (s.status == 'In Progress') statusBadge = '<span class="badge bg-primary" style="font-size:0.6rem">Đang làm</span>';
                else if (s.status == 'Pending') statusBadge = '<span class="badge bg-warning text-dark" style="font-size:0.6rem">Chờ duyệt</span>';
                if (isOverdue) statusBadge = '<span class="badge bg-danger" style="font-size:0.6rem">Trễ hạn</span>';

                let delBtn = (ROLE_ID <= 2 || ROLE_ID == 4) ? `<i class="bi bi-trash text-danger ms-2 pointer" onclick="event.stopPropagation();deleteSub(${s.id})" title="Xóa"></i>` : '';

                subtaskListHtml += `<div class="subtask-list-item ${colorClass}" onclick="bootstrap.Modal.getInstance(document.getElementById('taskDetailModal')).hide(); setTimeout(()=>openSubtaskDetail(${s.id}),300);">
                    <div>
                        <span class="fw-bold">${s.title}</span>
                        <div class="text-muted" style="font-size:0.7rem"><i class="bi bi-person"></i> ${s.assignee_name}${s.deadline ? ' · <i class=\"bi bi-clock\"></i> ' + new Date(s.deadline).toLocaleDateString('vi-VN') : ''}</div>
                    </div>
                    <div class="d-flex align-items-center">${statusBadge}${delBtn}</div>
                </div>`;
            });
        } else {
            subtaskListHtml = '<p class="p-3 text-muted text-center small">Chưa có việc con nào.</p>';
        }

        // Form thêm subtask (chỉ Leader/CEO)
        let addSubtaskFormHtml = '';
        if (ROLE_ID != 3) {
            addSubtaskFormHtml = `
                <div class="mt-3">
                    <button class="btn btn-outline-primary btn-sm w-100 rounded-pill fw-bold" onclick="bootstrap.Modal.getInstance(document.getElementById('taskDetailModal')).hide(); setTimeout(()=>openAddSubtaskModal(${task.id}),300);">
                        <i class="bi bi-plus-lg me-1"></i>Thêm công việc con
                    </button>
                </div>
            `;
        }

        $('#taskBody').html(`
            <div class="row">
                <div class="col-md-5 border-end">
                    <h5 class="fw-bold mb-2">${task.title}</h5>
                    <div class="small text-muted mb-3" style="white-space:pre-wrap">${task.description || '<span class=\"text-muted\">Không có mô tả</span>'}</div>
                    <div class="bg-light p-3 rounded-3 small mb-3">
                        <div class="d-flex justify-content-between mb-2"><span>Người tạo:</span><span class="fw-bold text-primary"><i class="bi bi-person me-1"></i>${task.creator_name || 'N/A'}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>Phòng ban:</span><span class="fw-bold">${task.dept_name || 'N/A'}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>Ưu tiên:</span><span class="priority-badge ${pClass}">${pLabel}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>Hạn chót:</span><span class="fw-bold">${task.deadline ? new Date(task.deadline).toLocaleDateString('vi-VN') : 'Không có'}</span></div>
                        <div class="d-flex justify-content-between"><span>Trạng thái:</span><span class="badge ${task.status == 'Done' ? 'bg-success' : 'bg-primary'}">${task.status}</span></div>
                    </div>
                    <!-- Progress bar -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small fw-bold mb-1"><span>Tiến độ</span><span>${pct}%</span></div>
                        <div class="progress-bar-wrap" style="height: 8px;">
                            <div class="progress-bar-fill ${pct == 100 ? 'complete' : ''}" style="width: ${pct}%"></div>
                        </div>
                        <div class="text-muted mt-1" style="font-size:0.7rem">${dCount}/${sCount} công việc hoàn thành</div>
                    </div>
                </div>
                <div class="col-md-7">
                    <p class="extra-small fw-bold text-muted mb-2 uppercase">DANH SÁCH VIỆC CON</p>
                    <div class="border rounded-3 mb-2" style="max-height: 350px; overflow-y: auto;">${subtaskListHtml}</div>
                    ${addSubtaskFormHtml}
                </div>
            </div>
        `);
    });
}

// ===== ACTION FUNCTIONS =====
function doStatus(id, s) { 
    $.post('index.php?action=api_update_subtask_status', {subtask_id: id, status: s}, function(res) { 
        if(!res.success) { handleDragError(res.message); } 
        else { location.reload(); }
    }, 'json'); 
}

function submitEv(e, id) { 
    e.preventDefault();
    let fd = new FormData(document.getElementById('evidForm'));
    fd.append('subtask_id', id);
    $.ajax({
        url: 'index.php?action=api_submit_evidence',
        type: 'POST', data: fd, processData: false, contentType: false,
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                Swal.fire({title: 'Thành công!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false}).then(() => location.reload());
            } else {
                Swal.fire({title: 'Lỗi!', text: res.message, icon: 'error'});
            }
        }
    });
}

function approve(id) { 
    Swal.fire({
        title: 'Duyệt công việc?',
        text: 'Xác nhận duyệt subtask này về trạng thái Hoàn thành?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Duyệt',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('index.php?action=api_approve_subtask', {subtask_id: id}, function(res) { 
                if(res.success) { Swal.fire({title: 'Đã duyệt!', icon: 'success', timer: 1200, showConfirmButton: false}).then(() => location.reload()); }
                else { Swal.fire({title: 'Lỗi!', text: res.message, icon: 'error'}); }
            }, 'json');
        }
    });
}

function reject(id) { 
    Swal.fire({
        title: 'Từ chối công việc',
        input: 'textarea',
        inputLabel: 'Lý do từ chối:',
        inputPlaceholder: 'Nhập lý do...',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Từ chối',
        cancelButtonText: 'Hủy',
        inputValidator: (value) => { if (!value) return 'Vui lòng nhập lý do!'; }
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('index.php?action=api_reject_subtask', {subtask_id: id, reason: result.value}, function(res) {
                if(res.success) { Swal.fire({title: 'Đã từ chối!', icon: 'info', timer: 1200, showConfirmButton: false}).then(() => location.reload()); }
                else { Swal.fire({title: 'Lỗi!', text: res.message, icon: 'error'}); }
            }, 'json');
        }
    });
}

function deleteSub(id) {
    Swal.fire({
        title: 'Xóa công việc con?',
        text: 'Mọi thông tin minh chứng sẽ bị mất. Hành động này không thể hoàn tác!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('index.php?action=api_delete_subtask', {subtask_id: id}, function(res) {
                if(res.success) location.reload(); 
                else Swal.fire({title: 'Lỗi!', text: res.message, icon: 'error'});
            }, 'json');
        }
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
