<?php

namespace App\Http\Controllers\Branch\Report;

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Daybook; 
use App\Models\Branch;  
use App\Models\Transcation; 
use App\Models\Memberinvestments; 
use App\Models\Member; 
use App\Models\AllHeadTransaction;
use App\Models\Memberloans;
use App\Models\SavingAccount;
use App\Models\LoanDayBooks;

use App\Models\Grouploans;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Email;
use App\Services\Sms;
use Illuminate\Support\Facades\Schema;
use App\Models\Plans; 

/*
    |---------------------------------------------------------------------------
    | Branch Panel -- Report Management ReportController
    |--------------------------------------------------------------------------
    |
    | This controller handles Employee all functionlity.
*/

class AssociateReportController extends Controller
{
    /**
     * Create a new controller instance.
     * @return void
     */

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }

    /**
     * Show Daybook report.
     * Route: /branch/report/daybook 
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {
       
	     
	   
	    $data['title']='Associate Report | Daybook Report'; 
        $data['branch'] = Branch::where('status',1)->get();
        $data['zone'] = Branch::where('status',1)->distinct('zone')->get(); 
         
        
        return view('templates.branch.associate_report.index', $data);
    }


    /**
     *  Associate Business detail.
     * Route: /branch/report/associate_business 
     * Method: get 
     * @return  array()  Response
     */
    public function associateBusinessReport()
    {
        
		if(!in_array('Associate Business Report', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 }
		
		$data['title']='Report | Associate Business Report (Branch Wise)'; 
        $data['branch'] = Branch::where('status',1)->get();
        $data['zone'] = Branch::where('status',1)->select('zone')->groupBy('zone')->get();
        
         
      //dd($data);
			return view('templates.branch.associate_report.associate_business', $data);
    }

 /**
     * GetAssociate Business list
     * Route: ajax call from - /admin/report/associate_business 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateBusinessList(Request $request)
    { 
        if ($request->ajax()) {
        
// fillter array 
       $arrFormData = array();   
        //  
        $arrFormData['start_date'] = $request->start_date;
        $arrFormData['end_date'] = $request->end_date;
        $arrFormData['branch_id'] = $request->branch_id;  
        $arrFormData['is_search'] = $request->is_search; 

        $arrFormData['zone'] = $request->zone; 
        $arrFormData['region'] = $request->region; 
        $arrFormData['sector'] = $request->sector; 
        $arrFormData['associate_code'] = $request->associate_code; 

        if($arrFormData['start_date'] !=''){
            $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
        }
        else{
            $startDate='';
        }

        if($arrFormData['end_date'] !=''){
             $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
        }
        else {
            $endDate='';
        }

        if($arrFormData['branch_id']!='') {
            $branch_id=$arrFormData['branch_id'];
        }
        else {
            $branch_id='';
        }
        $branch_id='';



            $data = Member::with('associate_branch')->where('member_id','!=','9999999')->where('is_associate',1);

    /******* fillter query start ****/        
           if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            {
              if($arrFormData['branch_id'] !=''){
                    $id=$arrFormData['branch_id'];
                    $data=$data->where('associate_branch_id','=',$id);
                }

                if($arrFormData['zone'] !=''){
                    $zone=$arrFormData['zone'];
                    $data=$data->whereHas('associate_branch', function ($query) use ($zone) {
                      $query->where('branch.zone',$zone);
                    });
                }
                if($arrFormData['region'] !=''){
                    $region=$arrFormData['region'];
                    $data=$data->whereHas('associate_branch', function ($query) use ($region) {
                      $query->where('branch.regan',$region);
                    });
                }

                if($arrFormData['sector'] !=''){
                    $sector=$arrFormData['sector'];
                    $data=$data->whereHas('associate_branch', function ($query) use ($sector) {
                      $query->where('branch.sector',$sector);
                    });
                }
                if($arrFormData['associate_code'] !=''){
                    $associate_code=$arrFormData['associate_code'];
                    $data=$data->where('associate_no','=',$associate_code);
                }
            }
            
    /******* fillter query End ****/   

            $data1=$data->orderby('associate_join_date','ASC')->get();
            $count=count($data1);

            $data=$data->orderby('associate_join_date','ASC')->offset($_POST['start'])->limit($_POST['length'])->get();
                   
            $totalCount = Member::where('member_id','!=','9999999')->where('is_associate',1)->count();
                    
            $sno=$_POST['start'];
            $rowReturn = array(); 

            foreach ($data as $row)
            {  
                $sno++;

                $associate_id=$row->id;
                $planDaily=getPlanID('710')->id;

                $dailyId=array($planDaily);

                $planSSB=getPlanID('703')->id;


                $planKanyadhan=getPlanID('709')->id;
                $planMB=getPlanID('708')->id;
                $planFRD=getPlanID('707')->id;
                $planJeevan=getPlanID('713')->id;  
                $planRD=getPlanID('704')->id;
                $planBhavhishya=getPlanID('718')->id;

                $monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);


                $planMI=getPlanID('712')->id;
                $planFFD=getPlanID('705')->id;
                $planFD=getPlanID('706')->id;

                $fdId=array($planMI,$planFFD,$planFD);

                $val['DT_RowIndex']=$sno;
                $val['join_date']=date("d/m/Y", strtotime($row->associate_join_date));
                $val['branch']=$row['associate_branch']->name;

                $val['branch_code']=$row['associate_branch']->branch_code;
                $val['sector_name']=$row['associate_branch']->sector;
                $val['region_name']=$row['associate_branch']->regan;
                $val['zone_name']=$row['associate_branch']->zone;


                $val['member_id']=$row->associate_no;
                $val['name']=$row->first_name.' '.$row->last_name;
                $val['cadre']=getCarderName($row->current_carder_id);

                $val['daily_new_ac']=investNewAcCount($associate_id,$startDate,$endDate,$planDaily,$branch_id);
                $val['daily_deno_sum']=investNewDenoSum($associate_id,$startDate,$endDate,$planDaily,$branch_id);
                $val['daily_renew_ac']=investRenewAc($associate_id,$startDate,$endDate,$dailyId,$branch_id);
                $val['daily_renew']=investRenewAmountSum($associate_id,$startDate,$endDate,$dailyId,$branch_id);

                $val['monthly_new_ac']=investNewAcCountType($associate_id,$startDate,$endDate,$monthlyId,$branch_id);
                $val['monthly_deno_sum']=investNewDenoSumType($associate_id,$startDate,$endDate,$monthlyId,$branch_id);
                $val['monthly_renew_ac']=investRenewAc($associate_id,$startDate,$endDate,$monthlyId,$branch_id);
                $val['monthly_renew']=investRenewAmountSum($associate_id,$startDate,$endDate,$monthlyId,$branch_id);

                $val['fd_new_ac']=investNewAcCountType($associate_id,$startDate,$endDate,$fdId,$branch_id);
                $val['fd_deno_sum']=investNewDenoSumType($associate_id,$startDate,$endDate,$fdId,$branch_id);
              /*  $val['fd_renew_ac']=investRenewAc($associate_id,$startDate,$endDate,$fdId,$branch_id);
                $val['fd_renew']=investRenewAmountSum($associate_id,$startDate,$endDate,$fdId,$branch_id);*/

                $val['ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$startDate,$endDate,$branch_id,1);
                $val['ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$startDate,$endDate,$branch_id,1);
                $val['ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$startDate,$endDate,$branch_id,2);
                $val['ssb_renew']=totalInvestSSbAmtByType($associate_id,$startDate,$endDate,$branch_id,2);

                $sum_ni_ac=$val['daily_new_ac']+$val['monthly_new_ac']+$val['fd_new_ac']+$val['ssb_new_ac'];   
                $sum_ni_amount=$val['daily_deno_sum']+$val['monthly_deno_sum']+$val['fd_deno_sum']+$val['ssb_deno_sum'];

                $val['total_ni_ac']=$sum_ni_ac; 
                $val['total_ni_amount']=number_format((float)$sum_ni_amount, 2, '.', '');

                $sum_renew_ac=$val['daily_renew_ac']+$val['monthly_renew_ac']+$val['ssb_renew_ac'];   
                $sum_renew_amount=$val['daily_renew']+$val['monthly_renew']+$val['ssb_renew']; 

                $val['total_ac']=$sum_renew_ac;
                $val['total_amount']=number_format((float)$sum_renew_amount, 2, '.', '');

                $val['other_mt']=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,11);
                $val['other_stn']=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,12);
                $ni_m=$val['daily_deno_sum']+$val['monthly_deno_sum']+$val['fd_deno_sum'];
                
                $tcc_m=$val['daily_deno_sum']+$val['monthly_deno_sum']+$val['fd_deno_sum']+$val['daily_renew']+$val['monthly_renew'];
                
                $tcc=$val['daily_deno_sum']+$val['monthly_deno_sum']+$val['fd_deno_sum']+$val['ssb_deno_sum']+$val['daily_renew']+$val['monthly_renew']+$val['ssb_renew'];
                
                $val['ni_m']=number_format((float)$ni_m, 2, '.', '');
                $val['ni']=number_format((float)$sum_ni_amount, 2, '.', '');
                $val['tcc_m']=number_format((float)$tcc_m, 2, '.', '');
                $val['tcc']=number_format((float)$tcc, 2, '.', '');

                $val['loan_ac']=totalLoanAc($associate_id,$startDate,$endDate,$branch_id);
                $val['loan_amount']=totalLoanAmount($associate_id,$startDate,$endDate,$branch_id);

                $val['loan_recovery_ac']=totalRenewLoanAc($associate_id,$startDate,$endDate,$branch_id);
                $val['loan_recovery_amount']=totalRenewLoanAmount($associate_id,$startDate,$endDate,$branch_id);

                

                $val['new_associate']=memberCountByType($associate_id,$startDate,$endDate,$branch_id,1,0);
                $val['total_associate']=memberCountByType($associate_id,$startDate,$endDate,$branch_id,1,1);

                $val['new_member']=memberCountByType($associate_id,$startDate,$endDate,$branch_id,0,0);
                $val['total_member']=memberCountByType($associate_id,$startDate,$endDate,$branch_id,0,1);                

            $rowReturn[] = $val; 
          } 
        //  print_r($rowReturn);die;
          $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );

          return json_encode($output);
        }
    }





    /**
     * Associate Business  summary detail.
     * Route: /branch/report/associate_business_summary 
     * Method: get 
     * @return  array()  Response
     */
    public function associateBusinessSummaryReport()
    {
        if(!in_array('Associate Business Summary Report', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 }
		
		$data['title']='Report | Associate Business Summary Report'; 
        $data['branch'] = Branch::where('status',1)->get();
        $data['zone'] = Branch::where('status',1)->select('zone')->groupBy('zone')->get();
        
         
        
        return view('templates.branch.associate_report.associate_business_summary', $data);
    }


    /**
     * Associate Business summary  list
     * Route: ajax call from - /branch/report/associate_business_summary 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateBusinessSummaryList(Request $request)
    { 
        if ($request->ajax()) {
        
// fillter array 
       $arrFormData = array();   
        //  
        $arrFormData['start_date'] = $request->start_date;
        $arrFormData['end_date'] = $request->end_date;
        $arrFormData['branch_id'] = $request->branch_id;  
        $arrFormData['is_search'] = $request->is_search; 

        $arrFormData['zone'] = $request->zone; 
        $arrFormData['region'] = $request->region; 
        $arrFormData['sector'] = $request->sector; 

        $arrFormData['associate_code'] = $request->associate_code; 

        if($arrFormData['start_date'] !=''){
            $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
        }
        else{
            $startDate='';
        }

        if($arrFormData['end_date'] !=''){
             $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
        }
        else {
            $endDate='';
        }

        if($arrFormData['branch_id']!='') {
            $branch_id=$arrFormData['branch_id'];
        }
        else {
            $branch_id='';
        }



            $data = Member::with('associate_branch')->where('member_id','!=','9999999')->where('is_associate',1);

    /******* fillter query start ****/        
           if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            {
              if($arrFormData['branch_id'] !=''){
                    $id=$arrFormData['branch_id'];
                    $data=$data->where('associate_branch_id','=',$id);
                }
				 if($request['start_date'] !=''){

                    $startDate=date("Y-m-d", strtotime(convertDate($request['start_date'])));



                    if($request['end_date'] !=''){

                        $endDate=date("Y-m-d ", strtotime(convertDate($request['end_date'])));

                    }

                    else

                    {

                        $endDate='';

                    }
					 $data=$data->whereBetween(\DB::raw('DATE(associate_join_date)'), [$startDate, $endDate]); 

                }
                if($arrFormData['zone'] !=''){
                    $zone=$arrFormData['zone'];
                    $data=$data->whereHas('associate_branch', function ($query) use ($zone) {
                      $query->where('branch.zone',$zone);
                    });
                }
                if($arrFormData['region'] !=''){
                    $region=$arrFormData['region'];
                    $data=$data->whereHas('associate_branch', function ($query) use ($region) {
                      $query->where('branch.regan',$region);
                    });
                }

                if($arrFormData['sector'] !=''){
                    $sector=$arrFormData['sector'];
                    $data=$data->whereHas('associate_branch', function ($query) use ($sector) {
                      $query->where('branch.sector',$sector);
                    });
                }
                
             if($arrFormData['associate_code'] !=''){
                    $associate_code=$arrFormData['associate_code'];
                    $data=$data->where('associate_no','=',$associate_code);
                }
            }
            
    /******* fillter query End ****/   

            $data1=$data->orderby('associate_join_date','ASC')->get();
            $count=count($data1);

            $data=$data->orderby('associate_join_date','ASC')->offset($_POST['start'])->limit($_POST['length'])->get();
                   
            $totalCount = Member::where('member_id','!=','9999999')->where('is_associate',1)->count();
                    
            $sno=$_POST['start'];
            $rowReturn = array(); 

            foreach ($data as $row)
            {  
                $sno++;

                $associate_id=$row->id;
                $planDaily=getPlanID('710')->id;
                $planSSB=getPlanID('703')->id;
                $planKanyadhan=getPlanID('709')->id;
                $planMB=getPlanID('708')->id;
                $planFFD=getPlanID('705')->id;
                $planFRD=getPlanID('707')->id;
                $planJeevan=getPlanID('713')->id;
                $planMI=getPlanID('712')->id;
                $planFD=getPlanID('706')->id;
                $planRD=getPlanID('704')->id;
                $planBhavhishya=getPlanID('718')->id;
                $planids=array($planDaily,$planSSB,$planKanyadhan,$planMB,$planFFD,$planFRD,$planJeevan,$planMI,$planFD,$planRD,$planBhavhishya,);

                $val['DT_RowIndex']=$sno;

                $val['join_date']=date("d/m/Y", strtotime($row->associate_join_date));

                $val['branch']=$row['associate_branch']->name;



                $val['branch_code']=$row['associate_branch']->branch_code;

                $val['sector_name']=$row['associate_branch']->sector;

                $val['region_name']=$row['associate_branch']->regan;

                $val['zone_name']=$row['associate_branch']->zone;





                $val['member_id']=$row->associate_no;

                $val['name']=$row->first_name.' '.$row->last_name;

                $val['cadre']=getCarderName($row->current_carder_id);



                $val['daily_new_ac']=investNewAcCount($associate_id,$startDate,$endDate,$planDaily,$branch_id);

                $val['daily_deno_sum']=investNewDenoSum($associate_id,$startDate,$endDate,$planDaily,$branch_id);

                $val['daily_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planDaily,$branch_id);

                $val['daily_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planDaily,$branch_id);



                 $val['ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$startDate,$endDate,$branch_id,1);

                $val['ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$startDate,$endDate,$branch_id,1);

                $val['ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$startDate,$endDate,$branch_id,2);

                $val['ssb_renew']=totalInvestSSbAmtByType($associate_id,$startDate,$endDate,$branch_id,2);



                 $val['kanyadhan_new_ac']=investNewAcCount($associate_id,$startDate,$endDate,$planKanyadhan,$branch_id);

                $val['kanyadhan_deno_sum']=investNewDenoSum($associate_id,$startDate,$endDate,$planKanyadhan,$branch_id);

                $val['kanyadhan_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planKanyadhan,$branch_id);

                $val['kanyadhan_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planKanyadhan,$branch_id);



                 $val['mb_new_ac']=investNewAcCount($associate_id,$startDate,$endDate,$planMB,$branch_id);

                $val['mb_deno_sum']=investNewDenoSum($associate_id,$startDate,$endDate,$planMB,$branch_id);

                $val['mb_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planMB,$branch_id);

                $val['mb_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planMB,$branch_id);



                 $val['ffd_new_ac']=investNewAcCount($associate_id,$startDate,$endDate,$planFFD,$branch_id);

                $val['ffd_deno_sum']=investNewDenoSum($associate_id,$startDate,$endDate,$planFFD,$branch_id);

            //    $val['ffd_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planFFD,$branch_id);

            //    $val['ffd_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planFFD,$branch_id);



                 $val['frd_new_ac']=investNewAcCount($associate_id,$startDate,$endDate,$planFRD,$branch_id);

                $val['frd_deno_sum']=investNewDenoSum($associate_id,$startDate,$endDate,$planFRD,$branch_id);

                $val['frd_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planFRD,$branch_id);

                $val['frd_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planFRD,$branch_id);



                 $val['jeevan_new_ac']=investNewAcCount($associate_id,$startDate,$endDate,$planJeevan,$branch_id);

                $val['jeevan_deno_sum']=investNewDenoSum($associate_id,$startDate,$endDate,$planJeevan,$branch_id);

                $val['jeevan_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planJeevan,$branch_id);;

                $val['jeevan_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planJeevan,$branch_id);



                 $val['mi_new_ac']=investNewAcCount($associate_id,$startDate,$endDate,$planMI,$branch_id);

                $val['mi_deno_sum']=investNewDenoSum($associate_id,$startDate,$endDate,$planMI,$branch_id);

                $val['mi_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planMI,$branch_id);;

                $val['mi_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planMI,$branch_id);



                 $val['fd_new_ac']=investNewAcCount($associate_id,$startDate,$endDate,$planFD,$branch_id);

                $val['fd_deno_sum']=investNewDenoSum($associate_id,$startDate,$endDate,$planFD,$branch_id);

            //    $val['fd_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planFD,$branch_id);

            //    $val['fd_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planFD,$branch_id);



                 $val['rd_new_ac']=investNewAcCount($associate_id,$startDate,$endDate,$planRD,$branch_id);

                $val['rd_deno_sum']=investNewDenoSum($associate_id,$startDate,$endDate,$planRD,$branch_id);

                $val['rd_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planRD,$branch_id);

                $val['rd_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planRD,$branch_id);



                 $val['bhavhishya_new_ac']=investNewAcCount($associate_id,$startDate,$endDate,$planBhavhishya,$branch_id);

                $val['bhavhishya_deno_sum']=investNewDenoSum($associate_id,$startDate,$endDate,$planBhavhishya,$branch_id);

                $val['bhavhishya_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planBhavhishya,$branch_id);

                $val['bhavhishya_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planBhavhishya,$branch_id);



                 $sum_ni_ac=$val['daily_new_ac']+$val['ssb_new_ac']+$val['kanyadhan_new_ac']+$val['mb_new_ac']+$val['ffd_new_ac']+$val['frd_new_ac']+$val['jeevan_new_ac']+$val['mi_new_ac']+$val['fd_new_ac']+$val['rd_new_ac']+$val['bhavhishya_new_ac'];  

                $sum_ni_amount=$val['daily_deno_sum']+$val['ssb_deno_sum']+$val['kanyadhan_deno_sum']+$val['mb_deno_sum']+$val['ffd_deno_sum']+$val['frd_deno_sum']+$val['jeevan_deno_sum']+$val['mi_deno_sum']+$val['fd_deno_sum']+$val['rd_deno_sum']+$val['bhavhishya_deno_sum'];                 


                $sum_renew_ac=investRenewAc($associate_id,$startDate,$endDate,$planids,$branch_id);  

                $sum_renew_amount=investRenewAmountSum($associate_id,$startDate,$planids,$planids,$branch_id); 







                $val['total_ni_ac']=$sum_ni_ac; 

                $val['total_ni_amount']=number_format((float)$sum_ni_amount, 2, '.', '');



                $val['total_ac']=$sum_renew_ac;

                $val['total_amount']=number_format((float)$sum_renew_amount, 2, '.', '');



                $val['other_mt']=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,11);

                $val['other_stn']=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,12);


                $ni_m=$val['daily_deno_sum']+$val['kanyadhan_deno_sum']+$val['mb_deno_sum']+$val['ffd_deno_sum']+$val['frd_deno_sum']+$val['jeevan_deno_sum']+$val['mi_deno_sum']+$val['fd_deno_sum']+$val['rd_deno_sum']+$val['bhavhishya_deno_sum'];              

                $tcc_m=$val['daily_deno_sum']+$val['kanyadhan_deno_sum']+$val['mb_deno_sum']+$val['ffd_deno_sum']+$val['frd_deno_sum']+$val['jeevan_deno_sum']+$val['mi_deno_sum']+$val['fd_deno_sum']+$val['rd_deno_sum']+$val['bhavhishya_deno_sum']+$val['bhavhishya_renew']+$val['rd_renew']+$val['mi_renew']+$val['jeevan_renew']+$val['frd_renew']+$val['mb_renew']+$val['kanyadhan_renew']+$val['daily_renew'];               

                $tcc=$val['daily_deno_sum']+$val['kanyadhan_deno_sum']+$val['mb_deno_sum']+$val['ffd_deno_sum']+$val['frd_deno_sum']+$val['jeevan_deno_sum']+$val['mi_deno_sum']+$val['fd_deno_sum']+$val['rd_deno_sum']+$val['bhavhishya_deno_sum']+$val['ssb_deno_sum']+$val['bhavhishya_renew']+$val['rd_renew']+$val['mi_renew']+$val['jeevan_renew']+$val['frd_renew']+$val['mb_renew']+$val['kanyadhan_renew']+$val['ssb_renew']+$val['daily_renew']; 

                $val['ni_m']=number_format((float)$ni_m, 2, '.', '');
                $val['ni']=number_format((float)$sum_ni_amount, 2, '.', '');
                $val['tcc_m']=number_format((float)$tcc_m, 2, '.', '');
                $val['tcc']=number_format((float)$tcc, 2, '.', '');



                $val['st_loan_ac']=associateLoanTypeAC($associate_id,$startDate,$endDate,$branch_id,2);
                $val['st_loan_amount']=associateLoanTypeAmount($associate_id,$startDate,$endDate,$branch_id,2);
                $val['pl_loan_ac']=associateLoanTypeAC($associate_id,$startDate,$endDate,$branch_id,1);
                $val['pl_loan_amount']=associateLoanTypeAmount($associate_id,$startDate,$endDate,$branch_id,1);
                $val['la_loan_ac']=associateLoanTypeAC($associate_id,$startDate,$endDate,$branch_id,4);
                $val['la_loan_amount']=associateLoanTypeAmount($associate_id,$startDate,$endDate,$branch_id,4);
                $val['gp_loan_ac']=associateLoanTypeAC($associate_id,$startDate,$endDate,$branch_id,3);
                $val['gp_loan_amount']=associateLoanTypeAmount($associate_id,$startDate,$endDate,$branch_id,3);

                $val['loan_ac']=$val['st_loan_ac']+$val['pl_loan_ac']+$val['la_loan_ac']+$val['gp_loan_ac'];
                $val['loan_amount']=$val['st_loan_amount']+$val['pl_loan_amount']+$val['la_loan_amount']+$val['gp_loan_amount'];

                $val['st_loan_recovery_ac']=associateLoanTypeRecoverAc($associate_id,$startDate,$endDate,$branch_id,2);
                $val['st_loan_recovery_amount']=associateLoanTypeRecoverAmount($associate_id,$startDate,$endDate,$branch_id,2);
                $val['pl_loan_recovery_ac']=associateLoanTypeRecoverAc($associate_id,$startDate,$endDate,$branch_id,1);
                $val['pl_loan_recovery_amount']=associateLoanTypeRecoverAmount($associate_id,$startDate,$endDate,$branch_id,1);
                $val['la_loan_recovery_ac']=associateLoanTypeRecoverAc($associate_id,$startDate,$endDate,$branch_id,4);
                $val['la_loan_recovery_amount']=associateLoanTypeRecoverAmount($associate_id,$startDate,$endDate,$branch_id,4);
                $val['gp_loan_recovery_ac']=associateLoanTypeRecoverAc($associate_id,$startDate,$endDate,$branch_id,3);
                $val['gp_loan_recovery_amount']=associateLoanTypeRecoverAmount($associate_id,$startDate,$endDate,$branch_id,3);

                $val['loan_recovery_ac']=$val['st_loan_recovery_ac']+$val['pl_loan_recovery_ac']+$val['la_loan_recovery_ac']+$val['gp_loan_recovery_ac'];
                $val['loan_recovery_amount']=$val['st_loan_recovery_amount']+$val['pl_loan_recovery_amount']+$val['la_loan_recovery_amount']+$val['gp_loan_recovery_amount'];  



                



                $val['new_associate']=memberCountByType($associate_id,$startDate,$endDate,$branch_id,1,0);

                $val['total_associate']=memberCountByType($associate_id,$startDate,$endDate,$branch_id,1,1);



                $val['new_member']=memberCountByType($associate_id,$startDate,$endDate,$branch_id,0,0);

                $val['total_member']=memberCountByType($associate_id,$startDate,$endDate,$branch_id,0,1); 
 

               

                
                

            $rowReturn[] = $val; 
          } 
        //  print_r($rowReturn);die;
          $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );

          return json_encode($output);
        }
    }


    /**
     * Associate Business Compare detail.
     * Route: /branch/report/associate_business_compare 
     * Method: get 
     * @return  array()  Response
     */
    public function associateBusinessCompareReport()
    {

     
	 if(!in_array('Associate Business Compare Report', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 }
	 
	  //  echo date('d/m/Y',strtotime('first day of -1 months')) ;die;
        $data['title']='Report | Associate Business Compare Report (Branch Wise)'; 
        $data['branch'] = Branch::where('status',1)->get();
       // $data['zone'] = Branch::where('status',1)->select('zone')->groupBy('zone')->get();
        $data['current_from']=date('d/m/Y',strtotime('first day of -1 months'));
        $data['current_to']=date('d/m/Y',strtotime('last day of -1 months'));
        $data['comp_from']=date('d/m/Y',strtotime('first day of -2 months'));
        $data['comp_to']=date('d/m/Y',strtotime('last day of -2 months'));
        
        
       
        return view('templates.branch.associate_report.associate_business_compare', $data);
    }


    /**
     * Associate Business Compare  list
     * Route: ajax call from - /branch/report/associate_business 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateBusinessCompareList(Request $request)
    { 
        if ($request->ajax()) {
        
// fillter array 
       $arrFormData = array();   
        //  
        $arrFormData['current_start_date'] = $request->current_start_date;
        $arrFormData['current_end_date'] = $request->current_end_date;

        $arrFormData['comp_start_date'] = $request->comp_start_date;
        $arrFormData['comp_end_date'] = $request->comp_end_date;


        $arrFormData['branch_id'] = $request->branch_id;  
        $arrFormData['is_search'] = $request->is_search; 

       /* $arrFormData['zone'] = $request->zone; 
        $arrFormData['region'] = $request->region; 
        $arrFormData['sector'] = $request->sector; */

        $arrFormData['associate_code'] = $request->associate_code; 

        if($arrFormData['current_start_date'] !=''){
          
            $current_start_date=date("Y-m-d", strtotime(convertDate($arrFormData['current_start_date'])));
        }
        else{
            $current_start_date='';
        }

        if($arrFormData['current_end_date'] !=''){
             $current_end_date=date("Y-m-d ", strtotime(convertDate($arrFormData['current_end_date'])));
        }
        else {
            $current_end_date='';
        }

        if($arrFormData['comp_start_date'] !=''){
            $comp_start_date=date("Y-m-d", strtotime(convertDate($arrFormData['comp_start_date'])));
        }
        else{
            $comp_start_date='';
        }

        if($arrFormData['comp_end_date'] !=''){
             $comp_end_date=date("Y-m-d ", strtotime(convertDate($arrFormData['comp_end_date'])));
        }
        else {
            $comp_end_date='';
        }

        if($arrFormData['branch_id']!='') {
            $branch_id=$arrFormData['branch_id'];
        }
        else {
            $branch_id='';
        }



            $data = Member::with('associate_branch')->where('member_id','!=','9999999')->where('is_associate',1);

    /******* fillter query start ****/        
           if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            {
              if($arrFormData['branch_id'] !=''){
                    $id=$arrFormData['branch_id'];
                    $data=$data->where('associate_branch_id','=',$id);
                }
             /*   if($arrFormData['zone'] !=''){
                    $zone=$arrFormData['zone'];
                    $data=$data->whereHas('associate_branch', function ($query) use ($zone) {
                      $query->where('branch.zone',$zone);
                    });
                }
                if($arrFormData['region'] !=''){
                    $region=$arrFormData['region'];
                    $data=$data->whereHas('associate_branch', function ($query) use ($region) {
                      $query->where('branch.regan',$region);
                    });
                }

                if($arrFormData['sector'] !=''){
                    $sector=$arrFormData['sector'];
                    $data=$data->whereHas('associate_branch', function ($query) use ($sector) {
                      $query->where('branch.sector',$sector);
                    });
                }*/
                
                  if($arrFormData['associate_code'] !=''){
                    $associate_code=$arrFormData['associate_code'];
                    $data=$data->where('associate_no','=',$associate_code);
                }
                /*
                
               
                if($arrFormData['name'] !=''){
                    $name =$arrFormData['name'];
                 $data=$data->where(function ($query) use ($name) { $query->where('first_name','LIKE','%'.$name.'%')->orWhere('last_name','LIKE','%'.$name.'%')->orWhere(DB::raw('concat(first_name," ",last_name)') , 'LIKE' , "%$name%"); });  
                }*/
            }
            
    /******* fillter query End ****/   

            $data1=$data->orderby('associate_join_date','ASC')->get();
            $count=count($data1);

            $data=$data->orderby('associate_join_date','ASC')->offset($_POST['start'])->limit($_POST['length'])->get();
                   
            $totalCount = Member::where('member_id','!=','9999999')->where('is_associate',1)->count();
                    
            $sno=$_POST['start'];
            $rowReturn = array(); 

            foreach ($data as $row)
            {  
                $sno++;

                $associate_id=$row->id;
                $planDaily=getPlanID('710')->id;

                $dailyId=array($planDaily);

                $planSSB=getPlanID('703')->id;


                $planKanyadhan=getPlanID('709')->id;
                $planMB=getPlanID('708')->id;
                $planFRD=getPlanID('707')->id;
                $planJeevan=getPlanID('713')->id;  
                $planRD=getPlanID('704')->id;
                $planBhavhishya=getPlanID('718')->id;

                $monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);


                $planMI=getPlanID('712')->id;
                $planFFD=getPlanID('705')->id;
                $planFD=getPlanID('706')->id;

                $fdId=array($planMI,$planFFD,$planFD);

                $val['DT_RowIndex']=$sno;

                $val['join_date']=date("d/m/Y", strtotime($row->associate_join_date));

                $val['branch']=$row['associate_branch']->name;



                $val['branch_code']=$row['associate_branch']->branch_code;

                $val['sector_name']=$row['associate_branch']->sector;

                $val['region_name']=$row['associate_branch']->regan;

                $val['zone_name']=$row['associate_branch']->zone;





                $val['member_id']=$row->associate_no;

                $val['name']=$row->first_name.' '.$row->last_name;

                $val['cadre']=getCarderName($row->current_carder_id);





               

                $val['current_daily_new_ac']=investNewAcCount($associate_id,$current_start_date,$current_end_date,$planDaily,$branch_id);
                $val['current_daily_deno_sum']=investNewDenoSum($associate_id,$current_start_date,$current_end_date,$planDaily,$branch_id);

                $val['current_daily_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);

                $val['current_daily_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);



                $val['current_monthly_new_ac']=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);

                $val['current_monthly_deno_sum']=investNewDenoSumType($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);

                $val['current_monthly_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);

                $val['current_monthly_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);



                $val['current_fd_new_ac']=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);

                $val['current_fd_deno_sum']=investNewDenoSumType($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);

                /*$val['current_fd_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);

                $val['current_fd_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);*/



                $val['current_ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1);

                $val['current_ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,1);

                $val['current_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);

                $val['current_ssb_renew']=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);



                $current_sum_ni_ac=$val['current_daily_new_ac']+$val['current_monthly_new_ac']+$val['current_fd_new_ac']+$val['current_ssb_new_ac'];   

                $current_sum_ni_amount=$val['current_daily_deno_sum']+$val['current_monthly_deno_sum']+$val['current_fd_deno_sum']+$val['current_ssb_deno_sum'];



                $val['current_total_ni_ac']=$current_sum_ni_ac; 

                $val['current_total_ni_amount']=number_format((float)$current_sum_ni_amount, 2, '.', '');



                $current_sum_renew_ac=$val['current_daily_renew_ac']+$val['current_monthly_renew_ac'];   

                $current_sum_renew_amount=$val['current_daily_renew']+$val['current_monthly_renew']; 



                $val['current_total_ac']=$current_sum_renew_ac;

                $val['current_total_amount']=number_format((float)$current_sum_renew_amount, 2, '.', '');



                $val['current_other_mt']=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,11);

                $val['current_other_stn']=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,12);



               


                $current_ni_m=$val['current_daily_deno_sum']+$val['current_monthly_deno_sum']+$val['current_fd_deno_sum'];              

                $current_tcc_m=$val['current_daily_deno_sum']+$val['current_monthly_deno_sum']+$val['current_fd_deno_sum']+$val['current_daily_renew']+$val['current_monthly_renew'];               

                $current_tcc=$val['current_daily_deno_sum']+$val['current_monthly_deno_sum']+$val['current_fd_deno_sum']+$val['current_ssb_deno_sum']+$val['current_daily_renew']+$val['current_monthly_renew']+$val['current_ssb_renew'];

                $val['current_ni_m']=number_format((float)$current_ni_m, 2, '.', '');
                $val['current_ni']=number_format((float)$current_sum_ni_amount, 2, '.', '');
                $val['current_tcc_m']=number_format((float)$current_tcc_m, 2, '.', '');
                $val['current_tcc']=number_format((float)$current_tcc, 2, '.', '');





                $val['current_loan_ac']=totalLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);

                $val['current_loan_amount']=totalLoanAmount($associate_id,$current_start_date,$current_end_date,$branch_id);

                $val['current_loan_recovery_ac']=totalRenewLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);

                $val['current_loan_recovery_amount']=totalRenewLoanAmount($associate_id,$current_start_date,$current_end_date,$branch_id);
                



                $val['current_new_associate']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,0);

                $val['current_total_associate']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,1);



                $val['current_new_member']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,0);

                $val['current_total_member']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,1); 













                



                $val['compare_daily_new_ac']=investNewAcCount($associate_id,$comp_start_date,$comp_end_date,$planDaily,$branch_id);

                $val['compare_daily_deno_sum']=investNewDenoSum($associate_id,$comp_start_date,$comp_end_date,$planDaily,$branch_id);

                $val['compare_daily_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);

                $val['compare_daily_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);



                $val['compare_monthly_new_ac']=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);

                $val['compare_monthly_deno_sum']=investNewDenoSumType($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);

                $val['compare_monthly_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);

                $val['compare_monthly_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);



                $val['compare_fd_new_ac']=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);

                $val['compare_fd_deno_sum']=investNewDenoSumType($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);

               /* $val['compare_fd_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);

                $val['compare_fd_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);*/



                $val['compare_ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1);

                $val['compare_ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_start_date,$branch_id,1);

                $val['compare_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);

                $val['compare_ssb_renew']=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);



                $compare_sum_ni_ac=$val['compare_daily_new_ac']+$val['compare_monthly_new_ac']+$val['compare_fd_new_ac']+$val['compare_ssb_new_ac'];   

                $compare_sum_ni_amount=$val['compare_daily_deno_sum']+$val['compare_monthly_deno_sum']+$val['compare_fd_deno_sum']+$val['compare_ssb_deno_sum'];



                $val['compare_total_ni_ac']=$compare_sum_ni_ac; 

                $val['compare_total_ni_amount']=number_format((float)$compare_sum_ni_amount, 2, '.', '');



                $compare_sum_renew_ac=$val['compare_daily_renew_ac']+$val['compare_monthly_renew_ac'];   

                $compare_sum_renew_amount=$val['compare_daily_renew']+$val['compare_monthly_renew']; 



                $val['compare_total_ac']=$compare_sum_renew_ac;

                $val['compare_total_amount']=number_format((float)$compare_sum_renew_amount, 2, '.', '');



                $val['compare_other_mt']=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,11);

                $val['compare_other_stn']=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,12);


                $compare_ni_m=$val['compare_daily_deno_sum']+$val['compare_monthly_deno_sum']+$val['compare_fd_deno_sum'];              

                $compare_tcc_m=$val['compare_daily_deno_sum']+$val['compare_monthly_deno_sum']+$val['compare_fd_deno_sum']+$val['compare_daily_renew']+$val['compare_monthly_renew'];               

                $compare_tcc=$val['compare_daily_deno_sum']+$val['compare_monthly_deno_sum']+$val['compare_fd_deno_sum']+$val['compare_ssb_deno_sum']+$val['compare_daily_renew']+$val['compare_monthly_renew']+$val['compare_ssb_renew'];

                $val['compare_ni_m']=number_format((float)$compare_ni_m, 2, '.', '');
                $val['compare_ni']=number_format((float)$compare_sum_ni_amount, 2, '.', '');
                $val['compare_tcc_m']=number_format((float)$compare_tcc_m, 2, '.', '');
                $val['compare_tcc']=number_format((float)$compare_tcc, 2, '.', '');


                $val['compare_loan_ac']=totalLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);

                $val['compare_loan_amount']=totalLoanAmount($associate_id,$comp_start_date,$comp_end_date,$branch_id);



                $val['compare_loan_recovery_ac']=totalRenewLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);

                $val['compare_loan_recovery_amount']=totalRenewLoanAmount($associate_id,$comp_start_date,$comp_end_date,$branch_id);



                



                $val['compare_new_associate']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,0);

                $val['compare_total_associate']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,1);



                $val['compare_new_member']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,0);

                $val['compare_total_member']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,1); 



















            $val['result_daily_new_ac']=$val['current_daily_new_ac']-$val['compare_daily_new_ac'];

                $val['result_daily_deno_sum']=$val['current_daily_deno_sum']-$val['compare_daily_deno_sum'];

                $val['result_daily_renew_ac']=$val['current_daily_renew_ac']-$val['compare_daily_renew_ac'];

                $val['result_daily_renew']=$val['current_daily_renew']-$val['compare_daily_renew'];



                $val['result_monthly_new_ac']=$val['current_monthly_new_ac']-$val['compare_monthly_new_ac'];
                
                $val['result_monthly_deno_sum']=$val['current_monthly_deno_sum']-$val['compare_monthly_deno_sum'];

                $val['result_monthly_renew_ac']=$val['current_monthly_renew_ac']-$val['compare_monthly_renew_ac'];

                $val['result_monthly_renew']=$val['current_monthly_renew']-$val['compare_monthly_renew'];



                $val['result_fd_new_ac']=$val['current_fd_new_ac']-$val['compare_fd_new_ac'];

                $val['result_fd_deno_sum']=$val['current_fd_deno_sum']-$val['compare_fd_deno_sum'];

                /*$val['result_fd_renew_ac']=$val['current_fd_renew_ac']-$val['compare_fd_renew_ac'];

                $val['result_fd_renew']=$val['current_fd_renew']-$val['compare_fd_renew'];*/



                $val['result_ssb_new_ac']=$val['current_ssb_new_ac']-$val['compare_ssb_new_ac'];

                $val['result_ssb_deno_sum']=$val['current_ssb_deno_sum']-$val['compare_ssb_deno_sum'];

                $val['result_ssb_renew_ac']=$val['current_ssb_renew_ac']-$val['compare_ssb_renew'];

                $val['result_ssb_renew']=$val['current_ssb_renew']-$val['compare_ssb_deno_sum'];



                $result_sum_ni_ac=$current_sum_ni_ac-$compare_sum_ni_ac;   

                $result_sum_ni_amount=$current_sum_ni_amount-$compare_sum_ni_amount;



                $val['result_total_ni_ac']=$result_sum_ni_ac; 

                $val['result_total_ni_amount']=number_format((float)$result_sum_ni_amount, 2, '.', '');



                $result_sum_renew_ac=$current_sum_renew_ac-$compare_sum_renew_ac;   

                $result_sum_renew_amount=$current_sum_renew_amount-$compare_sum_renew_amount; 



                $val['result_total_ac']=$result_sum_renew_ac;

                $val['result_total_amount']=number_format((float)$result_sum_renew_amount, 2, '.', '');



                $val['result_other_mt']=$val['current_other_mt']-$val['compare_other_mt'];

                $val['result_other_stn']=$val['current_other_stn']-$val['compare_other_stn'];



                $val['result_ni_m']=$val['current_ni_m']-$val['compare_ni_m'];

                $val['result_ni']=$val['current_ni']-$val['compare_ni'];

                $val['result_tcc_m']=$val['current_tcc_m']-$val['compare_tcc_m'];

                $val['result_tcc']=$val['current_tcc']-$val['compare_tcc'];



                $val['result_loan_ac']=$val['current_loan_ac']-$val['compare_loan_ac'];

                $val['result_loan_amount']=$val['current_loan_amount']-$val['compare_loan_amount'];



                $val['result_loan_recovery_ac']=$val['current_loan_recovery_ac']-$val['compare_loan_recovery_ac'];

                $val['result_loan_recovery_amount']=$val['current_loan_recovery_amount']-$val['compare_loan_recovery_amount'];                



                $val['result_new_associate']=$val['current_new_associate']-$val['compare_new_associate'];

                $val['result_total_associate']=$val['current_total_associate']-$val['compare_total_associate'];



                $val['result_new_member']=$val['current_new_member']-$val['compare_new_member'];

                $val['result_total_member']=$val['current_total_member']-$val['compare_total_member']; 

               

               

                
                

            $rowReturn[] = $val; 
          } 
        //  print_r($rowReturn);die;
          $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );

          return json_encode($output);
        }
    }
 
    


    



    /**
     * get branch region by zone.
     * Route: /hr 
     * Method: get 
     * @return  view
     */
    public function branchRegionByZone(Request $request)
    {
       //echo $request->start_date;die;

       /*$data['branch'] = Branch::where('status',1)->get();
        $data['zone'] = Branch::where('status',1)->distinct('zone')->get();
        $data['sector'] = Branch::where('status',1)->get();
        $data['region'] = Branch::where('status',1)->get();
        */
        $data=Branch::where('status',1)->where('zone',$request->zone)->distinct('regan')->get('regan');
             //print_r($data);die; 
             $return_array = compact('data');
        
        return json_encode($return_array);
    }
    /**
     * get branch sector by region.
     * Route: /hr 
     * Method: get 
     * @return  view
     */
    public function branchSectorByRegion(Request $request)
    {
       //echo $request->start_date;die;

       /*$data['branch'] = Branch::where('status',1)->get();
        $data['zone'] = Branch::where('status',1)->distinct('zone')->get();
        $data['sector'] = Branch::where('status',1)->get();
        $data['region'] = Branch::where('status',1)->get();
        */
        $data=Branch::where('status',1)->where('regan',$request->region)->distinct('sector')->get('sector');
             //print_r($data);die; 
             $return_array = compact('data');
        
        return json_encode($return_array);
    }
    /**
     * get branch  by sector or without sector.
     * Route: /hr 
     * Method: get 
     * @return  view
     */
    public function branchBySector(Request $request)
    {
       //echo $request->start_date;die;

       if($request->sector!='')
       {
          $data=Branch::where('status',1)->where('sector',$request->sector)->get();
       }
       else
       {
          $data=Branch::where('status',1)->get();
       }
        
             //print_r($data);die; 
             $return_array = compact('data');
        
        return json_encode($return_array);
    }

    public function maturity()
    {
         if(!in_array('Maturity Report', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 } 
		
		$data['title']='Report | Maturity  Report'; 
        $data['branch'] = Branch::where('status',1)->get();
        $data['plans'] = Plans::where('status',1)->where('plan_code','!=',703)->get();
        
        return view('templates.branch.associate_report.new_maturity', $data);
    }

     public function maturityReportListing(Request $request)
    {
          if ($request->ajax()) {        
// fillter array 
       $arrFormData = array();   
        $arrFormData['start_date'] = $request->start_date;
        $arrFormData['end_date'] = $request->end_date;
        $arrFormData['branch_id'] = $request->branch_id;  
        $arrFormData['payment_type'] = $request->payment_type; 
        $arrFormData['payment_mode'] = $request->payment_mode; 
        $arrFormData['is_search'] = $request->is_search; 

        
         $getBranchId=getUserBranchId(Auth::user()->id);
        $branch_id=$getBranchId->id;
        $data=Memberinvestments::with(['member','associateMember','demandadvice'])->where('plan_id','!=',1)->where('branch_id',$branch_id);

    /******* fillter query start ****/        
           if(isset($request['is_search']) && $request['is_search'] == 'yes')
            {
              if($request['branch_id'] !=''){
                    $bid=$request['branch_id'];
                    $data=$data->where('branch_id',$bid);
                }
                if($request['plan_id'] !=''){
                    $planId=$request['plan_id'];
                    $data=$data->where('plan_id','=',$planId);
                }

                if($request['member_id'] !=''){
                    $meid=$request['member_id'];
                    $data=$data->whereHas('member', function ($query) use ($meid) {
                  $query->where('members.member_id','LIKE','%'.$meid.'%');
                });
                }
                if($request['associate_code'])
                {
                    $associate_code = $request['associate_code'];
                    $data = $data->whereHas('associateMember',function($query) use ($associate_code){
                        $query->where('members.associate_no','Like','%'.$associate_code.'%');
                    });
                }
                if($request['scheme_account_number'])
                {
                    $scheme_account_number = $request['scheme_account_number'];
                      $data=$data->where('account_number','=',$scheme_account_number);
                }
                if($request['name'] !=''){
                    $name =$request['name'];
                    $data=$data->whereHas('member', function ($query) use ($name) {
                    $query->where('members.first_name','LIKE','%'.$name.'%') ->orWhere('members.last_name','LIKE','%'.$name.'%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)') , 'LIKE' , "%$name%");
                });
                }
               
                if($request['start_date'] !='' && $request['status'] == ''){

                    $startDate=date("Y-m-d", strtotime(convertDate($request['start_date'])));



                    if($arrFormData['end_date'] !=''){

                    $endDate=date("Y-m-d ", strtotime(convertDate($request['end_date'])));

                    }

                    else

                    {

                        $endDate='';

                    }

                    $data=$data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]); 

                }



                if($request['status'] != '' && $request['start_date'] !='')

                {

                    $status = $request['status'];

                    $Date = date('Y-m-d');

                  

                        if($request['status'] ==0)
							
                        {	
						 if($request['start_date'] !=''){

							$startDate=date("Y-m-d", strtotime(convertDate($request['start_date'])));
							$startDateMonth=date("m", strtotime(convertDate($request['start_date'])));
							$startDateYear=date("Y", strtotime(convertDate($request['start_date'])));
							if($arrFormData['end_date'] !=''){

							$endDate=date("Y-m-d ", strtotime(convertDate($request['end_date'])));

							}
						else

						{
							$endDate='';
						}
						
						$currentDate=date("Y-m-d", strtotime(convertDate($Date)));	
						$currentDateMonth = date("m", strtotime(convertDate($Date)));	
						$currentDateYear =  date("Y", strtotime(convertDate($Date)));
						
						if($startDateMonth == $currentDateMonth && $currentDateYear==$startDateYear )
						{
						
						$data=$data->whereDate('maturity_date', '>',$currentDate)->whereDate('maturity_date', '<=',$endDate);
						
						
						}
						elseif($startDateMonth > $currentDateMonth){
							
							$data=$data->whereBetween('maturity_date', [$startDate, $endDate]);
							
						}
						else{
							$data=$data->where('maturity_date','');
						}
						}
                        }

                        elseif($request['status'] ==1)

                        {
							
                             if($request['start_date'] !=''){
			
							$startDate=date("Y-m-d", strtotime(convertDate($request['start_date'])));
							$startDateMonth=date("m", strtotime(convertDate($request['start_date'])));
							$startDateYear=date("Y", strtotime(convertDate($request['start_date'])));
							if($arrFormData['end_date'] !=''){

							$endDate=date("Y-m-d ", strtotime(convertDate($request['end_date'])));

							}

						else

						{
							$endDate='';
						}
						
						$currentDate=date("Y-m-d", strtotime(convertDate($Date)));	
						$currentDateMonth = date("m", strtotime(convertDate($Date)));	
						$currentDateYear =  date("Y", strtotime(convertDate($Date)));
						
						if($startDateMonth == $currentDateMonth && $currentDateYear==$startDateYear )
						{
						$data->whereHas('demandadvice',function($query) use($startDate,$endDate,$currentDate){

                        $query->where('status',1)->whereBetween(\DB::raw('DATE(date)'), [$startDate, $currentDate]);

                        })->where('is_mature',0);
						
						
						
						}
						elseif($startDateMonth < $currentDateMonth)
						{
							
							
							$data->whereHas('demandadvice',function($query) use($startDate,$endDate,$currentDate){

							$query->where('status',1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate,$endDate]);

							})->where('is_mature',0);
						}
						
						else{
							$data->whereHas('demandadvice',function($query){

							$query->where('status',1);

							})->where('maturity_date', '<','');
						
						}
						}
                        }

                        elseif ($request['status'] ==2) {
                             if($request['start_date'] !=''){
			
							$startDate=date("Y-m-d", strtotime(convertDate($request['start_date'])));
							$startDateMonth=date("m", strtotime(convertDate($request['start_date'])));
							$startDateYear=date("Y", strtotime(convertDate($request['start_date'])));
							if($arrFormData['end_date'] !=''){

							$endDate=date("Y-m-d ", strtotime(convertDate($request['end_date'])));

							}

						else

						{
							$endDate='';
						}
						
						$currentDate=date("Y-m-d", strtotime(convertDate($Date)));	
						$currentDateMonth = date("m", strtotime(convertDate($Date)));	
						$currentDateYear =  date("Y", strtotime(convertDate($Date)));
						
						if($startDateMonth == $currentDateMonth && $currentDateYear==$startDateYear )
						{
							
							/*$data->whereHas('demandadvice',function($query) use($currentDate,$startDate){
								$query->orwhere('maturity_date','<',$currentDate)->where('is_mature',1)->where('maturity_date', '>=',$startDate)->orwhere('demand_advices.status','=',0)->where('demand_advices.is_mature',0);
								
							})->whereBetween(\DB::raw('DATE(maturity_date)'), [$startDate, $endDate]);
							
							

							$data->whereBetween(\DB::raw('DATE(maturity_date)'), [$startDate, $endDate]);
							$whereCond = '((maturity_date > "'.$currentDate.'" && is_mature = 1 and maturity_date > "'.$startDate.'") )';
							$data = $data->whereRaw($whereCond)->orwhere('demand_advices.is_mature',0);*/
							$data->where('is_mature',0)->whereBetween('maturity_date',[$startDate,$currentDate])->orWhere(function($q) use($startDate,$currentDate){
								$q->whereHas('demandadvice',function($query) use($startDate,$currentDate){
									$query->where('status',0)->whereBetween(\DB::raw('DATE(date)'),[$startDate,$currentDate]);
								});
							});
							
						
						}
						elseif($startDateMonth < $currentDateMonth)
						{
							
							$data->where('is_mature',0)->whereBetween('maturity_date',[$startDate,$endDate])->orWhere(function($q) use($startDate,$endDate){
								$q->whereHas('demandadvice',function($query) use($startDate,$endDate){
									$query->where('status',0)->whereBetween(\DB::raw('DATE(member_investments.maturity_date)'),[$startDate,$endDate]);
								});
							});

						}
						else{
							$data->whereHas('demandadvice',function($query){

							$query->where('status',0);

							})->where('maturity_date', '<','');
						}
						
						}
                  }
              }
           }        
    /******* fillter query End ****/  
            $data1=$data->orderby('created_at','DESC')->get();
            $count=count($data1);
            $data=$data->orderby('created_at','DESC')->offset($_POST['start'])->limit($_POST['length'])->get();   
                       
            $totalCount = $count; ;                    
            $sno=$_POST['start'];
            $rowReturn = array(); 
              
             foreach ($data as $row)

            {  
               if($row->id){
                    $investmentAmount = Daybook::where('investment_id',$row->id)->whereIn('transaction_type',[2,4])->sum('deposit');
                    $current_balance = $investmentAmount;
                }else{
                     $current_balance = 0;
                }
                $sno++;               

                // dd($row);

                 $val['DT_RowIndex']=$sno; 

                $val['branch']=getBranchDetail($row->branch_id)->name;

                $val['branch_code']=getBranchDetail($row->branch_id)->branch_code;

                // $val['sector_name']=getBranchDetail($row->branch_id)->sector;

                // $val['region_name']=getBranchDetail($row->branch_id)->regan;

                 $val['zone_name']=getBranchDetail($row->branch_id)->zone;

                // $val['member_id']=getSeniorData($row->member_id,'member_id');

                // $val['member_name']=getSeniorData($row->member_id,'first_name').' '.getSeniorData($row->member_id,'last_name');

                // $val['account_number']='Passbook Print';

                $val['account_no']=$row->account_number;

                if($row['member'])
                {
                  $val['member_name']=  $row['member']->first_name.' '.$row['member']->last_name;
                }
                else{
                    $val['member_name']='N/A';
                }
                if($row['member'])
                {
                $val['member_id']=$row['member']->member_id;
                }
                else{

                  $val['member_id']='N/A';
                }

                $val['plan_name']=getPlanDetail($row->plan_id)->name;

                $val['deno'] =  "&#8377;".number_format((float)$row->deposite_amount, 2, '.', '');

                $val['maturity_amount'] = "&#8377;".number_format((float)$row->maturity_amount, 2, '.', '');
                if(isset($row->tds_deduct_amount))
                {
                    $val['tds_amount'] = "&#8377;".number_format((float)$row->tds_deduct_amount, 2, '.', '');
                }
                else{
                    $val['tds_amount']='N/A';
                }

                if(isset($current_balance))
                {
                    $val['deposit_amount'] =$current_balance;
                }
                else{
                    $val['deposit_amount']='N/A';
                }
                $val['plan_name']=getPlanDetail($row->plan_id)->name;

                if($row['associateMember'])
                {
                    
                     $associate_code =  getMemberData($row['member']->id);
                     if(isset($associate_code->associate_code))
                     {
                        $val['associate_code'] = $associate_code->associate_code;
                     }
                     else{
                        $val['associate_code'] =  'N/A';
                     }
                }
                else{
                     $val['associate_code'] =  'N/A';
                }

               if($row)
                {
                 $associate_code =  getMemberData($row['member']->id);   
                 $associate_code = $associate_code->associate_code;
                     if(isset($associate_code))
                     {
                        $associate_name  =Member::where('associate_no',$associate_code)->first();
                            if(isset($associate_name->first_name)  && isset($associate_name->last_name))
                        {
                             $val['associate_name'] = $associate_name->first_name.' '.$associate_name->last_name;
                        }
                        else{
                            $val['associate_name'] = $associate_name->first_name;
                        }
                     }
                     else{
                        $val['associate_name'] ='N/A' ;
                     }
                    
                }
                else{
                     $val['associate_name'] = 'N/A';
                }

                $val['opening_date'] =  date("d/m/Y", strtotime($row->created_at));

                $val['due_amount'] =  "&#8377;".number_format((float)$row->due_amount, 2, '.', '');
                $interest = Daybook::where('investment_id',$row->id)->where('transaction_type',16)->first();
                $iamnount = '';
                if($interest)
                {
                    
                     $iamnount  =  number_format((float)$interest->deposit, 2, '.', '');
                }
                 $val['roi'] = $iamnount ;
                 if(isset($row['demandadvice']->final_amount))       
                 {
                    $val['total_amount'] ="&#8377;" .number_format((float)$row['demandadvice']->final_amount, 2, '.', '');;

                 }
                 else{
                    $val['total_amount'] = 'N/A';
                 }

                if($row['demandadvice'])
                {
                if($row['demandadvice']->payment_type==0)
                {
                    $val['maturity_type']='Expense';
                }                   
                
                elseif($row['demandadvice']->payment_type ==1)
                {
                    $val['maturity_type']='Maturity';
                }                   
                
                elseif($row['demandadvice']->payment_type ==2)
                {
                    $val['maturity_type']='PreMaturity';
                }                   
                
                elseif($row['demandadvice']->payment_type ==3)
                {
                    $val['maturity_type']='Death Help';
                }   
                elseif($row['demandadvice']->payment_type ==4)
                {
                    $val['maturity_type']='Emergancy Maturity';
                }   
                else{
                    $val['maturity_type']='N/A';
                }
                }
                else{
                    $val['maturity_type']='N/A';
                }


                if($row->maturity_date)

                {

                    $val['maturity_date'] = $val['maturity_date'] =  date('d/m/Y', strtotime($row->created_at. ' + '.($row->tenure).' year'));

                }

                else{

                      $val['maturity_date'] = "N/A";

                }



                 if( $row->tenure)

                {

                    $val['tenure'] = $row->tenure;

                }

                else{

                    $val['tenure'] = "N/A";

                }
                if($row['demandadvice']){          

                    $val['maturity_payable_amount'] = $row['demandadvice']->maturity_amount_payable.' &#8377';

                }

                else{

                    $val['maturity_payable_amount'] = 'N/A';

                    

                }
                if($row['demandadvice'])
                {
                    if($row['demandadvice']->payment_mode == 0)
                    {
                        $val['payment_mode'] = "Cash";
                    }
                    if($row['demandadvice']->payment_mode == 1)
                    {
                        $val['payment_mode'] = "Cheque";
                    }
                    if($row['demandadvice']->payment_mode == 2)
                    {
                        $val['payment_mode'] = "Online Transfer";
                    }
                    if($row['demandadvice']->payment_mode == 3)
                    {
                        $val['payment_mode'] = "SSB Transfer";
                    }
                
                }
                else{
                    $val['payment_mode'] = "N/A";
                }
                
                if($row['demandadvice'])
                {
                    $val['payment_date'] =  date('d/m/Y', strtotime($row['demandadvice']->date));
                }
                else{
                    $val['payment_date'] = "N/A";
                }
                if($row['demandadvice']){
                if($row['demandadvice']->payment_mode == 1)
                {
                    
                     $transaction = getMaturityTransactionRecord(13,133,$row['demandadvice']->id);
                    
                     if($transaction)
                     {
                         $val['cheque_no'] =$transaction->cheque_no;
                     }
                     else{
                         $val['cheque_no'] ='N/A';
                     }
                }
                elseif($row['demandadvice']->payment_mode == 2)
                {
                    $transaction =getDemandTransactionDetails(13,$row['demandadvice']->id);
                    if($transaction)
                     {
                         $val['cheque_no'] =$transaction->transction_no;
                     }
                     else{
                         $val['cheque_no'] ='N/A';
                     }
                }
                else{
                     $val['cheque_no'] ='N/A';
                }
                }
                else{
                     $val['cheque_no'] ='N/A';
                }
                //ssb payment
                if($row['demandadvice'])
                {
                    $ac = SavingAccount::where('member_id',$row['member']->id)->first();
                    if($row['demandadvice']->payment_mode == 3)
                {
                    $ac = SavingAccount::where('member_id',$row['member']->id)->first();
                    if($ac ){
                    $val['ssb_ac'] =$ac->account_no;
                    }
                    else{
                        $val['ssb_ac']  =$row['demandadvice']->ssb_account;
                    }
                }
                elseif(isset($ac->account_no))
                {
                    $val['ssb_ac'] =$ac->account_no;
                }
                else{
                    $val['ssb_ac'] ='N/A';  
                }
                    
                }
                else{
                    $val['ssb_ac'] ='N/A';  
                }
                
                //Bank Payment
                
                if($row['demandadvice'])
                {
                    if($row['demandadvice']->payment_mode == 1 || $row['demandadvice']->payment_mode == 2)
                {
                    
                    // $transaction = getMaturityTransactionRecord(13,133,$row['demandadvice']->id);
                
                     if(isset($row['demandadvice']->bank_name))
                     {
                         $val['bank_name'] =$row['demandadvice']->bank_name;
                     }
                     else{
                         $val['bank_name'] ='N/A';
                     }      
                }
                else{
                    $val['bank_name'] ='N/A';   
                }
                    
                }
                else{
                    $val['bank_name'] ='N/A';   
                }
            
            if($row['demandadvice'])
                {
                    if($row['demandadvice']->payment_mode == 1 || $row['demandadvice']->payment_mode == 2)
                {
                    // $transaction = getMaturityTransactionRecord(13,133,$row['demandadvice']->id);
                     if(isset($row['demandadvice']->bank_account_number))
                     {
                         $val['bank_ac'] =$row['demandadvice']->bank_account_number;
                     }
                     else{
                         $val['bank_ac'] ='N/A';
                     }  
                }
                else{
                    $val['bank_ac'] ='N/A'; 
                }
                    
                }
                else{
                    $val['bank_ac'] ='N/A'; 
                }
          
                // rtgs charge
                if($row['demandadvice'])
                {
                    if($row['demandadvice']->payment_mode == 2 )
                {
                    $transaction = AllHeadTransaction::where('head_id',92)->where('type_id',$row['demandadvice']->id)->first();;

                    if($transaction)
                    {  
                             $val['rtgs_chrg'] =number_format((float)$transaction->amount, 2, '.', '');
                        
                         
                    
                }
                    
                     else{
                         $val['rtgs_chrg'] ='N/A';
                     }
                }
                else{
                    $val['rtgs_chrg'] ='N/A';   
                }
                    
                }
                else{
                    $val['rtgs_chrg'] ='N/A';   
                }
                
                

                

            $rowReturn[] = $val; 

          } 

        //  print_r($rowReturn);die;
          $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
          return json_encode($output);
        }
    }



    public function loan()

    {

         if(!in_array('Loan Report', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 } 

        $data['title']='Report | Loan  Report'; 

        $data['branch'] = Branch::where('status',1)->get();

        

        return view('templates.branch.associate_report.loan', $data);

    }



    /**

     * Display the specified resource.

     *

     * @param  \App\Reservation  $reservation

     * @return \Illuminate\Http\Response

     */

    public function loanListing(Request $request)

    { 

         if ($request->ajax()) {



            $arrFormData = array();   

            if(!empty($_POST['searchform']))

            {

                foreach($_POST['searchform'] as $frm_data)

                {

                    $arrFormData[$frm_data['name']] = $frm_data['value'];

                }

            }



            $loan=Memberloans::with('loan','loanMember','LoanApplicants','LoanCoApplicants','LoanGuarantor','Loanotherdocs','GroupLoanMembers','loanInvestmentPlans')->where('loan_type','!=',3);
            $grpLoan  = Grouploans::with('loan','loanMember','LoanApplicants','LoanCoApplicants','LoanGuarantor');

            

            if(Auth::user()->branch_id>0){

             $id=Auth::user()->branch_id;

              $loan=$loan->where('branch_id','=',$id);
              $grpLoan=$grpLoan->where('branch_id','=',$id);

            }



            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')

              {

                if($arrFormData['start_date'] !=''){

                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));

                    if($arrFormData['end_date'] !=''){

                        $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));

                    }

                    else

                    {

                        $endDate='';

                    }

                    $loan=$loan->whereBetween('created_at', [$startDate, $endDate]);  
                    $grpLoan=$grpLoan->whereBetween('created_at', [$startDate, $endDate]);     

                }



                if($arrFormData['plan'] !=''){

                    $plan=$arrFormData['plan'];

                    $loan=$loan->where('loan_type','=',$plan);
                    $grpLoan=$grpLoan->where('loan_type','=',$plan);

                }



                if(isset($arrFormData['branch_id']) && $arrFormData['branch_id'] !=''){

                    $branch_id=$arrFormData['branch_id'];

                    $loan=$loan->where('branch_id','=',$branch_id);  
                    $grpLoan=$grpLoan->where('branch_id','=',$branch_id);   

                }



                if($arrFormData['status'] !=''){

                    $status=$arrFormData['status'];

                    $loan=$loan->where('status','=',$status);
                    $grpLoan=$grpLoan->where('status','=',$status);

                }



                if($arrFormData['application_number'] !=''){

                    $application_number=$arrFormData['application_number'];

                    $loan=$loan->where('account_number','=',$application_number);
                     $grpLoan=$grpLoan->where('account_number','=',$application_number);

                }



                if($arrFormData['member_id'] !=''){

                    $member_id=$arrFormData['member_id'];

                    $loan=$loan->whereHas('loanMember', function ($query) use ($member_id) {

                      $query->where('members.member_id','LIKE','%'.$member_id.'%');

                    });

                    $grpLoan=$grpLoan->whereHas('loanMember', function ($query) use ($member_id) {

                      $query->where('members.member_id','LIKE','%'.$member_id.'%');

                    });

                }

            }



            $loan = $loan->orderby('id','DESC')->get();
            $grpLoan = $grpLoan->orderby('id','DESC')->get();

            $data = $loan->merge($grpLoan);

            




            return Datatables::of($data)

            ->addIndexColumn()



            ->addColumn('status',function($row){

                if($row->status == 0){

                    return 'Inactive';

                }elseif($row->status == 1){

                    return 'Approved';

                }elseif($row->status == 2){

                    return 'Rejected';

                }elseif($row->status == 3){

                    return 'Completed';

                }elseif($row->status == 4){

                    return 'ONGOING';

                }

            })

            ->rawColumns(['status'])

            

            ->addColumn('applicant_name', function($row){

                if($row->loan_type == 3)
                {
                    if(isset($row->member_id)){

                        if(getMemberData($row->member_id)){

                            return getMemberData($row->member_id)->first_name.' '.getMemberData($row->member_id)->last_name;

                        }else{

                            return 'N/A';

                        }

                    }else{

                        return 'N/A';

                    }
                }
                else{
                        if(isset($row->applicant_id)){

                        if(getMemberData($row->applicant_id)){

                            return getMemberData($row->applicant_id)->first_name.' '.getMemberData($row->applicant_id)->last_name;

                        }else{

                            return 'N/A';

                        }

                    }else{

                        return 'N/A';

                    }
                 }
                


            })

            ->rawColumns(['applicant_name'])

            ->addColumn('applicant_id', function($row){
                  if($row->loan_type == 3)
                {
                    return $row->group_loan_common_id;
                }
                else{
                    return Member::find($row->applicant_id)->member_id;

                }

            })

            ->rawColumns(['applicant_id'])

            ->addColumn('applicant_phone_number', function($row){
                if($row->loan_type == 3)
                {
                   if(isset($row->member_id)){

                        if(getMemberData($row->member_id)){

                            return getMemberData($row->member_id)->mobile_no;

                        }else{

                            return 'N/A';

                        }

                    }else{

                        return 'N/A';

                    } 
                }
                else{
                    if(isset($row->applicant_id)){

                        if(getMemberData($row->applicant_id)){

                            return getMemberData($row->applicant_id)->mobile_no;

                        }else{

                            return 'N/A';

                        }

                    }else{

                        return 'N/A';

                    }
                }
                

            })

            ->rawColumns(['applicant_phone_number'])

            ->addColumn('membership_id', function($row){

                return 'N/A';

            })

            ->rawColumns(['membership_id'])

            ->addColumn('account_number', function($row){

                return $row->account_number;

            })

            ->rawColumns(['account_number'])

            ->addColumn('branch', function($row){
                if(isset($row->branch_id))
                {
                     return getBranchDetail($row->branch_id)->name;          
                }
                else{
                    return 'N/A';
                }
               

            })

            ->rawColumns(['branch'])

            ->addColumn('sector', function($row){

                return getBranchDetail($row->branch_id)->sector;     

            })

            ->rawColumns(['sector'])

            ->addColumn('member_id', function($row){
                if($row->loan_type == 3)
                {
                   return getMemberData($row->member_id)->member_id;
                }
                else{
                     return getMemberData($row->applicant_id)->member_id; 

                }

            })

            ->rawColumns(['member_id'])

            ->addColumn('sanctioned_amount', function($row){

                return $row->amount.' &#8377';   

            })

            ->escapeColumns(['co_applicant_number'])

            ->addColumn('transfer_amnt', function($row){

                return $row->deposite_amount.' &#8377';   

            })

            ->escapeColumns(['transfer_amnt'])

            ->addColumn('sanctioned_date', function($row){

                if($row->approve_date){

                    return date("d/m/Y", strtotime(convertDate($row->approve_date)));

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['sanctioned_date'])

            ->addColumn('emi_rate', function($row){

                return $row->emi_amount;

            })

            ->rawColumns(['emi_rate'])

            ->addColumn('no_of_installement', function($row){

                return $row->emi_period;

            })

            ->rawColumns(['no_of_installement'])

            ->addColumn('loan_mode', function($row){

                if($row->emi_option == 1){

                    return 'Months';

                }elseif($row->emi_option == 2){

                    return 'Weeks';

                }elseif($row->emi_option == 3){

                    return 'Daily';

                }

            })

            ->rawColumns(['loan_mode'])

            ->addColumn('loan_type', function($row){

                return $row['loan']->name;

            })

            ->rawColumns(['loan_type'])

            ->addColumn('loan_issue_date', function($row){

                return date("d/m/Y", strtotime(convertDate($row->created_at)));

            })

            ->rawColumns(['loan_issue_date'])

            ->addColumn('loan_issue_mode', function($row){

                $mode = Daybook::whereIn('transaction_type',[3,8])->where('loan_id',$row->id)->orderby('id','ASC')->first('payment_mode');

                if($mode){

                    if($mode->payment_mode == 1){

                        return 'Cash';

                    }elseif($mode->payment_mode == 2){

                        return 'Cheque';

                    }elseif($mode->payment_mode == 3){

                        return 'DD';

                    }elseif($mode->payment_mode == 4){

                        return 'Online Transaction';

                    }elseif($mode->payment_mode == 5){

                        return 'SSB';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['loan_issue_mode'])

            ->addColumn('cheque_no', function($row){

                $mode = Daybook::whereIn('transaction_type',[3,8])->where('loan_id',$row->id)->orderby('id','ASC')->first('cheque_dd_no');

                if($mode){

                    return $mode->cheque_dd_no;

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['cheque_no'])

            ->addColumn('total_recovery_amount', function($row){

                $amount = LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->sum('deposit');

                return $amount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])

            ->addColumn('total_recovery_emi_till_date', function($row){

                return $row->credit_amount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])





            ->addColumn('closing_amount', function($row){

                if($row->emi_option == 1){

                    $closingAmountROI = $row->due_amount*$row->ROI/1200;  

                }elseif($row->emi_option == 2){

                    $closingAmountROI = $row->due_amount*$row->ROI/5200;

                }elseif($row->emi_option == 3){

                    $closingAmountROI = $row->due_amount*$row->ROI/36500;

                }

                $closingAmount = round($row->due_amount+$closingAmountROI);

                return $closingAmount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])



            ->addColumn('balance_emi', function($row){

                $d1 = explode('-',$row->created_at);

                $d2 = explode('-',date("Y-m-d"));

                $firstMonth = $d1[1];

                $secondMonth = $d2[1];

                $monthDiff = $secondMonth-$firstMonth;

                $ramount = LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->sum('deposit');

                $camount  = $monthDiff*$row->emi_amount;

                if($ramount < $camount){

                    return 'Yes';

                }else{

                    return 'No';

                }

            })

            ->rawColumns(['balance_emi'])

            ->addColumn('emi_should_be_received_till_date', function($row){

                $d1 = explode('-',$row->created_at);

                $d2 = explode('-',date("Y-m-d"));

                $firstMonth = $d1[1];

                $secondMonth = $d2[1];

                $monthDiff = $secondMonth-$firstMonth;

                $camount  = $monthDiff*$row->emi_amount;

                return $camount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])

            ->addColumn('future_emi_due_till_date', function($row){

                $d1 = explode('-',$row->created_at);

                $d2 = explode('-',date("Y-m-d"));

                $firstMonth = $d1[1];

                $secondMonth = $d2[1];

                $monthDiff = $secondMonth-$firstMonth;

                $camount  = $monthDiff*$row->emi_amount;

                return $camount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])

            ->addColumn('date', function($row){

                return date("d/m/Y");

            })

            ->rawColumns(['date'])

            ->addColumn('co_applicant_name', function($row){

                if(count($row['LoanCoApplicants']) > 0){

                    if(getMemberData($row['LoanCoApplicants'][0]->member_id)){

                        return getMemberData($row['LoanCoApplicants'][0]->member_id)->first_name.' '.getMemberData($row['LoanCoApplicants'][0]->member_id)->last_name;

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['co_applicant_name'])

            ->addColumn('co_applicant_number', function($row){

                if(count($row['LoanCoApplicants']) > 0){

                    if(getMemberData($row['LoanCoApplicants'][0]->member_id)){

                        return getMemberData($row['LoanCoApplicants'][0]->member_id)->mobile_no;

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['co_applicant_number'])

            ->addColumn('gurantor_name', function($row){

                if(count($row['LoanGuarantor']) > 0){

                    if(getMemberData($row['LoanGuarantor'][0]->member_id)){

                        return getMemberData($row['LoanGuarantor'][0]->member_id)->first_name.' '.getMemberData($row['LoanGuarantor'][0]->member_id)->last_name;

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['gurantor_name'])

            ->addColumn('gurantor_number', function($row){

                if(count($row['LoanGuarantor']) > 0){

                    if(getMemberData($row['LoanGuarantor'][0]->member_id)){

                        return getMemberData($row['LoanGuarantor'][0]->member_id)->mobile_no;

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                } 

            })

            ->rawColumns(['gurantor_number'])

            ->addColumn('applicant_address', function($row){

                if(count($row['LoanApplicants']) > 0){

                    if(getMemberData($row['LoanApplicants'][0]->member_id)){

                        return preg_replace( "/\r|\n/", "",getMemberData($row['LoanApplicants'][0]->member_id)->address);

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['applicant_address'])

            ->addColumn('first_emi_date', function($row){

                $record = LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->orderby('created_at','asc')->first('created_at');

                if($record && isset($record)){

                    return date("d/m/Y", strtotime(convertDate($record->created_at)));

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['first_emi_date'])

            ->addColumn('loan_end_date', function($row){

                if($row->approve_date){
                    if($row->emi_option == 1){
                        $last_recovery_date = date('d/m/Y', strtotime("+".$row->emi_period." months", strtotime($row->approve_date)));       
                    }elseif($row->emi_option == 2){
                        $days = $row->emi_period*7;
                        $start_date = $row->approve_date;  
                        $date = strtotime($start_date);
                        $date = strtotime("+".$days." day", $date);
                        $last_recovery_date = date('d/m/Y', $date);
                    }elseif($row->emi_option == 3){  
                        $days = $row->emi_period;
                        $start_date = $row->approve_date;  
                        $date = strtotime($start_date);
                        $date = strtotime("+".$days." day", $date);
                        $last_recovery_date = date('d/m/Y', $date);
                    }
                }else{
                    $last_recovery_date = 'N/A';
                }
                   

                return  $last_recovery_date;

            })

            ->rawColumns(['loan_end_date'])

            ->addColumn('total_deposit_till_date', function($row){

                $amount = LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->sum('deposit');

                return $amount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])

            ->make(true);

        }

    }




    public function groupLoan()

    {

        $data['title']='Report | Group Loan  Report'; 

        $data['branch'] = Branch::where('status',1)->get();

        return view('templates.branch.associate_report.grouploan', $data);

    }



    public function groupLoanListing(Request $request)

    { 

        if ($request->ajax()) {



            $arrFormData = array();   

            if(!empty($_POST['searchform']))

            {

                foreach($_POST['searchform'] as $frm_data)

                {

                    $arrFormData[$frm_data['name']] = $frm_data['value'];

                }

            }



            $data=Grouploans::with('loanMember','LoanApplicants','LoanCoApplicants','LoanGuarantor');



            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')

              {

                if($arrFormData['start_date'] !=''){

                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));

                    if($arrFormData['end_date'] !=''){

                        $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));

                    }

                    else

                    {

                        $endDate='';

                    }

                    $data=$data->whereBetween('created_at', [$startDate, $endDate]);    

                }



                if($arrFormData['branch_id'] !=''){

                    $branch_id=$arrFormData['branch_id'];

                    $data=$data->where('branch_id','=',$branch_id);   

                }



                if($arrFormData['status'] !=''){

                    $status=$arrFormData['status'];

                    $data=$data->where('status','=',$status);

                }



                if($arrFormData['application_number'] !=''){

                    $application_number=$arrFormData['application_number'];

                    $data=$data->where('account_number','=',$application_number);

                }



                if($arrFormData['member_id'] !=''){

                    $member_id=$arrFormData['member_id'];

                    $data=$data->whereHas('loanMember', function ($query) use ($member_id) {

                      $query->where('members.member_id','LIKE','%'.$member_id.'%');

                    });

                }

            }



            $data = $data->orderby('id','DESC')->get();



            return Datatables::of($data)

            ->addIndexColumn()



            ->addColumn('status',function($row){

                if($row->status == 0){

                    return 'Inactive';

                }elseif($row->status == 1){

                    return 'Approved';

                }elseif($row->status == 2){

                    return 'Rejected';

                }elseif($row->status == 3){

                    return 'Completed';

                }elseif($row->status == 4){

                    return 'ONGOING';

                }

            })

            ->rawColumns(['status'])

            

            ->addColumn('applicant_name', function($row){

                if(count($row['LoanApplicants']) > 0){

                    if(getMemberData($row['LoanApplicants'][0]->member_id)){

                        return getMemberData($row['LoanApplicants'][0]->member_id)->first_name.' '.getMemberData($row['LoanApplicants'][0]->member_id)->last_name;

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['applicant_name'])

            ->addColumn('applicant_phone_number', function($row){

                if(count($row['LoanApplicants']) > 0){

                    if(getMemberData($row['LoanApplicants'][0]->member_id)){

                        return getMemberData($row['LoanApplicants'][0]->member_id)->mobile_no;

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['applicant_phone_number'])

            ->addColumn('membership_id', function($row){

                return 'N/A';

            })

            ->rawColumns(['membership_id'])

            ->addColumn('account_number', function($row){

                return $row->account_number;

            })

            ->rawColumns(['account_number'])

            ->addColumn('branch', function($row){

                return getBranchDetail($row->branch_id)->name;      

            })

            ->rawColumns(['branch'])

            ->addColumn('sector', function($row){

                return getBranchDetail($row->branch_id)->sector;     

            })

            ->rawColumns(['sector'])

            ->addColumn('member_id', function($row){

                return getMemberData($row->applicant_id)->member_id; 

            })

            ->rawColumns(['member_id'])

            ->addColumn('sanctioned_amount', function($row){

                return $row->deposite_amount.' &#8377';   

            })

            ->escapeColumns(['co_applicant_number'])

            ->addColumn('sanctioned_date', function($row){

                if($row->approve_date){

                    return date("d/m/Y", strtotime(convertDate($row->approve_date)));

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['sanctioned_date'])

            ->addColumn('emi_rate', function($row){

                return $row->emi_amount;

            })

            ->rawColumns(['emi_rate'])

            ->addColumn('no_of_installement', function($row){

                return $row->emi_period;

            })

            ->rawColumns(['no_of_installement'])

            ->addColumn('loan_mode', function($row){

                if($row->emi_option == 1){

                    return 'Months';

                }elseif($row->emi_option == 2){

                    return 'Weeks';

                }elseif($row->emi_option == 3){

                    return 'Daily';

                }

            })

            ->rawColumns(['loan_mode'])

            ->addColumn('loan_type', function($row){

                return $row['loan']->name;

            })

            ->rawColumns(['loan_type'])

            ->addColumn('loan_issue_date', function($row){

                return date("d/m/Y", strtotime(convertDate($row->created_at)));

            })

            ->rawColumns(['loan_issue_date'])

            ->addColumn('loan_issue_mode', function($row){

                $mode = Daybook::whereIn('transaction_type',[3,8])->where('loan_id',$row->id)->orderby('id','ASC')->first('payment_mode');

                if($mode){

                    if($mode->payment_mode == 1){

                        return 'Cash';

                    }elseif($mode->payment_mode == 2){

                        return 'Cheque';

                    }elseif($mode->payment_mode == 3){

                        return 'DD';

                    }elseif($mode->payment_mode == 4){

                        return 'Online Transaction';

                    }elseif($mode->payment_mode == 5){

                        return 'SSB';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['loan_issue_mode'])

            ->addColumn('cheque_no', function($row){

                $mode = Daybook::whereIn('transaction_type',[3,8])->where('loan_id',$row->id)->orderby('id','ASC')->first('cheque_dd_no');

                if($mode){

                    return $mode->cheque_dd_no;

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['cheque_no'])

            ->addColumn('total_recovery_amount', function($row){

                $amount = LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->sum('deposit');

                return $amount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])

            ->addColumn('total_recovery_emi_till_date', function($row){

                return $row->credit_amount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])





            ->addColumn('closing_amount', function($row){

                if($row->emi_option == 1){

                    $closingAmountROI = $row->due_amount*$row->ROI/1200;  

                }elseif($row->emi_option == 2){

                    $closingAmountROI = $row->due_amount*$row->ROI/5200;

                }elseif($row->emi_option == 3){

                    $closingAmountROI = $row->due_amount*$row->ROI/36500;

                }

                $closingAmount = round($row->due_amount+$closingAmountROI);

                return $closingAmount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])



            ->addColumn('balance_emi', function($row){

                $d1 = explode('-',$row->created_at);

                $d2 = explode('-',date("Y-m-d"));

                $firstMonth = $d1[1];

                $secondMonth = $d2[1];

                $monthDiff = $secondMonth-$firstMonth;

                $ramount = LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->sum('deposit');

                $camount  = $monthDiff*$row->emi_amount;

                if($ramount < $camount){

                    return 'Yes';

                }else{

                    return 'No';

                }

            })

            ->rawColumns(['balance_emi'])

            ->addColumn('emi_should_be_received_till_date', function($row){

                $d1 = explode('-',$row->created_at);

                $d2 = explode('-',date("Y-m-d"));

                $firstMonth = $d1[1];

                $secondMonth = $d2[1];

                $monthDiff = $secondMonth-$firstMonth;

                $camount  = $monthDiff*$row->emi_amount;

                return $camount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])

            ->addColumn('future_emi_due_till_date', function($row){

                $d1 = explode('-',$row->created_at);

                $d2 = explode('-',date("Y-m-d"));

                $firstMonth = $d1[1];

                $secondMonth = $d2[1];

                $monthDiff = $secondMonth-$firstMonth;

                $camount  = $monthDiff*$row->emi_amount;

                return $camount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])

            ->addColumn('date', function($row){

                return date("d/m/Y");

            })

            ->rawColumns(['date'])

            ->addColumn('co_applicant_name', function($row){

                if(count($row['LoanCoApplicants']) > 0){

                    if(getMemberData($row['LoanCoApplicants'][0]->member_id)){

                        return getMemberData($row['LoanCoApplicants'][0]->member_id)->first_name.' '.getMemberData($row['LoanCoApplicants'][0]->member_id)->last_name;

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['co_applicant_name'])

            ->addColumn('co_applicant_number', function($row){

                if(count($row['LoanCoApplicants']) > 0){

                    if(getMemberData($row['LoanCoApplicants'][0]->member_id)){

                        return getMemberData($row['LoanCoApplicants'][0]->member_id)->mobile_no;

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['co_applicant_number'])

            ->addColumn('gurantor_name', function($row){

                if(count($row['LoanGuarantor']) > 0){

                    if(getMemberData($row['LoanGuarantor'][0]->member_id)){

                        return getMemberData($row['LoanGuarantor'][0]->member_id)->first_name.' '.getMemberData($row['LoanGuarantor'][0]->member_id)->last_name;

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['gurantor_name'])

            ->addColumn('gurantor_number', function($row){

                if(count($row['LoanGuarantor']) > 0){

                    if(getMemberData($row['LoanGuarantor'][0]->member_id)){

                        return getMemberData($row['LoanGuarantor'][0]->member_id)->mobile_no;

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                } 

            })

            ->rawColumns(['gurantor_number'])

            ->addColumn('applicant_address', function($row){

                if(count($row['LoanApplicants']) > 0){

                    if(getMemberData($row['LoanApplicants'][0]->member_id)){

                        return preg_replace( "/\r|\n/", "",getMemberData($row['LoanApplicants'][0]->member_id)->address);

                    }else{

                        return 'N/A';

                    }

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['applicant_address'])

            ->addColumn('first_emi_date', function($row){

                $record = LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->orderby('created_at','asc')->first('created_at');

                if($record && isset($record)){

                    return date("d/m/Y", strtotime(convertDate($record->created_at)));

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['first_emi_date'])

            ->addColumn('loan_end_date', function($row){

                if($row->approve_date){
                    if($row->emi_option == 1){
                        $last_recovery_date = date('d/m/Y', strtotime("+".$row->emi_period." months", strtotime($row->approve_date)));       
                    }elseif($row->emi_option == 2){
                        $days = $row->emi_period*7;
                        $start_date = $row->approve_date;  
                        $date = strtotime($start_date);
                        $date = strtotime("+".$days." day", $date);
                        $last_recovery_date = date('d/m/Y', $date);
                    }elseif($row->emi_option == 3){  
                        $days = $row->emi_period;
                        $start_date = $row->approve_date;  
                        $date = strtotime($start_date);
                        $date = strtotime("+".$days." day", $date);
                        $last_recovery_date = date('d/m/Y', $date);
                    }
                }else{
                    $last_recovery_date = 'N/A';
                }
                   

                return  $last_recovery_date;

            })

            ->rawColumns(['loan_end_date'])

            ->addColumn('total_deposit_till_date', function($row){

                $amount = LoanDayBooks::where('loan_type',$row->loan_type)->where('loan_id',$row->id)->sum('deposit');

                return $amount.' &#8377';

            })

            ->escapeColumns(['co_applicant_number'])

            ->make(true);

        }

    }



}
