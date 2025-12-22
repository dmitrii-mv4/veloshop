<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
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
        return [
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|min:3|max:255|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'is_lang' => 'string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Это поле обязательно для заполнения!',
            'name.min' => 'Имя должно быть не менее :min символов',
            'email.required' => 'Это поле обязательно для заполнения!',
            'email.unique' => 'Пользователь с таким email уже существует',
            'role_id.required' => 'Выберите роль пользователя',
            'role_id.exists' => 'Выбранная роль не существует',
            'password.required' => 'Это поле обязательно для заполнения!',
            'password_confirmation.required' => 'Это поле обязательно для заполнения!',
            'password.confirmed' => 'Пароли не совпадают',
            'password.min' => 'Пароль должен быть не менее :min символов',
            'phone.max' => 'Телефон должен быть не более :max символов',
            'position.max' => 'Должность должна быть не более :max символов',
            'bio.max' => 'Биография должна быть не более :max символов',
        ];
    }
}