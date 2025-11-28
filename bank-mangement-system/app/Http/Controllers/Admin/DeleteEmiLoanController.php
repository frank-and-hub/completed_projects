<?php

namespace App\Http\Controllers\Admin;

use App\Models\{Memberloans, LoanDayBooks, Grouploans, LoanEmisNew, AllHeadTransaction, SavingAccountTranscation, SamraddhBankDaybook, BranchDaybook, MemberTransaction,ReceivedCheque};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use DB;
use Illuminate\Support\Facades\Auth;


class DeleteEmiLoanController extends Controller
{
    public function emideleteForm(Request $request)
    {
        $data['title'] = 'Delete Loan EMI';
        return view('templates.admin.loan.delete_emi_form', $data);
    }

    public function emilist(Request $request)
    {
        if ($request->is_search == 'yes') {
            $id = Memberloans::where('account_number', $request->account_number)
                ->first();
              
            if ($id) {
                if($id->status == 4)
                {
                    $record = LoanDayBooks::where('loan_type', '!=', '3')->where('loan_sub_type', 0)
                    ->where('loan_id', $id->id) /*->where('payment_mode',0)*/
                    ->orderBy('created_at', 'desc')
                    ->where('is_deleted', 0)
                    ->get();
                    if (count($record) > 0) {
                        $status = 1;
                        return \Response::json(['view' => view('templates.admin.loan.emi_list', ['record' => $record])->render(), 'msg_type' => 'success']);
                    } else {
                        $msg = ($id->status == 3) ? 'This Loan is Closed!.' : 'Loan EMI not generated!';
                        return response()->json(['msg'=>$msg]);
                    }
                }
                else{
                   
                    $msg = ($id->status == 3) ? 'This Loan is Closed!.' : 'Loan EMI not generated!';
                   
                    return response()->json(['msg'=>$msg]);
                }
                
            } else {
               
                $groupId = Grouploans::where('account_number', $request->account_number)
                    ->first();
                if ($groupId) {
                    if( $groupId->status == 4)
                    {
                            $record = LoanDayBooks::where('loan_type', 3)->where('loan_sub_type', 0)
                            ->where('is_deleted', 0) /*->where('payment_mode',0)*/
                            ->where('loan_id', $groupId->id)
                            ->orderBy('id', 'desc')
                            ->get();
                        if (count($record) > 0) {
                            $status = 1;
                            return \Response::json(['view' => view('templates.admin.loan.emi_list', ['record' => $record])->render(), 'msg_type' => 'success']);
                        } else {
                            $msg = ($id->status == 3) ? 'This Loan is Closed!.' : 'Loan EMI not generated!';
                            return response()->json(['msg'=>$msg]);
                        }
                    }
                    else{
                        $msg = ($groupId->status == 3) ? 'This Loan is Closed!.' : 'Loan EMI not generated!';
                        return response()->json(['msg'=>$msg]);

                    }
                    
                } else {
                    $msg = ($groupId->status == 3) ? 'This Loan is Closed!.' : 'Loan EMI not generated!';
                    return response()->json(['msg'=>$msg]);

                }
            }
        }
    }
    public function records($modelName, $daybookRefId)
    {

        $record = $modelName::where('daybook_ref_id', $daybookRefId)->update(['is_deleted' => 1]);
        BranchDaybook::where('daybook_ref_id', $daybookRefId)->update(['is_deleted' => 1]);
        // MemberTransaction::where('daybook_ref_id', $daybookRefId)->update(['is_deleted' => 1]);
    }
    public function cron($loanDetails)
    {
        $entryTime = date("H:i:s");
        $Approvedate = date('Y-m-d', strtotime($loanDetails->approve_date));
        $gdate = checkMonthAvailability(date('d'), date('m'), date('Y'), $loanDetails->loanBranch->state_id);
        $date =  date('Y-m-d', strtotime(convertDate($gdate)));
        $status = DB::select('call calculate_loan_interest_update(?,?,?)', [$date, $loanDetails->account_number,1]);
    }

