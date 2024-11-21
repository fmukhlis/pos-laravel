<?php

namespace Tests\Feature\API\V1\Customer;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerAdditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owners_can_create_new_customer_for_their_stores(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $token = $storeOwner
            ->createToken('Device A', ['basic:full-access'])
            ->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/customers',
            [
                'name' => 'Alice',
                'phone' => '6281234567890',
                'email' => 'alice@example.com',
                'gender' => 'Male'
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(201)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'phone',
                    'email',
                    'gender',
                    'monthlySpending',
                    'annuallySpending',
                    'allTimeSpending',
                ]
            ]);
    }

    public function test_guests_cannot_create_new_customer(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/customers',
            [
                'name' => 'Alice',
                'phone' => '6281234567890',
                'email' => 'alice@example.com',
                'gender' => 'Male'
            ],
        );

        $response->assertStatus(401);
    }


    public function test_store_owners_cannot_create_new_customer_for_stores_owned_by_other_store_owners(): void
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
            ->createToken('Device A', ['basic:full-access'])
            ->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores/' . $otherStore->id . '/customers',
            [
                'name' => 'Alice',
                'phone' => '6281234567890',
                'email' => 'alice@example.com',
                'gender' => 'Male'
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_store_owners_cannot_create_new_customer_for_their_stores_with_invalid_data(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $token = $storeOwner
            ->createToken('Device A', ['basic:full-access'])
            ->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/customers',
            [
                'name' => 'Alice',
                'phone' => 6281234567890,
                'email' => 'alice@example.com',
                'gender' => 'Unknown'
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }
}
