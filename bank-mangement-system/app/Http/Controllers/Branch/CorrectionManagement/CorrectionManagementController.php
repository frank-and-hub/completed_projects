<?php

namespace App\Http\Controllers\Branch\CorrectionManagement;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Branch\CommanController;
use App\Models\Branch;
use App\Models\Member;
use App\Models;
use App\Models\MemberBankDetail;
use App\Models\CorrectionRequests;
use App\Models\Companies;
use App\Models\AssociateGuarantor;
use App\Models\AssociateDependent;
use Carbon\Carbon;
use Session;
use URL;
use App\Services\ImageUpload;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\CorrectionType;
use App\Models\CorrectionRequestDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;


class CorrectionManagementController extends Controller
{

    public function index()
    {
        // if (!in_array('Correction Management Request', auth()->user()->getPermissionNames()->toArray())) {
        //     return redirect()->route('branch.dashboard');
        // }
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        $data['title'] = 'Create Request';
        $data['state']=stateList();  
        $data['occupation']=occupationList();
        $data['religion']=religionList();
        $data['specialCategory']=specialCategoryList();
        $data['idTypes']=idTypeList();
        $data['relations']=relationsList();
        return view('templates/branch/CorrectionManagement/add_request', $data);
    }

    public function fields(Request $request)
    {
        $correctionType = $request->correctionType;
        $fields = CorrectionType::select('id', 'field_name')->where('status', 1)->where('is_deleted',0)->where('module_name', $correctionType)->get();
        $return_array = compact('fields');
        return json_encode($return_array);
    }
    public function details(Request $request)
    {
        $correctionType = $request->correctionType;
        $field = $request->fields;
        $data = CorrectionType::whereId($field)->first(['id', 'module_name', 'field_slug', 'field_name', 'main_table']);
        $field_slug = $data->field_slug;
        $correction_id = $data->id;
        $return_array = [];
        $getBranchId = getUserBranchId(Auth::user()->id);
        $BranchId = $getBranchId->id;
        if ($data->module_name == $correctionType) {
            if ($data->main_table == "member") {
                $oldvalue = Member::whereBranch_id($BranchId)->select('' . $field_slug . '', 'id','company_id')->with('memberCompany:id,customer_id,company_id');
                if ($correctionType == "Customer Details") {
                    $oldvalue = $oldvalue->where('member_id', $request->user_info);
                } elseif ($correctionType == "Associate Details") {
                    $oldvalue = $oldvalue->where('associate_no', $request->user_info);
                }
                $record = $oldvalue->first();
                if ($record == null) {
                    return response()->json("b_issue", 200);
                }
                $return_array['name'] = $record->$field_slug;
                $return_array['id'] = $record->id;
                $return_array['correction_id'] = $correction_id;
                $return_array['field_slug'] = $field_slug;
                $return_array['company_id'] = $record->memberCompany->company_id;
            } else {
                $oldvalue = Member::whereBranch_id($BranchId)->select('id','company_id')->with('memberCompany:id,customer_id,company_id');
                if ($correctionType == "Customer Details") {
                    $oldvalue = $oldvalue->where('member_id', $request->user_info);
                } elseif ($correctionType == "Associate Details") {
                    $oldvalue = $oldvalue->where('associate_no', $request->user_info);
                }
                $oldvalue = $oldvalue->first();
                if ($oldvalue == null) {
                    return response()->json("b_issue", 200);
                }
                $id = $oldvalue->id;
                $company_id = $oldvalue->memberCompany->company_id;
                $modelClass = '\App\Models\\' . $data->main_table;
                $modelInstance = new $modelClass;
    
                // Now you can perform Eloquent operations on the model instance, for example:
                $record = $modelInstance->select($field_slug,'member_id')
                    ->where('member_id', $id)
                    ->first();
                    if ($record == null) {
                        return response()->json("not", 200);
                    }
                    $return_array['name'] = $record->$field_slug;
                    $return_array['id'] = $record->member_id;
                    $return_array['correction_id'] = $correction_id;
                    $return_array['field_slug'] = $field_slug;
                    $return_array['company_id'] = $company_id;
            }
            if (($field == 63 || $field == 64) && $return_array['name'] != '') {
                if ($field == 63) {
                    $folderName = 'profile/member_avatar/' . $return_array['name'];
                }
                if ($field == 64) {
                    $folderName = 'profile/member_signature/' . $return_array['name'];
                }
                $return_array['img'] = ImageUpload::generatePreSignedUrl($folderName);
            } else {
                $return_array['img'] = url('/') . '/asset/images/user.png';
            }
            return response()->json($return_array, 200);
        }
    }

