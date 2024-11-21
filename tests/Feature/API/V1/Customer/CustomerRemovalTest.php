<?php

namespace Tests\Feature\API\V1\Customer;

use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owners_can_remove_their_customers(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(Customer::factory()->count(5))
            ->create();

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )->plainTextToken;

        $response = $this->deleteJson(
            '/api/v1/stores/' . $store->id . '/customers/' . $store->customers()->first()->id,
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(204);
    }

    public function test_guests_cannot_remove_customers(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(Customer::factory()->count(5))
            ->create();

        $response = $this->deleteJson(
            '/api/v1/stores/' . $store->id . '/customers/' . $store->customers->first()->id,
            [],
        );

        $response->assertStatus(401);
    }

    public function test_store_owners_cannot_remove_customers_on_stores_owned_by_other_users(): void
    {
        $otherStoreOwner = User::factory()
            ->premium()
            ->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
            ->has(Customer::factory()->count(5))
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
            )->plainTextToken;

        $response = $this->deleteJson(
            '/api/v1/stores/' . $otherStore->id . '/customers/' . $otherStore->customers->first()->id,
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }
}
