<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Modules\ModuleGenerator\Models\Module;

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
    require app_path('Modules/ModuleGenerator/routes/web.php');
    require app_path('Modules/Integrator/routes/web.php');

    // Динамические модули
    if (Schema::hasTable('modules'))
    {
        $allModuleData = Module::get();

        if ($allModuleData->isNotEmpty()) {
            foreach ($allModuleData as $module)
            {
                $studlyName = Str::studly($module['code_module']);
                $routePath = base_path("Modules/{$studlyName}/routes/web.php");

                if (file_exists($routePath)) {
                    require $routePath;
                }
            }
        }
    }

    Route::get('/settings', [App\Admin\Controllers\Dashboard::class, 'settings'])->name('admin.settings');
    Route::patch('/settings/update/{settings}', [App\Admin\Controllers\Dashboard::class, 'settings_update'])->name('admin.settings.update');
});