<?php

namespace App\Http\Controllers\API\V1\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DeleteProductController extends Controller
{
    public function __invoke(Request $request, Store $store, Product $product)
    {
        Gate::authorize('delete', $product);

        $product->delete();

        return response()->json([], 204);
    }
}
