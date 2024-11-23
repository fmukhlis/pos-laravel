<?php

namespace App\Http\Controllers\API\V1\Permission;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GetPermissionController extends Controller
{
    public function getAll(Request $request, Store $store)
    {
        Gate::authorize('viewAny', [Permission::class, $store]);

        return new \App\Http\Resources\V1\PermissionCollection($store->permissions);
    }

    public function get(Request $request, Store $store, Permission $permission)
    {
        Gate::authorize('view', $permission);

        return new \App\Http\Resources\V1\Permission($permission);
    }
}
