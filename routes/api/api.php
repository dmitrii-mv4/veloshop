<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Core\Services\Router\ApiRouterLoaderService;

/**
 * Загрузка API-маршрутов системы через ApiRouterLoaderService
 */

Log::info('Загрузка API маршрутов: начало');

try {
    $apiRouterLoader = app(ApiRouterLoaderService::class);
    $apiRouterLoader->loadAllRoutes();
    
    $endpointsInfo = $apiRouterLoader->getApiEndpointsInfo();
    
    Log::info('ApiRouterLoaderService успешно выполнен', [
        'total_endpoints' => $endpointsInfo['total_endpoints'],
        'modules_with_api' => $endpointsInfo['modules_with_api'],
        'admin_api_loaded' => $endpointsInfo['admin_api_loaded']
    ]);

} catch (\Exception $e) {
    Log::error('Ошибка загрузки API маршрутов через ApiRouterLoaderService', [
        'message' => $e->getMessage(),
        'exception' => $e
    ]);
    
    // Резервный маршрут для ошибок API
    Route::prefix('api')->group(function () {
        Route::get('/error', function () {
            return response()->json([
                'status' => 'error',
                'message' => 'API router service failed to load',
                'timestamp' => now()->toISOString(),
                'error_code' => 'SERVICE_UNAVAILABLE'
            ], 503);
        })->name('api.fallback.error');
    });
}

Log::info('Загрузка API маршрутов: завершено');