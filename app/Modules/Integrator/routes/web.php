<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Integrator\Controllers\IntegratorController;
use App\Modules\Integrator\Controllers\ConnectionTestingController;

Route::prefix('integration')->name('admin.integration.')->middleware(['web', 'auth', 'admin'])->group(function () {
    Route::get('/', [IntegratorController::class, 'index'])->name('index');
    Route::get('/create', [IntegratorController::class, 'create'])->name('create');
    Route::post('/store', [IntegratorController::class, 'store'])->name('store');
    
    // AJAX маршрут для получения полей модуля
    Route::get('/module-fields/{moduleName}', [IntegratorController::class, 'getModuleFields'])
        ->name('module-fields')
        ->where('moduleName', '[a-zA-Z0-9_]+');

    // Тестирование соединения к внешним сервисам
    Route::prefix('testing')->name('testing.')->group(function () {
        Route::get('/', [ConnectionTestingController::class, 'index'])->name('index');
        
        // POST маршрут для проверки соединения
        Route::post('/test-connection', [ConnectionTestingController::class, 'testConnection'])
            ->name('test-connection');
    });
});