<?php

namespace App\Http\Controllers\API\V1\ProductCategory;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\Store;
use Illuminate\Http\Request;

class GetProductCategoryController extends Controller
{
    public function getAll(Request $request, Store $store)
    {
        return new \App\http\Resources\V1\ProductCategoryCollection(
            $store
                ->productCategories()
                ->with(['products'])
                ->get()
        );
    }

    public function get(Request $request, Store $store, ProductCategory $productCategory)
    {
        return new \App\http\resources\v1\DetailedProductCategory(
            $productCategory
                ->load(['products'])
        );
    }
}
