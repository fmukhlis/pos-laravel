<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'fullName' => $this->full_name,
        ];

        if ($request->is('/api/v1/settings/my-profile')) {
            $data = array_merge($data, [
                'role' => $this->role,
                'email' => $this->email,
                'emailVerifiedAt' => $this->when($request->has('include_email_verified_at'), $this->email_verified_at),
                'phone' => $this->phone,
            ]);
        }

        return $data;
    }
}
