<?php

namespace App\Modules\Role\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');
        
        // Роль Администратор (id=1) нельзя редактировать
        if ($role->id == 1) {
            return false;
        }
        
        // Для системных ролей разрешаем только системным пользователям
        if ($role->is_system) {
            return auth()->check() && auth()->user()->is_system;
        }
        
        // Для несистемных ролей проверяем право roles_update
        return auth()->check() && auth()->user()->hasPermission('roles_update');
    }

    public function rules(): array
    {
        $role = $this->route('role');
        
        $rules = [];
        
        // Добавляем правило для имени только для несистемных ролей
        if (!$role->is_system) {
            $rules['name'] = [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role->id)->where(function ($query) {
                    return $query->where('is_system', false);
                })
            ];
        }

        // Получаем все разрешения из БД и добавляем правила для них
        $permissions = \App\Modules\Role\Models\Permission::all();
        foreach ($permissions as $permission) {
            // Для роли Администратор (id=1) все разрешения обязательны и заблокированы
            // Для роли Пользователь (id=3) разрешения НЕ обязательны
            if ($role->id == 1 && in_array($permission->name, ['show_admin', 'users_viewAny', 'roles_viewAny'])) {
                $rules[$permission->name] = 'required|boolean|accepted';
            } else {
                $rules[$permission->name] = 'sometimes|boolean';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название роли обязательно для заполнения',
            'name.min' => 'Название роли должно быть не менее :min символов',
            'name.max' => 'Название роли должно быть не более :max символов',
            'name.unique' => 'Роль с таким названием уже существует',
            'show_admin.accepted' => 'Роль "Администратор" должна иметь доступ к админ-панели',
            'users_viewAny.accepted' => 'Роль "Администратор" должна иметь доступ к просмотру пользователей',
            'roles_viewAny.accepted' => 'Роль "Администратор" должна иметь доступ к просмотру ролей',
        ];
    }

    public function attributes(): array
    {
        $attributes = [];
        
        // Добавляем атрибут имени только для несистемных ролей
        $role = $this->route('role');
        if (!$role->is_system) {
            $attributes['name'] = 'Название роли';
        }
        
        $permissions = \App\Modules\Role\Models\Permission::all();
        foreach ($permissions as $permission) {
            $attributes[$permission->name] = $permission->title;
        }
        
        return $attributes;
    }
}