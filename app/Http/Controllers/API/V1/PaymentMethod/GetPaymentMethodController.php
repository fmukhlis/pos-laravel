<?php

namespace App\Http\Controllers\API\V1\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GetPaymentMethodController extends Controller
{
    public function getAll(Request $request, Store $store)
    {
        return new \App\http\Resources\V1\PaymentMethodCollection($store->paymentMethods);
    }

    public function get(Request $request, Store $store, PaymentMethod $paymentMethod)
    {
        return new \App\http\Resources\V1\PaymentMethod($paymentMethod);
    }
}
