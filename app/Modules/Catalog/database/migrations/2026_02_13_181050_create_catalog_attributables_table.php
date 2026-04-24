<?php

use App\Modules\Catalog\Models\CatalogAttribute;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catalog_attributables', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CatalogAttribute::class, 'catalog_attribute_id')->comment('ID атрибута');
            $table->morphs('attributable');
            $table->string('value')->comment('Значение атрибута');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_attributables');
    }
};
