<?php

namespace Tests\Feature\API\V1\PaymentMethod;

use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MultiplePaymentMethodRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_retrieve_all_payment_methods(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(PaymentMethod::factory()->count(5))
            ->create();

        $user = User::factory()->create();
        $token = $user
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/payment-methods',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'destination'
                    ]
                ]
            ]);
    }

    public function test_guests_cannot_retrieve_all_payment_methods(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(PaymentMethod::factory()->count(5))
            ->create();

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/payment-methods',
        );

        $response->assertStatus(401);
    }
}
