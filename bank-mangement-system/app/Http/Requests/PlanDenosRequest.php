<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanDenosRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'tenure' => 'required',
            'denomination' => 'required',
            'effective_from' => 'required',
        ];
    }

    public function messages()
    {
        return[
            'tenure.required' => 'Tenure is required',
            'denomination.required' => 'Denomination is required',
            'effective_from.required' => 'Select the date',
        ];
    }
}
