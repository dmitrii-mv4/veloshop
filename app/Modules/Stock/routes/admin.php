<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Stock\Controllers\StockController;
use App\Modules\Stock\Controllers\CategoryController;

Route::prefix('stock')->name('admin.stock.')->middleware(['web', 'auth'])->group(function () {
    // Активные акции
    Route::get('/', [StockController::class, 'index'])->name('index');
    Route::get('/create', [StockController::class, 'create'])->name('create');
    Route::post('/', [StockController::class, 'store'])->name('store');
    Route::get('/{stock}/edit', [StockController::class, 'edit'])->name('edit');
    Route::put('/{stock}', [StockController::class, 'update'])->name('update');
    Route::delete('/{stock}', [StockController::class, 'destroy'])->name('destroy');

    // Корзина
    Route::prefix('trash')->name('trash.')->group(function () {
        Route::get('/', [StockController::class, 'trash'])->name('index');
        Route::post('/restore/{id}', [StockController::class, 'restore'])->name('restore');
        Route::delete('/force/{id}', [StockController::class, 'forceDelete'])->name('force');
        Route::post('/empty', [StockController::class, 'emptyTrash'])->name('empty');
    });

    // Категории акций
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });
});