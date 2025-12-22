<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SwitchLanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }
    
    /**
     * Get the validation rules that apply to the request
     * @return array
     */
    public function rules(): array
    {
        return [
            'locale' => 'required|string|in:ru,en',
        ];
    }
    
    /**
     * Get custom messages for validator errors
     * @return array
     */
    public function messages(): array
    {
        return [
            'locale.required' => 'Язык обязателен для выбора',
            'locale.string' => 'Язык должен быть строкой',
            'locale.in' => 'Выбранный язык не поддерживается',
        ];
    }
}