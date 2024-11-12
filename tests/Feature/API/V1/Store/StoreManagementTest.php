<?php

namespace Tests\Feature\API\V1\Store;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_update_their_store(): void
    {
        $user = User::factory()->premium()->create();
        $store = Store::factory()->for($user, 'owner')->create();
        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id,
            ['email' => 'weloveccb@example.com'],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'phone',
                    'email',
                    'address',
                    'createdAt',
                ]
            ]);
    }

    public function test_guests_can_not_update_any_store(): void
    {
        $owner = User::factory()->premium()->create();
        $store = Store::factory()->for($owner, 'owner')->create();

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id,
            ['email' => 'weloveccb@example.com'],
        );

        $response->assertStatus(401);
    }

    public function test_users_can_not_update_other_user_store(): void
    {
        $owner = User::factory()->premium()->create();
        $store = Store::factory()->for($owner, 'owner')->create();

        $user = User::factory()->premium()->create();
        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id,
            ['email' => 'weloveccb@example.com'],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_users_can_not_update_their_store_with_invalid_data(): void
    {
        $user = User::factory()->premium()->create();
        $store = Store::factory()->for($user, 'owner')->create();
        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id,
            ['email' => true],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }

    public function test_users_can_delete_their_store(): void
    {
        $user = User::factory()->premium()->create();
        $store = Store::factory()->for($user, 'owner')->create();
        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->deleteJson(
            '/api/v1/stores/' . $store->id,
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(204);
    }

    public function test_guests_can_not_delete_any_store(): void
    {
        $owner = User::factory()->premium()->create();
        $store = Store::factory()->for($owner, 'owner')->create();

        $response = $this->deleteJson(
            '/api/v1/stores/' . $store->id,
            []
        );

        $response->assertStatus(401);
    }

    public function test_users_can_not_delete_other_user_store(): void
    {
        $owner = User::factory()->premium()->create();
        $store = Store::factory()->for($owner, 'owner')->create();

        $user = User::factory()->premium()->create();
        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->deleteJson(
            '/api/v1/stores/' . $store->id,
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }
}
