<?php

namespace App\Http\Controllers\API\V1\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class UpdateProductController extends Controller
{
    public function __invoke(Request $request, Store $store, Product $product)
    {
        Gate::authorize('update', $product);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'available_modifiers' => ['nullable', 'array'],

            'available_modifiers.*.category_name' => ['required', 'string', 'max:25'],
            'available_modifiers.*.values' => ['required', 'array'],

            'available_modifiers.*.values.*.name' => ['required', 'string', 'max:25'],

            'available_options' => ['nullable', 'array'],

            'available_options.*.category_name' => ['required', 'string', 'max:25'],
            'available_options.*.values' => ['required', 'array'],

            'available_options.*.values.*.name' => ['required', 'string', 'max:25'],

            'available_variants' => ['required', 'array'],

            'available_variants.*.stock' => ['required', 'numeric', 'min:1', 'max:999999'],
            'available_variants.*.price' => ['required', 'numeric', 'min:1', 'max:99999999'],
            'available_variants.*.sku' => ['nullable', 'string', 'max:50'],
            'available_variants.*.options' => ['nullable', 'required_unless:available_options,null', 'array'],

            'available_variants.*.options.*' => ['required', 'string'],
        ]);

        $product->update($validated);

        if (isset($validated['available_modifiers'])) {
            foreach ($validated['available_modifiers'] as $availableModifier) {
                $productModifierCategory = $product
                    ->productModifierCategories()
                    ->firstOrCreate([
                        'name' => Str::title(
                            $availableModifier['category_name']
                        )
                    ]);

                foreach ($availableModifier['values'] as $value) {
                    $productModifierCategory
                        ->productModifiers()
                        ->firstOrCreate([
                            'name' => Str::title(
                                $value['name']
                            )
                        ]);
                }
            }

            $product->productModifierCategories()
                ->whereNotIn(
                    'name',
                    collect($validated['available_modifiers'])
                        ->map(fn($availableModifier) => (
                            Str::title(
                                $availableModifier['category_name']
                            )
                        ))
                        ->all()
                )
                ->get()
                ->each(function ($productModifierCategory) {
                    $productModifierCategory->update([
                        'status' => 'Inactive'
                    ]);
                });

            $product->productModifiers()
                ->whereNotIn(
                    'product_modifiers.name',
                    collect($validated['available_modifiers'])
                        ->map(fn($availableModifier) => (
                            collect($availableModifier['values'])
                            ->map(fn($value) => (
                                Str::title(
                                    $value['name']
                                )
                            ))
                        ))
                        ->flatten()
                        ->all()
                )
                ->get()
                ->each(function ($productModifier) {
                    $productModifier->update([
                        'status' => 'Inactive'
                    ]);
                });
        }

        if (isset($validated['available_options'])) {
            foreach ($validated['available_options'] as $availableOption) {
                $productOptionCategory = $product
                    ->productOptionCategories()
                    ->firstOrCreate([
                        'name' => Str::title(
                            $availableOption['category_name']
                        )
                    ]);

                foreach ($availableOption['values'] as $value) {
                    $productOptionCategory
                        ->productOptions()
                        ->firstOrCreate([
                            'name' => Str::title(
                                $value['name']
                            )
                        ]);
                }
            }

            $product->productOptionCategories()
                ->whereNotIn(
                    'name',
                    collect($validated['available_options'])
                        ->map(fn($availableOption) => (
                            Str::title(
                                $availableOption['category_name']
                            )
                        ))
                        ->all()
                )
                ->get()
                ->each(function ($productOptionCategory) {
                    $productOptionCategory->update([
                        'status' => 'Inactive'
                    ]);
                });

            $product->productOptions()
                ->whereNotIn(
                    'product_options.name',
                    collect($validated['available_options'])
                        ->map(fn($availableOption) => (
                            collect($availableOption['values'])
                            ->map(fn($value) => (
                                Str::title(
                                    $value['name']
                                )
                            ))
                        ))
                        ->flatten()
                        ->all()
                )
                ->get()
                ->each(function ($productOption) {
                    $productOption->update([
                        'status' => 'Inactive'
                    ]);
                });
        }

        foreach ($validated['available_variants'] as $availableVariant) {
            $productVariant = $product
                ->productVariants()
                ->firstOrCreate([
                    'price' => $availableVariant['price'],
                    'stock' => $availableVariant['stock'],
                    'sku' => $availableVariant['sku']
                ]);

            if (isset($availableVariant['options'])) {
                $productOptions = ProductOption::whereIn(
                    'name',
                    $availableVariant['options']
                )->get();

                $productVariant
                    ->productOptions()
                    ->sync($productOptions);
            }
        }

        $product->productVariants()
            ->where(function ($query) use ($validated) {
                foreach ($validated['available_variants'] as $availableVariant) {
                    $query->orWhere(function ($query) use ($availableVariant) {
                        $query->where('stock', '!=', $availableVariant['stock'])
                            ->where('price', '!=', $availableVariant['price'])
                            ->where('sku', '!=', $availableVariant['sku']);
                    });
                }
            })
            ->get()
            ->each(function ($productVariant) {
                $productVariant->update([
                    'status' => 'Inactive'
                ]);
            });

        return new \App\Http\Resources\V1\ProductResource(
            $product->load(
                [
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
