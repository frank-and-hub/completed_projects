<?php

namespace App\Listeners;
use App\Events\OD_Reflection;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Auth;
class StoreODReflection
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
    public function handle(OD_Reflection $event)
    {	
			$data['bank_ac_id']=$event->bankId;
			$data['current_amount']=$event->current_amount;
			$data['created_by']=$event->created_by;
			$data['created_by_id']=Auth::user()->id;			
			\App\Models\BankODLimits::where('bank_id',$event->bankId)->where('status',1)->update($data);
    }
}
