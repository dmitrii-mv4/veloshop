<?php

namespace App\Modules\User\Controllers\Api;

use App\Core\Controllers\Controller;
use App\Modules\User\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер для аутентификации через API
 */
class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * Внедрение зависимости через конструктор
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Проверка email и пароля
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCredentials(Request $request)
    {
        Log::info('AuthController: запрос на проверку учётных данных', ['ip' => $request->ip()]);

        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:1',
        ]);

        $result = $this->authService->verifyCredentials(
            $validated['email'],
            $validated['password']
        );

        $response = [
            'success' => $result['success'],
            'message' => $result['message']
        ];

        if ($result['success']) {
            $response['user'] = $result['user'];
        }

        return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
    }
}