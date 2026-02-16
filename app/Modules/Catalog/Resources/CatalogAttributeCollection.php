<?php

namespace App\Modules\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Коллекция офферов.
 */
class CatalogAttributeCollection extends ResourceCollection
{
    /**
     * Преобразует коллекцию ресурсов в массив.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
