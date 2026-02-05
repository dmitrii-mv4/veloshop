<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Controllers\Api\TreeController;
use App\Modules\Catalog\Controllers\Api\PricesController;
use App\Modules\Catalog\Controllers\Api\WarehousesController;

Route::get('tree', [TreeController::class, 'getTree']); // Древовидная структура

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