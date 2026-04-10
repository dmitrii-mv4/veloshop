<?php

namespace App\Modules\User\Controllers\Api;

use App\Core\Controllers\Controller;
use App\Modules\User\Services\AuthService;

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


}
