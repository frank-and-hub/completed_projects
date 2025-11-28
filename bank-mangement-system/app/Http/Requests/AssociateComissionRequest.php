<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssociateComissionRequest extends FormRequest
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
            'month' =>'required',
            'year' => 'required' , 
            'company_id' => 'required' ,
            'associate_no' => 'required' ,
           
        ];
    }
}
