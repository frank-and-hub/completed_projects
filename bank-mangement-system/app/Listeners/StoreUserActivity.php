<?php

namespace App\Listeners;
use App\Events\UserActivity;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StoreUserActivity
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(UserActivity $event)
    {
        $details = $event->request->route()->getAction();
        
        $data = [
            'user_type' => (auth()->user()->role_id == 3) ? 1:0,
            'user_id' => auth()->user()->id,
            'module_name' => $details['controller'],
            'action_perform' => $event->action,
            'data' => $event->response

        ];
            \App\Models\UserActivity::create($data);
        
    }
}
