<?php
namespace App\Observers;

use App\Models\User;
use App\Events\UserUpdated;

class UserObserver
{
    public function updated(User $user)
    {
        event(new UserUpdated($user));
    }
}