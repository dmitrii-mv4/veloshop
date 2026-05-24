<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Modules\User\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register' , [AuthController::class, 'register']);
Route::post('/login' , [AuthController::class, 'login']);
Route::post('/logout' , [AuthController::class, 'logout']);
