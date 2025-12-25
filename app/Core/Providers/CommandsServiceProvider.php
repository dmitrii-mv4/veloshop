<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;

class CommandsServiceProvider extends ServiceProvider
{
    /**
     * Список команд ядра
     */
    protected $commands = [
        \App\Core\Console\Commands\InstallKotiksCMSCommand::class,
        \App\Core\Console\Commands\RouteModulesCommand::class,
        \App\Core\Console\Commands\ClearLanguageCache::class,
        // Добавьте другие команды ядра здесь
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем команды только если классы существуют
        $availableCommands = [];
        
        foreach ($this->commands as $command) {
            if (class_exists($command)) {
                $availableCommands[] = $command;
            } else {
                \Log::warning("Command class not found: {$command}");
            }
        }
        
        if (!empty($availableCommands)) {
            $this->commands($availableCommands);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}