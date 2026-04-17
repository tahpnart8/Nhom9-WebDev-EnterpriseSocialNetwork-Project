<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Relioo</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .login-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 450px;
        }
        .brand-logo {
            font-weight: 700;
            color: #ff4d4f;
            font-size: 2.5rem;
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .btn-primary {
            background-color: #ff4d4f;
            border-color: #ff4d4f;
            border-radius: 50rem;
            padding: 0.75rem;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #e63e40;
            border-color: #e63e40;
        }
        .form-control {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
        }
        .bg-image {
            background-image: url('src/hinh-nen-mau-hong-13-1.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .bg-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(255, 77, 79, 0.3) 0%, rgba(0, 0, 0, 0.4) 100%);
        }
        .welcome-text {
            position: relative;
            z-index: 2;
            color: white;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.3);
            text-align: center;
        }
        .welcome-text h1 {
            font-size: 4rem;
            font-weight: 800;
            letter-spacing: -1px;
        }
    </style>
</head>
<body>
    <div class="container-fluid min-vh-100 p-0">
        <div class="row g-0 min-vh-100">
            <!-- Left Side - Background Image -->
            <div class="col-md-6 col-lg-7 d-none d-md-flex align-items-center justify-content-center bg-image">
                <div class="bg-overlay"></div>
                <div class="welcome-text">
                    <h1>Relioo Xin chào</h1>
                    <p class="fs-4 mt-3 opacity-75">Nền tảng giao tiếp và quản lý công việc thế hệ mới</p>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="col-md-6 col-lg-5 d-flex align-items-center justify-content-center px-4 px-md-5 bg-white">
                <div class="w-100" style="max-width: 450px;">
                    <div class="text-center mb-5">
                        <div class="brand-logo mb-2">
                            <img src="src/logo.png" alt="Logo" style="height: 50px; border-radius: 50%;">
                            Relioo
                        </div>
                        <p class="text-muted fs-5">Mạng xã hội Doanh nghiệp</p>
                    </div>
                    
                    <h4 class="fw-bold mb-4">Đăng nhập hệ thống</h4>
                    
                    <div id="login-alert" class="alert alert-danger d-none" role="alert"></div>
                    
                    <form id="loginForm">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control bg-light border-0" id="username" name="username" placeholder="Tên đăng nhập" required>
                            <label for="username" class="text-muted">Tên đăng nhập</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control bg-light border-0" id="password" name="password" placeholder="Mật khẩu" required>
                            <label for="password" class="text-muted">Mật khẩu</label>
                        </div>
                        
                        <div class="d-grid gap-3">
                            <button class="btn btn-primary btn-lg fw-bold shadow-sm" type="submit" id="btn-login">
                                <span class="spinner-border spinner-border-sm d-none" id="login-spinner" role="status" aria-hidden="true"></span>
                                <span>Đăng nhập</span>
                            </button>
                        </div>
                    </form>

                    <div class="mt-5 text-center px-4 py-3 bg-light rounded-4 border">
                        <p class="text-muted mb-2 small fw-semibold text-uppercase">Dành cho Nhà Quản trị</p>
                        <a href="index.php?action=register_company" class="btn btn-outline-danger btn-sm rounded-pill px-4 mb-2">Đăng ký Workspace (CEO)</a>
                        <br>
                        <a href="index.php?action=admin_secret_portal" class="text-secondary small text-decoration-none border-bottom border-secondary d-inline-block mt-1"><i class="bi bi-shield-lock"></i> Đăng nhập Super Admin</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- jQuery (Bắt buộc theo yêu cầu dùng AJAX của nhóm) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                let $btn = $('#btn-login');
                let $spinner = $('#login-spinner');
                let $alert = $('#login-alert');
                
                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');
                $alert.addClass('d-none');

                $.ajax({
                    url: 'index.php?action=login_submit',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            window.location.href = response.redirect;
                        } else {
                            $alert.text(response.message).removeClass('d-none');
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                        }
                    },
                    error: function() {
                        $alert.text('Đã xảy ra lỗi kết nối Máy chủ. Kiểm tra lại XAMPP/Mạng của bạn.').removeClass('d-none');
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                    }
                });
            });
        });
    </script>
</body>
</html>
