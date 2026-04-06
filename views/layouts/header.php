<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relioo - Mạng xã hội doanh nghiệp</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/css/style.css">
    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>
<body>
    <div id="app-wrapper">
        <!-- Include Sidebar via PHP -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content Wrapper -->
        <main id="main-content">
            <!-- Topbar (Search, Notifications) -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h3 class="fw-bold mb-0"><?php echo htmlspecialchars($pageTitle ?? 'Tổng quan'); ?></h3>
                
                <div class="d-flex align-items-center gap-3">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" class="form-control rounded-pill ps-5 bg-white border" placeholder="Tìm kiếm..." style="width: 300px;">
                    </div>
                    
                    <button class="btn btn-light rounded-circle border shadow-sm position-relative d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-bell text-muted fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    </button>
                    
                    <a href="index.php?action=logout" class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-2">
                        <i class="bi bi-box-arrow-right"></i> Thoát
                    </a>
                </div>
            </div>
