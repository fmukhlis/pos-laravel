<?php

namespace Tests\Feature\API\V1\Customer;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_retrieve_all_customers(): void
    {
        $user = User::factory()
            ->create();

        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(Customer::factory()->count(5))
            ->create();

        $token = $user
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/customers',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'phone',
                        'email',
                        'gender',
                        'monthlySpending',
                        'annuallySpending',
                        'allTimeSpending',
                    ]
                ]
            ]);
    }

    public function test_guests_cannot_retrieve_all_customers(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(Customer::factory()->count(5))
            ->create();

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/customers',
        );

        $response->assertStatus(401);
    }

    public function test_users_can_retrieve_single_customers(): void
    {
        $user = User::factory()
            ->create();

        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(Customer::factory()->count(5))
            ->create();

        $token = $user
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/customers/' . $store->customers->first()->id,
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
                    'allTimeSpending',
                ]
            ]);
    }

    public function test_guests_cannot_retrieve_single_customers(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(Customer::factory()->count(5))
            ->create();

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/customers/' . $store->customers->first()->id,
        );

        $response->assertStatus(401);
    }
}
