<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Page\Controllers\PageController;

Route::prefix('/pages')->controller(PageController::class)->group(function ()
{
    Route::get('/', 'index')->name('admin.page.index');
    Route::get('/create', 'create')->name('admin.page.create');
    Route::post('/store', 'store')->name('admin.page.store');
    Route::get('/edit/{page}', 'edit')->name('admin.page.edit');
    Route::patch('/update/{page}', 'update')->name('admin.page.update');
    Route::delete('/delete/{page}', 'delete')->name('admin.page.delete');
});