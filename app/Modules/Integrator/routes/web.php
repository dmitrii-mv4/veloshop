<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Integrator\Controllers\IntegratorController;

Route::prefix('admin/integration')->name('admin.integration.')->middleware(['web', 'auth', 'admin'])->group(function () {
    Route::get('/', [IntegratorController::class, 'index'])->name('index');
    Route::get('/create', [IntegratorController::class, 'create'])->name('create');
    Route::post('/store', [IntegratorController::class, 'store'])->name('store');
    
    // AJAX маршрут для получения полей модуля
    Route::get('/module-fields/{moduleName}', [IntegratorController::class, 'getModuleFields'])
        ->name('module-fields')
        ->where('moduleName', '[a-zA-Z0-9_]+');
});