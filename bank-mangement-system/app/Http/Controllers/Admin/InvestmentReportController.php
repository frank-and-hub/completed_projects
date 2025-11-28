<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Memberinvestments;
use Carbon;
use URL;
use Auth;
use App\Models\Branch;
class InvestmentReportController extends Controller
{
    /**
     * Report Daily Deposite
     * Emi Pending wills how
    **/
    public function dailyReport(){
        if(check_my_permission( Auth::user()->id,"255") != "1"){
          return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Investment Management || Daily Due Report';
        $data['slug'] = 'daily';
        $data['branches'] = Branch::where('status',1)->get(['id','name']);
        return view('templates.admin.investment_management.dailyduereport',$data); 
    }
    /**
     * Report Monthly Deposite
     * Emi Pending wills how
    **/
    public function MonthlyReport(){
        if(check_my_permission( Auth::user()->id,"256") != "1"){
          return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Investment Management || Monthly Due Report';
        $data['slug'] = 'monthly';
        $data['plan'] = \App\Models\Plans::whereIn('id',[2,3,5,6,10,11])->get(['id','name']);
        $data['branches'] = Branch::where('status',1)->get(['id','name']);
        return view('templates.admin.investment_management.dailyduereport',$data); 
    }
    /**
    *Common function for daily and monthly 
    * 
    **/
    public static function getRecords($dailyDepositeRecord,$currentDate)
    {   
            switch ($dailyDepositeRecord->plan_id) {
                case '7':
                    $pendingEmiAMount=0;
                    $pendingEmi=0;
                    $investCreatedDate =strtotime($dailyDepositeRecord->created_at);
                    $CURRENTDATE = strtotime($currentDate);
                    $totalBetweendays = abs($investCreatedDate-$CURRENTDATE);
                    $totalBetweendays = ceil(floatval($totalBetweendays/86400));
                    $totalAmount = ($totalBetweendays + 1) * $dailyDepositeRecord->deposite_amount;
                    $getRenewalReceivedAmount= \App\Models\Daybook::whereIn('transaction_type',[2,4])
                        ->where('account_no',$dailyDepositeRecord->account_number)
                        ->where('is_deleted',0)
                        ->sum('deposit');
                    if($getRenewalReceivedAmount != $totalAmount)
                    {
                        $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount ;
                        $pendingEmi =  $pendingEmiAMount / $dailyDepositeRecord->deposite_amount;
                    } 
                    else{
                        $pendingEmiAMount = 0;
                        $pendingEmi =  0;
                    }
                    return ['pendingEmiAMount'=>$pendingEmiAMount,'pendingEmi'=>$pendingEmi];
                    break;
                case '2':
                case '3':
                case '5':
                case '7':
                case '10':
                case '11':
                    $pendingEmiAMount=0;
                    $pendingEmi=0;
                    $investCreatedDate = Carbon\Carbon::parse($dailyDepositeRecord->created_at);
                    $totalBetweenmonth = $investCreatedDate->diffInMonths($currentDate);
                    $totalAmount = ($totalBetweenmonth + 1) * $dailyDepositeRecord->deposite_amount;
                    $getRenewalReceivedAmount= \App\Models\Daybook::whereIn('transaction_type',[2,4])->where('account_no',$dailyDepositeRecord->account_number)->where('is_deleted',0)->sum('deposit');
                    // dd($getRenewalReceivedAmount , $totalAmount,$dailyDepositeRecord->deposite_amount);
                    if($getRenewalReceivedAmount != $totalAmount)
                    {
                        $pendingEmiAMount = ((int)$getRenewalReceivedAmount) - $totalAmount ;
                        $pendingEmi =  ($dailyDepositeRecord->deposite_amount > 0) ? ($pendingEmiAMount / $dailyDepositeRecord->deposite_amount) : 0;
                    } 
                     else{
                        $pendingEmiAMount = 0;
                        $pendingEmi =  0;
                    }
                    return ['pendingEmiAMount'=>$pendingEmiAMount,'pendingEmi'=>$pendingEmi];
                    break;
            }
    }
    public function dailyReportListing(Request $request)
    {
        if($request->ajax())
        {
            $arrFormData = array();
            if (!empty($_POST['searchform']))
            {
                foreach ($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') 
            {
                    $dailyDepositeRecord = Memberinvestments::has('company')->select('id','plan_id','created_at','account_number','deposite_amount','member_id','plan_id','associate_id','branch_id','tenure')
                        ->with(['plan' => function($q){ $q->select('id','name'); }])
                        ->with(['member' => function($q){ $q->select('id','first_name','last_name','member_id','mobile_no'); }])
                        ->with(['associateMember' => function($q){ $q->select('id','first_name','last_name','associate_no'); }])
                        ->with(['branch' => function($q){ $q->select('id','name','branch_code','sector','regan','zone'); }])
                    ->where('is_deleted',0)->where('is_mature',1);
                    if($request->slug == 'daily')
                    {
                        $dailyDepositeRecord = $dailyDepositeRecord->where('plan_id',7);
                    }
                    elseif($request->slug == 'monthly')
                    {
                        $dailyDepositeRecord = $dailyDepositeRecord->whereIn('plan_id',[2,3,5,6,10,11]);
                    }
                    $currentDate = Carbon\Carbon::now(); 
                    $currentDate = date("Y-m-d", strtotime(convertDate($currentDate)));
                        //// marurity date ciondition 
                    $dailyDepositeRecord=$dailyDepositeRecord->whereDate('maturity_date','>=',$currentDate)->whereDate('created_at','!=',$currentDate);
                        if($arrFormData['start_date'] !=''){                 
                            $startDate = $arrFormData['start_date'];
                            $currentDate = date("Y-m-d", strtotime(convertDate($startDate)));
                            }
                        if($arrFormData['scheme_account_number'] !=''){
                            $sAccountNumber=$arrFormData['scheme_account_number'];
                            $dailyDepositeRecord=$dailyDepositeRecord->where('account_number','LIKE','%'.$sAccountNumber.'%');
                        }
                        if($arrFormData['associate_code'] !=''){
                            $associateCode=$arrFormData['associate_code'];
                            $dailyDepositeRecord=$dailyDepositeRecord->whereHas('associateMember', function ($query) use ($associateCode) {
                            $query->where('members.associate_no','LIKE','%'.$associateCode.'%');
                            });
                        }
                        if($request->slug == 'monthly'){
                            if($arrFormData['plan'] !=''){
                            $plam=$arrFormData['plan'];
                            $dailyDepositeRecord=$dailyDepositeRecord->where('plan_id',$plam);    
                            }
                        }   
                        if($arrFormData['branch'] !=''){
                            $branch=$arrFormData['branch'];
                            $dailyDepositeRecord=$dailyDepositeRecord->where('branch_id',$branch);
                        } 
                    $count =$dailyDepositeRecord->orderby('created_at','desc')->count('id');
                    $data = $dailyDepositeRecord->orderby('created_at','desc')->offset($_POST['start'])->limit($_POST['length'])->get();
                    $rowReturn = array(); 
                    $totalCount =$count;
                    foreach($data as $sno => $row)
                    {
                        $record = $this->getRecords($row,$currentDate);
                        if($row->plan_id==1)
                        {
                        $tenure = 'N/A';
                        }
                        else
                        {
                        $tenure = $row->tenure.' Year';
                        }
                        if($row['associateMember'])
                        {
                            $associate_Name=$row['associateMember']['first_name'].' '.$row['associateMember']['last_name'];
                        }
                        else{
                            $associate_Name="N/A";
                        }
                        if($row['associateMember'])
                        {
                        $associateNumber=$row['associateMember']['associate_no'];  
                        }
                        else{
                            $associateNumber="N/A";
                        }
                        $sno++;
                        $val['DT_RowIndex']=$sno;
                        $val['branch_name']=$row['branch']->name;
                        $val['branch_code']=$row['branch']->branch_code;
                        $val['so_name']=$row['branch']->sector;
                        $val['ro_name']=$row['branch']->regan;
                        $val['zo_name']=$row['branch']->zone; 
                        $val['opening_date']=date("d/m/Y", strtotime( $row['created_at']));
                        $val['current_date']=date("d/m/Y", strtotime( $currentDate));
                        if(isset($row['member']->first_name))
                        {
                            $val['member']=$row['member']->first_name.' '.$row['member']->last_name;
                            $val['member_id']=$row['member']->member_id;
                        } 
                        else{
                            $val['member']='N/A';
                            $val['member_id']='N/A';
                        }
                        if($row['member']->mobile_no){
                            $val['mobile_no']= $row['member']->mobile_no;
                        }else{
                            $val['mobile_no']='N/A';
                        }
                        $val['associate_code']=$associateNumber;
                        $val['associate_name']=$associate_Name;
                        $val['account_no']=$row->account_number;
                        $val['plan_name']=$row['plan']->name;
                        $val['tenure']=$tenure;
                        $val['deno_amount']=$row->deposite_amount;
                        if(isset($record['pendingEmi']))
                        {
                            $val['due_emi']=$record['pendingEmi'] ;
                        }
                        else{
                            $val['due_emi']=0;
                        }
                        if(isset($record['pendingEmiAMount']))
                        {
                            $val['due_emi_amount']=$record['pendingEmiAMount'] ;
                        }
                        else{
                            $val['due_emi_amount']=0 ;
                        }
                        $rowReturn[] = $val; 
                } 
                $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn, );
                return json_encode($output);
            }
            else{
                $output = array( "draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0, );
                return json_encode($output);
            }    
        }
    }  
    public function export(Request $request)
    {  
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/')."/asset/due_report.csv"; 
        $fileName = env('APP_EXPORTURL') . "asset/due_report.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $dailyDepositeRecord = Memberinvestments::has('company')->with('plan','member','associateMember','branch')->where('is_deleted',0)->where('is_mature',1);
        if($request->slug == 'daily')
        {
            $dailyDepositeRecord = $dailyDepositeRecord->where('plan_id',7);
        }
        elseif($request->slug == 'monthly')
        {
            $dailyDepositeRecord = $dailyDepositeRecord->whereIn('plan_id',[2,3,5,6,10,11]);
        }
        $currentDate = Carbon\Carbon::now(); 
        $currentDate = date("Y-m-d", strtotime(convertDate($currentDate)));
            if($request['start_date'] !=''){                 
              $startDate = $request['start_date'];
              $currentDate = date("Y-m-d", strtotime(convertDate($startDate)));
            }
         // marurity date ciondition      
        $dailyDepositeRecord=$dailyDepositeRecord->whereDate('maturity_date','>=',$currentDate)->whereDate('created_at','!=',$currentDate);  
             if($request['scheme_account_number'] !=''){
                $sAccountNumber=$request['scheme_account_number'];
                $dailyDepositeRecord=$dailyDepositeRecord->where('account_number','LIKE','%'.$sAccountNumber.'%');
            }
            if($request['associate_code'] !=''){
                $associateCode=$request['associate_code'];
                $dailyDepositeRecord=$dailyDepositeRecord->whereHas('associateMember', function ($query) use ($associateCode) {
                  $query->where('members.associate_no','LIKE','%'.$associateCode.'%');
                });
            }
             if($request['plan'] !=''){
                $plam=$request['plan'];
                $dailyDepositeRecord=$dailyDepositeRecord->where('plan_id',$plam);    
            }   
            if($request['branch'] !=''){
                    $branch=$request['branch'];
                    $dailyDepositeRecord=$dailyDepositeRecord->where('branch_id',$branch);
                } 
            if($request['investments_export'] == 0){
            $totalResults=$dailyDepositeRecord->orderby('created_at','DESC')->count();
            $results=$dailyDepositeRecord->orderby('created_at','DESC')->offset($start)->limit($limit)->get();
            $result = 'next';
            if( ($start + $limit ) >= $totalResults){
                $result = 'finished';
            }
            // if its a fist run truncate the file. else append the file
            if($start==0) {
                $handle = fopen($fileName, 'w');
            }else{
                $handle = fopen($fileName, 'a');
            }
            if( ($start + $limit ) >= $totalResults){
                $result = 'finished';
            }
            // if its a fist run truncate the file. else append the file
            if($start==0) {
                $handle = fopen($fileName, 'w');
            }else{
                $handle = fopen($fileName, 'a');
            }
            if($start==0) {
                $headerDisplayed = false;
            }else{
                $headerDisplayed = true;
            }
            $sno=$_POST['start'];
            foreach($results as  $row)
            {
                $record = $this->getRecords($row,$currentDate);
                 if($row->plan_id==1)
                {
                  $tenure = 'N/A';
                }
                else
                {
                  $tenure = $row->tenure.' Year';
                }
                if($row['associateMember'])
                {
                    $associate_Name=$row['associateMember']['first_name'].' '.$row['associateMember']['last_name'];
                }
                 else{
                     $associate_Name="N/A";
                }
                if($row['associateMember'])
                {
                  $associateNumber=$row['associateMember']['associate_no'];  
                }
                 else{
                    $associateNumber="N/A";
                }
                $sno++;
                $val['s/n']=$sno;
                $val['br name']=$row['branch']->name;
                $val['br code']=$row['branch']->branch_code;
               // $val['so_name']=$row['branch']->sector;
                //$val['ro_name']=$row['branch']->regan;
                //$val['zo_name']=$row['branch']->zone; 
                $val['opening_date']=date("d/m/Y", strtotime( $row['created_at']));
                $val['current_date']=date("d/m/Y", strtotime( $currentDate));
                if(isset($row['member']->first_name))
                {
                    $val['member']=$row['member']->first_name.' '.$row['member']->last_name;
                    $val['member_id']=$row['member']->member_id;
                } 
                else{
                    $val['member']='N/A';
                    $val['member_id']='N/A';
                }
                if($row['member']->mobile_no)
                {
                    $val['Mobile No']=$row['member']->mobile_no;
                } 
                else{
                    $val['Mobile No']='N/A';
                }
                $val['associate code']=$associateNumber;
                $val['associate name']=$associate_Name;
                $val['account no']=$row->account_number;
                $val['plan name']=$row['plan']->name;
                $val['tenure']=$tenure;
                $val['deno amount']=$row->deposite_amount;
                if(isset($record['pendingEmi']))
                {
                    $val['due emi']=$record['pendingEmi'] ;
                }
                else{
                    $val['due emi']=0;
                }
                 if(isset($record['pendingEmiAMount']))
                {
                    $val['due emi amount']=$record['pendingEmiAMount'] ;
                }
                else{
                    $val['due emi amount']=0 ;
                }
                 if (!$headerDisplayed) {
                // Use the keys from $data as the titles
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
                }
                // Put the data into the stream
                fputcsv($handle, $val);
            } 
             fclose($handle);
            $percentage = ($start+$limit)*100/$totalResults;
            $percentage = number_format((float)$percentage, 1, '.', '');
            // Output some stuff for jquery to use
            $response = array(
                'result'        => $result,
                'start'         => $start,
                'limit'         => $limit,
                'totalResults'  => $totalResults,
                'fileName' => $returnURL,
                'percentage' => $percentage
            );
            echo json_encode($response);
            // Make sure nothing else is sent, our file is done
            exit;
        } 
    }
}