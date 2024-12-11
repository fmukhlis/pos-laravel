<?php

namespace App\Policies;

use App\Helpers\VerifyAuthorizationCode;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    use VerifyAuthorizationCode;
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

        return $ownedStoreIds->contains($store->id)
            ? Response::allow()
            : ($user->employee->store->is($store)
                ? Response::allow()
                : Response::deny("You don't have privilege to view order on this store"));
    }

    public function view(User $user, Order $order)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($order->store->id)
            ? Response::allow()
            : ($user->employee->store->is($order->store)
                ? Response::allow()
                : Response::deny("You don't have privilege to view detailed order on this store"));
    }

    public function create(User $user, Store $store)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($store->id)
            ? Response::allow()
            : ($user->employee->store->is($store)
                ? Response::allow()
                : Response::deny("You don't have privilege to make any order on this store"));
    }

    public function update(User $user, Order $order, string $authorizationCode)
    {
        if ($order->status === 'Paid') {
            return Response::deny('Modifying a paid order is forbidden');
        }

        if (!$this->canModifyBill($order, $authorizationCode)) {
            return Response::deny('Your authorization code cannot be used for modify the bill');
        }

        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($order->store->id)
            ? Response::allow()
            : ($user->employee->store->is($order->store)
                ? Response::allow()
                : Response::deny("You don't have privilege to update any order on this store"));
    }

    public function cancel(User $user, Order $order, string $authorizationCode)
    {
        if ($order->status === 'Paid') {
            return Response::deny('Canceling a paid order is forbidden, try to refund the order');
        }

        if (!$this->canModifyBill($order, $authorizationCode)) {
            return Response::deny('Your authorization code cannot be used for cancel the order');
        }

        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($order->store->id)
            ? Response::allow()
            : ($user->employee->store->is($order->store)
                ? Response::allow()
                : Response::deny("You don't have privilege to delete any order on this store"));
    }

    public function refund(User $user, Order $order, string $authorizationCode)
    {
        if ($order->status === 'Billed') {
            return Response::deny('The order is still in billing status, try to cancel the order');
        }

        if (!$this->canRefund($order, $authorizationCode)) {
            return Response::deny('Your authorization code cannot be used for refund the order');
        }

        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($order->store->id)
            ? Response::allow()
            : ($user->employee->store->is($order->store)
                ? Response::allow()
                : Response::deny("You don't have privilege to delete any order on this store"));
    }

    public function forceDelete(User $user, Order $order)
    {
        $ownedStoreIds = $user->ownedStores->map(
            fn($ownedStore) => $ownedStore->id
        );

        return $ownedStoreIds->contains($order->store->id)
            ? Response::allow()
            : Response::deny("You don't have privilege to delete any order on this store");
    }
}
