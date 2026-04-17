<aside class="right-sidebar d-none d-xl-flex flex-column gap-3">
    <!-- Box: Bảng xếp hạng (TÍNH NĂNG MỚI TỪ PROCEDURE) - Chỉ hiện ở Social -->
    <?php if (($currentAction ?? '') === 'social' && !empty($leaderboard)): ?>
        <div class="relioo-card p-0 border-0 shadow-sm overflow-hidden bg-white">
            <div class="p-3 bg-primary text-white d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold small"><i class="bi bi-trophy-fill me-2 text-warning"></i> BẢNG XẾP HẠNG</h6>
                <span class="badge bg-white text-primary rounded-pill" style="font-size: 0.6rem;">Top 10</span>
            </div>
            <div class="p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($leaderboard as $index => $userLB): ?>
                        <div class="list-group-item border-light d-flex align-items-center gap-2 py-2 px-3">
                            <div class="fw-bold text-muted" style="width: 20px; font-size: 0.8rem;">
                                <?php
                                if ($index == 0)
                                    echo '🥇';
                                elseif ($index == 1)
                                    echo '🥈';
                                elseif ($index == 2)
                                    echo '🥉';
                                else
                                    echo ($index + 1);
                                ?>
                            </div>
                            <div class="avatar-circle shadow-sm flex-shrink-0"
                                style="width: 32px; height: 32px; font-size: 12px;">
                                <?php if (!empty($userLB['avatar_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($userLB['avatar_url']); ?>"
                                        class="w-100 h-100 rounded-circle" style="object-fit:cover">
                                <?php else: ?>
                                    <?php echo mb_substr(trim($userLB['full_name']), 0, 1, 'UTF-8'); ?>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-dark text-truncate small" style="font-size: 0.75rem;">
                                    <?php echo htmlspecialchars($userLB['full_name']); ?></div>
                                <div class="text-muted" style="font-size: 0.65rem;">Xong: <b
                                        class="text-success"><?php echo $userLB['tasks_done']; ?></b> việc</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Box: Việc gấp cần xử lý -->
    <div class="relioo-card p-3 urgent-tasks-box">
        <h6 class="fw-bold mb-4 text-dark text-center pb-2" style="border-bottom: 2px dashed rgba(225,29,72,0.2);">🔥
            Việc gấp cần xử lý</h6>

        <div id="urgentTasksContainer" class="pe-2" style="max-height: 280px; overflow-y: auto;">
            <p class="text-center text-muted small py-3 mb-0" id="urgentTasksLoading">
                <span class="spinner-border spinner-border-sm me-1"></span> Đang tải...
            </p>
            <div id="urgentTasksList"></div>
            <p class="text-center text-muted small py-3 mb-0 d-none" id="urgentTasksEmpty">Không có việc gấp.</p>
        </div>
    </div>

    <?php if (($currentAction ?? '') === 'social'): ?>
        <div class="relioo-card p-4 shadow-sm">
            <h6 class="fw-bold mb-3 text-muted small text-uppercase">Quy định mạng nội bộ</h6>
            <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                <li>Tôn trọng đồng nghiệp.</li>
                <li>Chia sẻ tài liệu đúng phòng ban.</li>
                <li>Không bàn luận các nội dung vi phạm pháp luật.</li>
                <li>Giữ văn phong chuyên nghiệp.</li>
            </ul>
        </div>
    <?php endif; ?>
</aside>

<style>
    /* Styling specifics for urgent tasks */
    .urgent-task-item {
        transition: all 0.2s ease;
    }

    .urgent-task-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .urgent-task-number {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #ef4444;
        /* red as default */
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .urgent-task-progress-bg {
        height: 6px;
        background-color: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 6px;
    }

    .urgent-task-progress-bar {
        height: 100%;
        background-color: #22c55e;
        border-radius: 3px;
        transition: width 0.5s ease;
    }

    /* Custom Scrollbar for urgent tasks */
    #urgentTasksContainer::-webkit-scrollbar {
        width: 4px;
    }

    #urgentTasksContainer::-webkit-scrollbar-track {
        background: transparent;
    }

    #urgentTasksContainer::-webkit-scrollbar-thumb {
        background: rgba(225, 29, 72, 0.2);
        border-radius: 4px;
    }

    #urgentTasksContainer::-webkit-scrollbar-thumb:hover {
        background: rgba(225, 29, 72, 0.4);
    }
</style>

<script>
    $(document).ready(function () {
        function formatTimeLeft(seconds) {
            if (seconds > 315360000) return 'Chưa thiết lập';
            if (seconds < 0) return 'Đã trễ hạn!';
            if (seconds < 60) return 'còn ' + seconds + ' giây';
            if (seconds < 3600) return 'còn ' + Math.floor(seconds / 60) + ' phút';
            if (seconds < 86400) return 'còn ' + Math.floor(seconds / 3600) + ' giờ';
            return 'còn ' + Math.floor(seconds / 86400) + ' ngày';
        }

        function fetchUrgentSubtasks() {
            $.getJSON('index.php?action=api_urgent_subtasks', function (res) {
                $('#urgentTasksLoading').addClass('d-none');

                if (res.success) {
                    var items = res.data || [];
                    if (items.length === 0) {
                        $('#urgentTasksEmpty').removeClass('d-none');
                        $('#urgentTasksList').empty();
                        return;
                    } else {
                        $('#urgentTasksEmpty').addClass('d-none');
                    }

                    var html = '';
                    items.forEach(function (st, index) {
                        var numberColor = st.badge_color === 'danger' ? '#ef4444' : (st.badge_color === 'warning' ? '#eab308' : '#22c55e');

                        html += `
                    <div class="urgent-task-item bg-white p-2 rounded-3 mb-2 shadow-sm border border-light position-relative" 
                         style="cursor: pointer; transition: all 0.2s; overflow: hidden;"
                         onclick="window.location.href='index.php?action=tasks&subtask_id=${st.id}'"
                         onmouseover="this.classList.replace('border-light', 'border-primary'); this.classList.replace('shadow-sm', 'shadow');"
                         onmouseout="this.classList.replace('border-primary', 'border-light'); this.classList.replace('shadow', 'shadow-sm');">
                        <div class="d-flex gap-2">
                            <div class="urgent-task-number mt-1" style="background-color: ${numberColor}">${index + 1}</div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-start mb-1 gap-1">
                                    <h6 class="mb-0 text-dark fw-bold text-truncate" style="font-size: 0.85rem; line-height: 1.3;">
                                        ${st.subtask_title}
                                    </h6>
                                    <span class="badge bg-${st.badge_color} text-white px-2 rounded-pill flex-shrink-0" style="font-size: 0.65rem;">${st.priority_label}</span>
                                </div>
                                
                                <div class="text-muted fw-medium text-truncate mb-1" style="font-size: 0.7rem;">
                                    <i class="bi bi-folder2-open"></i> ${st.task_title}
                                </div>

                                <div class="text-dark fw-bold mb-1" style="font-size: 0.7rem;">
                                    Deadline: <span class="fw-normal text-${st.badge_color}">${formatTimeLeft(st.seconds_left)}</span>
                                </div>
                                
                                <div class="urgent-task-progress-bg">
                                    <div class="urgent-task-progress-bar" style="width: ${st.progress_percent}%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                    });
                    $('#urgentTasksList').html(html);
                }
            });
        }

        // Initial fetch
        fetchUrgentSubtasks();

        // Poll every 30 seconds
        setInterval(fetchUrgentSubtasks, 30000);
    });
</script>