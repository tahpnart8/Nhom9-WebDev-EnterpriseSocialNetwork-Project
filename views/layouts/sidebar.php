<?php
// Define active tab
$currentAction = $_GET['action'] ?? 'dashboard';
// Role helper
$roleId = $_SESSION['role_id'] ?? 3; // Default to staff
?>
<aside class="sidebar offcanvas-lg offcanvas-start border-0 bg-white shadow-sm" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header d-lg-none d-flex justify-content-between align-items-center pb-0 px-3">
        <h5 class="offcanvas-title fw-bold text-primary" id="sidebarMenuLabel">Relioo Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
    </div>
    
    <div class="offcanvas-body d-flex flex-column h-100 p-0 px-lg-0">
        <a href="index.php?action=dashboard" class="brand-logo d-none d-lg-flex mt-2 ms-2">
            <img src="src/logo.png" alt="Logo" style="height: 36px; border-radius: 50%;">
            Relioo
        </a>

        <ul class="sidebar-menu px-3">
            <li class="menu-label mt-lg-0 mt-3">Điều hướng chính</li>
        
        <li class="menu-item">
            <a href="index.php?action=social" class="menu-link <?php echo $currentAction == 'social' ? 'active' : ''; ?>">
                <i class="bi bi-house-door"></i>
                Bảng tin nội bộ
            </a>
        </li>
        
        <li class="menu-item">
            <a href="index.php?action=tasks" class="menu-link <?php echo $currentAction == 'tasks' ? 'active' : ''; ?>">
                <i class="bi bi-kanban"></i>
                Dự án & Công việc
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
    </div>
</aside>
