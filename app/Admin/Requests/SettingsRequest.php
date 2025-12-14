<?php

namespace App\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
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
            'name_site' => 'required|min:3|max:50',
            'url_site' => 'required|url|min:3|max:255',
            'description_site' => 'max:255',
            'lang_admin' => 'required|min:2|in:ru,en',
        ];
    }

    public function messages()
    {
        return [
            'name_site.required' => 'Это поле обязательно для заполнения!',
            'name_site.min' => 'Имя должено быть не менее :min символов',
            'name_site.max' => 'Имя должно быть не более :max символов',
            'url_site.required' => 'Это поле обязательно для заполнения!',
            'url_site.url' => 'Поле должно содержать корректный URL адрес',
            'url_site.min' => 'Имя должено быть не менее :min символов',
            'url_site.max' => 'URL должен быть не более :max символов',
            'description_site.max' => 'Описание сайта должно быть не более :max символов',
        ];
    }
}