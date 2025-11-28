<?php

namespace App\Http\Controllers\Admin\LoanFromBank;

use Illuminate\Http\Request;

use Auth;

use App\Models\Settings;

use App\Http\Controllers\Controller;

use App\Models\Branch;

use App\Models\AccountHeads;

use App\Models\SamraddhBank; 

use App\Models\SamraddhBankAccount; 

use App\Models\LoanFromBank;

use App\Models\LoanEmi;



use Validator;  
use Carbon\Carbon;
use DB;
use URL;
use Session;
use Image;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Admin\CommanController;



class LoanFromBankController extends Controller

{

    public function index()

    {
         if(check_my_permission( Auth::user()->id,"55") != "1"){
      return redirect()->route('admin.dashboard');
      }
       
      
      $data['title'] = "Account Head Management | Loan From Bank";

        return view('templates.admin.loan_from_bank.index',$data);

    }

    

    public function createLoanFromBank (Request $request)

    {

        if(check_my_permission( Auth::user()->id,"54") != "1"){
      return redirect()->route('admin.dashboard');
    }
     
     
     $data['title'] = "Account Head Management | Create Loan From Bank";

         $data['banks'] = SamraddhBank::with('bankAccount')->where('status',1)->get();



        return view('templates.admin.loan_from_bank.createLoanFromBank',$data);

    }

    public function getloanaccount()
    {
      $data = LoanFromBank::select('loan_account_number')->get();
      return response()->json($data);
    }

    public function storeLoanFromBank(Request $request)

    {

       $head_id =  AccountHeads::orderBy('head_id','desc')->first('head_id');



     $rules = [

            'bank_name' => 'required',

             'address' => 'required',

            'emi_amount' => 'required',

            'start_date' => 'required',

            'end_date' => 'required',

             'branch_name' => 'required',

            'loan_amount' => 'required',

            'remark' => 'required',

            'loan_account_number' => 'required',

            'loan_interest_rate' => 'required',

            'no_of_emi' => 'required',

            'received_bank_name' => 'required',

            'received_bank_account' => 'required',

            

        ];
       
         $customMessages = [

            'required' => 'The :attribute field is required.'

        ];

        $this->validate($request, $rules, $customMessages);

        DB::beginTransaction();

        try {

         $parentHeadDetail = AccountHeads::where('head_id',18)->first();

         $head_data['sub_head'] = $request->bank_name;
         $head_data['head_id'] = $head_id->head_id+1;
         $head_data['labels'] = 4;
         $head_data['parent_id'] = 18;
         $head_data['parentId_auto_id'] = $parentHeadDetail->id;
         $head_data['cr_nature'] = $parentHeadDetail->cr_nature;
         $head_data['dr_nature'] = $parentHeadDetail->dr_nature;
         $head_data['is_move'] = 1;
         $head_data['status'] = 0;

         $idget=AccountHeads::create($head_data);

         $account_head = AccountHeads::where('id',$idget->id)->first();

         $data['bank_name'] = $request->bank_name;

          $data['current_balance'] = $request->loan_amount;

         $data['branch_name'] = $request->branch_name;

         $data['address'] = $request->address;

         $data['emi_amount'] = $request->emi_amount;

         $data['emi_start_date'] = date("Y-m-d", strtotime( str_replace('/','-', $request['start_date'])));

         $data['emi_end_date'] =date("Y-m-d", strtotime( str_replace('/','-', $request['end_date'])));

         $data['account_head_id'] = $account_head->head_id;

         $data['loan_amount'] = $request->loan_amount;

         $data['remark'] = $request->remark;

         $data['loan_account_number'] = $request->loan_account_number;

         $data['loan_interest_rate'] = $request->loan_interest_rate;

         $data['number_of_emi'] = $request->no_of_emi;

         $data['received_bank'] = $bank_id =$request->received_bank_name;

         $data['received_bank_account'] = $bank_ac_id =$request->received_bank_account;
         $loanCreate=LoanFromBank::create($data);
     
    $encodeDate = json_encode($data);
    $arrs = array("loan_from_bank_id" => $loanCreate->id, "type" => "16", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Loan From bank Create", "data" => $encodeDate);
    DB::table('user_log')->insert($arrs);

         $globaldate=$request->created_at;  


            $currency_code='INR'; 

             $randNumber = mt_rand(0,999999999999999);
              $v_no = NULL;
              $v_date =NULL;


            $select_date = $request['start_date'];
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;

            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create))); 
            Session::put('created_at', $created_at);



