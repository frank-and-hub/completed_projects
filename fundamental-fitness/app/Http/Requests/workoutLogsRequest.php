<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use App\Models\MesoCycle;
use App\Models\Week;
use Illuminate\Foundation\Http\FormRequest;

class workoutLogsRequest extends FormRequest
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
            'meso_id' => 'required|exists:' . MesoCycle::class . ',id',
            'week_id' => 'required|exists:' . Week::class . ',id',
            'day_id'  => 'nullable|numeric|in:0,1,2,3,4,5,6',
            // 'exercise_id' => 'nullable|numeric|exists:' . Exercise::class . ',id',
            'exercise_id' => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'meso_id.required' => 'The meso cycle is required.',
            'meso_id.exists'   => 'The selected meso cycle is invalid.',

            'week_id.required' => 'The week is required.',
            'week_id.exists'   => 'The selected week is invalid.',

            'day_id.numeric'   => 'The day must be a valid number.',
            'day_id.in'        => 'The selected day must be between 1 and 6.',

            'exercise_id.numeric' => 'The exercise ID must be a valid number.',
            'exercise_id.exists'  => 'The selected exercise is invalid.',
        ];
    }
}
