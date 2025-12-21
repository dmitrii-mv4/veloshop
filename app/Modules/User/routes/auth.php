<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Controllers\Auth\LoginController;
use App\Modules\User\Controllers\Auth\RegisterController;
use App\Modules\User\Controllers\Auth\ForgotPasswordController;
use App\Modules\User\Controllers\Auth\ResetPasswordController;
use App\Modules\User\Controllers\Auth\ConfirmPasswordController;
use App\Modules\User\Controllers\Auth\VerificationController;

// для работы сессии, CSRF-токенов и переменной $errors
Route::middleware('web')->group(function () {
    
    // Маршруты для НЕавторизованных пользователей (гостей)
    Route::middleware('guest')->group(function () {
        // Логин
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
        
        // Регистрация (если используется)
        Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [RegisterController::class, 'register']);
        
        // Сброс пароля
        Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
            ->name('password.request');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
            ->name('password.email');
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
            ->name('password.reset');
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])
            ->name('password.update');
    });
    
    // Маршруты для АВТОРИЗОВАННЫХ пользователей
    Route::middleware('auth')->group(function () {
        // Выход (ОСТАВЛЯЕМ ТОЛЬКО ОДИН МАРШРУТ ДЛЯ ВЫХОДА)
        // Используем стандартный logout из LoginController
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
        
        // Подтверждение пароля
        Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])
            ->name('password.confirm');
        Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);
        
        // Верификация email
        Route::get('email/verify', [VerificationController::class, 'show'])
            ->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        Route::post('email/resend', [VerificationController::class, 'resend'])
            ->middleware('throttle:6,1')
            ->name('verification.resend');
    });
});