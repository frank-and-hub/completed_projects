<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssociateInvestmentRegisterRequest extends FormRequest
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
            'customer_id' => 'required',
            'branch_id' => 'required|numeric',
            'company_id' => 'required|numeric',
            'plan_type' => 'required' ,// it is actually a planCategory of plans table
            'amount'  => 'required|min:0|numeric',
            'payment-mode' => 'required',
            'interest-rate' => 'required',
            'maturity-amount' => 'required',
            'tenure' => 'required',
            'form_number' => 'required',
            'newUser' => 'required|boolean',
            'is_ssb_required' => 'required',
            'plan_sub_category' => 'required',
            'age' => 'required|numeric|min:0',
            'dob' => 'required|date|date_format:Y-m-d',
            'member_name' => 'required',
            'stationary_charge' => 'required|numeric|min:0',
            // nominee validate
            'fn_first_name' => 'required',
            'fn_relationship' => 'required',
            'fn_gender' => 'required',
            'fn_dob' => 'required',
            'fn_age' => 'required',
            'fn_percentage' => 'required|max:100',



        ];
    }
}
