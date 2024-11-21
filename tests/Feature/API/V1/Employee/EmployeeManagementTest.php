<?php

namespace Tests\Feature\API\V1\Employee;

use App\Models\Employee;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_owners_can_terminate_their_employees(): void
    {
        $storeOwner = User::factory()->premium()->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(Employee::factory()->for($store, 'store'))
            ->create();

        $token = $storeOwner->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->patchJson(
            '/api/v1/stores/' . $store->id . '/employees/' . $employee->employee->id . '/terminate',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'userName',
                    'storeName',
                ]
            ]);
    }

    public function test_guests_can_not_terminate_employees(): void
    {
        $storeOwner = User::factory()->premium()->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(Employee::factory()->for($store, 'store'))
            ->create();

        $response = $this->patchJson(
            '/api/v1/stores/' . $store->id . '/employees/' . $employee->employee->id . '/terminate',
            [],
        );

        $response->assertStatus(401);
    }

    public function test_store_owners_can_not_terminate_employee_that_worked_in_other_users_stores(): void
    {
        $otherStoreOwner = User::factory()->premium()->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(Employee::factory()->for($otherStore, 'store'))
            ->create();

        $storeOwner = User::factory()->premium()->create();

        Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $token = $storeOwner->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->patchJson(
            '/api/v1/stores/' . $otherStore->id . '/employees/' . $employee->employee->id . '/terminate',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_users_can_not_terminate_employee_that_worked_in_other_users_stores(): void
    {
        $storeOwner = User::factory()->premium()->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(Employee::factory()->for($store, 'store'))
            ->create();

        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->patchJson(
            '/api/v1/stores/' . $store->id . '/employees/' . $employee->employee->id . '/terminate',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_store_owners_can_set_employees_to_active(): void
    {
        $storeOwner = User::factory()->premium()->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(
                Employee::factory()
                    ->state(['status' => 'Inactive'])
                    ->for($store, 'store')
            )
            ->create();

        $token = $storeOwner->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->patchJson(
            '/api/v1/stores/' . $store->id . '/employees/' . $employee->employee->id . '/make-active',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'userName',
                    'storeName',
                ]
            ]);
    }

    public function test_guests_cannot_set_employees_to_active(): void
    {
        $storeOwner = User::factory()->premium()->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(
                Employee::factory()
                    ->state(['status' => 'Inactive'])
                    ->for($store, 'store')
            )
            ->create();

        $response = $this->patchJson(
            '/api/v1/stores/' . $store->id . '/employees/' . $employee->employee->id . '/make-active',
            [],
        );

        $response->assertStatus(401);
    }

    public function test_store_owners_cannot_set_employees_of_other_stores_to_active(): void
    {
        $otherStoreOwner = User::factory()->premium()->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(
                Employee::factory()
                    ->state(['status' => 'Inactive'])
                    ->for($otherStore, 'store')
            )
            ->create();

        $storeOwner = User::factory()->premium()->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $token = $storeOwner->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->patchJson(
            '/api/v1/stores/' . $otherStore->id . '/employees/' . $employee->employee->id . '/make-active',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_users_owners_cannot_set_employees_of_other_stores_to_active(): void
    {
        $otherStoreOwner = User::factory()->premium()->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(
                Employee::factory()
                    ->state(['status' => 'Inactive'])
                    ->for($otherStore, 'store')
            )
            ->create();

        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->patchJson(
            '/api/v1/stores/' . $otherStore->id . '/employees/' . $employee->employee->id . '/make-active',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_store_owners_can_set_employees_to_inactive(): void
    {
        $storeOwner = User::factory()->premium()->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(
                Employee::factory()
                    ->state(['status' => 'Active'])
                    ->for($store, 'store')
            )
            ->create();

        $token = $storeOwner->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->patchJson(
            '/api/v1/stores/' . $store->id . '/employees/' . $employee->employee->id . '/make-inactive',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'userName',
                    'storeName',
                ]
            ]);
    }

    public function test_guests_cannot_set_employees_to_inactive(): void
    {
        $storeOwner = User::factory()->premium()->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(
                Employee::factory()
                    ->state(['status' => 'Active'])
                    ->for($store, 'store')
            )
            ->create();

        $response = $this->patchJson(
            '/api/v1/stores/' . $store->id . '/employees/' . $employee->employee->id . '/make-inactive',
            [],
        );

        $response->assertStatus(401);
    }

    public function test_store_owners_cannot_set_employees_of_other_stores_to_inactive(): void
    {
        $otherStoreOwner = User::factory()->premium()->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(
                Employee::factory()
                    ->state(['status' => 'Active'])
                    ->for($otherStore, 'store')
            )
            ->create();

        $storeOwner = User::factory()->premium()->create();

        $store = Store::factory()
            ->for($storeOwner, 'owner')
            ->create();

        $token = $storeOwner->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->patchJson(
            '/api/v1/stores/' . $otherStore->id . '/employees/' . $employee->employee->id . '/make-inactive',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }

    public function test_users_cannot_set_employees_of_other_stores_to_inactive(): void
    {
        $otherStoreOwner = User::factory()->premium()->create();

        $otherStore = Store::factory()
            ->for($otherStoreOwner, 'owner')
            ->create();

        $employee = User::factory()
            ->has(
                Employee::factory()
                    ->state(['status' => 'Active'])
                    ->for($otherStore, 'store')
            )
            ->create();

        $user = User::factory()->create();

        $token = $user->createToken('Device A', ['basic:full-access'])->plainTextToken;

        $response = $this->patchJson(
            '/api/v1/stores/' . $otherStore->id . '/employees/' . $employee->employee->id . '/make-inactive',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);
    }
}
