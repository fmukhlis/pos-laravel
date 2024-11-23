<?php

namespace Tests\Feature\API\V1\PaymentMethod;

use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SinglePaymentMethodRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_retrieve_single_payment_method(): void
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
            '/api/v1/stores/' . $store->id . '/payment-methods/' . $store->paymentMethods->first()->id,
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'destination'
                ]
            ]);
    }

    public function test_guests_cannot_retrieve_single_payment_method(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(PaymentMethod::factory()->count(5))
            ->create();

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/payment-methods/' . $store->paymentMethods->first()->id,
        );

        $response->assertStatus(401);
    }
}
