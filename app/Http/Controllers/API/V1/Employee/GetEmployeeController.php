<?php

namespace App\Http\Controllers\API\V1\Employee;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class GetEmployeeController extends Controller
{
    public function __invoke(Request $request, Store $store)
    {
        return new \App\Http\Resources\V1\EmployeeCollection($store->employees);
    }
}
