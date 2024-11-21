<?php

namespace App\Http\Controllers\API\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ManageCustomerController extends Controller
{
    public function create(Request $request, Store $store)
    {
        Gate::authorize('create', [Customer::class, $store]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'gender' => ['required', 'in:Male,Female'],
        ]);

        $customer = $store->customers()->create($validated);

        return new \App\Http\Resources\V1\Customer($customer);
    }

    public function update(Request $request, Store $store, Customer $customer)
    {
        Gate::authorize('update', $customer);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'gender' => ['required', 'in:Male,Female'],
        ]);

        $customer->update($validated);

        return new \App\Http\Resources\V1\Customer($customer);
    }

    public function delete(Request $request, Store $store, Customer $customer)
    {
        Gate::authorize('delete', $customer);

        $customer->delete();

        return response()->json([], 204);
    }
}
