<?php

namespace App\Policies;

use App\Models\EmployeeInvite;
use App\Models\Store;
use App\Models\User;

class EmployeeInvitePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAnyIncoming(User $user, User $invitee)
    {
        return $user->id === $invitee->id;
    }

    public function viewAnyOutgoing(User $user, Store $store)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($store->id);
    }

    public function create(User $user, Store $store, array $prospectiveEmployeeIds)
    {
        foreach ($prospectiveEmployeeIds as $prospectiveEmployeeId) {
            $prospectiveEmployee = User::find($prospectiveEmployeeId);
            if ($prospectiveEmployee->employee) {
                if ($prospectiveEmployee->employee->store) {
                    return false;
                }
            }
        }

        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($store->id);
    }

    public function accept(User $user, EmployeeInvite $employeeInvite)
    {
        return $user->id === $employeeInvite->invitee->id;
    }

    public function decline(User $user, EmployeeInvite $employeeInvite)
    {
        return $user->id === $employeeInvite->invitee->id;
    }

    public function delete(User $user, EmployeeInvite $employeeInvite)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($employeeInvite->store->id);
    }
}
