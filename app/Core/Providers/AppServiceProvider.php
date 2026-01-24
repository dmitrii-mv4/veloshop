<?php

namespace App\Core\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureHttps();

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function configureHttps(): void
    {
        $forceHttps = config('secure.force_https');

        if ($forceHttps === true) {
            // Always force
            URL::forceScheme('https');
            return;
        }

        if ($forceHttps === false) {
            // Never force
            URL::forceScheme('http');
            return;
        }

        if ($forceHttps === 'auto') {
            // Auto-detect
            $this->autoConfigureHttps();
            return;
        }

        if (is_array($forceHttps)) {
            // Force in specific environments
            if (in_array(app()->environment(), $forceHttps)) {
                URL::forceScheme('https');
            }
        }
    }

    protected function autoConfigureHttps(): void
    {
        // Check APP_URL first
        $appUrl = config('app.url');
        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
            return;
        }

        // Check if behind HTTPS proxy
        if (request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
            return;
        }

        // Check environment
        if (in_array(app()->environment(), config('secure.https_environments', []))) {
            URL::forceScheme('https');
            return;
        }

        // Default to HTTP for local development
        if (app()->environment('local')) {
            URL::forceScheme('http');
        }
    }
}
