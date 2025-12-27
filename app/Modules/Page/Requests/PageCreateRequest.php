<?php
/**
 * Валидация создания страницы.
 * Включает проверку уникальности slug и все необходимые поля.
 */
namespace App\Modules\Page\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'slug' => 'required|string|unique:pages,slug|regex:/^[a-z0-9-]+$/|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,private',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:pages,id',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Заголовок обязателен для заполнения.',
            'title.min' => 'Заголовок должен содержать не менее :min символов.',
            'slug.required' => 'URL-адрес обязателен для заполнения.',
            'slug.unique' => 'Такой URL-адрес уже используется.',
            'slug.regex' => 'URL-адрес может содержать только латинские буквы, цифры и дефисы.',
            'status.required' => 'Статус обязателен для выбора.',
            'status.in' => 'Выбран некорректный статус.',
            'parent_id.exists' => 'Выбранная родительская страница не существует.',
        ];
    }

    public function prepareForValidation()
    {
        if (!$this->has('slug') && $this->has('title')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->title)
            ]);
        }
    }
}