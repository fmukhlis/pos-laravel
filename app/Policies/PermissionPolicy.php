<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\Store;
use App\Models\User;

class PermissionPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user, Store $store)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($store->id);
    }

    public function view(User $user, Permission $permission)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($permission->store->id);
    }

    public function create(User $user, Store $store)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($store->id);
    }

    public function update(User $user, Permission $permission)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($permission->store->id);
    }

    public function delete(User $user, Permission $permission)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($permission->store->id);
    }
}
