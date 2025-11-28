<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $usersTable = config('tables.users');

        return [
            'fullname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique($usersTable)->whereNull('deleted_at'),
            ],
            'password' => 'required|min:6|confirmed',
            'profile_photo' => 'nullable|string', // or 'image' if uploading files
            'language' => 'required|in:1,2', // 1 => English, 2 => Hindi
            'device_type' => 'required|in:android,ios',
            'device_id' => 'required|string',
            'role' => 'required|in:1,2,3,4', // Use integer codes: 1 = Admin, etc.
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('role') === 'child') {
            $this->merge([
                'role' => 2, 
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'fullname' => 'first name',
            'email' => 'email address',
            'password' => 'password',
            'profile_photo' => 'profile photo',
            'device_type' => 'device type',
            'device_id' => 'device ID',
            'language' => 'language',
            'role' => 'role',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}
