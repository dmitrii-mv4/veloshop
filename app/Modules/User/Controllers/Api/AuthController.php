<?php

namespace App\Modules\User\Controllers\Api;

use App\Core\Controllers\Controller;
use App\Modules\User\Models\User;
use App\Modules\User\Services\AuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Контроллер для аутентификации через API
 */
class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    protected AuthService $authService;

    /**
     * Внедрение зависимости через конструктор
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request): JsonResponse
    {
        $feilds = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::create($feilds);

        event(new Registered($user));

        return response()->json([
            'user' => $user,
        ]);

    }

    public function login(Request $request): JsonResponse
    {
        $feilds = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|'
        ]);

        if (!Auth::attempt($feilds)) {
            return response()->json(['errors' => [
                'user' => ['invalid credentials']
            ]], 401);
        }

        $request->session()->regenerate();

        return response()->json(['message' => 'Logged in successfully', 'user' => Auth::user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }
}
