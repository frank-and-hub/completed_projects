<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Member;
use App\Models\Memberinvestments;
use App\Models\Daybook;
use App\Models\SavingAccountTranscation;
use App\Models\SavingAccount;
use App\Models\SavingAccountBalannce;
use Carbon\Carbon;
use App\Models\Companies;
use App\Services\ImageUpload;

class AssociateMemberController extends Controller
{

    /**
     * Fetch member investments.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getCategoryTree($parent_id, $spacing = '', $tree_array = array())
    {
        $categories = associateTreeid($parent_id);

        foreach ($categories as $item) {
            $tree_array[] =   ['member_id' => $item->member_id, 'status' => $item['member']->associate_status, 'is_block' => $item['member']->is_block];
            $tree_array = $this->getCategoryTree($item->member_id, $spacing . '--', $tree_array);
        }
        return $tree_array;
    }


    public function member_list(Request $request)
    {
        $associate_no = $request->associate_no;

        try {

            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();

            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0 && $request->length > 0) {
                        $data = array();

                        // $a = $this->getCategoryTree($parent_id = $member->id, $spacing = '', $tree_array = array());
                        // $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        // $tree_array1 = array();
                        // $c = array_merge($a, $b);
                        // foreach ($c as $v) {
                        //     if ($v['status'] == 1 && $v['is_block'] == 0) {
                        //         $tree_array1[] =  $v['member_id'];
                        //     }
                        // }

                        $member_data = Member::with('branch')->where('associate_id', $member->id);

                        if ($request->from_date != '') {
                            $startDate = date("Y-m-d", strtotime(convertDate($request->from_date)));
                            if ($request->to_date != '') {
                                $endDate = date("Y-m-d ", strtotime(convertDate($request->to_date)));
                            } else {
                                $endDate = '';
                            }
                            $member_data = $member_data->whereBetween(\DB::raw('DATE(re_date)'), [$startDate, $endDate]);
                        }
                        if ($request->member_id != '') {
                            $member_id = $request->member_id;
                            $member_data = $member_data->where('member_id', '=', $member_id);
                        }
                        if ($request->associate_id != '') {
                            $associate_id = $request->associate_id;
                            $member_data = $member_data->where('associate_id', $associate_id);
                        }

                        $member_data1 = $member_data->orderby('re_date', 'DESC')->get();
                        $count = count($member_data1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }

                        $member_data = $member_data->orderby('re_date', 'DESC')->offset($start)->limit($request->length)->get();

                        foreach ($member_data as $key => $value) {
                            $data[$key]['id'] = $value->id;
                            $data[$key]['member_join_date'] =  date("d/m/Y", strtotime(str_replace('-', '/', $value->re_date)));
                            $data[$key]['branch_code'] = $value['branch']->branch_code;
                            $data[$key]['branch_name'] = $value['branch']->name;
                            $data[$key]['sector_name'] = $value['branch']->sector;
                            $data[$key]['region'] = $value['branch']->regan;
                            $data[$key]['member_id'] = $value->member_id;
                            $data[$key]['name'] = $value->first_name . ' ' . $value->last_name;
                            $data[$key]['associate_code'] = getSeniorData($value->associate_id, 'associate_no');
                            $data[$key]['associate_name'] = getSeniorData($value->associate_id, 'first_name') . ' ' . getSeniorData($value->associate_id, 'last_name');

                            $data[$key]['address'] = $value->address;
                            $idProofDetail = \App\Models\MemberIdProof::where('member_id', $value->id)->first();
                            $data[$key]['firstId'] = getIdProofName($idProofDetail->first_id_type_id) . ' - ' . $idProofDetail->first_id_no;
                            $data[$key]['secondId'] = getIdProofName($idProofDetail->second_id_type_id) . ' - ' . $idProofDetail->second_id_no;

                            if ($value->is_block == 1) {
                                $status = 'Blocked';
                            } else {
                                if ($value->status == 1) {
                                    $status = 'Active';
                                } else {
                                    $status = 'Inactive';
                                }
                            }
                            $data[$key]['status'] = $status;
                        }

                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Member listing!';
                        $page  = $request->page;
                        $length  = $request->length;
                        $result   = ['member' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;

                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } elseif ($request->page  == 0) {
                        $data = array();
                        
                        $a = $this->getCategoryTree($parent_id = $member->id, $spacing = '', $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            if ($v['status'] == 1 && $v['is_block'] == 0) {
                                $tree_array1[] =  $v['member_id'];
                            }
                        }

                        $member_data = Member::with('branch')->whereIn('associate_id', $tree_array1);

                        if ($request->from_date != '') {
                            $startDate = date("Y-m-d", strtotime(convertDate($request->from_date)));
                            if ($request->to_date != '') {
                                $endDate = date("Y-m-d ", strtotime(convertDate($request->to_date)));
                            } else {
                                $endDate = '';
                            }
                            $member_data = $member_data->whereBetween(\DB::raw('DATE(re_date)'), [$startDate, $endDate]);
                        }
                        if ($request->member_id != '') {
                            $member_id = $request->member_id;
                            $member_data = $member_data->where('member_id', '=', $member_id);
                        }
                        if ($request->associate_id != '') {
                            $associate_id = $request->associate_id;
                            $member_data = $member_data->where('associate_id', $associate_id);
                        }

                        $member_data1 = $member_data->orderby('re_date', 'DESC')->get();
                        $count = count($member_data1);
                        // if($request->page==1)
                        // {
                        //     $start=0;
                        // }
                        // else
                        // {
                        //     $start=($request->page-1)*$request->length;
                        // }

                        $member_data = $member_data->orderby('re_date', 'DESC')/*->offset($start)->limit($request->length)*/->get();

