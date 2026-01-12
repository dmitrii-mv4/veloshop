<?php

use Illuminate\Support\Facades\Route;
use App\Modules\MediaLib\Controllers\MediaLibController;

Route::prefix('medialib')->name('medialib.')->controller(MediaLibController::class)->group(function () {
    // Основные операции
    Route::get('/', 'index')->name('index');
    Route::get('/file/{file}', 'show')->name('show');
    
    // Создание папок и загрузка файлов
    Route::post('/folder/create', 'createFolder')->name('folder.create');
    Route::post('/file/upload', 'uploadFile')->name('file.upload');
    
    // Обновление метаданных
    Route::put('/file/{file}/update', 'update')->name('file.update');
    
    // Переименование
    Route::put('/file/{file}/rename', 'renameFile')->name('file.rename');
    Route::put('/folder/{folder}/rename', 'renameFolder')->name('folder.rename');
    
    // Удаление в корзину
    Route::delete('/file/{file}/delete', 'destroyFile')->name('file.destroy');
    Route::delete('/folder/{folder}/delete', 'destroyFolder')->name('folder.destroy');
    
    // Корзина
    Route::prefix('trash')->name('trash.')->group(function () {
        Route::get('/', 'trash')->name('index');
        Route::post('/file/{id}/restore', 'restoreFile')->name('file.restore');
        Route::post('/folder/{id}/restore', 'restoreFolder')->name('folder.restore');
        Route::delete('/file/{id}/force', 'forceDeleteFile')->name('file.force');
        Route::delete('/folder/{id}/force', 'forceDeleteFolder')->name('folder.force');
        Route::delete('/empty', 'emptyTrash')->name('empty');
    });
});