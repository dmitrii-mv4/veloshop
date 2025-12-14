<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Controllers\UsersController;

Route::prefix('/users')->controller(UsersController::class)->group(function () 
{
    Route::get('/', 'index')->middleware(['users_index'])->name('admin.users');
    Route::get('/create', 'create')->middleware(['users_create'])->name('admin.users.create');
    Route::post('/store', 'store')->middleware(['users_create'])->name('admin.users.store');
    Route::get('/edit/{user}', 'edit')->middleware(['users_update'])->name('admin.users.edit');
    Route::patch('/edit/{user}', 'update')->middleware(['users_update'])->name('admin.users.update');
    Route::delete('/delete/{user}', 'delete')->middleware(['users_delete'])->name('admin.users.delete');
});