            $created_by=1;
            $created_by_id=\Auth::user()->id;
            $created_by_name=\Auth::user()->username;  
            $amount=$request->loan_amount;



         $type=17;$sub_type=171;$type_id=$account_head->head_id;
        $type_transaction_id=$loanCreate->id;
        $daybookRef=CommanController::createBranchDayBookReferenceNew($amount,$globaldate);
            $refId=$daybook_ref_id=$daybookRef;
            $member_id=NULL;

            $bankDtail=getSamraddhBank($bank_id);
            $bankAcDetail=getSamraddhBankAccountId($bank_ac_id);

            $amount_from_id=$type;$amount_from_name=getAcountHeadNameHeadId($type_id);            
            $amount_to_id=$bank_id;$amount_to_name=$bankDtail->bank_name.'('.$bankAcDetail->account_no.')';

            $payment_mode=2;

          $des=getAcountHeadNameHeadId($type_id).' Loan amount transfer to bank '.$amount_to_name.'  through online';
          $description_cr= 'Bank A/c Dr '.$amount.'/-';
          $description_dr='To '.getAcountHeadNameHeadId($type_id).' A/c Cr '.$amount.' /-';

          $ssb_account_id_to= NULL;  $cheque_bank_from_id= NULL;  $cheque_bank_ac_from_id= NULL;  $cheque_bank_to_name= NULL;  $cheque_bank_to_branch= NULL;  $cheque_bank_to_ac_no= NULL;  $cheque_bank_to_ifsc= NULL;  
          $transction_bank_from_id= $type_id;  
          $transction_bank_from_ac_id= NULL;  
          $transction_bank_to_name= $bankDtail->bank_name;  
          $transction_bank_to_ac_no= $bankAcDetail->account_no;  $transction_bank_to_branch= $bankAcDetail->branch_name;  
          $transction_bank_to_ifsc =$bankAcDetail->ifsc_code;

            $ssb_account_id_from= NULL;  $cheque_no= NULL;  $cheque_date= NULL;  $cheque_bank_from= NULL;  $cheque_bank_ac_from= NULL;  $cheque_bank_ifsc_from= NULL;  $cheque_bank_branch_from= NULL;  $cheque_bank_to= NULL;  $cheque_bank_ac_to= NULL;  
          $transction_no= NULL; 

          $transction_bank_from= $request->bank_name;  
          $transction_bank_ac_from= $request->loan_account_number;  
          $transction_bank_ifsc_from= NULL;  
          $transction_bank_branch_from= $request->branch_name;  
          $transction_bank_to= $bank_id;  
          $transction_bank_ac_to= $bank_ac_id;  
          $transction_date = $entry_date;

          $branch_id=NULL; $associate_id=NULL; $branch_id_to=NULL; $branch_id_from=NULL; $opening_balance=NULL; $closing_balance=NULL; 
          $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL ;            
        /// ------------- bank head -------------        
              
              $lastheadId=$bankDtail->account_head_id;

            $allTran2=CommanController::headTransactionCreate($daybook_ref_id,$branch_id,$bank_id,$bank_ac_id,$lastheadId,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$des,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);



            $smbdc=CommanController::samraddhBankDaybookCreate($daybook_ref_id,$bank_id,$bank_ac_id,$type,$sub_type,$type_id,$associate_id=NULL,$member_id,$branch_id=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$des,$description_dr,$description_cr,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,
            $transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc); 
        //-----------   bank balence  ---------------------------
           /// $bankClosing=CommanController:: checkCreateBankClosing($bank_id,$bank_ac_id,$created_at,$amount,0);

