<?php

namespace Tests\Feature\API\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class StoreCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_premium_users_can_create_store(): void
    {
        $user = User::factory()->premium()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores',
            [
                'name' => 'Coffee Cafe Boss',
                'phone' => '+6281234567890',
                'email' => 'ccb@example.com',
                'address' => 'Jl. Tuparev No.168, Komplek Kagum City Blok C no 15, Kertawinangun, Kedawung, Cirebon, West Java 45153'
            ],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(201);
    }

    public function test_guest_users_can_not_create_a_store(): void
    {
        $response = $this->postJson(
            '/api/v1/stores',
            [
                'name' => 'Coffee Cafe Boss',
                'phone' => '+6281234567890',
                'email' => 'ccb@example.com',
                'address' => 'Jl. Tuparev No.168, Komplek Kagum City Blok C no 15, Kertawinangun, Kedawung, Cirebon, West Java 45153'
            ]
        );

        $response->assertStatus(401);
    }

    public function test_free_users_can_not_create_store(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores',
            [
                'name' => 'Coffee Cafe Boss',
                'phone' => '+6281234567890',
                'email' => 'ccb@example.com',
                'address' => 'Jl. Tuparev No.168, Komplek Kagum City Blok C no 15, Kertawinangun, Kedawung, Cirebon, West Java 45153'
            ],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(403);
    }

    public function test_premium_users_can_not_create_a_store_with_invalid_data(): void
    {
        $user = User::factory()->premium()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores',
            [
                'name' => 'Coffee Cafe Boss',
            ],
            [
                'Authorization' => 'Bearer ' . $token
            ]
        );

        $response->assertStatus(422);
    }
}
