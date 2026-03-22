<?php

namespace App\Modules\Menu\Requests\MenuItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Валидация запроса на обновление пункта меню
 * Обеспечивает целостность данных при обновлении пункта меню
 */
class UpdateMenuItemRequest extends FormRequest
{
    /**
     * Определить авторизацию пользователя
     *
     * @return bool
     */
    public function authorize(): bool
    {
        Log::info('Проверка авторизации для обновления пункта меню', ['user_id' => auth()->id()]);
        return auth()->check();
    }

    /**
     * Правила валидации для обновления пункта меню
     *
     * @return array
     */
    public function rules(): array
    {
        $menu = $this->route('menu');
        $menuId = $menu->id;
        
        $menuItem = $this->route('menuitem');
        $menuItemId = $menuItem->id;

        Log::debug('Формирование правил валидации для обновления пункта меню', [
            'menu_id' => $menuId,
            'menu_item_id' => $menuItemId
        ]);

        return [
            'title' => 'required|string|max:255',
            'url' => 'required|string|max:500',
            'icon' => 'nullable|string|max:100',
            'parent_id' => [
                'nullable',
                Rule::exists('menu_items', 'id')->where(function ($query) use ($menuId, $menuItemId) {
                    $query->where('menu_id', $menuId)
                        ->where('id', '!=', $menuItemId);
                })
            ],
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
            'parent_id.exists' => 'Родительский пункт меню не найден, принадлежит другому меню или является текущим пунктом',
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
        Log::debug('Подготовка данных для валидации обновления пункта меню', [
            'data' => $this->all(),
            'menu_id' => $this->route('menu')->id ?? null,
            'menu_item_id' => $this->route('menuitem')->id ?? null
        ]);
    }

    /**
     * Получить пользовательские атрибуты для формирования сообщений валидатора
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'title' => 'название пункта',
            'url' => 'URL адрес',
            'parent_id' => 'родительский пункт',
            'order' => 'порядок сортировки',
            'is_active' => 'активность',
            'seo_title' => 'SEO заголовок'
        ];
    }
}