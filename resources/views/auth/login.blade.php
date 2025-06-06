<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laxom- Bootstrap Admin Template</title>
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
                <div class="col-sm-10 col-md-7 col-lg-5">
                    <div class="middle-box">
                        <div class="card-body">
                            <!-- <div class="log-header-area card p-4 mb-4 text-center">
                                <h5>Welcome To CRM Assistant</h5>
                                <p class="mb-0">Sign in to continue.</p>
                            </div> -->
                            <div class="log-header-area card p-4 mb-4 text-center">
                                <h4 class="mb-2 text-logo fw-bold">CSDassistant</h4>
                                <p class="mb-0 text-logo-sub">Your Gateway to Better Management</p>
                            </div>
                            <div class="card">
                                <div class="card-body p-4">
                                    
                                    <!-- Show Validation Errors -->
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Show Session Messages -->
                                    @if (session('status'))
                                        <div class="alert alert-success">
                                            {{ session('status') }}
                                        </div>
                                    @endif

                                    <!-- Login Form -->
                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <div class="form-group mb-3">
                                            <label class="text-muted" for="email">Email address</label>
                                            <input class="form-control" type="email" name="email" id="email"
                                                placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="text-muted" for="password">Password</label>
                                            <input class="form-control" type="password" name="password" id="password"
                                                placeholder="Enter your password" required>
                                        </div>

                                        <div class="form-group mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" name="remember" id="remember">
                                            <label class="form-check-label" for="remember">Remember Me</label>
                                        </div>

                                        <div class="form-group mb-3">
                                            <button class="btn btn-primary btn-lg w-100" type="submit">Login</button>
                                        </div>

                                        <!-- <div class="text-center">
                                            @if (Route::has('password.request'))
                                                <a class="fw-bold" href="{{ route('password.request') }}">Forgot Password?</a>
                                            @endif
                                        </div>
                                         -->
                                    </form>
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

</body>
</html>
