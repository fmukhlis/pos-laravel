<?php

namespace Tests\Feature\API\V1\Store;

use App\Models\Employee;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployeeRetrievalTest extends TestCase
{
    // use RefreshDatabase;

    public function test_users_can_retrieve_all_employees(): void
    {
        $owner = User::factory()->premium()->create();
        $store = Store::factory()
            ->for($owner, 'owner')
            ->create();

        User::factory()->count(5)->create()->each(
            function ($user) use ($store) {
                Employee::factory()
                    ->for($user)
                    ->for($store)
                    ->create();
            }
        );

        $user = User::factory()->create(['email' => 'admin@example.com', 'password' => '12345678']);
        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/employees',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'fullName',
                        'status'
                    ]
                ]
            ]);
    }

    public function test_guests_can_not_retrieve_any_employees(): void
    {
        $owner = User::factory()->premium()->create();
        $store = Store::factory()->for($owner, 'owner')->create();

        $response = $this->getJson('/api/v1/stores/' . $store->id . '/employees');

        $response->assertStatus(401);
    }
}
