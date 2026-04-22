<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Catalog\Casts\CustomerType as CustomerTypeCast;

class Customer extends Model
{
    use SoftDeletes, CustomerRelationsTrait;

    protected $table = 'catalog_customers';

    protected $fillable = [
        'user_id',
        'type_id',
    ];

    protected $casts = [
        'type_id' => CustomerTypeCast::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
