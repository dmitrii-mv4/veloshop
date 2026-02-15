<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\Product;
use Illuminate\Http\Request;

class ProductApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): string
    {
        return Product::with(['attributes', 'offers'])->get()->toJson();
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
