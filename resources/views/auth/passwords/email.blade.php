<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('auth.partials.head', ['pageTitle' => 'Forgot password'])
    </head>
    <body>
        <div class="auth-shell">
            <div class="auth-visual">
                <div class="brand">
                    <span class="mark"><i class="bi bi-mortarboard-fill"></i></span>
                    PTMS
                </div>
                <div>
                    <h1>Forgot your password?</h1>
                    <p>No problem. Enter the email address on your account and we'll send you a link to choose a new password.</p>
                </div>
                <div></div>
            </div>
            <div class="auth-form">
                <img class="logo" src="{{ asset('sikhi/ptms-crest.png') }}" alt="Prime Torchbearers Montessori School logo">
                <h2>Reset password</h2>
                <p class="subtitle">We'll email you a secure reset link.</p>

                @if (session('status'))
                    <div class="alert-banner is-success"><i class="bi bi-check-circle-fill"></i> <span>{{ session('status') }}</span></div>
                @endif

                <form action="{{ route('password.email') }}" method="post">
                    @csrf
                    <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                        <label for="email">Email</label>
                        <div class="control">
                            <i class="bi bi-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="you@school.com" value="{{ old('email') }}" autocomplete="username" required autofocus>
                        </div>
                        @error('email')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="submit-btn">
                        <span class="label">Send reset link</span>
                    </button>
                </form>
                <a class="back-link" href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Back to sign in</a>
            </div>
        </div>
    </body>
</html>
