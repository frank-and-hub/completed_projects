<?php

namespace App\Http\Resources;

use App\Models\ContractStatus;
use App\Models\ContractUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserMetchingPropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        // internal property data
        if ($this->resource->getTable() == 'sent_internal_property_users') {

            // get all properties
            $property = $this->property;

            // get contract linked properties
            $contractProperty = \App\Models\InternalProperty::with('contract')->find($this->internal_property_id);

            // should be single contract with single property
            $contract = $contractProperty?->contract()->first();

            // get the contract linked user/tenant if any
            $contractUser = $contract?->id ? ContractUser::where('contract_id', $contract?->id)->where('user_id', Auth::user()?->id)?->latest()?->first() ?? null : null;

            // get conntract id
            $contractId = ($contract) ? $contract->id : null;

            // current contract status
            $NewContract = ContractStatus::whereContractId($contractId)->whereUserId(Auth::user()->id)->first();

            // if there is an contract then corrent uptodated contract file path
            $contractPath = ($contract) ? (($NewContract) ? $NewContract->contract_path : $contract->path) : null;

            //  current updatedd contract status
            $status = $NewContract ? $NewContract->status : null;

            // if there is a status then finding correct status alise name
            $contractStatus = $status ? ContractStatus::STATUS_ARRAY()[$status] : ContractStatus::STATUS_TENANT_PENDING;

            // get property main image
            $mainPhoto = $property->imageIsMain->first();

            $return = [
                'id' => $this->id,
                'property_id' => $this->internal_property_id,
                'title' => $property->title,
                'address' => $property->propertyAddress(),
                'image' => Storage::url($mainPhoto->path),
                'details_url' => "internal",
                'contract_status' => $contractStatus,
                'contract_id' => $contractId,
                'admin_id' => $contractProperty->admin_id,
                'property' => new InternalPropertyResource($property),
                'credit_reports_status' => $this->credit_reports_status,
            ];

            // check if there is a contract user/tenant exiest then updated contract file path should added to responce
            if ($contractUser) {
                if (filter_var($contractPath, FILTER_VALIDATE_URL)) {
                    $parsedUrl = parse_url($contractPath);
                    $contractPath = ltrim($parsedUrl['path'], '/');
                }
                $return['contract'] = str_contains($contractPath, 'storage/') ? $contractPath : 'storage/' . $contractPath;
            }
            return $return;
        } else {

            // external property data
            $property = $this->property;
            $mainPhoto = $property->photos->firstWhere('isMain', 1);
            return [
                'id' => $this->id,
                'property_id' => $this->property_id,
                'title' => $property->title,
                'address' => $property->suburb . ' ' . $property->town . ' ' . $property->province,
                'image' => $mainPhoto->imgUrl,
                'details_url' => 'external',
                'contract' => null,
                'contract_status' => null,
                'contract_id' => null,
                'admin_id' => null,
                'property' => new PropertyResource($property),
                'credit_reports_status' => $this->credit_reports_status,
            ];
        }
    }
}
