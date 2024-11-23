<?php

namespace App\Policies;

use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentMethodPolicy
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

        return $ownedStoreIds->contains($store->id)
            ? Response::allow()
            : Response::deny("You don't have privilege to add payment method for this store");
    }

    public function update(User $user, PaymentMethod $paymentMethod)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($paymentMethod->store->id);
    }

    public function delete(User $user, PaymentMethod $paymentMethod)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($paymentMethod->store->id);
    }
}
