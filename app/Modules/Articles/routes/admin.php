<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Articles\Controllers\ArticlesController;
use App\Modules\Articles\Controllers\CategoryController;

Route::prefix('articles')->name('admin.articles.')->middleware(['web', 'auth'])->group(function () {
    // Активные статьи
    Route::get('/', [ArticlesController::class, 'index'])->name('index');
    Route::get('/create', [ArticlesController::class, 'create'])->name('create');
    Route::post('/', [ArticlesController::class, 'store'])->name('store');
    Route::get('/{articles}/edit', [ArticlesController::class, 'edit'])->name('edit');
    Route::put('/{articles}', [ArticlesController::class, 'update'])->name('update');
    Route::delete('/{articles}', [ArticlesController::class, 'destroy'])->name('destroy');

    // Корзина
    Route::prefix('trash')->name('trash.')->group(function () {
        Route::get('/', [ArticlesController::class, 'trash'])->name('index');
        Route::post('/restore/{id}', [ArticlesController::class, 'restore'])->name('restore');
        Route::delete('/force/{id}', [ArticlesController::class, 'forceDelete'])->name('force');
        Route::post('/empty', [ArticlesController::class, 'emptyTrash'])->name('empty');
    });

    // Категории статей
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });
});