    public function save(Request $request){
        $input = $request->all();
        // if (isset($input['image'])) {
        //     $uploaded_file = $input['image'];
        //     $rand = rand(0000, 9999);
        //     $file_extension = $uploaded_file->getClientOriginalExtension();
        //     $file_name = $rand . '_' . time() . '.' . $file_extension;
        //     $file_location = 'asset/correction/' . $file_name;
        //     if (in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
        //         Image::make($uploaded_file)->save($file_location);
        //         $input['image'] = $file_name;
        //     }
        // }
        $values = ['branch_id', 'company_id', 'old_value','actual_value','correction_type_Id','type_id', 'new_value' , 'description'];
        if ($request->hasFile('photo')) {
            $photo_image = $request->file('photo');
            $randomNumber = random_int(10000, 99999);
            if ($request->correction_type_Id == 63) {
                $photo_filename = $randomNumber . '_m_' . time() . '.' . $photo_image->getClientOriginalExtension();
                $mainFolderPhoto = '/profile/member_avatar/';
                $input['new_value'] = 'Profile';
            } elseif ($request->correction_type_Id == 64) {
                $photo_filename = $randomNumber . '_s_' . time() . '.' . $photo_image->getClientOriginalExtension();
                $mainFolderPhoto = '/profile/member_signature/';
                $input['new_value'] = 'Signature';
            }
            ImageUpload::upload($photo_image, $mainFolderPhoto, $photo_filename);
            $input['actual_value'] = $photo_filename;
            unset($input['photo']);
        }
        foreach ($input as $key => $value) {            
            if (in_array($key,$values)) {
                $data[$key] = $value;
            } else {
               if($value != null && isset($value)){
                $data['new_value'] = $value;
               }
            }
        }
        $data['status'] = 0;
        $data['created_by'] = 2;
        $data['created_by_id'] = Auth::user()->id;
        $insert = CorrectionRequestDetail::create($data);
        if ($insert) {
            $data = 1;
            return response()->json($data, 200);
        } else {
            $data = 0;
            return response()->json($data, 200);
        }        
    }
    
