<?php

use Illuminate\Support\Facades\Route;
use App\Modules\MediaLib\Controllers\MediaController;

Route::prefix('media')->controller(MediaController::class)->group(function () {
    Route::get('/', 'index')->name('admin.media');
    Route::post('/store', 'store')->name('admin.media.store');
    Route::post('/create-folder', 'createFolder')->name('admin.media.create-folder');

    // Маршруты для папок
    Route::prefix('folders')->group(function () {
        Route::put('/{id}', 'updateFolder')->name('admin.media.update-folder');
        Route::delete('/{id}', 'destroyFolder')->name('admin.media.destroy-folder');
    });

    // Маршруты для файлов
    Route::get('/file/{id}', 'showFile')->name('admin.media.file-show');
    Route::get('/file/{id}/url', 'getFileUrl')->name('admin.media.file-url');
    Route::delete('/file/{id}', 'destroyFile')->name('admin.media.destroy-file');
});
