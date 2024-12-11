<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailedProduct extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => new ProductCategoryResource(
                $this->whenLoaded('productCategory')
            ),
            'availableModifiers' => ProductModifierCategoryResource::collection(
                $this->whenLoaded('productModifierCategories')
            ),
            'availableOptions' => ProductOptionCategoryResource::collection(
                $this->whenLoaded('productOptionCategories')
            ),
            'availableVariants' => ProductVariantResource::collection(
                $this->whenLoaded('productVariants')
            )
        ];
    }
}
