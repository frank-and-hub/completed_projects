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
use Illuminate\Support\Facades\Response;
use Validator;
use Session;
use Redirect;
use URL;
use App\Models\User;
use App\Models\Member;
use App\Models\Loans;
use App\Models\Files;
use App\Models\Memberloans;
use App\Models\Grouploans;
use App\Models\Branch;
use App\Models\LoanDayBooks;

class MemberLoanController extends Controller
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
     * Show Member loan list by member id
     * Route: /branch/member/loan
     * Method: get 
     * @param  $id,$msg
     * @return  array()  Response
     */
    public function index($id)
    {
       
	     if(!in_array('View Loan List', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 }
	    $data['title']='Member | Loans';
        $data['memberDetail'] = Member::where('id',$id)->first(['id','member_id','first_name','last_name']);
        return view('templates.branch.loan_management.member_loan', $data);
    }

     /**
     * Fetch loan listing data by member id.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function membersLoanListing(Request $request)
    { 

        if ($request->ajax()) {
            $memberId=$request['member_id'];
            $data = Memberloans::with('loan','loanMemberAssociate')->whereHas('loan', function ($query) {
                $query->where('loan_type', '!=', 'G'); 
            })->where('customer_id',$memberId)->orderBy('id', 'DESC')->get();
         
            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('company_name', function($row){
            $company_name = $row['company']->name;
            return $company_name;
           })
           ->rawColumns(['company_name'])

            ->addColumn('date', function($row){
                 $date = date("d/m/Y", strtotime($row->created_at));
                return $date;
            })
            ->rawColumns(['date']) 
            ->addColumn('loan_name', function($row){
                $loan_name = $row['loan']->name;
                return $loan_name;
            })
            ->rawColumns(['loan_name'])
            ->rawColumns(['account_number'])
            ->addColumn('account_number', function($row){
                $account_number = $row->account_number;
                return $account_number;
            })
            ->rawColumns(['account_number'])
            ->addColumn('amount', function($row){
                $amount = $row->deposite_amount;
                return $amount;
            })
            ->escapeColumns('loan_name')
             ->addColumn('loan_amount', function($row){
                $amount = $row->amount;
                return $amount;
            })
            ->escapeColumns('loan_name')
            ->addColumn('transfer_amount', function($row){
                $transfer_amount = $row->transfer_amount;
                return $transfer_amount;
            })
            ->escapeColumns('transfer_amount')
            ->addColumn('file_charges', function($row){
                if($row->file_charges){
                    $file_charges = $row->file_charges;
                }else{
                    $file_charges = '0.00';
                }
                return $file_charges;
            })
            ->escapeColumns(['loan_name'])
            ->addColumn('insurance_charge', function($row){
                $insurance_charge = $row->insurance_charge??'0';
                return $insurance_charge;
            })
            ->rawColumns(['insurance_charge'])
            ->addColumn('file_charges_payment_mode', function($row){
                if ($row->file_charge_type == 0) {
                    return $file_charges_payment_mode = 'Cash';
                } elseif ($row->file_charge_type == 1) {
                    return $file_charges_payment_mode = 'Loan Amount';
                } else {
                    return $file_charges_payment_mode = 'N/A';
                }
            })
            ->rawColumns(['file_charges_payment_mode'])
            ->addColumn('branch', function($row){
                $branch = Branch::where('id',$row->branch_id)->first()->name;
                return $branch;
            })
            ->rawColumns(['branch'])
            ->addColumn('associate_code', function($row){
                $associate_code = $row['loanMemberAssociate']->associate_no;
                return $associate_code;
            })
            ->rawColumns(['associate_code'])
            ->addColumn('associate_name', function($row){
                $associate_name = $row['loanMemberAssociate']->first_name.' '.$row['loanMemberAssociate']->last_name;
                return $associate_name;
            })
            ->rawColumns(['associate_name'])
            ->addColumn('approve_date', function($row){
                if($row['approve_date']){
                    return date("d/m/Y", strtotime( $row['approve_date']));
                }else{
                    return 'N/A';
                }
            })
            ->rawColumns(['approve_date'])
            ->addColumn('status', function($row){
                if($row->status == 0){
                    $status = 'Pending';
                }else if($row->status == 1){
                    $status = 'Approved';
                }else if($row->status == 2){
                    $status = 'Rejected';
                }else if($row->status == 3){
                    $status = 'Clear';
                }else if($row->status == 4){
                    $status = 'Due';
                }else if ($row->status == 5)
                {
                    $status = 'Rejected';
                }
                else if ($row->status == 6)
                {
                    $status = 'Hold';
                }
                else if ($row->status == 7)
                {
                    $status = 'Approved Hold';
                }
                return $status;
            })
            ->rawColumns(['status'])
            ->addColumn('action', function($row){
                $url = URL::to("branch/loan/emi-transactions/".$row->id."/".$row['loan']->loan_type."");
                $btn = '<a class="dropdown-item" href="'.$url.'"><i class="fas fa-eye mr-2"></i> View</a>';          
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }
    /**
     * Fetch loan listing data by member id.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function membersGroupLoanListing(Request $request)
    { 

        if ($request->ajax()) {
            $memberId=$request['member_id'];
            $data = Grouploans::with('loan','loanBranch','loanMemberAssociate')->whereHas('loan', function ($query) {
                $query->where('loan_type', '=', 'G');
            })->where('customer_id',$memberId)->orderBy('id', 'DESC')->get();
          
            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('company_name', function($row){
                $company_name = $row['company']->name;
                return $company_name;
               })
               ->rawColumns(['company_name'])
            ->addColumn('date', function($row){
                 $date = date("d/m/Y", strtotime($row->created_at));
                return $date;
            })
            ->rawColumns(['date']) 
            ->addColumn('loan_name', function($row){
                $loan_name = $row['loan']->name;
                return $loan_name;
            })
            ->rawColumns(['loan_name'])
            ->rawColumns(['account_number'])
            ->addColumn('account_number', function($row){
                $account_number = $row->account_number;
                return $account_number;
            })
            ->rawColumns(['account_number'])
            ->addColumn('leader', function($row){
                $member =Member::where('id',$row->groupleader_member_id)->first(['id','first_name','last_name']);
                $leader = $member->first_name.' '.$member->last_name;
                return $leader;
            })
            ->rawColumns(['leader'])
            ->addColumn('amount', function($row){
                $amount = $row->deposite_amount;
                return $amount;
            })
            ->rawColumns(['amount'])
            	->addColumn('loan_amount', function($row){
                $amount = $row->amount ;
                return $amount;
            })
            ->rawColumns(['loan_amount'])
            ->addColumn('file_charges', function($row){
                if($row->file_charges){
                    $file_charges = $row->file_charges;
                }else{
                    $file_charges = '0.00';
                }
                return $file_charges;
            })
            ->escapeColumns(['loan_name'])
            ->addColumn('insurance_charge', function($row){
                $insurance_charge = $row->insurance_charge;
                return $insurance_charge;
            })
            ->rawColumns(['insurance_charge'])
            ->addColumn('file_charges_payment_mode', function($row){
                if ($row->file_charge_type == 0) {
                    return $file_charges_payment_mode = 'Cash';
                } elseif ($row->file_charge_type == 1) {
                    return $file_charges_payment_mode = 'Loan Amount';
                } else {
                    return $file_charges_payment_mode = 'N/A';
                }
            })
            ->rawColumns(['file_charges_payment_mode'])
            ->addColumn('total_amount', function($row){
                $total_amount = $row->amount;
                return $total_amount;
            })
            ->escapeColumns(['leader'])
            ->addColumn('branch', function($row){
                $branch = Branch::where('id',$row->branch_id)->first()->name;
                return $branch;
            })
            ->rawColumns(['branch'])
            ->addColumn('associate_code', function($row){
                $associate_code = $row['loanMemberAssociate']->associate_no;
                return $associate_code;
            })
            ->rawColumns(['associate_code'])
            ->addColumn('associate_name', function($row){
                $associate_name = $row['loanMemberAssociate']->first_name.' '.$row['loanMemberAssociate']->last_name;
                return $associate_name;
            })
            ->rawColumns(['associate_name'])
            ->rawColumns(['memberNo'])
            ->addColumn('approve_date', function($row){
                if($row['approve_date']){
                    return date("d/m/Y", strtotime( $row['approve_date']));
                }else{
                    return 'N/A';
                }
            })
            ->rawColumns(['approve_date'])
            ->addColumn('status', function($row){
                if($row->status == 0){
                    $status = 'Pending';
                }else if($row->status == 1){
                    $status = 'Approved';
                }else if($row->status == 2){
                    $status = 'Rejected';
                }else if($row->status == 3){
                    $status = 'Clear';
                }else if($row->status == 4){
                    $status = 'Due';
                }
                return $status;
            })
            ->rawColumns(['status'])
            ->addColumn('action', function($row){
                $url = URL::to("branch/loan/emi-transactions/".$row->id."/".$row['loan']->loan_type);
                $btn = '<a class="dropdown-item" href="'.$url.'"><i class="fas fa-eye mr-2s"></i>View</a>';          
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

    /**
     * Show Member loan detail
     * Route: /branch/member/loan/detail
     * Method: get 
     * @param  $id,$memberId
     * @return  array()  Response
     */
    public function LoanDetail($id,$memberId)
    {
        $data['title']='Member | Loan';
        $data['memberDetail'] = Member::where('id',$memberId)->first(['id','member_id','first_name','last_name']);
        $data['loanDetail'] = Memberloans::with('loan')->where('id',$id)->first();
        return view('templates.branch.loan_management.member_loandetail', $data);
    }

    /**
     * Loan EMI transactions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function emiTransactionsView($id,$type)
    {
        if ($type != "G"){
		
		  if(!in_array('Loan Transactions', auth()->user()->getPermissionNames()->toArray())){  
				return redirect()->route('branch.dashboard');
				}
		
            $data['title']='Loan EMI Transactions';
            $data['loanDetails'] = Memberloans::select('loan_type', 'account_number')->whereHas('loans', function ($q) {
                $q->where('loan_type', 'L')->select('id', 'name', 'loan_type');
            })->where('id', $id)->first();
            
            $data['loanTitle'] = $data['loanDetails']->loan->name;
        }else{
            
			if(!in_array('Group Loan Transactions', auth()->user()->getPermissionNames()->toArray())){  //group loan
				return redirect()->route('branch.dashboard');
				}
			$data['title']='Group Loan EMI Transactions';
            $data['title'] = 'Group Loan EMI Transactions';
            $data['loanDetails'] = Grouploans::select('account_number','loan_type')->whereHas('loans', function ($q) {
                $q->where('loan_type', 'G')->select('id', 'name', 'loan_type');
            })->where('id', $id)->first();
            $data['loanTitle'] = $data['loanDetails']->loan->name;
        }
        $data['id'] = $id;
        $data['type'] = $type;
        return view('templates.branch.loan_management.loan_emi_transaction', $data);
    }

    /**
     *EMI transaction ajax listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function emiTransactionsList(Request $request)
    {
        if (!empty($_POST['searchform']))
            {
                foreach ($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            
        if ($request->ajax()) {
            if ($request['loanType'] != "G"){
                $loanRecord = Memberloans::Where('id',$request['loanId'])->first('transfer_amount');
                //$data=LoanDayBooks::where('loan_type',$request['loanType'])->where('loan_id',$request['loanId'])->get(); 
           }else{
                $loanRecord = Grouploans::Where('id',$request['loanId'])->first('transfer_amount');
           }
           /*$data=LoanDayBooks::/*where('loan_type',$request['loanType'])->*///where('account_number',$loanRecord->account_number);

        //    $company_id = $arrFormData['emi_transaction_company_id'];

           $data=LoanDayBooks::with('loan_member_company:id,member_id,company_id')
                ->whereHas('loan_plan', function ($q) use ($request) {
                    $q->where('loan_type', $request['loanType'])
                        ->where('loan_sub_type','!=',2)
                        ->select('id', 'name', 'loan_type','loan_sub_type')
                        ;
                    })
                    ->where('loan_id',$request['loanId'])
                    ->where('is_deleted',0); 
           
           $data1=$data->get();
            $count=count($data1);

            $data=$data->offset($_POST['start'])->limit($_POST['length'])->orderBy('payment_date','asc')->get();

                   

            $dataCount = LoanDayBooks::where('loan_id','=',$request->loanId)->where('loan_sub_type','!=',2);
			$totalCount =$dataCount->count();

                    

            $sno=$_POST['start'];

            $rowReturn = array(); 
			$total = 0;
            foreach($data as $row)
			{
				$sno++;

                $val['DT_RowIndex']=$sno;

                $val['transaction_id']=$row->id;

                $val['date']=date("d/m/Y", strtotime($row->payment_date));
				$paymentMode = '';
				  if($row->payment_mode == 0){

                    $paymentMode = 'Cash';

                }elseif($row->payment_mode == 1){

                    $paymentMode = 'Cheque';

                }elseif($row->payment_mode == 2){

                    $paymentMode = 'DD';

                }elseif($row->payment_mode == 3){

                    $paymentMode = 'Online Transaction';

                }elseif($row->payment_mode == 4){

                    $paymentMode = 'By Saving Account ';

                }
                elseif($row->payment_mode == 6){

                    $paymentMode = 'JV ';

                }
				$val['payment_mode']=$paymentMode;
				$val['sanction_amount']= $loanRecord->transfer_amount;
				
				$val['description']= $row->description;
				
				if($row->loan_sub_type == 1){

                    $deposite =  $row->deposit;

                    $val['penalty'] =  $deposite;

                }else{

                     $val['penalty'] =  '0';

                }
				if($row->loan_sub_type == 0){

                     $deposite =  $row->deposit;;

                     $val['deposite'] =  $deposite;

                }else{

                     $val['deposite'] =  '0';

                }
                if($row->jv_journal_amount){

                     $jv_journal_amount =  $row->jv_journal_amount;;

                     $val['jv_amount'] =  number_format((float)$jv_journal_amount, 2, '.', '');

                }else{

                     $val['jv_amount'] =  '0';

                }
				 
				
			    if($row['loan_member_company']->member_id > 0 && isset($row['loan_member_company']->member_id))
                {
                    $val['customer_id'] = $row['loan_member_company']->member_id;
                }
                else{
                    $val['customer_id'] = 'N/A';
                }
                if($row->igst_charge > 0 && isset($row->igst_charge))
                {
                    $val['igst_charge'] = number_format((float)$row->igst_charge, 2, '.', '');
                }
                else{
                    $val['igst_charge'] = '0';
                }
                if($row->cgst_charge > 0 && isset($row->cgst_charge))
                {
                    $val['cgst_charge'] = number_format((float)$row->cgst_charge, 2, '.', '');
                }
                else{
                    $val['cgst_charge'] = '0';
                }
                if($row->sgst_charge > 0 && isset($row->sgst_charge))
                {
                    $val['sgst_charge'] = number_format((float)$row->sgst_charge, 2, '.', '');
                }
                else{
                    $val['sgst_charge'] = '0';
                }
				
                $total = $total+$row->deposit + $val['sgst_charge'] +$val['cgst_charge']+ $val['igst_charge'];
				$val['balance'] = number_format((float)$total, 2, '.', '');
				$rowReturn[] = $val; 

			}
            
            /*return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('transaction_id', function($row){
                $transaction_id = $row->id;
                return $transaction_id;
            })
            ->rawColumns(['transaction_id'])
            ->addColumn('date', function($row){
                $date = date("d/m/Y", strtotime($row->payment_date));
                return $date;
            })
            ->rawColumns(['date'])
            ->addColumn('payment_mode', function($row){
                if($row->payment_mode == 0){
                    $payment_mode =  'Cash';
                }elseif($row->payment_mode == 1){
                    $payment_mode =  'Cheque';
                }elseif($row->payment_mode == 2){
                    $paymentMode = 'DD';
                }elseif($row->payment_mode == 3){
                    $payment_mode =  'Online Transaction';
                }elseif($row->payment_mode == 4){
                    $payment_mode =  'By Saving Account';
                }
                return $payment_mode;
            })
            ->rawColumns(['payment_mode'])
            ->addColumn('description', function($row){
                $description =  $row->description;
                return $description;
            })
            ->rawColumns(['description'])
            ->addColumn('penalty', function($row){
                if($row->loan_sub_type == 1){
                    $deposite =  $row->deposit.' <i class="fas fa-rupee-sign"></i>';
                    return $deposite;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('deposite', function($row){
                if($row->loan_sub_type == 0){
                    $deposite =  $row->deposit.' <i class="fas fa-rupee-sign"></i>';
                    return $deposite;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('roi_amount', function($row){
                if($row->loan_sub_type == 0){
                    $roi_amount =  $row->roi_amount.' <i class="fas fa-rupee-sign"></i>';
                    return $roi_amount;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('principal_amount', function($row){
                if($row->loan_sub_type == 0){
                    $principal_amount =  $row->principal_amount.' <i class="fas fa-rupee-sign"></i>';
                    return $principal_amount;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('opening_balance', function($row){
                if($row->loan_sub_type == 0){
                    $opening_balance =  $row->opening_balance.' <i class="fas fa-rupee-sign"></i>';
                    return $opening_balance;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->make(true);*/
        }
        $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );



          return json_encode($output);
    }
}
