<?php

namespace App\Http\Controllers\CommonController\MemberBlacklist;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Branch;
use App\Models\{MemberLog, Admin, User};
use URL;
use DB;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/

class MemberBlacklistController extends Controller
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
   * Show    members list.
   * Route: /admin/member 
   * Method: get 
   * @return  array()  Response
   */
  public function index()
  {

    if (Auth::user()->role_id != 3) {
      if (check_my_permission(Auth::user()->id, "233") != "1") {
        return redirect()->route('admin.dashboard');
      }
    } else {
      if (!in_array('Manage Blacklist Members For Loan', auth()->user()->getPermissionNames()->toArray())) {
        return redirect()->route('branch.dashboard');
      }
    }
    $data['title'] = 'Manage Blacklist Members For Loan';
    $data['branch'] = Branch::where('status', 1)->get(['id']);
    $data['branches'] = ['' => 'Please select branch'] + Branch::where('status', 1)->pluck('name', 'id')->toArray();
    return view('templates.CommonViews.Member.index', $data);
  }
  /**
   * Get members list
   * Route: ajax call from - /admin/member
   * Method: Post 
   * @param  \Illuminate\Http\Request  $request
   * @return JSON array
   */
  public function member_blacklist_on_loan_listing(Request $request)
  {

    // fillter array 
    $arrFormData = array();
    if (!empty($_POST['searchform'])) {
      foreach ($_POST['searchform'] as $frm_data) {
        $arrFormData[$frm_data['name']] = $frm_data['value'];
      }
    }
    $data = Member::select('id', 're_date', 'member_id', 'first_name', 'last_name',  'mobile_no', 'associate_code', 'is_block', 'status', 'is_blacklist_on_loan', 'address',  'father_husband',   'member_id', 'associate_id', 'branch_id', 'address', 'block_reason')
      ->with(['branch' => function ($q) {
        $q->select('id', 'name', 'branch_code');
      }])
      ->with(['children' => function ($q) {
        $q->select(['id', 'first_name', 'last_name']);
      }])->with('blackListData')
      ->where('member_id', '!=', '9999999')->where('is_blacklist_on_loan', '1');
    /******* fillter query start ****/
    if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
      if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
        $id = $arrFormData['branch_id'];
        $data = $data->where('branch_id', '=', $id);
      }
      if ($arrFormData['member_id'] != '') {
        $meid = $arrFormData['member_id'];
        $data = $data->where('member_id', 'LIKE', '%' . $meid . '%');
      }
    }
    $admin = (Auth::user()->role_id != 3) ? true : false;
    if ($admin == false) {
      $getBranchId = getUserBranchId(Auth::user()->id)->id;
      $data = $data->where('branch_id', '=', $getBranchId);
    }
    /******* fillter query End ****/
    $count = $data->orderby('id', 'DESC')->count('id');
    $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
    $sno = $_POST['start'];
    $rowReturn = array();
    foreach ($data as $row) {
      $sno++;
      $val['DT_RowIndex'] = $sno;
      $val['join_date'] = isset($row['blackListData']['created_at']) ? date("d/m/Y", strtotime($row['blackListData']['created_at'])) :"N/a";
      $val['branch'] = $row['branch']->name . '-' . $row['branch']->branch_code;
      $btnS = '';
      $url8 = URL::to("admin/member-edit/" . $row->id . "");
      $btnS .= '<a class=" " href="' . $url8 . '" title="Edit Member">' . $row->member_id . '</a>';
      $val['member_id'] = $row->member_id;
      $val['name'] = $row->first_name . ' ' . $row->last_name;
      $val['dob'] = $row->father_husband;
      $reasonBtn = '<div class="list-icons"><button class="list-icons-item btn btn btn-info" data-toggle="modal" data-target="#blockedDetail" title="Blocked Detail" id="details" data-customer_id=' . $row->id . '><i class="fas fa-clipboard-list"></i></button>';
      if (Auth::user()->role_id == 3) {
        $reasonBtn = $row->block_reason;
      }
      $val['block_reason'] =  $reasonBtn;
      $val['mobile_no'] = $row->mobile_no;
      $val['associate_code'] = $row->associate_code;
      $val['associate_name'] = $row['children']->first_name . ' ' . $row['children']->last_name;
      if ($row->is_block == 1) {
        $status = 'Blocked';
      } else {
        if ($row->status == 1) {
          $status = 'Active';
        } else {
          $status = 'Inactive';
        }
      }
      $val['status'] = $status;
      if ($row->is_blacklist_on_loan == "1") {
        $is_blacklist_on_loan = 'Blacklisted';
      } else {
        $is_blacklist_on_loan = 'Active';
      }
      $val['is_blacklist_on_loan'] = $is_blacklist_on_loan;
      $val['address'] = $row->address;
      if (check_my_permission(Auth::user()->id, "244") == "1") {
        $btn = '<div class="list-icons"><div class="dropdown"><button class="list-icons-item unblockUser btn btn-outline-secondary" title="Unblock" data-row-id=' . $row->id . '><i class="icon-blocked"></i></button>';
        $btn .= '</div></div>';
        $val['action'] = $btn;
      } else {
        $val['action'] = 'N/A';
      }
      $rowReturn[] = $val;
    }
    $output = array("branch_id" => Auth::user()->branch_id, "draw" => $_POST['draw'], "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $rowReturn,);
    return json_encode($output);
  }
  /**
   * Show    create member.
   * Route: /admin/member-register 
   * Method: get 
   * @return  array()  Response
   */
  public function addBlacklist()
  {
      if (Auth::user()->role_id != 3) {
        if (check_my_permission(Auth::user()->id, "235") != "1") {
            return redirect()->route('admin.dashboard');
        }
    } else {
        if (!in_array('Add Members for loan blacklist', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
    }
    if (Auth::user()->branch_id > 0) {
      $id = Auth::user()->branch_id;
      $data['branch'] = Branch::where('status', 1)->where('id', '=', $id)->get();
    } else {
      $data['branch'] = Branch::where('status', 1)->get();
    }
    $data['title'] = 'Add Blacklist Member For Loan';
    $data['state'] = stateList();
    $data['occupation'] = occupationList();
    $data['religion'] = religionList();
    $data['specialCategory'] = specialCategoryList();
    $data['idTypes'] = idTypeList();
    $data['relations'] = relationsList();
    return view('templates.CommonViews.Member.add', $data);
  }

  public function getBlacklistMemberData(Request $request)
  {
    $data = Member::where('member_id', $request->code)->where('status', 1)->where('is_deleted', 0);
    $admin = (Auth::user()->role_id != 3) ? true : false;
    if ($admin == false) {
      $getBranchId = getUserBranchId(Auth::user()->id)->id;
      $data = $data->where('branch_id', '=', $getBranchId);
    }
    if (!is_null(Auth::user()->branch_ids)) {
      $id = Auth::user()->branch_ids;
      $data = $data->whereIn('branch_id', explode(",", $id));
    }
    $data = $data->first();
    if ($data) {
      if ($data->is_block == 1) {
        return \Response::json(['view' => 'No data found', 'msg_type' => 'error2']);
      } else {

        if ($data->is_blacklist_on_loan == "0") {

          $id = $data->id;
          $member_name = $data->first_name . " " . $data->last_name;

          // Now Get Associate Name
          $associate_code = $data->associate_code;
          $associate_name = getSeniorData($data->associate_id, 'first_name') . ' ' . getSeniorData($data->associate_id, 'last_name');

          $idProofDetail = \App\Models\MemberIdProof::where('member_id', $id)->first();

          $firstId = getIdProofName($idProofDetail->first_id_type_id) . ' - ' . $idProofDetail->first_id_no;
          $secondId = getIdProofName($idProofDetail->second_id_type_id) . ' - ' . $idProofDetail->second_id_no;

          $viewData = '<h4 class="card-title mb-3">Member Personal Detail</h4>';
          $viewData = '<input type = "hidden" name="memberID" id="memberID" value=' . $id . '>';
          $viewData .= '<div class="row">
									<div class="col-lg-6 ">
									<div class=" row">
									  <label class=" col-lg-4">Member Name</label><label class=" col-lg-1">:</label>
									  <div class="col-lg-7 ">
										' . $member_name . '
									  </div>
									</div>
								  </div>
								  <div class="col-lg-6 ">
									<div class=" row">
									  <label class="col-lg-4">Associate Name</label><label class=" col-lg-1">:</label>
									  <div class="col-lg-7 ">
										' . $associate_name . ' 
									  </div>
									</div>
								  </div>
								  </div>';
          $viewData .= '<div class="row">
                          <div class="col-lg-6 ">
                            <div class=" row">
                              <label class=" col-lg-4">First ID Proof</label><label class=" col-lg-1">:</label>
                              <div class="col-lg-7 ">
                                ' . $firstId . '
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-6 ">
                            <div class=" row">
                              <label class="col-lg-4">Second ID Proof</label><label class=" col-lg-1">:</label>
                              <div class="col-lg-7 ">
                                ' . $secondId . ' 
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-12">
                            <div class="row p-2">
                              <label class="" for="block_reason" >Reason *</label>
                              <textarea class="form-control" rows="5" name="block_reason" id="block_reason" placeholder="Enter Reason"></textarea>
                              </div>
                          </div>
								  </div>';

          if (\Auth::user()->role_id != 3) {
            $viewData .= '<div class="row" style="text-align:center">
									<div class="col-lg-12"> 
										<button type="button" class="btn btn-primary legitRipple blockMemberOnLoan" id="btnAdd">Blacklist</button>  
									</div></div>';
          } else {
            $viewData .= '<div class="row" style="text-align:center">
                            <div class="col-lg-12"> 
                              <button type="button" class="btn btn-primary legitRipple blockMemberOnLoan_b" id="btnAdd">Blacklist</button>  
                            </div></div>';
          }

          //return \Response::json(['view' => view('templates.admin.associate.partials.member_detail' ,['memberData' => $data,'idProofDetail' => $idProofDetail])->render(),'msg_type'=>'success','id'=>$id]);

          return \Response::json(['view' => $viewData, 'msg_type' => 'success']);
        } else {
          return \Response::json(['view' => 'Customer already blacklisted', 'msg_type' => 'error1']);
        }
      }
    } else {
      return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
    }
  }

  public function actionBlacklistMemberForLoan(Request $request)
  {
    //  dd($request->all());
    $member_id = $request->memberID;
    $is_blacklist_on_loan = $request->is_block;
    $admin = (Auth::user()->role_id != 3) ? true : false;
    if ($admin == false) {
      $getBranchId = getUserBranchId(Auth::user()->id)->id;
      $m_bid = Member::whereId($member_id)->value('branch_id');
      if ($m_bid != $getBranchId) {
        return \Response::json(['view' => 'This member does not belongs from your branch.', 'msg_type' => 'error']);
      }
    }
    Member::whereId($member_id)->update(array('is_blacklist_on_loan' => $is_blacklist_on_loan, 'block_reason' => $request->block_reason));
    if ($is_blacklist_on_loan == "1") {
      $data['customer_id'] = $member_id;
      $data['reason'] = $request->block_reason;
      $data['created_by'] = (Auth::user()->role_id != 3) ? 1 : 2;
      $data['created_by_id'] = \Auth::user()->id;
      $data['created_at_default'] = date('Y-m-d', strtotime(convertDate($request->date)));
      MemberLog::create($data);
      return \Response::json(['view' => 'Member blacklisted successfully', 'msg_type' => 'success']);
    } else {
      return \Response::json(['view' => 'Member activated successfully.', 'msg_type' => 'success']);
    }
  }
  public function print_blacklist_member_on_loan(Request $request)
  {
    $data = Member::with('branch')
      ->with(['states' => function ($query) {
        $query->select('id', 'name');
      }])
      ->with(['city' => function ($q) {
        $q->select(['id', 'name']);
      }])
      ->with(['district' => function ($q) {
        $q->select(['id', 'name']);
      }])
      ->with(['memberIdProof' => function ($q) {
        $q->with(['idTypeFirst' => function ($q) {
          $q->select(['id', 'name']);
        }])
          ->with(['idTypeSecond' => function ($q) {
            $q->select(['id', 'name']);
          }]);
      }])
      ->with(['children' => function ($q) {
        $q->select(['id', 'first_name', 'last_name']);
      }])
      ->with(['memberNomineeDetails' => function ($q) {
        $q->with(['nomineeRelationDetails' => function ($q) {
          $q->select('id', 'name');
        }]);
      }])->where('member_id', '!=', '9999999')->where('is_blacklist_on_loan', '1');

    if (!is_null(Auth::user()->branch_ids)) {
      $branch_ids = Auth::user()->branch_ids;
      $data = $data->whereIn('branch_id', explode(",", $branch_ids));
    }

    if (isset($request['is_search']) && $request['is_search'] == 'yes') {
      if ($request['associate_code'] != '') {
        $associate_code = $request['associate_code'];
        $data = $data->where('associate_code', '=', $associate_code);
      }

      if ($request['branch_id'] != '') {
        $id = $request['branch_id'];
        $data = $data->where('branch_id', '=', $id);
      }

      if ($request['member_id'] != '') {
        $meid = $request['member_id'];
        $data = $data->where('member_id', '=', $meid);
      }

      if ($request['name'] != '') {
        $name = $request['name'];
        $data = $data->where(function ($query) use ($name) {
          $query->where('first_name', 'LIKE', '%' . $name . '%')->orWhere('last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
        });
      }

      if ($request['start_date'] != '') {
        $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));

        if ($request['end_date'] != '') {
          $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
        } else {
          $endDate = '';
        }

        $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
      }
    }

    $memberList = $data->orderby('id', 'DESC')->get();

    return view('templates.admin.member.memberBlacklistExport', compact('memberList'));
  }
  public function exportMemberBlacklistOnLoan(Request $request)
  {
    if ($request['member_export'] == 0) {
      $input = $request->all();
      $start = $input["start"];
      $limit = $input["limit"];
      $returnURL = URL::to('/') . "/asset/black_list_member_loan.csv";
      $fileName = env('APP_EXPORTURL') . "asset/black_list_member_loan.csv";
      global $wpdb;
      $postCols = array(
        'post_title',
        'post_content',
        'post_excerpt',
        'post_name',
      );
      header("Content-type: text/csv");
    }
    $data = Member::with('branch')
      ->with([
        'states' => function ($query) {
          $query->select('id', 'name');
        }
      ])
      ->with([
        'city' => function ($q) {
          $q->select(['id', 'name']);
        }
      ])
      ->with([
        'district' => function ($q) {
          $q->select(['id', 'name']);
        }
      ])
      ->with([
        'children' => function ($q) {
          $q->select(['id', 'first_name', 'last_name']);
        }
      ])->where('member_id', '!=', '9999999')->where('is_blacklist_on_loan', '1');
    if (isset($request['branch_id']) && $request['branch_id'] != '') {
      $id = $request['branch_id'];
      $data = $data->where('branch_id', '=', $id);
    }
    $admin = (Auth::user()->role_id != 3) ? true : false;
    if ($admin == false) {
      $getBranchId = getUserBranchId(Auth::user()->id)->id;
      $data = $data->where('branch_id', '=', $getBranchId);
    }
    if ($request['member_id'] != '') {
      $meid = $request['member_id'];
      $data = $data->where('member_id', 'LIKE', '%' . $meid . '%');
    }
    if ($request['status'] != '') {
      $status = $request['status'];
      $data = $data->where('status', $status);
    }
    if ($request['member_export'] == 0) {
      $sno = $_POST['start'];
      $totalResults = $data->orderby('id', 'DESC')->count();
      $results = $data->orderby('id', 'DESC')->offset($start)->limit($limit)->get();
      $result = 'next';
      if (($start + $limit) >= $totalResults) {
        $result = 'finished';
      }
      // if its a fist run truncate the file. else append the file
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
      foreach ($results as $row) {
        $sno++;
        $val['S/N'] = $sno;
        // $NomineeDetail = $row['memberNomineeDetails'];
        $val['BLACKLISTED ON'] = isset($row['blackListData']['created_at']) ? date("d/m/Y", strtotime($row['blackListData']['created_at'])) :"N/a";
        $val['BR NAME'] = $row['branch']->name;
        $val['BR CODE'] = $row['branch']->branch_code;
        $val['SO NAME'] = $row['branch']->sector;
        $val['RO NAME'] = $row['branch']->sector;
        $val['ZO NAME'] = $row['branch']->zone;
        $btnS = '';
        $val['CUSTOMER ID'] = $row->member_id;
        $val['NAME'] = $row->first_name . ' ' . $row->last_name;
        $val["FATHER/ HUSBAND's NAME"] = $row->father_husband;
        $val["REASON"] = $row->block_reason;
        $val['MOBILE NO'] = $row->mobile_no;
        $val['ASSOCIATE CODE'] = $row->associate_code;
        $val['ASSOCITE NAME'] = $row['children']->first_name . ' ' . $row['children']->last_name; //getSeniorData($row->associate_id,'first_name').' '.getSeniorData($row->associate_id,'last_name');
        if ($row->is_block == 1) {
          $status = 'Blocked';
        } else {
          if ($row->status == 1) {
            $status = 'Active';
          } else {
            $status = 'Inactive';
          }
        }
        if ($row->is_blacklist_on_loan == "1") {
          $is_blacklist_on_loan = 'Blacklisted';
        } else {
          $is_blacklist_on_loan = 'Active';
        }
        $val['is_blacklist_on_loan'] = $is_blacklist_on_loan;
        $val['ADDRESSS'] = preg_replace("/\r|\n/", "", $row->address);
        $val['status'] = $status;
        if (!$headerDisplayed) {
          // Use the keys from $data as the titles
          fputcsv($handle, array_keys($val));
          $headerDisplayed = true;
        }
        // Put the data into the stream
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
    } elseif ($request['member_export'] == 1) {
      $memberList = $data->orderby('id', 'DESC')->get();
      $pdf = PDF::loadView('templates.admin.member.memberexport', compact('memberList'))->setPaper('a4', 'landscape')->setWarnings(false);
      $pdf->save(storage_path() . '_filename.pdf');
      return $pdf->download('members.pdf');
    }
  }

  //For the fetching the blocked details from the member_logs table
  public function blockDetails(Request $request)
  {
    // dd($request->all());
    $customer_id = $request->customer_id;
    $member_log_details = MemberLog::where('customer_id', $customer_id)->with('member_data:id,member_id')->orderby('id', 'DESC')->get();
    //if the data is empty in the member_log_details then return response with message is 0

    $count = count($member_log_details);
    $totalResults = $count;
    $rowReturn = [];
    $sno = $_POST['start'];

    foreach ($member_log_details as $key) {
      $sno++;
      $val['sno'] = $sno;
      $val['date'] = date("d/m/Y", strtotime($key['created_at_default']));
      $val['created_by'] = $key['created_by'] == 1 ? 'Admin' : 'Branch';
      $val['name'] = $key['created_by'] == 1 ? Admin::where('id', $key['created_by_id'])->value('username') :  User::where('id', $key['created_by_id'])->value('username');
      $val['reason'] = $key['reason'];
      $val['member_id'] = $key['member_data']['member_id'];
      $rowReturn[] = $val;
    }
    $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalResults, "recordsFiltered" => $count, "data" => $rowReturn);
    if (empty($member_log_details)) {
      $output = array("draw" => $_POST['draw'], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
    }

    return json_encode($output);
    // if ($member_log_details->created_by == 1) {
    //   $userData = Admin::where('id', $member_log_details->created_by_id)->first(['id', 'username']);
    //   return response(['memberLogData' => $member_log_details, 'UserData' => $userData, 'msg' => 1]);
    // } else {
    //   $userData = User::where('id', $member_log_details->created_by_id)->first(['id', 'username']);
    //   return response(['memberLogData' => $member_log_details, 'UserData' => $userData, 'msg' => 1]);
    // }
  }
}
