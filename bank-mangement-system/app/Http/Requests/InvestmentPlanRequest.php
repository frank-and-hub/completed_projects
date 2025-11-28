<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvestmentPlanRequest extends FormRequest
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
        $planId = $this->input('plan_type');
         switch ($planId){
                case "S" : 
                       $rules =  [
                        'amount' => 'required',
                        'investmentplan' => 'required',
                        'form_number' => 'required',
                        'memberid' => 'required',
                        'fn_first_name' => 'required',
                        'fn_relationship' => 'required',
                        'fn_gender' => 'required',
                        'fn_dob' => 'required',
                        'fn_percentage' => 'required',
                        'account_no' => 'unique:saving_accounts',
                    ];
                    break;
                case "D" : 
                           $rules =  [
                            'form_number' => 'required',
                            'amount' => 'required',
                            'payment-mode' => 'required',
                            'investmentplan' => 'required',
                            'memberid' => 'required',
                            'fn_first_name' => 'required',
                            'fn_relationship' => 'required',
                            'fn_gender' => 'required',
                            'fn_dob' => 'required',
                            'fn_percentage' => 'required',
                        ];
                        break;
                 
                case "F" : 
                         $rules =  [
                            'form_number' => 'required',
                            'amount' => 'required',
                            'payment-mode' => 'required',
                            'tenure' => 'required',
                            'investmentplan' => 'required',
                            'memberid' => 'required',
                            'fn_first_name' => 'required',
                            'fn_relationship' => 'required',
                            'fn_gender' => 'required',
                            'fn_dob' => 'required',
                            'fn_percentage' => 'required',
                        ];
                case "M" : 
                     $rules =  [
                        'form_number' => 'required',
                        'amount' => 'required',
                        'payment-mode' => 'required',
                        'tenure' => 'required',
                        'investmentplan' => 'required',
                        'memberid' => 'required',
                       
                    ];        
                
        }
        if(($this->input('plan_sub_category') != 'K' || $this->input('plan_sub_category') == NULL ) && $planId == 'M')
        {
           $rules['fn_first_name'] = 'required';
           $rules['fn_relationship'] = 'required';
           $rules['fn_gender'] = 'required';
           $rules['fn_dob'] = 'required';
           $rules['fn_percentage'] = 'required'; 
        }
        return $rules;
     
    }

    
}
