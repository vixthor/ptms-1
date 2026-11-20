{{-- Admin "reset password" card for another user's edit page. Expects $user. --}}
@if (auth()->user()?->canResetPasswordFor($user))
<div class="mt-4 form-card">
    <h6 class="mb-1"><i class="bi bi-key"></i> Reset password</h6>
    <p class="text-muted small mb-3">Set a new password for {{ $user->first_name }}, or leave both fields blank to generate one. The new login details are emailed to {{ $user->email }}.</p>
    <form class="row g-3" action="{{ route('users.password.update', $user) }}" method="POST" onsubmit="return confirm('Reset this password and email the new login details?');">
        @csrf
        <div class="col-md-6">
            <label for="resetPassword" class="form-label">New password</label>
            <input type="password" class="form-control" id="resetPassword" name="password" minlength="8" autocomplete="new-password">
        </div>
        <div class="col-md-6">
            <label for="resetPasswordConfirmation" class="form-label">Confirm new password</label>
            <input type="password" class="form-control" id="resetPasswordConfirmation" name="password_confirmation" minlength="8" autocomplete="new-password">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-arrow-repeat"></i> Reset password</button>
        </div>
    </form>
</div>
@endif
