<?php

namespace App\Modules\Menu\Requests\MenuItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

/**
 * Валидация запроса на создание пункта меню
 * Обеспечивает целостность данных при создании пункта меню
 */
class StoreMenuItemRequest extends FormRequest
{
    /**
     * Определить авторизацию пользователя
     *
     * @return bool
     */
    public function authorize(): bool
    {
        Log::info('Проверка авторизации для создания пункта меню', ['user_id' => auth()->id()]);
        return auth()->check();
    }

    /**
     * Правила валидации для создания пункта меню
     *
     * @return array
     */
    public function rules(): array
    {
        $menu = $this->route('menu');
        $menuId = $menu->id;

        Log::debug('Формирование правил валидации для создания пункта меню', [
            'menu_id' => $menuId,
            'menu_name' => $menu->name
        ]);

        return [
            'title' => 'required|string|max:255',
            'url' => 'required|string|max:500',
            'icon' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:menu_items,id,menu_id,' . $menuId,
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
            'seo_title' => 'nullable|string|max:255'
        ];
    }

    /**
     * Сообщения об ошибках валидации
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Название пункта меню обязательно для заполнения',
            'title.max' => 'Название пункта меню не должно превышать 255 символов',
            'url.required' => 'URL адрес обязателен для заполнения',
            'url.max' => 'URL адрес не должен превышать 500 символов',
            'parent_id.exists' => 'Родительский пункт меню не найден или принадлежит другому меню',
            'order.integer' => 'Порядок сортировки должен быть целым числом',
            'order.min' => 'Порядок сортировки не может быть отрицательным',
            'is_active.boolean' => 'Статус активности должен быть true или false',
            'seo_title.max' => 'SEO заголовок не должен превышать 255 символов'
        ];
    }

    /**
     * Подготовка данных для валидации
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        Log::debug('Подготовка данных для валидации создания пункта меню', [
            'data' => $this->all(),
            'menu_id' => $this->route('menu')->id ?? null
        ]);
    }
}