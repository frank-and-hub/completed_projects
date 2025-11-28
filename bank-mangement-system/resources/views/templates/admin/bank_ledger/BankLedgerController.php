<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Auth;

use App\Http\Controllers\Controller;

use App\Models\Branch;

use Yajra\DataTables\DataTables;

use App\Models\SamraddhBankDaybook ;

use Carbon\Carbon;

use App\Models\SamraddhBank;

use App\Models\AccountHeads;

use Session;

use App\Models\SamraddhBankAccount ;

use DB;



class BankLedgerController  extends Controller

{





	public function __construct(){

		$this->middleware('auth');

	}



	// bank ledger report

	public function bankLedgerreport(){

	    

// 	   // if($request->session()->has('totalBalance'))

// 		{

//         $request->session()->forget('totalBalance');

//     	}

    	

    	$data['title'] = "Bank Ledger Report";

    	$data['branches'] = Branch::get();

    	$data['banks'] = SamraddhBank::get();

    	return view('templates.admin.bank_ledger.report',$data);

		

	}

	

	public function bankLedgerListing(Request $request)

	{

	   

		$pageStart = $_POST['start'];

		$start = $_POST['start'];

		$totalBalance = $_POST['balance'];

        $startAt = $_POST['draw'];

        $length = $_POST['length'];

        $search = $_POST['search']['value'];

		$from_start = $start;

	    

		$bank_id = '';

		$startDate = '';

		$endDate = '';

		$bankAccount_id = '';

//      	if($request->session()->has('totalBalance'))

// 		{

//         $request->session()->forget('totalBalance');

//     	}

	 	if ($request->ajax()) {

			$arrFormData = array();   

	        if(!empty($_POST['searchform']))

	        {

	            foreach($_POST['searchform'] as $frm_data)

	            {



	                $arrFormData[$frm_data['name']] = $frm_data['value'];

	            }

	        }
			
			
			// Now Count row start................................................................../

				$data1 = SamraddhBankDaybook ::with(['Branch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}]);

            

					if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')

					{

					    

					            

						if($arrFormData['bank_name'] !='' && $arrFormData['bank_account'] !=''){



							$bank_id=$arrFormData['bank_name'];

							 $bankAccount_id=$arrFormData['bank_account'];

						 

							 $data1=$data1->where(function($q) use($bank_id,$bankAccount_id){

								$q->orwhere('account_id','=',$bankAccount_id)->orwhere('amount_from_id','=',$bank_id)->orwhere('amount_to_id',$bank_id);

							});

						   

						}

						if($arrFormData['start_date'] !=''){

							

							$startDate=date("Y-m-d", strtotime(convertDate($arrFormData['start_date' ] )));

							$created_at=date("Y-m-d", strtotime(convertDate($arrFormData['created_at' ] )));

							//$startDate=date('Y-m-d', strtotime($startDate."-1 days"));

						   

							if($arrFormData['end_date'] !=''){

								$endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));

							}

							else

							{

								$endDate='';

							}

							$data1=$data1->whereBetween(\DB::raw('entry_date'), ["".$startDate."", "".$endDate.""]); 

						

						

						}

					}

					

					$whereCond = '((type = "3" && bank_id = "'.$bank_id.'") ||(type = "4" && amount_from_id = "'.$bank_id.'") ||(type = "10" && bank_id = "'.$bank_id.'") || (type = "7" && amount_to_id = "'.$bank_id.'") || (type="5" && amount_to_id = "'.$bank_id.'") || (type = "8" && (amount_to_id = "'.$bank_id.'" ||(type = "12" && bank_id = "'.$bank_id.'")||(type = "14" && amount_to_id = "'.$bank_id.'")||(type = "15" && amount_to_id = "'.$bank_id.'") || (type = "16" && amount_to_id = "'.$bank_id.'") || (type = "17" && amount_to_id = "'.$bank_id.'")|| (type = "18" && amount_from_id = "'.$bank_id.'")||amount_from_id = "'.$bank_id.'")) || (account_id = "'.$bank_id.'") ||  (type = "13" && amount_from_id = "'.$bank_id.'"))';

			

					$data1=$data1->whereRaw($whereCond);

				

					$totalCount = $data1->orderBy('entry_date','asc')->count();

			// Now count row end .................................................................../
			

            $date = Carbon::today()->toDateString();

        	$data = SamraddhBankDaybook ::with(['Branch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}])->offset($start)->limit($totalCount);

            

        	if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')

        	

            {

                if( $_POST['start'] == 0)

                {

                  	if($request->session()->has('totalBalance'))

		        {

                    $request->session()->forget('totalBalance');

    	        }     

                }

                

            	

                if($arrFormData['bank_name'] !='' && $arrFormData['bank_account'] !=''){



                    $bank_id=$arrFormData['bank_name'];

                    $bank_name=$arrFormData['name'];

                  

                     $bankAccount_id=$arrFormData['bank_account'];

                 

                     $data=$data->where(function($q) use($bank_id,$bankAccount_id){

                    	$q->orwhere('account_id','=',$bankAccount_id)->orwhere('amount_from_id','=',$bank_id)->orwhere('amount_to_id',$bank_id);

                    });

                   

                }

                if($arrFormData['start_date'] !=''){

                    

                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['start_date' ] )));

                    $created_at=date("Y-m-d", strtotime(convertDate($arrFormData['created_at' ] )));

                    //$startDate=date('Y-m-d', strtotime($startDate."-1 days"));

                   

                    if($arrFormData['end_date'] !=''){

                    	$endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));

                    }

                    else

                    {

                        $endDate='';

                    }

                    $data=$data->whereBetween(\DB::raw('entry_date'), ["".$startDate."", "".$endDate.""]); 

                    

                    	

					

                }

            }

			

			$whereCond = '((type = "3" && bank_id = "'.$bank_id.'") ||(type = "4" && amount_from_id = "'.$bank_id.'") ||(type = "10" && bank_id = "'.$bank_id.'") || (type = "7" && amount_to_id = "'.$bank_id.'") || (type="5" && amount_to_id = "'.$bank_id.'") || (type = "8" && (amount_to_id = "'.$bank_id.'" ||(type = "12" && bank_id = "'.$bank_id.'")||(type = "14" && amount_to_id = "'.$bank_id.'")||(type = "15" && amount_to_id = "'.$bank_id.'") || (type = "16" && amount_to_id = "'.$bank_id.'") || (type = "17" && amount_to_id = "'.$bank_id.'")|| (type = "18" && amount_from_id = "'.$bank_id.'")||amount_from_id = "'.$bank_id.'")) || (account_id = "'.$bank_id.'") ||  (type = "13" && amount_from_id = "'.$bank_id.'"))';

			

			$data=$data->whereRaw($whereCond);

			

            $data = $data->orderBy('entry_date','asc')->get();

