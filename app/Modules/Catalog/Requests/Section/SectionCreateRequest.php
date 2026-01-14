<?php

namespace App\Modules\Catalog\Requests\Section;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

/**
 * Request для создания раздела каталога
 * Валидация полей при создании нового раздела
 */
class SectionCreateRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса
     *
     * @return bool
     */
    public function authorize(): bool
    {
        Log::info('Проверка авторизации для создания раздела каталога', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'Unknown'
        ]);
        
        return true;
    }

    /**
     * Правила валидации
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|min:2',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9-]+$/|unique:catalog_sections,slug',
            'description' => 'nullable|string|max:2000',
            'parent_id' => 'nullable|integer|exists:catalog_sections,id',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Сообщения об ошибках валидации
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название раздела обязательно для заполнения',
            'name.max' => 'Название раздела не должно превышать 255 символов',
            'name.min' => 'Название раздела должно содержать минимум 2 символа',
            'slug.required' => 'URL-адрес обязателен для заполнения',
            'slug.regex' => 'URL-адрес должен содержать только латинские буквы в нижнем регистре, цифры и дефисы',
            'slug.unique' => 'Раздел с таким URL-адресом уже существует',
            'description.max' => 'Описание не должно превышать 2000 символов',
            'parent_id.exists' => 'Выбранный родительский раздел не существует',
            'sort_order.integer' => 'Порядок сортировки должен быть целым числом',
            'sort_order.min' => 'Порядок сортировки не может быть отрицательным',
            'sort_order.max' => 'Порядок сортировки не должен превышать 999',
            'meta_title.max' => 'Мета-заголовок не должен превышать 255 символов',
            'meta_keywords.max' => 'Ключевые слова не должны превышать 255 символов',
            'meta_description.max' => 'Мета-описание не должно превышать 500 символов',
            'is_active.required' => 'Статус активности обязателен для заполнения',
            'is_active.boolean' => 'Некорректное значение статуса активности',
        ];
    }

    /**
     * Подготовка данных для валидации
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge([
                'slug' => strtolower(trim($this->slug)),
            ]);
        }
        
        if (!$this->has('sort_order')) {
            $this->merge([
                'sort_order' => 0,
            ]);
        }
        
        Log::debug('Подготовка данных для валидации создания раздела', [
            'data' => $this->all(),
            'user_id' => auth()->id()
        ]);
    }
}