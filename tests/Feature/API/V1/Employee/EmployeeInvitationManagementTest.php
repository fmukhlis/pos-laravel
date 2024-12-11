<?php

namespace Tests\Feature\API\V1\Employee;

use App\Models\Employee;
use App\Models\EmployeeInvite;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployeeInvitationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owners_can_send_employee_invitations(): void
    {
        $user = User::factory()->premium()->create();

        $store = Store::factory()->for($user, 'owner')->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $prospectiveEmployees = User::factory()->count(5)->create();

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/invitations',
            [
                'userIds' => $prospectiveEmployees
                    ->map(fn($prospectiveEmployee) => (
                        $prospectiveEmployee->id
                    ))
                    ->all()
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
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

    public function test_guests_can_not_send_employee_invitations(): void
    {
        $owner = User::factory()->premium()->create();

        $store = Store::factory()->for($owner, 'owner')->create();

        $prospectiveEmployees = User::factory()->count(5)->create();

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/invitations',
            [
                'userIds' => $prospectiveEmployees
                    ->map(fn($prospectiveEmployee) => (
                        $prospectiveEmployee->id
                    ))
                    ->all()
            ]
        );

        $response->assertStatus(401);
    }

    public function test_store_owners_can_not_send_employee_invitations_for_stores_owned_by_other_store_owners(): void
    {
        $owner = User::factory()->premium()->create();

        $store = Store::factory()->for($owner, 'owner')->create();

        $user = User::factory()->premium()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $prospectiveEmployees = User::factory()->count(5)->create();

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/invitations',
            [
                'userIds' => $prospectiveEmployees
                    ->map(fn($prospectiveEmployee) => (
                        $prospectiveEmployee->id
                    ))
                    ->all()
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_store_owners_can_not_send_employee_invitations_to_employees_already_working_in_a_store(): void
    {
        $user = User::factory()->premium()->create();

        $employee = User::factory()->create();

        $store = Store::factory()
            ->has(Employee::factory()->for($employee, 'user'), 'employees')
            ->for($user, 'owner')
            ->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/invitations',
            [
                'userIds' => [$employee->id]
            ],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_store_owners_can_not_send_employee_invitations_with_invalid_data(): void
    {
        $user = User::factory()->premium()->create();

        $store = Store::factory()->for($user, 'owner')->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->postJson(
            '/api/v1/stores/' . $store->id . '/invitations',
            ['userIds' => [-1]],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(422);
    }

    public function test_store_owners_can_cancel_employee_invitations(): void
    {
        $user = User::factory()->premium()->create();

        $store  = Store::factory()
            ->for($user, 'owner')
            ->create();

        $prospectiveEmployees = User::factory()
            ->count(5)
            ->has(EmployeeInvite::factory()->for($store), 'invitations')
            ->create();

        $invitation = $prospectiveEmployees
            ->first()
            ->invitations()
            ->first();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->deleteJson(
            '/api/v1/stores/' . $store->id . '/invitations/' . $invitation->id,
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(204);
    }

    public function test_guests_can_not_cancel_employee_invitations(): void
    {
        $owner = User::factory()->premium()->create();

        $store  = Store::factory()
            ->for($owner, 'owner')
            ->create();

        $prospectiveEmployees = User::factory()
            ->count(5)
            ->has(EmployeeInvite::factory()->for($store), 'invitations')
            ->create();

        $invitation = $prospectiveEmployees
            ->first()
            ->invitations()
            ->first();

        $response = $this->deleteJson(
            '/api/v1/stores/' . $store->id . '/invitations/' . $invitation->id,
            [],
        );

        $response->assertStatus(401);
    }

    public function test_users_can_not_cancel_employee_invitations_for_other_users_stores(): void
    {
        $user = User::factory()->premium()->create();

        $owner = User::factory()->premium()->create();

        $store  = Store::factory()
            ->for($owner, 'owner')
            ->create();

        $prospectiveEmployees = User::factory()
            ->count(5)
            ->has(EmployeeInvite::factory()->for($store), 'invitations')
            ->create();

        $invitation = $prospectiveEmployees
            ->first()
            ->invitations()
            ->first();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->deleteJson(
            '/api/v1/stores/' . $store->id . '/invitations/' . $invitation->id,
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }
}
