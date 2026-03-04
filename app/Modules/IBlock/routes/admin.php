<?php
/**
 * Маршруты для модуля информационных блоков.
 * Включает все CRUD операции и управление корзиной.
 */
use Illuminate\Support\Facades\Route;
use App\Modules\IBlock\Controllers\IBlockController;

Route::prefix('iblocks')->name('admin.iblock.')->controller(IBlockController::class)->group(function () {
    // Основные CRUD операции
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/edit/{iblock}', 'edit')->name('edit');
    Route::put('/update/{iblock}', 'update')->name('update');
    Route::delete('/delete/{iblock}', 'destroy')->name('destroy');
    
    // Управление корзиной
    Route::prefix('trash')->name('trash.')->group(function () {
        Route::get('/', 'trash')->name('index');
        Route::post('/restore/{id}', 'restore')->name('restore');
        Route::delete('/force/{id}', 'forceDelete')->name('force');
        Route::post('/empty', 'emptyTrash')->name('empty');
    });
});