    public function delete_emi_transaction(Request $request, $loanId, $loanType, $recordId)
    {
        DB::beginTransaction();
        try {
            $getRecord = LoanDaybooks::findorfail($recordId);
            $memberLoanRecord = (($loanType != 'G') ? MemberLoans::where('account_number',$getRecord->account_number) : GroupLoans::where('account_number',$getRecord->account_number));
            $memberLoanRecord = $memberLoanRecord->first();
            
            $checkPenaltyExist = LoanDaybooks::where('loan_sub_type', 1)->where('created_at', $getRecord->created_at)->where(['loan_type' => $getRecord->loan_type, 'loan_id' => $getRecord->loan_id])->first();
            if ($checkPenaltyExist) {
                $this->records(new AllHeadTransaction(), $checkPenaltyExist->daybook_ref_id);
                $checkPenaltyExist->update(['is_deleted' => 1]);
            }
            /*Delete Record in All Head Transaction */
            $this->records(new AllHeadTransaction(), $getRecord->daybook_ref_id);
            switch ($getRecord->payment_mode) {
                case 4:
                    $getSavingAccount =  SavingAccountTranscation::where('daybook_ref_id', $getRecord->daybook_ref_id)->first();
                    $getSavingAccount->update(['is_deleted' => 1]);
                    // this code need to be uncommented , after uncomment this code comment DB::rollback() and uncomment  DB::commit() for currect result
                    // DB::select('call updateSSbTransactionAmount(?)', [$getSavingAccount->account_no]);
                    $getSavingAccount->savingAc->update(['balance' => $getSavingAccount->savingAc->balance + $getSavingAccount->withdrawal]);
                    break;
                case 1:
                case 3:
                    SamraddhBankDaybook::where('daybook_ref_id', $getRecord->daybook_ref_id)->update(['is_deleted' => 1]);
                    $cheque = SamraddhBankDaybook::where('daybook_ref_id', $getRecord->daybook_ref_id)->value('cheque_no');
                    if ($cheque) {
                        ReceivedCheque::where('cheque_no', $cheque)->update(['status' => '2']);
                    }
                    break;
            }
            $date = date('Y-m-d',strtotime($getRecord->created_at));
            $dataRecord = LoanEmisNew::whereDate('emi_date', $date)->where('loan_id',$memberLoanRecord->id)->where('loan_type',$memberLoanRecord->loan_type)->first();

            AllHeadTransaction::where('type_id',$dataRecord->id)->where('type_transaction_id',$memberLoanRecord->id)->where('type',5)->whereIn('sub_type',[545,546])->where('associate_id',$memberLoanRecord->associate_member_id)->where('is_query',1)->update(['is_deleted'=>1]);

            LoanEmisNew::whereDate('emi_date', $getRecord->created_at)->where('loan_id',$memberLoanRecord->id)->where('loan_type',$memberLoanRecord->loan_type)->update(['is_deleted'=>'1']);

            $dataRecordAccured = LoanEmisNew::whereDate('emi_date','<=', $date)->where('loan_id',$memberLoanRecord->id)->where('loan_type',$memberLoanRecord->loan_type)->where('is_deleted','0')->orderByDesc('emi_date')->first();


            $getRecord->update(['is_deleted' => 1]);


            
            $memberLoanRecord->update(['accrued_interest'=>($dataRecordAccured ? $dataRecordAccured->accrued_interest : 0 )]);

            $this->cron($memberLoanRecord);
            /*Get Branch DaybookRecord */
            $response = [
                'success'=>'Emi Deleted Successfully'
            ];
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $response = [
                // 'alert'=>$ex->getMessage(),
                'alert' =>$ex->getLine().''.$ex->getMessage(),
            ];
        }
        return back()->with($response);
    }

