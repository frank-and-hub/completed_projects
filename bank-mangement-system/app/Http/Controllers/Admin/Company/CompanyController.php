<?php

namespace App\Http\Controllers\Admin\Company;

use App\Interfaces\RepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{User, Branch, FaCode, CompanyAssociate, AccountHeads, SystemDefaultSettings};
use App\Http\Traits\companyFormValidation;
use App\Http\Requests\CompanyRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DB;
use App\Services\ImageUpload;
use App\Http\Controllers\Admin\Company\store;
// use illuminate\Database\Eloquent\SoftDeletes;
class CompanyController extends Controller
{
    private $repository;
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    use companyFormValidation;
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "328") != "1"){
            return redirect()
            ->route('admin.dashboard')
            ->with('alert', "you do not have permission");
        }
        $data['title'] = "Company | New Company Register";
        $select = ['id','parent_id','head_id','sub_head'];
        $data['account_head'] = $this->repository->getAllAccountHeads()
            ->with(
                [
                    'subcategory' => function ($q) use($select){
                        $q
                            ->whereLabels('2')
                            ->whereBasic_heads('1')
                            ->with(
                                [
                                    'subcategory' => function ($q) use($select){
                                        $q
                                            ->whereLabels('3')
                                            ->whereBasic_heads('1')
                                            ->with(
                                                [
                                                    'subcategory' => function ($q) use($select){
                                                        $q
                                                            ->whereLabels('4')
                                                            ->whereBasic_heads('1')
                                                            ->with(
                                                                [
                                                                    'subcategory' => function ($q) use($select){
                                                                        $q
                                                                            ->whereLabels('5')
                                                                            ->whereBasic_heads('1')
                                                                            ->with(
                                                                                [
                                                                                    'subcategory' => function ($q) use($select){
                                                                                        $q
                                                                                            ->whereLabels('6')
                                                                                            ->whereBasic_heads('1')
                                                                                            ->select(
                                                                                                $select
                                                                                            )->get();
                                                                                    }
                                                                                ]
                                                                            )
                                                                            ->select(
                                                                                $select
                                                                            )->get();
                                                                    }
                                                                ]
                                                            )
                                                            ->select(
                                                                $select
                                                            )->get();
                                                    }
                                                ]
                                            )
                                            ->select(
                                                $select
                                            )->get();
                                    }
                                ]
                            )
                            ->select(
                                $select
                            )->get();
                    }
                ]
            )->whereLabels('1')
            ->whereBasic_heads('1')
            ->get(
                ['id', 'head_id', 'sub_head']
            )
            ;
        $data['branch'] = Branch::get(['name', 'branch_code', 'id']);
