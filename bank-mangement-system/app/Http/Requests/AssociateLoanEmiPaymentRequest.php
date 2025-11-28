<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssociateLoanEmiPaymentRequest extends FormRequest
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
          
           'loan_type' =>'required',
           'data' => 'required',
            'data.*.account_number' => 'required',
            'data.*.amount' => 'required|numeric|min:1',
           'token' => 'required',
           'company_id' => 'required'
        ];
    }
}
