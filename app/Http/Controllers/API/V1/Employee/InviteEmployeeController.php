<?php

namespace App\Http\Controllers\API\V1\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeInvite;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InviteEmployeeController extends Controller
{
    public function invite(Request $request, Store $store)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id']
        ]);

        Gate::authorize('create', [EmployeeInvite::class, $store, $validated['user_ids']]);

        $employeeInvites = $store->employeeInvites()->saveMany(
            collect($validated['user_ids'])
                ->map(function ($id) {
                    $employeeInvite = new EmployeeInvite();
                    $user = User::find($id);
                    $employeeInvite->invitee()->associate($user);

                    return $employeeInvite;
                })
        );

        return response()->json([
            'message' => 'Invitation sent',
            'data' => \App\Http\Resources\V1\EmployeeInvite::collection($employeeInvites)
        ]);
    }
    public function disinvite(Request $request, Store $store, EmployeeInvite $employeeInvite)
    {
        Gate::authorize('delete', $employeeInvite);

        $employeeInvite->delete();

        return response()->json([], 204);
    }
}
