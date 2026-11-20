<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PasswordResetByAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserPasswordController extends Controller
{
    /**
     * Set a new password on another user's account and email it to them.
     * A blank password generates a random one.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->canResetPasswordFor($user), 403);

        $validated = $request->validate([
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $plainPassword = filled($validated['password'] ?? null)
            ? $validated['password']
            : Str::password(12, symbols: false);

        $user->forceFill([
            'password' => Hash::make($plainPassword),
            'remember_token' => Str::random(60),
        ])->save();

        try {
            $user->notify(new PasswordResetByAdmin($plainPassword));
        } catch (\Throwable $e) {
            Log::error('Failed to send admin password reset email.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Password was reset, but the email could not be sent. New password: '.$plainPassword);
        }

        return back()->with('status', 'Password reset and new login details emailed to '.$user->email.'.');
    }
}
