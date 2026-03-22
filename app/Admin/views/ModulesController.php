<?php

namespace App\Admin\Controllers;

use App\Core\Controllers\Controller;
use App\Core\Services\ModuleDiscoveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер для управления модулями
 * 
 * Отвечает за отображение списка модулей и управление их состоянием.
 */
class ModulesController extends Controller
{
    /**
     * Сервис обнаружения модулей
     * 
     * @var ModuleDiscoveryService
     */
    protected ModuleDiscoveryService $moduleService;

    /**
     * Конструктор
     * 
     * @param ModuleDiscoveryService $moduleService
     */
    public function __construct(ModuleDiscoveryService $moduleService)
    {
        $this->middleware('admin');
        $this->moduleService = $moduleService;
    }

    /**
     * Отображение списка всех модулей
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Получаем все обнаруженные модули с их конфигурациями
        $modules = $this->moduleService->discoverAllModules();

        Log::info('Открыт список модулей', ['count' => count($modules)]);

        return view('admin::modules.index', compact('modules'));
    }

    /**
     * Переключение активности модуля
     * 
     * Временная реализация без изменения файлов.
     * В будущем статус модуля следует хранить в БД.
     *
     * @param string $module
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle(string $module)
    {
        Log::info('Попытка переключения статуса модуля', ['module' => $module]);

        // Здесь должна быть логика изменения статуса в БД или конфиге
        // Пока просто возвращаем пользователя назад с уведомлением

        return redirect()->back()->with('success', "Статус модуля «{$module}» изменён (демо-режим)");
    }
}