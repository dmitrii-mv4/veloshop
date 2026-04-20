<?php

use App\Modules\Catalog\Controllers\Api\BasketController;
use App\Modules\Catalog\Controllers\Api\CatalogAttributeApiController;
use App\Modules\Catalog\Controllers\Api\CatalogCategoryApiController;
use App\Modules\Catalog\Controllers\Api\CatalogTypePriceApiController;
use App\Modules\Catalog\Controllers\Api\CustomersController;
use App\Modules\Catalog\Controllers\Api\PricesController;
use App\Modules\Catalog\Controllers\Api\ProductApiController;
use App\Modules\Catalog\Controllers\Api\TagsApiController;
use App\Modules\Catalog\Controllers\Api\WarehousesController;
use Illuminate\Support\Facades\Route;

Route::prefix('prices')->group(function () {
    // Основной метод - получение всех типов цен
    Route::get('/', [PricesController::class, 'getPrices']);

    // Получение только активных типов цен
    Route::get('/active', [PricesController::class, 'getActivePrices']);

    // Получение типа цены по техническому идентификатору
    Route::get('/type/{type}', [PricesController::class, 'getPriceByType']);

    // Получение основного типа цены
    Route::get('/main', [PricesController::class, 'getMainPriceType']);

    // Получение типов цен сгруппированных по валюте
    Route::get('/grouped-by-currency', [PricesController::class, 'getPricesGroupedByCurrency']);

    // Получение статистики по типам цен
    Route::get('/stats', [PricesController::class, 'getPricesStats']);
});

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

Route::apiResource('categories', CatalogCategoryApiController::class);

Route::apiResource('products', ProductApiController::class);

Route::apiResource('attributes', CatalogAttributeApiController::class);

Route::apiResource('pricetypes', CatalogTypePriceApiController::class);

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
