<?php

namespace App\Modules\ModuleGenerator\Services\Generator\Files;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Modules\Role\Models\Permission;
use App\Modules\Role\Models\RoleHasPermissions;

/**
 * Сервис для генерации политики для модулей
 * 
 * @param array $moduleData Настройки модулей
 * @param string $modulePolicyPath путь к директории модуля модели
 */

class Policy
{
    protected $moduleData;
    protected $modulePolicyPath;

    public function __construct($moduleData)
    {
        $this->moduleData = $moduleData;
    }

    public function generate()
    {
        // Создание структуры директорий
        $this->modulePolicyPath = $this->ensureModulesPoliciesDir();

        // Создаём политику
        $this->createPolicies();

        // Создаём разрешения для модуля
        $this->createPoliciesPermissions();

        // Создаём сразу доступ для админов
        $this->createPermissionsJoinRoleHasPermissions();
    }

    /**
     * Создает или проверяет существование директории для политики модуля
     * 
     * Директория создается по пути: app/Policies/Modules
     * 
     */
    private function ensureModulesPoliciesDir()
    {
        $modulePolicyPath = base_path($this->moduleData['path']['policies']);

        if (!File::exists($modulePolicyPath))
        {
            File::makeDirectory($modulePolicyPath, 0755, true);
        }
        return $modulePolicyPath;
    }

    /**
     * Создаём Policies
     */
    public function createPolicies()
    {
        // Полный путь к файлу
        $policyFilePath = base_path($this->moduleData['path']['policies'] . '/' . $this->moduleData['item']['policy_name']) . '.php';

        // Формируем названия для разрешений
        $moduleNameCodePermissions = $this->moduleData['code_module']."->permissions";
        $moduleNamePermissionsViewAny = $this->moduleData['code_module']."_viewAny";
        $moduleNamePermissionsCreate = $this->moduleData['code_module']."_create";
        $moduleNamePermissionsUpdate = $this->moduleData['code_module']."_update";
        $moduleNamePermissionsDelete = $this->moduleData['code_module']."_delete";

        $content = <<<PHP
<?php

namespace {$this->moduleData['namespace']['policies']};

use Illuminate\Auth\Access\Response;
use App\Modules\User\Models\User;

class {$this->moduleData['item']['policy_name']}
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User \$user): bool
    {
        // Получаем все разрешения пользователя
        \$permissions = \$user->permissions;

        // Поиск конкретного разрешения по имени
        \$showPermission = \$permissions->firstWhere('name', '$moduleNamePermissionsViewAny');

        if (\$showPermission)
        {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User \$user): bool
    {
        // Получаем все разрешения пользователя
        \$permissions = \$user->permissions;

        // Поиск конкретного разрешения по имени
        \$showPermission = \$permissions->firstWhere('name', '$moduleNamePermissionsCreate');

        if (\$showPermission)
        {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User \$user): bool
    {
        // Получаем все разрешения пользователя
        \$permissions = \$user->permissions;

        // Поиск конкретного разрешения по имени
        \$showPermission = \$permissions->firstWhere('name', '$moduleNamePermissionsUpdate');

        if (\$showPermission)
        {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User \$user): bool
    {
        // Получаем все разрешения пользователя
        \$permissions = \$user->permissions;

        // Поиск конкретного разрешения по имени
        \$showPermission = \$permissions->firstWhere('name', '$moduleNamePermissionsDelete');

        if (\$showPermission)
        {
            return true;
        }

        return false;
    }
}
PHP;

        // Записываем содержимое в файл
        File::put($policyFilePath, $content);
        
        if (!File::exists($policyFilePath)) {
            throw new \Exception("Файл политики не найден: ".$policyFilePath);
        }
        
        return true;
    }

    public function createPoliciesPermissions()
    {
        // Добавляем разрешения для модулей
        $permissions = [
            'viewAny' => 'Просмотр всех записей',
            'view' => 'Просмотр записи',
            'create' => 'Создание записей',
            'update' => 'Редактирование записей',
            'delete' => 'Удаление записей',
        ];

        foreach ($permissions as $action => $title) {
            Permission::create([
                'name' => $this->moduleData['code_module'] . '_' . $action,
                'title' => $title,
            ]);
        }
    }

    public function createPermissionsJoinRoleHasPermissions()
    {
        $moduleCode = $this->moduleData['code_module'];
        $roleId = 1; // ID роли Админа

        // Формируем массив имен разрешений для модуля
        $permissionNames = [
            "{$moduleCode}_viewAny",
            "{$moduleCode}_view",
            "{$moduleCode}_create", 
            "{$moduleCode}_update",
            "{$moduleCode}_delete"
        ];

        // Ищем соответствующие разрешения в БД через модель Permission
        $permissions = Permission::whereIn('name', $permissionNames)->get();

        // Подготавливаем данные для вставки в таблицу role_has_permissions
        $rolePermissionsData = [];
        
        foreach ($permissions as $permission) {
            // Проверяем, не существует ли уже такая связь
            $exists = RoleHasPermissions::where('role_id', $roleId)
                ->where('permission_id', $permission->id)
                ->exists();

            if (!$exists) {
                $rolePermissionsData[] = [
                    'role_id' => $roleId,
                    'permission_id' => $permission->id,
                ];
                
                Log::info("Prepared permission link: role {$roleId} -> {$permission->name}");
            } else {
                Log::info("Permission link already exists: role {$roleId} -> {$permission->name}");
            }
        }

        // Массово добавляем связи в таблицу role_has_permissions, если есть что добавлять
        if (!empty($rolePermissionsData)) {
            RoleHasPermissions::insert($rolePermissionsData);
            Log::info("Added " . count($rolePermissionsData) . " permission links for module {$moduleCode}");
        } else {
            Log::info("No new permission links to add for module {$moduleCode}");
        }

        return count($rolePermissionsData);
    }
}