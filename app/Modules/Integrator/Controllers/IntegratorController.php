<?php

namespace App\Modules\Integrator\Controllers;

use App\Core\Controllers\Controller;
use App\Modules\ModuleGenerator\Models\Module;
use App\Modules\Integrator\Models\Integrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntegratorController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        // Получаем параметры фильтрации
        $search = $request->input('search');
        $status = $request->input('status', 'all');
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        // Валидируем количество на странице
        $validPerPage = in_array($perPage, [5, 10, 25, 50]) ? (int)$perPage : 10;
        
        // Строим запрос с фильтрами
        $query = Integrator::query()
            ->when($search, function($q) use ($search) {
                return $q->where(function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('integration_description', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', function($q) use ($status) {
                return $q->where('is_active', $status === 'active');
            });

        // Применяем сортировку
        $validSortColumns = ['name', 'created_at', 'updated_at'];
        $validSortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : 'desc';
        
        if (in_array($sortBy, $validSortColumns)) {
            $query->orderBy($sortBy, $validSortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $integrations = $query->paginate($validPerPage)->withQueryString();
        
        // Статистика
        $totalIntegrations = Integrator::count();
        $activeIntegrations = Integrator::where('is_active', true)->count();
        $inactiveIntegrations = Integrator::where('is_active', false)->count();

        return view('integrator::index', compact(
            'integrations', 
            'search', 
            'perPage', 
            'sortBy', 
            'sortOrder',
            'status',
            'totalIntegrations',
            'activeIntegrations',
            'inactiveIntegrations'
        ));
    }

    public function create()
    {
        try {
            $modules = Module::all()->toArray();
            return view('integrator::create', compact('modules'));
        } catch (\Exception $e) {
            Log::error('Ошибка загрузки модулей для интеграции', [
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Не удалось загрузить список модулей');
        }
    }

    public function getModuleFields($moduleName)
    {
        try {
            // Временно: возвращаем тестовые данные
            $testFields = $this->getTestFields($moduleName);
            
            return response()->json([
                'success' => true,
                'fields' => $testFields,
                'module' => $moduleName,
                'debug' => 'Используются тестовые данные'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ошибка получения полей модуля', [
                'module' => $moduleName,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения полей модуля: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    private function getTestFields($moduleName): array
{
    // Базовые тестовые поля для разных типов модулей
    $baseFields = [
        ['name' => 'id', 'type' => 'integer', 'data_type' => 'int', 'nullable' => false],
        ['name' => 'title', 'type' => 'string', 'data_type' => 'varchar', 'nullable' => false],
        ['name' => 'description', 'type' => 'text', 'data_type' => 'text', 'nullable' => true],
        ['name' => 'is_active', 'type' => 'boolean', 'data_type' => 'boolean', 'nullable' => false],
        ['name' => 'created_at', 'type' => 'timestamp', 'data_type' => 'timestamp', 'nullable' => false],
        ['name' => 'updated_at', 'type' => 'timestamp', 'data_type' => 'timestamp', 'nullable' => false],
        ['name' => 'test', 'type' => 'string', 'data_type' => 'string', 'nullable' => false],
    ];
    
    // Специфичные поля в зависимости от имени модуля
    $specificFields = [];
    
    if (str_contains(strtolower($moduleName), 'product')) {
        $specificFields = [
            ['name' => 'price', 'type' => 'decimal', 'data_type' => 'decimal', 'nullable' => false],
            ['name' => 'sku', 'type' => 'string', 'data_type' => 'varchar', 'nullable' => true],
            ['name' => 'quantity', 'type' => 'integer', 'data_type' => 'integer', 'nullable' => false],
        ];
    } elseif (str_contains(strtolower($moduleName), 'news')) {
        $specificFields = [
            ['name' => 'content', 'type' => 'text', 'data_type' => 'text', 'nullable' => true],
            ['name' => 'author', 'type' => 'string', 'data_type' => 'varchar', 'nullable' => true],
            ['name' => 'published_at', 'type' => 'date', 'data_type' => 'date', 'nullable' => true],
        ];
    } elseif (str_contains(strtolower($moduleName), 'page')) {
        $specificFields = [
            ['name' => 'content', 'type' => 'text', 'data_type' => 'text', 'nullable' => true],
            ['name' => 'slug', 'type' => 'string', 'data_type' => 'varchar', 'nullable' => false],
        ];
    }
    
    return array_merge($baseFields, $specificFields);
}

    public function store(Request $request)
    {
        // Валидация
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service' => 'required|string',
            'service_name' => 'required|string',
            'module' => 'required|string',
            'sync_direction' => 'required|in:import,export,both',
            'field_mapping' => 'required|json',
        ]);

        try {
            Log::info('Создание интеграции', $validated);
            
            // Здесь будет сохранение интеграции в БД
            // Временно просто редирект с сообщением
            
            return redirect()->route('admin.integration.index')
                ->with('success', 'Интеграция успешно создана');
                
        } catch (\Exception $e) {
            Log::error('Ошибка создания интеграции', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Ошибка создания интеграции: ' . $e->getMessage());
        }
    }
}