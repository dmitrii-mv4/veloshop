<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

require app_path('Modules/User/routes/auth.php');

Route::middleware(['admin', 'locale'])->group(function () 
{
    Route::get('/', [App\Admin\Controllers\Dashboard::class, 'dashboard'])->name('admin.dashboard');

    // Статические системные модули (всегда загружаются)
    require app_path('Modules/Page/routes/web.php');
    require app_path('Modules/MediaLib/routes/web.php');
    require app_path('Modules/User/routes/web.php');
    require app_path('Modules/Role/routes/web.php');
    require app_path('Modules/InfoBlock/routes/web.php');
    
    Route::get('/settings', [App\Admin\Controllers\Dashboard::class, 'settings'])->name('admin.settings');
    Route::patch('/settings/update/{settings}', [App\Admin\Controllers\Dashboard::class, 'settings_update'])->name('admin.settings.update');
});