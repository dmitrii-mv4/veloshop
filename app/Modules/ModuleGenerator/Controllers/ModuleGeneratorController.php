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

    public function index()
    {
        $modules = Module::paginate(15);

        return view('module_generator::index', compact('modules'));
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
            
            Log::info('Удаление модуля', [
                'module' => $module,
                'table_name' => $tableName,
                'module_name' => $moduleName
            ]);
            
            // 2. Удаление таблицы
            try { 
                if (Schema::hasTable($tableName)) {
                    Schema::drop($tableName);
                    Log::info("Таблица {$tableName} удалена.");
                } else {
                    Log::warning("Таблица {$tableName} не существует в БД.");
                }
            } catch (\Exception $e) { 
                Log::error("Ошибка удаления таблицы: {$e->getMessage()}");
                // Не бросаем исключение - продолжаем удаление других частей
            }
            
            // 3. Удаление прав доступа (ПЕРВЫМ ДЕЛОМ, перед удалением записи модуля)
            try {
                // Сначала удаляем связи из role_has_permissions
                DB::table('role_has_permissions')
                    ->whereIn('permission_id', function($query) use ($module) {
                        $query->select('id')
                            ->from('permissions')
                            ->where('name', 'like', "module_{$module}_%");
                    })
                    ->delete();
                
                // Затем удаляем сами permissions
                DB::table('permissions')
                    ->where('name', 'like', "module_{$module}_%")
                    ->delete();
                    
                Log::info('Права доступа модуля удалены', ['module' => $module]);
            } catch (\Exception $e) { 
                Log::warning("Ошибка удаления прав: {$e->getMessage()}");
                // Не бросаем исключение - продолжаем
            }
            
            // 4. Удаление миграций (файлов и записей из таблицы migrations)
            try {
                $migrationsPath = database_path('migrations');
                $deletedMigrationFiles = [];
                
                if (File::exists($migrationsPath)) {
                    foreach (File::files($migrationsPath) as $file) {
                        $content = File::get($file->getPathname());
                        // Ищем миграции связанные с этим модулем
                        if (str_contains($content, $tableName) || 
                            str_contains($content, "'$tableName'") || 
                            str_contains($content, "\"$tableName\"")) {
                            
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
                    Log::info('Удалены записи из таблицы migrations', ['count' => count($deletedMigrationFiles)]);
                }
                
                // Также удаляем по названию таблицы (на всякий случай)
                DB::table('migrations')
                    ->where('migration', 'like', "%{$tableName}%")
                    ->delete();
                    
                // Удаляем по названию модуля
                DB::table('migrations')
                    ->where('migration', 'like', "%{$module}%")
                    ->delete();
                    
            } catch (\Exception $e) { 
                Log::warning("Ошибка удаления миграций: {$e->getMessage()}");
            }
            
            // 5. Удаление папки модуля (если существует)
            $modulePath = base_path("Modules/{$moduleName}");
            if (File::exists($modulePath)) {
                File::deleteDirectory($modulePath);
                Log::info('Удалена папка модуля', ['path' => $modulePath]);
            } else {
                Log::warning('Папка модуля не найдена', ['path' => $modulePath]);
            }
            
            // 6. Удаление записи модуля из БД
            $moduleRecord->delete();
            Log::info('Запись модуля удалена из БД');
            
            DB::commit();
            
            // 7. Очистка кеша Laravel после удаления
            try {
                \Artisan::call('optimize:clear');
                Log::info('Кеш Laravel очищен после удаления модуля.');
            } catch (\Exception $e) {
                Log::warning('Не удалось очистить кеш Laravel: ' . $e->getMessage());
            }
            
            return redirect()
                ->route('module_generator.index')
                ->with('success', "Модуль '{$moduleName}' удален.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при удалении модуля', [
                'module' => $module,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => "Ошибка при удалении модуля: " . $e->getMessage()]);
        }
    }
}