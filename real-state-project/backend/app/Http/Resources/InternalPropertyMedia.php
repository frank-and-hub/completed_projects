<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class InternalPropertyMedia extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = filter_var($this->path, FILTER_VALIDATE_URL) ? $this->path : Storage::url($this->path);
        return [
            'id'    => $this->id,
            'image'  => $imageUrl,
            'isMain'    => $this->isMain
        ];
    }
}
