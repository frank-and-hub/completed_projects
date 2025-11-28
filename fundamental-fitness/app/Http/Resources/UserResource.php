<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roles = [
            1 => 'Admin',
            2 => 'User'
        ];

        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'email' => $this->email,
            'profile_photo' => $this->profile_photo ? asset($this->profile_photo) : null,
            'role' => $roles[$this->role] ?? 'Unknown',
            'status' => $this->status,
            'workout_frequency' => new WorkoutFrequencyResource($this->whenLoaded('work_out_frequency')),
            'language' => $this->language,
            'device_type' => $this->device_type,
            'device_id' => $this->device_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_frequency_set' => $this->is_frequency_set,
            'is_subscribe' => $this->is_subscribe == 1 ? true : false
        ];
    }
}