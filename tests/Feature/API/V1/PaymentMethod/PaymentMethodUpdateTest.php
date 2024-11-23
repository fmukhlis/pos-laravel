<?php

namespace Tests\Feature\API\V1\PaymentMethod;

use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentMethodUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owners_can_update_payment_method_for_their_stores(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(PaymentMethod::factory()->count(3))
            ->create();

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id . '/payment-methods/' . $store->paymentMethods->first()->id,
            [
                'name' => 'Dana',
                'destination' => '6285158296631'
            ],
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

    public function test_guests_cannot_update_payment_method(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(PaymentMethod::factory()->count(3))
            ->create();

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id . '/payment-methods/' . $store->paymentMethods->first()->id,
            [
                'name' => 'Dana',
                'destination' => '6285158296631'
            ],
        );

        $response->assertStatus(401);
    }

    public function test_store_owners_cannot_update_payment_method_for_stores_owned_by_other_store_owners(): void
    {
        $otherStoreOwner = User::factory()
            ->premium()
            ->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
            ->has(PaymentMethod::factory()->count(3))
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

        $response = $this->putJson(
            '/api/v1/stores/' . $otherStore->id . '/payment-methods/' . $otherStore->paymentMethods->first()->id,
            [
                'name' => 'Dana',
                'destination' => '6285158296631'
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_store_owners_cannot_update_payment_method_for_their_stores_with_invalid_data(): void
    {
        $storeOwner = User::factory()
            ->premium()
            ->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->has(PaymentMethod::factory()->count(3))
            ->create();

        $token = $storeOwner
            ->createToken(
                'Device A',
                ['basic:full-access']
            )
            ->plainTextToken;

        $response = $this->putJson(
            '/api/v1/stores/' . $store->id . '/payment-methods/' . $store->paymentMethods->first()->id,
            [
                'destination' => 6285158296631
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }
}
