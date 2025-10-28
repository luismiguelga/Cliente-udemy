<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use App\Services\AuthenticatedSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        $data = AuthenticatedSessionService::validateLogin($request->all());

        if (! $data) {
            return back()->withErrors([
                'credentials' => 'These credentials do not match our records',
            ]);
        }

        AuthenticatedSessionService::storeAccessToken($data['user'], $data['service']);

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