// 		


			

				$endNumber = $pageStart + $length;

			  





	

            $rowReturn = array(); 

			

			// Start code opening balance...................................../

			if($bank_id!= "" && $bankAccount_id!= "" && count($data) > 0 && $pageStart == 0){

				

				$val = array();

				

				if($startDate!= ""){

					// if($data)

					// {

						$existRecord = DB::table('samraddh_bank_closing')->where('entry_date',$startDate)->where('bank_id',$bank_id)->exists();

					if($existRecord)

					{

						$SamraddhBankOpeningData = DB::table('samraddh_bank_closing')->where('entry_date',$startDate)->where('bank_id',$bank_id)->orderBy('id','DESC')

						->select('*')->get();

						$balance = $SamraddhBankOpeningData[0]->opening_balance;

					 }

					else{

						$SamraddhBankOpeningData = DB::table('samraddh_bank_closing')->where('bank_id',$bank_id)->orderBy('id','DESC')

						->select('*')->first();

						$balance = $SamraddhBankOpeningData->opening_balance;



					}

					

					// }

					// else{

					// 	$startDate=date('Y-m-d', strtotime($startDate."-1 days"));



					// 	$SamraddhBankOpeningData = DB::table('samraddh_bank_closing')->where('entry_date',$startDate)->where('bank_id',$bank_id)->orderBy('id','DESC')

					// 	->select('*')->get();

					// 	$balance = $SamraddhBankOpeningData[0]->closing_balance;

					// }

					



				} else {

					$SamraddhBankOpeningData = DB::table('samraddh_bank_closing')->where('bank_id',$bank_id)->orderBy('entry_date','ASC')

					->select('*')->get();

					$balance = $SamraddhBankOpeningData[0]->opening_balance;



				}

				

				$val["Sr_no"] = "";

				$val['DT_RowIndex']=" ";

				$val['id']= "";

				$val['daybook_ref_id']= "";

				$val['bank_id']= "";

				$val['account_id']= "";

				$val['type']= "";

				$val['sub_type']= "";

				$val['type_id']= "";

				$val['type_transaction_id']= "";

				$val['associate_id']= "";

				$val['member_id']= "";

				$val['branch_id']= "";

				$val['opening_balance']= "";

				$val['closing_balance']= "";

				$val['description']= "";

				$val['description_dr']= "";

				$val['description_cr']= "";

				$val['payment_type']= "";

				$val['payment_mode']= "";

				$val['currency_code']= "";

				$val['amount_to_id']= "";

				$val['amount_to_name']= "";

				$val['amount_from_id']= "";

				$val['amount_from_name']= "";

				$val['v_no']= "";

				$val['v_date']= "";

				$val['ssb_account_id_from']= "";

				$val['ssb_account_id_to']= "";

				$val['cheque_no']= "";

				$val['cheque_date']= "";

				$val['cheque_bank_from']= "";

				$val['cheque_bank_ac_from']= "";

				$val['cheque_bank_ifsc_from']= "";

				$val['cheque_bank_branch_from']= "";

				$val['cheque_bank_from_id']= "";

				$val['cheque_bank_ac_from_id']= "";

				$val['cheque_bank_to']= "";

				$val['cheque_bank_ac_to']= "";

				$val['cheque_bank_to_name']= "";

				$val['cheque_bank_to_branch']= "";

				$val['cheque_bank_to_ac_no']= "";

				$val['cheque_bank_to_ifsc']= "";

				$val['transction_no']= "";

				$val['transction_bank_from']= "";

				$val['transction_bank_ac_from']= "";

				$val['transction_bank_ifsc_from']= "";

				$val['transction_bank_branch_from']= "";

				$val['transction_bank_from_id']= "";

				$val['transction_bank_from_ac_id']= "";

				$val['transction_bank_to']= "";

				$val['transction_bank_ac_to']= "";

				$val['transction_bank_to_name']= "";

				$val['transction_bank_to_ac_no']= "";

				$val['transction_bank_to_branch']= "";

				$val['transction_bank_to_ifsc']= "";

				$val['transction_date']= "";

				$val['entry_date']= "";

				$val['entry_time']= "";

				$val['created_by']= "";

				$val['created_by_id']= "";

				$val['created_at']= "";

				$val['updated_at']= "";

				$val['branch_code']= "";

				$val['branch_name']= "";

				$val['date']= "";

				$val['account_number']= "";

				$val['particular']= "Opening Balance";

				$val['account_head_no']= "";

				$val['account_head_name']= "";

				$val['cheque_no']= "";

				$val['credit']= " ";

				$val['debit']= "";

					

				

				if($balance){

					$val['balance']= $balance;

					

				} else {

					$val['balance']= 0;

				}

				

				$rowReturn[] = $val; 

				

			}

			//end opening balance........................................//

			

			

             

