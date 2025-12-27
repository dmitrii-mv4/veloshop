<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ModuleGenerator\Controllers\ModuleGeneratorController;

Route::controller(ModuleGeneratorController::class)->group(function () 
{
    Route::get('/', 'index')->name('admin.module_generator.index');
    Route::get('/create', 'create')->name('admin.module_generator.create');
    Route::post('/store', 'store')->name('admin.module_generator.store');
    Route::delete('/delete/{module}', 'delete')->name('admin.module_generator.delete');
});