<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>2FA Setup - CSD Assistant Portal</title>
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
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code img {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background: white;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .status-enabled {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-disabled {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .setup-steps {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .setup-steps h6 {
            color: #495057;
            margin-bottom: 10px;
        }
        .step-number {
            display: inline-block;
            width: 25px;
            height: 25px;
            background: #0652dd;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 25px;
            font-size: 14px;
            margin-right: 10px;
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
                <div class="col-sm-10 col-md-8 col-lg-6">
                    <div class="middle-box">
                        <div class="card-body">
                            <div class="log-header-area card p-4 mb-4 text-center">
                                <h4 class="mb-2 text-logo fw-bold">CSD Assistant</h4>
                                <p class="mb-0 text-logo-sub">Two-Factor Authentication Setup</p>
                            </div>
                            
                            <div class="card">
                                <div class="card-body p-4">
                                    
                                    <!-- Status Badge -->
                                    <div class="text-center mb-3">
                                        <span class="status-badge {{ $user->google2fa_enabled ? 'status-enabled' : 'status-disabled' }}">
                                            2FA is {{ $user->google2fa_enabled ? 'ENABLED' : 'DISABLED' }}
                                        </span>
                                    </div>

                                    <!-- Show Success/Error Messages -->
                                    @if(session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    @if($errors->any())
                                        <div class="alert alert-danger">
                                            @foreach($errors->all() as $error)
                                                <div>{{ $error }}</div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(!$user->google2fa_enabled)
                                        <!-- Enable 2FA Section -->
                                        <div class="setup-steps">
                                            <h5 class="mb-3">Enable Two-Factor Authentication</h5>
                                            
                                            <div class="mb-3">
                                                <span class="step-number">1</span>
                                                <span>Install Google Authenticator app on your phone</span>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <span class="step-number">2</span>
                                                <span>Scan this QR code with the app:</span>
                                            </div>
                                            
                                            <div class="qr-code">
                                                <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code" width="200">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <span class="step-number">3</span>
                                                <span>Enter the 6-digit code from your app:</span>
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('2fa.enable') }}">
                                            @csrf
                                            <div class="form-group mb-3">
                                                <label class="text-muted" for="one_time_password">Authentication Code</label>
                                                <input 
                                                    class="form-control text-center" 
                                                    type="text" 
                                                    name="one_time_password" 
                                                    id="one_time_password"
                                                    placeholder="123456" 
                                                    maxlength="6" 
                                                    required 
                                                    autocomplete="off"
                                                    style="font-size: 18px; letter-spacing: 3px;"
                                                >
                                            </div>
                                            <button class="btn btn-success btn-lg w-100" type="submit">Enable 2FA</button>
                                        </form>
                                    @else
                                        <!-- Disable 2FA Section -->
                                        <div class="setup-steps">
                                            <h5 class="mb-3 text-center">Two-Factor Authentication is Active</h5>
                                            <p class="text-center text-muted">Your account is protected with 2FA. Enter your current authentication code to disable it.</p>
                                        </div>

                                        <form method="POST" action="{{ route('2fa.disable') }}">
                                            @csrf
                                            <div class="form-group mb-3">
                                                <label class="text-muted" for="one_time_password">Authentication Code</label>
                                                <input 
                                                    class="form-control text-center" 
                                                    type="text" 
                                                    name="one_time_password" 
                                                    id="one_time_password"
                                                    placeholder="123456" 
                                                    maxlength="6" 
                                                    required 
                                                    autocomplete="off"
                                                    style="font-size: 18px; letter-spacing: 3px;"
                                                >
                                            </div>
                                            <button class="btn btn-danger btn-lg w-100" type="submit">Disable 2FA</button>
                                        </form>
                                    @endif

                                    <div class="text-center mt-4">
                                        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                                            ← Back to Dashboard
                                        </a>
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
        // Auto-focus on input and format
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('one_time_password');
            if (otpInput) {
                otpInput.focus();
                
                // Only allow numbers
                otpInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                    
                    // Auto submit when 6 digits entered
                    if (e.target.value.length === 6) {
                        setTimeout(() => {
                            e.target.closest('form').submit();
                        }, 300);
                    }
                });
            }
        });
    </script>

</body>
</html>