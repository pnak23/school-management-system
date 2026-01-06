<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    use LogsActivity;
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.custom_log_in');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Get authenticated user
        $user = Auth::user();

        // Log login activity
        $this->logActivity(
            "User logged in: {$user->name} ({$user->email})",
            $user,
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_in_at' => now()->toDateTimeString()
            ],
            'authentication'
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Get user info before logout (Auth::user() won't be available after logout)
        $user = Auth::user();
        $userId = $user ? $user->id : null;
        $userName = $user ? $user->name : 'Unknown';
        $userEmail = $user ? $user->email : 'Unknown';

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Log logout activity (after logout, so we can't use $user as subject)
        if ($userId) {
            $this->logActivity(
                "User logged out: {$userName} ({$userEmail})",
                null, // No subject since user is logged out
                [
                    'user_id' => $userId,
                    'email' => $userEmail,
                    'name' => $userName,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'logged_out_at' => now()->toDateTimeString()
                ],
                'authentication'
            );
        }

        return redirect('/');
    }
}
