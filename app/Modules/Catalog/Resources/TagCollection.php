<?php

namespace App\Modules\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Класс коллекции тегов.
 */
class TagCollection extends ResourceCollection
{
    /**
     * Ресурс, используемый для элементов коллекции.
     *
     * @var string
     */
    public $collects = TagResource::class;
}
