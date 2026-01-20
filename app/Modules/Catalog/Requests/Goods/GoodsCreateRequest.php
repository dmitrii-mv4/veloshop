<?php

namespace App\Modules\Catalog\Requests\Goods;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request для создания товара
 * Валидация полей при создании нового товара
 * 
 * @package App\Modules\Catalog\Requests\Goods
 */
class GoodsCreateRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Правила валидации
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|min:3',
            'section_id' => 'nullable|integer|exists:catalog_sections,id',
            
            // SEO поля
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
        ];
    }

    /**
     * Подготовка данных для валидации
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Автоматически устанавливаем текущего пользователя как создателя
        if (auth()->check() && !$this->has('created_by')) {
            $this->merge([
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Сообщения об ошибках валидации
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Название товара обязательно для заполнения',
            'title.max' => 'Название товара не должно превышать 255 символов',
            'title.min' => 'Название товара должно содержать минимум 3 символа',
            'section_id.integer' => 'Раздел должен быть числовым значением',
            'section_id.exists' => 'Выбранный раздел не существует',
            'meta_title.max' => 'Meta Title не должен превышать 255 символов',
            'meta_description.max' => 'Meta Description не должен превышать 500 символов',
            'meta_keywords.max' => 'Meta Keywords не должны превышать 500 символов',
        ];
    }

    /**
     * Атрибуты полей для сообщений об ошибках
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'Название товара',
            'section_id' => 'Раздел каталога',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'meta_keywords' => 'Meta Keywords',
        ];
    }
}