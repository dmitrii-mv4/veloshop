<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Integrator\Controllers\IntegratorController;

Route::prefix('/integration')->controller(IntegratorController::class)->group(function () 
{
    Route::get('/', 'index')->name('admin.integration.index');
    //Route::get('/create', 'create')->name('admin.integration.create');
    //Route::post('/store', 'store')->name('admin.integration.store');
    //Route::get('/edit/{id}', 'edit')->name('admin.integration.edit');
    //Route::patch('/edit/{id}', 'update')name('admin.integration.update');
    //Route::delete('/delete/{id}', 'delete')->name('admin.integration.delete');
});