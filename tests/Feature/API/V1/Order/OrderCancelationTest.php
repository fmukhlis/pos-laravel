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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderCancelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_cancel_an_order_in_billing_status()
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

        $response = $this->patchJson(
            '/api/v1/stores/' . $store->id . '/orders/' . $store->orders()->first()->id . '/cancel',
            [
                'authorizationCode' => '123456'
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(204);
    }

    public function test_guest_cannot_cancel_an_order()
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

        $response = $this->patchJson(
            '/api/v1/stores/' . $store->id . '/orders/' . $store->orders()->first()->id . '/cancel',
            [
                'authorizationCode' => '123456'
            ]
        );

        $response->assertStatus(401);
    }

    public function test_staff_cannot_cancel_an_order_in_another_store()
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

        $response = $this->patchJson(
            '/api/v1/stores/' . $otherStore->id . '/orders/' . $otherStore->orders()->first()->id . '/cancel',
            [
                'authorizationCode' => '123456'
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }
}
