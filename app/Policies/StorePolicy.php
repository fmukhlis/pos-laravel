<?php

namespace App\Policies;

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
}
