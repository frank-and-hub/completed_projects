<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use DB;
use App\Models\Event;
use App\Models\HolidaySettings;
use App\Models\States;
use App\Models\Branch;
use Illuminate\Support\Arr;
use App\Models\CommisionMonthEnd;
class EventController extends Controller
{

    public function index()
    {
	   if(check_my_permission( Auth::user()->id,"93") != "1"){
		  return redirect()->route('admin.dashboard');
		}
	    $title = 'Add Holidays';
        $holidaySettings = HolidaySettings::select('month_name')->where('year',date("Y"))->get();
        $holidayArray = json_decode(json_encode($holidaySettings), true);
        $narray = Arr::flatten($holidayArray);
        $months = array(1 => 'January',2 => 'February',3 => 'March',4 => 'April',5 => 'May',6 => 'June',7 => 'July',8 => 'August',9 => 'September',10 => 'October',11 => 'November',12 => 'December');
        return view('templates.admin.events.addevent',compact('title','months','narray'));
    }

    function array_flatten($array) { 
      if (!is_array($array)) { 
        return FALSE; 
      } 
      $result = array(); 
      foreach ($array as $key => $value) { 
        if (is_array($value)) { 
          $result = array_merge($result, array_flatten($value)); 
        } 
        else { 
          $result[$key] = $value; 
        } 
      } 
      return $result; 
    } 

    /**
     * Add event on a date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function add(Request $request)
    {

        $eDate = $request->eventDate;
        $eText = $request->eventText;
        $sId = $request->stateid;
        $time = strtotime($eDate);

        DB::beginTransaction();
        try {

            $states = States::select('id')->get();


            if($sId == 0){

              foreach ($states as $key => $state) {

                $data['state_id'] = $state->id;
                $data['title'] = $eText;
                $data['start_date'] = date("Y-m-d", strtotime( $eDate));
                $data['end_date'] = date("Y-m-d", strtotime( $eDate));
                $data['month'] = date("m",$time);
                $sql = Event::create($data); 
                $eId = $sql->id;
              }
            }else{
              $data['state_id'] = $sId;
              $data['title'] = $eText;
              $data['start_date'] = date("Y-m-d", strtotime( $eDate));
              $data['end_date'] = date("Y-m-d", strtotime( $eDate));
              $data['month'] = date("m",$time);
              $sql = Event::create($data);           
              $eId = $sql->id;
            }
        DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }

        if($eId)
        {
          return Response::json(['msg_type'=>'success','eId' => $eId,'date' => $data['start_date']]);
        }
        else
        {
            return Response::json(['view' => 'Somthing went wrong!','msg_type'=>'error']);
        }
    }

    /**
     * Remove event.
     *
     * @return \Illuminate\Http\Response
     */    
    public function getEvent(Request $request)
    {
        $eventDate = date("Y-m-d", strtotime($request->eventDate));
        $stateId = $request->state;
        if($stateId > 0){
          $events = Event::where('state_id', $stateId)->where('start_date', $eventDate)->where('end_date', $eventDate)->get();  
        }else{
          $events = Event::where('start_date', $eventDate)->where('end_date', $eventDate)->get();
        }
        $output = $this->unique_multi_array($events,'title');

        if($events->count())
        {
          return Response::json(['msg_type'=>'success','events'=>$output]);
        }
        else
        {
            return Response::json(['view' => 'Somthing went wrong!','msg_type'=>'error']);
        }
    }

    function unique_multi_array($array, $key) { 
      $temp_array = array(); 
      $i = 0; 
      $key_array = array(); 
      
      foreach($array as $val) { 
          if (!in_array($val[$key], $key_array)) { 
              $key_array[$i] = $val[$key]; 
              $temp_array[$i] = $val; 
          } 
          $i++; 
      } 
      return $temp_array; 
    }

    /**
     * Remove event.
     *
     * @return \Illuminate\Http\Response
     */    
    public function remove(Request $request)
    {
        $eId = $request->eId;
        DB::beginTransaction();
        try {
            $deleteLog = Event::where('id', $eId)->delete();
        DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }

        if($deleteLog)
        {
          return Response::json(['msg_type'=>'success']);
        }
        else
        {
            return Response::json(['view' => 'Somthing went wrong!','msg_type'=>'error']);
        }
    }

