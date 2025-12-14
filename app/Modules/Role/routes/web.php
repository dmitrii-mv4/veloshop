<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Role\Controllers\RolesController;

Route::prefix('/roles')->controller(RolesController::class)->group(function () 
{
    Route::get('/', 'index')->middleware(['roles_index'])->name('admin.roles');
    Route::get('/create', 'create')->middleware(['roles_create'])->name('admin.roles.create');
    Route::post('/store', 'store')->middleware(['roles_create'])->name('admin.roles.store');
    Route::get('/edit/{role}', 'edit')->middleware(['roles_update'])->name('admin.roles.edit');
    Route::patch('/edit/{role}', 'update')->middleware(['roles_update'])->name('admin.roles.update');
    Route::delete('/delete/{role}', 'delete')->middleware(['roles_delete'])->name('admin.roles.delete');
});