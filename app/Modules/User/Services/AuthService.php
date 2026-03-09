<?php

namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с аутентификацией пользователей
 * Содержит бизнес-логику проверки учётных данных
 */
class AuthService
{
    /**
     * Проверяет соответствие email и пароля учётной записи пользователя
     *
     * @param string $email
     * @param string $password
     * @return array ['success' => bool, 'message' => string]
     */
    public function verifyCredentials(string $email, string $password): array
    {
        Log::info('AuthService: попытка проверки учётных данных', ['email' => $email]);

        $user = User::where('email', $email)->first();

        if (!$user) {
            Log::warning('AuthService: пользователь не найден', ['email' => $email]);
            return [
                'success' => false,
                'message' => 'Пользователь с таким email не зарегистрирован'
            ];
        }

        if (!$user->is_active) {
            Log::info('AuthService: пользователь деактивирован', ['email' => $email, 'user_id' => $user->id]);
            return [
                'success' => false,
                'message' => 'Учётная запись пользователя деактивирована'
            ];
        }

        if (!Hash::check($password, $user->password)) {
            Log::warning('AuthService: неверный пароль', ['email' => $email, 'user_id' => $user->id]);
            return [
                'success' => false,
                'message' => 'Неверный пароль'
            ];
        }

        // Успешная проверка - обновляем время последнего входа
        $user->last_login_at = now();
        $user->save();

        // Загружаем связанные данные, которые могут понадобиться на фронте
        $user->load('role');

        Log::info('AuthService: учётные данные подтверждены', ['email' => $email, 'user_id' => $user->id]);

        return [
            'success' => true,
            'message' => 'Учётные данные верны',
            'user' => $user->toArray()
        ];
    }
}