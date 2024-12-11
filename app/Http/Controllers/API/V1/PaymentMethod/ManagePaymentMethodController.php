<?php

namespace App\Http\Controllers\API\V1\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ManagePaymentMethodController extends Controller
{
    public function create(Request $request, Store $store)
    {
        Gate::authorize('create', [PaymentMethod::class, $store]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255']
        ]);

        $paymentMethod = $store->paymentMethods()
            ->where('name', $validated['name'])
            ->where('destination', $validated['destination'])
            ->first();

        if ($paymentMethod) {
            throw ValidationException::withMessages([
                'name' => 'This payment method is already exists',
                'destination' => 'This payment method is already exists'
            ]);
        } else {
            $paymentMethod = $store
                ->paymentMethods()
                ->create($validated);
        }

        return new \App\http\Resources\V1\PaymentMethod($paymentMethod);
    }

    public function update(Request $request, Store $store, PaymentMethod $paymentMethod)
    {
        Gate::authorize('update', $paymentMethod);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255']
        ]);

        $newPaymentMethod = $store->paymentMethods()
            ->where('name', $validated['name'])
            ->where('destination', $validated['destination'])
            ->first();

        if ($newPaymentMethod && $newPaymentMethod->is($paymentMethod)) {
            throw ValidationException::withMessages([
                'name' => 'This payment method is already exists',
                'destination' => 'This payment method is already exists'
            ]);
        } else {
            $paymentMethod->name = $validated['name'];
            $paymentMethod->destination = $validated['destination'];
            $paymentMethod->save();
        }

        return new \App\http\Resources\V1\PaymentMethod($paymentMethod);
    }

    public function delete(Request $request, Store $store, PaymentMethod $paymentMethod)
    {
        Gate::authorize('delete', $paymentMethod);

        $paymentMethod->delete();

        return response()->json([], 204);
    }
}
