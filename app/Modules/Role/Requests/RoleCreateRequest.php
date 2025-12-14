<?php

namespace App\Modules\Role\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|min:3|max:255',
            'show_admin' => 'sometimes|boolean',
            
            // Права для пользователей
            'users_viewAny' => 'sometimes|boolean',
            'users_view' => 'sometimes|boolean',
            'users_create' => 'sometimes|boolean',
            'users_update' => 'sometimes|boolean',
            'users_delete' => 'sometimes|boolean',
            
            // Права для ролей
            'roles_viewAny' => 'sometimes|boolean',
            'roles_create' => 'sometimes|boolean',
            'roles_update' => 'sometimes|boolean',
            'roles_delete' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Это поле обязательно для заполнения!',
            'name.min' => 'Имя должно быть не менее :min символов',
            'name.max' => 'Имя должно быть не более :max символов',
        ];
    }
}