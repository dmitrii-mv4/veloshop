<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Controllers\CatalogController;
use App\Modules\Catalog\Controllers\OfferController;

/**
 * Маршруты модуля Catalog
 * 
 * Определяет все маршруты для работы с каталогом товаров.
 */

// Основные маршруты каталога
Route::prefix('catalog')->name('catalog.')->middleware(['web', 'auth'])->group(function () {
    
    // Главная страница каталога - список товаров
    Route::get('/', [CatalogController::class, 'index'])->name('index');
    
    // Маршруты для работы с товарами
    Route::prefix('products')->name('products.')->group(function () {
        // Добавлен маршрут для списка товаров
        Route::get('/', [CatalogController::class, 'index'])->name('index');
        
        Route::get('/create', [CatalogController::class, 'create'])->name('create');
        Route::post('/', [CatalogController::class, 'store'])->name('store');
        Route::get('/{product}', [CatalogController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [CatalogController::class, 'edit'])->name('edit');
        Route::put('/{product}', [CatalogController::class, 'update'])->name('update');
        Route::delete('/{product}', [CatalogController::class, 'destroy'])->name('destroy');
        
        // Маршруты для работы с предложениями товара
        // Используем привязку модели к product_id
        Route::prefix('{product}/offers')->name('offers.')->controller(OfferController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{offer}', 'show')->name('show');
            Route::get('/{offer}/edit', 'edit')->name('edit');
            Route::put('/{offer}', 'update')->name('update');
            Route::delete('/{offer}', 'destroy')->name('destroy');
        });
    });
    
    // Маршруты для работы со складами
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::get('/', [CatalogController::class, 'warehouses'])->name('index');
    });
    
    // Статистика каталога (JSON для AJAX)
    Route::get('/statistics', [CatalogController::class, 'statistics'])->name('statistics');
});