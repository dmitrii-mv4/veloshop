<?php

namespace App\Admin\Database\Seeders;

use App\Admin\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function definition(): array
    {
        return [
            'id' => 1, 
            'name_site' => 'Мой сайт',
            'url_site' => '/',
            'description_site' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}