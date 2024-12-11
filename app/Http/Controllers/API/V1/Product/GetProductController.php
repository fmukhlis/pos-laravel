<?php

namespace App\Http\Controllers\API\V1\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class GetProductController extends Controller
{
    public function getAll(Request $request, Store $store)
    {
        return new \App\Http\Resources\V1\ProductCollection(
            $store->products()->with([
                'productOptionCategories' => [
                    'productOptions'
                ],
                'productModifierCategories' => [
                    'productModifiers'
                ],
                'productVariants' => [
                    'productOptions'
                ]
            ])->get()
        );
    }

    public function get(Request $request, Store $store, Product $product)
    {
        return new \App\http\Resources\V1\DetailedProduct(
            $product->load(
                [
                    'productCategory' => [
                        'products'
                    ],
                    'productOptionCategories' => [
                        'productOptions'
                    ],
                    'productModifierCategories' => [
                        'productModifiers'
                    ],
                    'productVariants' => [
                        'productOptions'
                    ]
                ]
            )
        );
    }
}
