<?php

namespace App\Modules\Catalog\Requests\Goods;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request для создания товара
 * Валидация полей при создании нового товара
 */
class GoodsCreateRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Правила валидации
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'articul' => 'required|string|max:100|unique:catalog_goods,articul',
            'section_id' => 'nullable|integer|exists:catalog_sections,id',
        ];
    }

    /**
     * Сообщения об ошибках валидации
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'title.required' => 'Название товара обязательно для заполнения',
            'title.max' => 'Название товара не должно превышать 255 символов',
            'articul.required' => 'Артикул товара обязателен для заполнения',
            'articul.max' => 'Артикул товара не должен превышать 100 символов',
            'articul.unique' => 'Товар с таким артикулом уже существует',
            'section_id.integer' => 'Раздел должен быть числовым значением',
            'section_id.exists' => 'Выбранный раздел не существует',
        ];
    }
}