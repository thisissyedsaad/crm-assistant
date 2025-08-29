<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>2FA Verification - CSD Assistant Portal</title>
    <link rel="icon" href="{{ asset('assets/admin/img/core-img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/style.css') }}">
    <style>
        .log-header-area.card.p-4.mb-4.text-center {
            background-color: #0652dd;
        }
        .text-logo {
            color: #fff;
            font-size: 35px;
        }
        .text-logo-sub{
            color: #fff;
        }
        .otp-input {
            font-size: 24px;
            letter-spacing: 8px;
            text-align: center;
            font-weight: bold;
        }
        .security-icon {
            font-size: 48px;
            color: #0652dd;
            margin-bottom: 20px;
        }
        .logout-btn {
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
        }
        .logout-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="login-area">

    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader-book">
            <div class="inner">
                <div class="left"></div>
                <div class="middle"></div>
                <div class="right"></div>
            </div>
            <ul>@for ($i = 0; $i < 10; $i++) <li></li> @endfor</ul>
        </div>
    </div>

    <div class="main-content- h-100vh">
        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center">
                <div class="col-sm-10 col-md-6 col-lg-4">
                    <div class="middle-box">
                        <div class="card-body">
                            <div class="log-header-area card p-4 mb-4 text-center">
                                <h4 class="mb-2 text-logo fw-bold">CSD Assistant</h4>
                                <p class="mb-0 text-logo-sub">Two-Factor Authentication</p>
                            </div>
                            
                            <div class="card">
                                <div class="card-body p-4 text-center">
                                    
                                    <!-- Security Icon -->
                                    <div class="security-icon">
                                        🔐
                                    </div>

                                    <h5 class="mb-3">Enter Authentication Code</h5>
                                    <p class="text-muted mb-4">Open your Google Authenticator app and enter the 6-digit code</p>

                                    <!-- Show Error Messages -->
                                    @if($errors->any())
                                        <div class="alert alert-danger">
                                            @foreach($errors->all() as $error)
                                                <div>{{ $error }}</div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- 2FA Verification Form -->
                                    <form method="POST" action="{{ route('2fa.verify.post') }}">
                                        @csrf

                                        <div class="form-group mb-4">
                                            <input 
                                                class="form-control otp-input" 
                                                type="text" 
                                                name="one_time_password" 
                                                id="one_time_password"
                                                placeholder="000000" 
                                                maxlength="6" 
                                                required 
                                                autocomplete="off"
                                                autofocus
                                            >
                                        </div>

                                        <div class="form-group mb-3">
                                            <button class="btn btn-primary btn-lg w-100" type="submit">Verify</button>
                                        </div>

                                    </form>

                                    <!-- Logout Link -->
                                    <div class="mt-4">
                                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                            @csrf
                                            <a href="#" class="logout-btn" onclick="event.preventDefault(); this.closest('form').submit();">
                                                Sign out from this account
                                            </a>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Files -->
    <script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/default-assets/setting.js') }}"></script>
    <script src="{{ asset('assets/admin/js/default-assets/scrool-bar.js') }}"></script>
    <script src="{{ asset('assets/admin/js/todo-list.js') }}"></script>
    <script src="{{ asset('assets/admin/js/default-assets/active.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('one_time_password');
            
            // Focus on input
            otpInput.focus();
            
            // Only allow numbers and auto-submit when 6 digits
            otpInput.addEventListener('input', function(e) {
                // Remove non-numeric characters
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                
                // Auto-submit when 6 digits entered
                if (e.target.value.length === 6) {
                    setTimeout(() => {
                        e.target.closest('form').submit();
                    }, 500);
                }
            });

            // Prevent paste of non-numeric content
            otpInput.addEventListener('paste', function(e) {
                e.preventDefault();
                let paste = (e.clipboardData || window.clipboardData).getData('text');
                paste = paste.replace(/[^0-9]/g, '').substring(0, 6);
                e.target.value = paste;
                
                if (paste.length === 6) {
                    setTimeout(() => {
                        e.target.closest('form').submit();
                    }, 500);
                }
            });
        });
    </script>

</body>
</html>