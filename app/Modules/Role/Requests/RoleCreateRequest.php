<?php
// app/Modules/Role/Requests/RoleCreateRequest.php

namespace App\Modules\Role\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('roles_create');
    }

    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('roles', 'name')->where(function ($query) {
                    return $query->where('is_system', false);
                })
            ]
        ];

        // Получаем все разрешения из БД и добавляем правила для них
        $permissions = \App\Modules\Role\Models\Permission::all();
        foreach ($permissions as $permission) {
            $rules[$permission->name] = 'sometimes|boolean';
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
        ];
    }

    public function attributes(): array
    {
        $attributes = ['name' => 'Название роли'];
        
        // Добавляем человеко-читаемые названия для разрешений
        $permissions = \App\Modules\Role\Models\Permission::all();
        foreach ($permissions as $permission) {
            $attributes[$permission->name] = $permission->title;
        }
        
        return $attributes;
    }
}