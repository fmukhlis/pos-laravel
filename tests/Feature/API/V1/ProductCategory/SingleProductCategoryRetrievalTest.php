<?php

namespace Tests\Feature\API\V1\ProductCategory;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SingleProductCategoryRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_retrieve_single_product_category_of_a_store(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(
                Product::factory()
                    ->count(5)
            )
            ->has(
                ProductCategory::factory()
                    ->count(3)
            )
            ->create();

        $store->productCategories()
            ->first()
            ->products()
            ->saveMany($store->products);

        $user = User::factory()->create(['password' => '12345678']);
        $token = $user
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/product-categories/' . $store->productCategories->first()->id,
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'products' => [
                        '*' => [
                            'id',
                            'name'
                        ]
                    ]
                ]
            ]);
    }

    public function test_guests_cannot_retrieve_single_product_category(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(
                Product::factory()
                    ->count(5)
            )
            ->has(
                ProductCategory::factory()
                    ->count(3)
            )
            ->create();

        $store->productCategories()
            ->first()
            ->products()
            ->saveMany($store->products);

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/product-categories/' . $store->productCategories->first()->id,
        );

        $response->assertStatus(401);
    }
}
