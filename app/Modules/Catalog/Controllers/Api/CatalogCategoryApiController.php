<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\Category;

class CategoryApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Category::all();
    }
}
