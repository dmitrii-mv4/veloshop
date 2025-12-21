<?php

use Illuminate\Support\Facades\Route;
use App\Admin\Controllers\Dashboard;

Route::middleware(['web', 'admin'])->group(function ()
{
    Route::get('/', [Dashboard::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/settings', [Dashboard::class, 'settings'])->name('admin.settings');
    Route::patch('/settings/update/{settings}', [Dashboard::class, 'settings_update'])->name('admin.settings.update');
});