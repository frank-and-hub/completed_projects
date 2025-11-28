<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssociateLoanRequest extends FormRequest
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
            'loan_ids' => 'required',
            'token' => 'required',
        ];
    }

    /**
     * Get the custom validation message for specific rule.
     * @return array
     */

    public function messages()
    {
        return [
            'loan_type.required' => 'The loan type field is required.',
            'loan_id.required' => 'The loan ID field is required.',
            'token.required' => 'The token field is required.',
            'account_numbers.required' => 'The account numbers field is required.',
        ];
    }
}
