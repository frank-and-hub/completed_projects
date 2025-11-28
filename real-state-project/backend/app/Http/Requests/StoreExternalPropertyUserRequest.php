<?php

namespace App\Http\Requests;

use App\Models\Admin;
use App\Models\ExternalPropertyUser;
use Illuminate\Foundation\Http\FormRequest;

class StoreExternalPropertyUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'nullable|string|email|unique:' . ExternalPropertyUser::class . ',email',
            'password' => 'required|string',
            'phone' => 'nullable|string|numeric|min:8',
            'country' => 'nullable|string',
            'agencies' => 'array|nullable',
            'agencies.*' => 'nullable|exists:' . Admin::class . ',id'
        ];
    }
}
