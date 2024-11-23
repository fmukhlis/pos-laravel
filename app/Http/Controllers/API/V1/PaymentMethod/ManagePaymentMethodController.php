<?php

namespace App\Http\Controllers\API\V1\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ManagePaymentMethodController extends Controller
{
    public function create(Request $request, Store $store)
    {
        Gate::authorize('create', [PaymentMethod::class, $store]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255']
        ]);

        $paymentMethod = $store->paymentMethods()->create($validated);

        return new \App\http\Resources\V1\PaymentMethod($paymentMethod);
    }

    public function update(Request $request, Store $store, PaymentMethod $paymentMethod)
    {
        Gate::authorize('update', $paymentMethod);

        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'destination' => ['string', 'max:255']
        ]);

        $paymentMethod->update($validated);

        return new \App\http\Resources\V1\PaymentMethod($paymentMethod);
    }

    public function delete(Request $request, Store $store, PaymentMethod $paymentMethod)
    {
        Gate::authorize('delete', $paymentMethod);

        $paymentMethod->delete();

        return response()->json([], 204);
    }
}
