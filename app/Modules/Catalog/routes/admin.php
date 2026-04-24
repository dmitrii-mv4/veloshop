<?php

use App\Modules\Catalog\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Controllers\CatalogController;
use App\Modules\Catalog\Controllers\OfferController;
use App\Modules\Catalog\Controllers\WarehouseController;
use App\Modules\Catalog\Controllers\TagController;
use App\Modules\Catalog\Controllers\CustomerController;
use App\Modules\Catalog\Controllers\CustomerTypeController;
use App\Modules\Catalog\Controllers\BasketController;

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

        // DELETE и PUT routes MUST come before GET /{product} to avoid conflicts
        Route::delete('/{product}', [CatalogController::class, 'destroy'])->name('destroy');
        Route::put('/{product}', [CatalogController::class, 'update'])->name('update');

        Route::get('/{product}', [CatalogController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [CatalogController::class, 'edit'])->name('edit');

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

    // Маршруты для управления складами
    Route::prefix('warehouses')->name('warehouses.')->controller(WarehouseController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{warehouse}/edit', 'edit')->name('edit');
        Route::put('/{warehouse}', 'update')->name('update');
        Route::delete('/{warehouse}', 'destroy')->name('destroy');
        Route::patch('/{warehouse}/toggle-status', 'toggleStatus')->name('toggle-status');
    });

    // Маршруты для управления категориями
    Route::resource('categories', CategoryController::class);

    // Маршруты для управления тегами
    Route::prefix('tags')->name('tags.')->controller(TagController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{tag}/edit', 'edit')->name('edit');
        Route::put('/{tag}', 'update')->name('update');
        Route::delete('/{tag}', 'destroy')->name('destroy');
    });

    // Статистика каталога (JSON для AJAX)
    Route::get('/statistics', [CatalogController::class, 'statistics'])->name('statistics');

    // Покупатели
    Route::prefix('customers')->name('customers.')->controller(CustomerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/trash', 'trash')->name('trash');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::patch('/{id}/restore', 'restore')->name('restore');
        Route::delete('/{id}/force', 'forceDelete')->name('force-delete');
        Route::delete('/force-all', 'forceDeleteAll')->name('force-delete-all');
    });

    // Корзины
    Route::prefix('basket')->name('basket.')->controller(BasketController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
});
