<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\PriceType;

class PriceTypeController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): string
    {
        return PriceType::all()->toJson();
    }
}