            if ($current_date == $entry_date)
            {
              $bankClosing = CommanController::checkCreateBankClosing($bank_id, $bank_ac_id, $created_at, $amount, 0);
            }
            else
            {
              $bankClosing = CommanController::checkCreateBankClosingCRBackDate($bank_id, $bank_ac_id, $created_at, $amount, 0);
            }
    /// -------------------- loan from bank -----------------            

            $allTranlb=CommanController::headTransactionCreate($daybook_ref_id,$branch_id,$bank_id,$bank_ac_id,$type_id,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$des,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);




        

         DB::commit();

        } 

        catch (\Exception $ex) 

        {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }

         return redirect()->route('admin.loan_from_bank')->with('success', 'Head Created Successfully!');

    }

    

    public function reportListing(Request $request)

  {

        if ($request->ajax() && check_my_permission( Auth::user()->id,"55") == "1") {

        $data = LoanFromBank::orderBy('id','DESC')->get();

        // fillter array 

        // $arrFormData = array();   

        // if(!empty($_POST['searchform']))

        // {

        //     foreach($_POST['searchform'] as $frm_data)

        //     {

        //         $arrFormData[$frm_data['name']] = $frm_data['value'];

        //     }

        // }

        // //print_r($arrFormData);die;



        //     $data = ShareHolder::orderBy('id','asc');



        //     /******* fillter query start ****/        

        //   if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')

        //     {

        //         if($arrFormData['type'] !=''){

        //             $type=$arrFormData['type'];

                   

        //             $data=$data->where('type',$type);

        //         }

              

                

        //     }

            

        /******* fillter query End ****/   

           

        //   $data = $data->get();



            return Datatables::of($data)

            ->addIndexColumn()

            ->addColumn('bank_name', function($row){

                 

                return $row->bank_name;

            })

            ->rawColumns(['bank_name'])

            ->addColumn('branch_name', function($row){

                 

                return $row->branch_name;

            })

            ->rawColumns(['branch_name'])

            ->addColumn('loan_amount', function($row){

                

                return number_format((float) $row->loan_amount, 2, '.', '');

            })

            ->rawColumns(['loan_amount'])

            ->addColumn('current_balance', function($row){

                

                return number_format((float) $row->current_balance, 2, '.', '');

            })

            ->rawColumns(['current_balance'])

            ->addColumn('loan_account_number', function($row){

               

                return $row->loan_account_number;

            })

            ->rawColumns(['loan_account_number'])

            ->addColumn('loan_interest_rate', function($row){

               

                return number_format((float) $row->loan_interest_rate, 2, '.', '');

            })

            ->rawColumns(['loan_interest_rate'])

           

            

            ->addColumn('number_of_emi', function($row){

                return $row->number_of_emi;

            })

            ->rawColumns(['number_of_emi'])

            ->addColumn('received_bank', function($row){

                

               $bank_name =  getSamraddhBank($row->received_bank);

                return $bank_name->bank_name;

            })

            ->rawColumns(['received_bank'])





            ->addColumn('received_bank_account', function($row){

                $account = SamraddhBankAccount::where('bank_id',$row->received_bank)->first();

                return $account->account_no;

            })

            ->rawColumns(['received_bank_account']) 

            ->addColumn('remark', function($row){
              return $row->remark;
            })
            ->rawColumns(['remark'])



            ->addColumn('start_date', function($row){
              return date("d/m/Y", strtotime(convertDate($row->emi_start_date)));
            })
            ->rawColumns(['start_date'])

            ->addColumn('end_date', function($row){
              return date("d/m/Y", strtotime(convertDate($row->emi_end_date)));
            })
            ->rawColumns(['end_date'])






             ->addColumn('action', function($row){
                $btn = '';    
                $head_id=AccountHeads::where('head_id',$row->account_head_id)->first();
               

                $url = URL::to("admin/loanFromBank/edit/".$row->id."");
                if(check_my_permission(Auth::user()->id,222) == 1 || check_my_permission(Auth::user()->id,223) ==1 || check_my_permission(Auth::user()->id,224) == 1)
                {

                

                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                if(check_my_permission(Auth::user()->id,222) == 1 )
                {
                $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                }
                $headLedger = URL::to("admin/loanFromBank/ledger/".$row->id.'/'.$row->account_head_id.'/'.$head_id->labels."");
                 $btn .= '<a class="dropdown-item" href="'.$headLedger.'" target="blank"><i class="icon-list mr-2"></i>Transactions</a>';       

                // $statusUrl = URL::to("admin/eli-loan/updateStatus/".$row->id."");

               if($row->status == 0){
                 if(check_my_permission(Auth::user()->id,224) == 1 )
                {
                    $btn .= '<button class="dropdown-item" href="#" title="" onclick="statusUpdate('.$row->account_head_id.')"><i class="icon-checkmark4 mr-2"></i>Active</button>  ';
                 }   
                }else{
                     if(check_my_permission(Auth::user()->id,223) == 1 )
                {
                  $btn .= '<button class="dropdown-item" href="#" title="" onclick="statusUpdate('.$row->account_head_id.')"><i class="icon-checkmark4 mr-2"></i>Deactive</button>  ';  
              }
                }

                $btn .= '</div></div></div>'; 
                }         

                return $btn;

            })

            ->rawColumns(['action'])

            ->make(true);

        }

  }

  

  public function edit_loan_from_bank($id)

  {
    if(check_my_permission(Auth::user()->id,222) != 1 || check_my_permission(Auth::user()->id,55)!= 1 )
    {
        return redirect()->route('admin.dashboard');
    }

      $data['title'] = "Account Head Management | Edit Loan From Bank";

      $data['banks'] = SamraddhBank::with('bankAccount')->where('status',1)->get();

     $data['data'] = LoanFromBank::where('id',$id)->first();



     return view('templates.admin.loan_from_bank.edit_loan_from_bank',$data);

  }

  

  public function update_loan_from_bank(Request $request)

  {     

        $rules = [
            
            'loan_account_number' => 'unique:loan_from_banks,loan_account_number,'.$request->id,
            

        ];

         $customMessages = [
            'required' => 'The :attribute field is unique.'
        ];

        $this->validate($request, $rules, $customMessages);

     try {
          $head_data['sub_head'] = $request->bank_name;      

         AccountHeads::where('head_id',$request->head_id)->update($head_data);
          $data['bank_name'] = $request->bank_name;
          $data['branch_name'] = $request->branch_name;
         $data['address'] = $request->address;
         $data['emi_amount'] = $request->emi_amount;

         $data['emi_start_date'] = date("Y-m-d", strtotime( str_replace('/','-', $request['start_date'])));

         $data['emi_end_date'] =date("Y-m-d", strtotime( str_replace('/','-', $request['end_date'])));

         //$data['loan_amount'] = $request->loan_amount;

         $data['remark'] = $request->remark;

         $data['loan_account_number'] = $request->loan_account_number;

         $data['loan_interest_rate'] = $request->loan_interest_rate;

         $data['number_of_emi'] = $request->no_of_emi;

         $data['received_bank'] = $request->received_bank_name;

         $data['received_bank_account'] = $request->received_bank_account;

         //$data['current_balance'] = $request->loan_amount;

         LoanFromBank::where('id',$request->id)->update($data);

        

         DB::commit();

        } 

        catch (\Exception $ex) 

        {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }

        return redirect()->route('admin.loan_from_bank')->with('success', 'Head Updated Successfully!');

  }

  

       public function updateStatus(Request $request)

    {

      // print_r($_POST);die;

        $headStatus = AccountHeads::select('status','id')->where('head_id',$request->head_id)->first();

       $statusLoan = LoanFromBank::select('status','id')->where('account_head_id',$request->head_id)->first();

       $statusLoanUpdate = LoanFromBank::findOrFail($statusLoan->id);

        $updateStatus = AccountHeads::findOrFail($headStatus->id);

        if($headStatus->status == 0  ){

             $updateStatus->status = 1; 
        }else{
            $updateStatus->status = 0; 
        }
        if($statusLoan->status == 0  ){

             $statusLoanUpdate->status = 1; 
        }else{
            $statusLoanUpdate->status = 0; 
        }     

      $updateStatus=$updateStatus->save();

     $statusLoanUpdate = $statusLoanUpdate->save(); 

      $message = [ $updateStatus];

        return response()->json($updateStatus);

    }





     public function loan_emi()

    {

        if(check_my_permission( Auth::user()->id,"56") != "1"){
      return redirect()->route('admin.dashboard');
    } 
    
    
    $data['title'] = " Loan From Bank | Loan Emi";

        $data['account'] = LoanFromBank::where('status',1)->get();

        $data['banks'] = SamraddhBank::with('bankAccount')->where('status',1)->get();

        return view('templates.admin.loan_from_bank.loan_emi',$data);

    }   



    public function get_loan_account_detail(Request $request)

    {

        $id = $request->id;
        $dateGet='';

        $data['loanData'] =LoanFromBank::where('id',$id)->first();
        if($data['loanData'])
        {
          $dateGet=date("d/m/Y", strtotime(convertDate($data['loanData']->emi_start_date)));
        }
        $data['dateGet']=$dateGet;
        return response()->json($data);

    }



    public function save_loan_emi(Request  $request)
    {

        $rules = [
            'bank_name' => 'required',
            'loan_amount' => 'required',
            'loan_account_number' => 'required',
            'date' => 'required',
            'emi_number' => 'required',
            'emi_principal_amount' => 'required',
            'emi_interest_rate' => 'required',
            'current_loan_amount' => 'required',
            'received_bank_name' => 'required',
            'received_bank_account' => 'required',          

        ];

         $customMessages = [
            'required' => 'The :attribute field is required.'
        ];



        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {

          $globaldate=$request->created_at; 

            
            $currency_code='INR'; 

             $randNumber = mt_rand(0,999999999999999);
              $v_no = NULL;
              $v_date =NULL;


            $created_by=1;
            $created_by_id=\Auth::user()->id;
            $created_by_name=\Auth::user()->username; 
            $amount=$request->emi_principal_amount;


            $select_date = $request->date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;

            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create))); 
            Session::put('created_at', $created_at);


        $accountDetail = LoanFromBank::where('id',$request->loan_account_number)->first();
        $data['loan_bank_name'] = $request->bank_name;
        $data['account_head_id'] = $request->account_head_id;
        $data['loan_amount'] = $request->loan_amount;
        $data['loan_emi_date'] = date("Y-m-d", strtotime( str_replace('/','-', $request['date'])));
        $data['loan_bank_account'] = $request->loan_account_number;
         $data['loan_from_bank_id'] = $request->loan_from_bank_id;
        $data['emi_principal_amount'] = $request->emi_principal_amount;
        $data['emi_interest_rate'] = $request->emi_interest_rate;
        $data['emi_number'] = $request->emi_number;
        $data['received_bank'] = $bank_id = $request->received_bank_name;
        $data['received_bank_account'] = $bank_ac_id = $request->received_bank_account;
        $loanEmiCreate = LoanEmi::create($data);

        $remaningBalance = $accountDetail->current_balance - $request->emi_principal_amount;
        $accountDetail->update(['current_balance' =>  $remaningBalance]);
        $amount1=$request->emi_principal_amount+$request->emi_interest_rate;

        $type=17;$sub_type=172;$type_id=$request->account_head_id;
        $type_transaction_id=$loanEmiCreate->id;
        $daybookRef=CommanController::createBranchDayBookReferenceNew($amount1,$globaldate);
            $refId=$daybook_ref_id=$daybookRef;
            $member_id=NULL;

            $bankDtail=getSamraddhBank($bank_id);
            $bankAcDetail=getSamraddhBankAccountId($bank_ac_id);

            $amount_from_id=$bank_id;$amount_from_name=$bankDtail->bank_name.'('.$bankAcDetail->account_no.')';
            
            $amount_to_id=$type_id;$amount_to_name=getAcountHeadNameHeadId($type_id);

            $payment_mode=4;

          $des=getAcountHeadNameHeadId($type_id).' Loan emi payment through auto debit(ECS) '.$amount_from_name;
          $bkAmount =$request->emi_interest_rate+$amount;
          $description_dr= 'To Bank A/c Cr '.$bkAmount.'/-';
          $description_cr=getAcountHeadNameHeadId($type_id).' A/c Dr '.$bkAmount.' /-';

          $ssb_account_id_to= NULL;  $cheque_bank_from_id= NULL;  $cheque_bank_ac_from_id= NULL;  $cheque_bank_to_name= NULL;  $cheque_bank_to_branch= NULL;  $cheque_bank_to_ac_no= NULL;  $cheque_bank_to_ifsc= NULL;  $transction_bank_from_id= NULL;  $transction_bank_from_ac_id= NULL;  $transction_bank_to_name= NULL;  $transction_bank_to_ac_no= NULL;  $transction_bank_to_branch= NULL;  $transction_bank_to_ifsc =NULL;
            $ssb_account_id_from= NULL;  $cheque_no= NULL;  $cheque_date= NULL;  $cheque_bank_from= NULL;  $cheque_bank_ac_from= NULL;  $cheque_bank_ifsc_from= NULL;  $cheque_bank_branch_from= NULL;  $cheque_bank_to= NULL;  $cheque_bank_ac_to= NULL;  $transction_no= NULL;  $transction_bank_from= NULL;  $transction_bank_ac_from= NULL;  $transction_bank_ifsc_from= NULL;  $transction_bank_branch_from= NULL;  $transction_bank_to= NULL;  $transction_bank_ac_to= NULL;  $transction_date = NULL;

            $branch_id=NULL; $associate_id=NULL; $branch_id_to=NULL; $branch_id_from=NULL; $opening_balance=NULL; $closing_balance=NULL; 
          $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL ;  


          /// -------------- interest entry ----------------

        $allTranINterest=CommanController::headTransactionCreate($daybook_ref_id,$branch_id,$bank_id,$bank_ac_id,97,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$request->emi_interest_rate,$closing_balance,$des,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);


            
        /// ------------- bank head -------------
            $bkAmount =$request->emi_interest_rate+$amount;

            $allTran2=CommanController::headTransactionCreate($daybook_ref_id,$branch_id,$bank_id,$bank_ac_id,$bankDtail->account_head_id,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$bkAmount,$closing_balance,$des,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);



            $smbdc=CommanController::samraddhBankDaybookCreate($daybook_ref_id,$bank_id,$bank_ac_id,$type,$sub_type,$type_id,$associate_id=NULL,$member_id,$branch_id=NULL,$opening_balance=NULL,$bkAmount,$closing_balance=NULL,$des,$description_dr,$description_cr,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,
            $transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc); 
        //-----------   bank balence  ---------------------------
           // $bankClosing=CommanController:: checkCreateBankClosingDR($bank_id,$bank_ac_id,$created_at,$amount1,0);

            if ($current_date == $entry_date)
                {                    
                    $bankClosing = CommanController::checkCreateBankClosingDR($bank_id,$bank_ac_id,$created_at,$amount1,0);
                }
                else
                {
                    $bankClosing = CommanController::checkCreateBankClosingDRBackDate($bank_id,$bank_ac_id,$created_at,$amount1,0);
                }

    /// -------------------- loan from bank -----------------

            $allTranlb= CommanController::headTransactionCreate($daybook_ref_id,$branch_id,$bank_id,$bank_ac_id,$type_id,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$des,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);



         DB::commit();

        } 

        catch (\Exception $ex) 

        {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }

         return redirect()->route('admin.loan_emi')->with('success', 'Loan Emi Payment Created Successfully!');

    } 



    public function loan_emi_report()

    {
         
    if(check_my_permission( Auth::user()->id,"57") != "1"){
      return redirect()->route('admin.dashboard');
    }
    
        $data['title'] = "Account Head Management | Loan From Bank | Loan Emi List";
        $data['loan'] = LoanFromBank::where('status',1)->get();

        return view('templates.admin.loan_from_bank.loan_emi_list',$data);

    } 



     public function loanemiReportListing(Request $request)

    {

//print_r($_POST);die;
          if ($request->ajax()) {

        $data = LoanEmi::with('loanBank');
        //fillter array 

        // $arrFormData = array();    
        if(!empty($_POST['searchform']))
        {
          foreach($_POST['searchform'] as $frm_data)
          {
            $arrFormData[$frm_data['name']] = $frm_data['value'];
          }
        } 
        /******* fillter query start ****/       
        if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
        {
          if($arrFormData['loanAccount'] !=''){
            $loanAccount=$arrFormData['loanAccount'];
            $data=$data->where('loan_from_bank_id',$loanAccount);
          }
        }
        /******* fillter query End ****/   
        $data = $data->orderBy('id','DESC')->get();
          


            return Datatables::of($data)

            ->addIndexColumn()

            ->addColumn('bank_name', function($row){ 
                return $row['loanBank']->bank_name;
            })
            ->rawColumns(['bank_name'])
            ->addColumn('bank_account', function($row){  
                return $row['loanBank']->loan_account_number;            
              })           

            ->rawColumns(['bank_account'])
            ->addColumn('emi_date', function($row){
              return date("d/m/Y", strtotime($row->loan_emi_date));
            })

            ->rawColumns(['emi_date'])

            ->addColumn('loan_amount', function($row){
              return number_format((float)$row['loanBank']->loan_amount, 2, '.', '');
            })

            ->rawColumns(['loan_amount'])

            ->addColumn('emi_number', function($row){
              return $row->emi_number;
            })
            ->rawColumns(['emi_number'])  
            ->addColumn('emi_amount', function($row){
                return number_format((float)$row->emi_principal_amount, 2, '.', '');
            })
            ->rawColumns(['emi_amount'])
             ->addColumn('emi_interest_rate', function($row){
                return number_format((float)$row->emi_interest_rate, 2, '.', '');
            })
            ->rawColumns(['emi_interest_rate'])
            ->addColumn('received_bank', function($row){
             $bank_name =  getSamraddhBank($row->received_bank);
                return $bank_name->bank_name;
            })
            ->rawColumns(['received_bank'])
             ->addColumn('received_bank_account', function($row){
                $account = SamraddhBankAccount::where('bank_id',$row->received_bank)->first();
                return $account->account_no;
            })
            ->rawColumns(['received_bank_account'])             

            ->make(true);

        }

    }


    public function ledger($id,$head_id,$label){
    $data['title'] = "Account Head Management | Loan From Bank Ledger Report"; 
             
       $data['head'] = $head_id;  
       $data['label'] = $label;     
        $data['detail']= LoanFromBank::where('id',$id)->first(); 
    return view('templates.admin.loan_from_bank.ledger',$data);
  }

  public function ledgerListing(Request $request)
  {



  if ($request->ajax()) {
    //print_r($request->head);die;
    $arrFormData = array(); 
    if(!empty($_POST['searchform']))
        {
          foreach($_POST['searchform'] as $frm_data)
          {
            $arrFormData[$frm_data['name']] = $frm_data['value'];
          }
        } 
      //  print_r($arrFormData['head']);die;
            
        $id = $arrFormData['head'];
        $label = $arrFormData['label'];
        $info='head'.$label;
        $date = $arrFormData['start_date1'];
        $end_date = $arrFormData['end_date1'];             

            $data=\App\Models\AllHeadTransaction:: where('head_id',$id)->where('type',17); 

            



                  if($date !='')
                  {
                      $startDate=date("Y-m-d", strtotime(convertDate($date)));
                      if($end_date!=''){
                      $endDate=date("Y-m-d ", strtotime(convertDate($end_date)));
                       $data=$data->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate]); 
                      }
                      else
                      {                        
                        $endDate='';
                        $data=$data->where(\DB::raw('DATE(entry_date)'),'>=',$startDate); 
                      }
                     
                  }

             
           $data = $data->orderBy('entry_date','ASC')->get();
           $balance=0;
            return Datatables::of($data)
            ->addIndexColumn()  
            ->addColumn('type', function($row){
                $getTransType = \App\Models\TransactionType::where('type',$row->type)->where('sub_type',$row->sub_type)->first();
                $type = '';
                if($row->type == $getTransType->type)
                {
                    if($row->sub_type == $getTransType->sub_type)
                    {
                        $type = $getTransType->title;
                    }
                }
                if($row->type == 21)
                    {
                         $record = \App\Models\ReceivedVoucher::where('id',$row->type_id)->first();
                         if($record )
                         {
                             $type= $record->particular;                   
                         }    
                        else{
                            $type="N/A";
                        }
                }



                    return $type;
                })
                ->rawColumns(['type'])
            ->addColumn('amount', function($row){
               if($row->payment_type=='CR')
                {
               return number_format((float)$row->amount, 2, '.', '');
             }
             else{
                return 0;
             }
            })
            ->rawColumns(['amount'])
            ->addColumn('amount1', function($row){
               if($row->payment_type=='DR')
                {
               return number_format((float)$row->amount, 2, '.', '');
             }
             else{
                return 0;
             }
            })
            ->rawColumns(['amount1'])
            ->addColumn('amount2', function($row) use (&$balance){
           
               if($row->payment_type=='DR')
                {
                    $balance=$balance-$row->amount;
                }
                else{
                      $balance=$balance+$row->amount;
                }

                return number_format((float)$balance, 2, '.', '');
            })
            ->rawColumns(['amount2']) 


            ->addColumn('description', function($row){
                if($row)
                {
                    return $row->description;
                }
                else{
                    return "N/A";
                }
            })
            ->rawColumns(['description']) 
             
            ->addColumn('payment_type', function($row){
                $payment_type = 'N/A';
                if($row->payment_type=='DR')
                {
                    $payment_type = 'Debit';
                }
                if($row->payment_type=='CR')
                {
                    $payment_type = 'Credit';
                }
                return $payment_type;
               
            })
            ->rawColumns(['payment_type'])
            ->addColumn('payment_mode', function($row){
                $payment_type = 'N/A'; 
                if($row->payment_mode==0)
                {
                    $payment_mode = 'Cash';
                }
                if($row->payment_mode==1)
                {
                    $payment_mode = 'Cheque';
                }
                if($row->payment_mode==2)
                {
                    $payment_mode = 'Online Transfer';
                }
                if($row->payment_mode==3)
                {
                    $payment_mode = 'SSB Transfer Through JV';
                }
                if($row->payment_mode==4)
                {
                    if($row->payment_type=='CR')
                    {
                        $payment_mode =  "Auto Credit";
                    }
                    else
                    {
                        $payment_mode = "Auto Debit";
                    }
                }
                if($row->payment_mode==6)
                {
                   
                        $payment_mode =  "JV";
                    
                }
               
                return $payment_mode;
               
            })
            ->rawColumns(['payment_mode']) 
            ->addColumn('received_bank', function($row){
               if($row->payment_mode==4)
                {
                     
                      $bank_id = $row->amount_from_id;

                       $bankDtail=getSamraddhBank($bank_id);
                        

                      return $bankDtail->bank_name;
                     
               } 
               if($row->payment_mode==2)
                {
                     
                      $transction_bank_to_name = $row->transction_bank_to_name;
                      return $transction_bank_to_name;
                     
                    
               }
              
            })
            ->rawColumns(['received_bank']) 
             ->addColumn('received_bank_account', function($row){
                if($row->payment_mode==2)
                {

                     
                      $transction_bank_to_ac_no = $row->transction_bank_to_ac_no;
                      return $transction_bank_to_ac_no;
                     
                    
               }
               if($row->payment_mode==4)
                {
                     
                      $bank_id = $row->amount_from_id;

                       $bankDtail=getSamraddhBank($bank_id);
                        $bankAcDetail=SamraddhBankAccount::where('bank_id',$bank_id)->first();

                      return $bankAcDetail->account_no;
                     
               } 

            })
            ->rawColumns(['received_bank_account'])    
             ->addColumn('date', function($row){
                if($row->entry_date)
                {
                    
                    $date = date("d/m/Y", strtotime(convertDate($row->entry_date)));
                    return $date;
                }
                else{
                    return "N/A";
                }
                
            })
            ->rawColumns(['date'])       
            
            ->make(true);
        }
    }


    

    

}