<?php

namespace App\Modules\Articles\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')->id ?? $this->route('category');
        return [
            'title'       => 'sometimes|required|string|max:255',
            'slug'        => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('articles_categories', 'slug')->ignore($categoryId),
            ],
            'description' => 'nullable|string',
        ];
    }
}