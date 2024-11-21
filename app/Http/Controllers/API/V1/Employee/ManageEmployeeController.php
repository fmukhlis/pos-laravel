<?php

namespace App\Http\Controllers\API\V1\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeInvite;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ManageEmployeeController extends Controller
{
    public function terminate(Request $request, Store $store, Employee $employee)
    {
        Gate::authorize('terminate', $employee);

        $employee->store()->dissociate();
        $employee->save();

        return new \App\Http\Resources\V1\Employee($employee);
    }

    public function makeActive(Request $request, Store $store, Employee $employee)
    {
        Gate::authorize('makeInactive', $employee);

        $employee->status = 'Active';
        $employee->save();

        return new \App\Http\Resources\V1\Employee($employee);
    }

    public function makeInactive(Request $request, Store $store, Employee $employee)
    {
        Gate::authorize('makeInactive', $employee);

        $employee->status = 'Inactive';
        $employee->save();

        return new \App\Http\Resources\V1\Employee($employee);
    }
}
