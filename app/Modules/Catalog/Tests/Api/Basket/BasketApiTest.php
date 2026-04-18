<?php

namespace App\Modules\Catalog\Tests\Api\Basket;

use App\Modules\Catalog\Models\CatalogBasket;
use App\Modules\Catalog\Models\CatalogBasketItem;
use App\Modules\Catalog\Models\CatalogProductOffer;
use App\Modules\Catalog\Models\Customer;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BasketApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Customer $customer;

    protected CatalogProductOffer $offer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        $this->customer = Customer::create([
            'user_id' => $this->user->id,
        ]);

        $this->offer = CatalogProductOffer::create([
            'offer_id' => 1,
            'product_id' => 1,
            'name' => 'Test Offer',
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_add_to_basket(): void
    {
        $response = $this->postJson('/api/catalog/basket/add', [
            'offer_id' => $this->offer->offer_id,
            'quantity' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_add_offer_to_basket(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/catalog/basket/add', [
            'offer_id' => $this->offer->offer_id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'item' => [
                'id',
                'catalog_basket_id',
                'offer_id',
                'quantity',
            ],
            'basket' => [
                'id',
                'customer_id',
                'total_price',
                'total_quantity',
            ],
        ]);
        $response->assertJsonPath('item.quantity', 2);
    }

    public function test_user_can_update_existing_basket_item(): void
    {
        Sanctum::actingAs($this->user);

        $basket = CatalogBasket::create([
            'customer_id' => $this->customer->id,
            'total_price' => 0,
            'total_quantity' => 0,
        ]);

        CatalogBasketItem::create([
            'catalog_basket_id' => $basket->id,
            'offer_id' => $this->offer->offer_id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/catalog/basket/add', [
            'offer_id' => $this->offer->offer_id,
            'quantity' => 3,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Количество оффера обновлено в корзине.');
    }

    public function test_returns_404_when_customer_not_found(): void
    {
        $userWithoutCustomer = User::create([
            'name' => 'No Customer User',
            'email' => 'nocustomer@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        Sanctum::actingAs($userWithoutCustomer);

        $response = $this->postJson('/api/catalog/basket/add', [
            'offer_id' => $this->offer->offer_id,
            'quantity' => 1,
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('message', 'Клиент не найден для данного пользователя.');
    }

    public function test_validation_fails_with_invalid_offer_id(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/catalog/basket/add', [
            'offer_id' => 999999,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.offer_id.0', 'Выбранный оффер не найден.');
    }

    public function test_validation_fails_with_missing_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/catalog/basket/add', [
            'offer_id' => $this->offer->offer_id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.quantity.0', 'Количество обязательно.');
    }

    public function test_validation_fails_with_invalid_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/catalog/basket/add', [
            'offer_id' => $this->offer->offer_id,
            'quantity' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.quantity.0', 'Количество должно быть не менее 1.');
    }
}
