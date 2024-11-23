<?php

namespace App\Http\Controllers\API\V1\Permission;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ManagePermissionController extends Controller
{
    public function create(Request $request, Store $store)
    {
        Gate::authorize('create', [Permission::class, $store]);

        $validated = $request->validate([
            'authorization_code' => ['required', 'string', 'size:6'],
            'refund' => ['required', 'boolean'],
            'modify_bill' => ['required', 'boolean']
        ]);

        $permission = $store->permissions()->create($validated);

        return new \App\Http\Resources\V1\Permission($permission);
    }

    public function update(Request $request, Store $store, Permission $permission)
    {
        Gate::authorize('update', $permission);

        $validated = $request->validate([
            'authorization_code' => ['string', 'size:6'],
            'refund' => ['boolean'],
            'modify_bill' => ['boolean']
        ]);

        $permission->update($validated);

        return new \App\Http\Resources\V1\Permission($permission);
    }

    public function delete(Request $request, Store $store, Permission $permission)
    {
        Gate::authorize('delete', $permission);

        $permission->delete();

        return response()->json([], 204);
    }
}
