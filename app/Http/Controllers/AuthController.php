<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'customer') {
                return redirect()->route('buyer.dashboard');
            }
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle authentication.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->status !== 'active') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is deactivated. Please contact the administrator.',
                ]);
            }

            if (Auth::user()->role === 'customer') {
                return redirect()->route('buyer.dashboard')
                    ->with('success', 'Welcome, ' . Auth::user()->name);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . Auth::user()->name);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }

    /**
     * Show forgot password page.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle sending the password reset email.
     */
    public function handleForgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->with('success', 'If an account exists with this email, a reset link has been dispatched.');
        }

        // Generate token and record in password_reset_tokens table
        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
        $companySetting = CompanySetting::getCached();

        try {
            Mail::send('emails.password-reset', [
                'user' => $user,
                'resetUrl' => $resetUrl,
                'companySetting' => $companySetting,
            ], function ($message) use ($user, $companySetting) {
                $appName = $companySetting?->company_name ?? config('app.name', 'RICAF CRM');
                $message->to($user->email, $user->name)
                        ->subject("Password Reset Request - {$appName}");
            });
        } catch (\Throwable $e) {
            // If local SMTP is not running, log it gracefully
            logger()->warning('Password reset email dispatch error: ' . $e->getMessage());
        }

        return back()->with('success', 'A secure password reset link has been dispatched to your email address.');
    }

    /**
     * Show the reset password view with token.
     */
    public function showResetPassword(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        return view('auth.reset-password', compact('token', 'email'));
    }

    /**
     * Update user password using reset token.
     */
    public function updatePasswordWithToken(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid or has expired.']);
        }

        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Clean up token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been reset successfully. You can now log in.');
    }

    /**
     * Direct self-service password update for logged-in user.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Your account password was updated successfully.');
    }
}
