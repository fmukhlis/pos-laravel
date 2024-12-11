<?php

namespace App\Http\Controllers\API\V1\Order;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderProductVariant;
use App\Models\PaymentMethod;
use App\Models\ProductModifier;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class DeleteOrderController extends Controller
{
    public function cancel(Request $request, Store $store, Order $order)
    {
        $validated = $request->validate([
            'authorization_code' => ['required', 'string', 'size:6'],
        ]);

        Gate::authorize('cancel', [$order, $validated['authorization_code']]);

        $order->delete();

        return response()->json([], 204);
    }

    public function refund(Request $request, Store $store, Order $order)
    {
        $validated = $request->validate([
            'authorization_code' => ['required', 'string', 'size:6'],
        ]);

        Gate::authorize('refund', [$order, $validated['authorization_code']]);

        $order->delete();

        return response()->json([], 204);
    }

    public function delete(Request $request, Store $store, Order $order)
    {
        Gate::authorize('forceDelete', $order);

        $order->forceDelete();

        return response()->json([], 204);
    }
}
