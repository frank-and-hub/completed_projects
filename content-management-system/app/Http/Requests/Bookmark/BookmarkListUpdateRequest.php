<?php

namespace App\Http\Requests\Bookmark;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookmarkListUpdateRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        $user = $this->user();
        return [
            'id' => 'required|exists:bookmark_types,id',
            'type' => [Rule::unique('bookmark_types', 'type')->where(fn ($q) =>
            $q->where('user_id', $user->id)
            ->where('id','!=',$this->id)), 'required', 'string', 'min:3', 'max:250'],
        ];
    }
}
