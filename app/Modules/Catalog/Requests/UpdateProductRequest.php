<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

/**
 * Запрос на обновление товара
 * 
 * Валидация данных при обновлении существующего товара.
 */
class UpdateProductRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Правила валидации
     *
     * @return array
     */
    public function rules()
    {
        // Получаем ID товара из маршрута
        $productId = $this->route('product');
        
        Log::info('UpdateProductRequest validation started', [
            'route_product_id' => $productId,
            'input_product_id' => $this->input('product_id')
        ]);

        // Находим текущий товар по ID
        $currentProduct = \App\Modules\Catalog\Models\Product::find($productId);
        
        // Если товар найден, исключаем его product_id из проверки уникальности
        $uniqueRule = 'required|string|max:50';
        if ($currentProduct && $this->input('product_id')) {
            $uniqueRule .= '|unique:catalog_products,product_id,' . $currentProduct->id . ',id';
        }

        return [
            'product_id' => $uniqueRule,
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'seazon' => 'nullable|string|max:50',
            'group_name' => 'nullable|string|max:100',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
        ];
    }

    /**
     * Сообщения об ошибках валидации
     *
     * @return array
     */
    public function messages()
    {
        return [
            'product_id.required' => 'Уникальный ID товара обязателен для заполнения',
            'product_id.unique' => 'Товар с таким ID уже существует',
            'name.required' => 'Название товара обязательно для заполнения',
            'name.max' => 'Название товара не должно превышать 255 символов',
            'brand.max' => 'Название бренда не должно превышать 100 символов',
            'model.max' => 'Модель не должна превышать 100 символов',
            'seazon.max' => 'Название сезона не должно превышать 50 символов',
            'group_name.max' => 'Групповое название не должно превышать 100 символов',
            'meta_title.max' => 'Мета-заголовок не должен превышать 255 символов',
            'meta_description.max' => 'Мета-описание не должно превышать 500 символов',
        ];
    }

    /**
     * Кастомные имена атрибутов для сообщений об ошибках
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'product_id' => 'уникальный ID товара',
            'name' => 'название товара',
            'brand' => 'бренд',
            'model' => 'модель',
            'seazon' => 'сезон',
            'group_name' => 'групповое название',
            'meta_title' => 'мета-заголовок',
            'meta_description' => 'мета-описание',
            'meta_keywords' => 'ключевые слова',
        ];
    }

    /**
     * Подготовка данных для валидации
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Очищаем и форматируем product_id
        if ($this->has('product_id')) {
            $productId = trim($this->input('product_id'));
            $this->merge(['product_id' => $productId]);
            
            Log::info('Product ID prepared for update validation', ['product_id' => $productId]);
        }
    }
}