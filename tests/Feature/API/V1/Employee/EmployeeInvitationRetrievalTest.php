<?php

namespace Tests\Feature\API\V1\Employee;

use App\Models\EmployeeInvite;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployeeInvitationRetrievalTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_retrieve_their_incoming_invitations(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $owner = User::factory()->premium()->create();

        Store::factory()
            ->has(EmployeeInvite::factory()->for($user, 'invitee'))
            ->for($owner, 'owner')
            ->create();

        $response = $this->getJson(
            '/api/v1/profiles/' . $user->id . '/invitations',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'status',
                        'userName',
                        'storeName',
                        'invitedAt'
                    ]
                ]
            ]);
    }

    public function test_guests_can_not_retrieve_incoming_invitations(): void
    {
        $user = User::factory()->create();

        $owner = User::factory()->premium()->create();

        Store::factory()
            ->has(EmployeeInvite::factory()->for($user, 'invitee'))
            ->for($owner, 'owner')
            ->create();

        $response = $this->getJson(
            '/api/v1/profiles/' . $user->id . '/invitations'
        );

        $response->assertStatus(401);
    }

    public function test_users_can_not_retrieve_other_users_incoming_invitations(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $otherUser = User::factory()->create();

        $owner = User::factory()->premium()->create();

        Store::factory()
            ->has(EmployeeInvite::factory()->for($otherUser, 'invitee'))
            ->for($owner, 'owner')
            ->create();

        $response = $this->getJson(
            '/api/v1/profiles/' . $otherUser->id . '/invitations',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_users_can_retrieve_their_outgoing_invitations(): void
    {
        $otherUser = User::factory()->create();

        $user = User::factory()->premium()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $store = Store::factory()
            ->has(EmployeeInvite::factory()->for($otherUser, 'invitee'))
            ->for($user, 'owner')
            ->create();

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/invitations',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'status',
                        'userName',
                        'storeName',
                        'invitedAt'
                    ]
                ]
            ]);
    }

    public function test_guests_can_not_retrieve_outgoging_invitations(): void
    {
        $otherUser = User::factory()->create();

        $owner = User::factory()->premium()->create();

        $store = Store::factory()
            ->has(EmployeeInvite::factory()->for($otherUser, 'invitee'))
            ->for($owner, 'owner')
            ->create();

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/invitations',
        );

        $response->assertStatus(401);
    }

    public function test_users_can_not_retrieve_other_users_outgoing_invitations(): void
    {
        $otherUser = User::factory()->create();

        $user = User::factory()->premium()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $owner = User::factory()->premium()->create();

        $store = Store::factory()
            ->has(EmployeeInvite::factory()->for($otherUser, 'invitee'))
            ->for($owner, 'owner')
            ->create();

        $response = $this->getJson(
            '/api/v1/stores/' . $store->id . '/invitations',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }
}
