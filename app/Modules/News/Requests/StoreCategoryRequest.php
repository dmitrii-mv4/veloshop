<?php

namespace App\Modules\News\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:news_categories,slug',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название категории обязательно.',
            'slug.unique'    => 'Такой символьный код уже используется.',
        ];
    }
}