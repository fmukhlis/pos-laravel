<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailedOrder extends JsonResource
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
            'cashAmount' => $this->cash_amount,
            'note' => $this->note,
            'orderType' => $this->order_type,
            'status' => $this->status,
            'tableNumber' => $this->table_number,
            'createdAt' => $this->created_at,
            'orderedProducts' => OrderProductVariantResource::collection(
                $this->whenLoaded('orderProductVariants')
            ),
            'customer' => $this->when(
                $this->relationLoaded('customer'),
                new Customer($this->customer)
            ),
            'paymentMethod' => $this->when(
                $this->relationLoaded('paymentMethod'),
                new PaymentMethod($this->paymentMethod)
            ),
            'servicedBy' =>  $this->when(
                $this->relationLoaded('user'),
                new User($this->user)
            )
        ];
    }
}
