<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\HolidayNotificationSetting;
use App\Models\HolidaySettingLogs;





class AllHolidayCronsController extends Controller
{
    public function index(){
        if(check_my_permission(Auth::user()->id,"352") != "1")
        {
            return redirect()->route('admin.dashboard');
         }
        $data['title'] = 'All Holidays Crons | Listing';
        $data['holidays'] = HolidayNotificationSetting::get();

        return view('templates.admin.allHolidaysCrons.index',$data);
    }

    // public function save(Request $request){
    //     if(isset($request->id)){
    //         $crn = HolidayNotificationSetting::where('id',$request->id)->first();
    //         $crn->cron_date_time = $request->effective_to_model;
    //         $crn->update();
    //         return response()->json(['msg' =>'true']);

    //     }else{
    //         $saveCron = new HolidayNotificationSetting();
    //         $saveCron->title = $request->corn_title;
    //         $saveCron->cron_date_time = $request->effective_to_model;
    //         $saveCron->status = 1;
    //         $saveCron->templateId = $request->templateId;
    //         $saveCron->cron_name = $request->cron_name;
            
    
    //         $slug = Str::slug($request->corn_title);
    
    //         // Check if the slug is unique
    //         $count = HolidayNotificationSetting::where('slug', $slug)->count();
    //         if ($count > 0) {
                
    //             $slug .= '-' . uniqid();
    //         }
    
    //         $saveCron->slug = $slug;
    //         $saveCron->created_by_id = auth()->user()->id;
            
    //         $saveCron->save();
    //         return response()->json(['msg' =>'true']);
    //     }
        
    // }

    public function save(Request $request){

     
        try {

            $validator = Validator::make($request->all(), [
                'corn_title' => 'required',
                'effective_to_model' => 'required',
                'templateId' => 'required',
                'cron_name' => 'required',
            ]);

            
    
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()]);
            }
    
            if(isset($request->id)){

                $crn = HolidayNotificationSetting::findOrFail($request->id);
                
                
            // $crn->message = $crn->title . ' Cron date has been changed by ' . auth()->user()->username . ' on ' . date('Y-m-d');
                $crn->cron_date = date("Y-m-d", strtotime(convertDate($request['effective_to_model'])));
              $crn->updated_by = auth()->user()->username;
                $crn->update();
                    
                
               $title = $crn->title  . ' Updated';
               $description =  $crn->title . ' Cron date has been changed by ' . auth()->user()->username . ' on ' . date('d/m/Y');
               $user_id = auth()->user()->id;
               $holiday_table_id = $crn->id;
                
            

                $this->cronLogsSave($title, $description, $user_id,$holiday_table_id);
               
            } else {
                $saveCron = new HolidayNotificationSetting();
                $saveCron->title = $request->corn_title;
                $saveCron->cron_date =  date("Y-m-d", strtotime(convertDate($request->input('effective_to_model'))));
                $saveCron->status = 1;
                $saveCron->templateId = $request->templateId;
                $saveCron->cron_name = $request->cron_name;
                $saveCron->message = $request->message;
                $saveCron->created_by = auth()->user()->username;
    
                $slug = Str::slug($request->corn_title);
    
                // Check if the slug is unique
                $count = HolidayNotificationSetting::where('slug', $slug)->count();
                if ($count > 0) {
                    $slug .= '-' . uniqid();
                }
    
                $saveCron->slug = $slug;
                $saveCron->created_by_id = auth()->user()->id;
                $saveCron->save();


                $title = $request->corn_title . ' Created';
                $description =  $request->corn_title . ' Cron  has been created by ' . auth()->user()->username . ' on ' . date('d/m/Y');
                $user_id = auth()->user()->id;
                $holiday_table_id = $saveCron->id;

                $this->cronLogsSave($title, $description, $user_id,$holiday_table_id);
              
            }
    
            return response()->json(['msg' => 'true']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            // Log the error or handle it in any other appropriate way
            return response()->json(['msg' => $e->getMessage()]);
        }
    }

    public function cronLogs($id){
        $data['logs'] = HolidaySettingLogs::where('holiday_id',$id)->orderBy('id', 'desc')->get();
        $data['title'] = HolidayNotificationSetting::where('id',$id)->first('title');
        
        $data['title'] = $data['title']->title . ' Logs';
   

        return view('templates.admin.allHolidaysCrons.logsDetals',$data);

 

    }

    private function cronLogsSave($title, $description, $user_id,$holiday_table_id){

       

        $saveLogs = new HolidaySettingLogs();
        $saveLogs->title = $title;
        $saveLogs->description = $description;
        $saveLogs->user_id = $user_id;
        $saveLogs->holiday_id = $holiday_table_id;
        $saveLogs->save();

    }


    public function cronStatus(Request $request){
        
        $getData = HolidayNotificationSetting::find($request->id);
        if($getData->status == 1){
            $getData->status = 0;
            $previousStatus = 'InActive';
        }else{
            $getData->status = 1;
            $previousStatus = 'Active';


        }

        $getData->update();
        $title = $getData->title. ' status Changed';
        $description =  $getData->title . ' Cron status has been changed to ' .$previousStatus .' by '  .auth()->user()->username . ' on ' . date('d/m/Y');

        $user_id = auth()->user()->id;
        $holiday_table_id = $getData->id;
        
        $this->cronLogsSave($title, $description, $user_id,$holiday_table_id);

        return response()->json(['msg' => 'true']);

    }

}
