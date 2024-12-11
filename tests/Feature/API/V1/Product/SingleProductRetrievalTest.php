<?php

namespace Tests\Feature\API\V1\Product;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductModifier;
use App\Models\ProductModifierCategory;
use App\Models\ProductOption;
use App\Models\ProductOptionCategory;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SingleProductRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_retrieve_single_product_on_a_store(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(ProductCategory::factory())
            ->has(
                Product::factory()
                    ->has(
                        ProductModifierCategory::factory()
                            ->has(
                                ProductModifier::factory()
                                    ->count(3)
                            )
                            ->count(2)
                    )
                    ->has(ProductOptionCategory::factory()
                        ->has(
                            ProductOption::factory()
                                ->count(3)
                        )
                        ->count(2))
            )
            ->create();

        $store->productCategories()
            ->first()
            ->products()
            ->save(
                $store->products()->first()
            );

        $optionIdCombinations = [[]];

        foreach (
            $store
                ->products()
                ->first()
                ->productOptionCategories
            as $productOptionCategory
        ) {
            $newOptionIdCombinations = [];

            foreach ($optionIdCombinations as $optionIdCombination) {
                foreach (
                    $productOptionCategory
                        ->productOptions
                    as $productOption
                ) {
                    $newOptionIdCombinations[] = array_merge(
                        $optionIdCombination,
                        [$productOption->id]
                    );
                }
            }

            $optionIdCombinations = $newOptionIdCombinations;
        }

        foreach ($optionIdCombinations as $optionIdCombination) {
            $productVariants = ProductVariant::factory()
                ->for($store->products()->first())
                ->create();

            $productVariants
                ->productOptions()
                ->sync($optionIdCombination);
        }

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/products/' . $store->products()->first()->id,
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'category' => [
                        'id',
                        'name',
                        'productsCount'
                    ],
                    'availableModifiers' => [
                        '*' => [
                            'id',
                            'name',
                            'status',
                            'values' => [
                                '*' => [
                                    'id',
                                    'name',
                                    'status'
                                ]
                            ]
                        ]
                    ],
                    'availableOptions' => [
                        '*' => [
                            'id',
                            'name',
                            'status',
                            'values' => [
                                '*' => [
                                    'id',
                                    'name',
                                    'status'
                                ]
                            ]
                        ]
                    ],
                    'availableVariants' => [
                        '*' => [
                            'id',
                            'price',
                            'stock',
                            'sku',
                            'status',
                            'productOptions' => [
                                '*' => [
                                    'id',
                                    'name',
                                    'status'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_guest_cannot_retrieve_single_products(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(
                Product::factory()
                    ->has(
                        ProductModifierCategory::factory()
                            ->has(
                                ProductModifier::factory()
                                    ->count(3)
                            )
                            ->count(2)
                    )
                    ->has(ProductOptionCategory::factory()
                        ->has(
                            ProductOption::factory()
                                ->count(3)
                        )
                        ->count(2))
            )
            ->create();

        $optionIdCombinations = [[]];

        foreach (
            $store
                ->products()
                ->first()
                ->productOptionCategories
            as $productOptionCategory
        ) {
            $newOptionIdCombinations = [];

            foreach ($optionIdCombinations as $optionIdCombination) {
                foreach (
                    $productOptionCategory
                        ->productOptions
                    as $productOption
                ) {
                    $newOptionIdCombinations[] = array_merge(
                        $optionIdCombination,
                        [$productOption->id]
                    );
                }
            }

            $optionIdCombinations = $newOptionIdCombinations;
        }

        foreach ($optionIdCombinations as $optionIdCombination) {
            $productVariants = ProductVariant::factory()
                ->for($store->products()->first())
                ->create();

            $productVariants
                ->productOptions()
                ->sync($optionIdCombination);
        }

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/products/' . $store->products()->first()->id,
        );

        $response->assertStatus(401);
    }
}
