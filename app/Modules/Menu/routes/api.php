<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Menu\Controllers\Api\ApiController;

Route::get('separate', [ApiController::class, 'getSeparate']);  // Раздельные данные
Route::get('tree', [ApiController::class, 'getTree']);          // Древовидная структура