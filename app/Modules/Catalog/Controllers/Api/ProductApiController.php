<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Resources\ProductCollection;
use Illuminate\Http\Request;

class ProductApiController
{
    /**
     * Количество постов блога на странице по-умолчанию.
     */
    private const int PRODUCTS_PER_PAGE = 100;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProductCollection::make(
            Product::with([
                'catalogAttributes',
                'offers' => [
                    'prices',
                    'warehouseOffers',
                    'catalogAttributes'
                ],
            ])
                ->paginate($this::PRODUCTS_PER_PAGE)
        );
    }
}
