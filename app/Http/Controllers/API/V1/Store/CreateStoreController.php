<?php

namespace App\Http\Controllers\API\V1\Store;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CreateStoreController extends Controller
{

    public function __invoke(Request $request)
    {
        Gate::authorize('create', Store::class);

        $validated = $request->validate([
            'name' => ['string', 'required', 'max:255'],
            'phone' => ['string', 'required', 'max:20'],
            'email' => ['string', 'required', 'lowercase', 'email', 'max:255'],
            'address' => ['string', 'required', 'max:512']
        ]);

        $store = new Store($validated);

        $request->user()->ownedStores()->save($store);

        return new \App\Http\Resources\V1\Store($store);
    }
}
