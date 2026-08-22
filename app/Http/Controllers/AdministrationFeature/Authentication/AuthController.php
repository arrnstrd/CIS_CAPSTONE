<?php

namespace App\Http\Controllers\AdministrationFeature\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\Authentication\LoginRequest;
use App\Models\User;
use App\Services\Administration\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->attemptLogin(
            $request->email,
            $request->password,
            $request->ip(),
            $request->userAgent()
        );

        if (!$result['success']) {
            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->with('error', $result['message']);
        }

        $user = $result['user'];

        return $this->redirectBasedOnRole($user);
    }

    protected function redirectBasedOnRole(User $user)
    {
        return match (true) {
            $user->isAdmin() => redirect()->route('admin.dashboard'),
            $user->isTeacher() => redirect()->route('teacher.dashboard'),
            $user->isScannerOperator() => redirect()->route('qr-station.index'),
            default => redirect()->route('login'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
