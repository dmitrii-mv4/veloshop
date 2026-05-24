<?php

namespace App\Modules\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Класс ресурса атрибута товара.
 *
 * @property int $id
 */
class FullAttributeResource extends JsonResource
{
    /**
     * Преобразовывает ресурс в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'slug'      => $this->slug,
        ];
    }
}
