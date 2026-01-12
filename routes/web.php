<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Core\Services\Router\RouterLoaderService;

/**
 * Загрузка web-маршрутов системы через RouterLoaderService
 */

Log::info('Загрузка web-маршрутов: начало');

try {
    $routerLoader = app(RouterLoaderService::class);
    $routerLoader->loadAllRoutes();
    
    $stats = $routerLoader->getRoutesStats();
    
    Log::info('RouterLoaderService успешно выполнен', [
        'total_routes' => $stats['total_routes'],
        'admin_routes' => $stats['admin_routes'],
        'module_routes' => $stats['module_routes']
    ]);

} catch (\Exception $e) {
    Log::error('Ошибка загрузки маршрутов через RouterLoaderService', [
        'message' => $e->getMessage(),
        'exception' => $e
    ]);
    
    // Резервные базовые маршруты на случай ошибки
    Route::get('/', function () {
        return response()->json([
            'status' => 'error',
            'message' => 'Router service not loaded properly',
            'timestamp' => now()->toISOString(),
            'support' => 'Check application logs'
        ], 500);
    })->name('fallback.error');
}

// Общий health check маршрут (всегда доступен)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'web',
        'timestamp' => now()->toISOString()
    ]);
})->name('health.check');

Log::info('Загрузка web-маршрутов: завершено');