//              $count = 0;

// 			$head = array();

            

		   /*

            foreach($data as $i => $transaction)

            {

               

                if($transaction->type == 7 && $transaction->amount_to_id ==$bank_id )

                {

                    $head[] = $transaction;

                     

                }

              else if($transaction->type == 8 && ($transaction->amount_to_id ==$bank_id || $transaction->amount_from_id ==$bank_id ))

              {

                    $head[] = $transaction;

              }

              else if($transaction->account_id == $bank_id)

              {

                  $head[] = $transaction;

              }

              $data = $head;

            }

			*/

			 $totalbalance =$totalBalance;

			 foreach ($data as $row)

            {    

				$val = array();

				 $start++;

				$val["Sr_no"] = $start;

                $val['DT_RowIndex']= $start;

                $val['id']= $row->id;

                $val['daybook_ref_id']= $row->daybook_ref_id;

				$val['bank_id']= $row->bank_id;

				$val['account_id']= $row->account_id;

				$val['type']= $row->type;

				$val['sub_type']= $row->sub_type;

				$val['type_id']= $row->type_id;

				$val['type_transaction_id']= $row->type_transaction_id;

				$val['associate_id']= $row->associate_id;

				$val['member_id']= $row->member_id;

                $val['branch_id']= $row->branch_id;

				$val['opening_balance']= $row->opening_balance;

				$val['closing_balance']= $row->closing_balance;

				$val['description']= $row->description;

				$val['description_dr']= $row->description_dr;

				$val['description_cr']= $row->description_cr;

				$val['payment_type']= $row->payment_type;

				$val['payment_mode']= $row->payment_mode;

				$val['currency_code']= $row->currency_code;

				$val['amount_to_id']= $row->amount_to_id;

				$val['amount_to_name']= $row->amount_to_name;

				$val['amount_from_id']= $row->amount_from_id;

				$val['amount_from_name']= $row->amount_from_name;

				$val['v_no']= $row->v_no;

				$val['v_date']= $row->v_date;

				$val['ssb_account_id_from']= $row->ssb_account_id_from;

				$val['ssb_account_id_to']= $row->ssb_account_id_to;

				$val['cheque_no']= $row->cheque_no;

				$val['cheque_date']= $row->cheque_date;

				$val['cheque_bank_from']= $row->cheque_bank_from;

				$val['cheque_bank_ac_from']= $row->cheque_bank_ac_from;

				$val['cheque_bank_ifsc_from']= $row->cheque_bank_ifsc_from;

				$val['cheque_bank_branch_from']= $row->cheque_bank_branch_from;

				$val['cheque_bank_from_id']= $row->cheque_bank_from_id;

				$val['cheque_bank_ac_from_id']= $row->cheque_bank_ac_from_id;

				$val['cheque_bank_to']= $row->cheque_bank_to;

				$val['cheque_bank_ac_to']= $row->cheque_bank_ac_to;

				$val['cheque_bank_to_name']= $row->cheque_bank_to_name;

				$val['cheque_bank_to_branch']= $row->cheque_bank_to_branch;

				$val['cheque_bank_to_ac_no']= $row->cheque_bank_to_ac_no;

				$val['cheque_bank_to_ifsc']= $row->cheque_bank_to_ifsc;

				$val['transction_no']= $row->transction_no;

				$val['transction_bank_from']= $row->transction_bank_from;

				$val['transction_bank_ac_from']= $row->transction_bank_ac_from;

				$val['transction_bank_ifsc_from']= $row->transction_bank_ifsc_from;

				$val['transction_bank_branch_from']= $row->transction_bank_branch_from;

				$val['transction_bank_from_id']= $row->transction_bank_from_id;

				$val['transction_bank_from_ac_id']= $row->transction_bank_from_ac_id;

				$val['transction_bank_to']= $row->transction_bank_to;

				$val['transction_bank_ac_to']= $row->transction_bank_ac_to;

				$val['transction_bank_to_name']= $row->transction_bank_to_name;

				$val['transction_bank_to_ac_no']= $row->transction_bank_to_ac_no;

				$val['transction_bank_to_branch']= $row->transction_bank_to_branch;

				$val['transction_bank_to_ifsc']= $row->transction_bank_to_ifsc;

				$val['transction_date']= $row->transction_date;

				$val['entry_date']= $row->entry_date;

				$val['entry_time']= $row->entry_time;

				$val['created_by']= $row->created_by;

				$val['created_by_id']= $row->created_by_id;

				$val['created_at']= $row->created_at;

				$val['updated_at']= $row->updated_at;

			

				if($row->Branch)

				{

                    $val['branch_code']= $row->Branch->branch_code ;

				}

				 else{

					$val['branch_code']= "N/A" ;

				 } 

			

				if($row->Branch)

				{

					$val['branch_name']= $row->Branch->name ;

				 }

				 else{

					$val['branch_name']= "N/A";

				 } 

			

				

				

				

				$transaction_date = date("d/m/Y", strtotime(convertDate($row->entry_date)));

	            $val['date'] = $transaction_date;

				

				

				

				if($row->amount_from_id ||$row->amount_to_id && $bank_id !='')

				{

				  if($bank_id !=  $row->amount_from_id)

				{

					$bank_name = getSamraddhBank($row->amount_to_id) ;

					if($bank_name)

					{

						$account = SamraddhBankAccount::where('id',$bank_name->id)->first();

						$val['account_number'] = $account->account_no;

					}

				

				}

					elseif($bank_id== $row->amount_from_id)

				{

					  $bank_name = getSamraddhBank($row->amount_from_id) ;

					  if($bank_name)

					{

					$account = SamraddhBankAccount::where('id',$bank_name->id)->first();

						$val['account_number'] = $account->account_no;

					}

				}   

				}

				else{

					$bank_name = getSamraddhBank($row->bank_id) ;

					if($bank_name)

					{

						$account = SamraddhBankAccount::where('id',$bank_name->id)->first();

						$val['account_number'] = $account->account_no;

					}

				}

				

				

				

				$bank_name = getSamraddhBank($row->bank_id) ;

					if($bank_name)

					{

						$account = SamraddhBankAccount::where('id',$bank_name->id)->first();

						$ac_no =  $account->account_no;

					}

				if($row->type == 7 || $row->type ==8 && $bank_id != '')

				{

					if($bank_id == $row->amount_from_id)

					{

					  
					$type='';
					$description = $row->description_dr;

					$type = $description. ' '.($ac_no) ;

			  

					}

					else if($bank_id != $row->amount_from_id)

					{

						 $description = $row->description_cr;

						$type = $description. ' '.($ac_no) ;

					}

					 

				}

				
                if($row->type == 1)
                {
                    if($row->sub_type==11)
                    {
                        $type ="Member - MI";
                    }
                    elseif($row->sub_type==12)
                    {
                        $type = "Member - STN ";
                    }
                }
                if($row->type == 2)
                {
                    if($row->sub_type == 21)
                    {
                        $type = 'Associate - Associate Commission';
                    }
                }

                if($row->type == 3)
                {
                    if($row->sub_type == 30)
                    {
                        $type = 'Investment - ELI';
                    }
                    elseif ($row->sub_type == 31) {
                        $type = 'Investment - Register';
                    }
                    elseif ($row->sub_type == 32) {
                          $type = 'Investment - Renew';
                    }
                    elseif ($row->sub_type == 33) {
                          $type = 'Investment - Passbook Print';
                    }
                }

                if($row->type == 4)
                {
                    if($row->sub_type == 41)
                    {
                        $type = "SSB - Register";
                    }
                    elseif ($row->sub_type == 42) {
                        $type = 'SSB - Renew(Deposit)';
                    }
                    elseif ($row->sub_type == 43) {
                          $type = 'SSB - Withdraw';
                    }
                    elseif ($row->sub_type == 44) {
                          $type = 'SSB - Passbook Print';
                    }
                    elseif ($row->sub_type == 45) {
                        $type = 'SSB - Commission';
                    }
                    elseif ($row->sub_type == 46) {
                          $type = 'SSB - Fule';
                    }
                    elseif ($row->sub_type == 47) {
                          $type = 'SSB - Transfer To Investment';
                    }
                    elseif ($row->sub_type == 48) {
                        $type = 'SSB - Transfer To loan';
                    }
                    elseif ($row->sub_type == 49) {
                          $type = 'SSB - Rent Transfer';
                    }
                    elseif ($row->sub_type == 410) {
                          $type = 'SSB - Salary Transfer';
                    }
                }

                if($row->type == 5)
                {
                     if($row->sub_type == 51)
                    {
                        $type = "Loan ";
                    }
                    elseif ($row->sub_type == 52) {
                        $type = 'Loan - Emi';
                    }
                    elseif ($row->sub_type == 53) {
                          $type = 'Loan - Panelty';
                    }
                    elseif ($row->sub_type == 54) {
                          $type = 'Loan - Group Loan';
                    }
                    elseif ($row->sub_type == 55) {
                        $type = 'Loan - Group Loan Emi';
                    }
                    elseif ($row->sub_type == 56) {
                          $type = 'Loan - Group Loan Panelty';
                    }
                    elseif ($row->sub_type == 57) {
                          $type = 'Loan - File Charge';
                    }
                    elseif ($row->sub_type == 58) {
                        $type = 'Loan - Group Loan File Charge';
                    }
                }

                if($row->type == 6)
                {
                    if($row->sub_type == 61)
                    {
                        $type = "Employee - Salary";
                    }
                }

                if($row->type == 7)
                {
                    if($row->sub_type == 70)
                    {
                        $type = "Branch To Bank - Branch Cash";
                    }
                    elseif ($row->sub_type == 71) {
                        $type = 'Branch To Bank - Branch Cheque';
                    }
                    elseif ($row->sub_type == 72) {
                          $type = 'Branch To Bank - Branch Online';
                    }
                    elseif ($row->sub_type == 73) {
                          $type = 'Branch To Bank - Branch SSB';
                    }
                }

                if($row->type == 8)
                {
                    if($row->sub_type == 80)
                    {
                        $type = "Bank To Bank - Bank Cash";
                    }
                    elseif ($row->sub_type == 81) {
                        $type = 'Bank To Bank - Bank Cheque';
                    }
                    elseif ($row->sub_type == 80) {
                          $type = 'Bank To Bank - Bank Online';
                    }
                    elseif ($row->sub_type == 83) {
                          $type = 'Bank To Bank - Bank SSB';
                    }
                }

                if($row->type == 9)
                {
                    if($row->sub_type == 90)
                    {
                        $type = "Tds - Commission";
                    }
                }

                if($row->type == 10)
                {
                    if($row->sub_type == 101)
                    {
                        $type = "Rent - Ledger";
                    }
                    elseif ($row->sub_type == 102) {
                        $type = 'Rent - Payment';
                    }
                    elseif ($row->sub_type == 103) {
                          $type = 'Rent - Security';
                    }
                    elseif ($row->sub_type == 104) {
                          $type = 'Rent - Advance';
                    }
                }

                if($row->type == 11)
                {
                    $type ="Demand";
                }

                 if($row->type ==12)
                {
                    if($row->sub_type == 121)
                    {
                        $type = "Salary - Ledger";
                    }
                    elseif ($row->sub_type == 122) {
                        $type = 'Salary - Transfer';
                    }
                    elseif ($row->sub_type == 123) {
                          $type = 'Salary - Advance';
                    }
                }

                if($row->type ==13)
                {
                    if($row->sub_type == 131)
                    {
                        $type = "Demand Advice - Fresh Expense";
                    }
                    elseif ($row->sub_type == 132) {
                        $type = 'Demand Advice - Ta Advance';
                    }
                    elseif ($row->sub_type == 133) {
                          $type = 'Demand Advice - Maturity';
                    }
                    elseif ($row->sub_type == 134) {
                          $type = 'Demand Advice - Prematurity';
                    }
                    elseif ($row->sub_type == 135) {
                        $type = 'Demand Advice - Death Help';
                    }
                    elseif ($row->sub_type == 136) {
                          $type = 'Demand Advice - Death Claim';
                    }
                    elseif ($row->sub_type == 137) {
                          $type = 'Demand Advice - EM';
                    }
                }

                if($row->type == 14)
                {
                    if($row->sub_type == 141)
                    {
                        $type = "Voucher - Director ";
                    }
                    elseif ($row->sub_type == 142) {
                        $type = 'Voucher  - ShareHolder';
                    }
                    elseif ($row->sub_type == 143) {
                          $type = 'Voucher  - Penal Interest';
                    }
                    elseif ($row->sub_type == 144) {
                          $type = 'Voucher  - Bank';
                    }
                    elseif ($row->sub_type == 145) {
                        $type = 'Voucher  - Eli Loan';
                    }
                }

                if($row->type == 15)
                {
                    if($row->sub_type == 151)
                    {
                        $type = 'Director - Deposit';
                    }

                    elseif($row->sub_type == 152)
                    {
                        $type = 'Director - Withdraw';
                    }
                }

                if($row->type == 16)
                {
                     if($row->sub_type == 161)
                    {
                        $type = 'ShareHolder - Deposit';
                    }
                     elseif($row->sub_type == 162)
                    {
                        $type = 'ShareHolder - Transfer';
                    }
                }
                
                if($row->type == 17)
                {
                     if($row->sub_type == 171)
                    {
                        $type = 'Loan From Bank  - Create Loan';
                    }
                     elseif($row->sub_type == 171)
                    {
                        $type = 'Loan From Bank  - Emi Payment';
                    }
                }

				

				$val['particular'] =$type;

				// account_head_noo
				

	            if($row->amount_from_id && $row->amount_to_id && $bank_id  )

	            {

					if($bank_id == $row->amount_from_id)

                    {

	            		 $account_head_id = SamraddhBank::where('id',$row->amount_from_id)->first('account_head_id');

	            		if($account_head_id)

	            		{

	            		    $account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['id']);

                  		    $val['account_head_no'] = $account_head_no->id;

	            		}

	            		else{

	            		    $val['account_head_no'] = "N/A";

	            		}

	               		

	            	}

	            	elseif($bank_id != $row->amount_from_id)

	            	{

	            		 $account_head_id = SamraddhBank::where('id',$row->amount_to_id)->first('account_head_id');

	            		 if( $account_head_id)

	            		 {

	            		    $account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['id']);

                  		    $val['account_head_no'] = $account_head_no->id;

	            		 }

	            		 else{

	            		    $val['account_head_no'] = "N/A";

	            		}

	               	

	            	}

	            

	            }

	            else if($row->amount_from_id =='' && $row->amount_to_id=='' && $row->bank_id == $bank_id)

				{

					 $account_head_id = SamraddhBank::where('id',$row->bank_id)->first('account_head_id');

					if($account_head_id)

					{

						$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['id']);

						$val['account_head_no'] = $account_head_no->id;

					}

				}

				else if($row->amount_from_id =='' && $row->amount_to_id=='' && $row->bank_id !=$bank_id)

				{

				    $account_head_id = SamraddhBank::where('id',$row->bank_id)->first('account_head_id');

				    if($account_head_id)

					{

						$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['id']);

						$val['account_head_no'] = $account_head_no->id;

					}

				}
				 elseif($row->type ==13 && $row->bank_id == $bank_id)

				{

					 $account_head_id = SamraddhBank::where('id',$row->amount_from_id)->first('account_head_id');

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['id']);

							$val['account_head_no'] = $account_head_no->id;

						}

				}
				elseif($row->type ==15 && $row->bank_id == $bank_id)

				{
					if($row->sub_type ==151)

					{
						$account_head_id = SamraddhBank::where('id',$row->amount_to_id)->first('account_head_id');

					}	
					elseif ($row->sub_type ==152) {
							 $account_head_id = SamraddhBank::where('id',$row->amount_from_id)->first('account_head_id');
						}	
					

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['id']);

							$val['account_head_no'] = $account_head_no->id;


						}

				}

				elseif($row->type ==16 && $row->bank_id == $bank_id)

				{
					if($row->sub_type ==151)

					{
						$account_head_id = SamraddhBank::where('id',$row->amount_to_id)->first('account_head_id');

					}	
					
						

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['id']);

							$val['account_head_no'] = $account_head_no->id;


						
				}
				elseif($row->type ==4 && $row->bank_id == $bank_id)

				{

					 $account_head_id = SamraddhBank::where('id',$row->amount_from_id)->first('account_head_id');

						if($account_head_id)

						{

							
							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['id']);

							$val['account_head_no'] = $account_head_no->id;

						}

				}



				else

				{

				    	$val['account_head_no'] ='';

				}

				

				

				//account_head

	                

				if($row->type == 8 && $row->amount_from_id == $bank_id)

				{

					 $account_head_id = SamraddhBank::where('id',$row->amount_from_id)->first('account_head_id');

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;

						}

				}

				 if($row->type == 8 && $row->amount_from_id != $bank_id)

				{

					 $account_head_id = SamraddhBank::where('id',$row->amount_to_id)->first('account_head_id');

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;

						}

				}

				 if($row->type ==13 && $row->bank_id == $bank_id)

				{

					 $account_head_id = SamraddhBank::where('id',$row->amount_from_id)->first('account_head_id');

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;

						}

				}


				 if($row->type ==4 && $row->bank_id == $bank_id)

				{

					 $account_head_id = SamraddhBank::where('id',$row->amount_from_id)->first('account_head_id');

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;

						}

				}


				 if($row->type ==18 && $row->bank_id == $bank_id)

				{

					 $account_head_id = SamraddhBank::where('id',$row->amount_from_id)->first('account_head_id');

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;

						}

				}



				 if($row->type ==17 && $row->bank_id == $bank_id)

				{
					if($row->sub_type ==171)

					{
						 $account_head_id = SamraddhBank::where('id',$row->amount_to_id)->first('account_head_id');
					}	
					elseif ($row->sub_type ==172) {
							 $account_head_id = SamraddhBank::where('id',$row->amount_from_id)->first('account_head_id');
						}	
					

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;

						}

				}


				elseif($row->type ==16 && $row->bank_id == $bank_id)

				{
					if($row->sub_type ==151)

					{
						$account_head_id = SamraddhBank::where('id',$row->amount_to_id)->first('account_head_id');

					}	
					
						

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;


						
				}

				 if($row->type ==15 && $row->bank_id == $bank_id)

				{
					if($row->sub_type ==151)

					{
						$account_head_id = SamraddhBank::where('id',$row->amount_to_id)->first('account_head_id');

					}	
					elseif ($row->sub_type ==152) {
							 $account_head_id = SamraddhBank::where('id',$row->amount_from_id)->first('account_head_id');
						}	
					

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;

						}

				}


				 if($row->type ==14 && $row->bank_id == $bank_id)

				{
					
					if ($row->sub_type ==142 || $row->sub_type ==143 ||$row->sub_type ==141 || $row->sub_type ==144 || $row->sub_type == 145) {
							 $account_head_id = SamraddhBank::where('id',$row->amount_to_id)->first('account_head_id');


						}	

						

						
							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;

						
				}





				if($row->type == 7 || $row->type == 5)

				{

					 $account_head_id = SamraddhBank::where('id',$row->amount_to_id)->first('account_head_id');

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;

						}

				}

				if( $row->amount_from_id == '' &&  $row->amount_to_id == '' && $row->bank_id ==$bank_id )

				{

					 $account_head_id = SamraddhBank::where('id',$row->bank_id)->first('account_head_id');

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;

						}

				}

					if( $row->amount_from_id == '' &&  $row->amount_to_id == '' && $row->bank_id !=$bank_id )

				{

					 $account_head_id = SamraddhBank::where('id',$row->bank_id)->first('account_head_id');

						if($account_head_id)

						{

							$account_head_no = AccountHeads::where('head_id',$account_head_id->account_head_id)->first(['sub_head']);

							$val['account_head_name'] = $account_head_no->sub_head;

						}

				}

			

				

				//Cheque Number

				

				

			   if($row->cheque_no){

					$val['cheque_no'] = $row->cheque_no;

				}

				else{

					$val['cheque_no'] = 'N/A';

				}

					

					

					

				$amount='';

			 //   if($row->amount_to_id && $row->amount_from_id)

			 //   {

				//  if($bank_id != $row->amount_from_id)

				// {

				//  $amount =  number_format((float)$row->amount, 2, '.', '');

				//  $val['credit'] = "&#x20B9;".$amount;

				// }

				// else{

				//    $val['credit'] = "N/A";

			 //   }

			 //   }

			 //   else if($row->type == 10 && $row->bank_id ==$bank_id)

			 //   {

			 //       $val['credit'] = "N/A";

			 //   }

			 //   else{

				//    $val['credit'] = "&#x20B9;".number_format((float)$row->amount, 2, '.', '');

			 //   }

			   

			   if($row->payment_type =="CR")
			   {
			   	  $val['credit'] = "&#x20B9;".number_format((float)$row->amount, 2, '.', '');
			   }
			   else{
			   		$val['credit'] = "N/A";
			   }
			   if($row->payment_type =="DR")
			   {
			   	 $val['debit'] =  "&#x20B9;".number_format((float)$row->amount, 2, '.', '');
			   }
			    else{
			   		$val['debit'] = "N/A";
			   }
	               

			       //  if($row->amount_to_id && $row->amount_from_id)

	         //       {

	         //         if($bank_id == $row->amount_from_id)

	         //       			{

	         //        $val['debit'] =  number_format((float)$row->amount, 2, '.', '');

	         //        }

	         //        else{

	         //            $val['debit'] = "N/A";

	         //       }

	         //       }

	         //        else if($row->type == 10 && $row->bank_id ==$bank_id)

    			   // {

    			   //     $val['debit'] =  $val['debit'] =  number_format((float)$row->amount, 2, '.', '');

    			   // }

    			   // else if($row->type == 4 && $row->bank_id ==$bank_id &&  $row->amount_to_id ==$bank_id)

    			   // {

    			   //     $val['debit'] =  $val['debit'] =  number_format((float)$row->amount, 2, '.', '');

    			   // }

	         //       else{

	         //            $val['debit'] = "N/A";

	         //       }

			   

			   

				

					// $totalBalance='';

					// $sessionValue = Session::get('totalBalance');

					// if($row->amount_from_id && $row->amount_to_id)

					// {

					// 	if($row->amount_from_id != $bank_id)

					// 	{

					// 		if($row)

					// 		{

					// 			$totalbalance = $row->amount + $totalbalance;

					// 		}

							

					// 		else{

					// 			if($row )

					// 			{

					// 			$totalbalance = $row->amount +  $totalbalance;

					// 			}

					// 			else{

					// 				$totalbalance = $row->amount ;

					// 			}

					// 	}

						

					// 	}

					// 	 elseif(($row->type == 4) && $row->bank_id == $bank_id && $row->amount_from_id == $bank_id)

				 // {

				 //     if($row->amount && $row->payment_mode =="CR")

					// 		{

					// 		$totalbalance = $totalbalance + $row->amount;

					// 		}

						

					// 	else{

					// 		if($row)

					// 		{

					// 		$totalbalance = $totalbalance+$row->amount ;

					// 		}

					// 		else{

					// 			$totalbalance = $row->amount ;

					// 		}

					// 	}

				 // }

					// 		elseif($row->amount_from_id == $bank_id && $row->payment_mode == "DR")

					// {

						

					// 		if($row->amount)

					// 		{

					// 		$totalbalance = $totalbalance - $row->amount;

					// 		}

						

					// 	else{

					// 		if($row)

					// 		{

					// 		$totalbalance = $totalbalance-$row->amount ;

					// 		}

					// 		else{

					// 			$totalbalance = $row->amount ;

					// 		}

					// 	}

				   

				 // }

					// }

				
				 // elseif(($row->type == 4) && $row->bank_id == $bank_id && $row->amount_from_id == $bank_id)

				 // {

				 //     if($row->amount && $row->payment_mode =="DR")

					// 		{

					// 		$totalbalance = $totalbalance - $row->amount;

					// 		}

						

					// 	else{

					// 		if($row)

					// 		{

					// 		$totalbalance = $totalbalance-$row->amount ;

					// 		}

					// 		else{

					// 			$totalbalance = $row->amount ;

					// 		}

					// 	}

				 // }

				 // elseif(($row->type == 10) && $row->bank_id == $bank_id )

				 // {

				 //     if($row->amount)

					// 		{

					// 		$totalbalance = $totalbalance - $row->amount;

					// 		}

						

					// 	else{

					// 		if($row)

					// 		{

					// 		$totalbalance = $totalbalance-$row->amount ;

					// 		}

					// 		else{

					// 			$totalbalance = $row->amount ;

					// 		}

					// 	}

				 // }

				 // else{

					// 	$totalbalance = $totalbalance+$row->amount ;

				 // }

				 if($row->payment_type=="CR")

				 {
				 	$totalbalance =$totalbalance + $row->amount;
				 }
				 elseif ($row->payment_type=="DR") {
				 $totalbalance =$totalbalance - $row->amount;
				 }

				 // Session::put('totalBalance', $totalBalance);

				$val['balance'] = "&#x20B9;".number_format((float)$totalbalance, 2, '.', '');		



				$rowReturn[] = $val; 

				

			}

            

			// closing balance start ............................/

				//if( $endNumber >= $totalCount ){

				

				if($bank_id!= "" && $bankAccount_id!= ""&& count($data) > 0){

					

					$start++ ;

					

					$val = array();

				

					if($endDate!= ""){

						$SamraddhBankClosingData = DB::table('samraddh_bank_closing')->where('entry_date',$endDate)->where('bank_id',$bank_id)->orderBy('id','DESC')

						->select('*')->get();

					} else {

						$SamraddhBankClosingData = DB::table('samraddh_bank_closing')->where('bank_id',$bank_id)->orderBy('entry_date','DESC')

						->select('*')->get();

					}

					

					$val["Sr_no"] = '';

					$val['DT_RowIndex']=" ";

					$val['id']= "";

					$val['daybook_ref_id']= "";

					$val['bank_id']= "";

					$val['account_id']= "";

					$val['type']= "";

					$val['sub_type']= "";

					$val['type_id']= "";

					$val['type_transaction_id']= "";

					$val['associate_id']= "";

					$val['member_id']= "";

					$val['branch_id']= "";

					$val['opening_balance']= "";

					$val['closing_balance']= "";

					$val['description']= "";

					$val['description_dr']= "";

					$val['description_cr']= "";

					$val['payment_type']= "";

					$val['payment_mode']= "";

					$val['currency_code']= "";

					$val['amount_to_id']= "";

					$val['amount_to_name']= "";

					$val['amount_from_id']= "";

					$val['amount_from_name']= "";

					$val['v_no']= "";

					$val['v_date']= "";

					$val['ssb_account_id_from']= "";

					$val['ssb_account_id_to']= "";

					$val['cheque_no']= "";

					$val['cheque_date']= "";

					$val['cheque_bank_from']= "";

					$val['cheque_bank_ac_from']= "";

					$val['cheque_bank_ifsc_from']= "";

					$val['cheque_bank_branch_from']= "";

					$val['cheque_bank_from_id']= "";

					$val['cheque_bank_ac_from_id']= "";

					$val['cheque_bank_to']= "";

					$val['cheque_bank_ac_to']= "";

					$val['cheque_bank_to_name']= "";

					$val['cheque_bank_to_branch']= "";

					$val['cheque_bank_to_ac_no']= "";

					$val['cheque_bank_to_ifsc']= "";

					$val['transction_no']= "";

					$val['transction_bank_from']= "";

					$val['transction_bank_ac_from']= "";

					$val['transction_bank_ifsc_from']= "";

					$val['transction_bank_branch_from']= "";

					$val['transction_bank_from_id']= "";

					$val['transction_bank_from_ac_id']= "";

					$val['transction_bank_to']= "";

					$val['transction_bank_ac_to']= "";

					$val['transction_bank_to_name']= "";

					$val['transction_bank_to_ac_no']= "";

					$val['transction_bank_to_branch']= "";

					$val['transction_bank_to_ifsc']= "";

					$val['transction_date']= "";

					$val['entry_date']= "";

					$val['entry_time']= "";

					$val['created_by']= "";

					$val['created_by_id']= "";

					$val['created_at']= "";

					$val['updated_at']= "";

					$val['branch_code']= "";

					$val['branch_name']= "";

					$val['date']= "";

					$val['account_number']= "";

					$val['particular']= "Closing Balance";

					$val['account_head_no']= "";

					$val['account_head_name']= "";

					$val['cheque_no']= "";

					$val['credit']= "";

					$val['debit']= "";

						

					

					if(count($SamraddhBankClosingData)> 0){

						$val['balance']= $SamraddhBankClosingData[0]->closing_balance;

						

					} else {

						$val['balance']= 0;

					}

					

					$rowReturn[] = $val; 

					

				}

				

				//}

			

			// closing balance end ................................./

		    

			$output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount,'balance'=>$totalbalance, "recordsFiltered" => $totalCount, "data" => $rowReturn,"page"=>$pageStart,'start' =>$start);

			return json_encode($output);

			  



				

		}  

		 





	}

	



}