    public function delete_emi_transaction_with_reason(Request $request, $loanId, $loanType, $recordId)
    {
        // dd( $loanId, $loanType, $recordId);
        DB::beginTransaction();
        try {
            $getRecord = LoanDaybooks::findorfail($recordId);
            $memberLoanRecord = (($loanType != 'G') ? MemberLoans::where('account_number', $getRecord->account_number) : GroupLoans::where('account_number', $getRecord->account_number));
            $memberLoanRecord = $memberLoanRecord->first();
            
            $checkPenaltyExist = LoanDaybooks::where('loan_sub_type', 1)
                ->where('created_at', $getRecord->created_at)
                ->where([
                    'loan_type' => $getRecord->loan_type, 
                    'loan_id' => $getRecord->loan_id
                    ])
                ->first();
                if ($checkPenaltyExist) {
                $this->records(new AllHeadTransaction(), $checkPenaltyExist->daybook_ref_id);
                $checkPenaltyExist->update(['is_deleted' => 1]);
            }
            /*Delete Record in All Head Transaction */
            $this->records(new AllHeadTransaction(), $getRecord->daybook_ref_id);
            // dd($getRecord->daybook_ref_id);
            switch ($getRecord->payment_mode) {
                case 4:
                    $getSavingAccount = SavingAccountTranscation::where('daybook_ref_id', $getRecord->daybook_ref_id)->first();
                    $getSavingAccount->update(['is_deleted' => 1]);
                    
                    $getSavingAccount->savingAc->update(['balance' => $getSavingAccount->savingAc->balance + $getSavingAccount->withdrawal]);
                    break;
                case 1:
                    case 3:
                        SamraddhBankDaybook::where('daybook_ref_id', $getRecord->daybook_ref_id)->update(['is_deleted' => 1]);
                        $cheque = SamraddhBankDaybook::where('daybook_ref_id', $getRecord->daybook_ref_id)->value('cheque_no');
                        if ($cheque) {
                        ReceivedCheque::where('cheque_no', $cheque)->update(['status' => '2']);
                    }
                    break;
            }
            $date = date('Y-m-d', strtotime($getRecord->created_at));

            $dataRecord = LoanEmisNew::whereDate('emi_date', $date)->where('loan_id', $memberLoanRecord->id)->where('loan_type', $memberLoanRecord->loan_type)->first();
            
            AllHeadTransaction::where('type_id', $dataRecord->id)->where('type_transaction_id', $memberLoanRecord->id)->where('type', 5)->whereIn('sub_type', [545, 546])->where('associate_id', $memberLoanRecord->associate_member_id)->where('is_query', 1)->update(['is_deleted' => 1]);
            
            LoanEmisNew::whereDate('emi_date', $getRecord->created_at)->where('loan_id', $memberLoanRecord->id)->where('loan_type', $memberLoanRecord->loan_type)->update(['is_deleted' => '1']);
            
            $dataRecordAccured = LoanEmisNew::whereDate('emi_date', '<=', $date)->where('loan_id', $memberLoanRecord->id)->where('loan_type', $memberLoanRecord->loan_type)->where('is_deleted', '0')->orderByDesc('emi_date')->first();
            
            $getRecord->update(['is_deleted' => 1]);
            
            $memberLoanRecord->update(['accrued_interest' => ($dataRecordAccured ? $dataRecordAccured->accrued_interest : 0)]);

            $this->cron($memberLoanRecord);
            /*Get Branch DaybookRecord */
            
            // Additional loan_log code
            if($loanType == 'L'){
                $mLoan = Memberloans::find($loanId);
                $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), 33);
                $data = [
                    'loanId' => $loanId,
                    'loan_type' => $mLoan->loan_type,
                    'loan_category' => getLoanData($mLoan->loan_type)->loan_category,
                    'loan_name' => getLoanData($mLoan->loan_type)->name,
                    'status' => 11,
                    'title' => 'Loan emi delete',
                    'description' => $request->reason,
                    'status_changed_date' => $globaldate,
                    'created_by' => Auth::user()->id,
                    'created_by_name'=>'Admin',
                    'user_name' => Auth::user()->username,
                ];
                // dd($data);
                \App\Models\LoanLog::create($data);
            }else{
                $mLoan = Grouploans::find($loanId);
                $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), 33);
                $data = [
                    'loanId' => $loanId,
                    'loan_type' => $mLoan->loan_type,
                    'loan_category' => getLoanData($mLoan->loan_type)->loan_category,
                    'loan_name' => getLoanData($mLoan->loan_type)->name,
                    'status' => 11,
                    'title' => 'Loan emi delete',
                    'description' => $request->reason,
                    'status_changed_date' => $globaldate,
                    'created_by' => Auth::user()->id,
                    'created_by_name'=>'Admin',
                    'user_name' => Auth::user()->username,
                ];
                // dd($data);
                \App\Models\LoanLog::create($data);
            }
            $response = [
                'success' => 'Emi Deleted Successfully'
            ];
            
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $response = [
                'error' => $ex->getLine() . '' . $ex->getMessage(),
            ];
        }
        return $response;

    }
}
