<?php

use Illuminate\Support\Facades\Route;
use App\Admin\Controllers\Dashboard;
use Illuminate\Support\Facades\File;

// Главный маршрут -> редирект на админку
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
})->name('home');

// Маршруты аутентификации из User модуля
$authRoutes = base_path('app/Modules/User/routes/auth.php');

if (File::exists($authRoutes)) {
    Route::middleware('web')->group($authRoutes);
}