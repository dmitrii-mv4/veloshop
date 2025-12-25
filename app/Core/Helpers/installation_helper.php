<?php
/**
    * Помошник для установки KotiksCMS
*/

if (!function_exists('kotiks_install')) {
    /**
     * Быстрая проверка статуса установки
     */
    function kotiks_install(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable('users') 
                && \App\Modules\User\Models\User::exists();
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('kotiks_admin_exists')) {
    /**
     * Проверка существования администратора
     */
    function kotiks_admin_exists(): bool
    {
        try {
            return \App\Modules\User\Models\User::where('email', 'admin@kotiks.local')->exists();
        } catch (\Exception $e) {
            return false;
        }
    }
}