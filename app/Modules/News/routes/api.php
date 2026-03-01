<?php

use Illuminate\Support\Facades\Route;
use App\Modules\News\Controllers\Api\ApiController;

Route::get('tree', [ApiController::class, 'getTree'])->name('api.news.tree');
Route::get('categories', [ApiController::class, 'getCategories'])->name('api.news.categories');