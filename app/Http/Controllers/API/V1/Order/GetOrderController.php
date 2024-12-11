<?php

namespace App\Http\Controllers\API\V1\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GetOrderController extends Controller
{
    public function getAll(Request $request, Store $store)
    {
        Gate::authorize('viewAny', [Order::class, $store]);

        return new \App\Http\Resources\V1\OrderCollection(
            $store->orders()
                ->with([
                    'orderProductVariants' => [
                        'productVariant' => [
                            'product',
                            'productOptions'
                        ],
                        'productModifiers'
                    ]
                ])
                ->get()
        );
    }

    public function get(Request $request, Store $store, Order $order)
    {
        Gate::authorize('view', $order);

        return new \App\Http\Resources\V1\DetailedOrder(
            $order->load([
                'orderProductVariants' => [
                    'productVariant' => [
                        'product',
                        'productOptions'
                    ],
                    'productModifiers'
                ],
                'customer',
                'paymentMethod',
                'user'
            ])
        );
    }
}
