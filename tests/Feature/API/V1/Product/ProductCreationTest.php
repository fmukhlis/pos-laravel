<?php

namespace Tests\Feature\API\V1\Product;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owner_can_add_new_product_to_their_store(): void
    {
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
            '/api/v1/stores/' . $store->id . '/products',
            [
                'name' => 'Iced Tea',
                'availableModifiers' => [
                    [
                        'categoryName' => 'Sugar Level',
                        'values' => [
                            ['name' => 'No Sugar'],
                            ['name' => 'Low Sugar'],
                            ['name' => 'Regular Sugar'],
                            ['name' => 'Extra Sugar']
                        ]
                    ],
                    [
                        'categoryName' => 'Tea Strength',
                        'values' => [
                            ['name' => 'Light Brew'],
                            ['name' => 'Regular Brew'],
                            ['name' => 'Strong Brew']
                        ]
                    ]
                ],
                'availableOptions' => [
                    [
                        'categoryName' => 'Size',
                        'values' => [
                            ['name' => 'S'],
                            ['name' => 'L']
                        ]
                    ],
                    [
                        'categoryName' => 'Tea Base',
                        'values' => [
                            ['name' => 'Black Tea'],
                            ['name' => 'Green Tea']
                        ]
                    ]
                ],
                'availableVariants' => [
                    [
                        'stock' => 100,
                        'price' => 5000,
                        'sku' => 'BVG001',
                        'options' => ['S', 'Black Tea']
                    ],
                    [
                        'stock' => 100,
                        'price' => 7000,
                        'sku' => 'BVG002',
                        'options' => ['L', 'Black Tea']
                    ],
                    [
                        'stock' => 100,
                        'price' => 9000,
                        'sku' => 'BVG003',
                        'options' => ['S', 'Green Tea']
                    ],
                    [
                        'stock' => 100,
                        'price' => 11000,
                        'sku' => 'BVG004',
                        'options' => ['L', 'Green Tea']
                    ],
                ]
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(201)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'name',
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

    public function test_guest_cannot_add_new_product_to_a_store(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/products',
            [
                'name' => 'Iced Tea',
                'availableModifiers' => [
                    [
                        'categoryName' => 'Sugar Level',
                        'values' => [
                            ['name' => 'No Sugar'],
                            ['name' => 'Low Sugar'],
                            ['name' => 'Regular Sugar'],
                            ['name' => 'Extra Sugar']
                        ]
                    ],
                    [
                        'categoryName' => 'Tea Strength',
                        'values' => [
                            ['name' => 'Light Brew'],
                            ['name' => 'Regular Brew'],
                            ['name' => 'Strong Brew']
                        ]
                    ]
                ],
                'availableOptions' => [
                    [
                        'categoryName' => 'Size',
                        'values' => [
                            ['name' => 'S'],
                            ['name' => 'L']
                        ]
                    ],
                    [
                        'categoryName' => 'Tea Base',
                        'values' => [
                            ['name' => 'Black Tea'],
                            ['name' => 'Green Tea']
                        ]
                    ]
                ],
                'availableVariants' => [
                    [
                        'stock' => 100,
                        'price' => 5000,
                        'sku' => 'BVG001',
                        'options' => ['S', 'Black Tea']
                    ],
                    [
                        'stock' => 100,
                        'price' => 7000,
                        'sku' => 'BVG002',
                        'options' => ['L', 'Black Tea']
                    ],
                    [
                        'stock' => 100,
                        'price' => 9000,
                        'sku' => 'BVG003',
                        'options' => ['S', 'Green Tea']
                    ],
                    [
                        'stock' => 100,
                        'price' => 11000,
                        'sku' => 'BVG004',
                        'options' => ['L', 'Green Tea']
                    ],
                ]
            ],
        );

        $response->assertStatus(401);
    }

    public function test_store_owner_cannot_add_new_product_to_store_owned_by_other_store_owner(): void
    {
        $otherStoreOwner = User::factory()
            ->premium()
            ->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
            ->create();

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
            '/api/v1/stores/' . $otherStore->id . '/products',
            [
                'name' => 'Iced Tea',
                'availableModifiers' => [
                    [
                        'categoryName' => 'Sugar Level',
                        'values' => [
                            ['name' => 'No Sugar'],
                            ['name' => 'Low Sugar'],
                            ['name' => 'Regular Sugar'],
                            ['name' => 'Extra Sugar']
                        ]
                    ],
                    [
                        'categoryName' => 'Tea Strength',
                        'values' => [
                            ['name' => 'Light Brew'],
                            ['name' => 'Regular Brew'],
                            ['name' => 'Strong Brew']
                        ]
                    ]
                ],
                'availableOptions' => [
                    [
                        'categoryName' => 'Size',
                        'values' => [
                            ['name' => 'S'],
                            ['name' => 'L']
                        ]
                    ],
                    [
                        'categoryName' => 'Tea Base',
                        'values' => [
                            ['name' => 'Black Tea'],
                            ['name' => 'Green Tea']
                        ]
                    ]
                ],
                'availableVariants' => [
                    [
                        'stock' => 100,
                        'price' => 5000,
                        'sku' => 'BVG001',
                        'options' => ['S', 'Black Tea']
                    ],
                    [
                        'stock' => 100,
                        'price' => 7000,
                        'sku' => 'BVG002',
                        'options' => ['L', 'Black Tea']
                    ],
                    [
                        'stock' => 100,
                        'price' => 9000,
                        'sku' => 'BVG003',
                        'options' => ['S', 'Green Tea']
                    ],
                    [
                        'stock' => 100,
                        'price' => 11000,
                        'sku' => 'BVG004',
                        'options' => ['L', 'Green Tea']
                    ],
                ]
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_store_owner_cannot_add_new_product_to_their_store_with_invalid_data(): void
    {
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
            '/api/v1/stores/' . $store->id . '/products',
            [
                'name' => '',
                'availableModifiers' => [
                    [
                        'categoryName' => '',
                        'values' => [
                            ['name' => ''],
                        ]
                    ],
                    [
                        'categoryName' => 'Tea Strength',
                        'values' => []
                    ]
                ],
                'availableOptions' => [
                    [
                        'categoryName' => '',
                        'values' => [
                            ['name' => ''],
                        ]
                    ],
                    [
                        'categoryName' => 'Tea Base',
                        'values' => []
                    ]
                ],
                'availableVariants' => [
                    [
                        'stock' => 0,
                        'price' => 0,
                        'sku' => 0,
                        'options' => ['']
                    ],
                ]
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }
}
