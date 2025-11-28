<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HeadSettingRequest extends FormRequest
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
            'head_id' => 'required|Integer',
            'gst_percentage' => 'required|Integer',
        ];
    }

    public function messages()
    {
        return [
            'head_id.required' => 'Please Select Head',
            'gst_percentage.required' => 'Please Enter Gst Percentage',
            'gst_percentage.integer' => 'Please Enter Gst Percentage in Valid Format',
        ];
    }
}
