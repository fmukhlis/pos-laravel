<?php

namespace App\Http\Controllers\API\V1\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;

class GetStoreController extends Controller
{
    public function __invoke(Request $request, Store $store)
    {
        return new \App\Http\Resources\V1\Store($store->load('owner'));
    }
}
