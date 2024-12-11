<?php

namespace Tests\Feature\API\V1\Order;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderProductVariant;
use App\Models\PaymentMethod;
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

class MultipleOrderRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_retrieve_all_order_on_their_store(): void
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

        for ($i = 0; $i < 3; $i++) {
            $productVariants = $store
                ->productVariants()
                ->inRandomOrder()
                ->take(3)
                ->get();

            Order::factory()
                ->for($employee, 'user')
                ->for($store, 'store')
                ->for(
                    $store
                        ->paymentMethods()
                        ->orderByDesc('id')
                        ->first(),
                    'paymentMethod'
                )
                ->for($store->customers[$i], 'customer')
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[0],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[1],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[2],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->create();
        }

        $token = $employee
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/orders',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    '*' => [
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
                ]
            ]);
    }

    public function test_store_owner_can_retrieve_all_order_on_their_store(): void
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

        for ($i = 0; $i < 3; $i++) {
            $productVariants = $store
                ->productVariants()
                ->inRandomOrder()
                ->take(3)
                ->get();

            Order::factory()
                ->for($employee, 'user')
                ->for($store, 'store')
                ->for(
                    $store
                        ->paymentMethods()
                        ->orderByDesc('id')
                        ->first(),
                    'paymentMethod'
                )
                ->for($store->customers[$i], 'customer')
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[0],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[1],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[2],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->create();
        }

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/orders',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    '*' => [
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
                ]
            ]);
    }

    public function test_guest_cannot_retrieve_all_order_on_a_store(): void
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

        for ($i = 0; $i < 3; $i++) {
            $productVariants = $store
                ->productVariants()
                ->inRandomOrder()
                ->take(3)
                ->get();

            Order::factory()
                ->for($employee, 'user')
                ->for($store, 'store')
                ->for(
                    $store
                        ->paymentMethods()
                        ->orderByDesc('id')
                        ->first(),
                    'paymentMethod'
                )
                ->for($store->customers[$i], 'customer')
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[0],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[1],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[2],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->create();
        }

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/orders',
        );

        $response->assertStatus(401);
    }

    public function test_employee_cannot_retrieve_all_order_on_another_store(): void
    {
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

        for ($i = 0; $i < 3; $i++) {
            $productVariants = $otherStore
                ->productVariants()
                ->inRandomOrder()
                ->take(3)
                ->get();

            Order::factory()
                ->for($employee, 'user')
                ->for($otherStore, 'store')
                ->for(
                    $otherStore
                        ->paymentMethods()
                        ->orderByDesc('id')
                        ->first(),
                    'paymentMethod'
                )
                ->for($otherStore->customers[$i], 'customer')
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[0],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[1],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[2],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->create();
        }

        $token = $employee
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->getJson(
            '/api/v1/stores/' . $otherStore->id . '/orders',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_store_owner_cannot_retrieve_all_order_on_another_store(): void
    {
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

        for ($i = 0; $i < 3; $i++) {
            $productVariants = $otherStore
                ->productVariants()
                ->inRandomOrder()
                ->take(3)
                ->get();

            Order::factory()
                ->for($employee, 'user')
                ->for($otherStore, 'store')
                ->for(
                    $otherStore
                        ->paymentMethods()
                        ->orderByDesc('id')
                        ->first(),
                    'paymentMethod'
                )
                ->for($otherStore->customers[$i], 'customer')
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[0],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[1],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->has(
                    OrderProductVariant::factory()
                        ->for(
                            $productVariants[2],
                            'productVariant'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[0]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                        ->hasAttached(
                            $productVariants[0]
                                ->product
                                ->productModifierCategories[1]
                                ->productModifiers()
                                ->inRandomOrder()
                                ->first(),
                            [],
                            'productModifiers'
                        )
                )
                ->create();
        }

        $token = $employee
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->getJson(
            '/api/v1/stores/' . $otherStore->id . '/orders',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }
}
