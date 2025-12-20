<?php

namespace App\Admin\Controllers;

use App\Core\Controllers\Controller;
use App\Core\Services\LocaleService;
use Illuminate\Http\Request;
use App\Admin\Requests\SettingsRequest;
use App\Modules\User\Models\User;
use App\Admin\Models\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Контроллер настройки админки и сайта
 * 
 * @param string $localeService Название таблицы текущего модуля
 */

class Dashboard extends Controller
{
    protected $localeService;

    public function __construct(LocaleService $localeService)
    {
        $this->middleware('admin');

        // Запуск сервиса языка
        $this->localeService = $localeService;
    }

    public function dashboard()
    {
        $users_count = User::count();

        return view('admin::dashboard', compact('users_count'));
    }

    public function settings()
    {
        $settings = Settings::get();
        $settings = $settings[0];
        
        $systemInfo = $this->getSystemInfo();
        
        return view('admin::settings', compact('settings', 'systemInfo'));
    }

    public function settings_update(Settings $settings, SettingsRequest $request)
    {
        $validated = $request->validated();

        $settings->update([
            'name_site' => $validated['name_site'],
            'url_site' => $validated['url_site'],
            'description_site' => $validated['description_site'],
        ]);

        // Язык для админ панели
        $this->localeService->setLocale($validated['lang_admin']);

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Настройки успешно обновлены');
    }

    /**
     * Получить информацию о системе для нового шаблона
     */
    private function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Неизвестно',
            'database_driver' => config('database.default'),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug') ? 'Включен' : 'Выключен',
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'users_count' => User::count(),
            'settings_updated' => Settings::first()->updated_at ?? now(),
        ];
    }
}
