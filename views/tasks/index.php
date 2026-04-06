<?php include __DIR__ . '/../layouts/header.php'; ?>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<style>
/* ===== KANBAN BOARD STYLES ===== */
.kanban-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.kanban-board {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding-bottom: 1rem;
    min-height: 70vh;
}
.kanban-column {
    min-width: 280px;
    flex: 1;
    background: #f1f5f9;
    border-radius: 1rem;
    padding: 1rem;
    display: flex;
    flex-direction: column;
}
.column-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0.5rem 1rem;
    border-bottom: 2px solid transparent;
    margin-bottom: 0.5rem;
}
.column-header h6 {
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin: 0;
}
.column-count {
    background: #e2e8f0;
    color: #475569;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 100px;
}
/* Column Color Indicators */
.col-todo .column-header     { border-bottom-color: #64748b; }
.col-progress .column-header { border-bottom-color: #3b82f6; }
.col-pending .column-header  { border-bottom-color: #f59e0b; }
.col-done .column-header     { border-bottom-color: #22c55e; }

.col-todo .column-count      { background: #f1f5f9; color: #64748b; }
.col-progress .column-count  { background: #dbeafe; color: #2563eb; }
.col-pending .column-count   { background: #fef3c7; color: #d97706; }
.col-done .column-count      { background: #dcfce7; color: #16a34a; }

/* Kanban Card (Subtask) */
.kanban-card-list {
    flex-grow: 1;
    min-height: 100px;
}
.kanban-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem;
    margin-bottom: 0.65rem;
    border: 1px solid #e2e8f0;
    cursor: grab;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.kanban-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}
.kanban-card:active {
    cursor: grabbing;
    opacity: 0.85;
}
.sortable-ghost {
    opacity: 0.4;
    background: #dbeafe !important;
}
.sortable-chosen {
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.25);
}

.card-task-label {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #3b82f6;
    margin-bottom: 0.3rem;
}
.card-title {
    font-weight: 600;
    font-size: 0.9rem;
    color: #1e293b;
    margin-bottom: 0.25rem;
}
.card-desc {
    font-size: 0.8rem;
    color: #64748b;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.card-assignee-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #f1f5f9;
    padding: 3px 10px;
    border-radius: 100px;
    font-size: 0.72rem;
    font-weight: 600;
    color: #475569;
}
.card-assignee-badge .mini-avatar {
    width: 18px; height: 18px;
    border-radius: 50%;
    background: #3b82f6;
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.55rem;
    font-weight: 800;
}
.priority-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.priority-high   { background: #ef4444; }
.priority-medium { background: #f59e0b; }
.priority-low    { background: #22c55e; }
.card-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 0.6rem;
    padding-top: 0.5rem;
    border-top: 1px solid #f1f5f9;
}
.card-deadline {
    font-size: 0.72rem;
    color: #94a3b8;
}
.card-deadline.overdue {
    color: #ef4444;
    font-weight: 600;
}
</style>

<div class="kanban-header">
    <div>
        <p class="text-muted mb-0 small">
            Tổng: <strong><?php echo count($subtasks); ?></strong> công việc nhỏ
            <?php if(!empty($tasks)): ?> 
            | <strong><?php echo count($tasks); ?></strong> task lớn
            <?php endif; ?>
        </p>
    </div>
    
    <?php if($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2): ?>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#createTaskModal">
            <i class="bi bi-folder-plus me-1"></i> Tạo Task
        </button>
        <button class="btn btn-primary rounded-pill px-3 shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#createSubtaskModal">
            <i class="bi bi-plus-lg me-1"></i> Giao việc
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- ===== KANBAN BOARD 4 COLUMNS ===== -->
<div class="kanban-board">
    <?php 
    $columnMeta = [
        'To Do'       => ['class' => 'col-todo',     'icon' => 'bi-circle',        'label' => 'Cần làm'],
        'In Progress' => ['class' => 'col-progress', 'icon' => 'bi-arrow-repeat',  'label' => 'Đang xử lý'],
        'Pending'     => ['class' => 'col-pending',  'icon' => 'bi-hourglass-split','label' => 'Chờ duyệt'],
        'Done'        => ['class' => 'col-done',     'icon' => 'bi-check-circle',  'label' => 'Hoàn thành']
    ];
    foreach ($columnMeta as $status => $meta):
    ?>
    <div class="kanban-column <?php echo $meta['class']; ?>">
        <div class="column-header">
            <h6><i class="bi <?php echo $meta['icon']; ?> me-2"></i><?php echo $meta['label']; ?></h6>
            <span class="column-count"><?php echo count($columns[$status]); ?></span>
        </div>
        
        <div class="kanban-card-list" id="kanban-<?php echo str_replace(' ', '-', strtolower($status)); ?>" data-status="<?php echo $status; ?>">
            <?php foreach ($columns[$status] as $card): ?>
            <div class="kanban-card" data-id="<?php echo $card['id']; ?>">
                <div class="card-task-label"><?php echo htmlspecialchars($card['task_title'] ?? 'TASK'); ?></div>
                <div class="card-title"><?php echo htmlspecialchars($card['title']); ?></div>
                <?php if(!empty($card['description'])): ?>
                <div class="card-desc"><?php echo htmlspecialchars($card['description']); ?></div>
                <?php endif; ?>
                
                <div class="card-meta">
                    <div class="card-assignee-badge">
                        <span class="mini-avatar"><?php echo mb_substr(trim($card['assignee_name']), 0, 1, 'UTF-8'); ?></span>
                        <?php echo htmlspecialchars($card['assignee_name']); ?>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <?php 
                            $priorityClass = 'priority-medium';
                            if(isset($card['priority'])) {
                                $priorityClass = 'priority-' . strtolower($card['priority']);
                            }
                        ?>
                        <span class="priority-dot <?php echo $priorityClass; ?>" title="<?php echo $card['priority'] ?? 'Medium'; ?>"></span>
                        
                        <?php if(!empty($card['deadline'])): 
                            $isOverdue = strtotime($card['deadline']) < time() && $card['status'] != 'Done';
                        ?>
                        <span class="card-deadline <?php echo $isOverdue ? 'overdue' : ''; ?>">
                            <i class="bi bi-calendar3 me-1"></i><?php echo date('d/m', strtotime($card['deadline'])); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($_SESSION['role_id'] == 2 || $_SESSION['role_id'] == 1): ?>
                <!-- Nút duyệt nhanh cho Leader/CEO (chỉ hiện ở cột Pending) -->
                <?php if($card['status'] == 'Pending'): ?>
                <div class="mt-2 pt-2 border-top d-flex gap-2">
                    <button class="btn btn-sm btn-success rounded-pill flex-fill btn-approve" data-id="<?php echo $card['id']; ?>" data-status="Done">
                        <i class="bi bi-check-lg"></i> Duyệt
                    </button>
                    <button class="btn btn-sm btn-outline-danger rounded-pill flex-fill btn-approve" data-id="<?php echo $card['id']; ?>" data-status="In Progress">
                        <i class="bi bi-arrow-return-left"></i> Trả lại
                    </button>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ===== MODAL: TẠO TASK LỚN ===== -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus text-primary me-2"></i>Tạo Task mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-4">
        <form id="formCreateTask">
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold">TIÊU ĐỀ TASK</label>
            <input type="text" name="title" class="form-control bg-light border-0" required placeholder="VD: Redesign Landing Page">
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold">MÔ TẢ</label>
            <textarea name="description" class="form-control bg-light border-0" rows="3" placeholder="Chi tiết yêu cầu..."></textarea>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label text-muted small fw-bold">ĐỘ ƯU TIÊN</label>
              <select name="priority" class="form-select bg-light border-0">
                <option value="Low">🟢 Thấp</option>
                <option value="Medium" selected>🟡 Trung bình</option>
                <option value="High">🔴 Cao</option>
              </select>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label text-muted small fw-bold">DEADLINE</label>
              <input type="datetime-local" name="deadline" class="form-control bg-light border-0">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer border-top-0">
        <button class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
        <button class="btn btn-primary rounded-pill px-4" id="btnSaveTask">Tạo Task</button>
      </div>
    </div>
  </div>
</div>

<!-- ===== MODAL: GIAO SUBTASK ===== -->
<div class="modal fade" id="createSubtaskModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-person-plus text-primary me-2"></i>Giao việc cho nhân viên</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-4">
        <form id="formCreateSubtask">
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold">THUỘC TASK LỚN</label>
            <select name="task_id" class="form-select bg-light border-0" required>
              <option value="">-- Chọn Task --</option>
              <?php foreach($tasks as $t): ?>
              <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['title']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold">GIAO CHO NHÂN VIÊN</label>
            <select name="assignee_id" class="form-select bg-light border-0" required>
              <option value="">-- Chọn nhân viên --</option>
              <?php foreach($staffList as $s): ?>
              <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['full_name']); ?> (<?php echo $s['role_name']; ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold">TIÊU ĐỀ CÔNG VIỆC</label>
            <input type="text" name="title" class="form-control bg-light border-0" required placeholder="VD: Code trang Login">
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold">MÔ TẢ CHI TIẾT</label>
            <textarea name="description" class="form-control bg-light border-0" rows="2" placeholder="Các bước cần hoàn thành..."></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold">DEADLINE</label>
            <input type="datetime-local" name="deadline" class="form-control bg-light border-0">
          </div>
        </form>
      </div>
      <div class="modal-footer border-top-0">
        <button class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
        <button class="btn btn-primary rounded-pill px-4" id="btnSaveSubtask">Giao việc</button>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
    // ===== SORTABLE.JS: Kéo thả thẻ giữa các cột =====
    document.querySelectorAll('.kanban-card-list').forEach(function(el) {
        new Sortable(el, {
            group: 'kanban',
            animation: 200,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: function(evt) {
                var subtaskId = evt.item.dataset.id;
                var newStatus = evt.to.dataset.status;
                
                $.post('index.php?action=api_update_subtask_status', {
                    subtask_id: subtaskId,
                    status: newStatus
                }, function(res) {
                    if (!res.success) {
                        alert(res.message);
                        location.reload(); // Rollback vị trí thẻ
                    }
                }, 'json').fail(function() {
                    alert('Lỗi kết nối máy chủ!');
                    location.reload();
                });
            }
        });
    });

    // ===== Nút Duyệt / Trả lại (Leader) =====
    $(document).on('click', '.btn-approve', function() {
        var $btn = $(this);
        var subtaskId = $btn.data('id');
        var newStatus = $btn.data('status');
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.post('index.php?action=api_update_subtask_status', {
            subtask_id: subtaskId,
            status: newStatus
        }, function(res) {
            location.reload();
        }, 'json');
    });

    // ===== Tạo Task: AJAX =====
    $('#btnSaveTask').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true);
        
        $.post('index.php?action=api_create_task', $('#formCreateTask').serialize(), function(res) {
            if (res.success) {
                location.reload();
            } else {
                alert(res.message);
                $btn.prop('disabled', false);
            }
        }, 'json');
    });

    // ===== Tạo Subtask: AJAX =====
    $('#btnSaveSubtask').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true);
        
        $.post('index.php?action=api_create_subtask', $('#formCreateSubtask').serialize(), function(res) {
            if (res.success) {
                location.reload();
            } else {
                alert(res.message);
                $btn.prop('disabled', false);
            }
        }, 'json');
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
