<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plans;
use App\Models\MoneyBackSetting;

class MoneyBackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug)
    {
        //--------------------------------------------- listing of our money back setting page 
        $plan = Plans::where('slug', $slug)->with('PlanTenures')->with('MoneyBack')->get(['id', 'name', 'plan_code'])->toArray() ?? "";
        $data['planName'] = $plan[0]['name'];
        $data['planCode'] = $plan[0]['plan_code'];
        $data['planId'] = $plan[0]['id'];
        $data['tenures'] = $plan[0]['plan_tenures'];
        $data['moneyBackSetting'] = $plan[0]['money_back'];
        $data['title'] = 'Money Back Setting';
        return view('templates.admin.py-scheme.moneyBackSetting', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // --------------------------------------------------------store new money back setting 
        $validated = $request->validate([
            'tenure' => 'required|numeric',
            'plan_id' => 'required|numeric',
            'plan_code' => 'required|numeric|',
            'months' => 'required|numeric|lte:tenure',
            'money_percentage' => 'required|numeric|max:100',
            'tenure_effective_from' => 'required',
            'tenure_effective_to' => 'nullable',
        ]);
        /*
      ----------------------------------------------before we store new data we validate that if already a data with same tenure and money back
      percentage exit or not if already exit then we redirect with an error messeage otherwise we save new data.
      */
      $checkValid = MoneyBackSetting::where('months', $request->months)->where('plan_code',$request->plan_code)->where('tenure',$request->tenure)->where('money_back_per', $request->money_percentage)->exists();

        if (!($checkValid)) {
            $moneyBackNew = new MoneyBackSetting;
            $moneyBackNew->tenure = $request->tenure;
            $moneyBackNew->plan_id = $request->plan_id;
            $moneyBackNew->plan_code = $request->plan_code;
            $moneyBackNew->months = $request->months;
            $moneyBackNew->money_back_per = $request->money_percentage;
            $moneyBackNew->created_by_id = Auth::user()->id;

            $date = str_replace('/', '-', $request->tenure_effective_from);
            $moneyBackNew->effective_from  = date('Y-m-d', strtotime($date));

            $date = str_replace('/', '-', $request->tenure_effective_to);
            $moneyBackNew->effective_to  = empty($request->tenure_effective_to) ? $request->tenure_effective_to : date('Y-m-d', strtotime($date));
            $moneyBackNew->save();
            return redirect()->back()->with('success', 'Money back settings  save successfully ');
        } else {
            return redirect()->back()->with('alert', 'This tenure already had same money back percentage');
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
            // 'months' => 'required|numeric|lte:tenure',
            'money_percentage' => 'required|numeric|max:100',
            // 'tenure_effective_from' => 'required|date',
            'tenure_effective_to' => 'nullable',
        ]);
        //------------------------------------------------before we update data we validate that with same tenure and same moenyback percentage data 
        //exits or not if data is not exit then we update data otherwise we check that if exit data have same id or not if id is same then we update data
        //otherwise redirect back with an error message .
        $checkValid  = MoneyBackSetting::where('months', $request->months)->where('plan_code',$request->plan_code)->where('money_back_per', $request->money_percentage)->where('id','!=',$request->editid)->get(['id'])->toArray();
        if (empty($checkValid) ) {
            $data = MoneyBackSetting::find($request->editid);
            // $data->tenure = $request->tenure;
            // $data->months = $request->months;
            $data->money_back_per = $request->money_percentage;
            // $date = str_replace('/', '-', $request->tenure_effective_from);
            // $data->effective_from  = date('Y-m-d', strtotime($date));
            $date = str_replace('/', '-', $request->tenure_effective_to);
            $data->effective_to  = empty($request->tenure_effective_to) ? $request->tenure_effective_to : date('Y-m-d', strtotime($date));
            $data->save();
            return redirect()->back()->with('success', 'Money back settings  save successfully ');
        } else {
            return redirect()->back()->with('alert', 'This tenure already had same money back percentage');
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
        //--------------------------------------soft delete of money back setting 
        $data = MoneyBackSetting::find($request['id']);
        $res = ($data->delete()) ?: 0;
        return  response()->json($res);
    }
    public function checkAvailablity(Request $request)
    {
        $data = [];
        // $data['monthTo'] =    MoneyBackSetting::select('month_to')->where('plan_code', $request->plan_code)->where('tenure', $request->tenure)->where('status', 1)->max('month_to');
        // $data['monthfrom'] =    MoneyBackSetting::select('month_from')->where('plan_code', $request->plan_code)->where('tenure', $request->tenure)->where('status', 1)->min('month_from');
        $data['dataa'] = MoneyBackSetting::select('months', 'tenure','effective_to')->where([
            ['plan_code', $request->plan_code],
            ['tenure', $request->tenure],
            ['months', $request->months],
            ['status', 1],
        ])->latest('created_at')->first();
      
        return json_encode($data);
    }
    public function status($id)
    {

        $data =   MoneyBackSetting::find($id);
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
