<?php

namespace App\Http\Controllers\Inventory\SupplierPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('supplier')->check()) {
            return redirect()->route('supplier.dashboard');
        }

        return view('supplier-portal.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\Inventory\SupplierUser::where('email', $credentials['email'])->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            if (!$user->is_active) {
                return back()->withErrors(['email' => 'Supplier portal account is deactivated.']);
            }
            $remember = $request->filled('remember');
            Auth::guard('supplier')->login($user, $remember);
            $user->update(['last_login_at' => now()]);
            $request->session()->regenerate();
            return redirect()->route('supplier.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid supplier portal credentials provided.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('supplier')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('supplier.login');
    }
}
