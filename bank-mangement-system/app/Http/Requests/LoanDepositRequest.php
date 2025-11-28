<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanDepositRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // 'tenure' => 'required|numeric',
            // 'plan_id' => 'required|numeric',
            // 'plan_code' => 'required|numeric|',
            // 'monthsFrom' => 'required|numeric',
            // 'monthsTo' => 'required|numeric|gte:monthsFrom',
            // 'loan_percentage' => 'required|numeric|max:100',
            // 'tenure_effective_from' => 'required',
            'tenure_effective_to' => 'nullable',
        ];
    }
    public function messages()
    {
        return[
            'tenure.required' => 'Tenure is required',
            'plan_id.required' => 'Plan id is required',
            'plan_code.required' => 'Plan code is required',
            'monthsFrom.required' => 'Months from is required',
            'monthsTo.required' => 'Months to is required',
            'loan_percentage.required' => 'Loan percentage is required',
            'tenure_effective_from.required' => 'Date is required',
        ];
    }
}
