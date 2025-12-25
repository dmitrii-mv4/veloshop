<?php

namespace App\Modules\Integrator\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integrator extends Model
{
    use HasFactory;

    protected $table = 'integrators';

    protected $fillable = [
        'name',
        'integration_description',
        'is_active',
        'config',
        'integration_type',
        'version',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
