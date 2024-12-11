<?php

namespace Tests\Feature\API\V1\ProductCategory;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductCategoryCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owner_can_add_new_category_for_their_store(): void
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

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/product-categories',
            [
                'name' => 'Beverages',
                'productIds' => $store
                    ->products
                    ->map(fn($product) => (
                        $product->id
                    ))
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(201)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'productsCount'
                ]
            ]);
    }

    public function test_guest_cannot_add_new_product_category(): void
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

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/product-categories',
            [
                'name' => 'Beverages',
                'productIds' => $store
                    ->products
                    ->map(fn($product) => (
                        $product->id
                    ))
            ],
        );

        $response->assertStatus(401);
    }


    public function test_store_owner_cannot_add_new_category_for_store_owned_by_other_store_owner(): void
    {
        $otherStoreOwner = User::factory()
            ->premium()
            ->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
            ->has(
                Product::factory()
                    ->count(5)
            )
            ->has(
                ProductCategory::factory()
                    ->count(3)
            )
            ->create();

        $otherStore->productCategories()
            ->first()
            ->products()
            ->saveMany($otherStore->products);

        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores/' . $otherStore->id . '/product-categories',
            [
                'name' => 'Beverages',
                'productIds' => $otherStore
                    ->products
                    ->map(fn($product) => (
                        $product->id
                    ))
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_store_owner_cannot_add_new_category_for_their_store_with_invalid_data(): void
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

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/product-categories',
            [
                'name' => 'Beverages',
                'productIds' => []
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }
}
