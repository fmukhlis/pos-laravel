<?php

namespace App\Http\Controllers\API\V1\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeInvite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ManageEmployeeInvitationController extends Controller
{
    public function accept(Request $request, User $user, EmployeeInvite $employeeInvite)
    {
        Gate::authorize('accept', $employeeInvite);

        $employeeInvite->status = 'Accepted';
        $employeeInvite->save();

        $employee = $user->employee()->firstOrCreate(['status' => 'Active']);
        $employee->store()->associate($employeeInvite->store);

        return response()->json([
            'message' => 'You joined a store',
            'data' => new \App\Http\Resources\V1\EmployeeInvite($employeeInvite)
        ]);
    }

    public function decline(Request $request, User $user, EmployeeInvite $employeeInvite)
    {
        Gate::authorize('decline', $employeeInvite);

        $employeeInvite->status = 'Declined';
        $employeeInvite->save();

        return response()->json([
            'message' => 'Invitation declined',
            'data' => new \App\Http\Resources\V1\EmployeeInvite($employeeInvite)
        ]);
    }
}
