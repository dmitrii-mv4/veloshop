<?php

namespace App\Modules\ModuleGenerator\Services\Generator;

use Illuminate\Support\Str;

/**
 * Сервис настроек для модулей
 * 
 * @param array $validatedData Данные, которые получаем из формы запроса при создании модулей
 * @param string $codeName Код модуля с заглавной буквой
 * @param string $mainDir Базовая папка для всех модулей
 * @param string $dirModule Директория с текущем модулем
 * @param string $dirModuleNamespace Директория с текущем модулем для Namespace
 * 
 * @param string $dir Название директории с модулем
 */

class ModuleConfigFormService
{
    protected array $validatedData;
    protected string $codeName;
    protected string $mainDir;
    protected string $dirModule;
    protected string $dirModuleNamespace;

    public function build(array $validatedData): array
    {
        // Проверяем, что данные содержатся
        if (empty($validatedData)) {
            throw new \InvalidArgumentException('Данные модуля пустные, невозможно собрать конфигурацию.');
        }

        $this->validatedData = $validatedData;

        // Собираем название модуля для путей и пространства namespace
        $this->codeName = Str::studly($this->validatedData['code_module']);

        // Назвначаем базовую папку для всех модулей
        $this->mainDir = 'Modules';

        // Собираем директорию с текущем модулем
        $this->dirModule =  $this->mainDir . '/' . $this->codeName;

        // Собираем директорию с текущем модулем для Namespace
        $this->dirModuleNamespace = $this->mainDir . '\\' . $this->codeName;

        // Собираем в 1 массив
        $configData = [
            'code_module' => $this->validatedData['code_module'],
            'code_name' => $this->codeName,
            'mod_name' => $validatedData['name'],
            'mod_description' => $validatedData['description'],
            'path' => $this->buildPaths(),
            'item' => $this->buildItem(),
            'trans' => $this->buildTrans(),
            'namespace' => $this->buildNamespaces(),
            'properties' => $this->validatedData['properties'],
            'option' => [
                'seo' => $validatedData['option_seo'],
                'trash' => $validatedData['option_trash'],
            ],
        ];

        return $configData;
    }

    /**
     * Создаём пути к инструментам модуля
     */
    private function buildPaths()
    {
        return [
            'mainDir' => $this->mainDir,
            'modules' => $this->dirModule,
            'full_base_module' => base_path($this->dirModule),
            'migration' => '/database/migrations/',
            'model' => $this->dirModule . '/Models',
            'views' => $this->dirModule . '/views',
            'request' => $this->dirModule . '/Requests',
            'controller' => $this->dirModule . '/Controllers',
            'middleware' => $this->dirModule . '/Middleware',
            'policies' => $this->dirModule . '/Policies',
            'router' => $this->dirModule . '/routes',
        ];
    }

    /**
     * Собираем пути и названия таблиц к Item
     */
    private function buildItem()
    {
        return [
            'table_name' => $this->validatedData['code_module'],
            'migration_name' => 'create_' . $this->validatedData['code_module'] . '_table',
            'model_name' => $this->codeName,
            'request_name_create' => $this->codeName . 'CreateRequest',
            'request_name_update' => $this->codeName . 'UpdateRequest',
            'controller_name' => $this->codeName . 'Controller',
            'controller_name_api' => $this->codeName . 'ApiController',
            'middleware_name_index' => $this->codeName . 'IndexMiddleware',
            'middleware_name_create' => $this->codeName . 'CreateMiddleware',
            'middleware_name_update' => $this->codeName . 'UpdateMiddleware',
            'middleware_name_delete' => $this->codeName . 'DeleteMiddleware',
            'policy_name' => $this->codeName . 'Policy',
        ];
    }

    /**
     * Собираем пути и названия таблиц к Trans
     */
    private function buildTrans()
    {
        return [
            'table_name' => $this->validatedData['code_module'] . '_trans',
            'migration_name' => 'create_' . $this->validatedData['code_module'] . '_trans_table',
            'model_name' => $this->codeName . 'Trans',
        ];
    }

    /**
     * Создаём namespace и их использование для модулей
     */
    private function buildNamespaces()
    {
        return [
            'model' => $this->dirModuleNamespace . '\\Models',
            'controller' => $this->dirModuleNamespace . '\\Controllers',
            'controller_api' => $this->dirModuleNamespace . '\\Controllers\Api',
            'requests' => $this->dirModuleNamespace . '\\Requests',
            'middleware_index' => $this->dirModuleNamespace . '\Middleware',
            'middleware_create' => $this->dirModuleNamespace . '\Middleware',
            'middleware_update' => $this->dirModuleNamespace . '\Middleware',
            'middleware_delete' => $this->dirModuleNamespace . '\Middleware',
            'policies' => $this->dirModuleNamespace . '\Policies',
            'use' => [
                'model' => $this->dirModuleNamespace . '\Models\\' . $this->codeName,
                'model_category' => $this->dirModuleNamespace . '\Models\\' . $this->codeName . 'Category',
                'controller' => $this->dirModuleNamespace . '\Controllers\\' . $this->codeName . 'Controller',
                'controller_category' => $this->dirModuleNamespace . '\Controllers\\' . $this->codeName . 'CategoryController',
                'controller_api' => $this->dirModuleNamespace . '\\Controllers\Api\\' . $this->codeName . 'ApiController',
                'request_create' => $this->dirModuleNamespace . '\Requests\\' . $this->codeName . 'CreateRequest',
                'request_update' => $this->dirModuleNamespace . '\Requests\\' . $this->codeName . 'UpdateRequest',
                'request_create_category' => $this->dirModuleNamespace . '\Requests\\' . $this->codeName . 'CategoryCreateRequest',
                'request_update_category' => $this->dirModuleNamespace . '\Requests\\' . $this->codeName . 'CategoryUpdateRequest',
                'middleware_index' => $this->dirModuleNamespace . '\Middleware\\' . $this->codeName . 'IndexMiddleware',
                'middleware_create' => $this->dirModuleNamespace . '\Middleware\\' . $this->codeName . 'CreateMiddleware',
                'middleware_update' => $this->dirModuleNamespace . '\Middleware\\' . $this->codeName . 'UpdateMiddleware',
                'middleware_delete' => $this->dirModuleNamespace . '\Middleware\\' . $this->codeName . 'DeleteMiddleware',
            ],
        ];
    }
}