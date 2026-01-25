<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Menu\Controllers\MenuController;
use App\Modules\Menu\Controllers\MenuItemController;
use App\Modules\Menu\Controllers\MenuTypeController;

/*
|--------------------------------------------------------------------------
| Web Routes для модуля Menu
|--------------------------------------------------------------------------
|
| Маршруты управления меню, типами меню и пунктами меню в административной части
| Все маршруты защищены middleware auth
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    // Маршруты для меню
    Route::prefix('menu')->name('admin.menu.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::get('/create', [MenuController::class, 'create'])->name('create');
        Route::post('/', [MenuController::class, 'store'])->name('store');
        Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
        Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
        Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
        
        // Маршруты для типов меню (вложенные)
        Route::prefix('types')->name('types.')->group(function () {
            Route::get('/', [MenuTypeController::class, 'index'])->name('index');
            Route::get('/create', [MenuTypeController::class, 'create'])->name('create');
            Route::post('/', [MenuTypeController::class, 'store'])->name('store');
            Route::get('/{menutype}/edit', [MenuTypeController::class, 'edit'])->name('edit');
            Route::put('/{menutype}', [MenuTypeController::class, 'update'])->name('update');
            Route::delete('/{menutype}', [MenuTypeController::class, 'destroy'])->name('destroy');
        });
        
        // Маршруты для пунктов меню (вложенные в конкретное меню)
        Route::prefix('{menu}/items')->name('items.')->group(function () {
            Route::get('/', [MenuItemController::class, 'index'])->name('index');
            Route::get('/create', [MenuItemController::class, 'create'])->name('create');
            Route::post('/', [MenuItemController::class, 'store'])->name('store');
            Route::get('/{menuitem}/edit', [MenuItemController::class, 'edit'])->name('edit');
            Route::put('/{menuitem}', [MenuItemController::class, 'update'])->name('update');
            Route::delete('/{menuitem}', [MenuItemController::class, 'destroy'])->name('destroy');
        });
    });
});