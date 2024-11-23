<?php

namespace Tests\Feature\API\V1\Permission;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PermissionCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owners_can_add_new_permission_for_their_stores(): void
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
            '/api/v1/stores/' . $store->id . '/permissions',
            [
                'authorizationCode' => '123456',
                'refund' => true,
                'modifyBill' => true
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(201)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'authorizationCode',
                    'refund',
                    'modifyBill'
                ]
            ]);
    }

    public function test_guests_cannot_add_new_permission(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/permissions',
            [
                'authorizationCode' => '123456',
                'refund' => true,
                'modifyBill' => true
            ],
        );

        $response->assertStatus(401);
    }

    public function test_store_owners_cannot_add_new_permission_for_stores_owned_by_other_store_owners(): void
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
            '/api/v1/stores/' . $otherStore->id . '/permissions',
            [
                'authorizationCode' => '123456',
                'refund' => true,
                'modifyBill' => true
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_store_owners_cannot_add_new_permission_for_their_stores_with_invalid_data(): void
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
            '/api/v1/stores/' . $store->id . '/permissions',
            [
                'authorizationCode' => 123456,
                'refund' => 'true',
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }
}