// pd($data['account_head']);
        $data['company'] = $this->repository->NewCompanies();
        $data['default_settings'] = $this->repository->getNewSystemDefaultSettings();
        return view('templates.admin.company.index', $data);
    }
    public function show()
    {
        if (check_my_permission(Auth::user()->id, "324") != "1"){
            return redirect()
            ->route('admin.dashboard')
            ->with('alert', "you do not have permission");
        }
        $data['title'] = "Company | All Company List";
        return view('templates.admin.company.companies_list', $data);
    }
    public function edit($id)
    {
        if (check_my_permission(Auth::user()->id, "326") != "1"){
            return redirect()
            ->route('admin.dashboard')
            ->with('alert', "you do not have permission");
        }
        $select = ['id','parent_id','head_id','sub_head'];
        $data['company'] = $this->repository->getCompaniesById($id)->whereStatus('1')->first();
        if (!$data['company']) {
            return redirect()->back()->with('error', 'Active Company not found !');
        }
        // $data['account_head'] = $this->repository->getAllAccountHeads()->get(['sub_head','id','head_id']);
        $data['account_head'] = $this->repository->getAllAccountHeads()
            ->with(
                [
                    'subcategory' => function ($q) use($select){
                        $q
                            ->whereLabels('2')
                            ->whereBasic_heads('1')
                            ->with(
                                [
                                    'subcategory' => function ($q) use($select){
                                        $q
                                            ->whereLabels('3')
                                            ->whereBasic_heads('1')
                                            ->with(
                                                [
                                                    'subcategory' => function ($q) use($select){
                                                        $q
                                                            ->whereLabels('4')
                                                            ->whereBasic_heads('1')
                                                            ->with(
                                                                [
                                                                    'subcategory' => function ($q) use($select){
                                                                        $q
                                                                            ->whereLabels('5')
                                                                            ->whereBasic_heads('1')
                                                                            ->with(
                                                                                [
                                                                                    'subcategory' => function ($q) use($select){
                                                                                        $q
                                                                                            ->whereLabels('6')
                                                                                            ->whereBasic_heads('1')
                                                                                            ->select(
                                                                                                $select
                                                                                            )->get();
                                                                                    }
                                                                                ]
                                                                            )
                                                                            ->select(
                                                                                $select
                                                                            )->get();
                                                                    }
                                                                ]
                                                            )
                                                            ->select(
                                                                $select
                                                            )->get();
                                                    }
                                                ]
                                            )
                                            ->select(
                                                $select
                                            )->get();
                                    }
                                ]
                            )
                            ->select(
                                $select
                            )->get();
                    }
                ]
            )->whereLabels('1')
            ->whereBasic_heads('1')
            ->get(
                ['id', 'head_id', 'sub_head']
            )
            ;
        $data['account_head_up'] = $this->repository->getAllAccountHeads()
            ->with(
                [
                    'subcategory' => function ($q) use($select){
                        $q
                            ->whereLabels('2')
                            ->whereBasic_heads('1')
                            ->with(
                                [
                                    'subcategory' => function ($q) use($select){
                                        $q
                                            ->whereLabels('3')
                                            ->whereBasic_heads('1')
                                            ->with(
                                                [
                                                    'subcategory' => function ($q) use($select){
                                                        $q
                                                            ->whereLabels('4')
                                                            ->whereBasic_heads('1')
                                                            ->with(
                                                                [
                                                                    'subcategory' => function ($q) use($select){
                                                                        $q
                                                                            ->whereLabels('5')
                                                                            ->whereBasic_heads('1')
                                                                            ->with(
                                                                                [
                                                                                    'subcategory' => function ($q) use($select){
                                                                                        $q
                                                                                            ->whereLabels('6')
                                                                                            ->whereBasic_heads('1')
                                                                                            ->select(
                                                                                                $select
                                                                                            )->get();
                                                                                    }
                                                                                ]
                                                                            )
                                                                            ->select(
                                                                                $select
                                                                            )->get();
                                                                    }
                                                                ]
                                                            )
                                                            ->select(
                                                                $select
                                                            )->get();
                                                    }
                                                ]
                                            )
                                            ->select(
                                                $select
                                            )->get();
                                    }
                                ]
                            )
                            ->select(
                                $select
                            )->get();
                    }
                ]
            )
            ->where('company_id', 'Like', '%' . $id . '%')
            ->whereLabels('1')
            ->whereBasic_heads('1')
            ->get(
                ['id', 'head_id', 'sub_head']
            );
        // $data['account_head_up_opposite'] = $this->repository->getAllAccountHeads()->where('company_id','Not Like','%'.$id.'%')->get(['sub_head','id','head_id']);
        $data['account_head_up_opposite'] = $this->repository->getAllAccountHeads()
            ->with(
                [
                    'subcategory' => function ($q) use($select){
                        $q
                            ->whereLabels('2')
                            ->whereBasic_heads('1')
                            ->with(
                                [
                                    'subcategory' => function ($q) use($select){
                                        $q
                                            ->whereLabels('3')
                                            ->whereBasic_heads('1')
                                            ->with(
                                                [
                                                    'subcategory' => function ($q) use($select){
                                                        $q
                                                            ->whereLabels('4')
                                                            ->whereBasic_heads('1')
                                                            ->with(
                                                                [
                                                                    'subcategory' => function ($q) use($select){
                                                                        $q
                                                                            ->whereLabels('5')
                                                                            ->whereBasic_heads('1')
                                                                            ->with(
                                                                                [
                                                                                    'subcategory' => function ($q) use($select){
                                                                                        $q
                                                                                            ->whereLabels('6')
                                                                                            ->whereBasic_heads('1')
                                                                                            ->select(
                                                                                                $select
                                                                                            )->get();
                                                                                    }
                                                                                ]
                                                                            )
                                                                            ->select(
                                                                                $select
                                                                            )->get();
                                                                    }
                                                                ]
                                                            )
                                                            ->select(
                                                                $select
                                                            )->get();
                                                    }
                                                ]
                                            )
                                            ->select(
                                                $select
                                            )->get();
                                    }
                                ]
                            )
                            ->select(
                                $select
                            )->get();
                    }
                ]
            )
            ->where('company_id', 'Like', '%' . $id . '%')
            ->whereLabels('1')
            ->whereBasic_heads('1')
            ->get(
                ['id', 'head_id', 'sub_head']
            );
        $data['branch'] = Branch::get(['name', 'branch_code', 'id']);
        $data['companybranch'] = \App\Models\CompanyBranch::whereCompanyId($id)->whereStatus('1')->with(['branch:id,name,branch_code','company:id,name'])->get();
        // $data['companybranch_not'] = \App\Models\CompanyBranch::whereCompanyId($id)->whereStatus('1')->with(['branch:id,name,branch_code','company:id,name'])->dd();
        
        $data['companybranch_not'] = \App\Models\CompanyBranch::whereDoesntHave('company', function ($query) use ($id) {
            $query->where('id', $id);
        })
        ->whereStatus('1')
        ->with(['branch:id,name,branch_code', 'company:id,name'])
        ->get();
        
        $data['fa_code'] = FaCode::where('company_id', $id)->get(['company_id', 'id', 'code']);
        $data['title'] = "Company | Edit Company Details";
        $data['default_settings'] = $this->repository->getAllSystemDefaultSettings()->where('company_id', $id)->get(['id', 'name', 'short_name', 'effective_from', 'amount']);
        return view('templates.admin.company.index', $data);
    }
    public function listing(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->repository->getAllCompanies();
            $count = $data->count("id");
            $totalCount = $count;
            // $data = $data->orderby('created_at','desc')->offset($_POST['start'])->limit($_POST['length'])->get(['id','name','short_name','mobile_no','fa_code_from','fa_code_to','tin_no','pan_no','cin_no','created_by','created_at','status']);
            $data = $data->latest()->skip($_POST['start'])->take($_POST['length'])->get();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $user = $this->repository->getUserById($row->created_by_id)->select('name', 'id')->first();
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['id'] = $row->id;
                $val['name'] = $row->name;
                $val['short_name'] = $row->short_name;
                $val['mobile_no'] = $row->mobile_no;
                $val['fa_code_from'] = $row->fa_code_from;
                $val['fa_code_to'] = $row->fa_code_to;
                $val['tin_no'] = $row->tin_no;
                $val['pan_no'] = $row->pan_no;
                $val['cin_no'] = $row->cin_no;
                $val['created_by'] = $row->created_by == 1 ? 'Admin' : 'Branch';
                $val['created_at'] = date("d/m/Y H:i:s", strtotime($row->created_at));
                $status = '';
                if ($row->status == '1') {
                    $status = 'Active';
                } else {
                    $status = 'Inactive';
                }
                $val['status'] = $status;
                $btn = '';
                $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown" aria-expanded="false"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(22px, 20px, 0px);">';
                $btn .= '<div class="dropdown-item companyid" data-companyid="' . $row->id . '"><i class="far fa-thumbs-up mr-2"></i>Status</div>';
                if(check_my_permission(Auth::user()->id, "326") == "1"){
                    $btn .= '<a class="dropdown-item" href="'.route('admin.companies.edit',$row->id).'"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                }
                if(check_my_permission(Auth::user()->id, "327") == "1"){
                    $btn .= '<a class="dropdown-item" href="' . route('admin.companies.view', $row->id) . '"><i class="icon-eye mr-2"></i>View</a>';
                }                
                $btn .= '</div></div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            // dd($output);
            return json_encode($output);
        }
    }
    public function save(Request $request)
    {
        //
    }
    public function companyRegisterForm(Request $request)
    {      
        if ($request->ajax()) {
            $request->validate([
                'name' => 'required',
                'fa_code_from' => 'required',
                'company_image' => 'required|image|mimes:jpeg,png,jpg,svg',
                'fa_code_to' => 'required',
                'tin_no' => 'required',
                'pan_no' => 'required',
                'cin_no' => 'required',
                'mobile_no' => 'required',
                'short_name' => 'required',
                'email' => 'required',
                'address' => 'required',
            ]);
            if (Auth::user()->id == $request->user_id) {
                // $user = User::where('status','0')->where('id',$request->user_id)->first();
                // $imgChk=0;  
                // $company_filename='';     
                // if ($request->hasFile('company_image')) {
                //     $company_image = $request->file('company_image');
                //     $company_filename = time().'.'.date("d-m-Y").'.'.$request->company_image->getClientOriginalExtension();
                //     $company_location = 'asset/company/' . $company_filename;
                //     Image::make($company_image)->resize(300,300)->save($company_location);
                //     $imgChk++;
                // }
                // if($imgChk>0){
                    $input = [];
                    $input['name'] = $request->name;                    
                    $input['short_name'] = $request->short_name;
                    $input['mobile_no'] = $request->mobile_no;
                    $input['email'] = $request->email;
                    $input['fa_code_from'] = $request->fa_code_from;
                    $input['fa_code_to'] = $request->fa_code_to;
                    $input['address'] = $request->address;
                    $input['tin_no'] = $request->tin_no;
                    $input['pan_no'] = $request->pan_no;
                    $input['cin_no'] = $request->cin_no;
                    $input['status'] = 1;
                    $input['count'] = 4;
                    $input['last_fa_code'] = $request->fa_code_from + 4;
                    $input['created_by'] = 1;
                    $input['created_by_id'] = Auth::user()->id;

                    if (!empty($_FILES)) {
                        if ($_FILES['company_image']['name'] != "") {
                            $companyImageName = date('Y_m_d_H_i_s_a') . '_' . time() . '.' . request()->company_image->getClientOriginalExtension();
                            $companyImage = request()->company_image;
                            $companyImagePath = 'company';
                            ImageUpload::upload($companyImage, $companyImagePath,$companyImageName);
                            $input['image'] = $companyImageName;
                        } else {
                            $input['image'] = $request->hidden_image;
                        }
                    } else {
                        $input['image'] = $request->hidden_image;
                    }
                    // dd($input,$request->all());
                    $Company = $this->repository->createCompanies($input);
                    if ($Company) {
                        $response = ['data' => $Company->id];
                    } else {
                        $response = ['data' => '0'];
                    }
                    return json_encode($response);
                // }
            } else {
                $response = ['data' => '0'];
                return json_encode($response);
            }
        }
    }
    public function companyAccountHead(Request $request)
    {
        if ($request->ajax()) {
            $account_head = $request->account_head;
            $company = $this->repository->getAllCompanies()->latest()->first();
            $company_id = $company->id;
            $input = [];
            foreach ($account_head as $val) {
                // print_r($val); echo"<pre>";
                $AccountHead = AccountHeads::where('head_id', $val)->whereBasic_heads('1')->first();
                $AccountHead_company = $AccountHead->company_id;
                if ($AccountHead_company == NULL) {
                    $AccountHead_company = [];
                } else {
                    $AccountHead_company = json_decode($AccountHead_company);
                }
                array_push($AccountHead_company, $company_id);
                $input = json_encode($AccountHead_company);
                $AccountHead->update(['company_id' => $input]);
            }
            if ($AccountHead) {
                $response = [
                    'data' => $company_id,
                ];
                return json_encode($response);
            } else {
                $response = [
                    'data' => ['0'],
                ];
                return json_encode($response);
            }
        }
    }
    public function companyFaCode(Request $request)
    {
        if ($request->ajax()) {
            $company = $this->repository->getAllCompanies()->latest()->first();
            $company_id = $company->id;
            $fa_code = [
                'name' => ['PASSBOOK','MEMBER ID', 'ASSOCIATE CODE', 'SAVING ACCOUNT', 'CERTIFICATE'],
                'code' => [$request->fa_code, $request->fa_code + 1, $request->fa_code + 2, $request->fa_code + 3, $request->fa_code + 4]
            ];
            $data = [];
            $names = ['PASSBOOK','MEMBER ID', 'ASSOCIATE CODE', 'SSB', 'CERTIFICATE'];
            foreach ($names as $key => $name) {
                $fa_code = new FaCode;
                $fa_code->name = $name;
                $fa_code->code = $request->fa_code + $key;
                $fa_code->status = '1';
                $fa_code->is_deleted = '0';
                $fa_code->company_id = $company_id;
                $fa_code->slug = str_replace(' ', '_', strtolower($name));
                $data[] = $fa_code->toArray();
            }
            $faCode = FaCode::insert($data);
            if ($faCode) {
                return json_encode(['data' => $company_id]);
            } else {
                return json_encode(['data' => '0']);
            }
        }
    }
    public function companyBranch(Request $request)
    {
        if ($request->ajax()) {
            $branch = $request->branch;
            $company = $this->repository->getAllCompanies()->latest()->first('id');
            $company_id = $company->id;
            $input = [];
            foreach ($branch as $val) {
                $input[] = [
                    'branch_id' => $val,
                    'company_id' => $company_id,
                    'status' => '1',
                    'is_primary' => $request->p_branch == $val ? '1' : '0',
                    'is_new_business' => '1',
                    'is_old_business' => '1',
                    'created_by' => '1',
                    'created_by_id' => Auth::user()->id
                ];
            }
            $Branch = $this->repository->insertCompanyBranch($input);
            // $Branch->upddate();
            if ($Branch) {
                $response = [
                    'data' => $company_id,
                ];
                return json_encode($response);
            } else {
                $response = [
                    'data' => ['0'],
                ];
                return json_encode($response);
            }
        }
    }
    public function companyRegisterForm_update(Request $request)
    {
        if ($request->ajax()) {
            $request->validate([
                'name' => 'required',
                'fa_code_from' => 'required',
                'fa_code_to' => 'required',
                'tin_no' => 'required',
                'pan_no' => 'required',
                'cin_no' => 'required',
                'mobile_no' => 'required',
                'short_name' => 'required',
                'email' => 'required',
                'address' => 'required',
                'company_status' => 'required',
            ]);
            if (Auth::user()->id == $request->user_id) {
                $company = $this->repository->getCompaniesById($request->company_id);
                $input = [];
                $input['name'] = $request->name;
                $input['short_name'] = $request->short_name;
                $input['mobile_no'] = $request->mobile_no;
                $input['email'] = $request->email;
                $input['fa_code_from'] = $request->fa_code_from;
                $input['fa_code_to'] = $request->fa_code_to;
                $input['address'] = $request->address;
                $input['tin_no'] = $request->tin_no;
                $input['pan_no'] = $request->pan_no;
                $input['cin_no'] = $request->cin_no;
                $input['status'] = $request->company_status;
                $input['created_by'] = 1;
                $input['created_by_id'] = Auth::user()->id;
                if (!empty($_FILES)) {
                    if ($_FILES['company_image']['name'] != "") {
                        $companyImageName = date('Y_m_d_H_i_s_a') . '_' . time() . '.' . request()->company_image->getClientOriginalExtension();
                        $companyImage = request()->company_image;
                        $companyImagePath = 'company';
                        ImageUpload::upload($companyImage, $companyImagePath,$companyImageName);
                        $input['image'] = $companyImageName;
                    } else {
                        $input['image'] = $request->hidden_image;
                    }
                } else {
                    $input['image'] = $request->hidden_image;
                }
                $Company = $company->update($input);
                if ($Company) {
                    $response = ['data' => $request->company_id];
                    return json_encode($response);
                }
            } else {
                $response = [
                    'data' => ['0'],
                ];
                return json_encode($response);
            }
        }
    }
    public function companyAccountHead_update(Request $request)
    {
        if ($request->ajax()) {
            $account_head = $request->account_head_up;
            $company_id = $request->company_id_ah;
            $datarr = AccountHeads::whereJsonContains('company_id', (int) $request->company_id_ah)->get();
            $re = AccountHeads::get()->groupBy('head_id');
            foreach ($account_head as $val) {
                $AccountHead = $re[$val]['0'];
                $AccountHead_company = $AccountHead->company_id;
                if ($AccountHead_company == null) {
                    $AccountHead_company = [];
                } else {
                    $AccountHead_company = json_decode($AccountHead_company);
                }
                array_push($AccountHead_company, $company_id);
                $AccountHead->update(['company_id' => json_encode(array_unique($AccountHead_company))]);
            }
            if ($AccountHead) {
                $response = [
                    'data' => $company_id,
                ];
                return json_encode($response);
            } else {
                $response = [
                    'data' => ['0'],
                ];
                return json_encode($response);
            }
        }
    }
    public function companyFaCode_update(Request $request)
    {
        if ($request->ajax()) {
            $company_id = $request->company_id_fa;
            $company = $this->repository->getCompaniesById($company_id);
            $FaCode = FaCode::where('company_id', $company_id)->get()->toArray();
            foreach ($FaCode as $key => $val) {
                $code = $request->fa_code + $key;
                FaCode::whereId($val['id'])->update(['code' => $code]);
            }
            // $company->update(['passbook_fa_code'=>$request->passbook_fa_code,'certificate_fa_code'=>$request->certificate_fa_code]);
            if ($FaCode) {
                $response = [
                    'data' => $company_id,
                ];
                return json_encode($response);
            } else {
                $response = ['data' => ['0']];
                return json_encode($response);
            }
        }
    }
    public function companyBranch_update(Request $request)
    {
        if ($request->ajax()) {
            $branch = $request->branch;
            $company = $this->repository->getAllCompanies()->latest()->first();
            $company_id = $company->id;
            $input = [];
            foreach ($branch as $val) {
                $Branch = Branch::where('id', $val)->first();
                $Branch_company = $Branch->company_id;
                if ($Branch_company == null) {
                    $Branch_company = [];
                } else {
                    $Branch_company = explode(',', $Branch_company);
                }
                array_push($Branch_company, $company_id);
                $input['company_id'] = implode(',', $Branch_company);
                $Branch->update($input);
            }
            if ($Branch) {
                $response = [
                    'data' => $company_id,
                ];
                return json_encode($response);
            } else {
                $response = [
                    'data' => ['0'],
                ];
                return json_encode($response);
            }
        }
    }
    public function status(Request $request)
    {
        if ($request->ajax()) {
            DB::beginTransaction();
            try {
                $company = $this->repository->getCompaniesById($request->id)->first();
                $status = ['status' => ($company->status == 1) ? '0' : '1'];
                $this->repository->getAllCompanyBranch()/*->where('is_primary', '!=', '1')*/->where('company_id', $request->id)->update($status);
                $company_id = $company->id;
                $companyList = $request->id;
                $arrayCompanyList = explode(' ', $companyList);
                $companyList = array_map(function ($value) {
                    return intval($value);
                }, $arrayCompanyList);
                $accountHead = 'A';
                if ($company->status == 1) {
                    $accountHeads = $this->repository->getAllAccountHeads()->when($company_id, function ($q) use ($companyList) {
                        $q->getCompanyRecords("CompanyId", $companyList);
                    })->pluck('head_id');
                    $status['account_heads'] = json_encode($accountHeads);
                    foreach ($accountHeads as $key => $val) {
                        $AccountHeadCompany = [];
                        $AccountHead = $this->repository->getAllAccountHeads()->where('head_id', $val)->first('company_id');
                        if ($AccountHead) {
                            $accounthead_company = json_decode($AccountHead->company_id);
                            $AccountHeadCompany = array_filter($accounthead_company, function ($v) use ($company_id) {
                                return $v !== $company_id;
                            });
                            $reindexedArray = array_values($AccountHeadCompany);
                            $this->repository->getAllAccountHeads()->where('head_id', $val)->update(['company_id' => json_encode($reindexedArray)]);
                        }
                    }
                } else {
                    $accountHead = 'B';
                    $status['account_heads'] = NULL;
                    $account_head = json_decode($company->account_heads);
                    // $input = [];
                    $re = $this->repository->getAllAccountHeads()->get()->groupBy('head_id');
                    foreach ($account_head as $val) {
                        $AccountHead = $re[$val]['0'];
                        $AccountHead_company = $AccountHead->company_id;
                        if ($AccountHead_company == null) {
                            $AccountHead_company = [];
                        } else {
                            $AccountHead_company = json_decode($AccountHead_company);
                        }
                        array_push($AccountHead_company, $company_id);
                        $AccountHead->update(['company_id' => json_encode(array_unique($AccountHead_company))]);
                    }
                }
                $company->update($status);
                DB::commit();
                $response = [
                    'data' => '1',
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                $response = [
                    'data' => '0',
                    'error' => $e->getMessage(),
                ];
            }
            return response()->json($response);
        }
    }
    public function view($id)
    {
        if (check_my_permission(Auth::user()->id, "327") != "1"){
            return redirect()
            ->route('admin.dashboard')
            ->with('alert', "you do not have permission");
        }
        $data['company'] = $this->repository->getCompaniesById($id)->whereStatus('1')->first();
        if (!$data['company']) {
            return redirect()->back()->with('error', 'Active Company not found !');
        }
        $data['company'] = $this->repository->getCompaniesById($id)->first();
        $data['account_head'] = $this->repository->getAllAccountHeads()->with([
            'subcategory' => function ($query) {
                $query->whereLabels('2')->whereBasic_heads('1')->with([
                    'subcategory' => function ($que) {
                        $que->whereLabels('3')->whereBasic_heads('1')->with([
                            'subcategory' => function ($qu) {
                                $qu->whereLabels('4')->whereBasic_heads('1')->with([
                                    'subcategory' => function ($q) {
                                        $q->whereLabels('5')->whereBasic_heads('1')->with([
                                            'subcategory' => function ($q) {
                                                $q->whereLabels('6')->whereBasic_heads('1')->select('id', 'parent_id', 'head_id', 'sub_head')->get();
                                            }
                                        ])->select('id', 'parent_id', 'head_id', 'sub_head')->get();
                                    }
                                ])->select('id', 'parent_id', 'head_id', 'sub_head')->get();
                            }
                        ])->select('id', 'parent_id', 'head_id', 'sub_head')->get();
                    }
                ])->select('id', 'parent_id', 'head_id', 'sub_head')->get();
            }
        ])->whereLabels('1')->whereBasic_heads('1')->get(['id', 'head_id', 'sub_head', 'company_id', 'basic_heads']);
        $data['account_head_up'] = $this->repository->getAllAccountHeads()->where('company_id', 'Like', '%' . $id . '%')->with([
            'subcategory' => function ($query) {
                $query->whereLabels('2')->whereBasic_heads('1')->with([
                    'subcategory' => function ($que) {
                        $que->whereLabels('3')->whereBasic_heads('1')->with([
                            'subcategory' => function ($qu) {
                                $qu->whereLabels('4')->whereBasic_heads('1')->with([
                                    'subcategory' => function ($q) {
                                        $q->whereLabels('5')->whereBasic_heads('1')->with([
                                            'subcategory' => function ($q) {
                                                $q->whereLabels('6')->whereBasic_heads('1')->select('id', 'parent_id', 'head_id', 'sub_head')->get();
                                            }
                                        ])->select('id', 'parent_id', 'head_id', 'sub_head')->get();
                                    }
                                ])->select('id', 'parent_id', 'head_id', 'sub_head')->get();
                            }
                        ])->select('id', 'parent_id', 'head_id', 'sub_head')->get();
                    }
                ])->select('id', 'parent_id', 'head_id', 'sub_head')->get();
            }
        ])->whereLabels('1')->whereBasic_heads('1')->get(['id', 'head_id', 'sub_head', 'company_id', 'basic_heads']);
        // $data['account_head_up_opposite'] = $this->repository->getAllAccountHeads()->where('company_id', 'Not Like', '%' . $id . '%')->get(['sub_head','id','head_id','company_id']);
        $data['account_head_up_opposite'] = $this->repository->getAllAccountHeads()->with('subcategory')->select('sub_head', 'id', 'head_id', 'company_id', 'basic_heads')->whereBasic_heads('1')->where('company_id', 'Not Like', '%' . $id . '%')->get();
        $data['branch'] = Branch::get(['name', 'branch_code', 'id']);

        $data['companybranch'] = Branch::has('companybranchs')->with([
            'companybranchs' => function ($q) use ($id) {
                $q->where('company_id', $id)->select(['company_id', 'branch_id']);
            }
        ])->get(['name', 'branch_code', 'id']);
        $data['selectedIsPrimary'] = $this->repository->getAllCompanyBranch()->whereCompanyId($id)->whereIsPrimary('1')->first(['id', 'is_primary', 'branch_id', 'company_id']);

        $data['companybranch_not'] = Branch::whereDoesntHave('companybranchs', function ($q) use ($id) {
            $q->where('company_id', $id);
        })->get(['name', 'branch_code', 'id']);

        $data['fa_code'] = FaCode::where('company_id', $id)->orderBy('code', 'asc')->get(['company_id', 'id', 'code']);
        $data['title'] = "Company | View Company Details";
        $data['default_settings'] = $this->repository->getAllSystemDefaultSettings()->where('company_id', $id)->get(['id', 'name', 'short_name', 'effective_from', 'amount']);
        return view('templates.admin.company.index', $data);
    }
    public function associateSetting()
    {
        if (check_my_permission(Auth::user()->id, "325") != "1"){
            return redirect()
            ->route('admin.dashboard')
            ->with('alert', "you do not have permission");
        }
        $data['title'] = "Company | Associate Setting";
        $data['company_associate'] = CompanyAssociate::select('id', 'company_id')->where('status', '1')->first();
        // $data['company_id'] = '';
        return view('templates.admin.company.associate_setting.associate_setting', $data);
    }
    public function associate_store(Request $request)
    {
        if ($request->ajax()) {
            DB::beginTransaction();
            try {
                $CompanyAssociate = CompanyAssociate::where('company_id', $request->company_id)->orderBy('created_at', 'desc')->first();
                $response = [];
                if ($CompanyAssociate != null) {
                    $associate = CompanyAssociate::all();
                    foreach ($associate as $key => $val) {
                        $Cassociate = CompanyAssociate::find($val['id']);
                        $Cassociate->update(['status' => '0']);
                    }
                    $data = [
                        'user_name' => Auth::user()->username,
                        'user_id' => Auth::user()->id,
                        'company_id' => $request->company_id,
                        'status' => '1',
                        'created_by' => 1,
                        'created_by_id' => Auth::user()->id,
                        ];
                    $CompanyAssociate->insert($data);
                    // $CompanyAssociate->update(['status' => '1']);
                    $response = [
                        'message' => 'change',
                        'status' => '',
                    ];
                } else {
                    $associate = CompanyAssociate::all();
                    foreach ($associate as $key => $val) {
                        $Cassociate = CompanyAssociate::find($val['id']);
                        $Cassociate->update(['status' => '0']);
                    }
                    $associate = new CompanyAssociate;
                    $associate->company_id = $request->company_id;
                    $associate->created_by_id = Auth::user()->id;
                    $associate->user_id = Auth::user()->id;
                    $associate->user_name = Auth::user()->username;
                    $associate->status = '1';
                    $associate->save();
                    if ($associate) {
                        $response = [
                            'message' => 'create',
                            'status' => '1',
                        ];
                    } else {
                        $response = [
                            'message' => 'error',
                            'status' => '0',
                        ];
                    }
                }
                DB::commit();
                return response()->json($response);
            } catch (\Exception $ex) {
                DB::rollback();
                return back()->with('alert', $ex->getMessage());
            }
        }
    }
    public function associate_update(Request $request)
    {
        if ($request->ajax()) {
            $company_id = $request->data;
            $CompanyAssociate = CompanyAssociate::where('company_id', $company_id)->orderBy('created_at', 'desc')->first();
            dd($CompanyAssociate->id);
        }
    }
    public function checkUnique($request)
    {
        $details = $this->CompanyFormValidation('companies', $request);
        $response = [
            'data' => $details > 0 ? '0' : '1',
        ];
        return response()->json($response);
    }
    public function name_unique(Request $request)
    {
        return $this->checkUnique($request->all());
    }
    public function fa_code_from_unique(Request $request)
    {
        return $this->checkUnique($request->all());
    }
    public function fa_code_to_unique(Request $request)
    {
        return $this->checkUnique($request->all());
    }
    public function tin_no_unique(Request $request)
    {
        return $this->checkUnique($request->all());
    }
    public function pan_no_unique(Request $request)
    {
        return $this->checkUnique($request->all());
    }
    public function cin_no_unique(Request $request)
    {
        return $this->checkUnique($request->all());
    }
    public function companyAssociatesListing(Request $request)
    {
        if ($request->ajax()) {
            $data = CompanyAssociate::has('company')->orderBy('updated_at', 'ASC');
            $user = \App\Models\Admin::pluck('username','id');
            $count = $data->count('id');
            $totalCount = $count;
            $data = $data->offset($_POST['start'])->limit($_POST['length'])->get(['status', 'id', 'company_id', 'user_name', 'created_at','created_by_id']);
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $company = $this->repository->getCompaniesById($row->company_id)->select('name', 'id')->first();
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['company_id'] = $row->company_id;
                $val['company_name'] = $company->name;
                $val['created_by'] = $user[$row->created_by_id];
                $val['created_at'] = date("d/m/Y H:i:s", strtotime($row->created_at));
                $val['status'] = $row->status == '1' ? 'Active' : 'Inactive';
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);
            return json_encode($output);
        }
    }
    public function company_default_settings(Request $request)
    {
        if ($request->ajax()) {
            $request->validate([
                'settings_name' => 'required',
                'settings_short_name' => 'required',
                'settings_effective_from' => 'required',
                'system_date' => 'required',
                'settings_amount' => 'required',
                'user_id' => 'required',
            ]);
            if (Auth::user()->id == $request->user_id) {
                $company = $this->repository->getAllCompanies()->latest()->first();
                $input = [];
                $input['name'] = $request->settings_name;
                $input['short_name'] = $request->settings_short_name;
                $input['effective_from'] = date('Y-m-d', strtotime($request->settings_effective_from));
                $input['effective_to'] = null;
                $input['company_id'] = $company->id;
                $input['status'] = 1;
                $input['delete'] = 0;
                $input['created_by'] = 1;
                $input['created_by_id'] = $request->user_id;
                $input['created_at'] = date('Y-m-d', strtotime($request->system_date));
                $input['head_id'] = 1;
                $defauly_setting = $this->repository->createSystemDefaultSettings($input);
                if ($defauly_setting) {
                    $response = ['data' => $company->id];
                    return json_encode($response);
                }
            } else {
                $response = [
                    'data' => ['0'],
                ];
                return json_encode($response);
            }
        }
    }
    public function company_default_settings_update(Request $request)
    {
        if ($request->ajax()) {
            $request->validate([
                'settings_name' => 'required',
                'settings_short_name' => 'required',
                'settings_effective_from' => 'required',
                'system_date' => 'required',
                'settings_amount' => 'required',
                'user_id' => 'required',
                'default_settings_id' => 'required',
                'company_id_default_settings' => 'required',
            ]);
            if (Auth::user()->id == $request->user_id) {
                $company = $this->repository->getCompaniesById($request->company_id_default_settings)->first();
                $input = [];
                $input['name'] = $request->settings_name;
                $input['short_name'] = $request->settings_short_name;
                $input['effective_from'] = date('Y-m-d', strtotime($request->settings_effective_from));
                $input['effective_to'] = null;
                $input['company_id'] = $company->id;
                $input['status'] = 1;
                $input['delete'] = 0;
                $input['created_by'] = 1;
                $input['created_by_id'] = $request->user_id;
                $input['created_at'] = date('Y-m-d', strtotime($request->system_date));
                $input['head_id'] = 1;
                $defaulySetting = $this->repository->updateSystemDefaultSettings($request->default_settings_id, $input);
                if ($defaulySetting) {
                    $response = ['data' => $request->company_id_default_settings];
                    return json_encode($response);
                }
            } else {
                $response = [
                    'data' => ['0'],
                ];
                return json_encode($response);
            }
        }
    }
    public function fa_code_from_check(Request $request)
    {
        $faCodeFrom = (int) $request->fa_code_from;
        $FaCodeTo = $faCodeFrom + 99;
        $company = $this->repository->getAllCompanies()
            ->where('fa_code_from', '<=', $FaCodeTo)
            ->where('fa_code_to', '>=', $faCodeFrom)
            ->count();
        return response()->json(['data' => $company == 0 ? '1' : '0']);
    }
}
