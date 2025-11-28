<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanCategoryRequest extends FormRequest
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
            'name' => 'required|unique:plan_categories|regex:/^[a-z\d\-_\s]+$/i',
            'plan_code' => 'required|unique:plan_categories,code|max:1|regex:/^[a-zA-Z]+$/u',
        ];
    }

    public function messages()
    {
        return[
            'name.required' => 'Name is required',
            'name.unique' => 'Name is already exists',
            'name.regex' => 'Use only alphabets',
            'plan_code.required' => 'Plan code is required',
            'plan_code.unique' => 'Plan code is already exists',
            'plan_code.max' => 'Please enter no more than 1 character',
            'plan_code.regex' => 'Use only alphabets',
        ];
    }
}