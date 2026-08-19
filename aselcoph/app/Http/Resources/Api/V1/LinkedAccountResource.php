<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkedAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'account_no' => (string) $this->account_no,
            'customer' => $this->customer,
            'status' => $this->status,
            'meter_no' => $this->meter_no ?? null,
            'address' => $this->address ?? null,
            'rate_class' => $this->rate_class ?? null,
        ];
    }
}
