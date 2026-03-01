<?php

use Illuminate\Support\Facades\Route;
use App\Modules\News\Controllers\NewsController;
use App\Modules\News\Controllers\CategoryController;

Route::prefix('news')->name('admin.news.')->middleware(['web', 'auth'])->group(function () {
    // Активные новости
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/create', [NewsController::class, 'create'])->name('create');
    Route::post('/', [NewsController::class, 'store'])->name('store');
    Route::get('/{news}/edit', [NewsController::class, 'edit'])->name('edit');
    Route::put('/{news}', [NewsController::class, 'update'])->name('update');
    Route::delete('/{news}', [NewsController::class, 'destroy'])->name('destroy');

    // Корзина
    Route::prefix('trash')->name('trash.')->group(function () {
        Route::get('/', [NewsController::class, 'trash'])->name('index');
        Route::post('/restore/{id}', [NewsController::class, 'restore'])->name('restore');
        Route::delete('/force/{id}', [NewsController::class, 'forceDelete'])->name('force');
        Route::post('/empty', [NewsController::class, 'emptyTrash'])->name('empty');
    });

    // Категории новостей
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });
});