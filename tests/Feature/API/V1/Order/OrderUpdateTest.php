<?php

namespace Tests\Feature\API\V1\Order;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderProductVariant;
use App\Models\PaymentMethod;
use App\Models\Permission;
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

class OrderUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_modify_an_order_in_billing_status()
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(
                Permission::factory()
                    ->state(function (array $attributes) {
                        return [
                            'authorization_code' => '123456',
                            'refund' => false,
                            'modify_bill' => true
                        ];
                    })
            )
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
                ->create(['status' => 'Billed']);
        }

        $token = $employee
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id . '/orders/' . $store->orders()->first()->id,
            [
                'authorizationCode' => '123456',
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

        $response->assertStatus(200)
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

    public function test_guest_cannot_modify_an_order()
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(
                Permission::factory()
                    ->state(function (array $attributes) {
                        return [
                            'authorization_code' => '123456',
                            'refund' => false,
                            'modify_bill' => true
                        ];
                    })
            )
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
                ->create(['status' => 'Billed']);
        }

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id . '/orders/' . $store->orders()->first()->id,
            [
                'authorizationCode' => '123456',
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
    public function test_staff_cannot_modify_an_order_on_another_store()
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
                Permission::factory()
                    ->state(function (array $attributes) {
                        return [
                            'authorization_code' => '123456',
                            'refund' => false,
                            'modify_bill' => true
                        ];
                    })
            )
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


        for ($i = 0; $i < 3; $i++) {
            $productVariants = $otherStore
                ->productVariants()
                ->inRandomOrder()
                ->take(3)
                ->get();

            Order::factory()
                ->for($otherEmployee, 'user')
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
                ->create(['status' => 'Billed']);
        }

        $token = $employee
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->putJson(
            '/api/v1/stores/' . $otherStore->id . '/orders/' . $otherStore->orders()->first()->id,
            [
                'authorizationCode' => '123456',
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
                            function (Builder $query) use ($otherStore) {
                                $query->whereHas(
                                    'product',
                                    function (Builder $query) use ($otherStore) {
                                        $query->whereHas(
                                            'store',
                                            function (Builder $query) use ($otherStore) {
                                                $query->where('id', $otherStore->id);
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

    public function test_staff_cannot_modify_an_order_with_invalid_data()
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(
                Permission::factory()
                    ->state(function (array $attributes) {
                        return [
                            'authorization_code' => '123456',
                            'refund' => false,
                            'modify_bill' => true
                        ];
                    })
            )
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
                ->create(['status' => 'Billed']);
        }

        $token = $employee
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id . '/orders/' . $store->orders()->first()->id,
            [
                'authorizationCode' => '123456',
                'cashAmount' => 10000,
                'note' => 'Add extra sugar',
                'orderType' => 'Bawa Pulang',
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

        $response->assertStatus(422);
    }
}
