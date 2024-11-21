<?php

namespace Tests\Feature\API\V1\Customer;

use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owners_can_update_their_customers(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(Customer::factory())
            ->create();

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id . '/customers/' . $store->customers->first()->id,
            [
                'name' => 'Alice',
                'phone' => '6281234567890',
                'email' => 'alice@example.com',
                'gender' => 'Male'
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'phone',
                    'email',
                    'gender',
                    'monthlySpending',
                    'annuallySpending',
                    'allTimeSpending'
                ]
            ]);
    }

    public function test_guests_cannot_update_customers(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(Customer::factory())
            ->create();

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id . '/customers/' . $store->customers->first()->id,
            [
                'name' => 'Alice',
                'phone' => '6281234567890',
                'email' => 'alice@example.com',
                'gender' => 'Male'
            ],
        );

        $response->assertStatus(401);
    }

    public function test_store_owners_cannot_update_customers_for_stores_owned_by_other_store_owners(): void
    {
        $otherStoreOwners = User::factory()
            ->premium()
            ->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwners, 'owner')
            ->has(Customer::factory())
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

        $response = $this->putJson(
            '/api/v1/stores/' . $otherStore->id . '/customers/' . $otherStore->customers->first()->id,
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

    public function test_store_owners_cannot_update_their_customers_with_invalid_data(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(Customer::factory())
            ->create();

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id . '/customers/' . $store->customers->first()->id,
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
