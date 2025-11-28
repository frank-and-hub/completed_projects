<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuestionAnswerUser;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roles = [
            1 => 'Admin',
            2 => 'User'
        ];
        // $totalQuestions = Question::count();

        $isProfileCompleted = QuestionAnswerUser::where('user_id', $this->id)->exists();

        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'email' => $this->email,
            'profile_photo' => $this->profile_photo ? asset($this->profile_photo) : null,
            'role' => null,
            'status' => $this->status,
            'language' => $this->language,
            'device_type' => $this->device_type,
            'device_id' => $this->device_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_profile_completed' => $isProfileCompleted ? 1 : 0,
            // 'unread_notifications_count' => method_exists($this, 'getUnreadNotificationsCount') 
            //     ? $this->getUnreadNotificationsCount()
            //     : 0,
        ];
    }
}
