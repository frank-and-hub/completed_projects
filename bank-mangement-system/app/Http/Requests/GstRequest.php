<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GstRequest extends FormRequest
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
            'gst_no' => 'required|unique:gst_setting',
            'applicable_date' => 'required',
            // 'end_date' => 'required',
            'category' => 'required',
            'state_id' => 'required'

        ];
        
    }

    public function messages()
    {
        return [
            'gst_no.required' => 'Please Enter Value',
            'applicable_date.required' =>'Please Enter Value',
            'end_date.required' => 'Please Enter Value',
            'category.required' =>'Please Enter Value',
            'state_id.required' => 'Please Enter Value',

        ];
        
    }
}
