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
        }
        .brand-logo {
            font-weight: 700;
            color: #0d6efd;
            font-size: 2rem;
            letter-spacing: -1px;
        }
    </style>
</head>
<body class="d-flex align-items-center py-4 min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <div class="brand-logo">Relioo</div>
                    <p class="text-muted">Mạng xã hội Doanh nghiệp</p>
                </div>
                
                <div class="card login-card">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="text-center mb-4">Đăng nhập hệ thống</h4>
                        
                        <div id="login-alert" class="alert alert-danger d-none" role="alert"></div>
                        
                        <form id="loginForm">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Tên đăng nhập" required>
                                <label for="username">Tên đăng nhập</label>
                            </div>
                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu" required>
                                <label for="password">Mật khẩu</label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-lg" type="submit" id="btn-login">
                                    <span class="spinner-border spinner-border-sm d-none" id="login-spinner" role="status" aria-hidden="true"></span>
                                    <span>Đăng nhập</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery (Bắt buộc theo yêu cầu dùng AJAX của nhóm) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault(); // Ngăn chặn load lại trang
                
                let $btn = $('#btn-login');
                let $spinner = $('#login-spinner');
                let $alert = $('#login-alert');
                
                // Trạng thái Loading
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
                            // Chuyển hướng dashboard
                            window.location.href = response.redirect;
                        } else {
                            // Thông báo lỗi
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
