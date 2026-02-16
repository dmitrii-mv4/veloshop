<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\CatalogTypePrice;
use App\Modules\Catalog\Models\CatalogWarehouse;
use Illuminate\Http\Request;

class CatalogWarehouseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): string
    {
        return CatalogWarehouse::all()->toJson();
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
