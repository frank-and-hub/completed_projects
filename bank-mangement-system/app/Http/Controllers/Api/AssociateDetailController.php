<?php
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Member;
use App\Models\MemberIdProof;
use App\Models\CommissionLeaser;
use App\Models\CommissionLeaserDetail;
use App\Models\AssociateGuarantor;
use App\Models\AssociateDependent;
use App\Models\AssociateKotaBusiness;
use App\Models\AssociateCommission;
use App\Models\Daybook;
use App\Models\SavingAccountTranscation;
use App\Models\SavingAccountTransactionView;
use App\Models\MemberCompany;
use App\Models\Memberinvestments;
use App\Models\SavingAccount;
use Carbon\Carbon;
use App\Services\ImageUpload;
class AssociateDetailController extends Controller
{
    /**
     * Fetch member investments.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getCategoryTree($parent_id, $tree_array = array(), $carder = null)
    {
        $categories = associateTreeid($parent_id, $carder);
        foreach ($categories as $item) {
            $tree_array[] = ['member_id' => $item->member_id, 'status' => $item['member']->associate_status, 'is_block' => $item['member']->is_block];
            if (count($item->subcategory) != 0) {
                $tree_array = $this->getCategoryTree($item->member_id, $tree_array, $carder);
            }
        }
        return $tree_array;
    }
    public function associateList(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status', 'associate_no', 'associate_status', 'is_block')
                ->where('associate_no', $request->associate_no)
                ->where('associate_status', 1)
                ->where('is_block', 0)
                ->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0) {
                        $data = array();
                        $id = $member->id;
                        $a = $this->getCategoryTree($id, $tree_array = array());
                        $b[] = array('member_id' => $id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            if ($v['status'] == 1 && $v['is_block'] == 0) {
                                $tree_array1[] = $v['member_id'];
                            }
                        }
                        $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                        $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->get();
                        $count = count($member_data1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            if (isset($request->length)) {
                                $start = ($request->page - 1) * $request->length;
                            }
                        }
                        $member_data = isset($request->length) ? $member_data->orderby('associate_join_date', 'DESC')->offset($start)->limit($request->length)->get() : $member_data->orderby('associate_join_date', 'DESC')->get();
                        foreach ($member_data as $key => $value) {
                            $data[$key]['id'] = $value->id;
                            $data[$key]['associate_join_date'] = date("d/m/Y", strtotime(str_replace('-', '/', $value->associate_join_date)));
                            $data[$key]['branch_code'] = $value['associate_branch']->branch_code;
                            $data[$key]['branch_name'] = $value['associate_branch']->name;
                            $data[$key]['sector_name'] = $value['associate_branch']->sector;
                            $data[$key]['associate_no'] = $value->associate_no;
                            $data[$key]['name'] = $value->first_name . ' ' . $value->last_name;
                            $data[$key]['associate_carder'] = ($value->associate_no == $associate_no) ? 'N/A' : getCarderName($value->current_carder_id);
                            $data[$key]['senior_associate_code'] = getSeniorData($value->associate_senior_id, 'associate_no');
                            $data[$key]['senior_associate_name'] = getSeniorData($value->associate_senior_id, 'first_name') . ' ' . getSeniorData($value->associate_senior_id, 'last_name');
                            $data[$key]['imageurl'] = $value->photo ? ImageUpload::fileExists('profile/member_avatar/' . $value->photo) ? ImageUpload::generatePreSignedUrl('profile/member_avatar/' . $value->photo) : URL('asset/images/user.png') : URL('asset/images/user.png');
                            $data[$key]['signatureurl'] = $value->signature ? ImageUpload::fileExists('profile/member_signature/' . $value->signature) ? ImageUpload::generatePreSignedUrl('profile/member_signature/' . $value->signature) : URL('asset/images/signature-logo-design.png') : URL('asset/images/signature-logo-design.png');
                            $data[$key]['status'] = ($value->is_block == 1) ? 'Blocked' : (($value->associate_status == 1) ? 'Active' : 'Inactive');
                            $data[$key]['nominee_name'] = getMemberNomineeDetail($value->id)->name;
                            if ($value->id) {
                                $relation_id = getMemberNomineeDetail($value->id);
                                if ($relation_id->relation != NULl) {
                                    $relationName = getMemberNomineeRelation($relation_id->relation);
                                    if (isset($relationName->name)) {
                                        $relation = $relationName->name;
                                    } else {
                                        $relation = 'N/A';
                                    }
                                } else {
                                    $relation = $relationId;
                                }
                            } else {
                                $relation = $relationId;
                            }
                            $data[$key]['relation'] = $relation;
                            $data[$key]['nominee_age'] = getMemberNomineeDetail($value->id)->age;
                            $data[$key]['email'] = isset($value->email) ? $value->email : 'N/A';
                            $data[$key]['mobile_no'] = $value->mobile_no;
                            $data[$key]['senior_associate_code'] = $value->associate_senior_code;
                            $data[$key]['senior_associate_name'] = getSeniorData($value->associate_senior_id, 'first_name') . ' ' . getSeniorData($value->associate_senior_id, 'last_name');
                            $data[$key]['address'] = $value->address;
                            $data[$key]['state'] = getStateName($value->state_id);
                            $data[$key]['district'] = getDistrictName($value->district_id);
                            $data[$key]['city'] = getCityName($value->city_id);
                            $data[$key]['village'] = $value->village;
                            $data[$key]['pin_code'] = $value->pin_code;
                            $idProofDetail = MemberIdProof::where('member_id', $value->id)->first();
                            $data[$key]['firstId'] = getIdProofName($idProofDetail->first_id_type_id) . ' - ' . $idProofDetail->first_id_no;
                            $data[$key]['secondId'] = getIdProofName($idProofDetail->second_id_type_id) . ' - ' . $idProofDetail->second_id_no;
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'Associate listing!';
                        $page = $request->page;
                        $length = $request->length;
                        $result = ['associate' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } elseif ($request->page == 0) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            if ($v['status'] == 1 && $v['is_block'] == 0) {
                                $tree_array1[] = $v['member_id'];
                            }
                        }
                        $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                        $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->get();
                        $count = count($member_data1);
                        $start = ($request->page == 1) ? 0 : (($request->page - 1) * $request->length);
                        $member_data = $member_data->orderby('associate_join_date', 'DESC')->get();
                        foreach ($member_data as $key => $value) {
                            $data[$key]['id'] = $value->id;
                            $data[$key]['associate_join_date'] = date("d/m/Y", strtotime(str_replace('-', '/', $value->associate_join_date)));
                            $data[$key]['branch_code'] = $value['associate_branch']->branch_code;
                            $data[$key]['branch_name'] = $value['associate_branch']->name;
                            $data[$key]['sector_name'] = $value['associate_branch']->sector;
                            $data[$key]['associate_no'] = $value->associate_no;
                            $data[$key]['name'] = $value->first_name . ' ' . $value->last_name;
                            $data[$key]['associate_carder'] = getCarderName($value->current_carder_id);
                            $data[$key]['senior_associate_code'] = getSeniorData($value->associate_senior_id, 'associate_no');
                            $data[$key]['senior_associate_name'] = getSeniorData($value->associate_senior_id, 'first_name') . ' ' . getSeniorData($value->associate_senior_id, 'last_name');
                            $data[$key]['imageurl'] = ($value->photo) ? ImageUpload::fileExists('profile/member_avatar/' . $value->photo) ? ImageUpload::generatePreSignedUrl('profile/member_avatar/' . $value->photo) : URL('asset/images/user.png') : URL('asset/images/user.png');
                            $data[$key]['signatureurl'] = ($value->signature) ? ImageUpload::fileExists('profile/member_signature/' . $value->signature) ? ImageUpload::generatePreSignedUrl('profile/member_signature/' . $value->signature) : URL('asset/images/signature-logo-design.png') : URL('asset/images/signature-logo-design.png');
                            $data[$key]['status'] = ($value->is_block == 1) ? "Blocked" : (($value->associate_status == 1) ? 'Active' : 'Inactive');
                            $data[$key]['nominee_name'] = getMemberNomineeDetail($value->id)->name;
                            if ($value->id) {
                                $relation_id = getMemberNomineeDetail($value->id);
                                if ($relation_id->relation != NULl) {
                                    $relationName = getMemberNomineeRelation($relation_id->relation);
                                    if (isset($relationName->name)) {
                                        $relation = $relationName->name;
                                    } else {
                                        $relation = 'N/A';
                                    }
                                } else {
                                    $relation = $relationId;
                                }
                            } else {
                                $relation = $relationId;
                            }
                            $data[$key]['relation'] = $relation;
                            $data[$key]['nominee_age'] = getMemberNomineeDetail($value->id)->age;
                            $data[$key]['email'] = isset($value->email) ? $value->email : 'N/A';
                            $data[$key]['mobile_no'] = $value->mobile_no;
                            $data[$key]['senior_associate_code'] = $value->associate_senior_code;
                            $data[$key]['senior_associate_name'] = getSeniorData($value->associate_senior_id, 'first_name') . ' ' . getSeniorData($value->associate_senior_id, 'last_name');
                            $data[$key]['address'] = $value->address;
                            $data[$key]['state'] = getStateName($value->state_id);
                            $data[$key]['district'] = getDistrictName($value->district_id);
                            $data[$key]['city'] = getCityName($value->city_id);
                            $data[$key]['village'] = $value->village;
                            $data[$key]['pin_code'] = $value->pin_code; 
                            $idProofDetail = MemberIdProof::where('member_id', $value->id)->first();
                            $data[$key]['firstId'] = getIdProofName($idProofDetail->first_id_type_id) . ' - ' . $idProofDetail->first_id_no;
                            $data[$key]['secondId'] = getIdProofName($idProofDetail->second_id_type_id) . ' - ' . $idProofDetail->second_id_no;
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'Associate listing!';
                        $result = ['associate' => $data, 'total_count' => $count, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function commissionLedgerList(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $comm_data = CommissionLeaser::where('status', '>', 0)->where('status', '<', 3)->where('is_deleted', 0)->orderby('id', 'DESC');
                    $comm_data1 = $comm_data->get();
                    $count = count($comm_data1);
                    $comm_data = $comm_data->get();
                    foreach ($comm_data as $key => $value) {
                        $data[$key]['ledger_id'] = $value->id;
                        $data[$key]['date'] = date("d/m/Y", strtotime($value->start_date)) . ' - ' . date("d/m/Y ", strtotime($value->end_date));
                    }
                    $status = "Success";
                    $code = 200;
                    $messages = 'Ledger  listing!';
                    $result = ['ledger' => $data, 'total_count' => $count];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function associateCommissionList(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0 && $request->length > 0) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            $tree_array1[] = $v['member_id'];
                        }
                        $comm_data = CommissionLeaserDetail::where('commission_leaser_id', $request->ledger_id)->whereIN('member_id', $tree_array1)->orderby('id', 'DESC');
                        $comm_data1 = $comm_data->get();
                        $count = count($comm_data1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }
                        $comm_data = $comm_data->offset($start)->limit($request->length)->get();
                        foreach ($comm_data as $key => $row) {
                            $data[$key]['ledger_id'] = $row->commission_leaser_id;
                            $data[$key]['member_id'] = $row->member_id;
                            $data[$key]['associate_name'] = getSeniorData($row->member_id, 'first_name') . ' ' . getSeniorData($row->member_id, 'last_name');
                            $data[$key]['associate_no'] = getSeniorData($row->member_id, 'associate_no');
                            $data[$key]['associate_carder'] = getCarderName(getSeniorData($row->member_id, 'current_carder_id'));
                            $data[$key]['branch_code'] = getBranchCode(getSeniorData($row->member_id, 'associate_branch_id'))->branch_code;
                            $data[$key]['branch_name'] = getBranchName(getSeniorData($row->member_id, 'associate_branch_id'))->name;
                            $data[$key]['total_amount'] = number_format((float) $row->amount_tds, 2, '.', '');
                            $data[$key]['final_total_amount'] = number_format((float) $row->amount, 2, '.', '');
                            $data[$key]['fuel_amount'] = number_format((float) $row->fuel, 2, '.', '');
                            $data[$key]['tds_amount'] = number_format((float) $row->total_tds, 2, '.', '');
                            $data[$key]['total_collection'] = number_format((float) $row->collection, 2, '.', '');
                            $data[$key]['commission_payment'] = number_format((float) $row->amount, 2, '.', '');
                            $pan = get_member_id_proof($row->member_id, 5);
                            $data[$key]['pan_no'] = $pan;
                            $ssbAccountDetail = getMemberSsbAccountDetail($row->member_id);
                            $account = $ssbAccountDetail->account_no;
                            $data[$key]['ssb_account'] = $account;
                            if ($row->status == 1) {
                                $status = 'Transferred';
                            } else {
                                $status = 'Deleted';
                            }
                            $data[$key]['status'] = $status;
                            $data[$key]['created_date'] = date("d/m/Y h:i:s a", strtotime($row->created_at));
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'associate commission listing!';
                        $page = $request->page;
                        $length = $request->length;
                        $result = ['associate_com' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } elseif ($request->page == 0) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            $tree_array1[] = $v['member_id'];
                        }
                        $comm_data = CommissionLeaserDetail::where('commission_leaser_id', $request->ledger_id)->whereIN('member_id', $tree_array1)->orderby('id', 'DESC');
                        $comm_data1 = $comm_data->get();
                        $count = count($comm_data1);
                        $comm_data = $comm_data->get();
                        foreach ($comm_data as $key => $row) {
                            $data[$key]['ledger_id'] = $row->commission_leaser_id;
                            $data[$key]['member_id'] = $row->member_id;
                            $data[$key]['associate_name'] = getSeniorData($row->member_id, 'first_name') . ' ' . getSeniorData($row->member_id, 'last_name');
                            $data[$key]['associate_no'] = getSeniorData($row->member_id, 'associate_no');
                            $data[$key]['associate_carder'] = getCarderName(getSeniorData($row->member_id, 'current_carder_id'));
                            $data[$key]['branch_code'] = getBranchCode(getSeniorData($row->member_id, 'associate_branch_id'))->branch_code;
                            $data[$key]['branch_name'] = getBranchName(getSeniorData($row->member_id, 'associate_branch_id'))->name;
                            $data[$key]['total_amount'] = number_format((float) $row->amount, 2, '.', '');
                            $data[$key]['final_total_amount'] = number_format((float) $row->amount, 2, '.', '');
                            $data[$key]['fuel_amount'] = number_format((float) $row->fuel, 2, '.', '');
                            $data[$key]['tds_amount'] = number_format((float) $row->total_tds, 2, '.', '');
                            $data[$key]['total_collection'] = number_format((float) $row->collection, 2, '.', '');
                            $data[$key]['commission_payment'] = number_format((float) $row->amount, 2, '.', '');
                            $pan = get_member_id_proof($row->member_id, 5);
                            $data[$key]['pan_no'] = $pan;
                            $ssbAccountDetail = getMemberSsbAccountDetail($row->member_id);
                            $account = $ssbAccountDetail->account_no;
                            $data[$key]['ssb_account'] = $account;
                            if ($row->status == 1) {
                                $status = 'Transferred';
                            } else {
                                $status = 'Deleted';
                            }
                            $data[$key]['status'] = $status;
                            $data[$key]['created_date'] = date("d/m/Y h:i:s a", strtotime($row->created_at));
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'associate commission listing!';
                        $result = ['associate_com' => $data, 'total_count' => $count, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function associate_detail(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    if ($request->id > 0) {
                        $id = $request->id;
                        $memberData = Member::where('id', $id)->first();
                        $data['idProofDetail'] = MemberIdProof::where('member_id', $id)->first();
                        $data['associate_form_information']['form_no'] = $memberData->associate_form_no;
                        $data['associate_form_information']['join_date'] = date("d/m/Y", strtotime($memberData->associate_join_date));
                        $data['associate_form_information']['member_id'] = $memberData->member_id;
                        $data['associate_form_information']['associate_no'] = $memberData->associate_no;
                        $data['associate_form_information']['carder'] = $memberData->current_carder_id ? getCarderName($memberData->current_carder_id) : 'N/A';
                        // $data['associate_form_information']['carder'] =($memberData->associate_no != $associate_no) ? '' : getCarderName($memberData->current_carder_id) ;
                        $data['associate_details']['associate_code'] = ''; //getSeniorData($memberData->associate_senior_id,'associate_no');
                        $data['associate_details']['associate_name'] = ''; //getSeniorData($memberData->associate_senior_id,'first_name').' '.getSeniorData($memberData->associate_senior_id,'last_name');
                        $data['associate_details']['mobile_no'] = ''; //getSeniorData($memberData->associate_senior_id,'mobile_no');
                        $data['associate_details']['carder'] = '';
                        // if($data['associate_details']['associate_code']!='9999999')
                        // {
                        //     if($memberData->associate_no == $associate_no)
                        //     {
                        //         $data['associate_details']['carder'] ='';
                        //     }
                        //     else{
                        //         $data['associate_details']['carder'] =getCarderName(getSeniorData($memberData->associate_senior_id,'current_carder_id'));
                        //     }
                        // }
                        $data['personal_information']['first_name'] = $memberData->first_name ?? '';
                        $data['personal_information']['last_name'] = $memberData->last_name ?? '';
                        $data['personal_information']['email'] = $memberData->email ?? '';
                        $data['personal_information']['mobile_no'] = $memberData->mobile_no ?? '';
                        $data['personal_information']['dob'] = date("d/m/Y", strtotime($memberData->dob));
                        $data['personal_information']['age'] = $memberData->age ?? '';
                        $data['personal_information']['gender'] = ($memberData->gender == 0) ? 'Female' : 'male';
                        $data['personal_information']['occupation_id'] = getOccupationName($memberData->occupation_id);
                        $data['personal_information']['annual_income'] = number_format($memberData->annual_income, 2, '.', ',');
                        $data['personal_information']['status'] = ($memberData->associate_status == 1) ? 'Active' : 'Inactive';
                        $data['personal_information']['address'] = $memberData->address;
                        $data['personal_information']['state'] = getStateName($memberData->state_id);
                        $data['personal_information']['district'] = getDistrictName($memberData->district_id);
                        $data['personal_information']['city'] = getCityName($memberData->city_id);
                        $data['personal_information']['village'] = $memberData->village;
                        $data['personal_information']['pin_code'] = $memberData->pin_code;
                        if ($memberData->photo) {
                            $data['profile_imge'] = ImageUpload::fileExists('profile/member_avatar/' . $memberData->photo) ? ImageUpload::generatePreSignedUrl('profile/member_avatar/' . $memberData->photo) : URL('asset/images/user.png');
                        } else {
                            $data['profile_imge'] = URL('asset/images/user.png');
                        }
                        if ($memberData->signature) {
                            // $data['signatureurl'] = URL('asset/profile/member_signature/' . $memberData->signature . '');
                            $data['signatureurl'] = ImageUpload::fileExists('profile/member_signature/' . $memberData->signature) ? ImageUpload::generatePreSignedUrl('profile/member_signature/' . $memberData->signature) : URL('asset/images/signature-logo-design.png');
                        } else {
                            $data['signatureurl'] = URL('asset/images/signature-logo-design.png');
                        }
                        $data['idProofDetail']['status'] = ($data['idProofDetail']->status == 1) ? 'Active' : 'Inactive';
                        $data['idProofDetail']['first_id_type_id'] = getIdProofName($data['idProofDetail']->first_id_type_id);
                        $data['idProofDetail']['second_id_type_id'] = getIdProofName($data['idProofDetail']->second_id_type_id);
                        $data['guarantorDetail'] = AssociateGuarantor::where('member_id', $id)->first();
                        $dependentDetail = AssociateDependent::where('member_id', $id)->get();
                        if (count($dependentDetail) > 0) {
                            foreach ($dependentDetail as $key => $val) {
                                $data['dependentDetail'][$key]['id'] = $val->id;
                                $data['dependentDetail'][$key]['name'] = $val->name;
                                if ($val->dependent_type == 1)
                                    $dependenttype = 'Fully';
                                else
                                    $dependenttype = 'Partially';
                                $data['dependentDetail'][$key]['dependent_type'] = $dependenttype;
                                if ($val->gender == 1)
                                    $gender = 'Male';
                                else
                                    $gender = 'Female';
                                $data['dependentDetail'][$key]['gender'] = $gender;
                                if ($val->relation > 0)
                                    $relation = getRelationsName($val->relation);
                                else
                                    $relation = '';
                                $data['dependentDetail'][$key]['relation'] = $relation;
                                if ($val->marital_status == 1)
                                    $marital_status = 'Married';
                                else
                                    $marital_status = 'Un Married';
                                $data['dependentDetail'][$key]['marital_status'] = $marital_status;
                                if ($val->living_with_associate == 1)
                                    $living_with_associate = 'Yes';
                                else
                                    $living_with_associate = 'No';
                                $data['dependentDetail'][$key]['living_with_associate'] = $living_with_associate;
                                $data['dependentDetail'][$key]['monthly_income'] = number_format($val->monthly_income, 2, '.', ',');
                            }
                        } else {
                            $data['dependentDetail'] = array();
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'Associate Detail!';
                        $result = $data;
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Id not enter';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function associateQuotaList(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0 && $request->length > 0) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            $tree_array1[] = $v['member_id'];
                        }
                        if ($request->from_date != '') {
                            $startDate = date("Y-m-d", strtotime(convertDate($request->from_date)));
                            if ($request->to_date != '') {
                                $endDate = date("Y-m-d ", strtotime(convertDate($request->to_date)));
                            } else {
                                $endDate = '';
                            }
                        } else {
                            $startDate = '';
                            $endDate = '';
                        }
                        $qoutaData = Member::with('associate_branch');
                        if ($request->associate_id != '') {
                            $id = $request->associate_id;
                            $qoutaData = $qoutaData->where('id', '=', $id);
                        } else {
                            $qoutaData = $qoutaData->whereIN('id', $tree_array1);
                        }
                        $qoutaData1 = $qoutaData->orderby('id', 'DESC')->get();
                        $count = count($qoutaData1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }
                        $qoutaData = $qoutaData->orderby('id', 'DESC')->offset($start)->limit($request->length)->get();
                        foreach ($qoutaData as $key => $row) {
                            $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                            $data[$key]['branch_name'] = $row['associate_branch']->name;
                            $data[$key]['sector'] = $row['associate_branch']->sector;
                            $data[$key]['regan'] = $row['associate_branch']->regan;
                            $data[$key]['zone'] = $row['associate_branch']->zone;
                            $data[$key]['senior_code'] = getSeniorData($row->associate_senior_id, 'associate_no');
                            $data[$key]['senior_name'] = getSeniorData($row->associate_senior_id, 'first_name') . ' ' . getSeniorData($row->associate_senior_id, 'last_name');
                            $data[$key]['quota_business_target_self_amt'] = getBusinessTargetAmt($row->current_carder_id)->self;
                            if ($startDate != '') {
                                $data[$key]['achieved_target_self_amt'] = round(AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount'), 2);
                            } else {
                                $data[$key]['achieved_target_self_amt'] = round(AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->sum('business_amount'), 2);
                            }
                            $targetSelf = getBusinessTargetAmt($row->current_carder_id)->self;
                            if ($startDate != '') {
                                $achievedSelf = AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                            } else {
                                $achievedSelf = AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->sum('business_amount');
                            }
                            if ($achievedSelf > 0) {
                                $targetSelfPer = 100 - ($achievedSelf / $targetSelf) * 100;
                            } else {
                                $targetSelfPer = 100;
                            }
                            $data[$key]['quota_business_target_self_percentage'] = number_format((float) $targetSelfPer, 1, '.', '');
                            ;
                            ;
                            $targetSelf = getBusinessTargetAmt($row->current_carder_id)->self;
                            if ($startDate != '') {
                                $achievedSelf = AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                            } else {
                                $achievedSelf = AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->sum('business_amount');
                            }
                            if ($achievedSelf > 0) {
                                $achievedSelfPer = ($achievedSelf / $targetSelf) * 100;
                            } else {
                                $achievedSelfPer = 0;
                            }
                            $data[$key]['achieved_target_self_percentage'] = number_format((float) $achievedSelfPer, 1, '.', '');
                            $data[$key]['associate_code'] = $row->associate_no;
                            $data[$key]['associate_name'] = $row->first_name . ' ' . $row->last_name;
                            if ($row->current_carder_id > 1) {
                                $targetTeam = round(getBusinessTargetAmt($row->current_carder_id)->credit, 2);
                            } else {
                                $targetTeam = 'N/A';
                            }
                            $data[$key]['quota_business_target_team_amt'] = (string) $targetTeam;
                            if ($row->current_carder_id > 1) {
                                $achievedTarget = round(getKotaBusinessTeam($row->id, $startDate, $endDate), 2);
                            } else {
                                $achievedTarget = 'N/A';
                            }
                            $data[$key]['achieved_target_team_amt'] = (string) $achievedTarget;
                            if ($row->current_carder_id > 1) {
                                $targetTeam = getBusinessTargetAmt($row->current_carder_id)->credit;
                                $targetteamAchivede = getKotaBusinessTeam($row->id, $startDate, $endDate);
                                $achievedTeamfPer = round(100.000 - ($targetteamAchivede / $targetTeam) * 100, 2);
                            } else {
                                $achievedTeamfPer = 'N/A';
                            }
                            $data[$key]['quota_business_target_team_percentage'] = (string) $achievedTeamfPer;
                            if ($row->current_carder_id > 1) {
                                $targetTeam = getBusinessTargetAmt($row->current_carder_id)->credit;
                                $achievedTarget = getKotaBusinessTeam($row->id, $startDate, $endDate);
                                $achievedTeamfPer = round(($achievedTarget / $targetTeam) * 100, 2);
                            } else {
                                $achievedTeamfPer = 'N/A';
                            }
                            $data[$key]['achieved_target_team_percentage'] = (string) $achievedTeamfPer;
                            $data[$key]['joining_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                            $data[$key]['mobile_number'] = $row->mobile_no;
                            if ($row->is_block == 0) {
                                if ($row->associate_status == 1) {
                                    $status = 'Active';
                                } else {
                                    $status = 'Inactive';
                                }
                            } else {
                                $status = 'Blocked';
                            }
                            $data[$key]['status'] = $status;
                            $data[$key]['associate_carder'] = getCarderNameFull($row->current_carder_id);
                            $data[$key]['senior_carder'] = getCarderNameFull(getSeniorData($row->associate_senior_id, 'current_carder_id'));
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'associate business listing!';
                        $page = $request->page;
                        $length = $request->length;
                        $result = ['qouta_report' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } elseif ($request->page == 0) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            $tree_array1[] = $v['member_id'];
                        }
                        $startDate = '';
                        $endDate = '';
                        if ($request->from_date != '') {
                            $startDate = date("Y-m-d", strtotime(convertDate($request->from_date)));
                            if ($request->to_date != '') {
                                $endDate = date("Y-m-d ", strtotime(convertDate($request->to_date)));
                            } else {
                                $endDate = '';
                            }
                        }
                        $qoutaData = Member::with('associate_branch');
                        if ($request->associate_id != '') {
                            $id = $request->associate_id;
                            $qoutaData = $qoutaData->where('id', '=', $id);
                        } else {
                            $qoutaData = $qoutaData->whereIN('id', $tree_array1);
                        }
                        $qoutaData1 = $qoutaData->orderby('id', 'DESC')->get();
                        $count = count($qoutaData1);
                        // if($request->page==1)
                        // {
                        //     $start=0;
                        // }
                        // else
                        // {
                        //     $start=($request->page-1)*$request->length;
                        // }
                        $qoutaData = $qoutaData->orderby('id', 'DESC')->get();
                        foreach ($qoutaData as $key => $row) {
                            $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                            $data[$key]['branch_name'] = $row['associate_branch']->name;
                            $data[$key]['sector'] = $row['associate_branch']->sector;
                            $data[$key]['regan'] = $row['associate_branch']->regan;
                            $data[$key]['zone'] = $row['associate_branch']->zone;
                            $data[$key]['senior_code'] = getSeniorData($row->associate_senior_id, 'associate_no');
                            $data[$key]['senior_name'] = getSeniorData($row->associate_senior_id, 'first_name') . ' ' . getSeniorData($row->associate_senior_id, 'last_name');
                            $data[$key]['quota_business_target_self_amt'] = getBusinessTargetAmt($row->current_carder_id)->self;
                            $data[$key]['achieved_target_self_amt'] = round(AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount'), 2);
                            $targetSelf = getBusinessTargetAmt($row->current_carder_id)->self;
                            $achievedSelf = AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                            if ($achievedSelf > 0) {
                                $targetSelfPer = 100 - ($achievedSelf / $targetSelf) * 100;
                            } else {
                                $targetSelfPer = 100;
                            }
                            $data[$key]['quota_business_target_self_percentage'] = round($targetSelfPer, 3);
                            $targetSelf = getBusinessTargetAmt($row->current_carder_id)->self;
                            $achievedSelf = AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                            if ($achievedSelf > 0) {
                                $achievedSelfPer = ($achievedSelf / $targetSelf) * 100;
                            } else {
                                $achievedSelfPer = 0;
                            }
                            $data[$key]['achieved_target_self_percentage'] = round($achievedSelfPer, 3);
                            $data[$key]['associate_code'] = $row->associate_no;
                            $data[$key]['associate_name'] = $row->first_name . ' ' . $row->last_name;
                            if ($row->current_carder_id > 1) {
                                $targetTeam = round(getBusinessTargetAmt($row->current_carder_id)->credit, 2);
                            } else {
                                $targetTeam = 'N/A';
                            }
                            $data[$key]['quota_business_target_team_amt'] = (string) $targetTeam;
                            if ($row->current_carder_id > 1) {
                                $achievedTarget = round(getKotaBusinessTeam($row->id, $startDate, $endDate), 2);
                            } else {
                                $achievedTarget = 'N/A';
                            }
                            $data[$key]['achieved_target_team_amt'] = (string) $achievedTarget;
                            if ($row->current_carder_id > 1) {
                                $targetTeam = getBusinessTargetAmt($row->current_carder_id)->credit;
                                $targetteamAchivede = getKotaBusinessTeam($row->id, $startDate, $endDate);
                                $achievedTeamfPer = round(100.000 - ($targetteamAchivede / $targetTeam) * 100, 2);
                            } else {
                                $achievedTeamfPer = 'N/A';
                            }
                            $data[$key]['quota_business_target_team_percentage'] = (string) $achievedTeamfPer;
                            if ($row->current_carder_id > 1) {
                                $targetTeam = getBusinessTargetAmt($row->current_carder_id)->credit;
                                $achievedTarget = getKotaBusinessTeam($row->id, $startDate, $endDate);
                                $achievedTeamfPer = round(($achievedTarget / $targetTeam) * 100, 2);
                            } else {
                                $achievedTeamfPer = 'N/A';
                            }
                            $data[$key]['achieved_target_team_percentage'] = (string) $achievedTeamfPer;
                            $data[$key]['joining_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                            $data[$key]['mobile_number'] = $row->mobile_no;
                            if ($row->is_block == 0) {
                                if ($row->associate_status == 1) {
                                    $status = 'Active';
                                } else {
                                    $status = 'Inactive';
                                }
                            } else {
                                $status = 'Blocked';
                            }
                            $data[$key]['status'] = $status;
                            $data[$key]['associate_carder'] = getCarderNameFull($row->current_carder_id);
                            $data[$key]['senior_carder'] = getCarderNameFull(getSeniorData($row->associate_senior_id, 'current_carder_id'));
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'associate business listing!';
                        $result = ['qouta_report' => $data, 'total_count' => $count, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function associateInvestmentCommission(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0 && $request->length > 0) {
                        $data = array();
                        $commisionList = CommissionLeaserDetail::where('commission_leaser_id', $request->ledger_id)->where('member_id', $request->member_id)->first();
                        $comId = explode(",", $commisionList->commission_id);
                        $comm_data = AssociateCommission::with('investment')->whereIN('id', $comId)->whereIn('type', array(3, 5))->orderby('id', 'DESC');
                        $comm_data1 = $comm_data->get();
                        $count = count($comm_data1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }
                        $comm_data = $comm_data->offset($start)->limit($request->length)->get();
                        foreach ($comm_data as $key => $val) {
                            $data[$key]['id'] = $val->id;
                            $data[$key]['investment_account'] = $val['investment']->account_number;
                            $data[$key]['plan_name'] = transactionPlanName($val->type_id);
                            $data[$key]['total_amount'] = number_format((float) $val->total_amount, 2, '.', '');
                            $data[$key]['commission_amount'] = number_format((float) $val->commission_amount, 2, '.', '');
                            $data[$key]['percentage'] = number_format((float) $val->percentage, 2, '.', '');
                            if ($val->type == 5) {
                                $carder_name = 'Collection Charge';
                            } else {
                                $carder_name = getCarderName($val->carder_id);
                            }
                            $data[$key]['carder_name'] = $carder_name;
                            $get_plan = $val['investment']->plan_id;
                            if ($get_plan == 7) {
                                if ($val->month > 1) {
                                    $emi_no = $val->month . ' Days';
                                } else {
                                    $emi_no = $val->month . ' Day';
                                }
                            } else {
                                if ($val->month > 1) {
                                    $emi_no = $val->month . ' Months';
                                } else {
                                    $emi_no = $val->month . ' Month';
                                }
                            }
                            $data[$key]['emi_no'] = $emi_no;
                            if ($val->commission_type == 0) {
                                $commission_type = 'Self';
                            } else {
                                $commission_type = 'Team Member';
                            }
                            $data[$key]['commission_type'] = $commission_type;
                            if ($val->associate_exist == 0) {
                                $associate_exist = 'Yes';
                            } else {
                                $associate_exist = 'No';
                            }
                            $data[$key]['associate_exist'] = $associate_exist;
                            if ($val->pay_type == 1) {
                                $pay_type = 'OverDue';
                            } elseif ($val->pay_type == 2) {
                                $pay_type = 'Due Date';
                            } else {
                                $pay_type = 'Advance';
                            }
                            $data[$key]['pay_type'] = $pay_type;
                            if ($val->is_distribute == 1) {
                                $is_distribute = 'Yes';
                            } else {
                                $is_distribute = 'No';
                            }
                            $data[$key]['is_distribute'] = $is_distribute;
                            $created_at = date("d/m/Y", strtotime($val->created_at));
                            $data[$key]['created_at'] = $created_at;
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'investment commission listing!';
                        $page = $request->page;
                        $length = $request->length;
                        $result = ['associate_com' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } elseif ($request->page == 0) {
                        $data = array();
                        $commisionList = CommissionLeaserDetail::where('commission_leaser_id', $request->ledger_id)->where('member_id', $request->member_id)->first();
                        //dd($commisionList);
                        $comId = explode(",", $commisionList->commission_id);
                        $comm_data = AssociateCommission::with('investment')->whereIN('id', $comId)->whereIn('type', array(3, 5))->orderby('id', 'DESC');
                        $comm_data1 = $comm_data->get();
                        $count = count($comm_data1);
                        // if($request->page==1)
                        // {
                        //     $start=0;
                        // }
                        // else
                        // {
                        //     $start=($request->page-1)*$request->length;
                        // }
                        $comm_data = $comm_data->get();
                        foreach ($comm_data as $key => $val) {
                            $data[$key]['id'] = $val->id;
                            $data[$key]['investment_account'] = $val['investment']->account_number;
                            $data[$key]['plan_name'] = transactionPlanName($val->type_id);
                            $data[$key]['total_amount'] = number_format((float) $val->total_amount, 2, '.', '');
                            $data[$key]['commission_amount'] = number_format((float) $val->commission_amount, 2, '.', '');
                            $data[$key]['percentage'] = number_format((float) $val->percentage, 2, '.', '');
                            if ($val->type == 5) {
                                $carder_name = 'Collection Charge';
                            } else {
                                $carder_name = getCarderName($val->carder_id);
                            }
                            $data[$key]['carder_name'] = $carder_name;
                            $get_plan = $val['investment']->plan_id;
                            if ($get_plan == 7) {
                                if ($val->month > 1) {
                                    $emi_no = $val->month . ' Days';
                                } else {
                                    $emi_no = $val->month . ' Day';
                                }
                            } else {
                                if ($val->month > 1) {
                                    $emi_no = $val->month . ' Months';
                                } else {
                                    $emi_no = $val->month . ' Month';
                                }
                            }
                            $data[$key]['emi_no'] = $emi_no;
                            if ($val->commission_type == 0) {
                                $commission_type = 'Self';
                            } else {
                                $commission_type = 'Team Member';
                            }
                            $data[$key]['commission_type'] = $commission_type;
                            if ($val->associate_exist == 0) {
                                $associate_exist = 'Yes';
                            } else {
                                $associate_exist = 'No';
                            }
                            $data[$key]['associate_exist'] = $associate_exist;
                            if ($val->pay_type == 1) {
                                $pay_type = 'OverDue';
                            } elseif ($val->pay_type == 2) {
                                $pay_type = 'Due Date';
                            } else {
                                $pay_type = 'Advance';
                            }
                            $data[$key]['pay_type'] = $pay_type;
                            if ($val->is_distribute == 1) {
                                $is_distribute = 'Yes';
                            } else {
                                $is_distribute = 'No';
                            }
                            $data[$key]['is_distribute'] = $is_distribute;
                            $created_at = date("d/m/Y", strtotime($val->created_at));
                            $data[$key]['created_at'] = $created_at;
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'investment commission listing!';
                        $result = ['associate_com' => $data, 'total_count' => $count, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function associateLoanCommission(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            // dd($request->ledger_id);
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0 && $request->length > 0) {
                        $data = array();
                        //dd($request->ledger_id,$request->member_id);
                        $commisionList = CommissionLeaserDetail::where('commission_leaser_id', $request->ledger_id)->where('member_id', $request->member_id)->first();
                        $comId = explode(",", $commisionList->commission_id);
                        $comm_data = AssociateCommission::with('investment')->whereIN('id', $comId)->whereIn('type', array(4, 6, 7, 8))->orderby('id', 'DESC');
                        $comm_data1 = $comm_data->get();
                        $count = count($comm_data1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }
                        $comm_data = $comm_data->offset($start)->limit($request->length)->get();
                        foreach ($comm_data as $key => $val) {
                            $data[$key]['id'] = $val->id;
                            $account = 'N/A';
                            $loan_type = 'N/A';
                            if ($val->type == 4 || $val->type == 6) {
                                $loan_detail = getLoanDetail($val->type_id);
                                // print_r($loan_detail->loan_type);die;
                                $account = $loan_detail->account_number;
                                $loan_type = 'Loan';
                                if ($loan_detail->loan_type == 1) {
                                    $loan_type = 'Personal Loan';
                                }
                                if ($loan_detail->loan_type == 2) {
                                    $loan_type = 'Staff Loan';
                                }
                                if ($loan_detail->loan_type == 4) {
                                    $loan_type = 'Loan Against Investment Plan(DL)';
                                }
                            }
                            if ($val->type == 7 || $val->type == 8) {
                                $loan_detail = getGroupLoanDetail($val->type_id);
                                $account = $loan_detail->account_number;
                                $loan_type = 'Group Loan';
                            }
                            $data[$key]['account'] = $account;
                            $data[$key]['loan_type'] = $loan_type;
                            $data[$key]['total_amount'] = number_format((float) $val->total_amount, 2, '.', '');
                            $data[$key]['commission_amount'] = number_format((float) $val->commission_amount, 2, '.', '');
                            $data[$key]['percentage'] = number_format((float) $val->percentage, 2, '.', '');
                            $carder_name = getCarderName($val->carder_id);
                            $data[$key]['carder_name'] = $carder_name;
                            $commission_for = '';
                            if ($val->type == 4) {
                                $commission_for = 'Loan Commission';
                            }
                            if ($val->type == 6) {
                                $commission_for = 'Loan Collection';
                            }
                            if ($val->type == 7) {
                                $commission_for = 'Group Loan Commission';
                            }
                            if ($val->type == 8) {
                                $commission_for = 'Group Loan Collection';
                            }
                            $data[$key]['commission_type'] = $commission_for;
                            $pay_type = '';
                            if ($val->pay_type == 4) {
                                $pay_type = 'Loan Emi';
                            } elseif ($val->pay_type == 5) {
                                $pay_type = 'Loan Panelty';
                            }
                            $data[$key]['pay_type'] = $pay_type;
                            if ($val->is_distribute == 1) {
                                $is_distribute = 'Yes';
                            } else {
                                $is_distribute = 'No';
                            }
                            $data[$key]['is_distribute'] = $is_distribute;
                            $created_at = date("d/m/Y", strtotime($val->created_at));
                            $data[$key]['created_at'] = $created_at;
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'loan commission listing!';
                        $page = $request->page;
                        $length = $request->length;
                        $result = ['associate_com' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } elseif ($request->page == 0) {
                        $data = array();
                        //dd($request->ledger_id,$request->member_id);
                        $commisionList = CommissionLeaserDetail::where('commission_leaser_id', $request->ledger_id)->where('member_id', $request->member_id)->first();
                        $comId = explode(",", $commisionList->commission_id);
                        $comm_data = AssociateCommission::with('investment')->whereIN('id', $comId)->whereIn('type', array(4, 6, 7, 8))->orderby('id', 'DESC');
                        $comm_data1 = $comm_data->get();
                        $count = count($comm_data1);
                        // if($request->page==1)
                        // {
                        //     $start=0;
                        // }
                        // else
                        // {
                        //     $start=($request->page-1)*$request->length;
                        // }
                        $comm_data = $comm_data->get();
                        foreach ($comm_data as $key => $val) {
                            $data[$key]['id'] = $val->id;
                            $account = 'N/A';
                            $loan_type = 'N/A';
                            if ($val->type == 4 || $val->type == 6) {
                                $loan_detail = getLoanDetail($val->type_id);
                                // print_r($loan_detail->loan_type);die;
                                $account = $loan_detail->account_number;
                                $loan_type = 'Loan';
                                if ($loan_detail->loan_type == 1) {
                                    $loan_type = 'Personal Loan';
                                }
                                if ($loan_detail->loan_type == 2) {
                                    $loan_type = 'Staff Loan';
                                }
                                if ($loan_detail->loan_type == 4) {
                                    $loan_type = 'Loan Against Investment Plan(DL)';
                                }
                            }
                            if ($val->type == 7 || $val->type == 8) {
                                $loan_detail = getGroupLoanDetail($val->type_id);
                                $account = $loan_detail->account_number;
                                $loan_type = 'Group Loan';
                            }
                            $data[$key]['account'] = $account;
                            $data[$key]['loan_type'] = $loan_type;
                            $data[$key]['total_amount'] = number_format((float) $val->total_amount, 2, '.', '');
                            $data[$key]['commission_amount'] = number_format((float) $val->commission_amount, 2, '.', '');
                            $data[$key]['percentage'] = number_format((float) $val->percentage, 2, '.', '');
                            $carder_name = getCarderName($val->carder_id);
                            $data[$key]['carder_name'] = $carder_name;
                            $commission_for = '';
                            if ($val->type == 4) {
                                $commission_for = 'Loan Commission';
                            }
                            if ($val->type == 6) {
                                $commission_for = 'Loan Collection';
                            }
                            if ($val->type == 7) {
                                $commission_for = 'Group Loan Commission';
                            }
                            if ($val->type == 8) {
                                $commission_for = 'Group Loan Collection';
                            }
                            $data[$key]['commission_type'] = $commission_for;
                            $pay_type = '';
                            if ($val->pay_type == 4) {
                                $pay_type = 'Loan Emi';
                            } elseif ($val->pay_type == 5) {
                                $pay_type = 'Loan Panelty';
                            }
                            $data[$key]['pay_type'] = $pay_type;
                            if ($val->is_distribute == 1) {
                                $is_distribute = 'Yes';
                            } else {
                                $is_distribute = 'No';
                            }
                            $data[$key]['is_distribute'] = $is_distribute;
                            $created_at = date("d/m/Y", strtotime($val->created_at));
                            $data[$key]['created_at'] = $created_at;
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'loan commission listing!';
                        $result = ['associate_com' => $data, 'total_count' => $count, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    function associteTreeDetail($parent_id, $tree_array, $carder = NULL)
    {
        $categories = associateTreeid($parent_id, $carder);
        foreach ($categories as $item) {
            $associate_code = $item['member']->associate_no;
            $associate_name = $item['member']->first_name . ' ' . $item['member']->last_name ?? '';
            $branchName = $item['member'] ? $item['member']['associate_branch'] ? $item['member']['associate_branch']->name : 'N/A' : 'N/A';
            $associate_carder = getCarderName($item['member']->current_carder_id);
            $senior_name = getSeniorData($item->member->associate_senior_id, 'first_name') . ' ' . getSeniorData($item->member->associate_senior_id, 'last_name');
            $senior_code = getSeniorData($item->member->associate_senior_id, 'associate_no');
            $senior_carder = getCarderName(getSeniorData($item->member->associate_senior_id, 'current_carder_id'));
            if ($item->member->is_block == 0) {
                if ($item->member->associate_status == 1) {
                    $status_name = 'Active';
                    $is_inactive = 0;
                } else {
                    $status_name = 'Inactive';
                    $is_inactive = 1;
                }
            } else {
                $status_name = 'Blocked';
                $is_inactive = 1;
            }
            $tree_array[] = [
                'id' => $item['member']->id,
                'associate_code' => $associate_code,
                'associate_name' => $associate_name,
                'associate_carder' => $associate_carder,
                'senior_name' => $senior_name,
                'senior_code' => $senior_code,
                'senior_carder' => $senior_carder,
                'status_name' => $status_name,
                'is_inactive' => $is_inactive,
                'branch_name' => $branchName
            ];
            if (count($item->subcategory) != 0) {
                $tree_array = $this->associteTreeDetail($item->member_id, $tree_array, $carder);
            }
        }
        return $tree_array;
    }
    public function associateTreeList(Request $request)
    {
        $associate_no = $request->associate_no;
        $page = $request->page;
        $length = $request->length;
        $count = '0';
        try {
            $member = Member::select('id', 'associate_app_status', 'associate_no')
                ->where('associate_no', $request->associate_no)
                ->where('associate_status', 1)
                ->where('is_block', 0)
                ->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->id > 0) {
                        $data = array();
                        $id = $member->id;
                        $a = $this->getCategoryTree($id, $tree_array = array());
                        $b[] = array('member_id' => $id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            if ($v['status'] == 1 && $v['is_block'] == 0) {
                                $tree_array1[] = $v['member_id'];
                            }
                        }
                        $member_data = Member::where('associate_no', $request->id)->first();
                        $recordExists = in_array($member_data->id, $tree_array1);
                        if ($recordExists) {
                            $associate_code = $member_data->associate_no;
                            $branchName = $member_data->branch ? $member_data->branch->name : 'N/A';
                            $associate_name = $member_data->first_name . ' ' . $member_data->last_name;
                            $associate_carder = getCarderName($member_data->current_carder_id);
                            $senior_name = getSeniorData($member_data->associate_senior_id, 'first_name') . ' ' . getSeniorData($member_data->associate_senior_id, 'last_name');
                            $senior_code = getSeniorData($member_data->associate_senior_id, 'associate_no');
                            if ($member_data->associate_no == $associate_no) {
                                $senior_carder = '';
                            } else {
                                $senior_carder = getCarderName(getSeniorData($member_data->associate_senior_id, 'current_carder_id'));
                            }
                            if ($member_data->is_block == 0) {
                                if ($member_data->associate_status == 1) {
                                    $status_name = 'Active';
                                    $is_inactive = 0;
                                } else {
                                    $status_name = 'Inactive';
                                    $is_inactive = 1;
                                }
                            } else {
                                $status_name = 'Blocked';
                                $is_inactive = 1;
                            }
                            $tree_array[] = ['id' => $member_data->id, 'associate_code' => $associate_code, 'associate_name' => $associate_name, 'associate_carder' => $associate_carder, 'senior_name' => $senior_name, 'senior_code' => $senior_code, 'senior_carder' => $senior_carder, 'status_name' => $status_name, 'is_inactive' => $is_inactive, 'branch_name' => $branchName];
                            $a = $this->associteTreeDetail($member_data->id, $tree_array);
                            $count = (string) count($a);
                            $status = "Success";
                            $code = 200;
                            $messages = 'Tree listing!';
                            $page = $request->page;
                            $length = $request->length;
                            $result = ['tree_list' => $a, 'total_count' => $count, 'page' => $page, 'length' => $length];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                        } else {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Associate not found';
                            $result = ['tree_list' => array(), 'total_count' => $count, 'page' => $page, 'length' => $length];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Associate id not selected!';
                        $result = ['tree_list' => array(), 'total_count' => $count, 'page' => $page, 'length' => $length];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['tree_list' => array(), 'total_count' => $count, 'page' => $page, 'length' => $length];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['tree_list' => array(), 'total_count' => $count, 'page' => $page, 'length' => $length];
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['tree_list' => array(), 'total_count' => $count, 'page' => $page, 'length' => $length];
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    function associteTreeViewDetail($parent_id, $tree_array)
    {
        $categories = associateTreeid($parent_id);
        foreach ($categories as $item) {
            $associate_code = $item['member']->associate_no;
            $associate_name = $item['member']->first_name . ' ' . $item['member']->last_name;
            $associate_carder = getCarderName($item['member']->current_carder_id);
            if ($item->member->is_block == 0) {
                if ($item->member->associate_status == 1) {
                    $status_name = 'Active';
                    $is_inactive = 0;
                } else {
                    $status_name = 'Inactive';
                    $is_inactive = 1;
                }
            } else {
                $status_name = 'Blocked';
                $is_inactive = 1;
            }
            $tree_array[] = ['id' => $item['member']->id, 'associate_code' => $associate_code, 'associate_name' => $associate_name, 'associate_carder' => $associate_carder, 'status_name' => $status_name, 'is_inactive' => $is_inactive, 'senior_id' => $item->member->associate_senior_id, 'carder_id' => $item['member']->current_carder_id, 'child' => $this->associteTreeViewDetail($item->member_id, $tree_array1 = array())];
            //$tree_array = $this->associteTreeViewDetail($item->member_id, $tree_array);            
        }
        return $tree_array;
    }
    function associteTreeViewDetail1($parent_id, $cader, $tree_array = array())
    {
        $categories = associateTreeCarder($parent_id, $cader);
        foreach ($categories as $item) {
            $associate_code = $item['member']->associate_no;
            $associate_name = $item['member']->first_name . ' ' . $item['member']->last_name;
            $associate_carder = getCarderName($item['member']->current_carder_id);
            if ($item->member->is_block == 0) {
                if ($item->member->associate_status == 1) {
                    $status_name = 'Active';
                    $is_inactive = 0;
                } else {
                    $status_name = 'Inactive';
                    $is_inactive = 1;
                }
            } else {
                $status_name = 'Blocked';
                $is_inactive = 1;
            }
            $tree_array[] = ['id' => $item['member']->id, 'associate_code' => $associate_code, 'associate_name' => $associate_name, 'associate_carder' => $associate_carder, 'status_name' => $status_name, 'is_inactive' => $is_inactive, 'senior_id' => 0, 'carder_id' => $item['member']->current_carder_id, 'child' => $this->associteTreeViewDetail($item->member_id, $tree_array1 = array())];
            //$tree_array = $this->associteTreeViewDetail($item->member_id, $tree_array);            
        }
        return $tree_array;
    }
    public function associateTreeView(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->id > 0) {
                        $carder = array();
                        //
                        $member_data = Member::where('id', $request->id)->first();
                        // print_r($member_data);die;
                        if ($member_data) {
                            $associate_code = $member_data->associate_no;
                            $associate_name = $member_data->first_name . ' ' . $member_data->last_name;
                            $associate_carder = getCarderName($member_data->current_carder_id);
                            $senior_name = getSeniorData($member_data->associate_senior_id, 'first_name') . ' ' . getSeniorData($member_data->associate_senior_id, 'last_name');
                            $senior_code = getSeniorData($member_data->associate_senior_id, 'associate_no');
                            $senior_carder = getCarderName(getSeniorData($member_data->associate_senior_id, 'current_carder_id'));
                            if ($member_data->is_block == 0) {
                                if ($member_data->associate_status == 1) {
                                    $status_name = 'Active';
                                    $is_inactive = 0;
                                } else {
                                    $status_name = 'Inactive';
                                    $is_inactive = 1;
                                }
                            } else {
                                $status_name = 'Blocked';
                                $is_inactive = 1;
                            }
                            $main_assocaite = ['id' => $member_data->id, 'associate_code' => $associate_code, 'associate_name' => $associate_name, 'associate_carder' => $associate_carder, 'status_name' => $status_name, 'is_inactive' => $is_inactive];
                            for ($i = 1; $i <= $member_data->current_carder_id; $i++) {
                                $a = $this->associteTreeViewDetail1($parent_id = $member_data->id, $i);
                                $c_name = 'Carder ' . $i;
                                $carder[] = ['carder_id' => $i, 'carder_name' => $c_name, 'data' => $a];
                            }
                            $status = "Success";
                            $code = 200;
                            $messages = 'Tree listing!';
                            $page = $request->page;
                            $length = $request->length;
                            $result = ['associate' => $main_assocaite, 'tree_data' => $carder];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                        } else {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Associate not found';
                            $result = '';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Associate id not selected!';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function associate_Ssb_Current_Balance(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $test = 1;
            //Get member table to associate id and ssb account details
            $data = Member::where('associate_no', $associate_no)->first();
            // dd($data->id);
            //associate Account Number
            $accountNumber = SavingAccount::where('associate_id', $data->id)->first();
            if ($accountNumber == null) {
                $status = "Error";
                $code = 201;
                $messages = 'Associate ssb account not found!';
                $Currentbalance = NULL;
                return response()->json(compact('status', 'Currentbalance', 'messages', 'code'));
            }
            //Get all deposit total amount of this particular associate account
            $data2 = SavingAccountTranscation::where('account_no', $accountNumber->account_no)->where('is_deleted', 0)->sum('deposit');
            //Get all Withdrawal total amount of this particular associate account
            $data3 = SavingAccountTranscation::where('account_no', $accountNumber->account_no)->where('is_deleted', 0)->sum('withdrawal');
            //Current Balance of Assoicate
            $Currentbalance = number_format((float) $data2 - $data3, 2, '.', '');
            if (!empty($data)) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if (!empty($data2)) {
                        $status = "Success";
                        $code = 200;
                        $messages = 'Get Associate account successfully';
                        $Currentbalance = $Currentbalance;
                        $associate_status = $data->associate_status;
                        return response()->json(compact('status', 'Currentbalance', 'associate_status', 'messages', 'code'));
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Associate not found';
                        $Currentbalance = NULL;
                        return response()->json(compact('status', 'Currentbalance', 'messages', 'code'));
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API Token not found!';
                    $account_detail = '';
                    $associate_status = $data->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'account_detail', 'associate_status'), $code);
                }
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $Currentbalance = NULL;
            return response()->json(compact('status', 'Currentbalance', 'messages', 'code'));
        }
    }
    public function associateSsbDetails(Request $request)
    {
        $associate_no = $request->associate_no;
        $page = $request->page;
        $length = $request->length;
        $token = $request->token;
        try {
            $test = 1;
            //Get member table to associate id and ssb account details
            $data = Member::where('associate_no', $associate_no)->first();
            if ($request->page == 1) {
                $start = 0;
            } else {
                $start = ($request->page - 1) * $request->length;
            }
            //associate Account Number
            $data2 = SavingAccount::whereHas('company')->with(['getMemberinvestments.getPlanCustom', 'getSSBAccountBalance'])->where('customer_id', $data->id)->orderby('id', 'DESC');
            $count = $data2->count('id');
            $data2 = $data2->offset($start)->limit($request->length)->get();
            if (!empty($data)) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if (empty($page)) {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please enter Page number!';
                        $data = NULL;
                        return response()->json(compact('status', 'data', 'messages', 'code'));
                    }
                    if (empty($length)) {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please enter Page length!';
                        $data = NULL;
                        return response()->json(compact('status', 'data', 'messages', 'code'));
                    }
                    if (!empty($data2)) {
                        $dataNew = [];
                        foreach ($data2 as $value) {
                            $ssbdata = [
                                'account_number' => $value->account_no,
                                'opening_date' => date("d-m-Y", strtotime(convertDate($value->created_at))),
                                'deno_amount' => isset($value['getMemberinvestments']['deposite_amount']) ? $value['getMemberinvestments']['deposite_amount'] : 0,
                                // current_balance comming from View no need to check CR - DR on this balance
                                'current_balance' => isset($value['getSSBAccountBalance']['totalBalance']) ? $value['getSSBAccountBalance']['totalBalance'] : 0,
                                'plan' => isset($value['getMemberinvestments']['getPlanCustom']['name']) ? $value['getMemberinvestments']['getPlanCustom']['name'] : '',
                            ];
                            array_push($dataNew, $ssbdata);
                        }
                        $result = [
                            'ssb_data' => $dataNew,
                            'page' => (string) $page,
                            'length' => (string) $length,
                            'total_count' => (string) $count,
                            'record_count' => (string) count($data2),
                        ];
                        // array_push($dataNew,['total_count'=>count($data2),'page'=>$page,'length'=>$length,'record_count'=>count($data2)]);
                        $status = "Success";
                        $code = 200;
                        $messages = 'Investment listing!';
                        $result = $result;
                        $associate_status = $data['associate_status'] ?? 'NULL';
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'));
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Associate not found';
                        $result = [];
                        return response()->json(compact('status', 'result', 'messages', 'code'));
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API Token not found!';
                    $account_detail = '';
                    $associate_status = $data->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'account_detail', 'associate_status'), $code);
                }
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $Currentbalance = NULL;
            return response()->json(compact('status', 'Currentbalance', 'messages', 'code'));
        }
    }
    public function associate_detail_list(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::where('associate_no', $associate_no)
                ->where('associate_status', 1)
                ->where('is_block', 0)
                ->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $id = $member->id;
                    $a = $this->getCategoryTree($id, $tree_array = array());
                    $b[] = array('member_id' => $id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        if ($v['status'] == 1 && $v['is_block'] == 0) {
                            $tree_array1[] = $v['member_id'];
                        }
                    }
                    $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                    $member_data1 = $member_data->orderByRaw("id = " . $id . " DESC")->get();
                    $count = count($member_data1);
                    if ($request->page == 1) {
                        $start = 0;
                    } else {
                        if (isset($request->length)) {
                            $start = ($request->page - 1) * $request->length;
                        }
                    }
                    $member_data = isset($request->length) ? $member_data->orderByRaw("id = " . $id . " DESC")->offset($start)->limit($request->length)->get() : $member_data->orderByRaw("id = " . $id . " DESC")->get();
                    foreach ($member_data as $key => $val) {
                        $data[$key]['id'] = $val->id;
                        $data[$key]['associate_no'] = $val->associate_no;
                        $data[$key]['name'] = $val->first_name . ' ' . $val->last_name;
                    }
                    $status = "Success";
                    $code = 200;
                    $messages = 'Associate Detail!';
                    $associate = $data;
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'associate', 'token', 'associate_status'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $associate = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'associate', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $associate = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'associate', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $associate = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'associate', 'associate_status'), $code);
        }
    }
    public function associate_ssb_transaction(Request $request)
    {
        $associate_no = $request->associate_no;
        $account_no = $request->account_number;
        $eliOpeningAmount = 0.00;
        $accountsNumber = array('R-066523000256', 'R-084511001514', 'R-066523000257', 'R-066523000620', 'R-084523000800', 'R-066523000510', 'R-066523000258', 'R-084504006762', 'R-066504006287', 'R-066523000509', 'R-066523000588', 'R-084504007930');
        try {
            $member = Member::where('associate_no', $associate_no)
                ->where('associate_status', 1)
                ->where('is_block', 0)
                ->first();
            if ($member) {
                $token = md5($associate_no);
                if ($token == $request->token) {
                    $investPlan = Memberinvestments::with('getPlanCustom')->select('plan_id', 'account_number', 'deposite_amount')->where('account_number', $account_no)->first();
                    if ($request->type == 'S') {
                        $data = SavingAccountTransactionView::where('account_no', $account_no)->orderBy('opening_date', 'DESC')->orderBy('id', 'desc')->get();
                        foreach ($data as $key => $val) {
                            $data[$key]['id'] = $val->id;
                            $data[$key]['saving_account_id'] = $val->saving_account_id;
                            $data[$key]['branch_id'] = null;
                            $data[$key]['associate_id'] = null;
                            $data[$key]['account_no'] = $val->account_no;
                            $data[$key]['type'] = 5;
                            $data[$key]['opening_balance'] = $val->opening_balance;
                            $data[$key]['deposit'] = $val->deposit;
                            $data[$key]['withdrawal'] = $val->withdrawal;
                            $data[$key]['description'] = $val->description;
                            $data[$key]['reference_no'] = null;
                            $data[$key]['amount'] = $val->amount;
                            $data[$key]['currency_code'] = "INR";
                            $data[$key]['payment_type'] = null;
                            $data[$key]['payment_mode'] = null;
                            $data[$key]['is_renewal'] = null;
                            $data[$key]['status'] = null;
                            $data[$key]['is_deleted'] = 0;
                            $data[$key]['created_at'] = $val->opening_date;
                            $data[$key]['updated_at'] = $val->opening_date;
                            $data[$key]['app_login_user_id'] = null;
                            $data[$key]['is_app'] = null;
                            $data[$key]['jv_journal_id'] = null;
                            $data[$key]['created_at_default'] = $val->transaction_default_date;
                            $data[$key]['daybook_ref_id'] = null;
                            $data[$key]['debit_card_transaction_id'] = null;
                            $data[$key]['company_id'] = null;
                        }
                        foreach ($data as $key => $value) {
                            if ($value->amount == '') {
                                $data[$key]['amount'] = '0.00';
                            }
                            if ($value->deposit == '') {
                                $data[$key]['deposit'] = '0.00';
                            }
                            if ($value->withdrawal == '') {
                                $data[$key]['withdrawal'] = '0.00';
                            }
                        }
                    }

                    $status = "Success";
                    $code = 200;
                    $messages = 'Investment transactions listing!';
                    $result = $data ? ['transactions' => $data] : '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
        }
    }
    public function view_trasaction_deatils(Request $request)
    {
        $associate_no = $request->associate_no;
        $account_no = $request->account_number;
        $mId = $request->member_id;
        $tId = $request->transaction_id;
        $type = $request->type;
        try {
            $member = Member::where('associate_no', $associate_no)
            ->where('associate_status', 1)
            ->where('is_block', 0)
            ->first();
            if ($member) {
                $token = md5($associate_no);
                if ($token == $request->token) {
                    $data = array();
                    if ($type == 'deposit' || $type == 'ssb') {
                        $investPlan = Memberinvestments::with('plan')->select('plan_id', 'deposite_amount', 'due_amount', 'associate_id', 'branch_id', 'created_at', 'member_id', 'tenure', 'id', 'account_number')->with('branch')->where('account_number', $account_no)->first();
                        if (isset($investPlan->id)) {
                            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $investPlan['branch']->state_id);
                            $globaldate = date("Y-m-d", strtotime(convertDate($globaldate)));
                            if ($investPlan['plan']['plan_category_code'] == "S") {
                                $data['tDetails'] = SavingAccountTransactionView::where('transaction_id', $tId)->first();
                                $mId = SavingAccount::select('member_investments_id', 'member_id')->where('account_no', $data['tDetails']->account_no)->first();
                                $aId = Memberinvestments::select('associate_id')->where('id', $mId->member_investments_id)->where('is_deleted', 0)->first();
                                $data['tDetails']['member_id'] = $mId->member_id;
                                $data['tDetails']['associate_id'] = $aId->associate_id;
                                if ($data['tDetails']->amount == '') {
                                    $data['tDetails']['amount'] = $data['tDetails']->deposit;
                                }
                            } else {
                                $data['tDetails'] = Daybook::where('id', $tId)->first();
                            }
                            $datane = MemberCompany::select('customer_id', 'associate_id')->find($data['tDetails']->member_id)->toArray();
                            $memberDetail = Member::find($datane['customer_id']);
                            // dd($memberDetail);
                            $associateDetail = Member::find($investPlan->associate_id);
                            $data['tDetails']['deo_amount'] = $investPlan->deposite_amount;
                            $data['tDetails']['user_name'] = $memberDetail->first_name . ' ' . $memberDetail->last_name;
                            $data['tDetails']['associate_name'] = $associateDetail->first_name . ' ' . $associateDetail->last_name;
                            $data['tDetails']['associate_code'] = $associateDetail->associate_no;
                            $data['tDetails']['plan_id'] = $investPlan->plan_id;
                            //  $dueAmount = CommanController::getRecords($investPlan,$globaldate);
                            $currentDate = Carbon::now();
                            $record = $this->getRecords($investPlan, $currentDate);
                            $pendingEmiAMount = 0;
                            if (isset($record['pendingEmiAMount'])) {
                                $pendingEmiAMount = $record['pendingEmiAMount'];
                                if ($record['pendingEmiAMount'] > 0) {
                                    $pendingEmiAMount = 0;
                                }
                            }
                            $pendingEmiAMount = str_replace('-', '', $pendingEmiAMount);
                            $data['tDetails']['due_amount'] = number_format((float) $pendingEmiAMount, 2, '.', '');
                            $Status = 'Success';
                            $Code = 200;
                            $msg = 'Transaction details!';
                        } else {
                            $Status = 'Failure';
                            $Code = 201;
                            $msg = 'No Record Found';
                        }
                    }

                    $status = $Status;
                    $code = $Code;
                    $messages = $msg;
                    $result = $data ? ['transactionDetail' => $data] : '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
        }
    }
    public static function getRecords($dailyDepositeRecord, $currentDate)
    {
        switch ($dailyDepositeRecord['plan_id']) {
            case '7':
                $pendingEmiAMount = 0;
                $pendingEmi = 0;
                $investCreatedDate = strtotime($dailyDepositeRecord['created_at']);
                $CURRENTDATE = strtotime($currentDate);
                $totalBetweendays = abs($investCreatedDate - $CURRENTDATE);
                $totalBetweendays = ceil(floatval($totalBetweendays / 86400));
                $totalAmount = ($totalBetweendays + 1) * $dailyDepositeRecord['deposite_amount'];
                $getRenewalReceivedAmount = Daybook::whereIn('transaction_type', [2, 4])->where('account_no', $dailyDepositeRecord['account_number'])->sum('deposit');
                if ($getRenewalReceivedAmount != $totalAmount) {
                    $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount;
                    $pendingEmi =  $pendingEmiAMount / $dailyDepositeRecord['deposite_amount'];
                } else {
                    $pendingEmiAMount = 0;
                    $pendingEmi =  0;
                }
                return ['pendingEmiAMount' => $pendingEmiAMount, 'pendingEmi' => $pendingEmi];
                break;
            case '2':
            case '3':
            case '5':
            case '7':
            case '10':
            case '11':
                $pendingEmiAMount = 0;
                $pendingEmi = 0;
                $investCreatedDate = Carbon\Carbon::parse($dailyDepositeRecord['created_at']);
                $totalBetweenmonth = $investCreatedDate->diffInMonths($currentDate);
                $totalAmount = ($totalBetweenmonth + 1) *  $dailyDepositeRecord['deposite_amount'];
                $getRenewalReceivedAmount = Daybook::whereIn('transaction_type', [2, 4])->where('account_no', $dailyDepositeRecord['account_number'])->sum('deposit');
                if ($getRenewalReceivedAmount != $totalAmount) {
                    $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount;
                    $pendingEmi =  $pendingEmiAMount / $dailyDepositeRecord['deposite_amount'];
                } else {
                    $pendingEmiAMount = 0;
                    $pendingEmi =  0;
                }
                return ['pendingEmiAMount' => $pendingEmiAMount, 'pendingEmi' => $pendingEmi];
                break;
        }
    }
}