<?php

namespace App\Modules\Catalog\Tests\Unit;

use App\Modules\Catalog\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_product_model_exists(): void
    {
        $this->assertTrue(class_exists(Product::class));
    }

    public function test_product_has_correct_table(): void
    {
        $this->assertEquals('catalog_products', Product::getTableName());
    }

    public function test_product_has_fillable_attributes(): void
    {
        $product = new Product;
        $fillable = $product->getFillable();

        $this->assertContains('product_id', $fillable);
        $this->assertContains('category_id', $fillable);
        $this->assertContains('brand', $fillable);
        $this->assertContains('model', $fillable);
        $this->assertContains('name', $fillable);
    }

    public function test_product_has_timestamps(): void
    {
        $product = new Product;
        $this->assertTrue($product->timestamps);
    }
}
