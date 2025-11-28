<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkoutPlanRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'program_id' => 'required|exists:ft_workout_programs,id',
            'start_date' => 'required|date|after_or_equal:today',
            'user_id' => 'sometimes|required|exists:ft_users,id'
        ];
    }
}