<?php

namespace App\Http\Controllers\API\V1\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Gate;

class ManageStoreController extends Controller
{
    public function update(Request $request, Store $store)
    {
        Gate::authorize('update', $store);

        $validated = $request->validate([
            'name' => ['string', 'nullable', 'max:255'],
            'phone' => ['string', 'nullable', 'max:20'],
            'email' => ['string', 'nullable', 'lowercase', 'email', 'max:255'],
            'address' => ['string', 'nullable', 'max:512']
        ]);

        $store->update($validated);

        return new \App\Http\Resources\V1\Store($store);
    }

    public function destroy(Request $request, Store $store)
    {
        Gate::authorize('delete', $store);

        $store->delete();

        return response()->json([], 204);
    }
}
