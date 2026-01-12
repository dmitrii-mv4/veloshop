<?php

namespace App\Modules\Catalog\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Services\OneCApiService;

class CatalogServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Catalog';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'catalog';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom($this->getModulePath('Database/Migrations'));
        $this->loadRoutes();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('catalog', function ($app) {
            return new \App\Modules\Catalog\Services\CatalogService(
                $app->make(\App\Modules\Catalog\Services\Api1CService::class)
            );
        });
        
        // Регистрация RouteServiceProvider если он существует
        if (class_exists('App\Modules\Catalog\Providers\RouteServiceProvider')) {
            $this->app->register(\App\Modules\Catalog\Providers\RouteServiceProvider::class);
        }

        // Регистрируем сервис работы с 1С
        $this->app->singleton(OneCApiService::class, function ($app) {
            return new OneCApiService();
        });
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $configPath = $this->getModulePath('Config/config.php');
        
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path($this->moduleNameLower . '.php'),
            ], 'config');
            $this->mergeConfigFrom(
                $configPath, $this->moduleNameLower
            );
        }
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = $this->getModulePath('Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Load routes.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        $routePath = $this->getModulePath('Routes/web.php');
        
        if (file_exists($routePath)) {
            Route::middleware(['web', 'auth', 'admin'])
                ->prefix('admin/catalog')
                ->name('catalog.')
                ->namespace('App\Modules\Catalog\Http\Controllers')
                ->group($routePath);
        }
    }

    /**
     * Get module path.
     *
     * @param string $path
     * @return string
     */
    private function getModulePath(string $path = ''): string
    {
        return app_path('Modules/' . $this->moduleName . '/' . $path);
    }

    /**
     * Get publishable view paths.
     *
     * @return array
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];
        
        foreach (config('view.paths', []) as $path) {
            $modulePath = $path . '/modules/' . $this->moduleNameLower;
            
            if (is_dir($modulePath)) {
                $paths[] = $modulePath;
            }
        }
        
        return $paths;
    }
}