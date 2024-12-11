<?php

namespace App\Http\Resources\V1;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductVariantResource extends JsonResource
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
            'isCanceled' => $this->is_canceled,
            'cancelReason' => $this->cancel_reason,
            'canceledAt' => $this->is_canceled
                ? $this->updated_at
                : null,
            'product' => $this->when(
                $this->relationLoaded('productVariant') &&
                    $this->productVariant->relationLoaded('product'),
                new ProductResource(
                    $this->productVariant
                        ->product
                )
            ),
            'variant' => $this->when(
                $this->relationLoaded('productVariant'),
                new ProductVariantResource(
                    $this->productVariant
                )
            ),
            'selectedModifiers' => ProductModifierResource::collection(
                $this->whenLoaded('productModifiers')
            )
        ];
    }
}
