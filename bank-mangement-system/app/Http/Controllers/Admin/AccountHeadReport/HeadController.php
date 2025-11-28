<?php

namespace App\Http\Controllers\Admin\AccountHeadReport;

use Illuminate\Http\Request;

use Auth;

use App\Models\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Branch;

use App\Models\AccountHeads;

use App\Models\Companies;

use Yajra\DataTables\DataTables;

use Carbon\Carbon;

use DB;

use URL;

use App\Models\HeadLog;

use Illuminate\Support\Facades\Cache;

class HeadController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth');

    }

    // EliLoan  Report

    public function index()
    {


        if (check_my_permission(Auth::user()->id, "143") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Account Head Management | Head";
        $data['company'] = Companies::whereStatus('1')->Pluck('name', 'id');
        return view('templates.admin.account_head_report.head.index', $data);

    }

    public function getHeadlistbyCompany(Request $request)
    {

        $companyList = $request->companyList;

        $arrayCompanyList = explode(' ', $companyList);

        $companyList = array_map(function ($value) {
            return intval($value);
        }, $arrayCompanyList);

        $data['heads'] = $heads = AccountHeads::getCompanyRecords("CompanyId", $companyList)->select('id', 'head_id', 'sub_head', 'parent_id', 'labels')->where('labels', 1)->get();

        $data['count'] = $count = count($data['heads']);

        $data['companyLists'] = $companyLists = $request->companyList;

        return \Response::json(['view' => view('templates.admin.account_head_report.head.partials.filtered_account_head', ['data' => $data, 'heads' => $heads, 'count' => $count, 'companyLists' => $companyList, "companyListing" => $request->companyList])->render(), 'msg_type' => 'success']);



    }

    public function create_head()
    {
        if (check_my_permission(Auth::user()->id, "142") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Account Head Management | Create head & Assign company";

        $data['heads'] = AccountHeads::select('id', 'head_id', 'sub_head', 'parent_id', 'labels')->where('labels', 1)->get();

        $data['company'] = Companies::whereStatus('1')->Pluck('name', 'id');

        return view('templates.admin.account_head_report.head.create_head', $data);

    }

    public function updateComanyHead(Request $request)
    {
      
      
        $rules = [
            'selected_companies' => 'required',
            
        ];
        
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
       $error = $this->validate($request, $rules, $customMessages);
   
       if($error['selected_companies'][0]==null){
        return response()->json(['msg_type' => 'error']);
    
       }
        $selectedCompanies = array_map('intval', $request->selected_companies);
        $accountHeadCompanies = AccountHeads::where('id', $request->headId)->first();
    
        $existingCompanies = json_decode($accountHeadCompanies->company_id, true);
     
        $mergedCompanies = array_merge($existingCompanies, $selectedCompanies);
    
       
        $uniqueCompanies = array_values(array_unique($mergedCompanies));
    
        $head_data['company_id'] = json_encode($uniqueCompanies);
    
        $accountHeads = AccountHeads::where('id', $request->headId)->update($head_data);
        $accountHead = AccountHeads::with('parent')->where('id', $request->headId)->first();
        // dd($accountHead->parent->sub_head);
        $selectedCompaniesAsString = implode(',', $selectedCompanies);

   

        head_create_logs($accountHead->head_id, json_encode($uniqueCompanies), $accountHead->parent_id, $type = 2,$accountHead->sub_head, $accountHead->parent->sub_head, $selectedCompaniesAsString,$data1=null,$data2=null,$newHead=null,$request->systemdate);
    
       
        
        return response()->json(['msg_type' => 'success']);
    
    }

    
    public function getCompanies(Request $request){

   
 
        if ($request->companyIds === null) {
            $getAllCompanies = Companies::get();
        } else {
            $getAllCompanies = Companies::whereIn('id', $request->companyIds)->get();
        
            
        }

        return response()->json(['comapnies' => $getAllCompanies,'message_type' => 'success']);
    }
    public function checkHeadTitle(Request $request)
    {
        $checkHead = AccountHeads::where('sub_head', $request->new_head)->first();

        if ($checkHead) {
            $companiesCount = Companies::count();
            $companyIdArray = json_decode($checkHead->company_id, true);
    
            if (is_array($companyIdArray)) {
                $headsCompanyCount = count($companyIdArray);
            } else {
                $headsCompanyCount = 0;
            }
    
            $parentID = '';
            if ($request->head1 != '') {
                $parentID = $request->head1;
            }
            if ($request->head2 != '') {
                $parentID = $request->head2;
            }
            if ($request->head3 != '') {
                $parentID = $request->head3;
            }
            if ($request->head4 != '') {
                $parentID = $request->head4;
            }
    
            // dd($checkHead->parent_id, $parentID);
            if ($headsCompanyCount == $companiesCount && $checkHead->parent_id == $parentID) {
                $headParentId = $checkHead->parent_id;
                $data = $this->datarecord($headParentId);
                $data['checkHead'] = $checkHead;
    
                return response()->json(['exists' => '0', 'data' => $data]);
            } elseif ($checkHead->parent_id == $parentID && $headsCompanyCount != $companiesCount) {
                $headParentId = $checkHead->parent_id;
    
                if (in_array($request->company, $companyIdArray)) {
                    $data = $this->datarecord($headParentId);
                    $data['checkHead'] = $checkHead;
    
                    $data = array_reverse($data, true);
    
                    // All heads are the same
                    return response()->json(['exists' => '1', 'data' => $data]);
                } elseif (!in_array($request->company, $companyIdArray)) {
                    $data = $this->datarecord($headParentId);
                    $data['checkHead'] = $checkHead;
    
                    $data = array_reverse($data, true);
    
                    return response()->json(['exists' => '4', 'data' => $data]);
                } else {
                    $data = $this->datarecord($headParentId);
                    $data['checkHead'] = $checkHead;
    
                    return response()->json(['exists' => '2', 'data' => $data]);
                }
            
            
        } elseif ($checkHead->parent_id != $parentID && !in_array($request->company, $companyIdArray)) {
            $headParentId = $checkHead->parent_id;

            $data = $this->datarecord($headParentId);
            $data['checkHead'] = $checkHead;

            $data = array_reverse($data, true);

            return response()->json(['exists' => '3', 'data' => $data]);
        } 
            else {
                $headParentId = $checkHead->parent_id;

            $data = $this->datarecord($headParentId);
            $data['checkHead'] = $checkHead;

            $data = array_reverse($data, true);
                return response()->json(['exists' => '3', 'data' => $data]);
            }
        } else {
            //if head is not matched 
            return response()->json(['exists' => '5']);
        }
    }


    // public function save(Request $request)
    // {


    //     $rules = [
    //         'company' => 'required',
    //         'new_head' => 'required',
    //         'head1' => 'required',
    //     ];
    //     $customMessages = [
    //         'required' => 'The :attribute field is required.'
    //     ];
    //     $this->validate($request, $rules, $customMessages);

    //     $company = $request->company;
    //     $head1 = $request->head1;
    //     $head2 = $request->head2;
    //     $head3 = $request->head3;
    //     $head4 = $request->head4;

    //     $startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), 33));
    //     $currentDatee = date('d/m/Y', strtotime($startDatee));

    //     $getLastHeadId = AccountHeads::orderBy('head_id', 'desc')->first('head_id');
    //     try {

    //         $parentID = '';
    //         if ($head1 != '') {
    //             $parentID = $head1;
    //         }
    //         if ($head2 != '') {
    //             $parentID = $head2;
    //         }
    //         if ($head3 != '') {
    //             $parentID = $head3;
    //         }
    //         if ($head4 != '') {
    //             $parentID = $head4;
    //         }

    //         $assetData = AccountHeads::select('id', 'labels', 'cr_nature', 'dr_nature', 'company_id')->where('head_id', $parentID)->first();

    //         $head_data['sub_head'] = $request->new_head;
    //         $head_data['head_id'] = $getLastHeadId->head_id + 1;
    //         $head_data['parent_id'] = $parentID;
    //         $head_data['labels'] = $assetData->labels + 1;
    //         $head_data['parentId_auto_id'] = $assetData->id;
    //         $head_data['cr_nature'] = $assetData->cr_nature;
    //         $head_data['dr_nature'] = $assetData->dr_nature;
    //         $head_data['is_move'] = 1;
    //         $head_data['company_id'] = json_encode([(int) $company]);
    //         $head_data['status'] = 0;
    //         $head_data['can_disable_status'] = 0;
    //         $head_data['entry_everywhere'] = 1;
    //         $head_data['created_at'] = date("Y-m-d", strtotime(convertDate($currentDatee)));
    //         if ($head1 == 96) {
    //             $head_data['created_at'] = '2020-06-01';
    //         }

    //         $accountHeads = AccountHeads::create($head_data);

    //         return \Response::json(['msg_type' => 'success']);

    //         //$encodeDate = json_encode($head_data);
    //         //$arrs = array("bank_account_id" => 0, "type" => "16", "account_head_id" => $accountHeads->id, "user_id" => Auth::user()->id, "message" => "Creation of indirect expense ", "data" => $encodeDate);
    //         //DB::table('user_log')->insert($arrs);

    //         DB::commit();

    //     } catch (\Exception $ex) {

    //         DB::rollback();

    //         return back()->with('alert', $ex->getMessage());

    //     }



    // }

    public function save(Request $request)
    {

        

        $rules = [
            'company' => 'required',
            'new_head' => 'required',
            'head1' => 'required',
        ];
        
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);


        $checkHead = AccountHeads::where('sub_head', $request->new_head)->first();
        

        
        if ($checkHead) {
            $companiesCount = Companies::count();
            $companyIdArray = json_decode($checkHead->company_id, true);
    
            if (is_array($companyIdArray)) {
                $headsCompanyCount = count($companyIdArray);
            } else {
                $headsCompanyCount = 0;
            }
    
            $parentID = '';
            if ($request->head1 != '') {
                $parentID = $request->head1;
            }
            if ($request->head2 != '') {
                $parentID = $request->head2;
            }
            if ($request->head3 != '') {
                $parentID = $request->head3;
            }
            if ($request->head4 != '') {
                $parentID = $request->head4;
            }
    
            // dd($checkHead->parent_id, $parentID);
            if ($headsCompanyCount == $companiesCount && $checkHead->parent_id == $parentID) {
                $headParentId = $checkHead->parent_id;
                $data = $this->datarecord($headParentId);
                $data['checkHead'] = $checkHead;
    
                return response()->json(['exists' => '0', 'data' => $data]);
            } elseif ($checkHead->parent_id == $parentID && $headsCompanyCount != $companiesCount) {
                $headParentId = $checkHead->parent_id;
    
                if (in_array($request->company, $companyIdArray)) {
                    $data = $this->datarecord($headParentId);
                    $data['checkHead'] = $checkHead;
    
                    $data = array_reverse($data, true);
    
                    // All heads are the same
                    return response()->json(['exists' => '1', 'data' => $data]);
                } elseif (!in_array($request->company, $companyIdArray)) {
                    $data = $this->datarecord($headParentId);
                    $data['checkHead'] = $checkHead;
    
                    $data = array_reverse($data, true);
    
                    return response()->json(['exists' => '4', 'data' => $data]);
                } else {
                    $data = $this->datarecord($headParentId);
                    $data['checkHead'] = $checkHead;
    
                    return response()->json(['exists' => '2', 'data' => $data]);
                }
            
            
        } elseif ($checkHead->parent_id != $parentID && !in_array($request->company, $companyIdArray)) {
            $headParentId = $checkHead->parent_id;

            $data = $this->datarecord($headParentId);
            $data['checkHead'] = $checkHead;

            $data = array_reverse($data, true);

            return response()->json(['exists' => '3', 'data' => $data]);
        } 
            else {
                $headParentId = $checkHead->parent_id;

            $data = $this->datarecord($headParentId);
            $data['checkHead'] = $checkHead;

            $data = array_reverse($data, true);
                return response()->json(['exists' => '3', 'data' => $data]);
            }
        } else {
            $company = $request->company;
        $head1 = $request->head1;
        $head2 = $request->head2;
        $head3 = $request->head3;
        $head4 = $request->head4;
       
        $startDatee = (checkMonthAvailability(date('d'),date('m'),date('Y'),33));
        $currentDatee =  date('d/m/Y',strtotime($startDatee));
        
        $getLastHeadId = AccountHeads::orderBy('head_id', 'desc')->first('head_id');
        try {

            $parentID= '';
            if($head1!='')
            {
                $parentID= $head1; 
            }
            if($head2!='')
            {
                $parentID= $head2; 
            }
            if($head3!='')
            {
                $parentID= $head3; 
            }
            if($head4!='')
            {
                $parentID= $head4; 
            }

           // echo $parentID;die;

            $assetData = AccountHeads::select('id', 'labels', 'cr_nature', 'dr_nature', 'company_id')->where('head_id', $parentID)->first();
            $chlidHead = AccountHeads::where('head_id',$parentID)->first();
            $head_data['sub_head'] = $request->new_head;
            $head_data['head_id'] = $getLastHeadId->head_id + 1;
            $head_data['parent_id'] = $parentID;
            $head_data['labels'] = $assetData->labels + 1;
            $head_data['parentId_auto_id'] = $assetData->id;
            $head_data['cr_nature'] = $assetData->cr_nature;
            $head_data['dr_nature'] = $assetData->dr_nature;
            $head_data['is_move'] = 1;
            $head_data['company_id'] = json_encode([(int) $company]);
            $head_data['status'] = 0;
            $head_data['can_disable_status'] = 0;
            // $head_data['basic_heads'] = 0;
            $head_data['entry_everywhere'] = 1;
           
            // $head_data['created_at'] =  date("Y-m-d", strtotime(convertDate($currentDatee)));
            if ($head1 == 96) {
                $head_data['created_at'] = '2020-06-01';
            } 
            
            $accountHeads = AccountHeads::create($head_data);

            $chlidHead->child_head = json_encode([(int) $accountHeads->id]);
            $chlidHead->update();

            $parentHead = AccountHeads::with('parent')->where('head_id',$parentID)->first();
         
            

            head_create_logs($getLastHeadId->head_id + 1, json_encode([(int) $company]), $parentID, $type = 1,$request->new_head, $parentHead->sub_head, $selectedCompanies =null, $new =null,$parentheadid =null,$newhead=null, $request->systemdate);
           

           
            // return \Response::json(['msg_type' => 'success']);
            return response()->json(['msg_type' => 'success']);

            //$encodeDate = json_encode($head_data);
            //$arrs = array("bank_account_id" => 0, "type" => "16", "account_head_id" => $accountHeads->id, "user_id" => Auth::user()->id, "message" => "Creation of indirect expense ", "data" => $encodeDate);
            //DB::table('user_log')->insert($arrs);

            DB::commit();
           
        } catch (\Exception $ex) {
            dd($ex->getMessage());
            DB::rollback();

            return back()->with('alert', $ex->getMessage());

        }
        }











        // $company = $request->company;
        // $head1 = $request->head1;
        // $head2 = $request->head2;
        // $head3 = $request->head3;
        // $head4 = $request->head4;
       
        // $startDatee = (checkMonthAvailability(date('d'),date('m'),date('Y'),33));
        // $currentDatee =  date('d/m/Y',strtotime($startDatee));
        
        // $getLastHeadId = AccountHeads::orderBy('head_id', 'desc')->first('head_id');
        // try {

        //     $parentID= '';
        //     if($head1!='')
        //     {
        //         $parentID= $head1; 
        //     }
        //     if($head2!='')
        //     {
        //         $parentID= $head2; 
        //     }
        //     if($head3!='')
        //     {
        //         $parentID= $head3; 
        //     }
        //     if($head4!='')
        //     {
        //         $parentID= $head4; 
        //     }

        //    // echo $parentID;die;

        //     $assetData = AccountHeads::select('id', 'labels', 'cr_nature', 'dr_nature', 'company_id')->where('head_id', $parentID)->first();
        //     $chlidHead = AccountHeads::where('head_id',$parentID)->first();
        //     $head_data['sub_head'] = $request->new_head;
        //     $head_data['head_id'] = $getLastHeadId->head_id + 1;
        //     $head_data['parent_id'] = $parentID;
        //     $head_data['labels'] = $assetData->labels + 1;
        //     $head_data['parentId_auto_id'] = $assetData->id;
        //     $head_data['cr_nature'] = $assetData->cr_nature;
        //     $head_data['dr_nature'] = $assetData->dr_nature;
        //     $head_data['is_move'] = 1;
        //     $head_data['company_id'] = json_encode([(int) $company]);
        //     $head_data['status'] = 0;
        //     $head_data['can_disable_status'] = 0;
        //     // $head_data['basic_heads'] = 0;
        //     $head_data['entry_everywhere'] = 1;
           
        //     // $head_data['created_at'] =  date("Y-m-d", strtotime(convertDate($currentDatee)));
        //     if ($head1 == 96) {
        //         $head_data['created_at'] = '2020-06-01';
        //     } 

        //     $accountHeads = AccountHeads::create($head_data);

        //     $chlidHead->child_head = json_encode([(int) $accountHeads->id]);
        //     $chlidHead->update();
        //     // return \Response::json(['msg_type' => 'success']);
        //     return response()->json(['msg_type' => 'success']);

        //     //$encodeDate = json_encode($head_data);
        //     //$arrs = array("bank_account_id" => 0, "type" => "16", "account_head_id" => $accountHeads->id, "user_id" => Auth::user()->id, "message" => "Creation of indirect expense ", "data" => $encodeDate);
        //     //DB::table('user_log')->insert($arrs);

        //     DB::commit();
           
        // } catch (\Exception $ex) {
        //     dd($ex->getMessage());
        //     DB::rollback();

        //     return back()->with('alert', $ex->getMessage());

        // }

       

    }



    public function head_listing(Request $request)
    {

        $data = AccountHeads::where('labels', 1)->get();
        return view('templates.admin.account_head_report.head.index', $data);

    }



    public function edit_head($id, $label, Request $request)
    {
        if (check_my_permission(Auth::user()->id, "145") != "1") {
            return redirect()->route('admin.dashboard');
        }


        $data['title'] = "Account Head Management | Edit Head";

        $data['head'] = AccountHeads::find($id);

        $data['labels'] = $label;

        if ($data['head']->labels == 5) {
            $data['sub_child_expense1'] = AccountHeads::where('id', $data['head']->id)->where('labels', 5)->first();


            $data['sub_child_expense'] = AccountHeads::where('head_id', $data['sub_child_expense1']->parent_id)->where('labels', 4)->first();
            ;
            $data['child_expense'] = AccountHeads::where('head_id', $data['sub_child_expense']->parent_id)->where('labels', 3)->first();

            $data['head4'] = AccountHeads::where('parent_id', $data['child_expense']->head_id)->get();

            $data['child_expense2'] = AccountHeads::where('head_id', $data['child_expense']->parent_id)->where('labels', 2)->first();
            $data['head3'] = AccountHeads::where('parent_id', $data['child_expense2']->head_id)->get();
            $data['child_expense3'] = AccountHeads::where('head_id', $data['child_expense2']->parent_id)->where('labels', 1)->first();
            $data['head2'] = AccountHeads::where('parent_id', $data['child_expense3']->head_id)->get();


        } elseif ($data['head']->labels == 4) {


            $data['sub_child_expense1'] = AccountHeads::where('id', $data['head']->id)->where('labels', 4)->first();
            // $data['sub_child_expense'] = AccountHeads::where('head_id',$data['sub_child_expense1']->parent_id)->where('labels', 4)->first();
            // dd($data['sub_child_expense1']);
            $data['child_expense'] = AccountHeads::where('head_id', $data['sub_child_expense1']->parent_id)->where('labels', 3)->first();
            // dd($data['child_expense']);
            $data['head4'] = array();
            $data['child_expense2'] = AccountHeads::where('head_id', $data['child_expense']->parent_id)->where('labels', 2)->first();
            $data['head3'] = AccountHeads::where('parent_id', $data['child_expense2']->head_id)->get();
            $data['child_expense3'] = AccountHeads::where('head_id', $data['child_expense2']->parent_id)->where('labels', 1)->first();
            $data['head2'] = AccountHeads::where('parent_id', $data['child_expense3']->head_id)->get();
        } elseif ($data['head']->labels == 3) {
            $data['sub_child_expense1'] = AccountHeads::where('id', $data['head']->id)->where('labels', 3)->first();

            $head4 = AccountHeads::where('head_id', $data['sub_child_expense1']->parent_id)->where('labels', 4)->first();
            if ($head4) {
                $data['sub_child_expense'] = AccountHeads::where('head_id', $data['sub_child_expense1']->parent_id)->where('labels', 4)->first();
            }
            $data['child_expense'] = AccountHeads::where('head_id', $data['head']->parent_id)->where('labels', 3)->first();
            if ($data['child_expense']) {
                $data['head3'] = AccountHeads::where('parent_id', $data['child_expense2']->head_id)->get();
            } else {
                $data['head3'] = array();
            }
            $data['head4'] = array();
            $data['child_expense2'] = AccountHeads::where('head_id', $data['sub_child_expense1']->parent_id)->where('labels', 2)->first();
            $data['head2'] = AccountHeads::where('parent_id', $data['child_expense2']->head_id)->get();

            $data['child_expense3'] = AccountHeads::where('head_id', $data['child_expense2']->parent_id)->where('labels', 1)->first();
            $data['head2'] = AccountHeads::where('parent_id', $data['child_expense3']->head_id)->get();

        } elseif ($data['head']->labels == 2) {
            //dd($id,$label);
            $data['child_expense2'] = AccountHeads::where('head_id', $data['head']->parent_id)->where('labels', 2)->first();
            if ($data['child_expense2']) {

                $data['head2'] = AccountHeads::where('parent_id', $data['child_expense2']->head_id)->get();
            } else {
                $data['head2'] = array();
            }
            if ($data['child_expense2']) {
                $data['child_expense3'] = AccountHeads::where('head_id', $data['child_expense2']->parent_id)->where('labels', 1)->first();
            } else {
                $data['child_expense3'] = AccountHeads::where('head_id', $data['head']->parent_id)->first();
            }
            $data['child_expense'] = array();
            $data['head3'] = array();
            $data['head4'] = array();
            $data['sub_child_expense1'] = AccountHeads::where('id', $data['head']->id)->first();
        } else {



            $data['child_expense'] = AccountHeads::where('head_id', $data['head']->parent_id)->first();

        }



        $data['sub_expense'] = AccountHeads::where('labels', 1)->get();




        return view('templates.admin.account_head_report.head.edit_head', $data);

    }


    public function getparentHeadbyCompany(Request $request)
    {

        $companyList = $request->company_id;
        $arrayCompanyList = explode(' ', $companyList);


        $companyList = array_map(function ($value) {
            return intval($value);
        }, $arrayCompanyList);



        $data['parent_headid'] = AccountHeads::getCompanyRecords("CompanyId", $companyList)->select('id', 'head_id', 'sub_head', 'parent_id', 'labels')->where('labels', 1)->get();
        // $data['parent_headid'] = AccountHeads::select('id','head_id','sub_head','parent_id','labels')->where('parent_id',$request->child_asset_id)->whereNotIn('head_id',[27,18,19,15])->get();

        return response()->json($data);

    }




    // public function getChildAsset(Request $request)
    // {
    //     $data['sub_child_assets'] = AccountHeads::select('id', 'head_id', 'sub_head', 'parent_id', 'labels')->where('parent_id', $request->child_asset_id)->whereNotIn('head_id', [27, 18, 19, 15])->where('company_id', 'like', '%' . $request->company_id . '%')->get();

    //     return response()->json($data);

    // }

    public function getChildAsset(Request $request)
    {

        $companyList = $request->selectedCompany;
        $arrayCompanyList = explode(' ', $companyList);


        $companyList = array_map(function ($value) {
            return intval($value);
        }, $arrayCompanyList);

        $data['sub_child_assets'] = AccountHeads::getCompanyRecords("CompanyId", $companyList)->select('id', 'head_id', 'sub_head', 'parent_id', 'labels')->where('parent_id', $request->child_asset_id)->whereNotIn('head_id', [27, 18, 19, 15])->where('status', 0)->get();

        return response()->json($data);

    }



    // public function update_head(Request $request)
    // {


    //     //  dd($request->all());
    //     // die();


    //     $rules = [

    //         'new_head' => 'required',

    //         'head1' => 'required',

    //     ];

    //     $customMessages = [

    //         'required' => 'The :attribute field is required.'

    //     ];

    //     $this->validate($request, $rules, $customMessages);

    //     $head1 = $request->head1;

    //     $head2 = $request->head2;

    //     $head3 = $request->head3;

    //     $head4 = $request->head4;
    //     $selectedHead = $request->selectedOption;

    //     $head_id = AccountHeads::orderBy('head_id', 'desc')->first('head_id');

    //     try {
    //         if ($head2 == NULL && $head3 == NULL && $head4 == NULL && $head1 != NULL) {

    //             $assetData = AccountHeads::select('id', 'labels', 'cr_nature', 'dr_nature')->where('head_id', $head1)->first();

    //             $head_data = array();

    //             // $head_data['head_id'] = $head_id->head_id+1;
    //             if ($head1 != $selectedHead) {
    //                 $head_data['parent_id'] = $head1;


    //             }
    //             if ($request->labels != $assetData->labels) {
    //                 $head_data['labels'] = $assetData->labels + 1;
    //             }
    //             $head_data['sub_head'] = $request->new_head;
    //             $head_data['parentId_auto_id'] = $assetData->id;
    //             $head_data['cr_nature'] = $assetData->cr_nature;
    //             $head_data['dr_nature'] = $assetData->dr_nature;
    //             $head_data['is_move'] = 1;



    //             // $head_data['status'] = 0;
    //             //dd($head_data);
    //             //die();

    //         } elseif ($head1 != NULL && $head2 != NULL && $head3 == NULL && $head4 == NULL) {

    //             $assetData = AccountHeads::select('id', 'labels', 'cr_nature', 'dr_nature')->where('head_id', $head2)->first();


    //             $head_data = array();

    //             //$head_data['head_id'] = $head_id->head_id+1;
    //             if ($request->head2 != $selectedHead) {
    //                 $head_data['parent_id'] = $head2;


    //             }
    //             if ($request->labels != $assetData->labels) {
    //                 $head_data['labels'] = $assetData->labels + 1;
    //             }
    //             $head_data['sub_head'] = $request->new_head;
    //             $head_data['parentId_auto_id'] = $assetData->id;
    //             $head_data['cr_nature'] = $assetData->cr_nature;
    //             $head_data['dr_nature'] = $assetData->dr_nature;
    //             $head_data['is_move'] = 1;
    //             //dd($request->all(),$assetData,$head_data); die();


    //             //$head_data['status'] = 0;
    //             //dd($head_data);
    //             //die();

    //         } elseif ($head1 != NULL && $head2 != NULL && $head3 != NULL && $head4 == NULL) {

    //             $assetData = AccountHeads::select('id', 'labels', 'cr_nature', 'dr_nature')->where('head_id', $head3)->first();

    //             // $head_data['sub_head'] = $request->new_head;

    //             // //$head_data['head_id'] = $head_id->head_id+1;

    //             // $head_data['parent_id'] = $head3;


    //             $head_data = array();

    //             if ($request->head3 != $selectedHead) {
    //                 $head_data['parent_id'] = $head3;


    //             }
    //             if ($request->labels != $assetData->labels) {
    //                 $head_data['labels'] = $assetData->labels + 1;
    //             }
    //             $head_data['sub_head'] = $request->new_head;
    //             $head_data['parentId_auto_id'] = $assetData->id;
    //             $head_data['cr_nature'] = $assetData->cr_nature;
    //             $head_data['dr_nature'] = $assetData->dr_nature;
    //             $head_data['is_move'] = 1;
    //             //dd($request->all(),$assetData,$head_data); die();

    //             //$head_data['status'] = 0;
    //             //dd($head_data);
    //             //die();

    //         } elseif ($head1 != NULL && $head2 != NULL && $head3 != NULL && $head4 != NULL) {
    //             $head_data = array();
    //             $assetData = AccountHeads::select('id', 'labels', 'cr_nature', 'dr_nature')->where('head_id', $head4)->first();

    //             if ($request->head4 != $selectedHead) {
    //                 $head_data['parent_id'] = $head4;


    //             }
    //             if ($request->labels != $assetData->labels) {
    //                 $head_data['labels'] = $assetData->labels + 1;
    //             }
    //             $head_data['sub_head'] = $request->new_head;
    //             $head_data['parentId_auto_id'] = $assetData->id;
    //             $head_data['cr_nature'] = $assetData->cr_nature;
    //             $head_data['dr_nature'] = $assetData->dr_nature;
    //             $head_data['is_move'] = 1;
    //             //dd($request->all(),$assetData,$head_data); die();
    //             //  $head_data['sub_head'] = $request->new_head;

    //             // // $head_data['head_id'] = $head_id->head_id+1;

    //             //  $head_data['parent_id'] = $head4;

    //             // if($request->labels != $assetData->labels){
    //             //        $head_data['labels'] = $assetData->labels+1;
    //             //  }
    //             //

    //             // $head_data['status'] = 0;
    //             //dd($head_data);
    //             //die();

    //         }

    //         // dd(isset($head_data));die();


    //         if ($head_data) {
    //             // AccountHeads::where('id', $request->id)->update($head_data);
    //             $data =AccountHeads::with('parent')->where('id', $request->id)->first();
                
    //             $company = $data->company_id;
    //             head_create_logs($data->head_id, $company, $data->parent_id, $type = 4,$data->sub_head, $data->parent->sub_head,$data1=null,$data2=null,$newdat=null,$request->new_head,$request->create_application_date);
    //             // dd($head_data,$request->id);
    //            AccountHeads::where('id', $request->id)->update($head_data);

    //             $updatedData = AccountHeads::where('id', $request->id)->first();
             
           
               

    
    //             $logDetail = HeadLog::where('head_id', $data->head_id)->latest()->first();
                
                
    //             // Update or create the log entry
    //             if ($logDetail) {
    //                 // $systemdates = Carbon::parse($systemDate)->setTime(now()->hour, now()->minute, now()->second);
    //                 $systemdates = Carbon::createFromFormat('d/m/Y', $request->create_application_date)->format('Y-m-d H:i:s');
                    
    //                 $logDetail->update([
    //                     'new_value' => json_encode($updatedData->toArray()),
    //                     'created_at' =>  $systemdates,
    //                     'updated_at' =>  $systemdates,
    //                 ]);
    //             } else {
    //                 // If the log entry does not exist, create a new one
                    
    //             }



    //         } else {
    //             return redirect()->route('admin.head')->with('success', 'Head Already Exists!!');
    //         }


    //         DB::commit();

    //     } catch (\Exception $ex) {

    //         DB::rollback();

    //         return back()->with('alert', $ex->getMessage());

    //     }

    //     return redirect()->route('admin.head')->with('success', 'Head Update Successfully!');

    // }

    public function update_head(Request $request)
    {


        

        $rules = [

            'new_head' => 'required',

            'head1' => 'required',

        ];

        $customMessages = [

            'required' => 'The :attribute field is required.'

        ];

        $this->validate($request, $rules, $customMessages);

        $head1 = $request->head1;

        $head2 = $request->head2;

        $head3 = $request->head3;

        $head4 = $request->head4;
        $selectedHead = $request->selectedOption;

        $head_id = AccountHeads::orderBy('head_id', 'desc')->first('head_id');

        try {
            if ($head2 == NULL && $head3 == NULL && $head4 == NULL && $head1 != NULL) {

                $assetData = AccountHeads::select('id', 'labels', 'cr_nature', 'dr_nature')->where('head_id', $head1)->first();

                $head_data = array();

                // $head_data['head_id'] = $head_id->head_id+1;
                if ($head1 != $selectedHead) {
                    $head_data['parent_id'] = $head1;


                }
                if ($request->labels != $assetData->labels) {
                    $head_data['labels'] = $assetData->labels + 1;
                }
                $head_data['sub_head'] = $request->new_head;
                $head_data['parentId_auto_id'] = $assetData->id;
                $head_data['cr_nature'] = $assetData->cr_nature;
                $head_data['dr_nature'] = $assetData->dr_nature;
                $head_data['is_move'] = 1;



                // $head_data['status'] = 0;
                //dd($head_data);
                //die();

            } elseif ($head1 != NULL && $head2 != NULL && $head3 == NULL && $head4 == NULL) {

                $assetData = AccountHeads::select('id', 'labels', 'cr_nature', 'dr_nature')->where('head_id', $head2)->first();


                $head_data = array();

                //$head_data['head_id'] = $head_id->head_id+1;
                if ($request->head2 != $selectedHead) {
                    $head_data['parent_id'] = $head2;


                }
                if ($request->labels != $assetData->labels) {
                    $head_data['labels'] = $assetData->labels + 1;
                }
                $head_data['sub_head'] = $request->new_head;
                $head_data['parentId_auto_id'] = $assetData->id;
                $head_data['cr_nature'] = $assetData->cr_nature;
                $head_data['dr_nature'] = $assetData->dr_nature;
                $head_data['is_move'] = 1;
                //dd($request->all(),$assetData,$head_data); die();


                //$head_data['status'] = 0;
                //dd($head_data);
                //die();

            } elseif ($head1 != NULL && $head2 != NULL && $head3 != NULL && $head4 == NULL) {

                $assetData = AccountHeads::select('id', 'labels', 'cr_nature', 'dr_nature')->where('head_id', $head3)->first();

                // $head_data['sub_head'] = $request->new_head;

                // //$head_data['head_id'] = $head_id->head_id+1;

                // $head_data['parent_id'] = $head3;


                $head_data = array();

                if ($request->head3 != $selectedHead) {
                    $head_data['parent_id'] = $head3;


                }
                if ($request->labels != $assetData->labels) {
                    $head_data['labels'] = $assetData->labels + 1;
                }
                $head_data['sub_head'] = $request->new_head;
                $head_data['parentId_auto_id'] = $assetData->id;
                $head_data['cr_nature'] = $assetData->cr_nature;
                $head_data['dr_nature'] = $assetData->dr_nature;
                $head_data['is_move'] = 1;
                //dd($request->all(),$assetData,$head_data); die();

                //$head_data['status'] = 0;
                //dd($head_data);
                //die();

            } elseif ($head1 != NULL && $head2 != NULL && $head3 != NULL && $head4 != NULL) {
                $head_data = array();
                $assetData = AccountHeads::select('id', 'labels', 'cr_nature', 'dr_nature')->where('head_id', $head4)->first();

                if ($request->head4 != $selectedHead) {
                    $head_data['parent_id'] = $head4;


                }
                if ($request->labels != $assetData->labels) {
                    $head_data['labels'] = $assetData->labels + 1;
                }
                $head_data['sub_head'] = $request->new_head;
                $head_data['parentId_auto_id'] = $assetData->id;
                $head_data['cr_nature'] = $assetData->cr_nature;
                $head_data['dr_nature'] = $assetData->dr_nature;
                $head_data['is_move'] = 1;
                //dd($request->all(),$assetData,$head_data); die();
                //  $head_data['sub_head'] = $request->new_head;

                // // $head_data['head_id'] = $head_id->head_id+1;

                //  $head_data['parent_id'] = $head4;

                // if($request->labels != $assetData->labels){
                //        $head_data['labels'] = $assetData->labels+1;
                //  }
                //

                // $head_data['status'] = 0;
                //dd($head_data);
                //die();

            }

            // dd(isset($head_data));die();


            if ($head_data) {

                $data =AccountHeads::with('parent')->where('id', $request->id)->first();
                
                $company = $data->company_id;
                head_create_logs($data->head_id, $company, $data->parent_id, $type = 4,$data->sub_head, $data->parent->sub_head,$data1=null,$data2=null,$newdat=null,$request->new_head,$request->create_application_date);
                // dd($head_data,$request->id);
               AccountHeads::where('id', $request->id)->update($head_data);

                $updatedData = AccountHeads::where('id', $request->id)->first();
             
           
               

    
                $logDetail = HeadLog::where('head_id', $data->head_id)->latest()->first();
                
                
                // Update or create the log entry
                if ($logDetail) {
                    // $systemdates = Carbon::parse($systemDate)->setTime(now()->hour, now()->minute, now()->second);
                    $systemdates = Carbon::createFromFormat('d/m/Y', $request->create_application_date)->format('Y-m-d H:i:s');
                    
                    $logDetail->update([
                        'new_value' => json_encode($updatedData->toArray()),
                        'created_at' =>  $systemdates,
                        'updated_at' =>  $systemdates,
                    ]);
                } else {
                    // If the log entry does not exist, create a new one
                    
                }
      
           
            } else {
                return redirect()->route('admin.head')->with('success', 'Head Already Exists!!');
            }


            DB::commit();

        } catch (\Exception $ex) {

            DB::rollback();

            return back()->with('alert', $ex->getMessage());

        }

        return redirect()->route('admin.head')->with('success', 'Head Update Successfully!');

    }

    public function datarecord($parentid, &$data = [])
    {
        $head = AccountHeads::where('head_id', $parentid)->first();
    
        if ($head) {
            $data[] = $head;
            $this->datarecord($head->parent_id, $data);
        }
    
        return $data;
    }



    public function updateStatus(Request $request)
    {



        $headStatus = AccountHeads::select('status')->where('id', $request->id)->first();



        $updateStatus = AccountHeads::findOrFail($request->id);

        if ($headStatus->status == 0) {

            $updateStatus->status = 1;

        } else {

            $updateStatus->status = 0;

        }



        $updateStatus = $updateStatus->save();



        return response()->json($updateStatus);

    }


    public function deleteHead($id, $label)
    {
        if (check_my_permission(Auth::user()->id, "147") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $record = AccountHeads::find($id);
        $child = AccountHeads::where('parent_id', $record->id)->exists();

        if ($child) {
            return redirect()->back()->with('alert', 'Please Delete Child Head First!');
        } else {
            $deleteRecord = $record->delete();
            return redirect()->back()->with('success', 'Deleted SuccessFully!');
        }



    }

    public function headlogs(){
        
        $data['title'] = "Account Head Management | Logs";
        return view('templates.admin.account_head_report.head.logs',$data);
        
    }

    public function headlogListing(Request $request){
        $data['title'] = "Account Head Management | Logs";
        
        if ($request->ajax()) {
            foreach ($request->searchBranchToHo as $frm_data) {
                $arrFormData[$frm_data['name']] = $frm_data['value'];
            }
    
            $companyId = '';
            $status = 0;
            $getBranchId = getUserBranchId(Auth::user()->id);
    
            // if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
            //     if ($arrFormData['status'] != '') {
            //         $status = $arrFormData['status'];
            //     }
            // }
    
            if ($status == 0) {
                $query = HeadLog::with('parent')->whereIn('type', [1, 2, 3,4]);
            } else {
                $query = HeadLog::with('parent')->where('type', $status);
            }
    
            if ($arrFormData['start_date'] != '') {
                $startDate = $arrFormData['start_date'];
                $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
    
                if ($arrFormData['end_date'] != '') {
                    $endDate = $arrFormData['end_date'];
                    $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
                } else {
                    $endDate = '';
                }
    
                $query = $query->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
    
        
            $headlogs = $query->orderBy('id', 'desc')->get();
         
            $count = $headlogs->count('id');
            // $cache_data = $headlogs->orderby('created_at', 'ASC')->get();
            $token = session()->get('_token');
            Cache::put('headLogData' . $token, $headlogs);
            Cache::put('headLogDataLogData_count' . $token, $count);

            $shortNames = [];
    
            foreach ($headlogs as $log) {
                
                $companyIds = json_decode($log->company_id,true);
       

                $companies = Companies::whereIn('id', $companyIds)->pluck('short_name')->toArray();
                $shortNames[$log->id] = implode(', ', $companies);
            }
    
            return Datatables::of($headlogs)
            ->addColumn('parent_id', function ($row) {
                return $row->parent->sub_head;
            })
            ->rawColumns(['head_id'])
            ->addColumn('head_id', function ($row) {
                $oldValueData = json_decode($row->old_value);
                $subHead = isset($oldValueData->sub_head) ? $oldValueData->sub_head : '';
                return $subHead;
            })
            ->rawColumns(['type'])
            ->addColumn('type', function ($row) {
                return $row->type == 1 ? 'Create' : ($row->type == 2 ? 'Assign' : ($row->type == 3 ? 'Grouping' : ($row->type == 4 ? 'Edit' : 'Unknown')));
            })
            ->addColumn('company_id', function ($row) use ($shortNames) {
                return $shortNames[$row->id] ?? '';
            })
            ->rawColumns(['branch'])
            ->addColumn('created_at', function ($row) {
                return date("d/m/Y H:i:s", strtotime(convertDate($row->created_at)));
            })
            ->rawColumns(['created_at'])
            ->addColumn('action', function ($row) {
                if ($row->type == 3 || $row->type == 4) {
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $url3 = URL::to("admin/head/grouping/logs/" . $row->id . "");
                    $btn .= '<a class="dropdown-item" href="' . $url3 . '" title="Saving Account"><i class="fa fa-eye" aria-hidden="true"></i>
                            Show Details </a>  ';
                    return $btn;
                } else {
                    // Handle other cases if needed
                }
            })
            ->rawColumns(['action'])
            ->make(true);
        
        } else {
            // Handle non-AJAX request if needed
        }
        // Additional code for non-AJAX request if needed
        // return view('templates.admin.account_head_report.head.logs',compact('headlogs'),$data);
    }
    

    public function headGroupingLogsDetail($id){

    $headDetails = HeadLog::with('parent') // Assuming 'parent' is a relationship method in the HeadLog model
    ->find($id);



        $data['title'] = "Account Head Management | Logs";

        $companyIds = json_decode($headDetails->company_id);

        $mainCompanies = Companies::select('short_name')->whereIn('id', $companyIds)->get();

        $shortNames = $mainCompanies->pluck('short_name')->toArray();

 
        $details = json_decode($headDetails->old_value, true);
        $newDetails = json_decode($headDetails->new_value, true);


        $newParentHeadName = AccountHeads::select('sub_head')->where('id',$newDetails['parent_id'])->first();
        $parentHeadName = AccountHeads::select('sub_head')->where('id',$details['parent_id'])->first();

        return view('templates.admin.account_head_report.head.grouplogsDetails', compact('details', 'newDetails', 'headDetails', 'shortNames','newParentHeadName','parentHeadName'), $data);

    }

}