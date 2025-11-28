<?php

namespace App\Http\Requests\Bookmark;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookmarkUpdateRequest extends FormRequest
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
        return [
            'id'=>'required|exists:bookmarks,id',
            'bookmark_type_id'=>[Rule::unique('bookmarks','bookmark_type_id')->where(fn($q)=>
            $q->where('park_id',$this->park_id)
            ->where('user_id',$this->user()->id)->where('id','!=',$this->id)
            )],
            'park_id'=>"required|exists:parks,id"
        ];
    }
}
