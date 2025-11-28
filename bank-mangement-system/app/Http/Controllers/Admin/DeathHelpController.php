<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plans;
use App\Models\DeathHelpSetting;

class DeathHelpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug)
    {
        //--------------------------------------------- listing of our death help setting page 
        $plan = Plans::where('slug', $slug)->with('PlanTenures')->with('DeathHelpSettin')->get(['id', 'name', 'plan_code'])->toArray() ?? "";
        $data['planName'] = $plan[0]['name'];
        $data['planCode'] = $plan[0]['plan_code'];
        $data['planId'] = $plan[0]['id'];
        $data['tenures'] = $plan[0]['plan_tenures'];
        $data['deathHelp'] = $plan[0]['death_help_settin'];
        $data['title'] = 'Death Help Setting';
        return view('templates.admin.py-scheme.deathHelp', $data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // --------------------------------------------------------store new death help setting 
        $validated = $request->validate([
            'tenure' => 'required|numeric',
            'plan_id' => 'required|numeric',
            'plan_code' => 'required|numeric|',
            'monthsFrom' => 'required|numeric',
            'monthsTo' => 'required|numeric|gte:monthsFrom',
            'death_help_percentage' => 'required|numeric|max:100',
            'tenure_effective_from' => 'required',
            'tenure_effective_to' => 'nullable',
        ]);
        /*
      ----------------------------------------------before we store new data we validate that if already a data with same tenure and death help
      percentage exit or not if already exit then we redirect with an error messeage otherwise we save new data.
      */
        $checkValid = DeathHelpSetting::where('tenure', $request->tenure)->where('death_help_per', $request->death_help_percentage)->exists();
        if (!($checkValid)) {
            $deathHelpNew = new DeathHelpSetting;
            $deathHelpNew->tenure = $request->tenure;
            $deathHelpNew->plan_id = $request->plan_id;
            $deathHelpNew->month_to = $request->monthsTo;
            $deathHelpNew->plan_code = $request->plan_code;
            $deathHelpNew->created_by_id = Auth::user()->id;
            $deathHelpNew->month_from = $request->monthsFrom;
            $deathHelpNew->created_at_default = $request->created_at;
            $date = str_replace('/', '-', $request->tenure_effective_to);
            $deathHelpNew->effective_to  = (empty($request->tenure_effective_to)) ? $request->tenure_effective_to : date('Y-m-d', strtotime($date));
            $date = str_replace('/', '-', $request->tenure_effective_from);
            $deathHelpNew->effective_from  = date('Y-m-d', strtotime($date));
            $deathHelpNew->death_help_per = $request->death_help_percentage;
            $deathHelpNew->save();
            return redirect()->back()->with('success', 'Death Help settings  save successfully ');
        } else {
            return redirect()->back()->with('alert', 'This tenure already has same death help percentage');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // -----------------------------------------------update existing data 
        $validated = $request->validate([
            // 'tenure' => 'required|numeric',
            // 'plan_id' => 'required|numeric',
            // 'plan_code' => 'required|numeric|',
            // 'monthsFrom' => 'required|numeric',
            // 'monthsTo' => 'required|numeric|gte:monthsFrom',
            'death_help_percentage' => 'required|numeric|max:100',
            // 'tenure_effective_from' => 'required',
            'tenure_effective_to' => 'nullable',
        ]);
        //------------------------------------------------before we update data we validate that with same tenure and same moenyback percentage data 
        //exits or not if data is not exit then we update data otherwise we check that if exit data have same id or not if id is same then we update data
        //otherwise redirect back with an error message .
        $checkValid  = DeathHelpSetting::where('tenure', $request->tenure)->where('death_help_per', $request->death_help_percentage)->get(['id'])->toArray();
        if (empty($checkValid) || ($checkValid[0]['id'] == $request->editid)) {
            $data = DeathHelpSetting::find($request->editid);
            // $data->tenure = $request->tenure;
            // $data->month_from = $request->monthsFrom;
            // $data->month_to = $request->monthsTo;
            $data->death_help_per = $request->death_help_percentage;
            // $date = str_replace('/', '-', $request->tenure_effective_from);
            // $data->effective_from  = date('Y-m-d', strtotime($date));
            $date = str_replace('/', '-', $request->tenure_effective_to);
            $data->effective_to  = (empty($request->tenure_effective_to)) ? $request->tenure_effective_to : date('Y-m-d', strtotime($date));
            $data->save();
            return redirect()->back()->with('success', 'Death Help settings  save successfully ');
        } else {
            return redirect()->back()->with('alert', 'This tenure already had same death help percentage');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //--------------------------------------soft delete of death help setting 
        $data = DeathHelpSetting::find($request['id']);
        $res = ($data->delete()) ?: 0;
        return  response()->json($res);
    }
    public function checkAvailablity(Request $request)
    {
        $data = [];
        $data['monthTo'] =    DeathHelpSetting::select('month_to')->where('plan_code', $request->plan_code)->where('tenure', $request->tenure)->where('status', 1)->max('month_to');
        $data['monthfrom'] =    DeathHelpSetting::select('month_from')->where('plan_code', $request->plan_code)->where('tenure', $request->tenure)->where('status', 1)->min('month_from');
        $data['dataa'] = DeathHelpSetting::select('month_from', 'month_to', 'tenure','effective_to')->where([
            ['plan_code', $request->plan_code],
            ['tenure', $request->tenure],
            ['month_from', $request->monthsFrom],
            ['month_to', $request->monthsTo],
            ['status', 1],
        ])->latest('created_at')->first();
      
        return json_encode($data);
    }
    public function status($id)
    {

        $data =   DeathHelpSetting::find($id);
        if ($data->status) {
            $data->status = 0;
            $data->effective_to =  date("Y/m/d");
        }
        if ($data->save()) {
            return  redirect()->back()->with('success', 'Status change successfully!');
        } else {
            return  redirect()->back()->with('alert', 'Sorry there was an issue !');
        }
    }
}
