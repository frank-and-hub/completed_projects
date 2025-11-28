<?php

namespace App\Http\Controllers\Api\InvestmentManagement;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\MemberIdProof;
use App\Models\Member;
use App\Models\Plans;
use App\Models\PlanCategory;
use App\Models\Memberinvestments;
use App\Models\Daybook;
use App\Models\SavingAccountTranscation;
use App\Models\SavingAccount;
use App\Models\TranscationLog;
use App\Models\Transcation;
use App\Models\Investmentplantransactions;
use Carbon\Carbon;
use Session;
use App\Services\Sms;
use App\Http\Controllers\Branch\CommanTransactionsController;


class InvestmentDueReportFilter extends Controller
{
    /**
     * Fetch member investments.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function investmentFilter(Request $request)
    {
        $code = 201;
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $plans = Plans::has('company')->where('plan_category_code', "M")->get(['id', 'name']);
                    $plancatogery = PlanCategory::where('code',in,['D','M'])->get();
                    // dd($plancategery);
                    die();
                    $status = "Shh";
                    $code = 200;
                }
                // dd($plans);
                return response()->json(compact('plans','status','plancatogery'), $code);
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
}