<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Page\Controllers\Api\ApiController;

Route::get('separate', [ApiController::class, 'getSeparate']);  // Раздельные данные
Route::get('tree', [ApiController::class, 'getTree']);          // Древовидная структура
Route::get('published-tree', [ApiController::class, 'getPublishedTree']); // Опубликованные страницы в виде дерева