    public function correctionRequestviewnew()
    {
        $data['title'] = 'Correction Request';
        $data['company'] = Companies::where('status',1)->pluck('name','id');
        return view('templates/branch/CorrectionManagement/correctionrequestnew', $data);
    }
    public function correctionRequestlists(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = CorrectionRequestDetail::with(['correction_type', 'branch:id,name', 'customer:id,first_name,member_id,associate_no', 'company:id,name', 'user:id,username']);
                $getBranchId = getUserBranchId(Auth::user()->id);
                $arrFormData['branch_id'] = $getBranchId->id;
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '0') {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $id);
                }

                if ($arrFormData['company_id'] != '' && $arrFormData['company_id'] != '0') {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
                }
                if ($arrFormData['associate_code'] != '') {
                    $name = $arrFormData['associate_code'];
                    $data = $data->whereHas('customer', function ($query) use ($name) {
                        $query->where('members.associate_no', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.associate_no)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['customer_id'] != '') {
                    $name = $arrFormData['customer_id'];
                    $data = $data->whereHas('customer', function ($query) use ($name) {
                        $query->where('members.member_id', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.member_id)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }
                $data1 = $data->orderBy('id', 'DESC')->count('id');
                $count = $data1;
                $datac = $data->orderBy('id', 'DESC')->get();
                $data = $data->orderBy('id', 'DESC')->offset($_POST['start'])->limit($_POST['length']);
                $data = $data->get();
                $totalCount = $data1;
                $sno = $_POST['start'];
                $rowReturn = array();
                $token = session()->get('_token');
                $Cache = Cache::put('correctionlist' . $token, $datac);
                Cache::put('correctionlist_COUNT' . $token, $count);
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['created_at']  = date("d/m/Y", strtotime(str_replace('-', '/', $row->created_at)));
                    $name = '';
                    $name .= $row['customer']->first_name ?? '';
                    $name .= $row['customer']->last_name ?? '';
                    $val['customer_name']  = $name;
                    $val['correction_type_Id'] = $row['correction_type']->module_name;
                    $val['field_name'] = $row['correction_type']->field_name;
                    $val['old_value'] = $row->old_value;
                    $act = '';
                    if ($row->correction_type_Id == 4 || $row->correction_type_Id == 38) {
                        $act = '('.$row->actual_value.')';
                    }
                    $val['new_value'] = $row->new_value.$act;
                    if ($row->correction_type_Id == 63 || $row->correction_type_Id == 64) {
                        $folderName = $row->correction_type_Id == 63 ? 'profile/member_avatar/'  . $row->actual_value :'profile/member_signature/' . $row->actual_value;
                        $folderName2 = $row->correction_type_Id == 63 ? 'profile/member_avatar/'  . $val['old_value'] :'profile/member_signature/' . $val['old_value'];
                        $url = ImageUpload::generatePreSignedUrl($folderName);
                        $url2 = ImageUpload::generatePreSignedUrl($folderName2);
                        $image = '<a href="' . $url . '" target="_blank">'.$val['new_value'].' </a>';
                        $val['old_value'] = '<a href="' . $url2 . '" target="_blank">'.$val['old_value'].' </a>';
                        $val['new_value'] = $image;
                    }
                    $val['description'] = $row->description;
                    $status = 'N/A';
                    if ($row->status == 0 || $row->status == '0') {
                        $status = 'PENDING';
                    } elseif ($row->status == 1 || $row->status == '1') {
                        $status = 'APPROVED';
                    } elseif ($row->status == 2 || $row->status == '2') {
                        $status = 'REJECTED';
                    }
                    $val['branch'] = $row['branch']->name;
                    $val['company'] = $row['company']->name;
                    $user = 'N/A';
                    if ($row->created_by == 3 || $row->created_by == '3') {
                        $user = 'ASSOCIATE';
                    } elseif ($row->created_by == 1 || $row->created_by == '1') {
                        $user = 'ADMIN';
                    } elseif ($row->created_by == 2 || $row->created_by == '2') {
                        $user = 'BRANCH';
                    } elseif ($row->created_by == 4 || $row->created_by == '4') {
                        $user = 'E-PASSBOOK';
                    }
                    $val['status'] = $status;
                    $val['created_by'] = $user;
                    $val['user'] =  $row['user']->username;
                    $val['status_date'] = $row->status_date ? date("d/m/Y", strtotime(str_replace('-', '/', $row->status_date))) : 'N/A';
                    $val['status_remark'] = $row->status_remark ?? 'N/A';
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                return json_encode($output);
            } else {
                $output = array(
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
                return json_encode($output);
            }
        }
    }
    public function exportcorrection(Request $request)
    {
        $token = session()->get('_token');
        $file = Session::get('_fileName');
        $data  = Cache::get('correctionlist' . $token);
        $count = Cache::get('correctionlist_COUNT' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/Correction_List" . $file . ".csv";
        $fileName = env('APP_EXPORTURL') . "/asset/Correction_List" . $file . ".csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $totalResults = $count;
        $result = 'next';
        if (($start + $limit) >= $totalResults) {
            $result = 'finished';
        }
        if ($start == 0) {
            $handle = fopen($fileName, 'w');
        } else {
            $handle = fopen($fileName, 'a');
        }
        if ($start == 0) {
            $headerDisplayed = false;
        } else {
            $headerDisplayed = true;
        }

        $sno = $_POST['start'];
        $rowReturn = [];
        $data = $data->toArray();
        $record = array_slice($data, $start, $limit);
        $totalCount = count($record);
        foreach ($record as $row) {
            $sno++;
            $val['S No.'] = $sno;
            $val['CREATED AT']  = date("d/m/Y", strtotime(str_replace('-', '/', $row['created_at'])));
            $name = '';
            $name .= $row['customer']['first_name'] ?? '';
            $name .= $row['customer']['last_name'] ?? '';
            $val['CUSTOMER NAME']  = $name;
            $val['CHANGES FOR'] = $row['correction_type']['module_name'];
            $val['FIELD TO UPDATE'] = $row['correction_type']['field_name'];
            $val['OLD VALUE'] = $row['old_value'];
            $val['NEW VALUE'] = $row['new_value'];
            $val['DESCRIPTION'] = $row['description'];
            $status = 'N/A';
            if ($row['status'] == 0 || $row['status'] == '0') {
                $status = 'PENDING';
            } elseif ($row['status'] == 1 || $row['status'] == '1') {
                $status = 'APPROVED';
            } elseif ($row['status'] == 2 || $row['status'] == '2') {
                $status = 'REJECTED';
            }
            $val['BRANCH NAME'] = $row['branch']['name'];
            $val['COMPANY NAME'] = $row['company']['name'];
            $user = 'N/A';
            if ($row['created_by'] == 3 || $row['created_by'] == '3') {
                $user = 'ASSOCIATE';
            } elseif ($row['created_by'] == 1 || $row['created_by'] == '1') {
                $user = 'ADMIN';
            } elseif ($row['created_by'] == 2 || $row['created_by'] == '2') {
                $user = 'BRANCH';
            } elseif ($row['created_by'] == 4 || $row['created_by'] == '4') {
                $user = 'E-PASSBOOK';
            }
            $val['STATUS'] = $status;
            $val['CREATED BY'] = $user;
            $val['USER'] =  $row['user']['username'];;
            $val['STATUS DATE'] = !empty($row['status_date']) ? date("d/m/Y", strtotime(str_replace('-', '/', $row['status_date']))) : 'N/A';
            $val['STATUS REMARK'] = $row['status_remark'] ?? 'N/A';
            if (!$headerDisplayed) {
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            fputcsv($handle, $val);
        }
        // Close the file
        fclose($handle);
        if ($totalResults == 0) {
            $percentage = 100;
        } else {
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float) $percentage, 1, '.', '');
        }
        // Output some stuff for jquery to use
        $response = array(
            'result' => $result,
            'start' => $start,
            'limit' => $limit,
            'totalResults' => $totalResults,
            'fileName' => $returnURL,
            'percentage' => $percentage
        );
        echo json_encode($response);
    }
  
}
