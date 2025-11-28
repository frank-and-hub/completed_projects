<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Plans;
use App\Models\LoanAgainstDeposit;
use App\Http\Requests\LoanDepositRequest;

class LoanAgainstDepositController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug)
    {
        //--------------------------------------------- listing of our loan against deposit page 
        $plan = Plans::where('slug', $slug)->with('PlanTenures')->with('LoanAgainst')->get(['id', 'name', 'plan_code'])->toArray() ?? "";
        $data['planName'] = $plan[0]['name'];
        $data['planCode'] = $plan[0]['plan_code'];
        $data['planId'] = $plan[0]['id'];
        $data['tenures'] = $plan[0]['plan_tenures'];
        $data['loanAgainst'] = $plan[0]['loan_against'];
        $data['title'] = 'Loan Against Deposit';
        return view('templates.admin.py-scheme.loanAgainstDeposit', $data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LoanDepositRequest  $request)
    {
        // --------------------------------------------------------store new Loan Deposit 

        /*
      ----------------------------------------------before we store new data we validate that if already a data with same tenure and Loan deposit percentage exit or not if already exit then we redirect with an error messeage otherwise we save new data.
      */
        $checkValid = LoanAgainstDeposit::where('tenure', $request->tenure)->where('loan_per', $request->loan_percentage)->exists();
        if (!($checkValid)) {
            $loanDepositNew = new LoanAgainstDeposit;
            $loanDepositNew->tenure = $request->tenure;
            $loanDepositNew->plan_id = $request->plan_id;
            $loanDepositNew->month_to = $request->monthsTo;
            $loanDepositNew->plan_code = $request->plan_code;
            $loanDepositNew->created_by_id = Auth::user()->id;
            $loanDepositNew->month_from = $request->monthsFrom;
            $loanDepositNew->created_at_default = $request->created_at;

            $date = str_replace('/', '-', $request->tenure_effective_to);
            $loanDepositNew->effective_to  = empty($request->tenure_effective_to) ? $request->tenure_effective_to : date('Y-m-d', strtotime($date));

            $date = str_replace('/', '-', $request->tenure_effective_from);
            $loanDepositNew->effective_from  = date('Y-m-d', strtotime($date));

            $loanDepositNew->loan_per = $request->loan_percentage;
            $loanDepositNew->save();
            return redirect()->back()->with('success', 'Loan deposit  save successfully ');
        } else {
            return redirect()->back()->with('alert', 'This tenure already has same Loan deposit percentage');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(LoanDepositRequest $request)
    {
        //exits or not if data is not exit then we update data otherwise we check that if exit data have same id or not if id is same then we update data
        //otherwise redirect back with an error message .
        $checkValid  = LoanAgainstDeposit::where('tenure', $request->tenure)->where('loan_per', $request->loan_percentage)->get(['id'])->toArray();
        if (empty($checkValid) || ($checkValid[0]['id'] == $request->editid)) {
            $data = LoanAgainstDeposit::find($request->editid);
            // $data->tenure = $request->tenure;
            // $data->month_from = $request->monthsFrom;
            // $data->month_to = $request->monthsTo;
            $data->loan_per = $request->loan_percentage;
            // $date = str_replace('/', '-', $request->tenure_effective_from);
            // $data->effective_from  = date('Y-m-d', strtotime($date));
            $date = str_replace('/', '-', $request->tenure_effective_to);
            $data->effective_to = empty($request->tenure_effective_to) ? $request->tenure_effective_to : date('Y-m-d', strtotime($date));
            $data->save();
            return redirect()->back()->with('success', 'Loan against deposit  save successfully ');
        } else {
            return redirect()->back()->with('alert', 'This tenure already had same loan against deposit percentage');
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
        //--------------------------------------soft delete of loan against deposit 
        $data = LoanAgainstDeposit::find($request['id']);
        $res = ($data->delete()) ?: 0;
        return  response()->json($res);
    }
    public function checkAvailablity(Request $request)
    {
        $data = [];
        $data['monthTo'] =    LoanAgainstDeposit::select('month_to')->where('plan_code', $request->plan_code)->where('tenure', $request->tenure)->where('status', 1)->max('month_to');
        $data['monthfrom'] =    LoanAgainstDeposit::select('month_from')->where('plan_code', $request->plan_code)->where('tenure', $request->tenure)->where('status', 1)->min('month_from');
        $data['dataa'] = LoanAgainstDeposit::select('month_from', 'month_to', 'tenure','effective_to')->where([
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

        $data =   LoanAgainstDeposit::find($id);
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

    // public function getData(){
    //     $data = LoanAgainstDeposit::where('member_id',$request->id)->get();
    //     return json_encode($data);
    // }
}
