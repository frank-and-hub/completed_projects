<?php
namespace App\Http\Controllers\Admin\Asset;
use Illuminate\Http\Request;
use Auth;
use App\Models\Settings;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\SamraddhBank;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use DB;
use URL;
use Session;
use App\Models\SamraddhBankAccount;
use App\Models\AccountHeads;
use App\Http\Controllers\Admin\CommanController;
use App\Models\DemandAdviceExpense;
use App\Models\DemandAdvice;
use App\Models\Files;
use App\Models\VendorBill;
use App\Models\ExpenseItem;
use App\Services\ImageUpload;



class AssetController extends Controller
{


        public function __construct()
    {
        $this->middleware('auth');
    }

        // Asset
        public function assetReport()
    {
        
        if(check_my_permission( Auth::user()->id,"62") != "1"){
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Assets Management | Assets List";
        $data['head'] = AccountHeads::select('id','head_id','sub_head')->where('parent_id',9)->where('status',0)->orderby('created_at','desc')->get();  
        
        if(Auth::user()->branch_id>0){
            $id=Auth::user()->branch_id;
            $data['branches'] = Branch::select('id','branch_code','name')->where('status',1)->where('id',$id)->get();
        }
        else{
            $data['branches'] = Branch::select('id','branch_code','name')->where('status',1)->get();
        }

        return view('templates.admin.asset_new.index',$data);
    }
        public function depreciationReport()
    {
        if(check_my_permission( Auth::user()->id,"63") != "1"){
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Depreciation Management | Depreciation List";
        if(Auth::user()->branch_id>0){
            $id=Auth::user()->branch_id;
            $data['branches'] = Branch::select('id','branch_code','name')->where('status',1)->where('id',$id)->get();
        }
        else{
            $data['branches'] = Branch::select('id','branch_code','name')->where('status',1)->get();
        }
        $data['head'] = AccountHeads::select('id','head_id','sub_head')->where('parent_id',9)->where('status',0)->orderby('created_at','desc')->get(); 
        return view('templates.admin.asset_new.depreciationReport',$data);
    }
        
        public function edit_asset($id)
    {
        $data['title'] = 'Edit | Asset';
        $asset = DemandAdviceExpense::select('id', 'current_balance', 'assets_subcategory', 'assets_category', 'purchase_date', 'amount', 'party_name', 'mobile_number', 'bill_number', 'bill_file_id', 'created_at', 'status', 'demand_advice_id', 'company_id','billId')
        ->with(['advices' => function ($q) {
            $q->select('id', 'status', 'payment_type', 'sub_payment_type', 'date', 'branch_id','company_id')
        ->with(['branch' => function ($q) {
                    $q->select('id', 'name', 'branch_code');
                }])
        ->with(['company' => function ($q) {
                    $q->select('id', 'name');
                }]);
        }])
        ->where('id', $id)
        ->first();


        // $branchDetail=getBranchDetail($asset['advices']['branch']->branch_id);
        $data['branch_name'] = $asset['advices']['branch']->name;
        $data['branch_id'] = $asset['advices']['branch']->id;
        $data['company_name'] = $asset['advices']['company']->name;
        $data['account_head_id'] = $asset->assets_category;
        $data['sub_account_head_id'] = $asset->assets_subcategory;
        $data['asset_id'] = $asset->id;
        $data['demand_id'] = $asset['advices']->id;
        $data['demand_date'] =date("d/m/Y", strtotime($asset['advices']->date));
        $data['advice_date'] = date("d/m/Y", strtotime($asset->purchase_date));
        $data['amount'] =  number_format((float)$asset->amount, 2, '.', '');
        $data['current_balance'] =  number_format((float)$asset->current_balance, 2, '.', '');
        $data['party_name'] =  $asset->party_name;
        $data['mobile_no'] =  $asset->mobile_number;
        $data['bill_no'] =  $asset->bill_number;
        $res = VendorBill::select('id','bill_upload')->where('id',$asset->billId)->first();
        if(isset($res))
        {
            $data['bill_copy'] = $res->bill_upload;
            $folderName = 'bill_expense/'.$res->bill_upload;
            $url = ImageUpload::generatePreSignedUrl($folderName);
            $data['bill_copy_path'] =$url;
        }
        else{
            $data['bill_copy'] = '';
            $data['bill_copy_path']='';
        }
        
        if($asset->status == 0){
                    $status = 'Working';
                }else{
                    $status = 'Damaged';        
                }
        $data['old_status'] = $status;
        $data['company_id'] = $asset->company_id;
        return view('templates.admin.asset_new.edit_asset',$data);
    }
        public function assetSave(Request $request) 
    { 

        
        DB::beginTransaction();
        try {
                $globaldate=$request->created_at; 
                Session::put('created_at', $request->created_at);
                $currency_code='INR';
                $entry_date=date("Y-m-d", strtotime(convertDate($globaldate)));
                $entry_time=date("H:i:s", strtotime(convertDate($globaldate)));

                $created_by=1;
                $created_by_id=\Auth::user()->id;
                $created_by_name=\Auth::user()->username; 
                $created_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $updated_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate))); 

                $randNumber = mt_rand(0,999999999999999);
                $v_no = $randNumber;
                $v_date = $entry_date;

                $amount=$request->amount;
                $branch_id=$request->branch_id;
                $type=19;
                $sub_type=191;
                $type_id=$request->demand_id;
                $tranId=$request->asset_id;
                $company_id=$request->company_id;
                $demandAdvice = DemandAdviceExpense::find($tranId);
                $current_balance=$demandAdvice->current_balance;
                $new_balance=$demandAdvice->current_balance-$current_balance;
                $amount=$current_balance;


                $daData = [
                        'current_balance' => $new_balance,
                        'remark' => $request->remark, 
                        'status' => 1, 
                    ];
                $demandAdvice->update($daData);
                
                $encodeDate = json_encode($_POST);
                



                $daybookRef=CommanController::createBranchDayBookReferenceNew($amount,$globaldate);
                $refId=$daybookRef;

                $des=$request->account_head_name.'-'.$request->sub_account_head_name.' damaged';
                $payment_mode=3;
                $bank_id = NULL; $bank_ac_id = NULL;  
                $amount_to_id = NULL; $amount_to_name = NULL; $amount_from_id = NULL; $amount_from_name = NULL; $ssb_account_id_from = NULL; $cheque_no = NULL; $cheque_date = NULL; $cheque_bank_from = NULL; $cheque_bank_ac_from = NULL; $cheque_bank_ifsc_from = NULL; $cheque_bank_branch_from = NULL; $cheque_bank_to = NULL; $cheque_bank_ac_to = NULL; $transction_no = NULL; $transction_bank_from = NULL; $transction_bank_ac_from = NULL; $transction_bank_ifsc_from = NULL; $transction_bank_branch_from = NULL; $transction_bank_to = NULL; $transction_bank_ac_to = NULL; $transction_date = NULL;  $ssb_account_id_to = NULL; $cheque_bank_from_id = NULL; $cheque_bank_ac_from_id = NULL; $cheque_bank_to_name = NULL; $cheque_bank_to_branch = NULL; $cheque_bank_to_ac_no = NULL; $cheque_bank_to_ifsc = NULL; $transction_bank_from_id = NULL; $transction_bank_from_ac_id = NULL; $transction_bank_to_name = NULL; $transction_bank_to_ac_no = NULL; $transction_bank_to_branch = NULL; $transction_bank_to_ifsc = NULL;
                $jv_unique_id=$ssb_account_tran_id_to=$ssb_account_tran_id_from=$cheque_type=$cheque_id=NULL;
                $associate_id=NULL;$member_id=NULL;$branch_id_to=NULL;$branch_id_from=NULL;$opening_balance=NULL;

            


                $lastheadId=2;
                $lastheadId=9;
                if($request->head_id!='')
                {
                    $lastheadId=$request->head_id;
                }
                if($request->sub_account_head_id!='')
                {
                    $lastheadId=$request->sub_account_head_id;
                }

            $allTranFixed= CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $lastheadId, $type, $sub_type, $type_id, $associate_idassociate_id=NULL, $member_id, $branch_id_to=NULL, $branch_id_from=NULL,  $amount, $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$company_id); 



