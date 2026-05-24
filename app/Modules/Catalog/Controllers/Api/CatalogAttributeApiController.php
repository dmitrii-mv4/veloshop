<?php

namespace App\Modules\Catalog\Controllers\Api;

use App\Modules\Catalog\Models\Attribute;
use App\Modules\Catalog\Resources\FullAttributeCollection;
use Illuminate\Http\Request;

class AttributeApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return FullAttributeCollection::make(Attribute::all());
    }
}
