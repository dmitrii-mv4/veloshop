<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Controllers\Api\ApiController;

Route::get('separate', [ApiController::class, 'getSeparate']);  // Раздельные данные
Route::get('id/{id}', [ApiController::class, 'getById']);       // Получение по ID
Route::get('active', [ApiController::class, 'getActive']);      // Только активные
Route::get('role/{roleId}', [ApiController::class, 'getByRole']); // По роли
Route::get('search/{query}', [ApiController::class, 'search']); // Поиск
Route::get('stats', [ApiController::class, 'getStats']);        // Статистика