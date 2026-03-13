<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Articles\Controllers\Api\ApiController;

Route::get('tree', [ApiController::class, 'getTree'])->name('api.articles.tree');
Route::get('categories', [ApiController::class, 'getCategories'])->name('api.articles.categories');