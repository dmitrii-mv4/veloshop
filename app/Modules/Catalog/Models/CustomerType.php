<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Catalog\Models\Customer;
use App\Modules\Catalog\Models\CustomerTrait;
use App\Modules\User\Models\User;

class CustomerType extends Model
{
    use SoftDeletes, CustomerTrait;

    protected $table = 'catalog_customers_type';

    protected $fillable = [
        'title',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}