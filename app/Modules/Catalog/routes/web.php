<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Controllers\CatalogController;
use App\Modules\Catalog\Controllers\GoodsController;
use App\Modules\Catalog\Controllers\SectionController;
use App\Modules\Catalog\Controllers\OrderController;

Route::prefix('catalog')->name('catalog.')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('index');

    // Разделы
    Route::prefix('sections')->name('sections.')->controller(SectionController::class)->group(function () {
        // Основные CRUD операции
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{section}', 'edit')->name('edit');
        Route::put('/update/{section}', 'update')->name('update');
        Route::delete('/delete/{section}', 'destroy')->name('destroy');
        
        // Быстрое изменение статуса
        Route::post('/toggle-status/{section}', 'toggleStatus')->name('toggle-status');
        
        // Управление корзиной
        Route::prefix('trash')->name('trash.')->group(function () {
            Route::get('/', 'trash')->name('index');
            Route::post('/restore/{id}', 'restore')->name('restore');
            Route::delete('/force/{id}', 'forceDelete')->name('force');
            Route::delete('/empty', 'emptyTrash')->name('empty');
        });
    });

    // Товары
    Route::prefix('goods')->name('goods.')->controller(GoodsController::class)->group(function () {
        // Основные CRUD операции
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{good}', 'edit')->name('edit');
        Route::put('/update/{good}', 'update')->name('update');
        Route::delete('/delete/{good}', 'destroy')->name('destroy');
        
        // Управление корзиной
        Route::prefix('trash')->name('trash.')->group(function () {
            Route::get('/', 'trash')->name('index');
            Route::post('/restore/{id}', 'restore')->name('restore');
            Route::delete('/force/{id}', 'forceDelete')->name('force');
            Route::delete('/empty', 'emptyTrash')->name('empty');
        });
    });

    // Заказы
    Route::prefix('orders')->name('orders.')->controller(OrderController::class)->group(function () {
        // Основные CRUD операции
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/edit/{order}', 'edit')->name('edit');
        Route::put('/update/{order}', 'update')->name('update');
        Route::delete('/delete/{order}', 'destroy')->name('destroy');
        
        // Управление корзиной
        Route::prefix('trash')->name('trash.')->group(function () {
            Route::get('/', 'trash')->name('index');
            Route::post('/restore/{id}', 'restore')->name('restore');
            Route::delete('/force/{id}', 'forceDelete')->name('force');
            Route::delete('/empty', 'emptyTrash')->name('empty');
        });
    });
});