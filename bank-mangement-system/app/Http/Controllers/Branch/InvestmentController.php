<?php

namespace App\Http\Controllers\Branch;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Validator;
use Session;
use Redirect;
use URL;
use App\Models\User;
use App\Models\Member;
use App\Models\Plans;
use App\Models\Memberinvestments;
use App\Models\Memberinvestmentsnominees;
use App\Models\Memberinvestmentspayments;

class InvestmentController extends Controller
{

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */    
    public function __construct()
    {		
        $this->middleware('auth');
    }

    /**
     * Show Member investment list
     * Route: /branch/member/investment
     * Method: get 
     * @param  $id,$msg
     * @return  array()  Response
     */
    public function index($id)
    {
     
	   /* echo 'calling'; exit; */
		
		 if(!in_array('View Member Investment List', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 }
	   
	    $data['title']='Member | Investment';
        $data['memberDetail'] = Member::where('id',$id)->first(['id','member_id','first_name','last_name']);
        return view('templates.branch.investment_management.member_investment', $data);
    }

     /**
     * Fetch invest listing data by member id.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function investmentListing(Request $request)
    { 
        if ($request->ajax()) {
            $memberId=$request['member_id'];
            $data = Memberinvestments::with(['plan','memberCompany:id,member_id','member:id,first_name,last_name'])->where('customer_id',$memberId)->orderBy('id', 'DESC')->get();
            //print_r($data);die;
            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('company_name', function($row){
                $company_name = $row['company']->name;
                return $company_name;
               })
            ->addColumn('date', function($row){
                 $date = date("d/m/Y", strtotime($row->created_at));
                return $date;
            })
            ->rawColumns(['date'])
            ->addColumn('plan', function($row){
                $plan = $row['plan']->name;
                return $plan;
            })
            ->rawColumns(['plan'])

            ->addColumn('member_id', function($row){
                $member_id = $row['memberCompany']->member_id;
                return $member_id;
            })
            ->rawColumns(['member_id'])

            ->addColumn('member_name', function($row){
                $member_name = $row['member']->first_name.' '.$row['member']->last_name;
                return $member_name;
            })
            ->rawColumns(['member_name'])

            ->addColumn('account_no', function($row){
                $account_no = $row->account_number;
                return $account_no;
            })
            ->rawColumns(['account_no'])
            ->addColumn('amount', function($row){
                $amount = $row->deposite_amount;
                return $amount;
            })
            ->rawColumns(['amount'])
            ->addColumn('tenure', function($row){
                if($row->tenure){
                    if($row['plan']->plan_code == 709)
                    {
                        $tenure = $row->tenure. ' Years';
                        if($row->tenure==1)
                        $tenure = $row->tenure. ' Year';
                    }
                    else
                    {
                        $tenure = ($row->tenure*12). ' Months';
                    }

                }else{
                    $tenure = 'N/A';
                }
                return $tenure;
            })
            ->rawColumns(['tenure'])
            ->addColumn('action', function($row){
	            if( in_array('Member Investment Transaction', auth()->user()->getPermissionNames()->toArray() ) ) {
		            $url = URL::to("branch/member/passbook/transaction/" . $row->id . "/" . $row['plan']->plan_category_code);
		            $btn = '<a class="dropdown-item" href="' . $url . '" style="padding-top: 1px;"><i class="fas fa-eye text-default mr-2"></i></a>';
	            } else {
		            $btn = '';
	            }
	            return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }
    /**
     * Show Member investment list
     * Route: /branch/member/investment
     * Method: get 
     * @param  $id,$msg
     * @return  array()  Response
     */
    public function investmentDetail($id,$memberId)
    {
        $data['title']='Member | Investment';
        $data['memberDetail'] = Member::where('id',$memberId)->first(['id','member_id','first_name','last_name']);
        $data['investment'] = Memberinvestments::with('plan')->where('id',$id)->first();
        return view('templates.branch.investment_management.member_plan_detail', $data);
    }


    public function checkgstCharge(Request $request)
    {
        $detail = getBranchDetail($request->branchId)->state_id;
        //$globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$detail);
      
        $amount = 50;
        $member = \App\Models\Member::with('branch')->where('member_id',$request->memberid)->first();
        $getHeadSetting = \App\Models\HeadSetting::where('head_id',122)->first(); 
       // $globaldate = date('Y-m-d',strtotime('2022-07-01'));
        $detailss = $member['branch']->state_id;
        $globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$detail);

        $getGstSetting = \App\Models\GstSetting::where('state_id',$detailss)->where('applicable_date', '<=',$globaldate)->where('company_id',$request->company_id)->first(); 
        if(isset($getHeadSetting->gst_percentage) &&  !empty($getGstSetting) )
        {
            if($detail == $getGstSetting->state_id)
            {
                $gstAmount =  (($amount*$getHeadSetting->gst_percentage)/100)/2;
                $IntraState = true;
            }
            else{
                $gstAmount =  ($amount*$getHeadSetting->gst_percentage)/100;
                $IntraState = false;
            }
            $msg = true;
            
        }else{
            $IntraState = '';
            $msg = false;
            $gstAmount = 0;
        }
        return response()->json(['IntraState'=>$IntraState,'gstAmount'=>$gstAmount,'msg'=>$msg]);


    }
    public function checkgstChargeMember(Request $request)
    {
        $detail = getBranchDetail($request->branchId)->state_id;
        //$globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$detail);
      
        $amount = 50;
        $member = \App\Models\MemberCompany::with('branch')->where('member_id',$request->memberid)->first();
        $getHeadSetting = \App\Models\HeadSetting::where('head_id',122)->first(); 
       // $globaldate = date('Y-m-d',strtotime('2022-07-01'));
        $detailss = $member['branch']->state_id;
        $globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$detail);

        $getGstSetting = \App\Models\GstSetting::where('state_id',$detailss)->where('applicable_date', '<=',$globaldate)->where('company_id',$request->company_id)->first(); 
        if(isset($getHeadSetting->gst_percentage) &&  !empty($getGstSetting) )
        {
            if($detail == $getGstSetting->state_id)
            {
                $gstAmount =  (($amount*$getHeadSetting->gst_percentage)/100)/2;
                $IntraState = true;
            }
            else{
                $gstAmount =  ($amount*$getHeadSetting->gst_percentage)/100;
                $IntraState = false;
            }
            $msg = true;
            
        }else{
            $IntraState = '';
            $msg = false;
            $gstAmount = 0;
        }
        return response()->json(['IntraState'=>$IntraState,'gstAmount'=>$gstAmount,'msg'=>$msg]);


    }
}
