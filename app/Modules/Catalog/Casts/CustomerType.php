<?php declare(strict_types=1);

namespace App\Modules\Catalog\Casts;

use App\Modules\Catalog\Enums\CustomerType as CustomerTypeEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Преобразователь типа покупателя.
 */
class CustomerType implements CastsAttributes
{
    /**
     * Приводит значение к типу.
     *
     * @param Model $model Модель
     * @param string $key Ключ атрибута
     * @param mixed $value Значение атрибута
     * @param array $attributes Атрибуты
     * @return null|CustomerTypeEnum
     */
    public function get(
        Model $model,
        string $key,
        mixed $value,
        array $attributes
    ): ?CustomerTypeEnum
    {
        if (!is_null($value)) {
            return CustomerTypeEnum::from((int) $value);
        }

        return null;
    }

    /**
     * Подготавливает значение для сохранения в БД.
     *
     * @param Model $model Модель
     * @param string $key Ключ атрибута
     * @param mixed $value Значение атрибута
     * @param array $attributes Атрибуты
     * @return array
     */
    public function set(
        Model $model,
        string $key,
        mixed $value,
        array $attributes
    ): array
    {
        return [
            'object_type' => (string) ($value instanceof CustomerTypeEnum) ? $value->value : $value,
        ];
    }
}
