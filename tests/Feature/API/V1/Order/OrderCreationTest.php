<?php

namespace Tests\Feature\API\V1\Order;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductModifier;
use App\Models\ProductModifierCategory;
use App\Models\ProductOption;
use App\Models\ProductOptionCategory;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_make_an_order()
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(
                PaymentMethod::factory()
                    ->count(3)
            )
            ->has(
                Customer::factory()
                    ->count(5)
            )
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
                    ->has(
                        ProductOptionCategory::factory()
                            ->has(
                                ProductOption::factory()
                                    ->count(3)
                            )
                            ->count(2)
                    )
            )
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
                    ->has(
                        ProductOptionCategory::factory()
                            ->has(
                                ProductOption::factory()
                                    ->count(3)
                            )
                            ->count(2)
                    )
            )
            ->create();

        foreach ($store->products as $product) {

            $optionIdCombinations = [[]];

            foreach (
                $product->productOptionCategories
                as $productOptionCategory
            ) {
                $newOptionIdCombinations = [];

                foreach ($optionIdCombinations as $optionIdCombination) {
                    foreach (
                        $productOptionCategory->productOptions
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
        }

        $employee = User::factory()
            ->has(Employee::factory()
                ->for($store, 'store'))
            ->create();

        $token = $employee
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/orders',
            [
                'cashAmount' => 10000,
                'note' => 'Add extra sugar',
                'orderType' => 'Dine In',
                'status' => 'Paid',
                'tableNumber' => 'T992',
                'customerId' => $store->customers()->first()->id,
                'paymentMethodId' => $store->paymentMethods()->first()->id,
                'orderedProducts' => [
                    [
                        'variantId' => $store->productVariants()->first()->id,
                        'modifierIds' => ProductModifier::whereHas(
                            'productModifierCategory',
                            function (Builder $query) use ($store) {
                                $query->whereHas(
                                    'product',
                                    function (Builder $query) use ($store) {
                                        $query->whereHas(
                                            'store',
                                            function (Builder $query) use ($store) {
                                                $query->where('id', $store->id);
                                            }
                                        );
                                    }
                                );
                            }
                        )
                            ->get()
                            ->map(fn($productModifier) => (
                                $productModifier->id
                            ))
                            ->only([0, 3])
                    ]
                ]
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(201)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'cashAmount',
                    'note',
                    'orderType',
                    'status',
                    'tableNumber',
                    'createdAt',
                    'orderedProducts' => [
                        '*' => [
                            'id',
                            'isCanceled',
                            'cancelReason',
                            'canceledAt',
                            'product' => [
                                'id',
                                'name',
                            ],
                            'variant' => [
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
                            ],
                            'selectedModifiers' => [
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

    public function test_guest_cannot_make_an_order()
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(
                PaymentMethod::factory()
                    ->count(3)
            )
            ->has(
                Customer::factory()
                    ->count(5)
            )
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
                    ->has(
                        ProductOptionCategory::factory()
                            ->has(
                                ProductOption::factory()
                                    ->count(3)
                            )
                            ->count(2)
                    )
            )
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
                    ->has(
                        ProductOptionCategory::factory()
                            ->has(
                                ProductOption::factory()
                                    ->count(3)
                            )
                            ->count(2)
                    )
            )
            ->create();

        foreach ($store->products as $product) {

            $optionIdCombinations = [[]];

            foreach (
                $product->productOptionCategories
                as $productOptionCategory
            ) {
                $newOptionIdCombinations = [];

                foreach ($optionIdCombinations as $optionIdCombination) {
                    foreach (
                        $productOptionCategory->productOptions
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
        }

        $employee = User::factory()
            ->has(Employee::factory()
                ->for($store, 'store'))
            ->create();

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/orders',
            [
                'cashAmount' => 10000,
                'note' => 'Add extra sugar',
                'orderType' => 'Dine In',
                'status' => 'Paid',
                'tableNumber' => 'T992',
                'customerId' => $store->customers()->first()->id,
                'paymentMethodId' => $store->paymentMethods()->first()->id,
                'orderedProducts' => [
                    [
                        'variantId' => $store->productVariants()->first()->id,
                        'modifierIds' => ProductModifier::whereHas(
                            'productModifierCategory',
                            function (Builder $query) use ($store) {
                                $query->whereHas(
                                    'product',
                                    function (Builder $query) use ($store) {
                                        $query->whereHas(
                                            'store',
                                            function (Builder $query) use ($store) {
                                                $query->where('id', $store->id);
                                            }
                                        );
                                    }
                                );
                            }
                        )
                            ->get()
                            ->map(fn($productModifier) => (
                                $productModifier->id
                            ))
                            ->only([0, 3])
                    ]
                ]
            ],
        );

        $response->assertStatus(401);
    }

    public function test_staff_cannot_make_an_order_on_another_store()
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(Employee::factory()
                ->for($store, 'store'))
            ->create();

        $otherStoreOwner = User::factory()
            ->premium()
            ->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
            ->has(
                PaymentMethod::factory()
                    ->count(3)
            )
            ->has(
                Customer::factory()
                    ->count(5)
            )
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
                    ->has(
                        ProductOptionCategory::factory()
                            ->has(
                                ProductOption::factory()
                                    ->count(3)
                            )
                            ->count(2)
                    )
            )
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
                    ->has(
                        ProductOptionCategory::factory()
                            ->has(
                                ProductOption::factory()
                                    ->count(3)
                            )
                            ->count(2)
                    )
            )
            ->create();

        foreach ($otherStore->products as $product) {

            $optionIdCombinations = [[]];

            foreach (
                $product->productOptionCategories
                as $productOptionCategory
            ) {
                $newOptionIdCombinations = [];

                foreach ($optionIdCombinations as $optionIdCombination) {
                    foreach (
                        $productOptionCategory->productOptions
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
        }

        $otherEmployee = User::factory()
            ->has(Employee::factory()
                ->for($otherStore, 'store'))
            ->create();

        $token = $employee
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores/' . $otherStore->id . '/orders',
            [
                'cashAmount' => 10000,
                'note' => 'Add extra sugar',
                'orderType' => 'Dine In',
                'status' => 'Paid',
                'tableNumber' => 'T992',
                'customerId' => $otherStore->customers()->first()->id,
                'paymentMethodId' => $otherStore->paymentMethods()->first()->id,
                'orderedProducts' => [
                    [
                        'variantId' => $otherStore->productVariants()->first()->id,
                        'modifierIds' => ProductModifier::whereHas(
                            'productModifierCategory',
                            function (Builder $query) use ($store) {
                                $query->whereHas(
                                    'product',
                                    function (Builder $query) use ($store) {
                                        $query->whereHas(
                                            'store',
                                            function (Builder $query) use ($store) {
                                                $query->where('id', $store->id);
                                            }
                                        );
                                    }
                                );
                            }
                        )
                            ->get()
                            ->map(fn($productModifier) => (
                                $productModifier->id
                            ))
                            ->only([0, 3])
                    ]
                ]
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_staff_cannot_make_an_order_with_invalid_data()
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(
                PaymentMethod::factory()
                    ->count(3)
            )
            ->has(
                Customer::factory()
                    ->count(5)
            )
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
                    ->has(
                        ProductOptionCategory::factory()
                            ->has(
                                ProductOption::factory()
                                    ->count(3)
                            )
                            ->count(2)
                    )
            )
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
                    ->has(
                        ProductOptionCategory::factory()
                            ->has(
                                ProductOption::factory()
                                    ->count(3)
                            )
                            ->count(2)
                    )
            )
            ->create();

        foreach ($store->products as $product) {

            $optionIdCombinations = [[]];

            foreach (
                $product->productOptionCategories
                as $productOptionCategory
            ) {
                $newOptionIdCombinations = [];

                foreach ($optionIdCombinations as $optionIdCombination) {
                    foreach (
                        $productOptionCategory->productOptions
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
        }

        $employee = User::factory()
            ->has(Employee::factory()
                ->for($store, 'store'))
            ->create();

        $token = $employee
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/orders',
            [
                'cashAmount' => 10000,
                'note' => 'Add extra sugar',
                'orderType' => 'Bawa Pulang',
                'status' => 'Paid',
                'tableNumber' => 'T992',
                'customerId' => $store->customers()->first()->id,
                'paymentMethodId' => $store->paymentMethods()->first()->id,
                'orderedProducts' => [1, 2]
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }
}
