<?php

namespace App\Policies;

use App\Models\ProductCategory;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductCategoryPolicy
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
            : Response::deny("You don't have privilege to add product category for this store");
    }

    public function update(User $user, ProductCategory $productCategory)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($productCategory->store->id)
            ? Response::allow()
            : Response::deny("You don't have privilege to add product category for this store");
    }

    public function delete(User $user, ProductCategory $productCategory)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($productCategory->store->id)
            ? Response::allow()
            : Response::deny("You don't have privilege to add product category for this store");
    }
}
