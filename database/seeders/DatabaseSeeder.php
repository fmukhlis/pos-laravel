<?php

namespace Database\Seeders;

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
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $storeOwner = User::factory()->premium()->create([
            'full_name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => '12345678',
        ]);

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
                Permission::factory()
                    ->state(function (array $attributes) {
                        return [
                            'authorization_code' => '654321',
                            'refund' => true,
                            'modify_bill' => false
                        ];
                    })
            )
            ->has(PaymentMethod::factory()
                ->count(3))
            ->has(Customer::factory()
                ->count(10))
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

        User::factory()
            ->has(Employee::factory()
                ->for($store, 'store'))
            ->create([
                'email' => 'employee@example.com',
                'password' => '12345678',
            ]);

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

        for ($i = 0; $i < 10; $i++) {
            $productVariants = $store
                ->productVariants()
                ->inRandomOrder()
                ->take(3)
                ->get();

            Order::factory()
                ->for($storeOwner, 'user')
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
    }
}
