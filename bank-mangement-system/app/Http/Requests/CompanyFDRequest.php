<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyFDRequest extends FormRequest
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
            'fd_no' => 'required|Integer|unique:company_bound,is_deleted',

        ];
    }

    /**
     * Cusstom Message For Validation
     * 
     * @return array
     */

     public function messages()
     {
        return [
            'fd_no.unique' =>'FD Number Should be unique',
        ];
     } 

}
