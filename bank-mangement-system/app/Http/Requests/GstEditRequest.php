<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\GstSetting;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class GstEditRequest extends FormRequest
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
    public function rules(Request $req)
    {
      
        return [
            'gst_no' => 'required|unique:gst_setting,gst_no,'.$req->edit_id,
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
            'gst_no.unique' => 'Gst Number Already exist',
            'applicable_date.required' =>'Please Enter Value',
            'end_date.required' => 'Please Enter Value',
            'category.required' =>'Please Enter Value',
            'state_id.required' => 'Please Enter Value',

        ];
        
    }
}
