<?php

namespace Tests\Feature\API\V1\Employee;

use App\Models\EmployeeInvite;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployeeInvitationResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_accept_invitations(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $owner = User::factory()->premium()->create();

        Store::factory()
            ->for($owner, 'owner')
            ->has(EmployeeInvite::factory()->for($user, 'invitee'))
            ->create();

        $response = $this->patchJson(
            '/api/v1/profiles/' . $user->id . '/invitations/' . $user->invitations->first()->id . '/accept',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'message',
                'data' => [
                    'id',
                    'status',
                    'userName',
                    'storeName',
                    'invitedAt'
                ]
            ]);
    }

    public function test_guests_can_not_accept_invitations(): void
    {
        $user = User::factory()->create();

        $owner = User::factory()->premium()->create();

        Store::factory()
            ->for($owner, 'owner')
            ->has(EmployeeInvite::factory()->for($user, 'invitee'))
            ->create();

        $response = $this->patchJson(
            '/api/v1/profiles/' . $user->id . '/invitations/' . $user->invitations->first()->id . '/accept',
            [],
        );

        $response->assertStatus(401);
    }

    public function test_users_can_not_accept_other_users_invitations(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $otherUser = User::factory()->create();

        $owner = User::factory()->premium()->create();

        Store::factory()
            ->for($owner, 'owner')
            ->has(EmployeeInvite::factory()->for($otherUser, 'invitee'))
            ->create();

        $response = $this->patchJson(
            '/api/v1/profiles/' . $otherUser->id . '/invitations/' . $otherUser->invitations->first()->id . '/accept',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_users_can_decline_invitations(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $owner = User::factory()->premium()->create();

        Store::factory()
            ->for($owner, 'owner')
            ->has(EmployeeInvite::factory()->for($user, 'invitee'))
            ->create();

        $response = $this->patchJson(
            '/api/v1/profiles/' . $user->id . '/invitations/' . $user->invitations->first()->id . '/decline',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'message',
                'data' => [
                    'id',
                    'status',
                    'userName',
                    'storeName',
                    'invitedAt'
                ]
            ]);
    }

    public function test_guests_can_not_decline_invitations(): void
    {
        $user = User::factory()->create();

        $owner = User::factory()->premium()->create();

        Store::factory()
            ->for($owner, 'owner')
            ->has(EmployeeInvite::factory()->for($user, 'invitee'))
            ->create();

        $response = $this->patchJson(
            '/api/v1/profiles/' . $user->id . '/invitations/' . $user->invitations->first()->id . '/decline',
            [],
        );

        $response->assertStatus(401);
    }

    public function test_users_can_not_decline_other_users_invitations(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $otherUser = User::factory()->create();

        $owner = User::factory()->premium()->create();

        Store::factory()
            ->for($owner, 'owner')
            ->has(EmployeeInvite::factory()->for($otherUser, 'invitee'))
            ->create();

        $response = $this->patchJson(
            '/api/v1/profiles/' . $otherUser->id . '/invitations/' . $otherUser->invitations->first()->id . '/decline',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }
}
