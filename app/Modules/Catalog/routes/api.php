<?php

use App\Modules\Catalog\Controllers\Api\BasketController;
use App\Modules\Catalog\Controllers\Api\AttributeApiController;
use App\Modules\Catalog\Controllers\Api\CategoryApiController;
use App\Modules\Catalog\Controllers\Api\PriceTypeController;
use App\Modules\Catalog\Controllers\Api\CustomersController;
use App\Modules\Catalog\Controllers\Api\ProductApiController;
use App\Modules\Catalog\Controllers\Api\TagsApiController;
use App\Modules\Catalog\Controllers\Api\WarehousesController;
use Illuminate\Support\Facades\Route;

Route::prefix('warehouses')->group(function () {
    // Основной метод - получение всех складов
    Route::get('/', [WarehousesController::class, 'getWarehouses']);

    // Получение только активных складов
    Route::get('/active', [WarehousesController::class, 'getActiveWarehouses']);

    // Получение склада по ID
    Route::get('/{id}', [WarehousesController::class, 'getWarehouseById']);

    // Получение статистики по складам
    Route::get('/stats', [WarehousesController::class, 'getWarehousesStats']);

    // Получение складов сгруппированных по активности
    Route::get('/grouped-by-activity', [WarehousesController::class, 'getWarehousesGroupedByActivity']);

    // Получение складов с фильтрацией по наличию товаров
    Route::get('/filter/by-stock/{filter}', [WarehousesController::class, 'getWarehousesByStock']);
});

Route::apiResource('categories', CategoryApiController::class, ['only' => ['index']]);

Route::apiResource('products', ProductApiController::class, ['only' => ['index']]);

Route::apiResource('attributes', AttributeApiController::class, ['only' => ['index']]);

Route::apiResource('pricetypes', PriceTypeController::class, ['only' => ['index']]);

/**
 * Маршруты для тегов
 */
Route::prefix('tags')->controller(TagsApiController::class)->group(function () {
    // Получение списка всех тегов
    Route::get('/', 'index')->name('tags.index');

    // Получение списка тегов по их ID
    Route::get('/list', 'listByIds')->name('tags.list');

    // Получение конкретного тега по ID
    Route::get('/{id}', 'show')->name('tags.show');
});

/**
 * Маршруты для покупателей
 */
Route::prefix('customers')->name('customers.')->controller(CustomersController::class)->group(function () {
    // Покупатели
    Route::get('/', 'index')->name('index');
    Route::get('/{id}', 'show')->name('show');
    Route::post('/', 'store')->name('store');
    Route::put('/{id}', 'update')->name('update');
    Route::delete('/{id}', 'destroy')->name('destroy');
    Route::patch('/{id}/restore', 'restore')->name('restore');
    Route::delete('/{id}/force', 'forceDelete')->name('force-delete');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('basket')->group(function () {
        Route::get('/', [BasketController::class, 'getBasket']);
        Route::post('/add', [BasketController::class, 'addToBasket']);
        Route::get('/clear', [BasketController::class, 'clearBasket']);
    });
});
