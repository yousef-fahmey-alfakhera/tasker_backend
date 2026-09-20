<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WebAuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard.index');
        }

        return view('auth.login');
    }

    /**
     * Process web login.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard.index'))
                ->with('success', __('messages.logged_in_successfully'));
        }

        return back()->withErrors([
            'email' => __('messages.invalid_credentials'),
        ])->onlyInput('email');
    }

    /**
     * Quick demo login for testing/evaluation.
     */
    public function quickLogin(Request $request): RedirectResponse
    {
        $role = $request->input('role', 'admin');
        $email = $role === 'admin' ? 'admin@admin.com' : 'user@user.com';

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::first();
        }

        if ($user) {
            Auth::guard('web')->login($user, true);
            $request->session()->regenerate();

            return redirect()->route('dashboard.index')
                ->with('success', 'Logged in as ' . $user->name);
        }

        return back()->withErrors(['email' => 'User not found.']);
    }

    /**
     * Web logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', __('messages.logged_out_successfully'));
    }
}
