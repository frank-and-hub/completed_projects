<?php

namespace App\Http\Resources;

use App\Models\ContractStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractStatusResource extends JsonResource
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
            'status' => ContractStatus::STATUS_ARRAY()[$this->status] ?? ContractStatus::STATUS_TENANT_PENDING,
            'contract_id' => $this->contract_id,
            'contract_path' => $this->contract_path,
        ];
        return $data;
    }
}
