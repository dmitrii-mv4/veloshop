<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');

            $table->index('role_id', 'role_has_permissions_role_id_index');
            $table->index('permission_id', 'role_has_permissions_permission_id_index');

            $table->foreign('role_id', 'role_has_permissions_role_id_foreign')->references('id')->on('roles');
            $table->foreign('permission_id', 'role_has_permissions_permission_id_foreign')->references('id')->on('permissions');

            $table->timestamps();
        });

        // DB::table('role_has_permissions')->insert([
        // [
        //     'role_id' => '1',
        //     'permission_id' => '1',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ],
        // [
        //     'role_id' => '1',
        //     'permission_id' => '2',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ],
        // [
        //     'role_id' => '1',
        //     'permission_id' => '3',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ],
        // [
        //     'role_id' => '1',
        //     'permission_id' => '4',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ],
        // [
        //     'role_id' => '1',
        //     'permission_id' => '5',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ],
        // [
        //     'role_id' => '1',
        //     'permission_id' => '6',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ],
        // [
        //     'role_id' => '1',
        //     'permission_id' => '7',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ],
        // [
        //     'role_id' => '1',
        //     'permission_id' => '8',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ],
        // [
        //     'role_id' => '1',
        //     'permission_id' => '9',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ],
        // [
        //     'role_id' => '1',
        //     'permission_id' => '10',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ],
        // [
        //     'role_id' => '1',
        //     'permission_id' => '11',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ],
        // ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
    }
};
