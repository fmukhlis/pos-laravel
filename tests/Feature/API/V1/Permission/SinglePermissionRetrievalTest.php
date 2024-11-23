<?php

namespace Tests\Feature\API\V1\Permission;

use App\Models\Permission;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SinglePermissionRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owners_can_retrieve_single_permission_for_their_stores(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(Permission::factory()->count(3))
            ->create();

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/permissions/' . $store->permissions->first()->id,
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'authorizationCode',
                    'refund',
                    'modifyBill'
                ]
            ]);
    }

    public function test_guests_cannot_retrieve_single_permission(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(Permission::factory()->count(3))
            ->create();

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/permissions/' . $store->permissions->first()->id,
        );

        $response->assertStatus(401);
    }

    public function test_store_owners_cannot_retrieve_single_permission_for_stores_owned_by_other_store_owners(): void
    {
        $otherStoreOwner = User::factory()
            ->premium()
            ->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
            ->has(Permission::factory()->count(3))
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

        $response = $this->getJson(
            '/api/v1/stores/' . $otherStore->id . '/permissions/' . $otherStore->permissions->first()->id,
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }
}
