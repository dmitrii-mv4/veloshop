<?php

use Illuminate\Support\Facades\Route;
use App\Admin\Controllers\Dashboard;
use App\Admin\Controllers\LocaleController;

Route::middleware(['web', 'admin'])->group(function ()
{
    Route::get('/', [Dashboard::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/settings', [Dashboard::class, 'settings'])->name('admin.settings');
    Route::patch('/settings/update/{settings}', [Dashboard::class, 'settings_update'])->name('admin.settings.update');
});

// Маршруты локализации
Route::prefix('language')->group(function ()
{
    Route::put('/switch', [\App\Modules\User\Controllers\UsersController::class, 'switchLanguage'])->name('admin.language.switch');
});
