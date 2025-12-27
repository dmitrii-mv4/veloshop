<?php
/**
 * Валидация редактирования страницы.
 * Включает проверку уникальности slug с игнорированием текущей страницы.
 */
namespace App\Modules\Page\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pageId = $this->route('page')->id;
        
        return [
            'title' => 'required|string|min:3|max:255',
            'slug' => [
                'required',
                'string',
                Rule::unique('pages', 'slug')->ignore($pageId),
                'regex:/^[a-z0-9-]+$/',
                'max:255'
            ],
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,private',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'parent_id' => [
                'nullable',
                'exists:pages,id',
                function ($attribute, $value, $fail) use ($pageId) {
                    if ($value == $pageId) {
                        $fail('Страница не может быть родительской для самой себя.');
                    }
                }
            ],
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
        ];
    }
}