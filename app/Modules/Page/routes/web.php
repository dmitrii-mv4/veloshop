<?php
/**
 * Маршруты для модуля страниц.
 * Включает все CRUD операции и управление корзиной.
 */
use Illuminate\Support\Facades\Route;
use App\Modules\Page\Controllers\PageController;

Route::prefix('pages')->name('admin.page.')->controller(PageController::class)->group(function () {
    // Основные CRUD операции
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/store', 'store')->name('store');
    Route::get('/edit/{page}', 'edit')->name('edit');
    Route::patch('/update/{page}', 'update')->name('update');
    Route::delete('/delete/{page}', 'destroy')->name('destroy'); // Исправлено
    
    // Управление корзиной
    Route::prefix('trash')->name('trash.')->group(function () {
        Route::get('/', 'trash')->name('index');
        Route::post('/restore/{id}', 'restore')->name('restore');
        Route::delete('/force/{id}', 'forceDelete')->name('force');
        Route::post('/empty', 'emptyTrash')->name('empty');
    });
});