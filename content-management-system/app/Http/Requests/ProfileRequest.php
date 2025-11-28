<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasRole('user');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "name" => "sometimes|max:255",
            // "username" =>"required|max:255",Rule::unique('users','username')->ignore($this->user()->id,'username'),
            "username"=>['required','max:255',Rule::unique("users","username")->ignore($this->user()->id,'id')],
            "image" => "sometimes|image|max:2048|mimes:png,jpg",
        ];
    }

    public function messages()
    {
        return [
            'image.max' => 'The size of the image must not be greater than 2 MB',
        ];
    }
}
