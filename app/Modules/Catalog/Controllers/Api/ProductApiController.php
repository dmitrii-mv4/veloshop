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
            Product::with('offers')
                ->paginate($this::PRODUCTS_PER_PAGE)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
