<?php

namespace App\Http\Requests\Bookmark;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookmarkcreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // return [
        //     // 'bookmark_type_id'=>'required|exists:bookmark_types,id',
        //     'bookmark_type_id'=>[Rule::unique('bookmarks','bookmark_type_id')->where(fn($q)=>
        //     $q->where('park_id',$this->park_id)
        //     ->where('user_id',$this->user()->id)
        //     )],
        //     'park_id'=>"required|exists:parks,id"
        // ];

        return [
            'bookmark_type_id'=>'exists:bookmark_types,id',
            'park_id'=>"required|exists:parks,id"
        ];
    }
}
