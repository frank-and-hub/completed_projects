<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\Member;
use App\Models\AssociateKotaBusiness;
use Auth;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuotaController extends Controller
{
    
    public function index()
    {
		if(check_my_permission( Auth::user()->id,"13") != "1"){
		  return redirect()->route('admin.dashboard');
		}
        $data['title'] = 'Quota Business | Report';

        $datas = Branch::where('status', 1)/*->where('branch_type', 'B')*/;
        if (Auth::user()->branch_id > 0) {
            $ids = $this->getDataRolewise(new Branch());
            $datas = $datas->whereIn('id', $ids);
        }
        $data['branch'] = $datas->get(['id', 'name']);

        // return view('templates.admin.associate.kotabusiness', $data);
        return view('templates.admin.quota_business.index', $data);
    }
    public function listing(Request $request)
    {
        if ($request->ajax() /* && check_my_permission(Auth::user()->id, "13") == "1" */) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $startDate = '';
            $endDate = '';

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = Member::select('id', 'mobile_no', 'is_block', 'associate_status', 'associate_join_date', 'current_carder_id', 'first_name', 'last_name', 'associate_no', 'associate_branch_id', 'associate_senior_id')
                    ->with('associate_branch:id,branch_code,name,sector,regan,zone')
                    ->with([
                        'seniorData' => function ($q) {
                            $q->select(['id', 'first_name', 'last_name', 'associate_no', 'associate_senior_id', 'current_carder_id'])
                                ->with(['getCarderNameCustom' => function ($q) {
                                            $q->select('id', 'name', 'short_name'); }]);
                        }
                    ])
                    ->with('getCarderNameCustom:id,name,short_name')
                    ->with('getBusinessTargetAmt:id,self,credit')
                    ->where('member_id', '!=', '9999999')
                    ->where('is_associate', 1);                    
                $data = (Auth::user()->branch_id > 0) ? $data->whereIn('associate_branch_id', $this->getDataRolewise(new Branch())) : $data ;             
                $data = ($arrFormData['cader_id'] != '') ? $data->where('current_carder_id', '=', $arrFormData['cader_id']) : $data;
                $data = ($arrFormData['associate_code'] != '') ? $data->where('associate_no', 'Like',"%".$arrFormData['associate_code']."%") : $data ; 				
                /*
                if ($arrFormData['start_date']) {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    $endDate = $arrFormData['end_date'] ? date("Y-m-d", strtotime(convertDate($arrFormData['end_date']))) : null;
                    $data->whereBetween('associate_join_date', [$startDate, $endDate]);
                }
                 */
                $data = $data->when($arrFormData['associate_name'], function ($query, $name) {
                    return $query->where(function ($query) use ($name) {
                        $query->where('first_name', 'LIKE', "%{$name}%")
                            ->orWhere('last_name', 'LIKE', "%{$name}%")
                            ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%{$name}%");
                    });
                });
                
                $data = (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') ? $data->where('associate_branch_id', '=', $arrFormData['branch_id']) : $data ;  
               
                $count = $data->orderby('id', 'DESC')->count('id');
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $dataCount = Member::where('member_id', '!=', '9999999')->where('is_associate', 1);                
                $dataCount = (Auth::user()->branch_id > 0) ? $dataCount->where('associate_branch_id', '=', Auth::user()->branch_id) : $dataCount;               
                $totalCount = $dataCount->count('id');
                $sno = $_POST['start'];
                $rowReturn = array();
             

                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['branch_code'] = $row['associate_branch']->branch_code;
                    $val['branch_name'] = $row['associate_branch']->name;
                    $val['sector'] = $row['associate_branch']->sector;
                    $val['regan'] = $row['associate_branch']->regan;
                    $val['zone'] = $row['associate_branch']->zone;
                    $val['senior_code'] = $row['seniorData']->associate_no;
                    $val['senior_name'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name;
                    $val['quota_business_target_self_amt'] = $row['getBusinessTargetAmt']->self; //getBusinessTargetAmt($row->current_carder_id)->self;
                    $val['achieved_target_self_amt'] = round(AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount'), 2);
                    $targetSelf = $row['getBusinessTargetAmt']->self; //getBusinessTargetAmt($row->current_carder_id)->self;
                    $achievedSelf = AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                    if ($achievedSelf > 0) {
                        $targetSelfPer = 100 - ($achievedSelf / $targetSelf) * 100;
                    } else {
                        $targetSelfPer = 100;
                    }
                    $val['quota_business_target_self_percentage'] = round($targetSelfPer, 3);
                    $targetSelf = $row['getBusinessTargetAmt']->self; //getBusinessTargetAmt($row->current_carder_id)->self;
                    $achievedSelf = AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                    if ($achievedSelf > 0) {
                        $achievedSelfPer = ($achievedSelf / $targetSelf) * 100;
                    } else {
                        $achievedSelfPer = 0;
                    }
                    $val['achieved_target_self_percentage'] = round($achievedSelfPer, 3);
                    $val['associate_code'] = $row->associate_no;
                    $val['associate_name'] = $row->first_name . ' ' . $row->last_name;
                    if ($row->current_carder_id > 1) {
                        //getBusinessTargetAmt($row->current_carder_id)->credit
                        $targetTeam = round($row['getBusinessTargetAmt']->credit, 2);
                    } else {
                        $targetTeam = 'N/A';
                    }
                    $val['quota_business_target_team_amt'] = $targetTeam;
                    if ($row->current_carder_id > 1) {
                        $achievedTarget = round(getKotaBusinessTeam($row->id, $startDate, $endDate), 2);
                    } else {
                        $achievedTarget = 'N/A';
                    }
                    $val['achieved_target_team_amt'] = $achievedTarget;
                    if ($row->current_carder_id > 1) {
                        $targetTeam = $row['getBusinessTargetAmt']->credit; //getBusinessTargetAmt( $row->current_carder_id )->credit;
                        $targetteamAchivede = getKotaBusinessTeam($row->id, $startDate, $endDate);
                        $achievedTeamfPer = round(100.000 - ($targetteamAchivede / $targetTeam) * 100, 2);
                    } else {
                        $achievedTeamfPer = 'N/A';
                    }
                    $val['quota_business_target_team_percentage'] = $achievedTeamfPer;
                    if ($row->current_carder_id > 1) {
                        $targetTeam = $row['getBusinessTargetAmt']->credit; //getBusinessTargetAmt ( $row->current_carder_id )->credit;
                        $achievedTarget = getKotaBusinessTeam($row->id, $startDate, $endDate);
                        $achievedTeamfPer = round(($achievedTarget / $targetTeam) * 100, 2);
                    } else {
                        $achievedTeamfPer = 'N/A';
                    }
                    $val['achieved_target_team_percentage'] = $achievedTeamfPer;
                    $val['joining_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                    $val['mobile_number'] = $row->mobile_no;
                    if ($row->is_block == 0) {
                        if ($row->associate_status == 1) {
                            $status = 'Active';
                        } else {
                            $status = 'Inactive';
                        }
                    } else {
                        $status = 'Blocked';
                    }
                    $val['status'] = $status;
                    //getCarderNameFull($row->current_carder_id);
                    if (isset($row['getCarderNameCustom'])) {
                        $val['associate_carder'] = $row['getCarderNameCustom']->name; //
                    } else {
                        $val['associate_carder'] = "N/A";
                    }
                    //getSeniorData($row->associate_senior_id, 'current_carder_id')
                    if (isset($row['seniorData']['getCarderNameCustom'])) {
                        $val['senior_carder'] = $row['seniorData']['getCarderNameCustom']->name;
                    } else {
                        $val['senior_carder'] = "N/A";
                    }
                    //getCarderNameFull($row['seniorData']->current_carder_id);
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
                return json_encode($output);
            } else {
                $output = array(
                    "branch_id" => Auth::user()->branch_id,
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
                return json_encode($output);
            }
        }
    }
}