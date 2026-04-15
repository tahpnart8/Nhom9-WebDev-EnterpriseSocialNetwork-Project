<?php
// Define active tab
$currentAction = $_GET['action'] ?? 'dashboard';
// Role helper
$roleId = $_SESSION['role_id'] ?? 3; // Default to staff
?>
<aside class="sidebar">
    <a href="index.php?action=dashboard" class="brand-logo">
        <img src="src/logo.png" alt="Logo" style="height: 36px; border-radius: 50%;">
        Relioo
    </a>

    <ul class="sidebar-menu">
        <li class="menu-label">Điều hướng chính</li>
        
        <li class="menu-item">
            <a href="index.php?action=social" class="menu-link <?php echo $currentAction == 'social' ? 'active' : ''; ?>">
                <i class="bi bi-house-door"></i>
                Bảng tin nội bộ
            </a>
        </li>
        
        <li class="menu-item">
            <a href="index.php?action=tasks" class="menu-link <?php echo $currentAction == 'tasks' ? 'active' : ''; ?>">
                <i class="bi bi-kanban"></i>
                Quản lý công việc
            </a>
        </li>
        
        <li class="menu-item">
            <a href="index.php?action=chat" class="menu-link <?php echo $currentAction == 'chat' ? 'active' : ''; ?>">
                <i class="bi bi-chat-dots"></i>
                Tin nhắn
            </a>
        </li>

        <?php if($roleId == 1 || $roleId == 2): // CEO or Leader ?>
        <li class="menu-label">Phân tích (Leader)</li>
        <li class="menu-item">
            <a href="index.php?action=dashboard" class="menu-link <?php echo $currentAction == 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-pie-chart"></i>
                Master Dashboard
            </a>
        </li>
        <?php else: // Menu riêng cho Staff ?>
        <li class="menu-item">
            <a href="index.php?action=dashboard" class="menu-link <?php echo $currentAction == 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                Tổng quan cá nhân
            </a>
        </li>
        <?php endif; ?>

        <?php if($roleId == 4 || $roleId == 1): // Admin or CEO ?>
        <li class="menu-label">Quản trị Hệ thống</li>
        <li class="menu-item">
            <a href="index.php?action=admin_users" class="menu-link <?php echo $currentAction == 'admin_users' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                Nhân sự
            </a>
        </li>
        <li class="menu-item">
            <a href="index.php?action=admin_departments" class="menu-link <?php echo $currentAction == 'admin_departments' ? 'active' : ''; ?>">
                <i class="bi bi-building"></i>
                Phòng ban
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- User Profile area -->
    <a href="index.php?action=profile" class="user-profile-tab" style="text-decoration: none;">
        <div class="avatar-circle shadow-sm" style="flex-shrink: 0;">
            <?php if(!empty($_SESSION['avatar_url'])): ?>
                <img src="<?php echo htmlspecialchars($_SESSION['avatar_url']); ?>" class="w-100 h-100 rounded-circle" style="object-fit:cover">
            <?php else: ?>
                <?php 
                    $nameParts = explode(' ', trim($_SESSION['full_name'] ?? 'User'));
                    echo mb_substr(end($nameParts), 0, 1, 'UTF-8'); 
                ?>
            <?php endif; ?>
        </div>
        <div class="user-info-text">
            <p class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Guest'); ?></p>
            <p class="user-role">
                <?php
                    $roleNames = [1 => 'CEO / Giám đốc', 2 => 'Trưởng phòng', 3 => 'Nhân viên', 4 => 'Quản trị viên'];
                    echo $roleNames[$_SESSION['role_id'] ?? 3];
                ?>
            </p>
        </div>
        <i class="bi bi-gear text-muted ms-auto pe-1"></i>
    </a>
</aside>
