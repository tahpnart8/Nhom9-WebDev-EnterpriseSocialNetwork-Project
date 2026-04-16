<?php include __DIR__ . '/../layouts/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* ===== LAYOUT TỔNG THỂ ===== */
    .tasks-wrapper {
        display: flex;
        flex-direction: column;
        gap: var(--app-gap);
        flex: 1;
        min-height: 0;
        min-width: 0;
        width: 100%;
        background: #ffffff;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    /* ===== TABS ===== */
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
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    /* ===== BOARD CONTAINER (CỐ ĐỊNH CHIỀU DỌC + SCROLL NGANG) ===== */
    .board-container {
        flex: 1;
        min-height: 0;
        overflow-x: auto;
        overflow-y: hidden;
    }

    .board-container::-webkit-scrollbar {
        height: 10px;
    }

    .board-container::-webkit-scrollbar-track {
        background: #e2e8f0;
        border-radius: 10px;
    }

    .board-container::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 10px;
    }

    .board-container::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }

    /* CHẾ ĐỘ 1: TIẾN ĐỘ (5 CỘT) */
    .board-progress {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.7rem;
        min-width: 1100px;
        height: 100%;
    }

    /* CHẾ ĐỘ 2: THEO TASK (SCROLL NGANG - inline-flex tạo width tự nhiên) */
    .board-tasks {
        display: inline-flex;
        gap: 0.8rem;
        padding-bottom: 0.5rem;
        height: 100%;
        padding-right: 1rem;
    }

    /* Custom scrollbar cũ (fallback) */
    .board-container::-webkit-scrollbar {
        height: 8px;
        width: 6px;
    }

    .board-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .board-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    /* CỘT */
    .column {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        min-height: 0;
        max-height: 100%;
    }

    .board-tasks .column {
        min-width: 300px;
        width: 320px;
        flex-shrink: 0;
    }

    .column-header {
        padding: 0.75rem 0.85rem;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        border-radius: 12px 12px 0 0;
        flex-shrink: 0;
    }

    .column-header h6 {
        margin: 0;
        font-weight: 800;
        font-size: 0.72rem;
        text-transform: uppercase;
        color: #475569;
    }

    /* CARD LIST (SCROLL DỌC) */
    .card-list {
        flex: 1;
        padding: 0.55rem;
        min-height: 40px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .card-list::-webkit-scrollbar {
        width: 4px;
    }

    .card-list::-webkit-scrollbar-track {
        background: transparent;
    }

    .card-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    /* THẺ CÔNG VIỆC */
    .task-card {
        background: white;
        border-radius: 12px;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        cursor: pointer;
        transition: 0.2s;
    }

    .task-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.07);
        border-color: #6366f1;
    }

    .task-card.rejected {
        background: #fff1f2;
        border-color: #fecaca;
    }

    .task-card.extended {
        background: #fefce8;
        border-color: #fde68a;
    }

    .task-card.locked-card {
        cursor: default;
        opacity: 0.7;
        user-select: none;
    }

    .task-card.locked-card:hover {
        transform: none;
        box-shadow: none;
        border-color: #e2e8f0;
    }

    .card-label {
        font-size: 0.58rem;
        font-weight: 800;
        color: #6366f1;
        text-transform: uppercase;
        margin-bottom: 0.15rem;
    }

    .card-name {
        font-weight: 700;
        font-size: 0.82rem;
        color: #1e293b;
        margin-bottom: 0.3rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .card-desc-preview {
        font-size: 0.68rem;
        color: #94a3b8;
        margin-bottom: 0.3rem;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .card-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.4rem;
        border-top: 1px solid #f1f5f9;
        gap: 0.2rem;
    }

    .assignee {
        font-size: 0.62rem;
        color: #64748b;
        font-weight: 600;
    }

    .priority-badge {
        font-size: 0.52rem;
        font-weight: 800;
        padding: 0.12rem 0.4rem;
        border-radius: 0.3rem;
        text-transform: uppercase;
    }

    .priority-high {
        background: #fee2e2;
        color: #dc2626;
    }

    .priority-medium {
        background: #fef3c7;
        color: #d97706;
    }

    .priority-low {
        background: #dcfce7;
        color: #16a34a;
    }

    .deadline-text {
        font-size: 0.58rem;
        color: #94a3b8;
    }

    .deadline-overdue {
        color: #dc2626 !important;
        font-weight: 700;
    }

    /* CỘT TIẾN ĐỘ */
    .status-todo {
        border-top: 4px solid #94a3b8;
    }

    .status-progress {
        border-top: 4px solid #3b82f6;
    }

    .status-pending {
        border-top: 4px solid #f59e0b;
    }

    .status-done {
        border-top: 4px solid #10b981;
    }

    .status-overdue {
        border-top: 4px solid #ef4444;
        background: #fef2f2;
    }

    /* PROGRESS BAR */
    .task-progress {
        padding: 0.35rem 0.85rem 0.4rem;
        background: #f8fafc;
        flex-shrink: 0;
    }

    .progress-bar-wrap {
        height: 5px;
        background: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        border-radius: 3px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        transition: width 0.4s;
    }

    .progress-bar-fill.complete {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .progress-text {
        font-size: 0.58rem;
        color: #94a3b8;
        font-weight: 700;
        margin-top: 0.15rem;
        text-align: right;
    }

    /* Task hoàn thành */
    .task-completed-column {
        border: 2px solid #10b981 !important;
        background: #f0fdf4 !important;
    }

    .task-completed-column .column-header {
        background: #f0fdf4;
    }

    /* SUBTASK LIST MODAL */
    .subtask-list-item {
        padding: 0.45rem 0.65rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.8rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: background 0.15s;
        border-left: 3px solid transparent;
    }

    .subtask-list-item:hover {
        background: #f8fafc;
    }

    .subtask-list-item.st-done {
        border-left-color: #10b981;
        background: #f0fdf4;
    }

    .subtask-list-item.st-progress {
        border-left-color: #f59e0b;
        background: #fffbeb;
    }

    .subtask-list-item.st-overdue {
        border-left-color: #ef4444;
        background: #fef2f2;
    }

    .subtask-list-item.st-todo {
        border-left-color: #94a3b8;
    }

    /* DYNAMIC ROWS */
    .subtask-row {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 0.6rem;
        margin-bottom: 0.4rem;
        position: relative;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }

    .subtask-row .remove-row {
        position: absolute;
        top: 0.3rem;
        right: 0.4rem;
        color: #ef4444;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .pointer {
        cursor: pointer;
    }

    .uppercase {
        text-transform: uppercase;
    }

    .extra-small {
        font-size: 0.7rem;
    }
</style>

<div class="tasks-wrapper">
    <div class="d-flex justify-content-between align-items-center">
        <div class="view-switcher">
            <button class="view-btn active" onclick="switchView('progress', this)">Quản lý theo tiến độ</button>
            <button class="view-btn" onclick="switchView('tasks', this)">Quản lý theo Task</button>
        </div>
        <?php if ($_SESSION['role_id'] != 3): ?>
            <button class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal"
                data-bs-target="#createTaskModal">
                <i class="bi bi-plus-lg me-1"></i> Tạo Task mới
            </button>
        <?php endif; ?>
    </div>

    <div class="board-container">
        <!-- VIEW 1: TIẾN ĐỘ -->
        <div id="board-progress" class="board-progress">
            <?php
            $metas = [
                'To Do' => ['label' => 'Cần làm', 'class' => 'status-todo'],
                'In Progress' => ['label' => 'Đang làm', 'class' => 'status-progress'],
                'Pending' => ['label' => 'Chờ duyệt', 'class' => 'status-pending'],
                'Done' => ['label' => 'Hoàn thành', 'class' => 'status-done'],
                'Overdue' => ['label' => 'Trễ hạn', 'class' => 'status-overdue']
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
                            $isExtended = ($c['is_extended'] ?? 0);
                            ?>
                            <div class="task-card <?php echo (($c['is_rejected'] ?? 0) && ($c['status'] ?? '') == 'To Do') ? 'rejected' : ''; ?> <?php echo $isExtended ? 'extended' : ''; ?> <?php echo $isDone ? 'locked-card' : ''; ?>"
                                data-id="<?php echo $c['id']; ?>" onclick="openSubtaskDetail(<?php echo $c['id']; ?>)">
                                <?php if (($c['is_approved'] ?? 0)): ?>
                                    <div class="badge bg-success d-inline-flex align-items-center mb-1" style="font-size:0.6rem"><i
                                            class="bi bi-check-circle-fill me-1"></i> Đã duyệt</div>
                                <?php endif; ?>
                                <div class="card-label text-truncate"><?php echo htmlspecialchars($c['task_title']); ?></div>
                                <div class="card-name"><?php echo htmlspecialchars($c['title']); ?></div>
                                <?php if (!empty($c['description'])): ?>
                                    <div class="card-desc-preview"><?php echo htmlspecialchars($c['description']); ?></div>
                                <?php endif; ?>
                                <div class="card-info">
                                    <span class="assignee"><i
                                            class="bi bi-person me-1"></i><?php echo htmlspecialchars($c['assignee_name']); ?></span>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php if (!empty($c['deadline'])): ?>
                                            <span class="deadline-text <?php echo $isCardOverdue ? 'deadline-overdue' : ''; ?>">
                                                <i
                                                    class="bi bi-clock me-1"></i><?php echo date('d/m', strtotime($c['deadline'])); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php
                                        $pClass = 'priority-medium';
                                        $pLabel = 'TB';
                                        $prio = $c['priority'] ?? 'Medium';
                                        if ($prio == 'High') {
                                            $pClass = 'priority-high';
                                            $pLabel = 'Cao';
                                        } elseif ($prio == 'Low') {
                                            $pClass = 'priority-low';
                                            $pLabel = 'Thấp';
                                        }
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

        <!-- VIEW 2: THEO TASK -->
        <div id="board-tasks" class="board-tasks d-none">
            <?php foreach ($tasksWithSubtasks as $task):
                $sCount = $task['subtask_count'] ?? count($task['subtasks']);
                $dCount = $task['done_count'] ?? count(array_filter($task['subtasks'], function ($s) {
                    return $s['status'] == 'Done'; }));
                $pct = $sCount > 0 ? round(($dCount / $sCount) * 100) : 0;
                $isComplete = ($sCount > 0 && $pct == 100);
                ?>
                <div class="column <?php echo $isComplete ? 'task-completed-column' : ''; ?>">
                    <div class="column-header pointer" onclick="openTaskDetail(<?php echo $task['id']; ?>)">
                        <h6 class="text-truncate" style="max-width: 75%;"><i
                                class="bi bi-folder2 me-2"></i><?php echo htmlspecialchars($task['title']); ?></h6>
                        <span class="badge bg-light text-muted rounded-pill"><?php echo $sCount; ?></span>
                    </div>
                    <div class="task-progress">
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill <?php echo $isComplete ? 'complete' : ''; ?>"
                                style="width: <?php echo $pct; ?>%"></div>
                        </div>
                        <div class="progress-text"><?php echo $dCount; ?>/<?php echo $sCount; ?> (<?php echo $pct; ?>%)
                        </div>
                    </div>
                    <div class="card-list">
                        <?php foreach ($task['subtasks'] as $c):
                            $isCardOverdue = (!empty($c['deadline']) && $c['deadline'] < $now && $c['status'] != 'Done');
                            $isExtended = ($c['is_extended'] ?? 0);
                            ?>
                            <div class="task-card <?php echo (($c['is_rejected'] ?? 0) && ($c['status'] ?? '') == 'To Do') ? 'rejected' : ''; ?> <?php echo $isExtended ? 'extended' : ''; ?>"
                                onclick="openSubtaskDetail(<?php echo $c['id']; ?>)">
                                <div class="card-name"><?php echo htmlspecialchars($c['title']); ?></div>
                                <div class="card-info">
                                    <span class="assignee"><?php echo htmlspecialchars($c['assignee_name']); ?></span>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php if (!empty($c['deadline'])): ?>
                                            <span class="deadline-text <?php echo $isCardOverdue ? 'deadline-overdue' : ''; ?>">
                                                <i class="bi bi-clock"></i> <?php echo date('d/m', strtotime($c['deadline'])); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php
                                        $stClass = 'bg-secondary';
                                        if ($c['status'] == 'Done')
                                            $stClass = 'bg-success';
                                        elseif ($c['status'] == 'In Progress')
                                            $stClass = 'bg-primary';
                                        elseif ($c['status'] == 'Pending')
                                            $stClass = 'bg-warning text-dark';
                                        if ($isCardOverdue)
                                            $stClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $stClass; ?>"
                                            style="font-size: 0.52rem;"><?php echo $isCardOverdue ? 'Trễ hạn' : $c['status']; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($_SESSION['role_id'] != 3): ?>
                            <button class="btn btn-link btn-sm w-100 text-muted mt-1 fw-bold text-decoration-none"
                                onclick="openAddSubtaskModal(<?php echo $task['id']; ?>)">+ Thêm việc...</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ==================== MODALS ==================== -->
<!-- Subtask Detail -->
<div class="modal fade" id="subtaskDetailModal">
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
<!-- Task Detail -->
<div class="modal fade" id="taskDetailModal">
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
<!-- Create Task -->
<div class="modal fade" id="createTaskModal">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">TẠO TASK MỚI</h6><button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTask">
                    <div class="mb-3"><label class="form-label small fw-bold">TIÊU ĐỀ TASK <span
                                class="text-danger">*</span></label><input type="text" name="title"
                            class="form-control rounded-3" required></div>
                    <div class="mb-3"><label class="form-label small fw-bold">MÔ TẢ <span
                                class="text-danger">*</span></label><textarea name="description"
                            class="form-control rounded-3" rows="2" required></textarea></div>
                    <div class="row mb-3">
                        <div class="col-6"><label class="form-label small fw-bold">ƯU TIÊN</label><select
                                name="priority" class="form-select rounded-3">
                                <option value="Low">Thấp</option>
                                <option value="Medium" selected>Trung bình</option>
                                <option value="High">Cao</option>
                            </select></div>
                        <div class="col-6"><label class="form-label small fw-bold">HẠN CHÓT</label><input type="date"
                                name="deadline" class="form-control rounded-3 date-no-past"></div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold mb-0">CÔNG VIỆC CON <span class="text-danger">* (ít nhất
                                1)</span></label>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill"
                            onclick="addSubtaskRow('taskSubtaskList')"><i class="bi bi-plus-lg me-1"></i>Thêm</button>
                    </div>
                    <div id="taskSubtaskList"></div>
                </form>
            </div>
            <div class="modal-footer border-0"><button class="btn btn-primary w-100 rounded-pill fw-bold"
                    onclick="saveTask()">TẠO NGAY</button></div>
        </div>
    </div>
</div>
<!-- Add Subtask -->
<div class="modal fade" id="addSubtaskModal">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">THÊM CÔNG VIỆC CON</h6><button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAddSubtasks">
                    <input type="hidden" name="task_id" id="addSubtask_taskId">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold mb-0">DANH SÁCH VIỆC CẦN THÊM</label>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill"
                            onclick="addSubtaskRow('addSubtaskList')"><i class="bi bi-plus-lg me-1"></i>Thêm
                            dòng</button>
                    </div>
                    <div id="addSubtaskList"></div>
                </form>
            </div>
            <div class="modal-footer border-0"><button class="btn btn-primary w-100 rounded-pill fw-bold"
                    onclick="saveMultipleSubtasks()">GIAO VIỆC</button></div>
        </div>
    </div>
</div>

<!-- MODAL BÁO CÁO CÔNG VIỆC BẰNG AI -->
<div class="modal fade" id="subtaskReportModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-robot me-2"></i>Báo Cáo Công Việc (Relioo AI)</h5>
                <!-- Nút close để user tắt modal, khi tắt sẽ reset drag -->
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    onclick="cancelSubtaskReport()"></button>
            </div>
            <div class="modal-body bg-light">
                <input type="hidden" id="reportSubtaskId">
                <div class="bg-white p-3 rounded shadow-sm border mb-3">
                    <h6 id="reportTaskTitle" class="fw-bold text-primary mb-1">...</h6>
                </div>

                <div id="reportFormArea">
                    <p class="text-muted small fw-bold mb-3">Trả lời 3 câu hỏi để AI sinh báo cáo tự động:</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold extra-small">1. Bạn đã hoàn thành công việc này như thế
                            nào?</label>
                        <textarea id="ai_q1" class="form-control" rows="2"
                            placeholder="Ví dụ: Tôi đã dùng công nghệ X, hoàn thành module Y..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold extra-small">2. Kinh nghiệm rút ra?</label>
                        <textarea id="ai_q2" class="form-control" rows="2"
                            placeholder="Ví dụ: Rút kinh nghiệm tối ưu truy vấn SQL..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold extra-small">3. Lưu ý cho lần sau?</label>
                        <textarea id="ai_q3" class="form-control" rows="2"
                            placeholder="Ví dụ: Cần check kỹ đầu vào API..."></textarea>
                    </div>
                    <button class="btn btn-primary w-100 fw-bold" onclick="generateAiReport()" id="btnGenerateAi">
                        <i class="bi bi-stars me-2"></i>Tạo báo cáo bằng AI
                    </button>
                </div>

                <div id="aiPreviewArea" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> AI đã phác thảo
                            xong</span>
                        <small class="text-muted">Bạn có thể chỉnh sửa lại nội dung bên dưới</small>
                    </div>
                    <textarea id="aiGeneratedContent" class="form-control mb-3" rows="8"></textarea>
                    <button class="btn btn-success w-100 fw-bold" onclick="submitAiReport()" id="btnSubmitAi">
                        <i class="bi bi-send-check me-2"></i>Xác nhận hoàn thành & Đăng bài
                    </button>
                    <button class="btn btn-outline-secondary w-100 mt-2 fw-bold" onclick="resetAiForm()">Làm
                        lại</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TỔNG KẾT DỰ ÁN BẰNG AI (Cho Trưởng phòng) -->
<div class="modal fade" id="taskSummaryModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-robot me-2"></i>Tổng Kết Dự Án (Relioo AI)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <input type="hidden" id="summaryTaskId">
                <div class="text-center mb-4 mt-2" id="summaryLoadingArea">
                    <p class="text-muted fw-bold mb-3" id="summaryTaskTitle">...</p>
                    <div class="spinner-border text-success mb-2" role="status"></div>
                    <p class="small text-muted">AI đang thu thập toàn bộ báo cáo từ các công việc con và soạn thảo tổng
                        kết...</p>
                </div>

                <div id="summaryPreviewArea" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Dự thảo bài đăng hoàn
                            tất</span>
                        <small class="text-muted">Bạn có thể chỉnh sửa lại nội dung trước khi xuất bản</small>
                    </div>
                    <textarea id="aiSummaryContent" class="form-control mb-3" rows="12"></textarea>

                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i> Bài viết sẽ được tự động đăng lên cả <b>Kênh Công
                            Khai</b> và <b>Kênh Phòng Ban</b>.
                    </div>
                    <button class="btn btn-success w-100 fw-bold" onclick="submitTaskSummary()" id="btnSubmitSummary">
                        <i class="bi bi-send-check me-2"></i>Xuất bản bài Tổng kết
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var STAFF_LIST = <?php echo json_encode($staffList); ?>;
    var USER_ID = <?php echo $_SESSION['user_id']; ?>;
    var ROLE_ID = <?php echo $_SESSION['role_id']; ?>;
    var SERVER_NOW = new Date("<?php echo date('Y-m-d H:i:s'); ?>");
    var TODAY = "<?php echo date('Y-m-d'); ?>";

    // ===== MIN DATE: không cho chọn ngày quá khứ =====
    $(function () {
        // Set min cho tất cả input date có class date-no-past
        $('input.date-no-past, input[name="deadline"]').attr('min', TODAY);

        // Auto-open subtask modal nếu có URL param
        let params = new URLSearchParams(window.location.search);
        let sid = params.get('subtask_id');
        if (sid) setTimeout(() => openSubtaskDetail(parseInt(sid)), 500);
    });

    // ===== VIEW SWITCH =====
    function switchView(view, btn) {
        $('.view-btn').removeClass('active'); $(btn).addClass('active');
        if (view === 'progress') { $('#board-progress').removeClass('d-none'); $('#board-tasks').addClass('d-none'); }
        else { $('#board-progress').addClass('d-none'); $('#board-tasks').removeClass('d-none'); }
    }

    // ===== SORTABLE =====
    $(function () {
        document.querySelectorAll('#board-progress .card-list').forEach(el => {
            let isDoneCol = el.classList.contains('done-column');
            let isOverdueCol = el.classList.contains('overdue-column');
            new Sortable(el, {
                group: 'kanban', animation: 150,
                filter: isDoneCol || isOverdueCol ? '.task-card' : '',
                onMove: function (evt) {
                    let toStatus = evt.to.dataset.status;
                    if (toStatus === '') return false;
                    if (evt.dragged.classList.contains('locked-card')) return false;
                },
                onEnd: function (evt) {
                    let id = evt.item.dataset.id;
                    let status = evt.to.dataset.status;
                    let oldStatus = evt.from.dataset.status;
                    if (!id || !status || status === oldStatus) { location.reload(); return; }
                    if (status === 'Pending') { handleDragToPending(id, evt); return; }
                    if (status === 'Done') {
                        // Check if it's approved pending before allowing drag to Done
                        if (oldStatus !== 'Pending') {
                            Swal.fire({ title: 'Không hợp lệ!', text: 'Chỉ công việc đang ở "Chờ duyệt" (và đã được duyệt) mới có thể chuyển sang Hoàn thành!', icon: 'error' }).then(() => location.reload());
                            return;
                        }
                        // Trigger modal form, without doing DB update yet
                        openAiReportModal(id, evt.item.querySelector('.card-name').innerText);
                        return;
                    }

                    $.post('index.php?action=api_update_subtask_status', { subtask_id: id, status: status }, function (res) {
                        if (!res.success) { handleDragError(res.message); location.reload(); }
                    }, 'json').fail(() => location.reload());
                }
            });
        });
    });

    function openAiReportModal(id, title) {
        document.getElementById('reportSubtaskId').value = id;
        document.getElementById('reportTaskTitle').innerText = title;
        resetAiForm();
        new bootstrap.Modal(document.getElementById('subtaskReportModal')).show();
    }

    function resetAiForm() {
        $('#reportFormArea').removeClass('d-none');
        $('#aiPreviewArea').addClass('d-none');
        $('#ai_q1').val('');
        $('#ai_q2').val('');
        $('#ai_q3').val('');
        $('#aiGeneratedContent').val('');
    }

    function cancelSubtaskReport() {
        // Nếu huỷ form thì reload trang để thẻ quay về lại cột gốc
        location.reload();
    }

    function generateAiReport() {
        let q1 = $('#ai_q1').val().trim();
        let q2 = $('#ai_q2').val().trim();
        let q3 = $('#ai_q3').val().trim();

        if (!q1 || !q2 || !q3) {
            Swal.fire({ title: 'Thiếu thông tin!', text: 'Vui lòng nhập đủ 3 câu trả lời!', icon: 'warning' });
            return;
        }

        let btn = $('#btnGenerateAi');
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Đang phân tích dữ liệu...').prop('disabled', true);

        $.post('index.php?action=api_generate_subtask_report', {
            subtask_id: $('#reportSubtaskId').val(), q1: q1, q2: q2, q3: q3
        }, function (res) {
            btn.html('<i class="bi bi-stars me-2"></i>Tạo báo cáo bằng AI').prop('disabled', false);
            if (res.success) {
                $('#aiGeneratedContent').val(res.data);
                $('#reportFormArea').addClass('d-none');
                $('#aiPreviewArea').removeClass('d-none');
            } else {
                Swal.fire({ title: 'Lỗi AI!', text: res.message, icon: 'error' });
            }
        }, 'json').fail(() => {
            btn.html('<i class="bi bi-stars me-2"></i>Tạo báo cáo bằng AI').prop('disabled', false);
            Swal.fire({ title: 'Lỗi hệ thống!', text: 'Không thể kết nối đến server.', icon: 'error' });
        });
    }

    function submitAiReport() {
        let aiContent = $('#aiGeneratedContent').val().trim();
        if (!aiContent) return;

        let btn = $('#btnSubmitAi');
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...').prop('disabled', true);

        $.post('index.php?action=api_save_subtask_report', {
            subtask_id: $('#reportSubtaskId').val(),
            q1: $('#ai_q1').val(),
            q2: $('#ai_q2').val(),
            q3: $('#ai_q3').val(),
            ai_content: aiContent
        }, function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('subtaskReportModal')).hide();
                Swal.fire({ title: 'Tuyệt vời!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false }).then(() => location.reload());
            } else {
                btn.html('<i class="bi bi-send-check me-2"></i>Xác nhận hoàn thành & Đăng bài').prop('disabled', false);
                Swal.fire({ title: 'Lỗi API!', text: res.message, icon: 'error' });
            }
        }, 'json').fail(function (xhr) {
            btn.html('<i class="bi bi-send-check me-2"></i>Xác nhận hoàn thành &amp; Đăng bài').prop('disabled', false);
            let errMsg = 'Không thể kết nối đến server.';
            try { let r = JSON.parse(xhr.responseText); if (r.message) errMsg = r.message; } catch (e) { }
            Swal.fire({ title: 'Lỗi hệ thống!', text: errMsg, icon: 'error' });
        });
    }

    function openAiTaskSummaryModal(taskId, title) {
        document.getElementById('summaryTaskId').value = taskId;
        document.getElementById('summaryTaskTitle').innerText = 'Dự án: ' + title;

        $('#summaryLoadingArea').removeClass('d-none');
        $('#summaryPreviewArea').addClass('d-none');
        $('#aiSummaryContent').val('');

        new bootstrap.Modal(document.getElementById('taskSummaryModal')).show();

        // Call API generate
        $.post('index.php?action=api_generate_task_summary', { task_id: taskId }, function (res) {
            if (res.success) {
                $('#summaryLoadingArea').addClass('d-none');
                $('#summaryPreviewArea').removeClass('d-none');
                $('#aiSummaryContent').val(res.data);
            } else {
                bootstrap.Modal.getInstance(document.getElementById('taskSummaryModal')).hide();
                Swal.fire({ title: 'Lỗi tổng hợp dữ liệu!', text: res.message, icon: 'error' });
            }
        }, 'json').fail(() => {
            bootstrap.Modal.getInstance(document.getElementById('taskSummaryModal')).hide();
            Swal.fire({ title: 'Lỗi hệ thống!', text: 'Không thể kết nối API AI.', icon: 'error' });
        });
    }

    function submitTaskSummary() {
        let aiContent = $('#aiSummaryContent').val().trim();
        if (!aiContent) return;

        let btn = $('#btnSubmitSummary');
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Đang đăng bài...').prop('disabled', true);

        $.post('index.php?action=api_save_task_summary', {
            task_id: $('#summaryTaskId').val(),
            ai_content: aiContent
        }, function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('taskSummaryModal')).hide();
                Swal.fire({ title: 'Xuất bản thành công!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false }).then(() => location.reload());
            } else {
                btn.html('<i class="bi bi-send-check me-2"></i>Xuất bản bài Tổng kết').prop('disabled', false);
                Swal.fire({ title: 'Lỗi đăng bài!', text: res.message, icon: 'error' });
            }
        }, 'json');
    }

    function handleDragToPending(subtaskId) {
        $.getJSON('index.php?action=api_check_evidence&id=' + subtaskId, function (res) {
            if (res.has_evidence) {
                Swal.fire({ title: 'Gửi duyệt công việc?', text: 'Bạn đã có minh chứng. Xác nhận gửi duyệt?', icon: 'question', showCancelButton: true, confirmButtonColor: '#6366f1', confirmButtonText: 'Gửi duyệt', cancelButtonText: 'Hủy' }).then(r => {
                    if (r.isConfirmed) {
                        $.post('index.php?action=api_update_subtask_status', { subtask_id: subtaskId, status: 'Pending', confirm: '1' }, function (res) {
                            if (res.success) Swal.fire({ title: 'Thành công!', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload());
                            else location.reload();
                        }, 'json');
                    } else location.reload();
                });
            } else {
                Swal.fire({ title: 'Chưa có minh chứng!', html: 'Bạn cần điền minh chứng <b>trước khi</b> gửi duyệt.', icon: 'warning', confirmButtonColor: '#f59e0b', confirmButtonText: 'Đã hiểu' }).then(() => location.reload());
            }
        }).fail(() => location.reload());
    }

    function handleDragError(msg) {
        if (msg === 'locked_done') Swal.fire({ title: 'Không thể thao tác!', text: 'Công việc đã hoàn thành không thể thay đổi.', icon: 'error' });
        else if (msg === 'no_evidence') Swal.fire({ title: 'Chưa có minh chứng!', text: 'Bổ sung minh chứng trước khi gửi duyệt.', icon: 'warning' });
        else if (msg === 'permission_denied') Swal.fire({ title: 'Không có quyền!', text: 'Chỉ người được giao mới thao tác được.', icon: 'error' });
        else if (msg === 'locked_pending') Swal.fire({ title: 'Đang chờ duyệt!', text: 'Chờ Trưởng phòng phê duyệt.', icon: 'info' });
        else if (msg === 'overdue_locked') Swal.fire({ title: 'Đã trễ hạn!', text: 'Bạn cần gửi "Yêu cầu gia hạn" và được cấp trên phê duyệt mới có thể tiếp tục.', icon: 'warning' });
        else if (msg !== 'confirm_pending') Swal.fire({ title: 'Lỗi!', text: msg || 'Có lỗi xảy ra.', icon: 'error' });
    }

    // ===== SAVE TASK (WITH VALIDATION) =====
    function saveTask() {
        let title = $('input[name="title"]', '#formTask').val().trim();
        let desc = $('textarea[name="description"]', '#formTask').val().trim();
        let subtaskRows = $('#taskSubtaskList .subtask-row');

        if (!title) { Swal.fire({ title: 'Thiếu thông tin!', text: 'Vui lòng nhập tiêu đề Task.', icon: 'warning' }); return; }
        if (!desc) { Swal.fire({ title: 'Thiếu thông tin!', text: 'Vui lòng nhập mô tả Task.', icon: 'warning' }); return; }
        if (subtaskRows.length === 0) { Swal.fire({ title: 'Thiếu subtask!', text: 'Cần ít nhất 1 công việc con.', icon: 'warning' }); return; }

        // Check date validation
        let hasDateError = false;
        $('#formTask input[type="date"]').each(function () {
            if ($(this).val() && $(this).val() < TODAY) { hasDateError = true; }
        });
        if (hasDateError) { Swal.fire({ title: 'Lỗi ngày!', text: 'Hạn chót không được là ngày quá khứ.', icon: 'error' }); return; }

        let fd = new FormData(document.getElementById('formTask'));
        $.ajax({
            url: 'index.php?action=api_create_task', type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
            success: function (res) {
                if (res.success) Swal.fire({ title: 'Thành công!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload());
                else Swal.fire({ title: 'Lỗi!', text: res.message, icon: 'error' });
            }
        });
    }

    // ===== DYNAMIC SUBTASK ROWS =====
    function addSubtaskRow(containerId) {
        let staffOpts = STAFF_LIST.map(u => `<option value="${u.id}">${u.full_name}${u.dept_name ? ' (' + u.dept_name + ')' : ''}</option>`).join('');
        let html = `<div class="subtask-row"><i class="bi bi-x-lg remove-row" onclick="this.closest('.subtask-row').remove()"></i>
        <div class="row g-2 mb-2"><div class="col-md-6"><input type="text" name="subtask_title[]" class="form-control form-control-sm" placeholder="Tên công việc con *" required></div><div class="col-md-6"><select name="subtask_assignee[]" class="form-select form-select-sm" required><option value="">-- Giao cho --</option>${staffOpts}</select></div></div>
        <div class="row g-2 mb-1"><div class="col-md-5"><input type="text" name="subtask_description[]" class="form-control form-control-sm" placeholder="Mô tả ngắn..."></div><div class="col-md-4"><input type="date" name="subtask_deadline[]" class="form-control form-control-sm date-no-past" min="${TODAY}"></div><div class="col-md-3"><select name="subtask_priority[]" class="form-select form-select-sm"><option value="Low">Thấp</option><option value="Medium" selected>TB</option><option value="High">Cao</option></select></div></div></div>`;
        document.getElementById(containerId).insertAdjacentHTML('beforeend', html);
    }

    function openAddSubtaskModal(taskId) {
        document.getElementById('addSubtask_taskId').value = taskId;
        document.getElementById('addSubtaskList').innerHTML = '';
        addSubtaskRow('addSubtaskList');
        new bootstrap.Modal(document.getElementById('addSubtaskModal')).show();
    }

    function saveMultipleSubtasks() {
        let hasDateError = false;
        $('#formAddSubtasks input[type="date"]').each(function () { if ($(this).val() && $(this).val() < TODAY) hasDateError = true; });
        if (hasDateError) { Swal.fire({ title: 'Lỗi ngày!', text: 'Hạn chót không được là ngày quá khứ.', icon: 'error' }); return; }

        let fd = new FormData(document.getElementById('formAddSubtasks'));
        $.ajax({
            url: 'index.php?action=api_create_subtask', type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
            success: function (res) {
                if (res.success) Swal.fire({ title: 'Thành công!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload());
                else Swal.fire({ title: 'Lỗi!', text: res.message, icon: 'error' });
            }
        });
    }

    // ===== SUBTASK DETAIL MODAL =====
    function openSubtaskDetail(id) {
        var modal = new bootstrap.Modal(document.getElementById('subtaskDetailModal'));
        modal.show();
        $('#subtaskBody').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
        $.getJSON('index.php?action=api_subtask_detail&id=' + id, function (res) {
            if (!res.success) return;
            let s = res.data;
            let deadlineStr = s.deadline ? s.deadline.replace(' ', 'T') : null;
            let isOverdue = deadlineStr && new Date(deadlineStr) < SERVER_NOW && s.status !== 'Done';

            let feedbackHtml = (s.is_rejected == 1 && s.feedback) ? `<div class="alert alert-danger py-2 small fw-bold"><i class="bi bi-exclamation-triangle me-1"></i>Lý do từ chối: ${s.feedback}</div>` : '';

            let attachmentsHtml = '';
            if (s.attachments && s.attachments.length > 0) {
                attachmentsHtml = '<div class="mb-3"><h6 class="extra-small fw-bold text-muted mb-2 uppercase">MINH CHỨNG ĐÍNH KÈM</h6>';
                s.attachments.forEach(att => {
                    attachmentsHtml += `<div class="p-2 border rounded-3 bg-light mb-2 small">`;
                    if (att.file_url) attachmentsHtml += `<div class="mb-1"><img src="${att.file_url}" class="img-fluid rounded" style="max-height:120px;"></div>`;
                    if (att.notes) attachmentsHtml += `<div>Ghi chú: <b>${att.notes}</b></div>`;
                    attachmentsHtml += `</div>`;
                });
                attachmentsHtml += '</div>';
            }

            let pClass = 'priority-medium', pLabel = 'Trung bình';
            if ((s.priority || 'Medium') == 'High') { pClass = 'priority-high'; pLabel = 'Cao'; }
            else if ((s.priority || 'Medium') == 'Low') { pClass = 'priority-low'; pLabel = 'Thấp'; }

            let statusBadge = `<span class="badge bg-secondary">${s.status}</span>`;
            if (s.status == 'Done') statusBadge = '<span class="badge bg-success">Hoàn thành</span>';
            else if (s.status == 'In Progress') statusBadge = '<span class="badge bg-primary">Đang làm</span>';
            else if (s.status == 'Pending') statusBadge = '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
            if (isOverdue) statusBadge += ' <span class="badge bg-danger">Trễ hạn</span>';
            if (s.is_extended == 1) statusBadge += ' <span class="badge bg-warning text-dark">Đã gia hạn</span>';

            $('#subtaskBody').html(`
            <h5 class="fw-bold mb-1">${s.title}</h5>
            <p class="text-muted small mb-3"><i class="bi bi-folder me-1"></i>${s.task_title}</p>
            ${feedbackHtml}
            <div class="bg-light p-3 rounded-3 mb-3 small">
                <div class="d-flex justify-content-between mb-2"><span>Người làm:</span><b>${s.assignee_name}</b></div>
                <div class="d-flex justify-content-between mb-2"><span>Trạng thái:</span><span>${statusBadge}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Ưu tiên:</span><span class="priority-badge ${pClass}">${pLabel}</span></div>
                <div class="d-flex justify-content-between"><span>Hạn chót:</span><b class="${isOverdue ? 'text-danger' : ''}">${deadlineStr ? new Date(deadlineStr).toLocaleString('vi-VN') : 'Không có'}</b></div>
            </div>
            <div class="mb-3"><h6 class="extra-small fw-bold text-muted mb-1 uppercase">MÔ TẢ</h6><div class="small p-2 bg-light rounded-3" style="white-space:pre-wrap">${s.description || '<span class="text-muted">Không có mô tả.</span>'}</div></div>
            ${attachmentsHtml}
            <div id="sub-actions">${renderSubActions(s)}</div>
        `);
        });
    }

    function renderSubActions(s) {
        let deadlineStr = s.deadline ? s.deadline.replace(' ', 'T') : null;
        let isOverdue = deadlineStr && new Date(deadlineStr) < SERVER_NOW && s.status !== 'Done';

        // Xử lý với người được giao (Assignee)
        if (s.assignee_id == USER_ID) {
            // Nếu đang trễ hạn -> Chỉ được yêu cầu gia hạn
            if (isOverdue) {
                if (s.extension_requested_at) {
                    return `<div class="alert alert-info py-2 text-center small fw-bold mb-0">
                    <i class="bi bi-clock-history me-1"></i> Đang chờ gia hạn...
                </div>`;
                }
                return `<div class="text-center">
                <div class="alert alert-danger py-2 small fw-bold mb-2"><i class="bi bi-exclamation-triangle me-1"></i>Đã quá hạn! Cần gia hạn để tiếp tục.</div>
                <button class="btn btn-warning w-100 rounded-pill fw-bold" onclick="requestExtension(${s.id})">
                    <i class="bi bi-calendar-plus me-1"></i>YÊU CẦU GIA HẠN
                </button>
            </div>`;
            }

            // Nếu chưa trễ hạn -> Thực hiện bình thường
            if (s.status == 'To Do') return `<button class="btn btn-primary w-100 rounded-pill fw-bold" onclick="doStatus(${s.id}, 'In Progress')">BẮT ĐẦU LÀM</button>`;
            if (s.status == 'In Progress') {
                return `
                <form id="evidForm">
                    <label class="small fw-bold mb-1">Minh chứng làm việc</label>
                    <textarea name="notes" class="form-control mb-2" placeholder="Nhập Link hoặc Ghi chú..." rows="2"></textarea>
                    <input type="file" name="evidence_file" class="form-control mb-3" accept="image/*">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary flex-fill rounded-pill fw-bold" onclick="saveEvidenceOnly(${s.id})"><i class="bi bi-save me-1"></i>LƯU MINH CHỨNG</button>
                        <button type="button" class="btn btn-warning flex-fill rounded-pill fw-bold" onclick="submitEv(event, ${s.id})"><i class="bi bi-send me-1"></i>GỬI DUYỆT</button>
                    </div>
                </form>`;
            }
        }

        // Leader/CEO: Duyệt/Từ chối (chỉ khi KHÔNG trễ hạn)
        if ((ROLE_ID == 1 || ROLE_ID == 2 || ROLE_ID == 4) && s.status == 'Pending' && !isOverdue) {
            return `<div class="d-flex gap-2"><button class="btn btn-success flex-fill rounded-pill fw-bold" onclick="approve(${s.id})">DUYỆT</button><button class="btn btn-danger flex-fill rounded-pill fw-bold" onclick="reject(${s.id})">TỪ CHỐI</button></div>`;
        }

        // Leader/CEO: Subtask trễ hạn hoặc có yêu cầu gia hạn -> Nút GIA HẠN
        if ((ROLE_ID == 1 || ROLE_ID == 2 || ROLE_ID == 4) && (isOverdue || s.extension_requested_at)) {
            let msg = isOverdue ? 'Subtask đã trễ hạn!' : 'Nhân viên yêu cầu gia hạn!';
            let reqDateStr = s.requested_deadline ? s.requested_deadline.replace(' ', 'T') : null;
            let reasonHtml = s.extension_reason ? `<div class="small mb-2 text-start p-2 bg-light rounded border">Lý do: <i>"${s.extension_reason}"</i><br>Muốn gia hạn đến: <b>${reqDateStr ? new Date(reqDateStr).toLocaleDateString('vi-VN') : ''}</b></div>` : '';
            return `<div class="text-center">
            <div class="alert alert-warning py-2 small fw-bold mb-2"><i class="bi bi-exclamation-triangle me-1"></i>${msg}</div>
            ${reasonHtml}
            <button class="btn btn-warning w-100 rounded-pill fw-bold" onclick="extendSubtask(${s.id})"><i class="bi bi-calendar-plus me-1"></i>GIA HẠN THỜI GIAN</button>
        </div>`;
        }

        if (s.status == 'Done') return `<div class="alert alert-success py-2 text-center fw-bold mb-0"><i class="bi bi-check-circle me-1"></i>Đã hoàn thành!</div>`;
        return '';
    }

    // ================= DYNAMIC TASK SEARCH HANDLING (PINK GLOW) =================
    function normalizeText(str) {
        if (!str) return "";
        return str.normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .trim();
    }

    window.highlightTasks = function (keyword) {
        if (!keyword || keyword.trim() === "") {
            window.clearTaskHighlights();
            // Show everything
            $('.task-card').show();
            $('.column').show();
            return;
        }

        const searchTerm = normalizeText(keyword);
        let firstMatch = null;

        // View 1: Progress (Kanban) filtering
        $('.task-card').each(function () {
            const $card = $(this);
            // Normalize searchable content (Task Name in label, Subtask Name in card-name)
            const taskName = normalizeText($card.find('.card-label').text());
            const subtaskName = normalizeText($card.find('.card-name').text());
            const subtaskDesc = normalizeText($card.find('.card-desc-preview').text());

            if (taskName.includes(searchTerm) || subtaskName.includes(searchTerm) || subtaskDesc.includes(searchTerm)) {
                $card.addClass('search-highlight').show();
                if (!firstMatch) firstMatch = $card;
            } else {
                $card.removeClass('search-highlight').hide();
            }
        });

        // View 2: Task Group filtering (Columns)
        $('.board-tasks .column').each(function () {
            const $col = $(this);
            const taskTitle = normalizeText($col.find('.column-header h6').text());

            if (taskTitle.includes(searchTerm)) {
                $col.show();
            } else {
                $col.hide();
            }
        });

        if (firstMatch) {
            $('html, body').animate({
                scrollTop: firstMatch.offset().top - 150
            }, 500);

            const $column = firstMatch.closest('.card-list');
            if ($column.length) {
                $column.animate({
                    scrollTop: firstMatch.position().top + $column.scrollTop() - 20
                }, 500);
            }
        }
    };

    window.clearTaskHighlights = function () {
        $('.task-card').removeClass('search-highlight').show();
        $('.board-tasks .column').show();
    };

    // Auto-trigger if q param exists
    $(function () {
        const urlParams = new URLSearchParams(window.location.search);
        const q = urlParams.get('q');
        if (q) {
            setTimeout(() => window.highlightTasks(q), 800);
        }
    });

    // ===== TASK DETAIL MODAL =====
    function openTaskDetail(tid) {
        var modal = new bootstrap.Modal(document.getElementById('taskDetailModal'));
        modal.show();
        $('#taskBody').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
        $.getJSON('index.php?action=api_task_detail&id=' + tid, function (res) {
            if (!res.success) return;
            let task = res.data, now = SERVER_NOW;
            let sCount = task.subtask_count || 0, dCount = task.done_count || 0;
            let pct = sCount > 0 ? Math.round((dCount / sCount) * 100) : 0;
            let pClass = 'priority-medium', pLabel = 'Trung bình';
            if (task.priority == 'High') { pClass = 'priority-high'; pLabel = 'Cao'; }
            else if (task.priority == 'Low') { pClass = 'priority-low'; pLabel = 'Thấp'; }

            let subtaskListHtml = '';
            if (task.subtasks && task.subtasks.length > 0) {
                task.subtasks.forEach(s => {
                    let sDeadlineStr = s.deadline ? s.deadline.replace(' ', 'T') : null;
                    let isOD = sDeadlineStr && new Date(sDeadlineStr) < now && s.status !== 'Done';
                    let cc = 'st-todo';
                    if (s.status === 'Done') cc = 'st-done'; else if (isOD) cc = 'st-overdue'; else if (s.status === 'In Progress' || s.status === 'Pending') cc = 'st-progress';
                    let sb = `<span class="badge bg-secondary" style="font-size:0.58rem">${s.status}</span>`;
                    if (s.status == 'Done') sb = '<span class="badge bg-success" style="font-size:0.58rem">Hoàn thành</span>';
                    else if (s.status == 'In Progress') sb = '<span class="badge bg-primary" style="font-size:0.58rem">Đang làm</span>';
                    else if (s.status == 'Pending') sb = '<span class="badge bg-warning text-dark" style="font-size:0.58rem">Chờ duyệt</span>';
                    if (isOD) sb = '<span class="badge bg-danger" style="font-size:0.58rem">Trễ hạn</span>';
                    let delBtn = (ROLE_ID <= 2 || ROLE_ID == 4) ? `<i class="bi bi-trash text-danger ms-2 pointer" onclick="event.stopPropagation();deleteSub(${s.id})" title="Xóa"></i>` : '';
                    subtaskListHtml += `<div class="subtask-list-item ${cc}" onclick="bootstrap.Modal.getInstance(document.getElementById('taskDetailModal')).hide();setTimeout(()=>openSubtaskDetail(${s.id}),300);">
                    <div><span class="fw-bold">${s.title}</span><div class="text-muted" style="font-size:0.68rem"><i class="bi bi-person"></i> ${s.assignee_name}${sDeadlineStr ? ' · <i class="bi bi-clock"></i> ' + new Date(sDeadlineStr).toLocaleDateString('vi-VN') : ''}</div></div>
                    <div class="d-flex align-items-center">${sb}${delBtn}</div></div>`;
                });
            } else subtaskListHtml = '<p class="p-3 text-muted text-center small">Chưa có việc con nào.</p>';

            let btns = '';
            if (ROLE_ID != 3) {
                let rowBtns = `<div class="mt-3 d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm flex-fill rounded-pill fw-bold" onclick="bootstrap.Modal.getInstance(document.getElementById('taskDetailModal')).hide();setTimeout(()=>openAddSubtaskModal(${task.id}),300);"><i class="bi bi-plus-lg me-1"></i>Thêm việc con</button>
                <button class="btn btn-outline-danger btn-sm rounded-pill fw-bold px-3" onclick="deleteTask(${task.id})"><i class="bi bi-trash me-1"></i>Xóa Task</button>
            </div>`;

                if (pct == 100 && sCount > 0) {
                    btns = `
                <div class="mt-3">
                    <button class="btn btn-success w-100 rounded-pill fw-bold mb-2 shadow-sm" onclick="bootstrap.Modal.getInstance(document.getElementById('taskDetailModal')).hide();setTimeout(()=>openAiTaskSummaryModal(${task.id}, '${task.title.replace(/'/g, "\\'")}'),300);">
                        <i class="bi bi-stars me-2"></i>Tổng kết & Đăng bài dự án (AI)
                    </button>
                    ${rowBtns}
                </div>`;
                } else {
                    btns = rowBtns;
                }
            }

            $('#taskBody').html(`<div class="row"><div class="col-md-5 border-end">
            <h5 class="fw-bold mb-2">${task.title}</h5>
            <div class="small text-muted mb-3" style="white-space:pre-wrap">${task.description || '<span class="text-muted">Không có mô tả</span>'}</div>
            <div class="bg-light p-3 rounded-3 small mb-3">
                <div class="d-flex justify-content-between mb-2"><span>Người tạo:</span><b class="text-primary"><i class="bi bi-person me-1"></i>${task.creator_name || 'N/A'}</b></div>
                <div class="d-flex justify-content-between mb-2"><span>Phòng ban:</span><b>${task.dept_name || 'N/A'}</b></div>
                <div class="d-flex justify-content-between mb-2"><span>Ưu tiên:</span><span class="priority-badge ${pClass}">${pLabel}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Hạn chót:</span><b>${task.deadline ? new Date(task.deadline.replace(' ', 'T')).toLocaleDateString('vi-VN') : 'Không có'}</b></div>
                <div class="d-flex justify-content-between"><span>Trạng thái:</span><span class="badge ${task.status == 'Done' ? 'bg-success' : 'bg-primary'}">${task.status}</span></div>
            </div>
            <div class="mb-2"><div class="d-flex justify-content-between small fw-bold mb-1"><span>Tiến độ</span><span>${pct}%</span></div>
                <div class="progress-bar-wrap" style="height:8px"><div class="progress-bar-fill ${pct == 100 ? 'complete' : ''}" style="width:${pct}%"></div></div>
                <div class="text-muted mt-1" style="font-size:0.68rem">${dCount}/${sCount} hoàn thành</div></div>
        </div><div class="col-md-7">
            <p class="extra-small fw-bold text-muted mb-2 uppercase">DANH SÁCH VIỆC CON</p>
            <div class="border rounded-3 mb-2" style="max-height:350px;overflow-y:auto">${subtaskListHtml}</div>
            ${btns}
        </div></div>`);
        });
    }

    // ===== ACTIONS =====
    function doStatus(id, s) {
        $.post('index.php?action=api_update_subtask_status', { subtask_id: id, status: s }, function (res) {
            if (!res.success) handleDragError(res.message); else location.reload();
        }, 'json');
    }

    function submitEv(e, id) {
        e.preventDefault();
        let notes = $('#evidForm textarea[name="notes"]').val().trim();
        let fileInput = $('#evidForm input[name="evidence_file"]')[0];
        let hasFile = fileInput && fileInput.files.length > 0;
        if (!notes && !hasFile) {
            Swal.fire({ title: 'Chưa có minh chứng!', text: 'Vui lòng nhập ghi chú hoặc đính kèm file trước khi gửi duyệt.', icon: 'warning' });
            return;
        }
        let fd = new FormData(document.getElementById('evidForm'));
        fd.append('subtask_id', id);
        $.ajax({
            url: 'index.php?action=api_submit_evidence', type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
            success: function (res) {
                if (res.success) Swal.fire({ title: 'Thành công!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload());
                else Swal.fire({ title: 'Lỗi!', text: res.message, icon: 'error' });
            }
        });
    }

    function approve(id) {
        Swal.fire({ title: 'Duyệt công việc?', text: 'Xác nhận duyệt subtask về Hoàn thành?', icon: 'question', showCancelButton: true, confirmButtonColor: '#10b981', confirmButtonText: 'Duyệt', cancelButtonText: 'Hủy' }).then(r => {
            if (r.isConfirmed) $.post('index.php?action=api_approve_subtask', { subtask_id: id }, function (res) {
                if (res.success) Swal.fire({ title: 'Đã duyệt!', icon: 'success', timer: 1200, showConfirmButton: false }).then(() => location.reload());
                else Swal.fire({ title: 'Lỗi!', text: res.message, icon: 'error' });
            }, 'json');
        });
    }

    function reject(id) {
        // Đóng modal subtask trước để SweetAlert textarea không bị block
        var modalEl = document.getElementById('subtaskDetailModal');
        var modalInst = bootstrap.Modal.getInstance(modalEl);
        if (modalInst) modalInst.hide();

        setTimeout(function () {
            Swal.fire({
                title: 'Từ chối công việc', input: 'textarea', inputLabel: 'Lý do từ chối:', inputPlaceholder: 'Nhập lý do...', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Từ chối', cancelButtonText: 'Hủy',
                inputValidator: v => { if (!v) return 'Vui lòng nhập lý do!'; }
            }).then(r => {
                if (r.isConfirmed) $.post('index.php?action=api_reject_subtask', { subtask_id: id, reason: r.value }, function (res) {
                    if (res.success) Swal.fire({ title: 'Đã từ chối!', icon: 'info', timer: 1200, showConfirmButton: false }).then(() => location.reload());
                    else Swal.fire({ title: 'Lỗi!', text: res.message, icon: 'error' });
                }, 'json');
            });
        }, 350);
    }

    function deleteSub(id) {
        Swal.fire({ title: 'Xóa công việc con?', text: 'Mọi minh chứng sẽ bị mất!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' }).then(r => {
            if (r.isConfirmed) $.post('index.php?action=api_delete_subtask', { subtask_id: id }, function (res) { if (res.success) location.reload(); else Swal.fire({ title: 'Lỗi!', text: res.message, icon: 'error' }); }, 'json');
        });
    }

    function deleteTask(id) {
        Swal.fire({ title: 'Xóa toàn bộ Task?', html: 'Tất cả công việc con và minh chứng sẽ bị <b>xóa vĩnh viễn</b>!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Xóa Task', cancelButtonText: 'Hủy' }).then(r => {
            if (r.isConfirmed) $.post('index.php?action=api_delete_task', { task_id: id }, function (res) {
                if (res.success) { bootstrap.Modal.getInstance(document.getElementById('taskDetailModal')).hide(); Swal.fire({ title: 'Đã xóa!', icon: 'success', timer: 1200, showConfirmButton: false }).then(() => location.reload()); }
                else Swal.fire({ title: 'Lỗi!', text: res.message, icon: 'error' });
            }, 'json');
        });
    }

    // ===== GIA HẠN SUBTASK TRỄ HẠN =====
    function extendSubtask(id) {
        Swal.fire({
            title: 'Gia hạn thời gian',
            html: `<p class="small text-muted mb-2">Chọn ngày gia hạn mới cho subtask:</p><input type="date" id="swal-new-deadline" class="form-control" min="${TODAY}">`,
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            confirmButtonText: '<i class="bi bi-calendar-plus me-1"></i>Gia hạn',
            cancelButtonText: 'Hủy',
            preConfirm: () => {
                let val = document.getElementById('swal-new-deadline').value;
                if (!val) { Swal.showValidationMessage('Vui lòng chọn ngày!'); return false; }
                if (val < TODAY) { Swal.showValidationMessage('Không được chọn ngày quá khứ!'); return false; }
                return val;
            }
        }).then(r => {
            if (r.isConfirmed) {
                $.post('index.php?action=api_extend_subtask', { subtask_id: id, new_deadline: r.value }, function (res) {
                    if (res.success) Swal.fire({ title: 'Đã gia hạn!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload());
                    else Swal.fire({ title: 'Lỗi!', text: res.message, icon: 'error' });
                }, 'json');
            }
        });
    }

    function requestExtension(id) {
        // Đóng modal chi tiết để tránh lỗi kẹt focus của Bootstrap
        const detailModalEl = document.getElementById('subtaskDetailModal');
        const bModal = bootstrap.Modal.getInstance(detailModalEl);
        if (bModal) bModal.hide();

        // Bước 1: Chọn ngày
        Swal.fire({
            title: 'Yêu cầu gia hạn (1/2)',
            text: 'Vui lòng chọn ngày bạn mong muốn hoàn thành:',
            input: 'date',
            inputAttributes: { min: TODAY },
            showCancelButton: true,
            confirmButtonText: 'Tiếp tục',
            cancelButtonText: 'Hủy',
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value) return 'Vui lòng chọn ngày!';
            }
        }).then((resDate) => {
            if (resDate.isConfirmed) {
                // Bước 2: Nhập lý do (Sử dụng input textarea bản gốc của Swal để tránh lỗi focus)
                Swal.fire({
                    title: 'Giải trình lý do (2/2)',
                    input: 'textarea',
                    inputPlaceholder: 'Giải trình lý do trễ hạn và cam kết tiến độ...',
                    inputAttributes: { 'aria-label': 'Lý do gia hạn' },
                    showCancelButton: true,
                    confirmButtonText: 'Gửi yêu cầu',
                    cancelButtonText: 'Quay lại',
                    reverseButtons: true,
                    inputValidator: (value) => {
                        if (!value) return 'Vui lòng nhập lý do!';
                    }
                }).then((resReason) => {
                    if (resReason.isConfirmed) {
                        $.post('index.php?action=api_request_extension', {
                            subtask_id: id,
                            target_date: resDate.value,
                            reason: resReason.value
                        }, function (res) {
                            if (res.success) {
                                Swal.fire({ title: 'Đã gửi!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false }).then(() => location.reload());
                            } else {
                                Swal.fire({ title: 'Lỗi!', text: res.message, icon: 'error' }).then(() => { if (bModal) bModal.show(); });
                            }
                        }, 'json');
                    } else if (resReason.dismiss === Swal.DismissReason.cancel) {
                        // Nếu bấm quay lại thì hiện lại modal ban đầu
                        if (bModal) bModal.show();
                    }
                });
            } else if (resDate.dismiss === Swal.DismissReason.cancel) {
                // Nếu bấm hủy thì hiện lại modal ban đầu
                if (bModal) bModal.show();
            }
        });
    }

    // ===== LƯU MINH CHỨNG RIÊNG (không gửi duyệt) =====
    function saveEvidenceOnly(id) {
        let notes = $('#evidForm textarea[name="notes"]').val().trim();
        let fileInput = $('#evidForm input[name="evidence_file"]')[0];
        let hasFile = fileInput && fileInput.files.length > 0;
        if (!notes && !hasFile) {
            Swal.fire({ title: 'Chưa có nội dung!', text: 'Vui lòng nhập ghi chú hoặc đính kèm file.', icon: 'warning' });
            return;
        }
        let fd = new FormData(document.getElementById('evidForm'));
        fd.append('subtask_id', id);
        $.ajax({
            url: 'index.php?action=api_save_evidence', type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
            success: function (res) {
                if (res.success) {
                    // Đóng modal + reload trang
                    var modalInst = bootstrap.Modal.getInstance(document.getElementById('subtaskDetailModal'));
                    if (modalInst) modalInst.hide();
                    Swal.fire({ title: 'Đã lưu!', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload());
                }
                else Swal.fire({ title: 'Lỗi!', text: res.message, icon: 'error' });
            }
        });
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>