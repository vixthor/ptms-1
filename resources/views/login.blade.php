<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PTMS &middot; Prime Torch Bearers Montessori School</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Lexend:wght@600;700&display=swap" rel="stylesheet">
        <link href="{{ asset('css/bootstrap-icons-1.7.1/font/bootstrap-icons.css') }}" rel="stylesheet">
        <script src="{{ asset("js/jquery-3.6.0.min.js") }}"></script>
    <style>
        :root {
            --brand-500: #4747d7;
            --brand-600: #3a3ab8;
            --brand-800: #0d33e0;
            --ink-900: #1a1a24;
            --ink-500: #5c5c68;
            --ink-400: #76767f;
            --ink-200: #e2e2ea;
            --ink-100: #edeef5;
            --page-bg: #f5f6fb;
            --danger-500: #e0374a;
            --danger-100: #fbe1e4;
        }
        * { box-sizing: border-box; }
        body {
            background: var(--page-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--ink-900);
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .auth-shell {
            width: 100%;
            max-width: 980px;
            min-height: 600px;
            display: flex;
            border-radius: 22px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 24px 60px rgba(26, 26, 36, 0.12), 0 4px 12px rgba(26,26,36,0.06);
        }
        .auth-visual {
            position: relative;
            flex: 1 1 52%;
            background: linear-gradient(160deg, rgba(13,20,64,.92), rgba(71,71,215,.85)), url("{{ asset('sikhi/sikhi.avif') }}") center/cover no-repeat;
            color: #fff;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .auth-visual .brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-family: 'Lexend', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .auth-visual .brand .mark {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,.16);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .auth-visual h1 {
            font-family: 'Lexend', sans-serif;
            font-weight: 700;
            font-size: 2.1rem;
            line-height: 1.2;
            margin: 0 0 .85rem;
        }
        .auth-visual p {
            color: rgba(255,255,255,.82);
            font-size: .95rem;
            line-height: 1.6;
            max-width: 30rem;
            margin: 0;
        }
        .auth-visual .avatars {
            display: flex;
            align-items: center;
            gap: .9rem;
        }
        .auth-visual .avatars .stack {
            display: flex;
        }
        .auth-visual .avatars img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,.85);
            object-fit: cover;
            margin-left: -12px;
        }
        .auth-visual .avatars img:first-child { margin-left: 0; }
        .auth-visual .avatars .caption {
            font-size: .8125rem;
            color: rgba(255,255,255,.85);
        }
        .auth-form {
            flex: 1 1 48%;
            padding: 3rem 3.25rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .auth-form .logo {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            object-fit: contain;
        }
        .auth-form h2 {
            font-family: 'Lexend', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0 0 .25rem;
        }
        .auth-form .subtitle {
            color: var(--ink-500);
            font-size: .875rem;
            margin: 0 0 1.75rem;
        }
        .alert-banner {
            display: flex;
            align-items: flex-start;
            gap: .55rem;
            background: var(--danger-100);
            color: var(--danger-500);
            border-radius: 10px;
            padding: .7rem .9rem;
            font-size: .8125rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
        }
        .field { margin-bottom: 1.15rem; }
        .field label {
            display: block;
            font-size: .8125rem;
            font-weight: 600;
            color: var(--ink-900);
            margin-bottom: .4rem;
        }
        .field .control {
            position: relative;
        }
        .field .control i {
            position: absolute;
            left: .9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ink-400);
            font-size: .95rem;
        }
        .field input {
            width: 100%;
            padding: .7rem .9rem .7rem 2.4rem;
            border-radius: 10px;
            border: 1.5px solid var(--ink-200);
            outline: none;
            font-size: .9rem;
            font-family: inherit;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .field input:focus {
            border-color: var(--brand-500);
            box-shadow: 0 0 0 3px rgba(71,71,215,.15);
        }
        .field.has-error input { border-color: var(--danger-500); }
        .field .error-msg {
            color: var(--danger-500);
            font-size: .75rem;
            margin-top: .35rem;
        }
        .row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: .8125rem;
        }
        .row-between .remember {
            display: flex;
            align-items: center;
            gap: .45rem;
            color: var(--ink-500);
        }
        .row-between a {
            color: var(--brand-500);
            text-decoration: none;
            font-weight: 600;
        }
        .row-between a:hover { text-decoration: underline; }
        .submit-btn {
            width: 100%;
            padding: .8rem 1rem;
            border-radius: 10px;
            border: none;
            outline: none;
            color: #fff;
            font-weight: 700;
            font-size: .9375rem;
            background: linear-gradient(135deg, var(--brand-500), var(--brand-800));
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            transition: filter .15s ease, transform .1s ease;
        }
        .submit-btn:hover { filter: brightness(1.06); }
        .submit-btn:active { transform: translateY(1px); }
        .submit-btn .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            display: none;
            animation: rotation .8s linear infinite;
        }
        .submit-btn.is-loading .spinner { display: inline-block; }
        .submit-btn.is-loading .label { opacity: .85; }
        @keyframes rotation { to { transform: rotate(360deg); } }
        .version-tag {
            margin-top: 1.75rem;
            font-size: .75rem;
            color: var(--ink-400);
            text-align: center;
        }
        @media (max-width: 860px) {
            .auth-shell { flex-direction: column; min-height: 0; }
            .auth-visual { padding: 2rem; }
            .auth-form { padding: 2.25rem; }
        }
        @media (max-width: 480px) {
            .auth-visual { display: none; }
        }
    </style>
    </head>
    <body>
        <div class="auth-shell">
            <div class="auth-visual">
                <div class="brand">
                    <span class="mark"><i class="bi bi-mortarboard-fill"></i></span>
                    PTMS
                </div>
                <div>
                    <h1>Welcome back to PTMS</h1>
                    <p>Prime Torch Bearers Montessori School &mdash; manage attendance, exams, grading and academic records for the whole school in one paperless dashboard.</p>
                </div>
                <div class="avatars">
                    <div class="stack">
                        <img src="{{asset('sikhi/sikhi1.jpeg')}}" alt="">
                        <img src="{{asset('sikhi/sikhi2.jpg')}}" alt="">
                    </div>
                    <span class="caption">Excellence at its peak</span>
                </div>
            </div>
            <div class="auth-form">
                <img class="logo" src="{{asset('sikhi/logo.png')}}" alt="PTMS logo">
                <h2>Sign in</h2>
                <p class="subtitle">Enter your credentials to access your dashboard.</p>

                @if (session('error'))
                    <div class="alert-banner"><i class="bi bi-exclamation-circle-fill"></i> <span>{{ session('error') }}</span></div>
                @endif

                <form action="{{ route('login.process')}}" method="post" id="loginForm">
                    @csrf
                    <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                        <label for="email">Email</label>
                        <div class="control">
                            <i class="bi bi-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="you@school.com" value="{{ old('email') }}" autocomplete="username" required>
                        </div>
                        @error('email')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
                        <label for="password">Password</label>
                        <div class="control">
                            <i class="bi bi-lock"></i>
                            <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                        </div>
                        @error('password')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <div class="row-between">
                        <label class="remember">
                            <input type="checkbox" id="myCheckbox" name="remember">
                            Remember me
                        </label>
                        <a href="">Forgot password?</a>
                    </div>
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="spinner"></span>
                        <span class="label">Sign in</span>
                    </button>
                </form>
                <p class="version-tag">Web App Version: 2.1.88</p>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                $('#loginForm').on('submit', function () {
                    $('#submitBtn').addClass('is-loading').prop('disabled', true);
                    $('#submitBtn .label').text('Signing in…');
                });
            });
        </script>
    </body>
</html>
