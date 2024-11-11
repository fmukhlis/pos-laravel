<?php

namespace Tests\Feature\API\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_login(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Device A'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    public function test_users_can_not_login_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'device_name' => 'Device A'
        ]);

        $response->assertStatus(422);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->postJson('/api/v1/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(204);
    }
}
