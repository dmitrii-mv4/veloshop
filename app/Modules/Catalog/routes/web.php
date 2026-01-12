<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Controllers\CatalogController;
use App\Modules\Catalog\Controllers\GoodsController;

Route::prefix('catalog')->name('catalog.')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('index');

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
});