<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Cookie;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        if (Auth::check()) {
            dd(111);
            return redirect('/');
        }
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        // Удаляем все токены пользователя при выходе
        Auth::user()?->tokens()->delete();
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Удаляем куки сессии и XSRF-TOKEN
        $sessionCookie = Cookie::forget(config('session.cookie'));
        $xsrfCookie = Cookie::forget('XSRF-TOKEN');
        $rememberCookie = Cookie::forget(Auth::getRecallerName());

        return response()
            ->json(['message' => 'Выход выполнен успешно'])
            ->withCookie($sessionCookie)
            ->withCookie($xsrfCookie)
            ->withCookie($rememberCookie);
    }
}
