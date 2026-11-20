<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('auth.partials.head', ['pageTitle' => 'Choose a new password'])
    </head>
    <body>
        <div class="auth-shell">
            <div class="auth-visual">
                <div class="brand">
                    <span class="mark"><i class="bi bi-mortarboard-fill"></i></span>
                    PTMS
                </div>
                <div>
                    <h1>Choose a new password</h1>
                    <p>Pick a password of at least 8 characters that you don't use anywhere else.</p>
                </div>
                <div></div>
            </div>
            <div class="auth-form">
                <img class="logo" src="{{ asset('sikhi/ptms-crest.png') }}" alt="Prime Torchbearers Montessori School logo">
                <h2>New password</h2>
                <p class="subtitle">Set a new password for your account.</p>

                <form action="{{ route('password.store') }}" method="post">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                        <label for="email">Email</label>
                        <div class="control">
                            <i class="bi bi-envelope"></i>
                            <input type="email" id="email" name="email" value="{{ old('email', $email) }}" autocomplete="username" required>
                        </div>
                        @error('email')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
                        <label for="password">New password</label>
                        <div class="control">
                            <i class="bi bi-lock"></i>
                            <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required autofocus>
                        </div>
                        @error('password')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Confirm new password</label>
                        <div class="control">
                            <i class="bi bi-lock"></i>
                            <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">
                        <span class="label">Reset password</span>
                    </button>
                </form>
                <a class="back-link" href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Back to sign in</a>
            </div>
        </div>
    </body>
</html>
