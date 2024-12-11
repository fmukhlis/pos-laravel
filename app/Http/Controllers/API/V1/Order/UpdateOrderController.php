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

class UpdateOrderController extends Controller
{
    public function __invoke(Request $request, Store $store, Order $order)
    {
        $validated = $request->validate([
            'authorization_code' => ['required', 'string', 'size:6'],
            'cash_amount' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'note' => ['nullable', 'string', 'max:255'],
            'order_type' => ['required', Rule::in(['Dine In', 'Take Away'])],
            'status' => ['required', Rule::in(['Billed', 'Paid'])],
            'table_number' => ['nullable', 'string', 'max:20'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'ordered_products' => ['required', 'array'],
            'ordered_products.*.variant_id' => ['exists:product_variants,id'],
            'ordered_products.*.modifier_ids' => ['required', 'array'],
            'ordered_products.*.modifier_ids.*' => ['exists:product_options,id']
        ]);

        Gate::authorize('update', [$order, $validated['authorization_code']]);

        $order->cash_amount = $validated['cash_amount'];
        $order->note = $validated['note'];
        $order->order_type = $validated['order_type'];
        $order->status = $validated['status'];
        $order->table_number = $validated['table_number'];

        if (Arr::has($validated, 'customer_id')) {
            $order->customer()
                ->associate(
                    Customer::find(
                        $validated['customer_id']
                    )
                );
        }

        $order->paymentMethod()
            ->associate(
                PaymentMethod::find(
                    $validated['payment_method_id']
                )
            );

        $order->user()
            ->associate($request->user());

        $order->save();

        $order->orderProductVariants()->delete();

        foreach ($validated['ordered_products'] as $orderedProduct) {
            $orderProductVariant = new OrderProductVariant();

            $orderProductVariant->order()
                ->associate($order);

            $productVariant = ProductVariant::find(
                $orderedProduct['variant_id']
            );

            $orderProductVariant->productVariant()
                ->associate($productVariant);

            $orderProductVariant->save();

            $orderProductVariant->productModifiers()
                ->sync(
                    $orderedProduct['modifier_ids']
                );
        }

        return new \App\Http\Resources\V1\OrderResource(
            $order->load([
                'orderProductVariants' => [
                    'productVariant' => [
                        'product',
                        'productOptions'
                    ],
                    'productModifiers'
                ]
            ])
        );
    }
}
