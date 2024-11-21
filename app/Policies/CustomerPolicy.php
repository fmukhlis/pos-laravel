<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Store;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user, Store $store)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($store->id);
    }

    public function update(User $user, Customer $customer)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($customer->store->id);
    }

    public function delete(User $user, Customer $customer)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($customer->store->id);
    }
}
