<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenewalRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'member_id'=>'required',
            'deposite_by_name' => 'required',

        ];
    }

    

    public function messages()
    {
        return [
            'member_id.required' => 'Something Went Wrong (Associate Id Missing)',
            'deposite_by_name.required' => 'Some Thing Went Wrong',
        ];
    }
}
