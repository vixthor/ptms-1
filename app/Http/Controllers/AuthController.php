<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->back();
        }

        return view('login');
    }

    public function process(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);
            if ($validate->passes()) {
                if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                    if (session('redirect_url')) {
                        return redirect(session('redirect_url'));
                    }

                    return redirect()->route('dashboard');
                }

                return redirect()->back()->with('error', ' Either Email or Password is wrong');
            }

            return redirect()->back()->withErrors($validate);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updatePasword()
    {
        return view('auth.passwords.edit');
    }

    public function processUpdatePasword(Request $request): RedirectResponse
    {
        $request->validate([
            'old_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:old_password'],
        ], [
            'old_password.current_password' => 'Your current password is incorrect.',
            'new_password.different' => 'Your new password must be different from your current password.',
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($request->new_password),
        ])->save();

        return redirect()->back()->with('status', 'Password changed successfully.');
    }

    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();

            return redirect()->route('login');
        }
    }
}
