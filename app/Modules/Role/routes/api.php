<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Role\Controllers\Api\ApiController;

Route::get('separate', [ApiController::class, 'getSeparate']);             // Раздельные данные
Route::get('id/{id}', [ApiController::class, 'getById']);                  // Получение по ID
Route::get('system', [ApiController::class, 'getSystemRoles']);            // Системные роли
Route::get('user', [ApiController::class, 'getUserRoles']);                // Пользовательские роли
Route::get('module/{module}', [ApiController::class, 'getPermissionsByModule']); // Разрешения по модулю
Route::get('stats', [ApiController::class, 'getStats']);                   // Статистика
Route::get('search/{query}', [ApiController::class, 'search']);            // Поиск ролей