<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function terminate(User $user, Employee $employee)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );
        return $ownedStoreIds->contains($employee->store->id);
    }


    public function makeActive(User $user, Employee $employee)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );
        return $ownedStoreIds->contains($employee->store->id);
    }

    public function makeInactive(User $user, Employee $employee)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );
        return $ownedStoreIds->contains($employee->store->id);
    }
}
