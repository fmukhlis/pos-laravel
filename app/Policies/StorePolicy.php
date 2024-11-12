<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StorePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user)
    {
        return $user->role === 'Premium'
            ? Response::allow()
            : Response::deny('Please upgrade your account to premium to start creating and managing your own store.');
    }

    public function update(User $user, Store $store)
    {
        return $user->id === $store->user_id
            ? Response::allow()
            : Response::deny('You have no right to update this store.');
    }

    public function delete(User $user, Store $store)
    {
        return $user->id === $store->user_id
            ? Response::allow()
            : Response::deny('You have no right to update this store.');
    }
}