                        foreach ($member_data as $key => $value) {
                            $data[$key]['id'] = $value->id;
                            $data[$key]['member_join_date'] =  date("d/m/Y", strtotime(str_replace('-', '/', $value->re_date)));
                            $data[$key]['branch_code'] = $value['branch']->branch_code;
                            $data[$key]['branch_name'] = $value['branch']->name;
                            $data[$key]['sector_name'] = $value['branch']->sector;
                            $data[$key]['member_id'] = $value->member_id;
                            $data[$key]['name'] = $value->first_name . ' ' . $value->last_name;
                            $data[$key]['associate_code'] = getSeniorData($value->associate_id, 'associate_no');
                            $data[$key]['associate_name'] = getSeniorData($value->associate_id, 'first_name') . ' ' . getSeniorData($value->associate_id, 'last_name');

                            $data[$key]['address'] = $value->address;
                            $idProofDetail = \App\Models\MemberIdProof::where('member_id', $value->id)->first();
                            $data[$key]['firstId'] = getIdProofName($idProofDetail->first_id_type_id) . ' - ' . $idProofDetail->first_id_no;
                            $data[$key]['secondId'] = getIdProofName($idProofDetail->second_id_type_id) . ' - ' . $idProofDetail->second_id_no;

                            if ($value->is_block == 1) {
                                $status = 'Blocked';
                            } else {
                                if ($value->status == 1) {
                                    $status = 'Active';
                                } else {
                                    $status = 'Inactive';
                                }
                            }
                            $data[$key]['status'] = $status;
                        }

                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Member listing!';

                        $result   = ['member' => $data, 'total_count' => $count, 'record_count' => count($data)];
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




