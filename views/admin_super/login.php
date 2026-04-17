<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Portal | Relioo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #212529; color: #fff; }
        .login-card { background-color: #343a40; border: 1px solid #495057; border-radius: 1.5rem; box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.5); }
        .brand-logo { font-weight: 700; color: #ff4d4f; font-size: 2.5rem; letter-spacing: -1px; display: flex; align-items: center; justify-content: center; gap: 12px; }
        .form-control { background-color: #495057; color: #fff; border-color: #6c757d; border-radius: 0.75rem; }
        .form-control:focus { background-color: #495057; color: #fff; border-color: #ff4d4f; box-shadow: 0 0 0 0.25rem rgba(255, 77, 79, 0.25); }
        .form-floating > label { color: #ced4da; }
        .btn-primary { background-color: #ff4d4f; border-color: #ff4d4f; color: #fff; border-radius: 50rem; padding: 0.75rem; }
        .btn-primary:hover, .btn-primary:focus { background-color: #e63e40; border-color: #e63e40; }
    </style>
</head>
<body class="d-flex align-items-center py-4 min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <div class="brand-logo">
                        Relioo Super Admin
                    </div>
                    <p class="text-muted">Hệ thống quản trị cao cấp</p>
                </div>
                
                <div class="card login-card">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="text-center mb-4 text-light">Xác thực Admin</h4>
                        
                        <div id="login-alert" class="alert alert-danger d-none" role="alert"></div>
                        
                        <form id="loginForm">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Tên đăng nhập" required>
                                <label for="username">Tài khoản Super Admin</label>
                            </div>
                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu" required>
                                <label for="password">Mật khẩu</label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-lg fw-bold" type="submit" id="btn-login">
                                    <span class="spinner-border spinner-border-sm d-none" id="login-spinner" role="status" aria-hidden="true"></span>
                                    <span>Vào trung tâm điều khiển</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    url: 'index.php?action=admin_login_submit',
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
                        $alert.text('Lỗi kết nối Máy chủ.').removeClass('d-none');
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                    }
                });
            });
        });
    </script>
</body>
</html>