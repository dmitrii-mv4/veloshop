<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\CatalogCategory;

class CatalogCategoryApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CatalogCategory::all();
    }
}
