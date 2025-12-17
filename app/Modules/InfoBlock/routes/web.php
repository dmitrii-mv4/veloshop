<?php

use Illuminate\Support\Facades\Route;
use App\Modules\InfoBlock\Controllers\InfoBlockController;

Route::prefix('info_block')->controller(InfoBlockController::class)->group(function ()
{
    Route::get('/', 'index')->name('admin.info_block.index');
    Route::get('/create', 'create')->name('admin.info_block.create');
    Route::post('/store', 'store')->name('admin.info_block.store');
    Route::get('/edit/{item}', 'edit')->name('admin.info_block.edit');
    Route::patch('/edit/{item}', 'update')->name('admin.info_block.update');
    Route::delete('/delete/{item}', 'delete')->name('admin.info_block.delete');
});