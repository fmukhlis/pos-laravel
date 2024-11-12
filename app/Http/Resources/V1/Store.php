<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Store extends JsonResource
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
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'createdAt' => $this->created_at,
            'owner' => User::make($this->whenLoaded('owner'))
        ];
    }
}
