<?php

namespace Tests\Feature\API\V1\Store;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_retrieve_a_store(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $store = Store::factory()->for($user, 'owner')->create();

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id,
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
                    'owner' => [
                        'id',
                        'fullName',
                        'email',
                    ]
                ]
            ]);
    }

    public function test_guests_can_not_retrieve_a_store(): void
    {
        $user = User::factory()->create();

        $store = Store::factory()->for($user, 'owner')->create();

        $response = $this->getJson('/api/v1/stores/' . $store->id);

        $response->assertStatus(401);
    }
}
