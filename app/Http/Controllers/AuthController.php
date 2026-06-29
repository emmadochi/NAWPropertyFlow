<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Mock forgot password submission.
     */
    public function handleForgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        return back()->with('success', 'A password reset link has been sent to your email address (Simulated).');
    }
}
