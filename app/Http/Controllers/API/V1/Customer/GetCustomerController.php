<?php

namespace App\Http\Controllers\API\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\Request;

class GetCustomerController extends Controller
{
    public function get(Request $request, Store $store, Customer $customer)
    {
        return new \App\Http\Resources\V1\Customer($customer);
    }

    public function getAll(Request $request, Store $store)
    {
        return new \App\Http\Resources\V1\CustomerCollection($store->customers);
    }
}
