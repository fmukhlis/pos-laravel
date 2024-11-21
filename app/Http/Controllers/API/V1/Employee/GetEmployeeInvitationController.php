<?php

namespace App\Http\Controllers\API\V1\Employee;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EmployeeInviteCollection;
use App\Models\EmployeeInvite;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GetEmployeeInvitationController extends Controller
{
    public function getIncoming(Request $request, User $user)
    {
        Gate::authorize('viewAnyIncoming', [EmployeeInvite::class, $user]);

        return new \App\Http\Resources\V1\EmployeeInviteCollection($user->invitations);
    }

    public function getOutgoing(Request $request, Store $store)
    {
        Gate::authorize('viewAnyOutgoing', [EmployeeInvite::class, $store]);

        return new \App\Http\Resources\V1\EmployeeInviteCollection($store->employeeInvites);
    }
}
