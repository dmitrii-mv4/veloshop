<?php

use Illuminate\Support\Facades\Route;
use App\Modules\IBlock\Controllers\Api\ApiController;

Route::get('separate', [ApiController::class, 'getSeparate']);  // Раздельные данные
Route::get('id/{id}', [ApiController::class, 'getById']);       // Получение по ID
Route::get('active', [ApiController::class, 'getActive']);      // Только активные
Route::get('search/{query}', [ApiController::class, 'search']); // Поиск
Route::get('author/{authorId}', [ApiController::class, 'getByAuthor']); // По автору
Route::get('stats', [ApiController::class, 'getStats']);        // Статистика