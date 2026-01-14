<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ExchangeOneCVeloshop\Controllers\ExchangeController;

Route::prefix('exchange1c')->name('exchange1c.')->group(function () {

    Route::get('/', [ExchangeController::class, 'index'])
        ->name('index');

    // Проверка соединения
    Route::get('/exchange/check', [ExchangeController::class, 'index'])
        ->name('exchange.check');
    
    // Получение товаров (JSON API)
    Route::get('/exchange/products', [ExchangeController::class, 'getProducts'])
        ->name('exchange.products');
    
    // Веб-интерфейс для товаров
    Route::get('/exchange/products/view', [ExchangeController::class, 'showProductsInterface'])
        ->name('exchange.products.view');
    
    // Форма настроек
    Route::get('/exchange/settings', [ExchangeController::class, 'showSettingsForm'])
        ->name('exchange.settings');
});
