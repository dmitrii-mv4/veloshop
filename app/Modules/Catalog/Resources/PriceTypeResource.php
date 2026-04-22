<?php

namespace App\Modules\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Класс ресурса типа цен.
 *
 * @property int $id
 * @property string $title Название типа цены
 * @property string $type Технический идентификатор типа
 * @property string $currency Валюта
 * @property bool $is_active Активен ли тип
 * @property int $sort_order Порядок сортировки
 */
class PriceTypeResource extends JsonResource
{
    /**
     * Преобразовывает ресурс в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'type'          => $this->type,
            'currency'      => $this->currency,
            'is_active'     => $this->is_active,
            'sort_order'    => $this->sort_order,
        ];
    }
}