            $allTranPL=CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 40, $type, $sub_type, $type_id, $associate_id=NULL, $member_id, $branch_id_to=NULL, $branch_id_from=NULL,  $amount,  $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$company_id); 

                
            
            DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                return back()->with('alert', $ex->getMessage());
            }
            return redirect('admin/asset')->with('success', 'Asset Updated successfully');
    }

        public function edit_depreciation($id)
    {
        $data['title'] = 'Edit | Depreciation';
        $asset=DemandAdviceExpense::select('id','assets_subcategory','current_balance','assets_category','purchase_date','amount','party_name','mobile_number','bill_number','bill_file_id','created_at','status','demand_advice_id','company_id')->with(['AssestFilesCustom'=>function($q){
            $q->select('id','file_name');
        },'AcountHeadNameHeadIdCustom'=>function($q){
            $q->select('id','head_id','sub_head');
        },'AcountHeadNameHeadIdCustom2'=>function($q){
            $q->select('id','head_id','sub_head');
        },'advices' => function($q){ $q->select('id','status','payment_type','sub_payment_type','date','branch_id','company_id')->with(['branch'=>function($q){
            $q->select('id','name','branch_code');
        }] )->with(['company' => function ($q) {
            $q->select('id', 'name');
        }]); }])->where('id',$id)->first();

        $data['asset_purchase_date'] = date("d/m/Y", strtotime($asset->purchase_date));
        $data['party_name'] =  $asset->party_name;
        $data['branch_id'] = $asset['advices']['branch']->id;
        $data['company_name'] = $asset['advices']['company']->name;
        $data['account_head_id'] = $asset->assets_category;
        $data['sub_account_head_id'] = $asset->assets_subcategory;
        $data['asset_id'] = $asset->id;
        $data['demand_id'] = $asset['advices']->id;
        $data['branch_name'] =  $asset['advices']['branch']->name;
        $data['asset_name'] =  $asset['AcountHeadNameHeadIdCustom']->sub_head;
        $data['asset_category'] = $asset['AcountHeadNameHeadIdCustom2']->sub_head;
        $data['total_asset'] = number_format((float)$asset->amount, 2, '.', '');
        $data['current_asset_value'] = number_format((float)$asset->current_balance, 2, '.', '');
        // $data['depreciation_percentage'] = "1000";
        $data['company_id'] = $asset->company_id;
        return view('templates.admin.asset_new.edit_depreciation',$data);
    }


        public function depreciationSave (Request $request) 
    { 

        DB::beginTransaction();
        try {
                $globaldate=$request->created_at; 
                $entryDate = $request->created_at; 
                
                Session::put('created_at', $request->created_at);
                $currency_code='INR';
                $entry_date=date("Y-m-d", strtotime(convertDate($entryDate)));
                
                $entry_time=date("H:i:s", strtotime(convertDate($entryDate)));

                $created_by=1;
                $created_by_id=\Auth::user()->id;
                $created_by_name=\Auth::user()->username; 
                $created_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $updated_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate))); 

                $randNumber = mt_rand(0,999999999999999);
                $v_no = $randNumber;
                $v_date = $entry_date;

                $after_depreciation_asset_value=$request->after_depreciation_asset_value;

                $current_asset_value=$request->current_asset_value;
                $amount=$current_asset_value-$after_depreciation_asset_value;

                $branch_id=$request->branch_id;
                $type=19;
                $sub_type=192;
                $type_id=$request->demand_id;
                $tranId=$request->asset_id;
                $companyId=$request->company_id;
                $demandAdvice = DemandAdviceExpense::find($tranId);
                $current_balance=$demandAdvice->current_balance; 


                $daData = [
                        'current_balance' => $after_depreciation_asset_value,
                        'remark' => $request->remark, 
                        //'status' => 1, 
                        'depreciation_per' => $request->depreciation_percentage, 
                    ];
                $demandAdvice->update($daData);

                $encodeDate = json_encode($_POST);
                

                $daybookRef=CommanController::createBranchDayBookReferenceNew($amount,$globaldate);
                $refId=$daybookRef;

                $des=$request->asset_name.'-'.$request->asset_category.' depreciation';
                $payment_mode=3;
                $bank_id = NULL; $bank_ac_id = NULL;  
                $amount_to_id = NULL; $amount_to_name = NULL; $amount_from_id = NULL; $amount_from_name = NULL; $ssb_account_id_from = NULL; $cheque_no = NULL; $cheque_date = NULL; $cheque_bank_from = NULL; $cheque_bank_ac_from = NULL; $cheque_bank_ifsc_from = NULL; $cheque_bank_branch_from = NULL; $cheque_bank_to = NULL; $cheque_bank_ac_to = NULL; $transction_no = NULL; $transction_bank_from = NULL; $transction_bank_ac_from = NULL; $transction_bank_ifsc_from = NULL; $transction_bank_branch_from = NULL; $transction_bank_to = NULL; $transction_bank_ac_to = NULL; $transction_date = NULL;  $ssb_account_id_to = NULL; $cheque_bank_from_id = NULL; $cheque_bank_ac_from_id = NULL; $cheque_bank_to_name = NULL; $cheque_bank_to_branch = NULL; $cheque_bank_to_ac_no = NULL; $cheque_bank_to_ifsc = NULL; $transction_bank_from_id = NULL; $transction_bank_from_ac_id = NULL; $transction_bank_to_name = NULL; $transction_bank_to_ac_no = NULL; $transction_bank_to_branch = NULL; $transction_bank_to_ifsc = NULL;

                $jv_unique_id=$ssb_account_tran_id_to=$ssb_account_tran_id_from=$cheque_type=$cheque_id=NULL;
                $associate_id=NULL;$member_id=NULL;$branch_id_to=NULL;$branch_id_from=NULL;$opening_balance=NULL;


            


       

                $lastheadId=2;
                $lastheadId=9;
                if($request->head_id!='')
                {
                    $lastheadId=$request->head_id;
                }
                if($request->sub_account_head_id!='')
                {
                    $lastheadId=$request->sub_account_head_id;
                }

            $allTranFixed= CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $lastheadId, $type, $sub_type, $type_id, $associate_id=NULL, $member_id, $branch_id_to=NULL, $branch_id_from=NULL,  $amount, $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$companyId); 

            
       

                $allTranPL=CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 40, $type, $sub_type, $type_id, $associate_id=NULL, $member_id, $branch_id_to=NULL, $branch_id_from=NULL,  $amount, $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$companyId); 

                
            
            DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                return back()->with('alert', $ex->getMessage());
            }
            return redirect('admin/depreciation')->with('success', 'Depreciation Updated successfully');
    }


        public function assetListing(Request $request)
    { 
        if ($request->ajax() && check_my_permission( Auth::user()->id,"62") == "1") {

            $arrFormData = array();   
            if(!empty($_POST['searchform']))
            {
                foreach($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            { 
                $data=DemandAdviceExpense::select('id','assets_subcategory','assets_category','purchase_date','amount','party_name','mobile_number','bill_number','bill_file_id','created_at','status','demand_advice_id','company_id','billId')
                ->has('company')
                ->with([
                    'AssestFilesCustom:id,file_name',
                    'AcountHeadNameHeadIdCustom:id,head_id,sub_head',
                    'AcountHeadNameHeadIdCustom2:id,head_id,sub_head',
                    'advices:id,status,payment_type,sub_payment_type,date,branch_id',
                    'advices.branch:id,name,branch_code',
                    'company:id,name,short_name'
                ])
                ->where('is_assets',0);
                
                
                if(Auth::user()->branch_id>0){
                    $branchId=Auth::user()->branch_id;
                    $data=$data->whereHas('advices', function ($query) use ($branchId) {
                        $query->where('demand_advices.branch_id',$branchId);
                    });
                }
                
                
                $data=$data->whereHas('advices', function ($query) {
                    $query->where('demand_advices.status',1);
                });
                $data=$data->whereHas('advices', function ($query)  {
                    $query->where('demand_advices.payment_type',0);
                });
                $data=$data->whereHas('advices', function ($query)  {
                    $query->where('demand_advices.sub_payment_type',0);
                });

                
            

                if(isset($arrFormData['company_id']) && $arrFormData['company_id'] > 0){
                    $companyId=$arrFormData['company_id'];
                    $data=$data->where('company_id',$companyId);
                }
                if(isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0){
                    $branchId=$arrFormData['branch_id'];
                    $data=$data->whereHas('advices', function ($query) use ($branchId) {
                        $query->where('demand_advices.branch_id',$branchId);
                    });
                }
                if($arrFormData['category'] !=''){
                    $category=$arrFormData['category'];
                    
                    $data=$data->where('assets_category',$category); 
                    
                }

                if($arrFormData['status'] !=''){
                    $status=$arrFormData['status'];
                    $data=$data->where('status',$status); 
                }
                
                $totalCount = $data->count('id'); 
                $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderby('id','DESC')->get();
                $sno = $_POST['start'];
                $rowReturn = array();
                        

                foreach ($data as $row)
                {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company'] = $row['company']->short_name;
                    $val['branch'] = $row['advices']['branch']->name.'-'.$row['advices']['branch']->branch_code;
                    if($row['AcountHeadNameHeadIdCustom']){
                        $val['assets_category'] = $row['AcountHeadNameHeadIdCustom']->sub_head; //getAcountHeadNameHeadId($row->assets_category);
                    }else{
                        $val['assets_category'] = 'N/A';
                    }
                    
                    $val['assets_subcategory'] = $row['AcountHeadNameHeadIdCustom2']->sub_head;
                    $val['demand_date'] = date("d/m/Y", strtotime($row['advices']->date));
                    $val['advice_date'] = date("d/m/Y", strtotime($row->purchase_date));
                    $val['amount'] = number_format((float)$row->amount, 2, '.', '');
                    $val['party_name'] = $row->party_name;
                    $val['mobile_number'] = $row->mobile_number;
                    $val['bill_number'] = $row->bill_number;

                    if($row->billId)
                    {
                        $res = VendorBill::select('id','bill_upload')->where('id',$row->billId)->first();
                        $folderName = 'bill_expense/'.$res->bill_upload;
                        $url=ImageUpload::generatePreSignedUrl($folderName);
                        $val['bill_file_id'] = '<a href="'.$url.'" target="blank">'.$res->bill_upload.'</a>';
                    }
                    else
                    {
                        $val['bill_file_id'] ='N/A'; 
                    }

                    if($row->status == 0){
                        $status = 'Working';
                    }else{
                        $status = 'Damaged';        
                    }

                    $val['status'] = $status;
                    $val['created_at'] = date("d/m/Y", strtotime( $row->created_at));

                    $url = URL::to("admin/asset/edit/".$row->id."");
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    if($row->status == 0){
                    $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                    }
                    else
                    {
                    $btn.='N/A';
                    }
                    $btn .= '</div></div></div>';

                    $val['action'] = $btn;
                    $rowReturn[] = $val;                

                }

                $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn );
                return json_encode($output);
            }
            else
            {
                $output = array(
                    "draw" =>0,
                    "recordsTotal" => 0,
                    "recordsFiltered" =>0,
                    "data" => 0,
                );
                return json_encode($output);
            }
        }
    }
        

        public function depreciationListing(Request $request)
    { 
        if ($request->ajax() && check_my_permission( Auth::user()->id,"63") == "1") {

            $arrFormData = array();   
            if(!empty($_POST['searchform']))
            {
                foreach($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            { 
                $data = DemandAdviceExpense::select(
                    'id', 'current_balance', 'assets_subcategory', 'assets_category',
                    'purchase_date', 'amount', 'party_name', 'mobile_number',
                    'bill_number', 'bill_file_id', 'created_at', 'status', 'demand_advice_id','company_id','billId','depreciation_per'
                )->has('company')
                    ->with([
                        'AssestFilesCustom' => function ($q) {
                            $q->select('id', 'file_name');
                        },
                        'AcountHeadNameHeadIdCustom' => function ($q) {
                            $q->select('id', 'head_id', 'sub_head');
                        },
                        'AcountHeadNameHeadIdCustom2' => function ($q) {
                            $q->select('id', 'head_id', 'sub_head');
                        },
                        'advices' => function ($q) {
                            $q->select('id', 'status', 'payment_type', 'sub_payment_type', 'date', 'branch_id','company_id')
                                ->with([
                                    'branch' => function ($q) {
                                        $q->select('id', 'name', 'branch_code');
                                    }
                                ]);
                        }
                    ])->where('is_assets', 0);
        
                
                if(Auth::user()->branch_id>0){
                $branchId=Auth::user()->branch_id;
                    $data=$data->whereHas('advices', function ($query) use ($branchId) {
                        $query->where('demand_advices.branch_id',$branchId);
                    });
                }
                
                $data=$data->whereHas('advices', function ($query) {
                    $query->where('demand_advices.status',1);
                });
                $data=$data->whereHas('advices', function ($query)  {
                    $query->where('demand_advices.payment_type',0);
                });
                $data=$data->whereHas('advices', function ($query)  {
                    $query->where('demand_advices.sub_payment_type',0);
                });            
                if(isset($arrFormData['company_id']) && $arrFormData['company_id'] > 0){
                    $companyId=$arrFormData['company_id'];
                    if($companyId != '0'){
                        $data=$data->where('company_id',$companyId);
                    }
                }
                if(isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0){
                    $branchId=$arrFormData['branch_id'];
                    if($branchId != '0'){
                        $data=$data->whereHas('advices', function ($query) use ($branchId) {
                            $query->where('demand_advices.branch_id',$branchId);
                        });
                    }
                }
                if($arrFormData['category'] !=''){
                    $category=$arrFormData['category'];
                    $data=$data->where('assets_category',$category); 
                }
                $data1 = $data->count('id');
                $totalCount = $data1;
                $count = $data1;
                $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderBy('created_at','DESC')->get();	
                // print_r($data);die;
                $sno=$_POST['start'];
                $rowReturn = array(); 

            
                foreach ($data as $row)
                {
                    $sno++;
                    $val['DT_RowIndex']=$sno;
                    $branch = 'N/A';
                    if(isset($row['advices']['branch']->name))
                    {
                        $branch=$row['advices']['branch']->name .'-'. $row['advices']['branch']->branch_code;
                    }
                        $val['branch']= $branch;
                        $assets_categorys = 'N/A';
                        $assets_subcategorys = 'N/A';
                        $assets_subcategory =$row['AcountHeadNameHeadIdCustom2']->sub_head;;
                        if(isset($assets_subcategory))
                        {
                            $assets_subcategorys=$assets_subcategory;     
                        }
                        $assets_category = $row['AcountHeadNameHeadIdCustom']->sub_head;;
                        if(isset($assets_category))
                        {
                            $assets_categorys = $assets_category; 
                        }
                    
                        $val['assets_category']=$assets_categorys;
                        $val['assets_subcategory']=$assets_subcategorys;
                        $advice_date = date("d/m/Y", strtotime($row->purchase_date));
                        $val['advice_date']=$advice_date;
                        $depreciation_date = 'N/A';
                        if(isset($row->depreciation_date)){
                            $depreciation_date = date("d/m/Y", strtotime($row->depreciation_date));
                        }
                        $val['depreciation_date']=$depreciation_date;
                        $val['amount']= number_format((float)$row->amount, 2, '.', '');
                        $val['current_balance']= number_format((float)$row->current_balance, 2, '.', '');
                        
                        if($row->depreciation_per)
                        {
                            $depreciation_per = number_format((float)$row->depreciation_per, 2, '.', '');
                        }else{
                            $depreciation_per = 'N/A';
                        }
                        $val['depreciation_per']= $depreciation_per;
                        $val['party_name']=  $row->party_name;
                        $val['mobile_number']=  $row->mobile_number;
                        $val['bill_number']=  $row->bill_number;
                        
                        if($row->billId)
                        {
                            $res = VendorBill::select('id','bill_upload')->where('id',$row->billId)->first();
                            $folderName = 'bill_expense/'.$res->bill_upload;
                            $url=ImageUpload::generatePreSignedUrl($folderName);
                            $val['bill_file_id'] = '<a href="'.$url.'" target="blank">'.$res->bill_upload.'</a>';
                        }

                        else
                        {
                            $val['bill_file_id'] ='N/A'; 
                        }
                        $val['created_at']= date("d/m/Y", strtotime( $row->created_at));
                        $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                        $url = URL::to("admin/depreciation/edit/".$row->id."");
                        if($row->current_balance>0)  
                        {  
                            $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Depreciation Update</a>';
                        }
                        else
                        {
                            $btn.='N/A';
                        }
                        $btn .= '</div></div></div>';
                        $val['action'] = $btn;
                        $rowReturn[] = $val; 
                }
                
                $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                return json_encode($output);
            }
            else
            {
                $output = array(
                
                    "draw" =>0,
                    "recordsTotal" => 0,
                    "recordsFiltered" =>0,
                    "data" => 0,
                );
                return json_encode($output);
            
            }
        }
    }

        public function asset_items(Request $request)
    {
        $company=$request->company;
        $data['items'] = AccountHeads::select('id','head_id','sub_head')
        ->where('parent_id',9)
        ->where('status',0)
        ->orderby('created_at','desc')
        ->where('company_id', 'LIKE', '%' . $company . '%')
        ->get();

        return $data;
    }
    
}