<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\PriceType;
use App\Modules\Catalog\Resources\PriceTypeCollection;

class PriceTypeController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PriceTypeCollection::make(PriceType::all());
    }
}
