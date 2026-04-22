<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\CatalogAttribute;
use App\Modules\Catalog\Resources\CatalogFullAttributeCollection;
use Illuminate\Http\Request;

class CatalogAttributeApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CatalogFullAttributeCollection::make(CatalogAttribute::all());
    }
}
