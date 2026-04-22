<?php

use App\Modules\User\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->comment('ID пользователя (владелец профиля покупателя)');
            $table->unsignedTinyInteger('type_id')->default(0)->comment('ID типа покупателя');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_customers');
    }
};
