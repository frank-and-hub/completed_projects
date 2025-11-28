<?php

namespace App\Http\Controllers\Admin\vendorManagement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\VendorCategory; 
use App\Models\Vendor;  
use App\Models\VendorBill; 
use App\Models\VendorBillPayment;
use App\Models\VendorTransaction; 
use App\Models\VendorBillItem;
use App\Models\VendorLog;
use App\Models\AccountHeads;
use App\Models\VendorCreditNode;
 
use Carbon\Carbon;
use App\Models\EmployeeSalary;
use App\Models\CommissionLeaserDetail;
use DB;
use URL;
use Image;
use Yajra\DataTables\DataTables;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Vendor Management VendorController
    |--------------------------------------------------------------------------
    |
    | This controller handles Vendor   all functionlity.
*/

class VendorCredit extends Controller
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
     * Show Vendor  .
     * Route: admin/vendor
     * Method: get 
     * @return  array()  Response 
     */
    public function index($bill_id)
    {         
        
          
		$data['title']='Vendor Management | Create Credit Note'; 
        $data['bill'] =VendorBill::where('id',$bill_id)->first(); 
		$data['billItem'] =VendorBillItem::where('vendor_bill_id',$bill_id)->get(); 
        $data['vendor'] =Vendor::where('id',$data['bill']->vendor_id)->first();

        return view('templates.admin.vendor_management.vendorCredit.index', $data);
    }
   
    /**
     * Vendor   delete.
     * Route: admin/vendor/delete
     * Method: get 
     * @return  array()  Response
     */
    public function vendorDelete($id)
    {
        DB::beginTransaction();
        try {
            $chk=0;
            if($chk==0)
            {
                $deleteupdate = Vendor::whereId($id)->delete();
            }           
                 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
         return redirect()->route('admin.vendor')->with('success', 'Vendor Deleted Successfully');
    }
   
     /**
     * Add  Vendor  .
     * Route: admin/vendor/add
     * Method: get 
     * @return  array()  Response
     */
    public function add()
    { 
        if(check_my_permission(Auth::user()->id,"195") != "1" && check_my_permission(Auth::user()->id,"196") != "1")
        {
            return redirect()->route('admin.dashboard');
         }
		$data['title']='Vendor Management | Vendor Registration'; 
        $data['category']=VendorCategory::where('status',1)->orderby('id','DESC')->get();
        $data['state']=stateList();  
        return view('templates.admin.vendor_management.vendor.add', $data);
    }
    /**
     * save  Vendor  .
     * Route: admin/vendor/add
     * Method: get 
     * @return  array()  Response
     */
    public function save(Request $request)
    {

        DB::beginTransaction();
        try {

           

            $globaldate = $request->created_at;
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));


            $data['name'] = $request->name;             
            $data['company_name'] = $request->company_name; 
            $data['email_id'] = $request->email;
            $data['mobile_no'] = $request->mobile;             
            $data['gst_type'] = $request->gst_treatment; 
            $data['gst_no'] = $request->gst_no;
            $data['pan_number'] = $request->pan_card;             
            $data['vendor_category'] = $category; 
            $data['address'] = $request->address;
            $data['city'] = $request->city;             
            $data['state'] = $request->state; 
            $data['zip_code'] = $request->zip_code;
            $data['bank_name'] = $request->bank_name;             
            $data['bank_ac_no'] = $request->account_no; 
            $data['ifsc'] = $request->ifsc_code;
            $data['ssb_account_id'] = $request->ssb_account_id;             
            $data['ssb_account'] = $request->ssb_account; 
            $data['status'] = 1;
            $data['created_at'] = $created_at;             
            $data['updated_at'] = $created_at; 
            $data['type'] = $request->type;

            $create = Vendor::create($data); 
            
            $created_by=1;
            $created_by_id=\Auth::user()->id;
            $created_by_name=\Auth::user()->username; 

            $data1['vendor_id'] = $create->id; 
            $data1['title'] = 'Vendor Credit Note Added';
            $data1['description'] =NULL;             
            $data1['created_by'] = $created_by; 
            $data1['created_by_id'] = $created_by_id;
            $data1['created_by_name'] = $created_by_name;             
            $data1['created_at'] = $created_at; 
            $data1['updated_at'] = $created_at; 

            $create1 = VendorLog::create($data1); 

                 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
         return redirect('admin/vendor')->with('success', 'Vendor Created Successfully');
    }

    /**
     * Edit  Vendor  .
     * Route: admin/vendor/edit
     * Method: get 
     * @return  array()  Response
     */
    public function edit($id)
    {
		if(check_my_permission(Auth::user()->id,"197") != "1" && check_my_permission(Auth::user()->id,"199") != "1")
        {
            return redirect()->route('admin.dashboard');
        }
		 
        $data['vendor']=Vendor::where('id',$id)->first();
        if($data['vendor']->type == 0)
        {

         $data['title']='Vendor Management | Vendor Edit'; 
        }
        else{
             $data['title']='Vendor Management | Customer Edit'; 
        }
        $data['get_cat']='';
        if($data['vendor']->vendor_category!='')
        {
            $data['get_cat']= $data['vendor']->vendor_category;
        }
       // print_r($data['get_cat']);die;
        
        $data['category']=VendorCategory::where('status',1)->orderby('id','DESC')->get();
        $data['state']=stateList(); 
        return view('templates.admin.vendor_management.vendor.edit', $data);
    }
    /**
     * Update  Vendor  .
     * Route: admin/vendor/edit
     * Method: get 
     * @return  array()  Response
     */
    public function update(Request $request)
    {
        $created_by=1;
        $created_by_id=\Auth::user()->id;
        $created_by_name=\Auth::user()->username; 
        DB::beginTransaction();
        try {
            $category = '';
            if($request->type==0)
            {
                if(!isset($_POST['category']))
                {
                return back()->with('alert','Please Select Category');
                }
                if(count($request->category)>0)
                {
                    $category = implode(",", $request->category);
                }
                else
                {
                    $category = '';
                }
            }
            
            
            $data['name'] = $request->name;             
            $data['company_name'] = $request->company_name; 
            $data['email_id'] = $request->email;
            $data['mobile_no'] = $request->mobile;             
            $data['gst_type'] = $request->gst_treatment; 
            $data['gst_no'] = $request->gst_no;
            $data['pan_number'] = $request->pan_card;             
            $data['vendor_category'] = $category; 
            $data['address'] = $request->address;
            $data['city'] = $request->city;             
            $data['state'] = $request->state; 
            $data['zip_code'] = $request->zip_code;
            $data['bank_name'] = $request->bank_name;             
            $data['bank_ac_no'] = $request->account_no; 
            $data['ifsc'] = $request->ifsc_code;
            $data['ssb_account_id'] = $request->ssb_account_id;             
            $data['ssb_account'] = $request->ssb_account; 
            $data['type'] = $request->type;           
            $updatedata = Vendor::find($request->id);
            $updatedata->update($data);

            $data1['vendor_id'] = $request->id; 
            $data1['title'] = 'Vendor Contact Updeted';
            $data1['description'] =NULL;             
            $data1['created_by'] = $created_by; 
            $data1['created_by_id'] = $created_by_id;
            $data1['created_by_name'] = $created_by_name;             
            // $data1['created_at'] = $created_at; 
            // $data1['updated_at'] = $created_at; 

            $create1 = VendorLog::create($data1); 
            

                 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
         return redirect('admin/vendor')->with('success', 'Vendor Updated Successfully');
    }

    /**
     * Edit  Vendor  .
     * Route: admin/vendor/edit
     * Method: get 
     * @return  array()  Response
     */
    public function detail($id)
    {
		if(check_my_permission(Auth::user()->id,"198") != "1" && check_my_permission(Auth::user()->id,"200") != "1")
        {
            return redirect()->route('admin.dashboard');
         }
       
        $data['vendor']=Vendor::where('id',$id)->first();

        if($data['vendor']->type == 0)
        {
            $data['outstanding']=VendorBill::where('vendor_id',$id)->sum('balance');
            //$data['unused_credit']=AdvancedTransaction::where('vendor_id',$id)->sum('balance');   
            $data['credit_node']=VendorCreditNode::where('vendor_id',$id)->sum('total_amount');   
            $data['title']='Vendor Management | Vendor Detail'; 
            $advance_amt = \App\Models\BankingLedger::where('vendor_type',3)->where('vendor_type_id',$id)->sum('advanced_amount');
        $data['advance_amt'] = number_format((float)$advance_amt, 2, '.', '');
        }
        else{
            $data['outstanding']=CustomerTransaction::where('customer_id',$id)->sum('amount');
            //$data['unused_credit']=AdvancedTransaction::where('vendor_id',$id)->sum('balance');   
            $data['credit_node']=VendorCreditNode::where('vendor_id',$id)->sum('total_amount');
            $data['title']='Vendor Management | Customer Detail'; 
            $advance_amt = \App\Models\BankingLedger::whereIN('vendor_type',['4','5'])->where('vendor_type_id',$id)->sum('customer_advanced_payment');
        $data['advance_amt'] = number_format((float)$advance_amt, 2, '.', '');
        }
        return view('templates.admin.vendor_management.vendor.detail', $data);
    }


    /**
     * Edit  Vendor  .
     * Route: admin/vendor/edit
     * Method: get 
     * @return  array()  Response
     */
    public function print()
    {
        $data['title']='Vendor Management | Vendor Print'; 
        $data['vendor']=Vendor::where('id',$id)->first();
        return view('templates.admin.vendor_management.vendor.print', $data);
    }


     /**
     * save  Vendor Category .
     * Route: admin/vendor/category/add
     * Method: get 
     * @return  array()  Response
     */
    public function categorySave(Request $request)
    {
        $id=0;
        $msg_type='';
        $error='';
        DB::beginTransaction();
        try {

            $data['name'] = $request->name;             
            $data['status'] = 1; 
            $data['created_at'] = $request->created_at;            
            $create = VendorCategory::create($data);  
            $id=$create->id;

            $msg_type='success'; 
            $error=''; 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
           /// return back()->with('alert', $ex->getMessage());
            $msg_type='error';
            $error=$ex->getMessage();
        }
        $data_cat=VendorCategory::where('status',1)->orderby('id','DESC')->get();       

        $return_array = compact('data_cat','msg_type','error','id');

        return json_encode($return_array); 
    }


    /**
     * status change Customer &  Vendor  .
     * Route: admin/admin/vendor/status/
     * Method: get 
     * @return  array()  Response
     */
    public function changeStatus($id,$status)
    {
        
        $created_by=1;
        $created_by_id=\Auth::user()->id;
        $created_by_name=\Auth::user()->username; 
        DB::beginTransaction();
        try {
            
              if($status==1)
              {
                $data['status'] = 0; 
                $msg='Mark as inactive';
              }  
              else
              {
                $data['status'] = 1; 
                $msg='Mark as active'; 
              }    
            $updatedata = Vendor::find($id);
            $updatedata->update($data);

            $data1['vendor_id'] = $id; 
            $data1['title'] = 'Vendor Status Chenge ';
            $data1['description'] =$msg;             
            $data1['created_by'] = $created_by; 
            $data1['created_by_id'] = $created_by_id;
            $data1['created_by_name'] = $created_by_name;             
            // $data1['created_at'] = $created_at; 
            // $data1['updated_at'] = $created_at; 

            $create1 = VendorLog::create($data1); 

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
        if($updatedata->type==0)
        {
            return back()->with('success', 'Vendor Status Changed Successfully');
        }
        else
        {
            return back()->with('success', 'Customer Status Changed Successfully');
        }
        
    }

    /**
     * deleteCustomer &  Vendor  .
     * Route: admin/admin/vendor/status/
     * Method: get 
     * @return  array()  Response
     */
    public function delete($id)
    {

        DB::beginTransaction();
        try {
            $vendor=Vendor::where('id',$id)->first();
            $chk=VendorBill::where('vendor_id',$id)->count();
            if($chk==0)
            {
                $deleteupdate = Vendor::whereId($id)->delete();
            } 
            else
            {
                    if($vendor->type==0)
                    {
                        return back()->with('success', 'The vendor cannot be deleted  moved because the bill has already been created.');
                    }
                    else
                    {
                        return back()->with('success', 'The customer cannot be deleted because the bill has already been created.');
                    }
            }          
                 
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
        
        if($vendor->type==0)
        {
            return redirect('admin/vendor')->with('success', 'Vendor Deleted Successfully');
        }
        else
        {
            return redirect('admin/vendor')->with('success', 'Customer Deleted Successfully');
        }
    }


    public function detail_rent($id)
    {
        $data['title']='Vendor Management | Vendor Rent Detail'; 
        $data['vendor']=Vendor::where('id',$id)->first();
        $data['rentLiability'] = RentLiability::with('liabilityBranch','liabilityFile')->with(['employee_rent' => function($query){ $query->select('id', 'designation_id','employee_code','employee_name','mobile_no','employee_date');}])->where('id',$id)->first();

        $actual_transfer_amnt=RentPayment::where('rent_liability_id',$id)->sum('actual_transfer_amount');
        $transfer_amnt=RentPayment::where('rent_liability_id',$id)->sum('transferred_amount');
        $data['outstanding'] = number_format((float)$actual_transfer_amnt - $transfer_amnt, 2, '.', '');
        $advance_amt = \App\Models\BankingLedger::where('vendor_type',0)->where('vendor_type_id',$id)->sum('advanced_amount');
        $data['advance_amt'] = number_format((float)$advance_amt, 2, '.', '');
        return view('templates.admin.vendor_management.vendor.rent_detail', $data);
    }

    public function detail_salary($id)
    {   
         $data['title']='Vendor Management | Vendor Employee Detail'; 
        $empID=$id;

        $data['employee']=Employee::where('id',$empID)->first();

        if($data['employee']->is_resigned==1)

        {

            $data['app']=\App\Models\EmployeeApplication::where('employee_id',$empID)->where('application_type',2)->orderby('id','DESC')->get();

        }

        if($data['employee']->is_terminate==1)

        {

            $data['terminate']=\App\Models\EmployeeTerminate::where('employee_id',$empID)->get();

        }

        if($data['employee']->is_transfer==1)

        {

            $data['transfer']=\App\Models\EmployeeTransfer::with(['transferBranch' => function($query){ $query->select('id','name');}])->with(['transferBranchOld' => function($query){ $query->select('id','name');}])->with(['transferEmployee' => function($query){ $query->select('*');}])->where('employee_id',$empID)->orderby('id','DESC')->get();

        }
        $actual_transfer_amnt=EmployeeSalary::where('employee_id',$id)->sum('actual_transfer_amount');
        $transfer_amnt=EmployeeSalary::where('employee_id',$id)->sum('transferred_salary');
        $data['outstanding'] = number_format((float)$actual_transfer_amnt - $transfer_amnt, 2, '.', '');

        $advance_amt = \App\Models\BankingLedger::where('vendor_type',1)->where('vendor_type_id',$id)->sum('advanced_amount');
        $data['advance_amt'] = number_format((float)$advance_amt, 2, '.', '');

        return view('templates.admin.vendor_management.vendor.salary_detail', $data);

    }

    public function vendor_transaction(Request $request)
    {
        if($request->vendor_type == 0 )
        {
             $data = VendorTransaction::with('branch_detail','bill_detail')->where('vendor_id',$request->vendor_id);
        }
        elseif($request->vendor_type ==1)
        {
             $data=\App\Models\CustomerTransaction::with('branch_detail','bill_detail')->where('customer_id',$request->vendor_id); 
        }
        elseif($request->vendor_type ==3)
        {
             $data=\App\Models\EmployeeLedger::with('branch_detail')->where('employee_id',$request->vendor_id); 
        }
        elseif($request->vendor_type ==2)
        {
            $data = \App\Models\RentLiabilityLedger::where('rent_liability_id',$request->vendor_id);
        }
         elseif($request->vendor_type ==4)
        {
            $data = AssociateTransaction::where('associate_id',$request->vendor_id);
        }
        $data1=$data->orderby('created_at','ASC')->get();
        $count=count($data1);
        $data=$data->orderby('created_at','ASC')->offset($_POST['start'])->limit($_POST['length'])->get();
        $totalCount =$count;
        $sno=$_POST['start'];
        $rowReturn = array(); 
        if($_POST['pages'] == 1)
        {
            $totalAmount  = 0;
        }   
        else{
            $totalAmount  = $_POST['total'];
        }
            
              if($request->vendor_type == 0 )
            {
                 $dataCR = VendorTransaction::with('branch_detail','bill_detail')->where('vendor_id',$request->vendor_id);
                 $accountheadId = 140;
            }
            elseif($request->vendor_type ==1)
            {
                 $dataCR=\App\Models\CustomerTransaction::where('customer_id',$request->vendor_id); 
                  $accountheadId = 142;
            }
            elseif($request->vendor_type ==3)
            {
                 $dataCR=\App\Models\EmployeeLedger::where('type','!=',2)->where('employee_id',$request->vendor_id); 
                 $accountheadId = 61;
            }
            elseif($request->vendor_type ==2)
            {
                $dataCR = \App\Models\RentLiabilityLedger::where('rent_liability_id',$request->vendor_id);
                $accountheadId = 60;
            }
             elseif($request->vendor_type ==4)
            {
                $dataCR = AssociateTransaction::where('type_id',$request->vendor_id);
                $accountheadId = 141;
            }
            $accounthead = AccountHeads::where('head_id',$accountheadId)->first();
            if($_POST['pages'] == "1"){
                $length = ($_POST['pages']) * $_POST['length'];
            } else {
                $length = ($_POST['pages']-1) * $_POST['length'];
            }
            
            $dataCR = $dataCR->offset(0)->limit($length)->get();

               if($request->vendor_type ==2 || $request->vendor_type ==3)
                {
                    $totalCR = $dataCR->where('payment_type','CR')->sum('deposit');
                    $totalDR = $dataCR->where('payment_type','DR')->sum('withdrawal');
                    $totalAmountssssss = $totalCR - $totalDR;
                }
                else{
                    $totalDR = $dataCR->where('payment_type','DR')->sum('amount');
                    $totalCR = $dataCR->where('payment_type','CR')->sum('amount');
                    $totalAmountssssss = $totalCR - $totalDR;
                }
                
            
            
            if($_POST['pages'] == "1"){
                $totalAmountssssss = 0;
            }
           foreach ($data as $key => $row) {
                $val['DT_RowIndex'] =  $sno + 1;
                $val['date'] =  date("d/m/Y", strtotime($row->created_at));
                if(isset($row['bill_detail']->bill_number))
                {
                    $bill =  $row['bill_detail']->bill_number;
                }
                else{
                    $bill = 'N/A';
                }
                $val['bill_number'] = $bill;
                if(isset($row['branch_detail']->name))
                {
                   $branch = $row['branch_detail']->name; 
                }
                else{
                    $branch = 'N/A';
                }
                $val['branch_name'] = $branch;
                if(isset($row['branch_detail']->branch_code))
                {
                    $branch_code = $row['branch_detail']->branch_code; 
                }
                else{
                        $branch_code ='N/A';
                    }
                $val['branch_code'] = $branch_code;
                if(isset($row->description))
                 {
                     $description = $row->description;
                 }
                 else{
                     $description ='N/A';
                 }
                $val['particular'] = $description;
                
                if($row->payment_type == 'DR')
                {
                    if($request->vendor_type ==2 ||  $request->vendor_type ==3){
                        $dr =number_format((float)$row->withdrawal, 2, '.', '') ;
                    }else{
                         $dr =number_format((float)$row->amount, 2, '.', '') ;
                    }
                    
                }
                else{
                    $dr = '0';
                }

                $val['dr'] = $dr;
                if($row->payment_type == 'CR')
                {
                    if($request->vendor_type ==2 ||  $request->vendor_type ==3){
                        $cr =number_format((float)$row->deposit, 2, '.', '') ;
                    }else{
                        $cr = number_format((float) $row->amount, 2, '.', '') ;
                    }
                    
                }
                else{
                     $cr = '0';
                }
                $val['cr'] = $cr;
                 if($accounthead->cr_nature == 1)
                {
                    $total =(float)$cr  - (float)$dr ;
                    $totalAmountssssss = $totalAmountssssss + $total;
                }else{
                    $total = (float)$dr - (float)$cr;
                    $totalAmountssssss = $totalAmountssssss + $total;
                
                }

                $val['balance'] =number_format((float)$totalAmountssssss, 2, '.', '');
                if($row->payment_type == 'CR')
                {
                    $payment = 'Credit';
                } 
                else{
                    $payment = 'Debit';
                }
                $val['payment_type'] = $payment;

                    $rowReturn[] = $val;
            }
            $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn,'total' =>$totalAmountssssss );

            return json_encode($output);
         
         
    }

    // Associate Lsiting

    public function associateListing(Request $request)
    { 
        if ($request->ajax()) {
// fillter array 
        $arrFormData = array(); 
        if(!empty($_POST['searchform']))
        {
            foreach($_POST['searchform'] as $frm_data)
            {
                $arrFormData[$frm_data['name']] = $frm_data['value'];
            }
        }
            $data = Member::with('associate_branch')->where('member_id','!=','9999999')->where('is_associate',1);
   
    /******* fillter query start ****/        

           if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            {                 
                if($arrFormData['status'] !=''){
                    $status=$arrFormData['status'];                   

                    if($status==1)
                    {
                        $data=$data->where('status',1); 
                    }
                    if($status==0)
                    {
                        $data=$data->where('status',0); 
                    }              
               
                }
                if($arrFormData['start_date'] !=''){
                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if($arrFormData['end_date'] !=''){
                    $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    }
                    else
                    {
                        $endDate='';
                    }
                    $data=$data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]); 
                }
            }            

    /******* fillter query End ****/   

            $data=$data->orderby('associate_join_date','DESC')->get();

            return Datatables::of($data)
            ->addIndexColumn() 
            ->addColumn('join_date', function($row){
                $join_date =date("d/m/Y", strtotime($row->associate_join_date));;
                return $join_date;
            })
            ->rawColumns(['join_date']) 
                    
            ->addColumn('branch', function($row){
                $branch = $row['associate_branch']->name;
                return $branch;
            })
            ->rawColumns(['branch'])
            ->addColumn('branch_code', function($row){
                $branch_code = $row['associate_branch']->branch_code;
                return $branch_code;
            })
            ->rawColumns(['branch_code'])
            ->addColumn('sector', function($row){
                $sector =$row['associate_branch']->sector;
                return $sector;
            })
            ->rawColumns(['sector'])
            ->addColumn('region', function($row){
                $regan = $row['associate_branch']->regan;
                return $regan;
            })
            ->rawColumns(['region'])
            ->addColumn('zone', function($row){
                $zone = $row['associate_branch']->zone;
                return $zone;
            })
            ->rawColumns(['zone'])           
           ->addColumn('m_id', function($row){
                $m_id = $row->member_id;
                return $m_id;
            })
            ->rawColumns(['m_id'])
            ->addColumn('member_id', function($row){
                $associate_no = $row->associate_no;
                return $associate_no;
            })
            ->rawColumns(['member_id'])
            ->addColumn('dob', function($row){
                $dob = date('d/m/Y', strtotime($row->dob));
                return $dob;
            })
            ->rawColumns(['dob'])
            ->addColumn('name', function($row){
                $name =$row->first_name.' '.$row->last_name;
                return $name;
            })
            ->rawColumns(['name'])
            ->addColumn('mobile_no', function($row){
                $mobile_no =$row->mobile_no;
                return $mobile_no;
            })
            ->rawColumns(['mobile_no'])
            
            ->addColumn('associate_code', function($row){
                $associate_code =$row->associate_senior_code;
                return $associate_code;
            })
            ->rawColumns(['associate_code'])
            ->addColumn('associate_name', function($row){
                $associate_name = getSeniorData($row->associate_senior_id,'first_name').' '.getSeniorData($row->associate_senior_id,'last_name');
                return $associate_name;
            })
            ->rawColumns(['associate_name'])
            ->addColumn('status', function($row){
                 if($row->is_block==1)
                {
                    $status = 'Blocked';
                }
                else
                {
                        if($row->associate_status==1)
                        {
                          $status = 'Active';
                        }
                        else
                        {
                            $status = 'Inactive';
                        }

                }   
                $status = $status;
                return $status;
            })
            ->rawColumns(['status'])
           
            ->addColumn('action', function($row){ 



                 $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">'; 


                $url2 = URL::to("admin/associate-edit/".$row->id); 
                $url4 = URL::to("admin/hr/associate/detail/".$row->id);
                $url = URL::to("admin/vendor/associate_detail/".$row->id);
                 
                $btn .= '<a class="dropdown-item" href="'.$url.'" title="Employee Detail"><i class="icon-eye8  mr-2"></i>Associate Detail</a>'; 
                $btn .= '<a class="dropdown-item" href="'.$url2.'" title="Employee Edit"><i class="icon-pencil7  mr-2"></i>Associate Edit</a>';                                 

                $btn .= '</div></div></div>';  
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

    public function associate_detail($id)
    {
        $data['memberDetail'] = Member::where('id',$id)->first();
        $data['title'] = 'Associate Detail';
        $amnt=CommissionLeaserDetail::where('member_id',$id)->sum('amount');
        $fuelamnt=CommissionLeaserDetail::where('member_id',$id)->sum('fuel');
        $trans_fuelamnt=CommissionLeaserDetail::where('member_id',$id)->sum('transferred_fuel_amount');
        $trans_amnt=CommissionLeaserDetail::where('member_id',$id)->sum('transferred_amount');
        $realAmnt = $amnt + $fuelamnt;
        $transferAmnt = $trans_fuelamnt + $trans_amnt;
        $outstanding= $realAmnt - $transferAmnt;
        $data['outstanding'] = number_format((float)$outstanding, 2, '.', '');
        
        $advance_amt = \App\Models\BankingLedger::where('vendor_type',2)->where('vendor_type_id',$id)->sum('advanced_amount');
        $data['advance_amt'] = number_format((float)$advance_amt, 2, '.', '');

        return view('templates.admin.vendor_management.vendor.associate_detail', $data);

    }


    public function credit_node_transaction()
    {
		if(check_my_permission(Auth::user()->id,"229") != "1")
        {
            return redirect()->route('admin.dashboard');
         }
		 
        $id = '';
        if($_GET['id'])
        {
            $id = $_GET['id'];
        }
        $data['id'] = $id;
        $data['title'] = 'Credit Note Transaction';

       return view("templates.admin.vendor_management.vendor.credit_node_transaction",$data);
    }

    public function credit_node_transaction_list(Request $request)
    {
        if($request->ajax())
        {
            $id = $request->id;
            if($id)
            {
                 $data  = VendorCreditNode::where('vendor_id',$id);
            }
            // else{
            //      $data  = VendorCreditNode::where('vendor_id',$id);
            // }


            $data = $data->orderby('created_at','desc')->get();

            $count=count($data);
            $totalCount =$data->count();
            $sno=$_POST['start'];
            $rowReturn = array(); 

            foreach ($data as $key => $value) {
                 $sno++;

                $val['DT_RowIndex']=$sno;
                $val['date'] = date('d/m/Y',strtotime($value->credit_node_date));
                $val['credit_node'] = $value->credit_node;
                $val['order_no'] = $value->order_no;
                $val['balance'] =number_format((float)$value->total_amount - $value->used_amount, 2, '.', '');
                $val['amount'] = number_format((float)$value->total_amount, 2, '.', '');
                if($value->status == '0')
                {
                    $status = 'Open';
                }
                elseif($value->status == '1')
                {
                    $status = 'Closed';
                }
                else{
                    $status = 'N/A';
                }
                $val['status'] = $status;

                $rowReturn[] = $val;
            }
            $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );


            return json_encode($output);

        }

    }

    public function advance_transaction()
    {
		if(check_my_permission(Auth::user()->id,"230") != "1")
        {
            return redirect()->route('admin.dashboard');
         }
		 
        $id = '';
        $type = '';
        if($_GET['id'])
        {
            $id = $_GET['id'];
        }

        if($_GET['type'])
        {
            $type = $_GET['type'];
            
        }
        if($type == 0)
        {
            $type = 3;
        }
        elseif($type == 1)
        {
            $type = 4;
        }
        elseif($type == 3)
        {
            $type = 0;
        }
        elseif($type == 4)
        {
            $type = 1;
        }
          
        $data['id'] = $id;
        $data['title'] = 'Advance Transaction';
        $data['type'] = $type;
       return view("templates.admin.vendor_management.vendor.advance_transaction",$data);
    }
    
     public function advance_transaction_list(Request $request)
    {
               
        if($request->ajax())
        {
            $id = $request->id;
            if($id)
            {
               

                if($request->type == 4)
                {
                   
                     $data  = \App\Models\BankingLedger::whereIN('vendor_type',[$request->type,5])->where('vendor_type_id',$id);
                
                }
                else{

                     $data  = \App\Models\BankingLedger::where('vendor_type',$request->type)->where('vendor_type_id',$id)->where('advanced_amount','>',0);
                }
            }
           

            $data = $data->orderby('created_at','desc')->get();

            $count=count($data);
            $totalCount =$data->count();
            $sno=$_POST['start'];
            $rowReturn = array(); 

            foreach ($data as $key => $value) {
                 $sno++;

                $val['DT_RowIndex']=$sno;
                $val['date'] = date('d/m/Y',strtotime($value->created_at));
                $val['bill_no'] = $value->bill_id;
                if($value->payment_type == 'CR')
                {
                    $mode = 'Credit';
                }
                else{
                    $mode = 'Debit';
                }
                $val['mode'] = $mode;
                $val['t_amnt'] =number_format((float)$value->amount , 2, '.', '');
                if($value->vendor_type == 4 || $value->vendor_type == 5)
                {
                     $val['used_amnt'] = number_format((float)$value->amount - $value->customer_advanced_payment, 2, '.', '');
                }
                else{
                   $val['used_amnt'] = number_format((float)$value->amount - $value->advanced_amount, 2, '.', '');  
                }
               if($value->vendor_type == 4)
               {
                    $val['balance'] = number_format((float)$value->customer_advanced_payment, 2, '.', '');
               }
                else{
                    $val['balance'] = number_format((float)$value->advanced_amount, 2, '.', '');
                }
                

                $rowReturn[] = $val;
            }
            $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );


            return json_encode($output);

        }

    }

      public function jv_transaction()
    {
		if(check_my_permission(Auth::user()->id,"231") != "1")
        {
            return redirect()->route('admin.dashboard');
         }
		 
        $id = '';
        if($_GET['id'])
        {
            $id = $_GET['id'];
        }
        $data['id'] = $id;
        $data['title'] = 'Advance Transaction';

       return view("templates.admin.vendor_management.vendor.advance_transaction",$data);
    }
    
     public function jv_transaction_list(Request $request)
    {
        if($request->ajax())
        {
            $id = $request->id;
            if($id)
            {
                 $data  = AdvancedTransaction::where('type_id',$id);
            }
            // else{
            //      $data  = VendorCreditNode::where('vendor_id',$id);
            // }


            $data = $data->orderby('created_at','desc')->get();

            $count=count($data);
            $totalCount =$data->count();
            $sno=$_POST['start'];
            $rowReturn = array(); 

            foreach ($data as $key => $value) {
                 $sno++;

                $val['DT_RowIndex']=$sno;
                $val['date'] = date('d/m/Y',strtotime($value->entry_date));
                $val['bill_no'] = $value->bill_id;
                if($value->payment_type == 'CR')
                {
                    $mode = 'Credit';
                }
                else{
                    $mode = 'Debit';
                }
                $val['mode'] = $mode;
                $val['t_amnt'] =number_format((float)$value->total_amount , 2, '.', '');
                $val['used_amnt'] = number_format((float)$value->used_amount, 2, '.', '');
                
                $val['balance'] = number_format((float)$value->total_amount - $value->used_amount, 2, '.', '');

                $rowReturn[] = $val;
            }
            $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );


            return json_encode($output);

        }

    }

    /**
     * Show Vendor transaction log   .
     * Route: admin/vendor/log 
     * Method: get 
     * @return  array()  Response
     */
    public function transactionLog($id)
    {    
		if(check_my_permission(Auth::user()->id,"232") != "1")
        {
            return redirect()->route('admin.dashboard');
         }
        
        $data['log']=VendorLog::where('vendor_id',$id)->orderby('created_at','DESC')->get();   
		$data['title']='Vendor Management | Vendors Transaction Logs';  

        return view('templates.admin.vendor_management.vendor.log', $data);
    }

}
