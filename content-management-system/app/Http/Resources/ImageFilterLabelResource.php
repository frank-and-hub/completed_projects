<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImageFilterLabelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        

        return [
            [
                'label'=>'All Photos',
                'totalImg'=>$this->clone()->count(),
            ],
            [
                'label'=>'Latest',
                'totalImg'=>$this->clone()->orderBy('created_at', 'desc')->count(), 
            ],
            [
                'label'=>'By Parkscape',
                'totalImg'=>$this->clone()->whereNull('user_id')->orWhere(function ($q) {
                                $q->whereNotNull('user_id')->where('is_verified', 1)->whereHas('user', function ($query) {
                                    $query->role('subadmin');
                                });
                            })->count()
            ],
            [
                'label'=>'By Users',
                'totalImg'=>$this->clone()->whereNotNull('user_id')->where('is_verified', true)
                ->WhereHas('user', function ($q) {
                    $q->role('user');
                })->count()
                            
            ]
            ];
    }
}
