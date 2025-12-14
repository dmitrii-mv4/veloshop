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

        return view('admin::settings', compact('settings'));
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

        return redirect()->route('admin.settings')->with('success', 'Настройки обновлены');
    }
}