    public function member_match(Request $request)
    {


        try {
            $associate_chk = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($associate_chk) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {


                    $member = Member::select('id', 'status', 'is_block')->where('member_id', $request->member_id)->first();
                    if ($member) {
                        if ($member->is_block == 0 && $member->status == 1) {
                            $status   = "Success";
                            $code     = 200;
                            $messages = 'Member id match !';
                            $result   = '';
                            $associate_status = $associate_chk->associate_app_status;

                            return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                        } else {
                            if ($member->is_block == 1) {
                                $messages = 'Member id blocked!';
                            } else {
                                if ($member->status == 0) {
                                    $messages = 'Member id inactive!';
                                }
                            }
                            $status = "Error";
                            $code = 201;

                            $result = '';
                            $associate_status = $associate_chk->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Member id not match!';
                        $result = '';
                        $associate_status = $associate_chk->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $associate_chk->associate_app_status;
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




    public function active_associate_list(Request $request)
    {
        $associate_no = $request->associate_no;

        try {

            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();

            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {

                    $data = array();

                    $a = $this->getCategoryTree($parent_id = $member->id, $spacing = '', $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);

                    foreach ($c as $v) {
                        if ($v['status'] == 1 && $v['is_block'] == 0) {
                            $tree_array1[] =  $v['member_id'];
                        }
                    }
                    //$member_data = Member::whereIn('id',$tree_array1)->where('associate_status',1)->where('is_block',0);

                    $member_data = Member::where('id', $member->id)->where('associate_status', 1)->where('is_block', 0);

                    $member_data = $member_data->orderby('associate_join_date', 'DESC')->get();

                    foreach ($member_data as $key => $value) {
                        $data[$key]['id'] = $value->id;
                        $data[$key]['associate_no'] = $value->associate_no;
                        $data[$key]['name'] = $value->first_name . ' ' . $value->last_name;
                    }

                    // Get Company List
                    $company =  Companies::select('id', 'name', 'short_name')->where('status', '1')->where('delete', '0')->get();
                    $rowReturn = array();
                    foreach ($company as $key) {
                        $val['id'] = $key->id;
                        $val['name'] = $key->name;
                        $val['short_name'] = $key->short_name;

                        $rowReturn[] = $val;
                    }

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Associate listing!';
                    $result   = ['associate_list' => $data,'company_list' => $rowReturn, 'total_count' => count($member_data)];
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
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }

    public function member_detail(Request $request)
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
                        $memberData = \App\Models\Member::where('id', $id)->first();
                        $bankDetail = \App\Models\MemberBankDetail::where('member_id', $id)->first();
                        $data['bankDetail']['id'] = 0;
                        $data['bankDetail']['member_id'] = 0;
                        $data['bankDetail']['bank_name'] = '';
                        $data['bankDetail']['branch_name'] = '';
                        $data['bankDetail']['account_no'] = '';
                        $data['bankDetail']['ifsc_code'] = '';
                        $data['bankDetail']['address'] = '';
                        if ($bankDetail) {
                            // print_r($data['bankDetail']);die;
                            $data['bankDetail']['id'] = $bankDetail->id;
                            $data['bankDetail']['member_id'] = $bankDetail->member_id;
                            $data['bankDetail']['bank_name'] = $bankDetail->bank_name;
                            $data['bankDetail']['branch_name'] = $bankDetail->branch_name;
                            $data['bankDetail']['account_no'] = $bankDetail->account_no;
                            $data['bankDetail']['ifsc_code'] = $bankDetail->ifsc_code;
                            $data['bankDetail']['address'] = $bankDetail->address;
                        }

                        $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id', $id)->first();
                        $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id', $id)->first();

                        $data['member_form_information']['form_no'] = $memberData->form_no;
                        $data['member_form_information']['join_date'] = date("d/m/Y", strtotime($memberData->re_date));
                        $data['member_form_information']['member_id'] = $memberData->member_id;
                        $data['member_form_information']['branch_mi'] = $memberData->branch_mi;

                        $data['associate_details']['associate_code'] = getSeniorData($memberData->associate_id, 'associate_no');
                        $data['associate_details']['associate_name'] = getSeniorData($memberData->associate_id, 'first_name') . ' ' . getSeniorData($memberData->associate_id, 'last_name');

                        $data['personal_information']['first_name'] = $memberData->first_name;
                        $data['personal_information']['last_name'] = $memberData->last_name;
                        $data['personal_information']['email'] = $memberData->email;
                        $data['personal_information']['mobile_no'] = $memberData->mobile_no;
                        $data['personal_information']['age'] = $memberData->age;
                        $data['personal_information']['dob'] = date("d/m/Y", strtotime($memberData->dob));
                        if ($memberData->gender == 0) {
                            $data['personal_information']['gender'] = 'Female';
                        } elseif ($memberData->gender == 1) {
                            $data['personal_information']['gender'] = 'Male';
                        }
                        $data['personal_information']['occupation_id'] = getOccupationName($memberData->occupation_id);
                        $data['personal_information']['annual_income'] = number_format($memberData->annual_income, 2, '.', ',');
                        $data['personal_information']['mother_name'] = $memberData->mother_name;
                        $data['personal_information']['father_husband'] = $memberData->father_husband;
                        if ($memberData->marital_status == 0) {
                            $data['personal_information']['marital_status'] = 'Un Married';
                        } elseif ($memberData->marital_status == 1) {
                            $data['personal_information']['marital_status'] = 'Married';
                        }
                        if ($memberData->anniversary_date) {
                            $data['personal_information']['anniversary_date'] = date("d/m/Y", strtotime(convertDate($memberData->anniversary_date)));
                        } else {
                            $data['personal_information']['anniversary_date'] = '';
                        }
                        if ($memberData->religion_id > 0) {
                            $data['personal_information']['religion'] = getReligionName($memberData->religion_id);
                        } else {
                            $data['personal_information']['religion'] = '';
                        }
                        if ($memberData->special_category_id > 0) {
                            $data['personal_information']['special_category'] = getSpecialCategoryName($memberData->special_category_id);
                        } else {
                            $data['personal_information']['special_category'] = 'General Category';
                        }
                        if ($memberData->status == 1)
                            $data['personal_information']['status'] = 'Active';
                        else {
                            $data['personal_information']['status'] = 'Inactive';
                        }
                        $data['personal_information']['address'] = $memberData->address;
                        $data['personal_information']['state'] = getStateName($memberData->state_id);
                        $data['personal_information']['district'] = getDistrictName($memberData->district_id);
                        $data['personal_information']['city'] = getCityName($memberData->city_id);
                        $data['personal_information']['village'] = $memberData->village;
                        $data['personal_information']['pin_code'] = $memberData->pin_code;

                        $data['profile_imge']  = isset($memberData->photo) ? ImageUpload::fileExists('profile/member_avatar/' .  $memberData->photo) ? ImageUpload::generatePreSignedUrl('profile/member_avatar/' .  $memberData->photo) : URL('asset/images/user.png') : URL('asset/images/user.png');
                       
                        $data['signatureurl'] = isset($memberData->signature) ? ImageUpload::fileExists('profile/member_signature/' .  $memberData->signature) ? ImageUpload::generatePreSignedUrl('profile/member_signature/' .  $memberData->signature) : URL('asset/images/signature-logo-design.png') : URL('asset/images/signature-logo-design.png');
                 
                        if ($data['nomineeDetail']->is_minor == 1)
                            $data['nomineeDetail']['is_minor'] = 'Yes';
                        else {
                            $data['nomineeDetail']['is_minor'] = 'No';
                        }

                        if ($data['nomineeDetail']->status == 1)
                            $data['nomineeDetail']['status'] = 'Active';
                        else {
                            $data['nomineeDetail']['status'] = 'Inactive';
                        }

                        if ($data['nomineeDetail']->relation > 0) {
                            $data['nomineeDetail']['relation'] = getRelationsName($data['nomineeDetail']->relation);
                        }

                        if ($data['nomineeDetail']->gender == 0) {
                            $data['nomineeDetail']['gender'] = 'Female';
                        } elseif ($data['nomineeDetail']->gender == 1) {
                            $data['nomineeDetail']['gender'] = 'Male';
                        }
                        if ($data['nomineeDetail']->dob) {
                            $data['nomineeDetail']['dob'] = date("d/m/Y", strtotime($data['nomineeDetail']->dob));
                        } else {
                            $data['nomineeDetail']['dob'] = '';
                        }

                        if ($data['idProofDetail']->status == 1)
                            $data['idProofDetail']['status'] = 'Active';
                        else {
                            $data['idProofDetail']['status'] = 'Inactive';
                        }

                        $data['idProofDetail']['first_id_type_id'] = getIdProofName($data['idProofDetail']->first_id_type_id);

                        $data['idProofDetail']['second_id_type_id'] = getIdProofName($data['idProofDetail']->second_id_type_id);

                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Member Detail!';
                        $result   = $data;
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

    public function member_balance(Request $request)
    {
        $customer_id = $request->customer_id;
        $member =  SavingAccount::select('account_no', 'balance')->where([
            ['customer_id', $customer_id],
            ['is_deleted', 0],
            ['status', 1],
        ])->first();
        if ($member) {
            $token = md5($request->customer_id);
            if ($token == $request->token) {
                $balance = SavingAccountBalannce::where('account_no', $member->account_no)->sum('totalBalance');
                $status   = "Success";
                $code     = 200;
                $messages = 'Account balance and account number!';
                $account_no   = $member->account_no;
                return response()->json(compact('status', 'code', 'messages', 'account_no', 'token','balance'), $code);
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
    }
}
