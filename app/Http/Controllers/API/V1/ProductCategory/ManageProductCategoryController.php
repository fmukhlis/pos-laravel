<?php

namespace App\Http\Controllers\API\V1\ProductCategory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ManageProductCategoryController extends Controller
{
    public function create(Request $request, Store $store)
    {
        Gate::authorize('create', [ProductCategory::class, $store]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id']
        ]);

        $productCategory = $store
            ->productCategories()
            ->create($validated);

        $productCategory->products()->saveMany(
            collect($validated['product_ids'])
                ->map(fn($productId) => (
                    Product::find($productId)
                ))
        );

        return new \App\http\resources\v1\ProductCategoryResource(
            $productCategory
                ->load(['products'])
        );
    }

    public function update(Request $request, Store $store, ProductCategory $productCategory)
    {
        Gate::authorize('update', $productCategory);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id']
        ]);

        $productCategory->name = $validated['name'];
        $productCategory->save();

        $products = collect($validated['product_ids'])
            ->map(fn($productId) => (
                Product::find($productId)
            ))
            ->all();

        $productCategory
            ->products
            ->each(function ($product) {
                $product
                    ->productCategory()
                    ->dissociate();
            });

        $productCategory->products()
            ->saveMany($products);

        return new \App\http\resources\v1\ProductCategoryResource(
            $productCategory
                ->load(['products'])
        );
    }

    public function delete(Request $request, Store $store, ProductCategory $productCategory)
    {
        Gate::authorize('delete', $productCategory);

        $productCategory->delete();

        return response()->json([], 204);
    }
}