    /**
     * Save Holiday Setting.
     *
     * @return \Illuminate\Http\Response
     */    
    public function saveholidaysetting(Request $request)
    {
        $month = $request->month;
        $stateId = $request->monthstateid;

        if($stateId == 0){
          HolidaySettings::where('year', date('Y'))->delete(); 
        }else{
          HolidaySettings::where('state_id', $stateId)->where('year', date('Y'))->delete();
        }

        foreach ($month as $mkey => $value) {
            $states = States::select('id')->get();
            if($stateId == 0){
              foreach ($states as $key => $state) {
                $data['state_id'] = $state->id;
                if($mkey >= 1 && $mkey <= 9){
                  $mNumber = '0'.$mkey;
                }else{
                  $mNumber = $mkey;
                }
                $data['month_number'] = $mNumber;
                $data['year'] = date('Y');
                $data['month_name'] = $value;
                $data['is_active'] = 0;
                $sql = HolidaySettings::create($data); 
                $hId = $sql->id;
              }
            }else{
              $data['state_id'] = $stateId;
              if($mkey >= 1 && $mkey <= 9){
                $mNumber = '0'.$mkey;
              }else{
                $mNumber = $mkey;
              }
              $data['month_number'] = $mNumber;
              $data['year'] = date('Y');
              $data['month_name'] = $value;
              $data['is_active'] = 0;
              $sql = HolidaySettings::create($data); 
              $hId = $sql->id;
            }
        }

        return back()->with('success', 'Calendar updated successfully!');
    }

    /**
     * Get all cerated events.
     *
     * @return \Illuminate\Http\Response
     */    
    public function getAllEvent(Request $request)
    {
        $stateId = $request->state;
        if($stateId > 0){
          $events = Event::select('start_date')->where('state_id', $stateId)->get();  
        }else{
          $events = Event::select('start_date')->get();
        }
        if($events->count())
        {
          return Response::json(['msg_type'=>'success','events'=>$events]);
        }
        else
        {
            return Response::json(['view' => 'Somthing went wrong!','msg_type'=>'error']);
        }
    }

    /**
     * Get months according to state.
     *
     * @return \Illuminate\Http\Response
     */    
    public function getStateMonths(Request $request)
    {
        $stateId = $request->state;

        $holidays = HolidaySettings::select('id','month_name')->where('state_id', $stateId)->where('year', date('Y'))->get();

        if($holidays->count())
        {
          return Response::json(['msg_type'=>'success','holidays'=>$holidays]);
        }
        else
        {
            return Response::json(['view' => 'Months Not Found!','msg_type'=>'error']);
        }
    }

    /**
     * Get global date from branch.
     *
     * @return \Illuminate\Http\Response
     */    
    // public function getGlobalDate(Request $request)
    // {
    //   $branchId = $request->branchid;
    //   //$branch = Branch::select('state_id')->where('id',$branchId)->first();
    //   $globalDate = headerMonthAvailability(date('d'),date('m'),date('Y'),$branchId);
    //   $globalDateTime = checkMonthAvailability(date('d'),date('m'),date('Y'),$branchId);
    //   if($globalDate)
    //   {
    //     return Response::json(['msg_type'=>'success','globalDate'=>$globalDate,'globalDateTime'=>$globalDateTime]);
    //   }
    //   else
    //   {
    //       return Response::json(['view' => 'Date Not Found!','msg_type'=>'error']);
    //   }            
    // }

    public function getGlobalDate(Request $request)
  {
    $branchId = $request->branchid;
    //$branch = Branch::select('state_id')->where('id',$branchId)->first();
    $globalDate = headerMonthAvailability(date('d'), date('m'), date('Y'), $branchId);
    $globalDateTime = checkMonthAvailability(date('d'), date('m'), date('Y'), $branchId);

    $mNumber = date('m', strtotime($globalDateTime));
    $yNumber = date('Y', strtotime($globalDateTime));

    if ($mNumber == 1) {
      $lastmont = 12;
      $lastyear = $yNumber - 1;
    } else {
      $lastmont = $mNumber - 1;
      $lastyear = $yNumber;
    }
    $commissionProcess = 0;
    $commissionDetail = CommisionMonthEnd::where('month', $lastmont)->where('year', $lastyear)->count();  
    $commissionDetailPocess = CommisionMonthEnd::where('month', $lastmont)
                            ->where('year', $lastyear) 
                            ->orderBy('id', 'desc')
                            ->first(); 
                            
   // pd($commissionDetail);
    if ($commissionDetail == 0) { 
       $commissionProcess = 1;
    }
    else{
      $commissionProcess = 2;
    }
    if($commissionDetailPocess)
    {
      
      if ($commissionDetailPocess->commission_process == 1) 
      { 
        $commissionProcess = 3;
      } 
      if ($commissionDetailPocess->leadger_created == 1) 
      { 
        $commissionProcess = 4;
      } 
    }
    
    if ($globalDate) {
      return Response::json(['msg_type' => 'success', 'globalDate' => $globalDate, 'globalDateTime' => $globalDateTime, 'commissionProcess'=> $commissionProcess]);
    } else {
      return Response::json(['view' => 'Date Not Found!', 'msg_type' => 'error']);
    }
  }
}
