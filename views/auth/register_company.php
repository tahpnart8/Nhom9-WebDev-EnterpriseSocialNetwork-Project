<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Không gian Doanh nghiệp | Relioo</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .register-card { border: none; border-radius: 1rem; box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08); }
        .brand-logo { font-weight: 700; color: #ff4d4f; font-size: 2.2rem; letter-spacing: -1px; display: flex; align-items: center; justify-content: center; gap: 12px; }
        .btn-primary { background-color: #ff4d4f; border-color: #ff4d4f; }
        .btn-primary:hover, .btn-primary:focus { background-color: #e63e40; border-color: #e63e40; }
    </style>
</head>
<body class="d-flex align-items-center py-4 min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="text-center mb-4">
                    <div class="brand-logo">
                        <img src="src/logo.png" alt="Logo" style="height: 44px; border-radius: 50%;">
                        Relioo
                    </div>
                    <p class="text-muted">Mạng xã hội Doanh nghiệp</p>
                </div>
                
                <div class="card register-card">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="text-center mb-4">Đăng ký Không gian Doanh nghiệp</h4>
                        
                        <div id="register-alert" class="alert d-none" role="alert"></div>
                        
                        <form id="registerForm">
                            <h6 class="mb-3 text-primary">Thông tin Công ty</h6>
                            <div class="row">
                                <div class="col-md-6 form-floating mb-3">
                                    <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Tên Công ty" required>
                                    <label for="company_name" class="ps-4">Tên Công ty</label>
                                </div>
                                <div class="col-md-6 form-floating mb-3">
                                    <input type="text" class="form-control" id="industry" name="industry" placeholder="Lĩnh vực">
                                    <label for="industry" class="ps-4">Lĩnh vực (Tùy chọn)</label>
                                </div>
                            </div>

                            <h6 class="mb-3 mt-3 text-primary">Thông tin CEO (Quản trị viên)</h6>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="ceo_name" name="ceo_name" placeholder="Họ và Tên CEO" required>
                                <label for="ceo_name">Họ và Tên CEO</label>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-floating mb-3">
                                    <input type="email" class="form-control" id="ceo_email" name="ceo_email" placeholder="Email" required>
                                    <label for="ceo_email" class="ps-4">Email liên hệ</label>
                                </div>
                                <div class="col-md-6 form-floating mb-3">
                                    <input type="text" class="form-control" id="ceo_phone" name="ceo_phone" placeholder="Số điện thoại">
                                    <label for="ceo_phone" class="ps-4">Số điện thoại</label>
                                </div>
                            </div>
                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="ceo_password" name="ceo_password" placeholder="Mật khẩu tài khoản CEO" required>
                                <label for="ceo_password">Mật khẩu mong muốn cho tài khoản</label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-lg" type="submit" id="btn-register">
                                    <span class="spinner-border spinner-border-sm d-none" id="register-spinner" role="status" aria-hidden="true"></span>
                                    <span>Gửi yêu cầu đăng ký</span>
                                </button>
                            </div>
                        </form>

                        <div class="mt-4 text-center">
                            <a href="index.php?action=login" class="text-decoration-none">← Quay lại Đăng nhập</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                
                let $btn = $('#btn-register');
                let $spinner = $('#register-spinner');
                let $alert = $('#register-alert');
                
                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');
                $alert.addClass('d-none').removeClass('alert-success alert-danger');

                $.ajax({
                    url: 'index.php?action=register_company_submit',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            $alert.text(response.message).addClass('alert-success').removeClass('d-none');
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 2000);
                        } else {
                            $alert.text(response.message).addClass('alert-danger').removeClass('d-none');
                            $btn.prop('disabled', false);
                            $spinner.addClass('d-none');
                        }
                    },
                    error: function() {
                        $alert.text('Lỗi kết nối Máy chủ.').addClass('alert-danger').removeClass('d-none');
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                    }
                });
            });
        });
    </script>
</body>
</html>