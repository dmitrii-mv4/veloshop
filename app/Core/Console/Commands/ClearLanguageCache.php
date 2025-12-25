<?php

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use App\Admin\Services\LanguageService;

class ClearLanguageCache extends Command
{
    protected $signature = 'admin:clear-language-cache';
    protected $description = 'Очистить кэш языков админ-панели';
    
    public function handle()
    {
        $service = app(LanguageService::class);
        $service->clearLanguagesCache();
        
        $this->info('Кэш языков админ-панели очищен.');
        return 0;
    }
}