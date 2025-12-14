<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserEditRequest extends FormRequest
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
            'email' => 'required|email|min:3|max:255',
            'role_id' => 'required',
        ];

        // Добавляем правила для пароля только если он указан
        if ($this->filled('password')) {
            $rules['password'] = 'required|min:8';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Это поле обязательно для заполнения!',
            'name.min' => 'Имя должено быть не менее :min символов',
            'email.required' => 'Это поле обязательно для заполнения!',
            'password.required' => 'Поле пароля обязательно для заполнения, если вы хотите сменить пароль',
            'password.min' => 'Пароль должен быть не менее :min символов',
            'password.confirmed' => 'Пароли не совпадают',
        ];
    }
}