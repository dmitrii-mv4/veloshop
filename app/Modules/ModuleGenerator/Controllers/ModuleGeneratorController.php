<?php

namespace App\Modules\ModuleGenerator\Controllers;

use App\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use App\Modules\ModuleGenerator\Models\Module;
use App\Modules\ModuleGenerator\Requests\CreateRequest;
use App\Modules\ModuleGenerator\Services\Generator\ModuleGeneratorService;
use App\Modules\ModuleGenerator\Services\Generator\CheckModuleService;

class ModuleGeneratorController extends Controller
{
    public function __construct(private ModuleGeneratorService $generatorService, private CheckModuleService $checkModuleService)
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        
        $query = Module::query();
        
        // Добавляем поиск по названию модуля и описанию
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('code_module', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Добавляем сортировку по умолчанию
        $query->orderBy('created_at', 'desc');
        
        $modules = $query->paginate($perPage)->withQueryString();
        
        return view('module_generator::index', compact('modules', 'search', 'perPage'));
    }

    public function create()
    {
        return view('module_generator::create');
    }

    public function store(CreateRequest $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            
            Log::info('Попытка создания модуля', [
                'validated_data_keys' => array_keys($validatedData),
                'user_id' => auth()->id()
            ]);

            // Проверка существования модуля
            $checkResult = $this->checkModuleService->main(
                $validatedData['code_module'],
                $validatedData['slug']
            );

            Log::debug('Результат проверки модуля:', $checkResult);

            if (!$checkResult['success']) {
                // Определяем, какая именно проверка не прошла
                if (isset($checkResult['conflicts'])) {
                    // Это проверка на зарезервированные имена
                    // Привязываем ошибку к полю code_module
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['code_module' => $checkResult['message']]);
                } else {
                    // Другие ошибки (таблица уже существует и т.д.)
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['system_error' => $checkResult['message']]);
                }
            }
            
            // Проверяем наличие обязательных полей
            $requiredFields = ['code_module', 'slug', 'status'];
            foreach ($requiredFields as $field) {
                if (!isset($validatedData[$field]) || empty($validatedData[$field])) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors([$field => "Поле '{$field}' обязательно для заполнения."]);
                }
            }
            
            // Проверяем существование модуля
            $codeModule = $validatedData['code_module'];
            if (Module::where('code_module', $codeModule)->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['code_module' => 'Модуль с таким кодом уже существует.']);
            }
            
            // Подготавливаем данные для сохранения
            $dataToSave = [
                'code_module' => $codeModule,
                'slug' => $validatedData['slug'],
                'status' => $validatedData['status'],
                'section_seo' => $request->has('section_seo') && filter_var($request->input('section_seo'), FILTER_VALIDATE_BOOLEAN),
                'section_categories' => $request->has('section_categories') && filter_var($request->input('section_categories'), FILTER_VALIDATE_BOOLEAN),
                'user_id_created' => auth()->id(),
            ];
            
            // Создаем запись
            $moduleRecord = Module::create($dataToSave);
            
            if (!$moduleRecord) {
                throw new \Exception('Не удалось создать запись модуля в базе данных');
            }
            
            // Подготавливаем данные для генератора
            $generationData = [
                'code_module' => $codeModule,
                'code_name' => ucfirst($codeModule), // или берем из name['ru']
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'slug' => $validatedData['slug'],
                'status' => $validatedData['status'],
                'section_seo' => $dataToSave['section_seo'],
                'section_categories' => $dataToSave['section_categories'],
                'properties' => $validatedData['properties'] ?? [],
                'module_record_id' => $moduleRecord->id,
            ];

            
            
            // Если есть name, используем его
            if (isset($validatedData['name']) && is_array($validatedData['name'])) {
                $generationData['code_name'] = $validatedData['name']['ru'] ?? ucfirst($codeModule);
            }
            
            Log::info('Данные для генератора', $generationData);
            
            // Запускаем генератор
            $generationResult = $this->generatorService->main($generationData);
            
            Log::info('Результат генерации', [
                'result' => $generationResult
            ]);
            
            // Проверяем результат
            if (is_null($generationResult)) {
                throw new \Exception('Сервис генерации вернул null');
            }
            
            DB::commit();
            
            return redirect()
                ->route('admin.module_generator.index')
                ->with('success', 'Модуль "' . $codeModule . '" успешно создан.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании модуля', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['system_error' => 'Произошла ошибка: ' . $e->getMessage()]);
        }
    }

    public function delete($module)
    {
        DB::beginTransaction();
        
        try {
            // 1. Поиск модуля
            if (!$moduleRecord = Module::where('code_module', $module)->first()) {
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => "Модуль '{$module}' не найден."]);
            }
            
            $moduleName = ucfirst($module);
            $tableName = strtolower($module);
            $transTableName = $tableName . '_trans';
            
            Log::info('Удаление модуля', [
                'module' => $module,
                'table_name' => $tableName,
                'trans_table_name' => $transTableName,
                'module_name' => $moduleName
            ]);
            
            // 2. Удаление основной таблицы модуля
            try { 
                if (Schema::hasTable($tableName)) {
                    Schema::drop($tableName);
                    Log::info("Таблица {$tableName} удалена.");
                } else {
                    Log::warning("Таблица {$tableName} не существует в БД.");
                }
            } catch (\Exception $e) { 
                Log::error("Ошибка удаления таблицы {$tableName}: {$e->getMessage()}");
                // Не бросаем исключение - продолжаем удаление других частей
            }
            
            // 3. Удаление таблицы переводов (articles_trans)
            try {
                if (Schema::hasTable($transTableName)) {
                    Schema::drop($transTableName);
                    Log::info("Таблица переводов {$transTableName} удалена.");
                } else {
                    Log::warning("Таблица переводов {$transTableName} не существует в БД.");
                }
            } catch (\Exception $e) {
                Log::error("Ошибка удаления таблицы переводов {$transTableName}: {$e->getMessage()}");
                // Не бросаем исключение - продолжаем удаление других частей
            }
            
            // 4. Удаление прав доступа модуля (ИСПРАВЛЕННЫЙ КОД)
            try {
                // Получаем ID permissions для этого модуля
                $permissionIds = DB::table('permissions')
                    ->where('name', 'like', $module . '_%')
                    ->pluck('id')
                    ->toArray();
                
                Log::info('Найдены permissions для удаления', [
                    'module' => $module,
                    'permission_ids' => $permissionIds,
                    'pattern' => $module . '_%'
                ]);
                
                if (!empty($permissionIds)) {
                    // 4.1 Сначала удаляем связи из role_has_permissions
                    $deletedFromRoleHasPermissions = DB::table('role_has_permissions')
                        ->whereIn('permission_id', $permissionIds)
                        ->delete();
                    
                    Log::info('Удалены связи из role_has_permissions для модуля', [
                        'module' => $module,
                        'deleted_count' => $deletedFromRoleHasPermissions,
                        'permission_ids' => $permissionIds
                    ]);
                    
                    // 4.2 Затем удаляем сами permissions
                    $deletedFromPermissions = DB::table('permissions')
                        ->whereIn('id', $permissionIds)
                        ->delete();
                        
                    Log::info('Права доступа модуля удалены из permissions', [
                        'module' => $module,
                        'deleted_count' => $deletedFromPermissions
                    ]);
                } else {
                    Log::info('Права доступа для модуля не найдены', ['module' => $module]);
                }
            } catch (\Exception $e) { 
                Log::warning("Ошибка удаления прав доступа: {$e->getMessage()}", [
                    'error_trace' => $e->getTraceAsString()
                ]);
                // Не бросаем исключение - продолжаем
            }
            
            // 5. Удаление миграций (файлов и записей из таблицы migrations)
            try {
                $migrationsPath = database_path('migrations');
                $deletedMigrationFiles = [];
                
                if (File::exists($migrationsPath)) {
                    foreach (File::files($migrationsPath) as $file) {
                        $content = File::get($file->getPathname());
                        // Ищем миграции связанные с этим модулем
                        if (str_contains($content, $tableName) || 
                            str_contains($content, "'$tableName'") || 
                            str_contains($content, "\"$tableName\"") ||
                            str_contains($content, $transTableName) ||
                            str_contains($content, "'$transTableName'") ||
                            str_contains($content, "\"$transTableName\"")) {
                            
                            // Получаем имя миграции без расширения
                            $migrationName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                            $deletedMigrationFiles[] = $migrationName;
                            
                            File::delete($file->getPathname());
                            Log::info('Удалена миграция', ['file' => $file->getFilename()]);
                        }
                    }
                }
                
                $moduleMigrationsPath = base_path("Modules/{$moduleName}/Database/Migrations");
                if (File::exists($moduleMigrationsPath)) {
                    // Получаем имена файлов миграций из папки модуля
                    $moduleMigrationFiles = File::files($moduleMigrationsPath);
                    foreach ($moduleMigrationFiles as $file) {
                        $migrationName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                        $deletedMigrationFiles[] = $migrationName;
                    }
                    
                    File::deleteDirectory($moduleMigrationsPath);
                    Log::info('Удалена директория миграций модуля', ['path' => $moduleMigrationsPath]);
                }
                
                // Удаляем записи из таблицы migrations
                if (!empty($deletedMigrationFiles)) {
                    // Удаляем по именам миграций
                    DB::table('migrations')
                        ->whereIn('migration', $deletedMigrationFiles)
                        ->delete();
                    Log::info('Удалены записи из таблицы migrations', [
                        'count' => count($deletedMigrationFiles),
                        'migrations' => $deletedMigrationFiles
                    ]);
                }
                
                // Также удаляем по названию таблиц (на всякий случай)
                DB::table('migrations')
                    ->where('migration', 'like', "%{$tableName}%")
                    ->orWhere('migration', 'like', "%{$transTableName}%")
                    ->delete();
                    
                // Удаляем по названию модуля
                DB::table('migrations')
                    ->where('migration', 'like', "%{$module}%")
                    ->delete();
                    
            } catch (\Exception $e) { 
                Log::warning("Ошибка удаления миграций: {$e->getMessage()}");
            }
            
            // 6. Удаление папки модуля (если существует)
            $modulePath = base_path("Modules/{$moduleName}");
            if (File::exists($modulePath)) {
                File::deleteDirectory($modulePath);
                Log::info('Удалена папка модуля', ['path' => $modulePath]);
            } else {
                Log::warning('Папка модуля не найдена', ['path' => $modulePath]);
            }
            
            // 7. Удаление записи модуля из БД
            $moduleRecord->delete();
            Log::info('Запись модуля удалена из БД');
            
            DB::commit();
            
            // 8. Очистка кеша Laravel после удаления
            try {
                \Artisan::call('optimize:clear');
                Log::info('Кеш Laravel очищен после удаления модуля.');
            } catch (\Exception $e) {
                Log::warning('Не удалось очистить кеш Laravel: ' . $e->getMessage());
            }
            
            // 9. Редирект на главную страницу админки (ИСПРАВЛЕНО)
            // Используем абсолютный путь вместо именованного маршрута
            return redirect()
                ->route('admin.module_generator.index')
                ->with('success', "Модуль '{$moduleName}' удален.");
                    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при удалении модуля', [
                'module' => $module,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => "Ошибка при удалении модуля: " . $e->getMessage()]);
        }
    }
}