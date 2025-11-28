<?php

namespace App\Http\Controllers\Api\LoanManagement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{LoanDayBooks};
use Validator;
use DB;
use App\Services\InvestmentService;

class LoanController extends Controller
{
    public function loanListing(Request $request)
    {
        //checking the mendatory field 
        $validator = Validator::make($request->all(), [
            'loan_type' => 'required',           
        ], [
            'required' => 'The :attribute field is required.',
        ]);

        //if the validation fails then error show
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $associate_no = $request->input('associate_no');
        $loanType = $request->input('loan_type', 'L'); //Checking the loan_type values if not assign then by default it will assign L
        $plan = $request->input('plan'); //if user not assign the plan then assign the null value
        $branch_id = $request->input('branch_id'); //if request not received of the branch then null assign 
        $account_number = $request->input('account_number', "");
        $from_date = $request->input('from_date');
        $start_date = date('Y-m-d',strtotime(convertDate($from_date))); //convert the date format

        $to_date = $request->input('to_date');
        $end_date = date('Y-m-d',strtotime(convertDate($to_date))); //convert the date format

        $pageNo = $request->input('page_no','1'); // if the page_no not assigned by user then by default assign 1 .
        $transaction_by = $request->input('transaction_by'); //if the transaction_by not assigned by user then by default assign 0 for ('web')
        $limit = 20; //limit set 20 static
        $start = ($pageNo - 1) * $limit;

        //Calling the getLoanDetails function for fetch the data 
        $getLoanDetails = $this->getLoanDetails($loanType,$plan,$branch_id,$start_date,$end_date,$transaction_by,$start,$limit,$associate_no,$account_number);

        //if the data not found then this condition will work and show the data not found message.
        if(!$getLoanDetails)
        {
            $LoanServices = new InvestmentService();
            return $LoanServices->handleDataNotFound('Data Not Found'); //return the message with the "Data Not Found"
        }
        //Calling the getLoanListing funciton with the data.
        $loanListing = $this->getLoanListing($getLoanDetails[0]);

        return response()->json([
            'Status'=>'success',
            'Message'=>'Reterive Details',
            'Data' =>$loanListing,
            'Total Records'=>$getLoanDetails[1],
            'Limit'=>$limit,
            'Page'=>$pageNo,
        ]);
        
    }

    //Retrieve the data from table (Table loan_day_books)
    public function getLoanDetails($loanType,$plan,$branch_id,$start_date,$end_date,$transaction_by,$start,$limit,$associate_no,$account_number)
    {
        $data = LoanDayBooks::wherehas('company')->with([
            'loan_member:id,member_id,first_name,last_name,associate_code',
            'loanBranch:id,name,branch_code,sector,regan,zone',
            'member_loan:id,emi_option,emi_period,applicant_id,emi_amount,customer_id',
            'member_loan.loanMemberCompany:id,member_id',
            'group_member_loan_via_id:id,emi_option,emi_period,applicant_id,member_id,customer_id',
            'group_member_loan_via_id.loanMemberCompanyid:id,member_id',
            'group_member_loan_via_id.member:id,member_id,first_name,last_name',
            'company:id,name',           
            'loan_plan:id,name,loan_type',
            'allHeadTransaction:id,is_app,daybook_ref_id'
        ])
        ->where('is_deleted','0')
        ->whereHas('loan_plan', function ($q) use ($loanType) {
            $q->where('loan_type', $loanType)->select('id', 'name', 'loan_type');
        })
        ->when($plan != '',function($q) use ($plan){ //when the plan not empty then fetching data with the plan
            $q->where('loan_type',$plan);
        })
        ->when($branch_id !='',function($q) use ($branch_id){ //when the branch id not empty then fetching data with the branch
            $q->where('branch_id',$branch_id);
        })
        ->when(($start_date != ''),function($q) use ($start_date,$end_date){ //when the from date select then fetching the data as per the selected date
            $q->when($end_date !='1970-01-01',function($q)use($start_date,$end_date){
                $q->whereBetween(\DB::raw('DATE(created_at)'),[$start_date,$end_date]);
            })->whereDate('created_at', '>=', $start_date);
        })
        ->when($transaction_by !='',function($q) use ($transaction_by){
            $q->where('is_app',$transaction_by);
        })
        ->whereHas('loanMemberAssociate',function($q) use ($associate_no)
        {
            $q->when($associate_no !='',function($q) use ($associate_no){
                $q->where('associate_no',$associate_no);
            });
        })
        ->when($account_number !='',function($q) use ($account_number){
            $q->where('account_number',$account_number);
        })
        ->where('status', '1');
        
        // if($transaction_by != '')
        // {
        //     $data->whereHas('allHeadTransaction',function($query) use ($transaction_by){
        //         $query->where('is_app',$transaction_by);
        //     });
        // }
        $totalRecords = $data->count('id');
        $data = $data->offset($start)->limit($limit)->get();
        return [$data,$totalRecords];
    }

    //Arrange the all fetched data in the correct form
    public function getLoanListing($data) 
    {
        $rowReturn = array();
        $sno = 1;
        foreach($data as $row)
        {
            //Checking the payment_mode value as per the value assign the which payment mode used at that time.
            switch($row->payment_mode)
            {
                case 1:
                    {
                        $payment_mode = 'Cheque';
                        break;
                    }
                case 2: 
                    {
                        $payment_mode = 'DD';
                        break;
                    }
                case 3:
                    {
                        $payment_mode = 'Online Transaction';
                        break;
                    }
                case 4: 
                    {
                        $payment_mode = 'By Saving Account';
                        break;
                    }
                default:
                {
                    $payment_mode = 'Cash'; //By default set the payment mode "Cash"
                    break;
                }
            }
            if(($row['allHeadTransaction']) != ''){
                switch($row['allHeadTransaction'][0]->is_app)
                {
                    case 0:
                    {
                        $transaction_by = 'Web';
                        break;
                    }
                    case 1:
                    {
                        $transaction_by = 'Associate App';
                        break;
                    }
                    case 2:
                    {
                        $transaction_by = 'E-Passbook';
                        break;
                    }
                }
            }else{
                $transaction_by = 'null';
            }
            if(($row['loan_plan']['loan_type'] === 'L'))
            {
                $first_name = ($row['member_loan']->member->first_name)??'';
                $last_name = ($row['member_loan']->member->last_name)??'';
            }
            else{
                $first_name = ($row['group_member_loan_via_id']['member']->first_name)??'';
                $last_name = ($row['group_member_loan_via_id']['member']->last_name)??'';    
            }
            
            $val = [
                's_no'=>$sno++,
                'emi_date'=>date('d/m/Y',strtotime(convertDate($row->created_at))),
                'branch'=>$row['loanBranch']->name .' - '. $row['loanBranch']->branch_code,
                'customer_id' => ($row['loan_plan']['loan_type'] === 'L') ? $row['member_loan']->member->member_id ?? 'N/A'   : $row['group_member_loan_via_id']['member']->member_id  ?? 'N/A'  ,
                'name' =>$first_name.' '. $last_name,
                'account_no'=>($row->account_number)??'N/A',
                'plan'=>($row['loan_plan']['name'])??'N/A',
                'emi_amount'=>($row->deposit)??'N/A',
                'payment_mode'=>($payment_mode)??'N/A',
                'transaction_by'=>($transaction_by)??'N/A',
            ];
            $rowReturn[] = $val;
        }

        return $rowReturn; //return the data in the rowReturn variable

    }

}
