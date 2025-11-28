<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EpassBookLoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = $this->resource ?? null; // Extract 'data' from the original response
       
        if (isset($data->original)) {
            // If 'data' is available, return the formatted response with 'status', 'message', and 'data' keys
            return [
                'status' =>$data->original['status'],
                'code' => '200',
                'message' => $data->original['message'],
                'data' => $data->original['data'],
            ];
        }else if (isset($data)) {
            // If 'data' is available, return the formatted response with 'status', 'message', and 'data' keys
            return [
                'status' => 'success',
                'code' => '200',
                'message' => 'Retrive Details',
                'data' => $this->resource,
            ];
        }else if(isset($data['status']) && $data['status'] === 'error') {
            // If 'data' is not available, return an error response with 'status', 'message', and 'data' keys
            return [
                'status' => 'error',
                'code' => '201',
                'message' => $data['message'],
                'data' => null,
            ];
        } else {
            // If 'data' is not available, return an error response with 'status', 'message', and 'data' keys
            return [
                'status' => 'error',
                'code' => '201',
                'message' => 'Unknown error occurred',
                'data' => null,
            ];
        }
    }
}
