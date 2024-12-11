<?php

namespace Tests\Feature\API\V1\Product;

use App\Models\Product;
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

class ProductDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owner_can_delete_product_of_their_store(): void
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

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->deleteJson(
            '/api/v1/stores/' . $store->id . '/products/' . $store->products()->first()->id,
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(204);
    }

    public function test_guest_cannot_delete_product_of_a_store(): void
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

        $response = $this->deleteJson(
            '/api/v1/stores/' . $store->id . '/products/' . $store->products()->first()->id,
            [],
        );

        $response->assertStatus(401);
    }

    public function test_store_owner_cannot_delete_product_of_store_owned_by_other_store_owner(): void
    {
        $otherStoreOwner = User::factory()
            ->premium()
            ->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
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
            $otherStore
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
                ->for($otherStore->products()->first())
                ->create();

            $productVariants
                ->productOptions()
                ->sync($optionIdCombination);
        }

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

        $response = $this->deleteJson(
            '/api/v1/stores/' . $otherStore->id . '/products/' . $otherStore->products()->first()->id,
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }
}
