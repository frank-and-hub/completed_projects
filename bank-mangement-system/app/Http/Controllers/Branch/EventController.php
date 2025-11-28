<?php

namespace App\Http\Controllers\Branch;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Event;
use App\Models\HolidaySettings;
use App\Models\Branch;

use App\Http\Controllers\Branch\CommanTransactionsController;

class EventController extends Controller
{

    public function index()
    {
        
		if(!in_array('Holidays', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 }
		
		$title = 'Holidays Calendar';
        $events = [];
        $sId = Branch::where('manager_id', Auth::user()->id)->first('state_id');
        $data = Event::where('state_id',$sId->state_id)->get();

        if($data->count()) {        
            foreach ($data as $key => $value) {  

                $events[] = \Calendar::event(
                    $value->title,
                    true,
                    new \DateTime($value->start_date),
                    new \DateTime($value->end_date.' +1 day'),
                    null,
                    [
                        'color' => '#f05050',
                    ]
                ); 
            }
        }
        $calendar = \Calendar::addEvents($events);
        return view('templates.branch.events.fullcalender', compact('calendar','title'));
    }

    /**
     * Add event on a date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function nextMonth(Request $request)
    {

        $month = $request->monthNumber;
        $nMonth =  $month+1;           
        $mCount = HolidaySettings::where('month_number',$nMonth)->count();

        if($mCount > 0)
        {
          return Response::json(['msg_type'=>'success','view' => 'next month available.']);
        }
        else
        {
            return Response::json(['msg_type'=>'error','view' => 'Month not available']);
        }
    }
}
