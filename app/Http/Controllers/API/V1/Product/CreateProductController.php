<?php

namespace App\Http\Controllers\API\V1\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CreateProductController extends Controller
{
    public function __invoke(Request $request, Store $store)
    {
        Gate::authorize('create', [Product::class, $store]);

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

        $product = $store->products()->create($validated);

        if (isset($validated['available_modifiers'])) {
            foreach ($validated['available_modifiers'] as $availableModifier) {
                $productModifierCategory = $product
                    ->productModifierCategories()
                    ->create([
                        'name' => Str::title(
                            $availableModifier['category_name']
                        )
                    ]);

                foreach ($availableModifier['values'] as $value) {
                    $productModifierCategory
                        ->productModifiers()
                        ->create([
                            'name' => Str::title(
                                $value['name']
                            )
                        ]);
                }
            }
        }

        if (isset($validated['available_options'])) {
            foreach ($validated['available_options'] as $availableOption) {
                $productOptionCategory = $product
                    ->productOptionCategories()
                    ->create([
                        'name' => Str::title(
                            $availableOption['category_name']
                        )
                    ]);

                foreach ($availableOption['values'] as $value) {
                    $productOptionCategory
                        ->productOptions()
                        ->create([
                            'name' => Str::title(
                                $value['name']
                            )
                        ]);
                }
            }
        }

        foreach ($validated['available_variants'] as $availableVariant) {
            $productVariant = $product
                ->productVariants()
                ->create($availableVariant);

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
