<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PTMS &middot; Prime Torch Bearers Montessori School</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Lexend:wght@600;700&display=swap" rel="stylesheet">
        <link href="{{ asset('css/bootstrap-icons-1.7.1/font/bootstrap-icons.css') }}" rel="stylesheet">
        <script src="{{ asset("js/jquery-3.6.0.min.js") }}"></script>
    @include('auth.partials.styles')
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
                <img class="logo" src="{{asset('sikhi/ptms-crest.png')}}" alt="Prime Torchbearers Montessori School logo">
                <h2>Sign in</h2>
                <p class="subtitle">Enter your credentials to access your dashboard.</p>

                @if (session('status'))
                    <div class="alert-banner is-success"><i class="bi bi-check-circle-fill"></i> <span>{{ session('status') }}</span></div>
                @endif
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
                        <a href="{{ route('password.request') }}">Forgot password?</a>
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
