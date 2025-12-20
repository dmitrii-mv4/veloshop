<?php

namespace App\Modules\Role\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Проверяем, не существуют ли уже записи
        if (DB::table('role_has_permissions')->where('role_id', 1)->exists()) {
            $this->command->info('❌ Записи для роли 1 уже существуют');
            return;
        }

        $permissions = [];
        foreach (range(1, 11) as $permissionId) {
            $permissions[] = [
                'role_id' => 1,
                'permission_id' => $permissionId,
            ];
        }

        DB::table('role_has_permissions')->insert($permissions);
        
        $this->command->info('✅ Назначено 11 разрешений для роли администратора');
    }
}