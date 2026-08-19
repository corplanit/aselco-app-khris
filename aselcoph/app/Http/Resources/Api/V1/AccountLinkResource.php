<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountLinkResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_number' => (string) $this->account_number,
            'owner_name' => $this->owner_name,
            'status' => $this->validated_at ? 'validated' : 'pending',
            'validated_at' => $this->validated_at,
            'validated_by' => $this->validated_by,
            'created_at' => $this->created_at,
        ];
    }
}
