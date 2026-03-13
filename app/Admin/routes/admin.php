<?php

use Illuminate\Support\Facades\Route;
use App\Admin\Controllers\Dashboard;
use App\Admin\Controllers\LocaleController;
use App\Modules\User\Controllers\UsersController;
use App\Admin\Controllers\ModulesController;

Route::get('/', [Dashboard::class, 'dashboard'])->name('admin.dashboard');
Route::get('/settings', [Dashboard::class, 'settings'])->name('admin.settings');
Route::patch('/settings/update/{settings}', [Dashboard::class, 'settings_update'])->name('admin.settings.update');


// Маршруты локализации
Route::prefix('language')->group(function ()
{
    Route::put('/switch', [UsersController::class, 'switchLanguage'])->name('admin.language.switch');
});

// Маршруты управления модулями
Route::prefix('modules')->group(function () {
    Route::get('/', [ModulesController::class, 'index'])->name('admin.modules.index');
    Route::post('/{module}/toggle', [ModulesController::class, 'toggle'])->name('admin.modules.toggle');
});