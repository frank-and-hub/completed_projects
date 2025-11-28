<?php

namespace App\Http\Controllers\Admin\PaymentHistory;

use Illuminate\Http\Request;
use Auth;
use App\Models\Settings;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\AccountHeads;
use App\Models\VendorBillPayment;
use App\Models\VendorBill;
use Validator;  
use Carbon\Carbon;
use DB;
use URL;
use Session;
use Image;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Admin\CommanController;



class PaymentHistoryController extends Controller

{

    public function index()

    {
		if(check_my_permission(Auth::user()->id,"204") != "1")
        {
            return redirect()->route('admin.dashboard');
         }  
        
        $data['title'] = "Payment History Management  | payment List";
        $data['branches'] = Branch::where('status',1)->get();

        return view('templates.admin.payment_history.index',$data);

    }

    // Listing Of Payment

    public function payment_list(Request $request)
    {
       if($request->ajax())
       {
            $arrFormData = array();
            if(!empty($_POST['searchform']))
            {
               foreach($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }

            }
            $data = VendorBillPayment::with('branch_detail','bill_detail','vendor_detail');
            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            {
                if($arrFormData['branch_id'] != '')
                {
                    $data = $data->where('branch_id',$arrFormData['branch_id']);
                }

                // if($arrFormData['type'] != '')
                // {
                //     $data = $data->where("bill_type",$arrFormData['type']);
                // }

                if($arrFormData['start_date'] != '')
                {
                    $startDate = date('Y-m-d',strtotime(convertDate($arrFormData['start_date'])));
                    if($arrFormData['end_date'] != '')
                    {
                        $endDate  = date('Y-m-d',strtotime(convertDate($arrFormData['end_date'])));
                    }
                    else{
                        $endDate = '';
                    }

                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'),[$startDate,$endDate]);
                }
            }
                    $data = $data->orderBy('created_at','desc')->get();

                    return Datatables::of($data)
                    ->addIndexColumn() 
                    ->addColumn('date', function($row){
                        $date =  date("d/m/Y", strtotime($row->created_at));;
                        return $date;
                    })
                    ->rawColumns(['date'])  
                    ->addColumn('payment', function($row){
                        return $row->id;
                        
                    })
                    ->rawColumns(['payment'])  
                    ->addColumn('reference', function($row){
                        if(isset($row['bill_detail']->bill_number))
                        {
                            return $row['bill_detail']->bill_number;
                        }
                        else{
                            return 'N/A';
                        }
                        
                    })
                    ->rawColumns(['reference']) 
                    ->addColumn('v_name', function($row){
                        $name  = '';
                        if($row->bill_type == 0)
                        {
                            if(isset($row['vendor_detail']->name))
                            {
                            $name =  $row['vendor_detail']->name;
                            }
                            else{
                                $name =  'N/A';
                            } 
                        }
                        elseif($row->bill_type == 2)
                        {
                            if(isset($row->employee_id))
                            {
                            $Employee =  \App\Models\Employee::where('id',$row->employee_id)->first();
                            $name = $Employee->employee_name;
                            }
                            else{
                                $name =  'N/A';
                            } 
                        }
                        elseif($row->bill_type == 2)
                        {
                            if(isset($row->rent_owner_id))
                            {
                            $rent =  \App\Models\RentLiability::where('id',$row->rent_owner_id)->first();
                            $name = $rent->owner_name;
                            }
                            else{
                                $name =  'N/A';
                            } 
                        }
                        elseif($row->bill_type == 3)
                        {
                            if(isset($row->associate_id))
                            {
                            $member =  \App\Models\Member::where('id',$row->associate_id)->first();
                            $name = $member->first_name.' '.$member->last_name;
                            }
                            else{
                                $name =  'N/A';
                            } 
                        }
                        return $name;
                        
                    })
                    ->rawColumns(['v_name']) 
                    ->addColumn('bill', function($row){
                        if(isset($row['bill_detail']->bill_number))
                        {
                            $billData = \App\Models\VendorBill::where('id',$row->vendor_bill_id)->first();
                            $bill = $billData->bill_number;
                        }
                        else{
                           $bill = '';
                        }
                        
                    })
                    ->rawColumns(['bill'])  
                    ->addColumn('mode', function($row){
                        if($row->payment_mode == 0){
                            $payment_mode='Cash';
                        }elseif($row->payment_mode == 1){
                            $payment_mode='Cheque';
                        }elseif($row->payment_mode == 2){
                            $payment_mode='Online Transfer'; 
                        }elseif($row->payment_mode == 3){
                            $payment_mode='SSB/GV Transfer';
                        }elseif($row->payment_mode == 4){
                            $payment_mode='Auto Transfer(ECS)';
                        }elseif($row->payment_mode == 5){
                            $payment_mode='By loan amount';
                        }elseif($row->payment_mode == 6){
                            $payment_mode='JV Module';
                        }elseif($row->payment_mode == 7){
                            $payment_mode='Credit Card';
                        }
                        return $payment_mode;
                        
                    })
                    ->rawColumns(['mode'])           
                    ->addColumn('amount', function($row){
                        return $row->deposit;
                        
                    })
                    ->rawColumns(['amount']) 
                    ->addColumn('unused_amount', function($row){
                        if(isset($row['bill_detail']->bill_number))
                        {
                            return $row['bill_detail']->bill_number;
                        }
                        else{
                            return 'N/A';
                        }
                        
                    })
                    ->rawColumns(['unused_amount']) 
                   
                    ->addColumn('action', function($row){ 



                         $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">'; 


                        $url2 = URL::to("admin/associate-edit/".$row->id); 
                        $url4 = URL::to("admin/hr/associate/detail/".$row->id); 
                         
                       
                        $btn .= '<a class="dropdown-item" href="'.$url2.'" title="Payment Edit"><i class="icon-pencil7  mr-2"></i> Edit</a>';  

						if(check_my_permission( Auth::user()->id,"206") == "1"){
							$btn .= '<a class="dropdown-item" href="'.$url4.'" title="Payment Delete"><i class="icon-eye8  mr-2"></i> Delete</a>';     
						}
						
						if(check_my_permission( Auth::user()->id,"207") == "1"){

							$btn .= '<button class="dropdown-item printBillPayement" data-row-id="'.$row->id.'"  data-toggle="modal" data-target="#exampleModal"  ><i class="icon-list mr-2"></i>Detail</button>';   
						}							

                        $btn .= '</div></div></div>';  
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

    }


    public function edit_payment()
    {
		 
        $data['title'] = 'Payment History Management | Edit Payment';
        return view('templates.admin.payment_history.edit_payment',$data);

    }
	
	
	
	public function getPaymentBillDetails(Request $request)
    {
		if( isset($request->bill_payment_id) ){
			$bill_payment_id = trim($request->bill_payment_id);
			
			// Now Get bill records
			$bill_payment_records = VendorBillPayment::with('branch_detail','bill_detail','vendor_detail')->where("id",$bill_payment_id)->first()->toArray();
			$date = date("d/m/Y", strtotime($bill_payment_records["payment_date"]));
			$bill_payment_records["payment_date"] = $date;
			
			$array = array("bill_records" => $bill_payment_records);
			
			echo json_encode($array);
			
		}
    }


}