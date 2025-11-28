<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Validator;
use App\Models\Transcation;
use App\Models\BranchDaybook;
use App\Models\Daybook;
use App\Models\SamraddhBankDaybook;
use App\Models\AllTransaction;
use App\Models\AllHeadTransaction;
use App\Models\Memberloans;
use App\Models\Grouploans;
use App\Http\Controllers\Admin\CommanController;
use App\Http\Controllers\Admin\InvestmentplanController;
use App\Models\SavingAccountTranscation;
use App\Models\SavingAccount;
use App\Models\AccountHeads;
use App\Models\MemberTransaction;
use App\Models\Memberinvestments;
use App\Models\DemandAdvice;
use Yajra\DataTables\DataTables;
use App\Models\TransactionType;
use App\Models\Branch;
use App\Models\Plans;
use CommanTransactionFacade;
use App\Models\CollectorAccount;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Http\Controllers\Branch\CommanTransactionsController;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Account Management AccountImplementController
    |--------------------------------------------------------------------------
    |
    | This controller handles Account all functionlity.
*/
class TestController extends Controller
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
    public function test()
    {
        $data = DB::table('samraddh_bank_daybook')
            ->join('all_transaction', 'all_transaction.daybook_ref_id', '=', 'samraddh_bank_daybook.daybook_ref_id')
            ->select('samraddh_bank_daybook.id', 'samraddh_bank_daybook.daybook_ref_id', 'samraddh_bank_daybook.bank_id', 'samraddh_bank_daybook.account_id', 'samraddh_bank_daybook.type', 'samraddh_bank_daybook.sub_type', 'samraddh_bank_daybook.type_id', 'samraddh_bank_daybook.type_transaction_id', 'samraddh_bank_daybook.amount', 'samraddh_bank_daybook.payment_type', 'samraddh_bank_daybook.payment_mode', 'samraddh_bank_daybook.entry_date', 'samraddh_bank_daybook.created_at', 'all_transaction.id as all_tra_id', 'all_transaction.daybook_ref_id as all_tra_daybook_ref', 'all_transaction.head1', 'all_transaction.head2', 'all_transaction.head3', 'all_transaction.head4', 'all_transaction.head5', 'all_transaction.type as all_tra_type', 'all_transaction.sub_type as all_tra_sub_type', 'all_transaction.type_id as all_tra_type_id', 'all_transaction.type_transaction_id as  all_tran_type_transaction_id', 'all_transaction.amount as all_tra_amnt', 'all_transaction.payment_type as all_tra_payment_type', 'all_transaction.payment_mode as all_tra_payment_mode', 'all_transaction.entry_date as all_tra_entry_date', 'all_transaction.created_at as all_tra_created_at')
            ->where('samraddh_bank_daybook.bank_id', '=', 1)
            ->where('all_transaction.head3', '=', 27)
            ->get();
        echo "<table border='1'>";
        echo "<tr>
                    <th>Id</th>
                    <th>daybook_ref_id</th>
                    <th>bank_id</th>
                    <th>account_id</th>
                    <th>type</th>
                    <th>sub_type</th>
                    <th>type_id</th>
                    <th>type_transaction_id</th>
                    <th>amount</th>
                    <th>payment_type</th>
                    <th>payment_mode</th>
                    <th>all_tra_payment_type</th>
                    <th>all_tra_payment_mode</th>
                    <th>entry_date</th>
                    <th>created_at</th>
                    <th>all_tra_id</th>
                    <th>all_tra_daybook_ref</th>
                    <th>head1</th>
                    <th>head2</th>
                    <th>head3</th>
                    <th>head4</th>
                    <th>head5</th>
                    <th>all_tra_type</th>
                    <th>all_tra_sub_type</th>
                    <th>all_tra_type_id</th>
                    <th>all_tran_type_transaction_id</th>
                    <th>all_tra_amnt</th>
                    <th>all_tra_entry_date</th>
                    <th>all_tra_created_at</th>
                    </tr>";
        foreach ($data as $key => $value) {
            echo "<tr><td>" . $value->id . "</td>
                    <td>" . $value->daybook_ref_id . "</td>
                    <td>" . $value->bank_id . "</td>
                    <td>" . $value->account_id . "</td>
                    <td>" . $value->type . "</td>
                    <td>" . $value->sub_type . "</td>
                    <td>" . $value->type_id . "</td>
                    <td>" . $value->type_transaction_id . "</td>
                    <td>" . $value->amount . "</td>
                    <td>" . $value->payment_type . "</td>
                    <td>" . $value->payment_mode . "</td>
                    <td>" . $value->all_tra_payment_type . "</td>
                    <td>" . $value->all_tra_payment_mode . "</td>
                    <td>" . $value->entry_date . "</td>
                    <td>" . $value->created_at . "</td>
                    <td>" . $value->all_tra_id . "</td>
                    <td>" . $value->all_tra_daybook_ref . "</td>
                    <td>" . $value->head1 . "</td>
                    <td>" . $value->head2 . "</td>
                    <td>" . $value->head3 . "</td>
                    <td>" . $value->head4 . "</td>
                    <td>" . $value->head5 . "</td>
                    <td>" . $value->all_tra_type . "</td>
                    <td>" . $value->all_tra_sub_type . "</td>
                    <td>" . $value->all_tra_type_id . "</td>
                    <td>" . $value->all_tran_type_transaction_id . "</td>
                    <td>" . $value->all_tra_amnt . "</td>
                    <td>" . $value->all_tra_entry_date . "</td>
                    <td>" . $value->all_tra_created_at . "</td>
                </tr>";
        }
        echo "</table>";
    }
    public function insert_data_branch_daybook()
    {
        $data = DB::table('all_head_transaction')->where('type', 13)->where('sub_type', 137)->whereIN('payment_mode', [1, 2, 3])->where('branch_id', 11)->get();
        foreach ($data as $key => $value) {
            $ifexists = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->count();
            if ($ifexists == 0) {
                $record['daybook_ref_id'] = $value->daybook_ref_id;
                $record['branch_id'] = $value->branch_id;
                $record['type'] = $value->type;
                $record['sub_type'] = $value->sub_type;
                $record['type_id'] = $value->type_id;
                $record['type_transaction_id'] = $value->type_transaction_id;
                $record['associate_id'] = $value->associate_id;
                $record['member_id'] = $value->member_id;
                $record['branch_id_to'] = $value->branch_id_to;
                $record['branch_id_from'] = $value->branch_id_from;
                $record['opening_balance'] = $value->opening_balance;
                $record['amount'] = $value->amount;
                $record['closing_balance'] = $value->closing_balance;
                $record['description'] = $value->description;
                $record['description_dr'] = 'Maturity Amount A/C Dr ' . $value->amount;
                $record['description_cr'] = 'To Cash A/C Cr ' . $value->amount;
                $record['payment_type'] = $value->payment_type;
                $record['payment_mode'] = $value->payment_mode;
                $record['currency_code'] = $value->currency_code;
                $record['amount_to_id'] = $value->amount_to_id;
                $record['amount_to_name'] = $value->amount_to_name;
                $record['amount_from_id'] = $value->amount_from_id;
                $record['amount_from_name'] = $value->amount_from_name;
                $record['v_no'] = $value->v_no;
                $record['v_date'] = $value->v_date;
                $record['ssb_account_id_from'] = $value->ssb_account_id_from;
                $record['ssb_account_id_to'] = $value->ssb_account_id_to;
                $record['cheque_no'] = $value->ssb_account_id_to;
                $record['cheque_date'] = $value->cheque_date;
                $record['cheque_bank_from'] = $value->cheque_bank_from;
                $record['cheque_bank_ac_from'] = $value->cheque_bank_ac_from;
                $record['cheque_bank_ifsc_from'] = $value->cheque_bank_ifsc_from;
                $record['cheque_bank_branch_from'] = $value->cheque_bank_branch_from;
                $record['cheque_bank_from_id'] = $value->cheque_bank_from_id;
                $record['cheque_bank_ac_from_id'] = $value->cheque_bank_ac_from_id;
                $record['cheque_bank_to'] = $value->cheque_bank_to;
                $record['cheque_bank_ac_to'] = $value->cheque_bank_ac_to;
                $record['cheque_bank_to_name'] = $value->cheque_bank_to_name;
                $record['cheque_bank_to_branch'] = $value->cheque_bank_to_branch;
                $record['cheque_bank_to_ac_no'] = $value->cheque_bank_to_ac_no;
                $record['cheque_bank_to_ifsc'] = $value->cheque_bank_to_ifsc;
                $record['transction_no'] = $value->transction_no;
                $record['transction_bank_from'] = $value->transction_bank_from;
                $record['transction_bank_ac_from'] = $value->transction_bank_ac_from;
                $record['transction_bank_ifsc_from'] = $value->transction_bank_ifsc_from;
                $record['transction_bank_branch_from'] = $value->transction_bank_branch_from;
                $record['transction_bank_from_id'] = $value->transction_bank_from_id;
                $record['transction_bank_from_ac_id'] = $value->transction_bank_from_ac_id;
                $record['transction_bank_to'] = $value->transction_bank_to;
                $record['transction_bank_ac_to'] = $value->transction_bank_ac_to;
                $record['transction_bank_to_name'] = $value->transction_bank_to_name;
                $record['transction_bank_to_ac_no'] = $value->transction_bank_to_ac_no;
                $record['transction_bank_to_branch'] = $value->transction_bank_to_branch;
                $record['transction_bank_to_ifsc'] = $value->transction_bank_to_ifsc;
                $record['transction_date'] = $value->transction_date;
                $record['entry_date'] = $value->entry_date;
                $record['entry_time'] = $value->entry_time;
                $record['created_by'] = $value->created_by;
                $record['created_by_id'] = $value->created_by_id;
                $record['is_contra'] = 0;
                $record['contra_id'] = NULL;
                $record['created_at'] = $value->created_at;
                $record['updated_at'] = $value->updated_at;
                $record['ssb_account_tran_id_to'] = $value->ssb_account_tran_id_to;
                $record['ssb_account_tran_id_from'] = $value->ssb_account_tran_id_from;
                $record['amount_type'] = 0;
                $recordInsert = BranchDaybook::create($record);
            }
        }
        die("success");
    }
    public function getBankLoanTransactions()
    {
        DB::beginTransaction();
        try {
            $getRecords = AllTransaction::where('type', 5)->whereIN('sub_type', [52, 53, 55, 56])->whereIN('payment_mode', [1, 2])->get();
            foreach ($getRecords as $key => $value) {
                $ifexists = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->count();
                if ($ifexists == 0) {
                    if ($value->sub_type == 52 || $value->sub_type == 53) {
                        $loanDeatils = Memberloans::where('id', $value->type_id)->first();
                    } elseif ($value->sub_type == 54 || $value->sub_type == 55) {
                        $loanDeatils = Grouploans::where('id', $value->type_id)->first();
                    }
                    $record['daybook_ref_id'] = $value->daybook_ref_id;
                    $record['branch_id'] = $value->branch_id;
                    $record['type'] = $value->type;
                    $record['sub_type'] = $value->sub_type;
                    $record['type_id'] = $value->type_id;
                    $record['type_transaction_id'] = $value->type_transaction_id;
                    $record['associate_id'] = $value->associate_id;
                    $record['member_id'] = $value->member_id;
                    $record['branch_id_to'] = $value->branch_id_to;
                    $record['branch_id_from'] = $value->branch_id_from;
                    $record['opening_balance'] = $value->opening_balance;
                    $record['amount'] = $value->amount;
                    $record['closing_balance'] = $value->closing_balance;
                    $record['description'] = $value->description;
                    $record['description_dr'] = 'Cash A/C Dr ' . $value->amount;
                    $record['description_cr'] = 'To ' . $loanDeatils->account_number . ' A/C Cr ' . $value->amount;
                    $record['payment_type'] = $value->payment_type;
                    $record['payment_mode'] = $value->payment_mode;
                    $record['currency_code'] = $value->currency_code;
                    $record['amount_to_id'] = $value->amount_to_id;
                    $record['amount_to_name'] = $value->amount_to_name;
                    $record['amount_from_id'] = $value->amount_from_id;
                    $record['amount_from_name'] = $value->amount_from_name;
                    $record['v_no'] = $value->v_no;
                    $record['v_date'] = $value->v_date;
                    $record['ssb_account_id_from'] = $value->ssb_account_id_from;
                    $record['ssb_account_id_to'] = $value->ssb_account_id_to;
                    $record['cheque_no'] = $value->ssb_account_id_to;
                    $record['cheque_date'] = $value->cheque_date;
                    $record['cheque_bank_from'] = $value->cheque_bank_from;
                    $record['cheque_bank_ac_from'] = $value->cheque_bank_ac_from;
                    $record['cheque_bank_ifsc_from'] = $value->cheque_bank_ifsc_from;
                    $record['cheque_bank_branch_from'] = $value->cheque_bank_branch_from;
                    $record['cheque_bank_from_id'] = $value->cheque_bank_from_id;
                    $record['cheque_bank_ac_from_id'] = $value->cheque_bank_ac_from_id;
                    $record['cheque_bank_to'] = $value->cheque_bank_to;
                    $record['cheque_bank_ac_to'] = $value->cheque_bank_ac_to;
                    $record['cheque_bank_to_name'] = $value->cheque_bank_to_name;
                    $record['cheque_bank_to_branch'] = $value->cheque_bank_to_branch;
                    $record['cheque_bank_to_ac_no'] = $value->cheque_bank_to_ac_no;
                    $record['cheque_bank_to_ifsc'] = $value->cheque_bank_to_ifsc;
                    $record['transction_no'] = $value->transction_no;
                    $record['transction_bank_from'] = $value->transction_bank_from;
                    $record['transction_bank_ac_from'] = $value->transction_bank_ac_from;
                    $record['transction_bank_ifsc_from'] = $value->transction_bank_ifsc_from;
                    $record['transction_bank_branch_from'] = $value->transction_bank_branch_from;
                    $record['transction_bank_from_id'] = $value->transction_bank_from_id;
                    $record['transction_bank_from_ac_id'] = $value->transction_bank_from_ac_id;
                    $record['transction_bank_to'] = $value->transction_bank_to;
                    $record['transction_bank_ac_to'] = $value->transction_bank_ac_to;
                    $record['transction_bank_to_name'] = $value->transction_bank_to_name;
                    $record['transction_bank_to_ac_no'] = $value->transction_bank_to_ac_no;
                    $record['transction_bank_to_branch'] = $value->transction_bank_to_branch;
                    $record['transction_bank_to_ifsc'] = $value->transction_bank_to_ifsc;
                    $record['transction_date'] = $value->transction_date;
                    $record['entry_date'] = $value->entry_date;
                    $record['entry_time'] = $value->entry_time;
                    $record['created_by'] = $value->created_by;
                    $record['created_by_id'] = $value->created_by_id;
                    $record['is_contra'] = 0;
                    $record['contra_id'] = NULL;
                    $record['created_at'] = $value->created_at;
                    $record['updated_at'] = $value->updated_at;
                    $record['ssb_account_tran_id_to'] = $value->ssb_account_tran_id_to;
                    $record['ssb_account_tran_id_from'] = $value->ssb_account_tran_id_from;
                    $record['amount_type'] = 0;
                    $insertData = BranchDaybook::create($record);
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
    }
    // Update Branch of record by daybook ref id
    public function update_branch()
    {
        $getData = BranchDaybook::whereIn('id', [123600, 123601, 123602, 123603, 123604, 123605, 123606, 123607, 123608, 123609])->get();
        ;
        foreach ($getData as $key => $value) {
            $updateAlltransaction = AllTransaction::where('daybook_ref_id', $value->daybook_ref_id)->get();
            foreach ($updateAlltransaction as $key => $value2) {
                $data = AllTransaction::find($value2->id);
                $data->update(['branch_id' => 27]);
            }
            $updateMemberTransaction = \App\Models\MemberTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['branch_id' => 27]);
            $DaybookTransaction = Daybook::where('id', $value->type_transaction_id)->first();
            $updateDaybookTransaction = Daybook::where('id', $value->type_transaction_id)->update(['branch_id' => 27]);
            $updateAlltransaction = BranchDaybook::where('id', $value->id)->update(['branch_id' => 27]);
            $updatetransaction = Transcation::where('id', $DaybookTransaction->id)->update(['branch_id' => 27]);
            $updatetransaction = \App\Models\TranscationLog::where('transaction_id', $DaybookTransaction->id)->update(['branch_id' => 27]);
        }
        die('success');
    }
    // Update Branch cash bu daybook Ref id
    public function updateBranchCash()
    {
        $getData = BranchDaybook::whereIn('id', [123423, 123424, 123425, 123426, 123427, 123428, 123429, 123430, 123431, 123432, 123433, 123434, 123435, 123436, 123437, 123438, 123359, 123360, 123600, 123601, 123602, 123603, 123604, 123605, 123606, 123607, 123608, 123609])->get();
        foreach ($getData as $key => $value) {
            $data = \App\Models\BranchCash::where('branch_id', 30)->whereDate('entry_date', '>=', $value->entry_date)->get();
            foreach ($data as $key => $value2) {
                $recordUpdate = \App\Models\BranchCash::find($value2->id);
                $recordUpdate->balance = $value2->balance - $value->amount;
                $recordUpdate->save();
                if ($value2->entry_date == $value->entry_date) {
                    $recordUpdate->closing_balance = $value2->balance - $value->amount;
                    $recordUpdate->save();
                }
            }
            $data2 = \App\Models\BranchClosing::where('branch_id', 30)->whereDate('entry_date', '>=', $value->entry_date)->get();
            foreach ($data2 as $key => $value3) {
                $recordUpdate2 = \App\Models\BranchClosing::find($value3->id);
                $recordUpdate2->balance = $value3->balance - $value->amount;
                $recordUpdate2->save();
                if ($value3->entry_date == $value->entry_date) {
                    $recordUpdate2->closing_balance = $value3->balance - $value->amount;
                    //$recordUpdate2->save();
                }
            }
            $data3 = \App\Models\BranchCash::where('branch_id', 27)->whereDate('entry_date', '>=', $value->entry_date)->get();
            foreach ($data3 as $key => $value4) {
                $recordUpdate3 = \App\Models\BranchCash::find($value4->id);
                $recordUpdate3->balance = $value4->balance + $value->amount;
                $recordUpdate3->save();
                if ($value4->entry_date == $value->entry_date) {
                    $recordUpdate3->closing_balance = $value4->balance + $value->amount;
                    //$recordUpdate3->save();
                }
            }
            $data4 = \App\Models\BranchClosing::where('branch_id', 27)->whereDate('entry_date', '>=', $value->entry_date)->get();
            foreach ($data4 as $key => $value5) {
                $recordUpdate4 = \App\Models\BranchClosing::find($value5->id);
                $recordUpdate4->balance = $value5->balance + $value->amount;
                //$recordUpdate4->save();
                if ($value5->entry_date == $value->entry_date) {
                    $recordUpdate4->closing_balance = $value5->balance + $value->amount;
                    //$recordUpdate4->save();
                }
            }
        }
        die('success');
    }
    // public function update_branch_cash_daywise()
    // {
    //   $startdate =  new Carbon('2020-04-30');
    //   $enddate =  new Carbon('2021-08-09');
    //   for($i = $startdate;$i<=$enddate ;$i->modify('+1 day'))
    //   {
    //     $dataCR = BranchDaybook::where('type','!=',5)->where('branch_id',2)->where('payment_type','CR')->where('payment_mode',0)->where('entry_date',$i->format('Y-m-d'))->sum('amount');
    //     $dataDR = BranchDaybook::where('type','!=',5)->where('branch_id',2)->where('payment_type','DR')->where('payment_mode',0)->where('entry_date',$i->format('Y-m-d'))->sum('amount');
    //     $total = (float)$dataCR - (float)$dataDR;
    //     $previousRecord = \App\Models\BranchCash::where('branch_id',2)->where('entry_date','<',$i->format('Y-m-d'))->orderBy('entry_date','desc')->first();
    //     if($previousRecord){
    //         $RecordUpdate = \App\Models\BranchCash::where('branch_id',2)->where('entry_date',$i->format('Y-m-d'))->update(['opening_balance'=>$previousRecord->closing_balance,'balance'=>$previousRecord->balance + $total,'closing_balance'=>$previousRecord->balance + $total]);
    //     }else{
    //        $RecordUpdate = \App\Models\BranchCash::where('branch_id',2)->where('entry_date',$i->format('Y-m-d'))->update(['opening_balance'=>0,'balance'=>$total,'closing_balance'=>$total]);
    //     }
    //     $previousRecord = \App\Models\BranchCash::where('branch_id',2)->where('entry_date','<',$i->format('Y-m-d'))->orderBy('entry_date','desc')->first();
    //    if($previousRecord){
    //      echo "<table border='1'>";
    //                 echo"<tr>
    //                 <th>date</th>
    //                 <th>opening_balance</th>
    //                  <th>closing_balance</th>
    //                  <th>balance(Previoud Balance + total)</th>
    //                  <th>CR balance</th>
    //                  <th>DR balance</th>
    //                    <th>Total</th>
    //                 </tr>";
    //                 echo "<tr><td>".$i->format('Y-m-d')."</td>
    //                  <td>".$previousRecord->closing_balance."</td>
    //                  <td>".($previousRecord->balance +  $total). "</td>
    //                  <td>".($previousRecord->balance +  $total). "</td>
    //                   <td>". $dataCR . "</td>
    //                    <td>". $dataDR . "</td>
    //                     <td>".$total."</td>
    //             </tr>";
    //              echo "</table>";
    //     }
    //   }
    //   '<br/>';
    //  die('success');
    // }
    // Update Samraddh Bank Closing Balance
    // public function update_bank_balance_daywise()
//     {
//         $startdate =  new Carbon('2019-10-01');
//       $enddate =  new Carbon('2021-11-20');
//       for($i = $startdate;$i<=$enddate ;$i->modify('+1 day'))
//       {
//         $dataCR = \App\Models\SamraddhBankDaybook::where('bank_id',1)->where('payment_type','CR')->where('entry_date',$i->format('Y-m-d'))->where('is_deleted',0)->sum('amount');
//         $dataDR = \App\Models\SamraddhBankDaybook::where('bank_id',1)->where('payment_type','DR')->where('entry_date',$i->format('Y-m-d'))->where('is_deleted',0)->sum('amount');
//         // $data = \App\Models\SamraddhBankDaybook::where('bank_id',4)->where('payment_type','DR')->where('entry_date',$i->format('Y-m-d'))->first();
    //         $total = (float)$dataCR - (float)$dataDR;
    //         $previousRecord = \App\Models\SamraddhBankClosing::where('bank_id',1)->where('entry_date','<',$i->format('Y-m-d'))->orderBy('entry_date','desc')->first();
// 		 $currentRecord = \App\Models\SamraddhBankClosing::where('bank_id',1)->where('entry_date',$i->format('Y-m-d'))->orderBy('entry_date','desc')->first();
    // 		if($currentRecord){
// 			 if($previousRecord){
//             $RecordUpdate = \App\Models\SamraddhBankClosing::where('bank_id',1)->where('entry_date',$i->format('Y-m-d'))->update(['opening_balance'=>$previousRecord->closing_balance,'balance'=>$previousRecord->balance + $total,'closing_balance'=>$previousRecord->balance + $total]);
//         }else{
//            $RecordUpdate = \App\Models\SamraddhBankClosing::where('bank_id',1)->where('entry_date',$i->format('Y-m-d'))->update(['opening_balance'=>0,'balance'=>$total,'closing_balance'=>$total]);
//         }
    // 		}
// 		elseif($total > 0 || $total < 0){
// 			$data['bank_id'] = 1 ;
// 			$data['account_id'] = 2;
// 			if($previousRecord)
// 			{
// 				$data['opening_balance'] = $previousRecord->closing_balance;
// 				$data['balance'] = $previousRecord->balance + $total;
// 				$data['closing_balance'] = $previousRecord->balance + $total;
    // 			}
// 			else{
// 				$data['opening_balance'] = 0;
// 				$data['balance'] =$total;
// 				$data['closing_balance'] = $total;
// 			}
// 			$data['loan_opening_balance'] = 0;
// 			$data['loan_balance'] = 0;
// 			$data['loan_closing_balance'] = 0;
// 			$data['type'] = 0;
// 			$data['entry_date'] = $i->format('Y-m-d');
// 			$data['entry_time'] = null ;
// 			$data['created_at'] = $i->format('Y-m-d');
// 			$data['updated_at'] = $i->format('Y-m-d');
// 			$createdRow = \App\Models\SamraddhBankClosing::create($data);
    // 		}
    //        if($previousRecord){
//          echo "<table border='1'>";
//                     echo"<tr>
//                     <th>date</th>
    //                     <th>opening_balance</th>
//                      <th>closing_balance</th>
//                      <th>balance(Previoud Balance + total)</th>
//                      <th>CR balance</th>
//                      <th>DR balance</th>
//                        <th>Total</th>
    //                     </tr>";
//                     echo "<tr><td>".$i->format('Y-m-d')."</td>
    //                      <td>".$previousRecord->closing_balance."</td>
//                      <td>".($previousRecord->balance +  $total). "</td>
//                      <td>".($previousRecord->balance +  $total). "</td>
//                       <td>". $dataCR . "</td>
//                        <td>". $dataDR . "</td>
//                         <td>".$total."</td>
    //                 </tr>";
//                  echo "</table>";
//         }
//       }
//       '<br/>';
//     }
    public function update_bank_balance_daywise(Request $request)
    {
        $startdate = date('Y-m-d', strtotime(convertDate($request->start_date)));
        $startdate = new Carbon($startdate);
        $enddate = date('Y-m-d', strtotime(convertDate($request->end_date)));
        $bankId = $request->bank_name;
        $headId = getSamraddhBank($bankId)->account_head_id;
        // if($bankId == 1)
        // {
        //     $headId = 69;
        // }
        // elseif($bankId == 2)
        // {
        //     $headId = 70;
        // }
        // elseif($bankId == 3)
        // {
        //     $headId = 68;
        // }
        // elseif($bankId == 4)
        // {
        //     $headId = 91;
        // }
        // elseif($bankId == 5)
        // {
        //     $headId = 145;
        // }
        for ($i = $startdate; $i <= $enddate; $i->modify('+1 day')) {
            // $dataCR = \App\Models\SamraddhBankDaybook::where('bank_id',$bankId)->where('payment_type','CR')->where('entry_date',$i->format('Y-m-d'))->where('is_deleted',0)->sum('amount');
            // $dataDR = \App\Models\SamraddhBankDaybook::where('bank_id',$bankId)->where('payment_type','DR')->where('entry_date',$i->format('Y-m-d'))->where('is_deleted',0)->sum('amount');
            $dataCR = \App\Models\AllHeadTransaction::where('head_id', $headId)->where('payment_type', 'CR')->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            $dataDR = \App\Models\AllHeadTransaction::where('head_id', $headId)->where('payment_type', 'DR')->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            // $data = \App\Models\SamraddhBankDaybook::where('bank_id',4)->where('payment_type','DR')->where('entry_date',$i->format('Y-m-d'))->first();
            $total = (float) $dataDR - (float) $dataCR;
            $previousRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bankId)->where('entry_date', '<', $i->format('Y-m-d'))->orderBy('entry_date', 'desc')->first();
            $currentRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bankId)->where('entry_date', $i->format('Y-m-d'))->orderBy('entry_date', 'desc')->first();
            if ($currentRecord) {
                if ($previousRecord) {
                    $RecordUpdate = \App\Models\SamraddhBankClosing::where('bank_id', $bankId)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => $previousRecord->closing_balance, 'balance' => $previousRecord->balance + $total, 'closing_balance' => $previousRecord->balance + $total]);
                } else {
                    $RecordUpdate = \App\Models\SamraddhBankClosing::where('bank_id', $bankId)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => 0, 'balance' => $total, 'closing_balance' => $total]);
                }
            } elseif ($total > 0 || $total < 0) {
                $data['bank_id'] = $bankId;
                $data['account_id'] = $bankId;
                if ($previousRecord) {
                    $data['opening_balance'] = $previousRecord->closing_balance;
                    $data['balance'] = $previousRecord->balance + $total;
                    $data['closing_balance'] = $previousRecord->balance + $total;
                } else {
                    $data['opening_balance'] = 0;
                    $data['balance'] = $total;
                    $data['closing_balance'] = $total;
                }
                $data['loan_opening_balance'] = 0;
                $data['loan_balance'] = 0;
                $data['loan_closing_balance'] = 0;
                $data['type'] = 0;
                $data['entry_date'] = $i->format('Y-m-d');
                $data['entry_time'] = null;
                $data['created_at'] = $i->format('Y-m-d');
                $data['updated_at'] = $i->format('Y-m-d');
                $createdRow = \App\Models\SamraddhBankClosing::create($data);
            }
        }
        return redirect()->back()->with('success', 'Balance Updated Suceessfully', '!success');
    }
    public function update_bank_balance_daywise2(Request $request)
    {
        $startdate = new Carbon('2020-06-01');
        $enddate = new Carbon('2020-10-01');
        $bankId = 1;
        if ($bankId == 1) {
            $headId = 69;
        } elseif ($bankId == 2) {
            $headId = 70;
        } elseif ($bankId == 3) {
            $headId = 68;
        } elseif ($bankId == 4) {
            $headId = 91;
        } elseif ($bankId == 5) {
            $headId = 145;
        }
        for ($i = $startdate; $i <= $enddate; $i->modify('+1 day')) {
            // $dataCR = \App\Models\SamraddhBankDaybook::where('bank_id',$bankId)->where('payment_type','CR')->where('entry_date',$i->format('Y-m-d'))->where('is_deleted',0)->sum('amount');
            // $dataDR = \App\Models\SamraddhBankDaybook::where('bank_id',$bankId)->where('payment_type','DR')->where('entry_date',$i->format('Y-m-d'))->where('is_deleted',0)->sum('amount');
            $dataCR = \App\Models\AllHeadTransaction::where('head_id', $headId)->where('payment_type', 'CR')->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            $dataDR = \App\Models\AllHeadTransaction::where('head_id', $headId)->where('payment_type', 'DR')->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            // $data = \App\Models\SamraddhBankDaybook::where('bank_id',4)->where('payment_type','DR')->where('entry_date',$i->format('Y-m-d'))->first();
            $total = (float) $dataDR - (float) $dataCR;
            $previousRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bankId)->where('entry_date', '<', $i->format('Y-m-d'))->orderBy('entry_date', 'desc')->first();
            $currentRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bankId)->where('entry_date', $i->format('Y-m-d'))->orderBy('entry_date', 'desc')->first();
            $RecordUpdate = \App\Models\SamraddhBankClosing::where('bank_id', $bankId)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => 0, 'balance' => 0, 'closing_balance' => 0, 'loan_opening_balance' => 0, 'loan_balance' => 0, 'loan_closing_balance' => 0]);
            if ($currentRecord) {
                if ($previousRecord) {
                    $RecordUpdate = \App\Models\SamraddhBankClosing::where('bank_id', $bankId)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => $previousRecord->closing_balance, 'balance' => $previousRecord->balance + $total, 'closing_balance' => $previousRecord->balance + $total]);
                } else {
                    $RecordUpdate = \App\Models\SamraddhBankClosing::where('bank_id', $bankId)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => 0, 'balance' => $total, 'closing_balance' => $total]);
                }
            } elseif ($total > 0 || $total < 0) {
                $data['bank_id'] = $bankId;
                $data['account_id'] = $bankId;
                if ($previousRecord) {
                    $data['opening_balance'] = $previousRecord->closing_balance;
                    $data['balance'] = $previousRecord->balance + $total;
                    $data['closing_balance'] = $previousRecord->balance + $total;
                } else {
                    $data['opening_balance'] = 0;
                    $data['balance'] = $total;
                    $data['closing_balance'] = $total;
                }
                $data['loan_opening_balance'] = 0;
                $data['loan_balance'] = 0;
                $data['loan_closing_balance'] = 0;
                $data['type'] = 0;
                $data['entry_date'] = $i->format('Y-m-d');
                $data['entry_time'] = null;
                $data['created_at'] = $i->format('Y-m-d');
                $data['updated_at'] = $i->format('Y-m-d');
                $createdRow = \App\Models\SamraddhBankClosing::create($data);
            }
        }
        return redirect()->back()->with('success', 'Balance Updated Suceessfully', '!success');
    }
    //Update Branch cash balance
    public function update_branch_cash_daywise(Request $request)
    {
        $branch = $request->branch_id;
        $branchDetail = \App\Models\Branch::select('id', 'date')->where('id', $branch)->first();
        $startdate = date('Y-m-d', strtotime(convertDate($request->start_date)));
        $startdate = new Carbon($startdate);
        $enddate = date('Y-m-d', strtotime("+1 day", strtotime(convertDate($request->end_date))));
        for ($i = $startdate; $i <= $enddate; $i->modify('+1 day')) {
            $dataCR = \App\Models\AllHeadTransaction::where('branch_id', $branch)->where('payment_type', 'CR')->where('head_id', 28)->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            $dataDR = \App\Models\AllHeadTransaction::where('branch_id', $branch)->where('payment_type', 'DR')->where('head_id', 28)->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            $total = (float) $dataDR - (float) $dataCR;
            $previousRecord = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', '<', $i->format('Y-m-d'))->orderBy('entry_date', 'desc')->first();
            $currentRecord = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', $i->format('Y-m-d'))->orderBy('entry_date', 'desc')->first();
            $RecordUpdate = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => 0, 'balance' => 0, 'closing_balance' => 0, 'loan_opening_balance' => 0, 'loan_balance' => 0, 'loan_closing_balance' => 0]);
            if ($currentRecord) {
                if ($previousRecord) {
                    $RecordUpdate = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => $previousRecord->closing_balance, 'balance' => $previousRecord->balance + $total, 'closing_balance' => $previousRecord->balance + $total, 'loan_opening_balance' => 0, 'loan_balance' => 0, 'loan_closing_balance' => 0]);
                } else {
                    $RecordUpdate = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => 0, 'balance' => $total, 'closing_balance' => $total, 'loan_opening_balance' => 0, 'loan_balance' => 0, 'loan_closing_balance' => 0]);
                }
            } elseif ($total > 0 || $total < 0) {
                $data['branch_id'] = $branch;
                if ($previousRecord) {
                    $data['opening_balance'] = $previousRecord->closing_balance;
                    $data['balance'] = $previousRecord->balance + $total;
                    $data['closing_balance'] = $previousRecord->balance + $total;
                    $data['loan_opening_balance'] = 0;
                    $data['loan_balance'] = 0;
                    $data['loan_closing_balance'] = 0;
                } else {
                    $data['opening_balance'] = 0;
                    $data['balance'] = $total;
                    $data['closing_balance'] = $total;
                    $data['loan_opening_balance'] = 0;
                    $data['loan_balance'] = 0;
                    $data['loan_closing_balance'] = 0;
                }
                $data['entry_date'] = $i->format('Y-m-d');
                $data['entry_time'] = 1;
                $data['created_at'] = $i->format('Y-m-d');
                $data['updated_at'] = $i->format('Y-m-d');
                $createdRow = \App\Models\BranchCash::create($data);
            }
        }
        return redirect()->back()->with('success', 'Balance Updated Suceessfully', '!success');
    }
    public function update_branch_cash_daywise2()
    {
        $startdate = new Carbon('2022-04-01');
        $enddate = new Carbon('2023-03-28');
        $branch = 4;
        for ($i = $startdate; $i <= $enddate; $i->modify('+1 day')) {
            $dataCR = \App\Models\AllHeadTransaction::where('branch_id', $branch)->where('payment_type', 'CR')->where('head_id', 28)->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            $dataDR = \App\Models\AllHeadTransaction::where('branch_id', $branch)->where('payment_type', 'DR')->where('head_id', 28)->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            $total = (float) $dataDR - (float) $dataCR;
            $previousRecord = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', '<', $i->format('Y-m-d'))->orderBy('entry_date', 'desc')->first();
            $currentRecord = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', $i->format('Y-m-d'))->orderBy('entry_date', 'desc')->first();
            $RecordUpdate = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => 0, 'balance' => 0, 'closing_balance' => 0, 'loan_opening_balance' => 0, 'loan_balance' => 0, 'loan_closing_balance' => 0]);
            if ($currentRecord) {
                if ($previousRecord) {
                    $RecordUpdate = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => $previousRecord->closing_balance, 'balance' => $previousRecord->balance + $total, 'closing_balance' => $previousRecord->balance + $total, 'loan_opening_balance' => 0, 'loan_balance' => 0, 'loan_closing_balance' => 0]);
                } else {
                    $RecordUpdate = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => 0, 'balance' => $total, 'closing_balance' => $total, 'loan_opening_balance' => 0, 'loan_balance' => 0, 'loan_closing_balance' => 0]);
                }
            } elseif ($total > 0 || $total < 0) {
                $data['branch_id'] = $branch;
                if ($previousRecord) {
                    $data['opening_balance'] = $previousRecord->closing_balance;
                    $data['balance'] = $previousRecord->balance + $total;
                    $data['closing_balance'] = $previousRecord->balance + $total;
                    $data['loan_opening_balance'] = 0;
                    $data['loan_balance'] = 0;
                    $data['loan_closing_balance'] = 0;
                } else {
                    $data['opening_balance'] = 0;
                    $data['balance'] = $total;
                    $data['closing_balance'] = $total;
                    $data['loan_opening_balance'] = 0;
                    $data['loan_balance'] = 0;
                    $data['loan_closing_balance'] = 0;
                }
                $data['entry_date'] = $i->format('Y-m-d');
                $data['entry_time'] = 1;
                $data['created_at'] = $i->format('Y-m-d');
                $data['updated_at'] = $i->format('Y-m-d');
                $createdRow = \App\Models\BranchCash::create($data);
            }
            if ($previousRecord) {
                echo "<table border='1'>";
                echo "<tr>
                    <th>date</th>
                    <th>opening_balance</th>
                     <th>closing_balance</th>
                     <th>balance(Previoud Balance + total)</th>
                     <th>CR balance</th>
                     <th>DR balance</th>
                       <th>Total</th>
                    </tr>";
                echo "<tr><td>" . $i->format('Y-m-d') . "</td>
                     <td>" . $previousRecord->closing_balance . "</td>
                     <td>" . ($previousRecord->balance + $total) . "</td>
                     <td>" . ($previousRecord->balance + $total) . "</td>
                      <td>" . $dataCR . "</td>
                       <td>" . $dataDR . "</td>
                        <td>" . $total . "</td>
                </tr>";
                echo "</table>";
            }
        }
        '<br/>';
    }
    public function getInvestmentMonthlyInterestDeposit()
    {
        $records = \App\Models\InvestmentMonthlyYearlyInterestDeposits::where('yearly_deposit_amount', '!=', null)->get();
        foreach ($records as $value) {
            $getRecord = AllTransaction::where('type', 3)->where('sub_type', 34)->where('payment_type', 'CR')->where('type_id', $value->investment_id)->orderBy('created_at', 'desc')->first();
            $ifexists = BranchDaybook::where('daybook_ref_id', $getRecord->daybook_ref_id)->count();
            if ($ifexists == 0) {
                $record['daybook_ref_id'] = $getRecord->daybook_ref_id;
                $record['branch_id'] = $getRecord->branch_id;
                $record['type'] = 3;
                $record['sub_type'] = 34;
                $record['type_id'] = $getRecord->type_id;
                $record['type_transaction_id'] = $getRecord->type_transaction_id;
                $record['associate_id'] = NULL;
                $record['member_id'] = $getRecord->member_id;
                $record['branch_id_to'] = null;
                $record['branch_id_from'] = null;
                $record['opening_balance'] = $value->yearly_deposit_amount;
                $record['amount'] = $value->yearly_deposit_amount;
                $record['closing_balance'] = $value->yearly_deposit_amount;
                $record['description'] = ($getRecord->description);
                $record['description_dr'] = getMemberData($getRecord->member_id)->first_name . ' ' . getMemberData($getRecord->member_id)->last_name . ' Dr ' . number_format((float) $value->yearly_deposit_amount, 2, '.', '');
                $record['description_cr'] = 'To Monthly Income scheme A/C Cr ' . number_format((float) $value->yearly_deposit_amount, 2, '.', '');
                $record['payment_type'] = $getRecord->payment_type;
                $record['payment_mode'] = $getRecord->payment_mode;
                $record['currency_code'] = $getRecord->currency_code;
                $record['amount_to_id'] = $getRecord->amount_to_id;
                $record['amount_to_name'] = $getRecord->amount_to_name;
                $record['amount_from_id'] = null;
                $record['amount_from_name'] = null;
                $record['v_no'] = $getRecord->v_no;
                $record['v_date'] = $getRecord->v_date;
                ;
                $record['ssb_account_id_from'] = null;
                $record['ssb_account_id_to'] = null;
                $record['cheque_no'] = null;
                $record['cheque_date'] = null;
                $record['cheque_bank_from'] = null;
                $record['cheque_bank_ac_from'] = null;
                $record['cheque_bank_ifsc_from'] = null;
                $record['cheque_bank_branch_from'] = null;
                $record['cheque_bank_from_id'] = null;
                $record['cheque_bank_ac_from_id'] = null;
                $record['cheque_bank_to'] = null;
                $record['cheque_bank_ac_to'] = null;
                $record['cheque_bank_to_name'] = null;
                $record['cheque_bank_to_branch'] = null;
                $record['cheque_bank_to_ac_no'] = null;
                $record['cheque_bank_to_ifsc'] = null;
                $record['transction_no'] = null;
                $record['transction_bank_from'] = null;
                $record['transction_bank_ac_from'] = null;
                $record['transction_bank_ifsc_from'] = null;
                $record['transction_bank_branch_from'] = null;
                $record['transction_bank_from_id'] = null;
                $record['transction_bank_from_ac_id'] = null;
                $record['transction_bank_to'] = null;
                $record['transction_bank_ac_to'] = null;
                $record['transction_bank_to_name'] = null;
                $record['transction_bank_to_ac_no'] = null;
                $record['transction_bank_to_branch'] = null;
                $record['transction_bank_to_ifsc'] = null;
                $record['transction_date'] = null;
                $record['entry_date'] = $getRecord->entry_date;
                ;
                $record['entry_time'] = $getRecord->entry_time;
                $record['created_by'] = $getRecord->created_by;
                $record['created_by_id'] = $getRecord->created_by_id;
                $record['is_contra'] = 0;
                $record['contra_id'] = NULL;
                $record['created_at'] = $value->created_at;
                $record['updated_at'] = $value->updated_at;
                $record['ssb_account_tran_id_to'] = null;
                $record['ssb_account_tran_id_from'] = null;
                $record['amount_type'] = 0;
                $insertData = BranchDaybook::create($record);
                print_r("success");
            }
        }
    }
    // Update branch daybook amount  Emergency maturity type
    public function update_emergency_maturity_type_amount_branch_daybook()
    {
        $records = AllHeadTransaction::where('type', 13)->where('sub_type', 137)->where('branch_id', 3)->where('head_id', 28)->where('payment_type', 'CR')->where('payment_mode', 0)->get();
        foreach ($records as $value) {
            $day_book_id = $value->daybook_ref_id;
            $data = BranchDaybook::where('branch_id', 3)->where('daybook_ref_id', $day_book_id)->update(['amount' => $value->amount, 'opening_balance' => $value->amount, 'closing_balance' => $value->amount]);
        }
    }
    //  Update Eli record in all_tranaction
    public function update_eli_transaction_all_transaction()
    {
        $records = BranchDaybook::where('type', 3)->where('sub_type', 30)->where('branch_id', 6)->where('payment_type', 'CR')->where('payment_mode', 0)->get();
        foreach ($records as $value) {
            $day_book_id = $value->daybook_ref_id;
            $data = AllHeadTransaction::where('daybook_ref_id', $day_book_id)->where('head_id', 28)->count();
            dd($records);
            if ($data == 0) {
                $record['daybook_ref_id'] = $value->daybook_ref_id;
                $record['branch_id'] = $value->branch_id;
                $record['type'] = 3;
                $record['sub_type'] = 30;
                $record['head_id'] = 28;
                $record['type_id'] = $value->type_id;
                $record['type_transaction_id'] = $value->type_transaction_id;
                ;
                $record['associate_id'] = $value->associate_id;
                $record['member_id'] = $value->member_id;
                $record['branch_id_to'] = $value->branch_id_to;
                $record['branch_id_from'] = $value->branch_id_from;
                $record['opening_balance'] = $value->amount;
                $record['amount'] = $value->amount;
                $record['closing_balance'] = $value->amount;
                $record['description'] = $value->description;
                $record['payment_type'] = 'DR';
                $record['payment_mode'] = $value->payment_mode;
                $record['currency_code'] = $value->currency_code;
                $record['amount_to_id'] = $value->amount_to_id;
                $record['amount_to_name'] = $value->amount_to_name;
                $record['amount_from_id'] = $value->amount_from_id;
                ;
                $record['amount_from_name'] = $value->amount_from_name;
                ;
                $record['v_no'] = $value->v_no;
                $record['v_date'] = $value->v_date;
                ;
                $record['ssb_account_id_from'] = $value->ssb_account_id_from;
                $record['ssb_account_id_to'] = $value->ssb_account_id_to;
                $record['cheque_no'] = null;
                $record['cheque_date'] = null;
                $record['cheque_bank_from'] = null;
                $record['cheque_bank_ac_from'] = null;
                $record['cheque_bank_ifsc_from'] = null;
                $record['cheque_bank_branch_from'] = null;
                $record['cheque_bank_from_id'] = null;
                $record['cheque_bank_ac_from_id'] = null;
                $record['cheque_bank_to'] = null;
                $record['cheque_bank_ac_to'] = null;
                $record['cheque_bank_to_name'] = null;
                $record['cheque_bank_to_branch'] = null;
                $record['cheque_bank_to_ac_no'] = null;
                $record['cheque_bank_to_ifsc'] = null;
                $record['transction_no'] = null;
                $record['transction_bank_from'] = null;
                $record['transction_bank_ac_from'] = null;
                $record['transction_bank_ifsc_from'] = null;
                $record['transction_bank_branch_from'] = null;
                $record['transction_bank_from_id'] = null;
                $record['transction_bank_from_ac_id'] = null;
                $record['transction_bank_to'] = null;
                $record['transction_bank_ac_to'] = null;
                $record['transction_bank_to_name'] = null;
                $record['transction_bank_to_ac_no'] = null;
                $record['transction_bank_to_branch'] = null;
                $record['transction_bank_to_ifsc'] = null;
                $record['transction_date'] = $value->transaction_date;
                $record['entry_date'] = $value->entry_date;
                ;
                $record['entry_time'] = $value->entry_time;
                $record['created_by'] = $value->created_by;
                $record['created_by_id'] = $value->created_by_id;
                $record['created_at'] = $value->created_at;
                $record['updated_at'] = $value->updated_at;
                $record['ssb_account_tran_id_to'] = null;
                $record['ssb_account_tran_id_from'] = null;
                $insertData = AllHeadTransaction::create($record);
                print_r("success");
            }
        }
    }
    //  Update Transaction entry_date in  all_transaction
    public function update_transaction_date_all_transaction()
    {
        $records = Daybook::where('transaction_type', 4)->where('is_eli', 1)->get();
        foreach ($records as $value) {
            $entry_date = date("Y-m-d", strtotime($value->created_at));
            $data = AllTransaction::where('type', '3')->where('sub_type', '34')->where('type_id', $value->investment_id)->count();
            if ($data > 0) {
                $datas = AllTransaction::where('type', '3')->where('sub_type', '34')->where('type_id', $value->investment_id)->where('amount', $value->deposit)->update(['created_at' => $value->created_at, 'entry_date' => $entry_date]);
                print($datas);
            }
        }
    }
    public function insert_saving_record()
    {
        $branch_id = 1;
        $saving_accounts_transaction = SavingAccountTranscation::where('type', 1)->where('branch_id', $branch_id)->get();
        foreach ($saving_accounts_transaction as $key => $value) {
            $entry_date = date("Y-m-d", strtotime($value->created_at));
            $entry_time = $value->created_at->format('H:i:s');
            $data['amount'] = $value->deposit;
            $data['entry_date'] = $entry_date;
            $data['entry_time'] = $entry_time;
            $data['created_at'] = $value->created_at;
            $data['updated_at'] = $value->updated_at;
            $branch_ref = \App\Models\BranchDaybookReference::create($data);
            $daybook_ref_id = $branch_ref->id;
            $existsBranchDaybook = BranchDaybook::where('type', 4)->where('sub_type', 41)->where('branch_id', $branch_id)->where('type_id', $value->saving_account_id)->where('type_transaction_id', $value->id)->count();
            if ($existsBranchDaybook == 0) {
                $savingAccount = SavingAccount::where('id', $value->saving_account_id)->first();
                $data_branch['daybook_ref_id'] = $daybook_ref_id;
                $data_branch['branch_id'] = $value->branch_id;
                $data_branch['type'] = 4;
                $data_branch['sub_type'] = 41;
                $data_branch['type_id'] = $value->saving_account_id;
                $data_branch['type_transaction_id'] = $value->id;
                $data_branch['associate_id'] = $value->associate_id;
                $data_branch['member_id'] = $savingAccount->member_id;
                $data_branch['branch_id_to'] = null;
                $data_branch['branch_id_from'] = null;
                $data_branch['opening_balance'] = null;
                $data_branch['amount'] = $value->deposit;
                $data_branch['closing_balance'] = null;
                $data_branch['description'] = 'Amount received for SSB A/C Deposit' . $savingAccount->account_no . 'through cash' . ($savingAccount->branch_code);
                $data_branch['description_dr'] = 'Cash A/c Dr' . number_format((float) $value->deposit, 2, '.', '') . '/-';
                $data_branch['description_cr'] = 'To SSB' . $savingAccount->account_no . ' A/c Cr' . number_format((float) $value->deposit, 2, '.', '') . '/-';
                ;
                $data_branch['payment_type'] = $value->payment_type;
                $data_branch['payment_mode'] = $value->payment_mode;
                $data_branch['currency_code'] = $value->currency_code;
                $data_branch['amount_to_id'] = null;
                $data_branch['amount_to_name'] = null;
                $data_branch['amount_from_id'] = null;
                $data_branch['amount_from_name'] = null;
                $data_branch['v_no'] = null;
                $data_branch['v_date'] = null;
                $data_branch['ssb_account_id_from'] = null;
                $data_branch['ssb_account_id_to'] = null;
                $data_branch['cheque_no'] = null;
                $data_branch['cheque_date'] = null;
                $data_branch['cheque_bank_from'] = null;
                $data_branch['cheque_bank_ac_from'] = null;
                $data_branch['cheque_bank_ifsc_from'] = null;
                $data_branch['cheque_bank_branch_from'] = null;
                $data_branch['cheque_bank_from_id'] = null;
                $data_branch['cheque_bank_ac_from_id'] = null;
                $data_branch['cheque_bank_to'] = null;
                $data_branch['cheque_bank_ac_to'] = null;
                $data_branch['cheque_bank_to_name'] = null;
                $data_branch['cheque_bank_to_branch'] = null;
                $data_branch['cheque_bank_to_ac_no'] = null;
                $data_branch['cheque_bank_to_ifsc'] = null;
                $data_branch['transction_no'] = null;
                $data_branch['transction_bank_from'] = null;
                $data_branch['transction_bank_ac_from'] = null;
                $data_branch['transction_bank_ifsc_from'] = null;
                $data_branch['transction_bank_branch_from'] = null;
                $data_branch['transction_bank_from_id'] = null;
                $data_branch['transction_bank_from_ac_id'] = null;
                $data_branch['transction_bank_to'] = null;
                $data_branch['transction_bank_ac_to'] = null;
                $data_branch['transction_bank_to_name'] = null;
                $data_branch['transction_bank_to_ac_no'] = null;
                $data_branch['transction_bank_to_branch'] = null;
                $data_branch['transction_bank_to_ifsc'] = null;
                $data_branch['transction_date'] = null;
                $data_branch['entry_date'] = $entry_date;
                ;
                $data_branch['entry_time'] = $entry_time;
                $data_branch['created_by'] = $savingAccount->created_by;
                $data_branch['created_by_id'] = $savingAccount->created_by_id;
                $data_branch['is_contra'] = NULL;
                $data_branch['contra_id'] = NULL;
                $data_branch['created_at'] = $value->created_at;
                $data_branch['updated_at'] = $value->updated_at;
                $data_branch['ssb_account_tran_id_to'] = null;
                $data_branch['ssb_account_tran_id_from'] = null;
                $data_branch['amount_type'] = 0;
                $insertData1 = BranchDaybook::create($data_branch);
                print_r("success");
            }
            $existsBranchDaybook = MemberTransaction::where('type', 4)->where('sub_type', 41)->where('branch_id', $branch_id)->where('type_id', $value->saving_account_id)->where('type_transaction_id', $value->id)->count();
            if ($existsBranchDaybook == 0) {
                $savingAccount = SavingAccount::where('id', $value->saving_account_id)->first();
                $member_transa['daybook_ref_id'] = $daybook_ref_id;
                $member_transa['branch_id'] = $value->branch_id;
                $member_transa['type'] = 4;
                $member_transa['sub_type'] = 41;
                $member_transa['type_id'] = $value->saving_account_id;
                $member_transa['type_transaction_id'] = $value->id;
                $member_transa['associate_id'] = $value->associate_id;
                $member_transa['member_id'] = $savingAccount->member_id;
                $member_transa['amount'] = $value->deposit;
                $member_transa['description'] = 'Amount received for SSB A/C Deposit ' . $savingAccount->account_no . ' through cash' . ($savingAccount->branch_code);
                $member_transa['payment_type'] = $value->payment_type;
                $member_transa['payment_mode'] = $value->payment_mode;
                $member_transa['currency_code'] = $value->currency_code;
                $member_transa['amount_to_id'] = null;
                $member_transa['amount_to_name'] = null;
                $member_transa['amount_from_id'] = null;
                $member_transa['amount_from_name'] = null;
                $member_transa['v_no'] = null;
                $member_transa['v_date'] = null;
                $member_transa['ssb_account_id_from'] = null;
                $member_transa['ssb_account_id_to'] = null;
                $member_transa['cheque_no'] = null;
                $member_transa['cheque_date'] = null;
                $member_transa['cheque_bank_from'] = null;
                $member_transa['cheque_bank_ac_from'] = null;
                $member_transa['cheque_bank_ifsc_from'] = null;
                $member_transa['cheque_bank_branch_from'] = null;
                $member_transa['cheque_bank_from_id'] = null;
                $member_transa['cheque_bank_ac_from_id'] = null;
                $member_transa['cheque_bank_to'] = null;
                $member_transa['cheque_bank_ac_to'] = null;
                $member_transa['cheque_bank_to_name'] = null;
                $member_transa['cheque_bank_to_branch'] = null;
                $member_transa['cheque_bank_to_ac_no'] = null;
                $member_transa['cheque_bank_to_ifsc'] = null;
                $member_transa['transction_no'] = null;
                $member_transa['transction_bank_from'] = null;
                $member_transa['transction_bank_ac_from'] = null;
                $member_transa['transction_bank_ifsc_from'] = null;
                $member_transa['transction_bank_branch_from'] = null;
                $member_transa['transction_bank_from_id'] = null;
                $member_transa['transction_bank_from_ac_id'] = null;
                $member_transa['transction_bank_to'] = null;
                $member_transa['transction_bank_ac_to'] = null;
                $member_transa['transction_bank_to_name'] = null;
                $member_transa['transction_bank_to_ac_no'] = null;
                $member_transa['transction_bank_to_branch'] = null;
                $member_transa['transction_bank_to_ifsc'] = null;
                $member_transa['transction_date'] = null;
                $member_transa['entry_date'] = $entry_date;
                ;
                $member_transa['entry_time'] = $entry_time;
                $member_transa['created_by'] = $savingAccount->created_by;
                $member_transa['created_by_id'] = $savingAccount->created_by_id;
                $member_transa['jv_unique_id'] = NULL;
                $member_transa['cheque_type'] = NULL;
                $member_transa['cheque_id'] = NULL;
                $member_transa['created_at_deafult'] = '2021-08-25 06:29:16';
                $member_transa['created_at'] = $value->created_at;
                $member_transa['updated_at'] = $value->updated_at;
                $member_transa['ssb_account_tran_id_to'] = null;
                $member_transa['ssb_account_tran_id_from'] = null;
                $insertData5 = MemberTransaction::create($member_transa);
                print_r("success");
            }
            $existAllTransactionCashInHand = AllHeadTransaction::where('type', 4)->where('sub_type', 41)->where('branch_id', $branch_id)->where('head_id', 28)->where('type_id', $value->saving_account_id)->where('type_transaction_id', $value->id)->count();
            $existAllTransactionDailyDeposite = AllHeadTransaction::where('type', 4)->where('branch_id', $branch_id)->where('sub_type', 41)->where('head_id', 56)->where('type_id', $value->saving_account_id)->where('type_transaction_id', $value->id)->count();
            if ($existAllTransactionCashInHand == 0) {
                $savingAccount = SavingAccount::where('id', $value->saving_account_id)->first();
                $allTransaction['daybook_ref_id'] = $daybook_ref_id;
                $allTransaction['branch_id'] = $value->branch_id;
                $allTransaction['type'] = 4;
                $allTransaction['sub_type'] = 41;
                $allTransaction['head_id'] = 28;
                $allTransaction['type_id'] = $value->saving_account_id;
                $allTransaction['type_transaction_id'] = $value->id;
                $allTransaction['associate_id'] = $value->associate_id;
                $allTransaction['member_id'] = $savingAccount->member_id;
                $allTransaction['branch_id_to'] = null;
                $allTransaction['branch_id_from'] = null;
                $allTransaction['opening_balance'] = null;
                $allTransaction['amount'] = $value->deposit;
                $allTransaction['closing_balance'] = null;
                $allTransaction['description'] = 'Amount received for SSB A/C Deposit' . $savingAccount->account_no . 'through cash' . ($savingAccount->branch_code);
                ;
                $allTransaction['payment_type'] = 'DR';
                $allTransaction['payment_mode'] = $value->payment_mode;
                $allTransaction['currency_code'] = $value->currency_code;
                $allTransaction['amount_to_id'] = null;
                $allTransaction['amount_to_name'] = null;
                $allTransaction['amount_from_id'] = null;
                $allTransaction['amount_from_name'] = null;
                $allTransaction['v_no'] = null;
                $allTransaction['v_date'] = null;
                ;
                $allTransaction['ssb_account_id_from'] = null;
                $allTransaction['ssb_account_id_to'] = null;
                $allTransaction['cheque_no'] = null;
                $allTransaction['cheque_date'] = null;
                $allTransaction['cheque_bank_from'] = null;
                $allTransaction['cheque_bank_ac_from'] = null;
                $allTransaction['cheque_bank_ifsc_from'] = null;
                $allTransaction['cheque_bank_branch_from'] = null;
                $allTransaction['cheque_bank_from_id'] = null;
                $allTransaction['cheque_bank_ac_from_id'] = null;
                $allTransaction['cheque_bank_to'] = null;
                $allTransaction['cheque_bank_ac_to'] = null;
                $allTransaction['cheque_bank_to_name'] = null;
                $allTransaction['cheque_bank_to_branch'] = null;
                $allTransaction['cheque_bank_to_ac_no'] = null;
                $allTransaction['cheque_bank_to_ifsc'] = null;
                $allTransaction['transction_no'] = null;
                $allTransaction['transction_bank_from'] = null;
                $allTransaction['transction_bank_ac_from'] = null;
                $allTransaction['transction_bank_ifsc_from'] = null;
                $allTransaction['transction_bank_branch_from'] = null;
                $allTransaction['transction_bank_from_id'] = null;
                $allTransaction['transction_bank_from_ac_id'] = null;
                $allTransaction['transction_bank_to'] = null;
                $allTransaction['transction_bank_ac_to'] = null;
                $allTransaction['transction_bank_to_name'] = null;
                $allTransaction['transction_bank_to_ac_no'] = null;
                $allTransaction['transction_bank_to_branch'] = null;
                $allTransaction['transction_bank_to_ifsc'] = null;
                $allTransaction['transction_date'] = null;
                $allTransaction['entry_date'] = $value->entry_date;
                ;
                $allTransaction['entry_time'] = $value->entry_time;
                $allTransaction['created_by'] = $value->created_by;
                $allTransaction['created_by_id'] = $value->created_by_id;
                $allTransaction['created_at'] = $value->created_at;
                $allTransaction['updated_at'] = $value->updated_at;
                $allTransaction['ssb_account_tran_id_to'] = null;
                $allTransaction['ssb_account_tran_id_from'] = null;
                $insertData2 = AllHeadTransaction::create($allTransaction);
                print_r("success");
            }
            if ($existAllTransactionDailyDeposite == 0) {
                $savingAccount = SavingAccount::where('id', $value->saving_account_id)->first();
                $allTransaction2['daybook_ref_id'] = $daybook_ref_id;
                $allTransaction2['branch_id'] = $value->branch_id;
                $allTransaction2['type'] = 4;
                $allTransaction2['sub_type'] = 41;
                $allTransaction2['head_id'] = 56;
                $allTransaction2['head5'] = null;
                $allTransaction2['type_id'] = $value->saving_account_id;
                $allTransaction2['type_transaction_id'] = $value->id;
                $allTransaction2['associate_id'] = $value->associate_id;
                $allTransaction2['member_id'] = $savingAccount->member_id;
                $allTransaction2['branch_id_to'] = null;
                $allTransaction2['branch_id_from'] = null;
                $allTransaction2['opening_balance'] = null;
                $allTransaction2['amount'] = $value->deposit;
                $allTransaction2['closing_balance'] = null;
                $allTransaction2['description'] = 'Amount received for SSB A/C Deposit' . $savingAccount->account_no . 'through cash' . ($savingAccount->branch_code);
                ;
                $allTransaction2['payment_type'] = $value->payment_type;
                $allTransaction2['payment_mode'] = $value->payment_mode;
                $allTransaction2['currency_code'] = $value->currency_code;
                $allTransaction2['amount_to_id'] = null;
                $allTransaction2['amount_to_name'] = null;
                $allTransaction2['amount_from_id'] = null;
                $allTransaction2['amount_from_name'] = null;
                $allTransaction2['v_no'] = null;
                $allTransaction2['v_date'] = null;
                ;
                $allTransaction2['ssb_account_id_from'] = null;
                $allTransaction2['ssb_account_id_to'] = null;
                $allTransaction2['cheque_no'] = null;
                $allTransaction2['cheque_date'] = null;
                $allTransaction2['cheque_bank_from'] = null;
                $allTransaction2['cheque_bank_ac_from'] = null;
                $allTransaction2['cheque_bank_ifsc_from'] = null;
                $allTransaction2['cheque_bank_branch_from'] = null;
                $allTransaction2['cheque_bank_from_id'] = null;
                $allTransaction2['cheque_bank_ac_from_id'] = null;
                $allTransaction2['cheque_bank_to'] = null;
                $allTransaction2['cheque_bank_ac_to'] = null;
                $allTransaction2['cheque_bank_to_name'] = null;
                $allTransaction2['cheque_bank_to_branch'] = null;
                $allTransaction2['cheque_bank_to_ac_no'] = null;
                $allTransaction2['cheque_bank_to_ifsc'] = null;
                $allTransaction2['transction_no'] = null;
                $allTransaction2['transction_bank_from'] = null;
                $allTransaction2['transction_bank_ac_from'] = null;
                $allTransaction2['transction_bank_ifsc_from'] = null;
                $allTransaction2['transction_bank_branch_from'] = null;
                $allTransaction2['transction_bank_from_id'] = null;
                $allTransaction2['transction_bank_from_ac_id'] = null;
                $allTransaction2['transction_bank_to'] = null;
                $allTransaction2['transction_bank_ac_to'] = null;
                $allTransaction2['transction_bank_to_name'] = null;
                $allTransaction2['transction_bank_to_ac_no'] = null;
                $allTransaction2['transction_bank_to_branch'] = null;
                $allTransaction2['transction_bank_to_ifsc'] = null;
                $allTransaction2['transction_date'] = null;
                $allTransaction2['entry_date'] = $value->entry_date;
                ;
                $allTransaction2['entry_time'] = $value->entry_time;
                $allTransaction2['created_by'] = $value->created_by;
                $allTransaction2['created_by_id'] = $value->created_by_id;
                $allTransaction2['created_at'] = $value->created_at;
                $allTransaction2['updated_at'] = $value->updated_at;
                $allTransaction2['ssb_account_tran_id_to'] = null;
                $allTransaction2['ssb_account_tran_id_from'] = null;
                $insertData3 = AllTransaction::create($allTransaction2);
                print_r("success");
            }
            $daybookRecord = Daybook::where('account_no', $value->account_no)->where('branch_id', $branch_id)->orderBy('created_at', 'ASC')->count();
            if ($daybookRecord == 0) {
                $savingAccount = SavingAccount::where('id', $value->saving_account_id)->first();
                $member_name = getMemberData($savingAccount->member_id)->first_name . ' ' . getMemberData($savingAccount->member_id)->last_name;
                $data_log['transaction_type'] = 1;
                $data_log['transaction_id'] = null;
                $data_log['saving_account_transaction_reference_id'] = $value->id;
                $data_log['investment_id'] = $savingAccount->member_investments_id;
                $data_log['account_no'] = $value->account_no;
                $data_log['associate_id'] = $value->associate_id;
                $data_log['member_id'] = $savingAccount->member_id;
                $data_log['opening_balance'] = $value->deposit;
                $data_log['deposit'] = $value->deposit;
                $data_log['withdrawal'] = $value->withdrawal;
                $data_log['description'] = $value->description;
                $data_log['reference_no'] = $value->account_no;
                $data_log['branch_id'] = $value->branch_id;
                $data_log['branch_code'] = $savingAccount->branch_code;
                $data_log['amount'] = $value->deposit;
                $data_log['currency_code'] = 'INR';
                $data_log['payment_mode'] = $value->payment_mode;
                $data_log['payment_type'] = $value->payment_type;
                $data_log['saving_account_id'] = 0;
                $data_log['cheque_dd_no'] = null;
                $data_log['bank_name'] = null;
                $data_log['branch_name'] = null;
                $data_log['payment_date'] = null;
                $data_log['online_payment_id'] = null;
                $data_log['online_payment_by'] = null;
                $data_log['amount_deposit_by_name'] = $member_name;
                $data_log['received_cheque_id'] = null;
                $data_log['cheque_deposit_bank_id'] = null;
                $data_log['cheque_deposit_bank_ac_id'] = null;
                $data_log['online_deposit_bank_id'] = null;
                $data_log['online_deposit_bank_ac_id'] = null;
                $data_log['amount_deposit_by_id'] = $savingAccount->member_id;
                $data_log['created_by_id'] = $savingAccount->created_by_id;
                $data_log['created_by'] = $savingAccount->created_by;
                $data_log['is_renewal'] = $value->is_renewal;
                $data_log['status'] = $value->status;
                $data_log['is_deleted'] = $value->is_deleted;
                $data_log['created_at'] = $value->created_at;
                $data_log['updated_at'] = $value->updated_at;
                $data_log['app_login_user_id'] = $value->app_login_user_id;
                $data_log['is_app'] = $value->is_app;
                $insertData4 = Daybook::create($data_log);
                print_r('success');
            }
        }
        dd('done');
    }
    // Query for correct payment mode of loan type in branch_daybook
    public function correct_loan_payment_mode()
    {
        $branch_id = 1;
        $records = AllTransaction::where('type', 5)->where('sub_type', 57)->where('branch_id', $branch_id)->get();
        foreach ($records as $key => $value) {
            $getRecord = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->first();
            if ($getRecord) {
                $updateRecord = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->update(['payment_mode' => $value->payment_mode]);
                print('success');
            }
        }
        dd('done');
    }
    // Get Account number of multiple transaction on same date
    public function getAccountNumberdaybook()
    {
        $branch_id = 1;
        $records = Daybook::where('branch_id', $branch_id)->get();
        foreach ($records as $key => $value) {
            $entry_date = date("Y-m-d", strtotime(convertDate($value->created_at)));
            $data = Daybook::where('branch_id', $branch_id)->where('account_no', $value->account_no)->where(\DB::raw('DATE(created_at)'), $entry_date)->count();
            if ($data > 1) {
                echo "<table border='1'>";
                echo "<tr>
                    <th>Account Number</th>
                    <th>Count</th>
                     <th>Date</th>
                    </tr>";
                echo "<tr>
                     <td>" . $value->account_no . "</td>
                     <td>" . ($data) . "</td>
                      <td>" . $entry_date . "</td>
                </tr>";
                echo "</table>";
            }
        }
    }
    //  Update parent_auto id column in account_head
    public function update_parent_auto_id()
    {
        $records = AccountHeads::get();
        foreach ($records as $key => $value) {
            $data = AccountHeads::where('head_id', $value->head_id)->first();
            $parenthead_detail = AccountHeads::where('head_id', $data->parent_id)->first();
            if ($parenthead_detail) {
                $updated_record = $data->update(['parentId_auto_id' => $parenthead_detail->id]);
            }
        }
        dd('done');
    }
    // Update maturity amount in tables
    public function update_maturity_amount()
    {
        $branch = 1;
        $demand_adviceRecord = DemandAdvice::where('payment_type', 4)->where('status', 1)->where('branch_id', $branch)->where('id', '138')->get();
        foreach ($demand_adviceRecord as $key => $value) {
            $check_opening_balance = Daybook::where('transaction_type', 17)->where('investment_id', $value->investment_id)->where('branch_id', $branch)->first();
            if ($check_opening_balance) {
                if ($check_opening_balance->opening_balance < 0) {
                    $investment_amount = Daybook::whereIn('transaction_type', [2, 4])->where('investment_id', $value->investment_id)->where('branch_id', $branch)->sum('deposit');
                    $payable_amount = $value->maturity_amount_payable;
                    // Compare Amount Start
                    if ($payable_amount > $investment_amount) {
                        $interest = $payable_amount - $investment_amount;
                    } elseif ($payable_amount < $investment_amount) {
                        $interest = 0;
                    } elseif ($payable_amount == $investment_amount) {
                        $interest = 0;
                    }
                    // Compare Amount End
                    $member_investments_data = Memberinvestments::where('id', $value->investment_id)->first();
                    // Update Member Investment Maturity Amount Start
                    if ($member_investments_data) {
                        $update_member_investment_data = $member_investments_data->update(['maturity_payable_interest' => $interest]);
                    }
                    // Update Member Investment Maturity Amount End
                    $daybook_bonus_record = Daybook::where('transaction_type', 16)->where('investment_id', $value->investment_id)->first();
                    if ($daybook_bonus_record) {
                        $update_daybook_bonus_record = $daybook_bonus_record->update(['deposit' => $interest]);
                    }
                    // Update Bonus Amount in Daybook Start
                    $daybook_redemption_record = Daybook::where('transaction_type', 17)->where('investment_id', $value->investment_id)->first();
                    // Update Bonus Amount in Daybook End
                    // Update Redemption  Amount in Daybook Start
                    if ($daybook_redemption_record) {
                        $update_redemption_record = $daybook_redemption_record->update(['withdrawal' => $payable_amount]);
                    }
                    // Update Redemption  Amount in Daybook End
                    // Get Head Amount Start
                    $get_head36_amount = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head3', 36)->first();
                    $get_head55_amount = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head4', 55)->first();
                    $get_head56_amount = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head4', 56)->first();
                    $get_head57_amount = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head4', 57)->first();
                    $get_head58_amount = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head4', 58)->first();
                    $get_head59_amount = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head4', 59)->first();
                    $get_head68_amount = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head4', 68)->first();
                    $get_head69_amount = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head4', 69)->first();
                    $get_head70_amount = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head4', 70)->first();
                    $get_head91_amount = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head4', 91)->first();
                    // Get Head Amount End
                    $get_branch_daybook_amount_head20 = '';
                    $get_branch_daybook_amount_head57 = '';
                    $get_branch_daybook_amount_head58 = '';
                    $get_branch_daybook_amount_head59 = '';
                    $get_branch_daybook_amount_head56 = '';
                    $get_branch_daybook_amount_head36 = '';
                    if ($get_head36_amount) {
                        $get_branch_daybook_amount_head36 = BranchDaybook::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('amount', $get_head36_amount->amount)->first();
                    }
                    if ($get_head55_amount) {
                        $get_branch_daybook_amount_head20 = BranchDaybook::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('amount', $get_head55_amount->amount)->first();
                    }
                    if ($get_head56_amount) {
                        $get_branch_daybook_amount_head56 = BranchDaybook::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('amount', $get_head56_amount->amount)->first();
                    }
                    if ($get_head57_amount) {
                        $get_branch_daybook_amount_head57 = BranchDaybook::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('amount', $get_head57_amount->amount)->first();
                    }
                    if ($get_head58_amount) {
                        $get_branch_daybook_amount_head58 = BranchDaybook::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('amount', $get_head58_amount->amount)->first();
                    }
                    if ($get_head59_amount) {
                        $get_branch_daybook_amount_head59 = BranchDaybook::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('amount', $get_head59_amount->amount)->first();
                    }
                    // Update Branch Daybook Start
                    if ($get_branch_daybook_amount_head36) {
                        $update_branch_daybook_head36_amount = $get_branch_daybook_amount_head36->update(['opening_balance' => $interest, 'amount' => $interest, 'closing_balance' => $interest, 'description_cr' => 'To Cash A/C Cr ' . $interest, 'description_dr' => 'Maturity Amount A/C Dr ' . $interest]);
                    }
                    if ($get_branch_daybook_amount_head20) {
                        $update_branch_daybook_head20_amount = $get_branch_daybook_amount_head20->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount, 'description_cr' => 'To Cash A/C Cr ' . $investment_amount, 'description_dr' => 'Maturity Amount A/C Dr ' . $investment_amount]);
                    }
                    if ($get_branch_daybook_amount_head56) {
                        $update_branch_daybook_head56_amount = $get_branch_daybook_amount_head56->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount, 'description_cr' => 'To Cash A/C Cr ' . $investment_amount, 'description_dr' => 'Maturity Amount A/C Dr ' . $investment_amount]);
                    }
                    if ($get_branch_daybook_amount_head57) {
                        $update_branch_daybook_head57_amount = $get_branch_daybook_amount_head57->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount, 'description_cr' => 'To Cash A/C Cr ' . $investment_amount, 'description_dr' => 'Maturity Amount A/C Dr ' . $investment_amount]);
                    }
                    if ($get_branch_daybook_amount_head58) {
                        $update_branch_daybook_head58_amount = $get_branch_daybook_amount_head58->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount, 'description_cr' => 'To Cash A/C Cr ' . $investment_amount, 'description_dr' => 'Maturity Amount A/C Dr ' . $investment_amount]);
                    }
                    if ($get_branch_daybook_amount_head59) {
                        $update_branch_daybook_head59_amount = $get_branch_daybook_amount_head59->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount, 'description_cr' => 'To Cash A/C Cr ' . $investment_amount, 'description_dr' => 'Maturity Amount A/C Dr ' . $investment_amount]);
                    }
                    // Update Branch Daybook End
                    // Update All Transaction start
                    if ($payable_amount > $investment_amount) {
                        if ($get_head55_amount) {
                            // dd($get_branch_daybook_amount_head20);
                            $update_all_transaction_head20 = $get_head55_amount->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount]);
                        }
                        if ($get_head56_amount) {
                            // dd($get_branch_daybook_amount_head20);
                            $update_all_transaction_head56 = $get_head56_amount->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount]);
                        }
                        if ($get_head57_amount) {
                            // dd($get_branch_daybook_amount_head20);
                            $update_all_transaction_head57 = $get_head57_amount->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount]);
                        }
                        if ($get_head58_amount) {
                            // dd($get_branch_daybook_amount_head20);
                            $update_all_transaction_head58 = $get_head58_amount->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount]);
                        }
                        if ($get_head59_amount) {
                            // dd($get_branch_daybook_amount_head20);
                            $update_all_transaction_head59 = $get_head59_amount->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount]);
                        }
                        if ($get_head68_amount) {
                            // dd($get_branch_daybook_amount_head20);
                            $update_all_transaction_head68 = $get_head68_amount->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount]);
                        }
                        if ($get_head69_amount) {
                            // dd($get_branch_daybook_amount_head20);
                            $update_all_transaction_head69 = $get_head69_amount->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount]);
                        }
                        if ($get_head70_amount) {
                            // dd($get_branch_daybook_amount_head20);
                            $update_all_transaction_head70 = $get_head70_amount->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount]);
                        }
                        if ($get_head91_amount) {
                            // dd($get_branch_daybook_amount_head20);
                            $update_all_transaction_head59 = $get_head91_amount->update(['opening_balance' => $investment_amount, 'amount' => $investment_amount, 'closing_balance' => $investment_amount]);
                        }
                        if ($get_head36_amount) {
                            $update_all_transaction_head36 = $get_head36_amount->update(['opening_balance' => $interest, 'amount' => $interest, 'closing_balance' => $interest]);
                        }
                        $update_all_transaction_head28 = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head3', 28)->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                    } elseif ($payable_amount < $investment_amount) {
                        if ($get_head55_amount) {
                            $update_all_transaction_head20 = $get_head55_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head56_amount) {
                            $update_all_transaction_head56 = $get_head56_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head57_amount) {
                            $update_all_transaction_head57 = $get_head57_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head58_amount) {
                            $update_all_transaction_head58 = $get_head58_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head59_amount) {
                            $update_all_transaction_head59 = $get_head59_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head68_amount) {
                            $update_all_transaction_head68 = $get_head68_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head69_amount) {
                            $update_all_transaction_head69 = $get_head69_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head70_amount) {
                            $update_all_transaction_head70 = $get_head70_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head91_amount) {
                            $update_all_transaction_head91 = $get_head91_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head36_amount) {
                            $update_all_transaction_head36 = $get_head36_amount->update(['opening_balance' => $interest, 'amount' => $interest, 'closing_balance' => $interest]);
                        }
                        $update_all_transaction_head28 = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head3', 28)->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                    } elseif ($payable_amount == $investment_amount) {
                        if ($get_head55_amount) {
                            $update_all_transaction_head20 = $get_head55_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head56_amount) {
                            $update_all_transaction_head56 = $get_head56_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head57_amount) {
                            $update_all_transaction_head57 = $get_head57_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head58_amount) {
                            $update_all_transaction_head58 = $get_head58_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head59_amount) {
                            $update_all_transaction_head59 = $get_head59_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head68_amount) {
                            $update_all_transaction_head68 = $get_head68_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head69_amount) {
                            $update_all_transaction_head69 = $get_head69_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head70_amount) {
                            $update_all_transaction_head70 = $get_head70_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head91_amount) {
                            $update_all_transaction_head59 = $get_head91_amount->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                        }
                        if ($get_head36_amount) {
                            $update_all_transaction_head36 = $get_branch_daybook_amount_head36->update(['opening_balance' => $interest, 'amount' => $interest, 'closing_balance' => $interest]);
                        }
                        $update_all_transaction_head28 = AllHeadTransaction::where('type', 13)->where('sub_type', 133)->where('type_id', $value->id)->where('head3', 28)->update(['opening_balance' => $payable_amount, 'amount' => $payable_amount, 'closing_balance' => $payable_amount]);
                    }
                    // Update All Transaction End
                    if ($daybook_redemption_record) {
                        echo "<table border='1'>";
                        echo "<tr>
                            <th>Account Number</th>
                            </tr>";
                        echo "<tr>
                             <td>" . $daybook_redemption_record->account_no . "</td>
                        </tr>";
                        echo "</table>";
                    }
                    print('success');
                }
            }
        }
        dd('success');
    }
    public function update_cash_in_hand()
    {
        $startdate = new Carbon('2019-10-01');
        $enddate = new Carbon('2021-09-14');
        $branch = 1;
        for ($i = $startdate; $i <= $enddate; $i->modify('+1 day')) {
            $dataCR = \App\Models\BranchDaybook::where('branch_id', $branch)->where('payment_type', 'CR')->where('payment_mode', 0)->where('entry_date', $i->format('Y-m-d'))->sum('amount');
            $dataDR = \App\Models\BranchDaybook::where('branch_id', $branch)->where('payment_type', 'DR')->where('payment_mode', 0)->where('entry_date', $i->format('Y-m-d'))->sum('amount');
            $dataLoanCR = \App\Models\BranchDaybook::where('branch_id', $branch)->where('payment_type', 'CR')->where('payment_mode', 0)->where('entry_date', $i->format('Y-m-d'))->sum('amount');
            $dataLoanDR = \App\Models\BranchDaybook::where('branch_id', $branch)->where('payment_type', 'DR')->where('payment_mode', 0)->where('entry_date', $i->format('Y-m-d'))->sum('amount');
            $total = (float) $dataCR - (float) $dataDR;
            $Loantotal = (float) $dataLoanCR - (float) $dataLoanDR;
            $previousRecord = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', '<', $i->format('Y-m-d'))->orderBy('entry_date', 'desc')->first();
            $currentRecord = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', $i->format('Y-m-d'))->orderBy('entry_date', 'desc')->first();
            if ($currentRecord) {
                if ($previousRecord) {
                    $RecordUpdate = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => $previousRecord->closing_balance, 'balance' => $previousRecord->balance + $total, 'closing_balance' => $previousRecord->balance + $total]);
                } else {
                    $RecordUpdate = \App\Models\BranchCash::where('branch_id', $branch)->where('entry_date', $i->format('Y-m-d'))->update(['opening_balance' => 0, 'balance' => $total, 'closing_balance' => $total]);
                }
            } elseif ($total > 0 || $total < 0) {
                $data['branch_id'] = $branch;
                if ($previousRecord) {
                    $data['opening_balance'] = $previousRecord->closing_balance;
                    $data['balance'] = $previousRecord->balance + $total;
                    $data['closing_balance'] = $previousRecord->balance + $total;
                } else {
                    $data['opening_balance'] = 0;
                    $data['balance'] = $total;
                    $data['closing_balance'] = $total;
                }
                $data['entry_date'] = $i->format('Y-m-d');
                $data['entry_time'] = 1;
                $data['created_at'] = $i->format('Y-m-d');
                $data['updated_at'] = $i->format('Y-m-d');
                $createdRow = \App\Models\BranchCash::create($data);
            }
            if ($previousRecord) {
                echo "<table border='1'>";
                echo "<tr>
                    <th>date</th>
                    <th>opening_balance</th>
                     <th>closing_balance</th>
                     <th>balance(Previoud Balance + total)</th>
                     <th>CR balance</th>
                     <th>DR balance</th>
                       <th>Total</th>
                    </tr>";
                echo "<tr><td>" . $i->format('Y-m-d') . "</td>
                     <td>" . $previousRecord->closing_balance . "</td>
                     <td>" . ($previousRecord->balance + $total) . "</td>
                     <td>" . ($previousRecord->balance + $total) . "</td>
                      <td>" . $dataCR . "</td>
                       <td>" . $dataDR . "</td>
                        <td>" . $total . "</td>
                </tr>";
                echo "</table>";
            }
        }
        '<br/>';
    }
    public function add_file_charge_type()
    {
        $memberLoans = Memberloans::whereNotNull('approve_date')->get();
        $membergroupLoan = Grouploans::whereNotNull('approve_date')->get();
        foreach ($memberLoans as $key => $member_loan) {
            if ($member_loan->amount == $member_loan->deposite_amount) {
                $flag = '0';
                $amount = $member_loan->amount;
            } else {
                $flag = '1';
                $amount = $member_loan->amount - $member_loan->file_charges - $member_loan->insurance_charge - $member_loan->insurance_cgst - $member_loan->insurance_sgst - $member_loan->insurance_charge_igst - $member_loan->filecharge_igst - $member_loan->filecharge_sgst - $member_loan->filecharge_cgst;
            }
            $updatedata = $member_loan->update(['file_charge_type' => $flag, 'transfer_amount' => $amount]);
        }
        foreach ($membergroupLoan as $key => $member_loan_group) {
            if ($member_loan_group->amount == $member_loan_group->deposite_amount) {
                $flag = '0';
                $amount = $member_loan_group->amount;
            } else {
                $flag = '1';
                $amount = $member_loan_group->amount - $member_loan_group->file_charges - $member_loan_group->insurance_charge - $member_loan_group->insurance_cgst - $member_loan_group->insurance_sgst - $member_loan_group->insurance_charge_igst - $member_loan_group->filecharge_igst - $member_loan_group->filecharge_sgst - $member_loan_group->filecharge_cgst;
            }
            $updatedata = $member_loan_group->update(['file_charge_type' => $flag, 'transfer_amount' => $amount]);
        }
        dd('success');
    }
    public function insert_transaction_type()
    {
        $data = [
            ['type' => 5, 'sub_type' => 51, 'title' => 'Loan  '],
            ['type' => 5, 'sub_type' => 52, 'title' => 'Loan - Emi '],
            ['type' => 5, 'sub_type' => 53, 'title' => 'Loan - Penalty'],
            ['type' => 5, 'sub_type' => 54, 'title' => 'Loan - Group Loan'],
            ['type' => 5, 'sub_type' => 55, 'title' => 'Loan - Group Loan Emi'],
            ['type' => 5, 'sub_type' => 56, 'title' => 'Loan - Group Loan Panelty'],
            ['type' => 5, 'sub_type' => 57, 'title' => 'Loan -  File Charge'],
            ['type' => 5, 'sub_type' => 58, 'title' => 'Loan - Group Loan File Charge'],
            ['type' => 5, 'sub_type' => 511, 'title' => 'Loan - JV Loan'],
            ['type' => 5, 'sub_type' => 512, 'title' => 'Loan - JV  Group Loan'],
            ['type' => 5, 'sub_type' => 513, 'title' => 'Loan - JV Loan Panelty'],
            ['type' => 5, 'sub_type' => 514, 'title' => 'Loan - JV Group Loan Panelty'],
            ['type' => 5, 'sub_type' => 515, 'title' => 'Loan - JV Loan Emi'],
            ['type' => 5, 'sub_type' => 516, 'title' => 'Loan - JV Group Loan Emi'],
            ['type' => 6, 'sub_type' => 61, 'title' => 'Employee - Salary'],
            ['type' => 6, 'sub_type' => 62, 'title' => 'Employee -JV Salary'],
            ['type' => 7, 'sub_type' => 70, 'title' => 'Transferred Branch To Bank - Branch Cash'],
            ['type' => 7, 'sub_type' => 71, 'title' => 'Transferred Branch To Bank - Branch Cheque'],
            ['type' => 7, 'sub_type' => 72, 'title' => 'Transferred Branch To Bank - Branch Online'],
            ['type' => 7, 'sub_type' => 73, 'title' => 'Transferred Branch To Bank - Branch SSB/JV'],
            ['type' => 8, 'sub_type' => 80, 'title' => 'Transferred Bank To Bank - Bank Cash'],
            ['type' => 8, 'sub_type' => 81, 'title' => 'Transferred Bank To Bank - Bank Cheque'],
            ['type' => 8, 'sub_type' => 82, 'title' => 'Transferred Bank To Bank - Bank Online'],
            ['type' => 8, 'sub_type' => 83, 'title' => 'Transferred Bank To Bank - Bank SSB/JV'],
            ['type' => 9, 'sub_type' => 90, 'title' => 'Tds - Commission'],
            ['type' => 10, 'sub_type' => 101, 'title' => 'Rent - Ledger'],
            ['type' => 10, 'sub_type' => 102, 'title' => 'Rent - Payment'],
            ['type' => 10, 'sub_type' => 103, 'title' => 'Rent - Security'],
            ['type' => 10, 'sub_type' => 104, 'title' => 'Rent - Advance'],
            ['type' => 10, 'sub_type' => 105, 'title' => 'JV - Ledger'],
            ['type' => 10, 'sub_type' => 106, 'title' => 'JV - Ledger'],
            ['type' => 12, 'sub_type' => 121, 'title' => 'Salary - Ledger'],
            ['type' => 12, 'sub_type' => 122, 'title' => 'Salary - Transfer'],
            ['type' => 12, 'sub_type' => 123, 'title' => 'Salary - Advance'],
            ['type' => 13, 'sub_type' => 131, 'title' => 'Demand Advice - Fresh Expense'],
            ['type' => 13, 'sub_type' => 132, 'title' => 'Demand Advice - Ta Advance'],
            ['type' => 13, 'sub_type' => 133, 'title' => 'Maturity'],
            ['type' => 13, 'sub_type' => 134, 'title' => 'Prematurity'],
            ['type' => 13, 'sub_type' => 135, 'title' => 'Death Help'],
            ['type' => 13, 'sub_type' => 136, 'title' => 'Death Claim'],
            ['type' => 13, 'sub_type' => 137, 'title' => 'EM'],
            ['type' => 13, 'sub_type' => 138, 'title' => 'Demand Advice - JV Ta Advance'],
            ['type' => 14, 'sub_type' => 141, 'title' => 'Voucher - Director'],
            ['type' => 14, 'sub_type' => 142, 'title' => 'Voucher - ShareHolder'],
            ['type' => 14, 'sub_type' => 143, 'title' => 'Voucher - Penal Interest'],
            ['type' => 14, 'sub_type' => 144, 'title' => 'Voucher - Bank'],
            ['type' => 14, 'sub_type' => 145, 'title' => 'Voucher - Eli Loan'],
            ['type' => 15, 'sub_type' => 151, 'title' => 'Director - Deposit'],
            ['type' => 15, 'sub_type' => 152, 'title' => 'Director - Withdraw'],
            ['type' => 15, 'sub_type' => 153, 'title' => 'Director - JV Deposit'],
            ['type' => 16, 'sub_type' => 161, 'title' => 'ShareHolder - Deposit'],
            ['type' => 16, 'sub_type' => 162, 'title' => 'ShareHolder - Transfer'],
            ['type' => 16, 'sub_type' => 163, 'title' => 'ShareHolder - JV Deposit'],
            ['type' => 17, 'sub_type' => 171, 'title' => 'Loan From Bank - Create Loan'],
            ['type' => 17, 'sub_type' => 172, 'title' => 'Loan From Bank - Emi Payment'],
            ['type' => 17, 'sub_type' => 173, 'title' => 'Loan From Bank - JV Entry'],
            ['type' => 18, 'sub_type' => 181, 'title' => 'Bank Charge  - Create'],
            ['type' => 19, 'sub_type' => 191, 'title' => 'Assets - Assets'],
            ['type' => 19, 'sub_type' => 192, 'title' => 'Assets - Depreciation'],
            ['type' => 20, 'sub_type' => 201, 'title' => 'Expense Booking  - Create Expense'],
            ['type' => 22, 'sub_type' => 222, 'title' => 'JV To Bank'],
            ['type' => 23, 'sub_type' => 232, 'title' => 'JV To Branch'],
        ];
        $insert = TransactionType::insert($data);
    }
    // Update reinvest Date
    public function update_reinvest_date()
    {
        $daybook_record = Daybook::where('account_no', 'like', 'R-%')->get();
        foreach ($daybook_record as $key => $value) {
            $getBranchdaybookRecord = BranchDaybook::where('type_id', $value->investment_id)->where('type_transaction_id', $value->id)->first();
            if ($getBranchdaybookRecord) {
                $date = $value->created_at->format('Y-m-d');
                $getBranchdaybookRecord->update(['entry_date' => $date, 'created_at' => $value->created_at]);
            }
            print('success');
        }
    }
    public function get_ssb_ac_more_than_one_transaction()
    {
        $record = SavingAccount::get();
        foreach ($record as $key => $value) {
            $data = SavingAccountTranscation::where('account_no', $value->account_no)->where('description', 'not like', 'SSB Account Opening')->count();
            if ($data > 1) {
                echo "<table border='1'>";
                echo "<tr>
                    <th>Account Number</th>
                    </tr>";
                echo "<tr>
                     <td>" . $value->account_no . "</td>
                </tr>";
                echo "</table>";
            }
        }
    }
    public function get_record_not_in_all_transaction()
    {
        $data = BranchDaybook::where('branch_id', 3)->where('type', 3)->where('payment_type', 'CR')->where('payment_mode', 0)->get();
        foreach ($data as $value) {
            $rec = \App\Models\AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->where('branch_id', 3)->count();
        }
        if ($rec == 0) {
            echo "<table border='1'>";
            echo "<tr>
                    <th>Account Number</th>
                    </tr>";
            echo "<tr>
                     <td>" . $value->daybook_ref_id . "</td>
                </tr>";
            echo "</table>";
        }
    }
    public function insert_loan_emi_record()
    {
        $data = BranchDaybook::where('branch_id', 13)->where('type', 5)->where('sub_type', 55)->where('payment_type', 'CR')->where('payment_mode', 0)->get();
        // dd($data);
        foreach ($data as $value) {
            $daybook = Daybook::where('id', $value->type_transaction_id)->count();
            $loan = \App\Models\LoanDayBooks::where('group_loan_id', $value->type_id)->orwhere('loan_id', $value->type_id)->where('deposit', $value->amount)->count();
            if ($daybook == 1 && $loan == 1) {
                $record['daybook_ref_id'] = $value->daybook_ref_id;
                $record['branch_id'] = $value->branch_id;
                $record['type'] = 5;
                $record['sub_type'] = 55;
                $record['head_id'] = 28;
                $record['type_id'] = $value->type_id;
                $record['type_transaction_id'] = $value->type_transaction_id;
                ;
                $record['associate_id'] = $value->associate_id;
                $record['member_id'] = $value->member_id;
                $record['branch_id_to'] = $value->branch_id_to;
                $record['branch_id_from'] = $value->branch_id_from;
                $record['opening_balance'] = $value->amount;
                $record['amount'] = $value->amount;
                $record['closing_balance'] = $value->amount;
                $record['description'] = $value->description;
                $record['payment_type'] = 'DR';
                $record['payment_mode'] = $value->payment_mode;
                $record['currency_code'] = $value->currency_code;
                $record['amount_to_id'] = $value->amount_to_id;
                $record['amount_to_name'] = $value->amount_to_name;
                $record['amount_from_id'] = $value->amount_from_id;
                ;
                $record['amount_from_name'] = $value->amount_from_name;
                ;
                $record['v_no'] = $value->v_no;
                $record['v_date'] = $value->v_date;
                ;
                $record['ssb_account_id_from'] = $value->ssb_account_id_from;
                $record['ssb_account_id_to'] = $value->ssb_account_id_to;
                $record['cheque_no'] = null;
                $record['cheque_date'] = null;
                $record['cheque_bank_from'] = null;
                $record['cheque_bank_ac_from'] = null;
                $record['cheque_bank_ifsc_from'] = null;
                $record['cheque_bank_branch_from'] = null;
                $record['cheque_bank_from_id'] = null;
                $record['cheque_bank_ac_from_id'] = null;
                $record['cheque_bank_to'] = null;
                $record['cheque_bank_ac_to'] = null;
                $record['cheque_bank_to_name'] = null;
                $record['cheque_bank_to_branch'] = null;
                $record['cheque_bank_to_ac_no'] = null;
                $record['cheque_bank_to_ifsc'] = null;
                $record['transction_no'] = null;
                $record['transction_bank_from'] = null;
                $record['transction_bank_ac_from'] = null;
                $record['transction_bank_ifsc_from'] = null;
                $record['transction_bank_branch_from'] = null;
                $record['transction_bank_from_id'] = null;
                $record['transction_bank_from_ac_id'] = null;
                $record['transction_bank_to'] = null;
                $record['transction_bank_ac_to'] = null;
                $record['transction_bank_to_name'] = null;
                $record['transction_bank_to_ac_no'] = null;
                $record['transction_bank_to_branch'] = null;
                $record['transction_bank_to_ifsc'] = null;
                $record['transction_date'] = $value->transaction_date;
                $record['entry_date'] = $value->entry_date;
                ;
                $record['entry_time'] = $value->entry_time;
                $record['created_by'] = $value->created_by;
                $record['created_by_id'] = $value->created_by_id;
                $record['created_at'] = $value->created_at;
                $record['updated_at'] = $value->updated_at;
                $record['ssb_account_tran_id_to'] = null;
                $record['ssb_account_tran_id_from'] = null;
                $insertData = AllHeadTransaction::create($record);
                print_r("success");
            }
        }
    }
    public function deleteLoanEmi()
    {
        $daybook_record = AllHeadTransaction::where('branch_id', 1)->select('daybook_ref_id')->where('type', 5)->where('sub_type', 52)->where('payment_mode', 0) /*->offset(20)->limit(1500)*/->whereBetween('entry_date', ['2020-06-01', '2021-03-31'])->get();
        foreach ($daybook_record as $key => $value) {
            $getHadRecord = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->count();
            if ($getHadRecord == 0) {
                $d = AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 1]);
                if ($d) {
                    //$succ = $d->update(['is_deleted' => 1]);
                    print('success' . $value->daybook_ref_id . $getHadRecord);
                }
            }
        }
    }
    public function insertMI_record()
    {
        $allTransaction = AllHeadTransaction::where('type', 1)->whereIn('sub_type', [11, 12])->where('branch_id', 1)->get();
        foreach ($allTransaction as $key => $getRecord) {
            $ifexists = BranchDaybook::where('daybook_ref_id', $getRecord->daybook_ref_id)->count();
            if ($ifexists == 0) {
                $record['daybook_ref_id'] = $getRecord->daybook_ref_id;
                $record['branch_id'] = $getRecord->branch_id;
                $record['type'] = $getRecord->type;
                $record['sub_type'] = $getRecord->sub_type;
                $record['type_id'] = $getRecord->type_id;
                $record['type_transaction_id'] = $getRecord->type_transaction_id;
                ;
                $record['associate_id'] = $getRecord->associate_id;
                $record['member_id'] = $getRecord->member_id;
                $record['branch_id_to'] = null;
                $record['branch_id_from'] = null;
                $record['opening_balance'] = $getRecord->opening_balance;
                $record['amount'] = $getRecord->amount;
                $record['closing_balance'] = $getRecord->closing_balance;
                $record['description'] = ($getRecord->description);
                $record['description_dr'] = 'Cash A/c Dr 10/-' . number_format((float) $getRecord->amount, 2, '.', '');
                $record['description_cr'] = getMemberData($getRecord->member_id)->first_name . ' ' . getMemberData($getRecord->member_id)->last_name . (getMemberData($getRecord->member_id)->member_id) . 'A/c Cr' . $getRecord->amount;
                $record['payment_type'] = $getRecord->payment_type;
                $record['payment_mode'] = $getRecord->payment_mode;
                $record['currency_code'] = $getRecord->currency_code;
                $record['amount_to_id'] = $getRecord->amount_to_id;
                $record['amount_to_name'] = $getRecord->amount_to_name;
                $record['amount_from_id'] = null;
                $record['amount_from_name'] = null;
                $record['v_no'] = $getRecord->v_no;
                $record['v_date'] = $getRecord->v_date;
                ;
                $record['ssb_account_id_from'] = null;
                $record['ssb_account_id_to'] = null;
                $record['cheque_no'] = null;
                $record['cheque_date'] = null;
                $record['cheque_bank_from'] = null;
                $record['cheque_bank_ac_from'] = null;
                $record['cheque_bank_ifsc_from'] = null;
                $record['cheque_bank_branch_from'] = null;
                $record['cheque_bank_from_id'] = null;
                $record['cheque_bank_ac_from_id'] = null;
                $record['cheque_bank_to'] = null;
                $record['cheque_bank_ac_to'] = null;
                $record['cheque_bank_to_name'] = null;
                $record['cheque_bank_to_branch'] = null;
                $record['cheque_bank_to_ac_no'] = null;
                $record['cheque_bank_to_ifsc'] = null;
                $record['transction_no'] = null;
                $record['transction_bank_from'] = null;
                $record['transction_bank_ac_from'] = null;
                $record['transction_bank_ifsc_from'] = null;
                $record['transction_bank_branch_from'] = null;
                $record['transction_bank_from_id'] = null;
                $record['transction_bank_from_ac_id'] = null;
                $record['transction_bank_to'] = null;
                $record['transction_bank_ac_to'] = null;
                $record['transction_bank_to_name'] = null;
                $record['transction_bank_to_ac_no'] = null;
                $record['transction_bank_to_branch'] = null;
                $record['transction_bank_to_ifsc'] = null;
                $record['transction_date'] = null;
                $record['entry_date'] = $getRecord->entry_date;
                ;
                $record['entry_time'] = $getRecord->entry_time;
                $record['created_by'] = $getRecord->created_by;
                $record['created_by_id'] = $getRecord->created_by_id;
                $record['is_contra'] = 0;
                $record['contra_id'] = NULL;
                $record['created_at'] = $getRecord->created_at;
                $record['updated_at'] = $getRecord->updated_at;
                $record['ssb_account_tran_id_to'] = null;
                $record['ssb_account_tran_id_from'] = null;
                $record['amount_type'] = 0;
                $insertData = BranchDaybook::create($record);
                print_r("success");
            }
        }
    }
    public function get_emergancy_maturity_account()
    {
        $record = DemandAdvice::where('payment_type', 4)->where('status', 1)->where('id', '>', 0)->where('id', '<=', 850)->get();
        foreach ($record as $key => $value) {
            $totalAmount = Daybook::whereIn('transaction_type', [2, 4])->where('investment_id', $value->investment_id)->sum('deposit');
            $ac = Daybook::where('investment_id', $value->investment_id)->first();
            $headAmount = AllHeadTransaction::whereIn('head_id', [57, 58, 59, 80, 81, 82, 83, 84, 85, 77, 78, 79])->where('type', 13)->where('type_id', $value->id)->where('type_transaction_id', $value->id)->first();
            if (isset($headAmount->amount)) {
                if ($headAmount->amount != $totalAmount) {
                    $updateHeadamount = $headAmount->update(['opening_balance' => $totalAmount, 'amount' => $totalAmount, 'closing_balance' => $totalAmount]);
                    $headData = AllHeadTransaction::where('head_id', 36)->where('type', 13)->where('type_id', $value->id)->where('type_transaction_id', $value->id)->first();
                    $interest = $value->maturity_amount_payable - $totalAmount;
                    if ($headData) {
                        if ($interest > 0) {
                            $updateHeadamount2 = $headData->update(['opening_balance' => $interest, 'amount' => $interest, 'closing_balance' => $interest]);
                        } else {
                            $updateHeadamount2 = $headData->update(['opening_balance' => 0, 'amount' => 0, 'closing_balance' => 0]);
                        }
                    }
                    $daybookRecord = Daybook::where('transaction_type', 16)->where('investment_id', $value->investment_id)->first();
                    $daybookRecord2 = Daybook::where('transaction_type', 17)->where('investment_id', $value->investment_id)->first();
                    if ($daybookRecord) {
                        if ($interest > 0) {
                            $updateDaybookTransaction = $daybookRecord->update(['deposit' => $interest]);
                        } else {
                            $updateDaybookTransaction = $daybookRecord->update(['deposit' => 0]);
                        }
                    }
                    if ($daybookRecord2) {
                        $updateDaybookTransaction2 = $daybookRecord2->update(['withdrawal' => $value->maturity_amount_payable]);
                    }
                }
                print_r('success' . $ac->account_no);
            }
        }
    }
    public function insert_saving_withrawal_record()
    {
        $branch_id = 5;
        $startdate = '2020-06-01';
        $endDate = '2021-03-31';
        $record = SavingAccountTranscation::with('savingAc')->where('type', 5)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startdate, $endDate])->get();
        foreach ($record as $key => $value) {
            $day_book_id = $value->daybook_ref_id;
            $des = 'Cash Withdrawal';
            $date = date("Y-m-d", strtotime($value->created_at));
            $daybookRecord = Daybook::where('account_no', $value->account_no)->where('transaction_type', 1)->where('withdrawal', $value->withdrawal)->where('description', 'like', '%' . $des . '%')->where(\DB::raw('DATE(created_at)'), $date)->first();
            $data = AllHeadTransaction::where('branch_id', $value->branch_id)->where('type', 4)->where('sub_type', 43)->where('head_id', 56)->where('type_id', $value->saving_account_id)->where('type_transaction_id', $value->id)->where('amount', $value->withdrawal)->count();
            $branchDetail = getBranchDetail($value->branch_id);
            $withdrawal = $value->withdrawal;
            $t = date("H:i:s");
            $globaldate = Session::get('created_at');
            $refData['amount'] = $withdrawal;
            $refData['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
            $refData['entry_time'] = date("H:i:s");
            $refData['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
            $transcation = \App\Models\BranchDaybookReference::create($refData);
            $daybook_ref_id = $transcation->id;
            $record = array();
            if ($data == 0) {
                $record['daybook_ref_id'] = $daybook_ref_id;
                $record['branch_id'] = $value->branch_id;
                $record['type'] = 4;
                $record['sub_type'] = 43;
                $record['head_id'] = 56;
                $record['type_id'] = $value->saving_account_id;
                $record['type_transaction_id'] = $value->id;
                ;
                $record['member_id'] = $value['savingAc']->member_id;
                $record['branch_id_to'] = $value->branch_id_to;
                $record['branch_id_from'] = $value->branch_id_from;
                $record['opening_balance'] = $value->withdrawal;
                $record['amount'] = $value->withdrawal;
                $record['closing_balance'] = $value->withdrawal;
                $record['description'] = 'SSB A/c (' . $value->account_no . ') withdrawal payment through cash ' . $branchDetail->name . '(' . $branchDetail->branch_code . ')';
                $record['payment_type'] = $value->payment_type;
                $record['payment_mode'] = $value->payment_mode;
                $record['currency_code'] = $value->currency_code;
                $record['transction_date'] = date("Y-m-d", strtotime($value->created_at));
                $record['entry_date'] = date("Y-m-d", strtotime($value->created_at));
                $record['created_by'] = $daybookRecord->created_by;
                $record['created_by_id'] = $daybookRecord->created_by_id;
                $record['created_at'] = $value->created_at;
                $record['updated_at'] = $value->updated_at;
                $record['ssb_account_tran_id_to'] = null;
                $record['ssb_account_tran_id_from'] = null;
                $insertData = AllHeadTransaction::create($record);
                print_r("success");
            }
            $ifexists = BranchDaybook::where('branch_id', $value->branch_id)->where('type', 4)->where('sub_type', 43)->where('type_id', $value->saving_account_id)->where('type_transaction_id', $value->id)->where('amount', $value->withdrawal)->count();
            if ($ifexists == 0) {
                $branchSavingRecord['daybook_ref_id'] = $daybook_ref_id;
                $branchSavingRecord['branch_id'] = $value->branch_id;
                $branchSavingRecord['type'] = 4;
                $branchSavingRecord['sub_type'] = 43;
                $branchSavingRecord['type_id'] = $value->saving_account_id;
                ;
                $branchSavingRecord['type_transaction_id'] = $value->id;
                $branchSavingRecord['member_id'] = $value['savingAc']->member_id;
                $branchSavingRecord['opening_balance'] = $value->withdrawal;
                $branchSavingRecord['amount'] = $value->withdrawal;
                $branchSavingRecord['closing_balance'] = $value->withdrawal;
                $branchSavingRecord['description'] = 'SSB A/c (' . $value->account_no . ') withdrawal payment through cash ' . $branchDetail->name . '(' . $branchDetail->branch_code . ')';
                $branchSavingRecord['description_dr'] = 'SSB(' . $value->account_no . ') A/c Dr ' . $value->withdrawal . '/-';
                $branchSavingRecord['description_cr'] = 'To Cash A/C Cr ' . $value->withdrawal;
                $branchSavingRecord['payment_type'] = $value->payment_type;
                $branchSavingRecord['payment_mode'] = $value->payment_mode;
                $branchSavingRecord['currency_code'] = $value->currency_code;
                $branchSavingRecord['transction_date'] = date("Y-m-d", strtotime($value->created_at));
                $branchSavingRecord['entry_date'] = date("Y-m-d", strtotime($value->created_at));
                $branchSavingRecord['created_by'] = $daybookRecord->created_by;
                $branchSavingRecord['created_by_id'] = $daybookRecord->created_by_id;
                $branchSavingRecord['created_at'] = $value->created_at;
                $branchSavingRecord['updated_at'] = $value->updated_at;
                $branchSavingRecord['is_contra'] = 0;
                $branchSavingRecord['contra_id'] = NULL;
                $branchSavingRecord['amount_type'] = 0;
                $recordInsert = BranchDaybook::create($branchSavingRecord);
                print_r('branch_sucess');
            }
        }
    }
    public function insert_file_charge_in_cash_head()
    {
        $branchId = 4;
        $loanData = AllHeadTransaction::where('branch_id', $branchId)->where('type', 5)->whereIn('sub_type', [58])->get();
        foreach ($loanData as $value) {
            if ($value->sub_type == 57) {
                $checkFileChargeType = Memberloans::where('id', $value->type_id)->where('file_charge_type', 0)->first();
                if ($checkFileChargeType) {
                    $record['daybook_ref_id'] = $value->daybook_ref_id;
                    $record['branch_id'] = $value->branch_id;
                    $record['type'] = $value->type;
                    $record['sub_type'] = $value->sub_type;
                    $record['head_id'] = 28;
                    $record['type_id'] = $value->type_id;
                    $record['type_transaction_id'] = $value->type_transaction_id;
                    ;
                    $record['associate_id'] = $value->associate_id;
                    $record['member_id'] = $value->member_id;
                    $record['branch_id_to'] = $value->branch_id_to;
                    $record['branch_id_from'] = $value->branch_id_from;
                    $record['opening_balance'] = $value->amount;
                    $record['amount'] = $value->amount;
                    $record['closing_balance'] = $value->amount;
                    $record['description'] = $value->description;
                    $record['payment_type'] = 'DR';
                    $record['payment_mode'] = $value->payment_mode;
                    $record['currency_code'] = $value->currency_code;
                    $record['amount_to_id'] = $value->amount_to_id;
                    $record['amount_to_name'] = $value->amount_to_name;
                    $record['amount_from_id'] = $value->amount_from_id;
                    ;
                    $record['amount_from_name'] = $value->amount_from_name;
                    ;
                    $record['v_no'] = $value->v_no;
                    $record['v_date'] = $value->v_date;
                    ;
                    $record['ssb_account_id_from'] = $value->ssb_account_id_from;
                    $record['ssb_account_id_to'] = $value->ssb_account_id_to;
                    $record['cheque_no'] = null;
                    $record['cheque_date'] = null;
                    $record['cheque_bank_from'] = null;
                    $record['cheque_bank_ac_from'] = null;
                    $record['cheque_bank_ifsc_from'] = null;
                    $record['cheque_bank_branch_from'] = null;
                    $record['cheque_bank_from_id'] = null;
                    $record['cheque_bank_ac_from_id'] = null;
                    $record['cheque_bank_to'] = null;
                    $record['cheque_bank_ac_to'] = null;
                    $record['cheque_bank_to_name'] = null;
                    $record['cheque_bank_to_branch'] = null;
                    $record['cheque_bank_to_ac_no'] = null;
                    $record['cheque_bank_to_ifsc'] = null;
                    $record['transction_no'] = null;
                    $record['transction_bank_from'] = null;
                    $record['transction_bank_ac_from'] = null;
                    $record['transction_bank_ifsc_from'] = null;
                    $record['transction_bank_branch_from'] = null;
                    $record['transction_bank_from_id'] = null;
                    $record['transction_bank_from_ac_id'] = null;
                    $record['transction_bank_to'] = null;
                    $record['transction_bank_ac_to'] = null;
                    $record['transction_bank_to_name'] = null;
                    $record['transction_bank_to_ac_no'] = null;
                    $record['transction_bank_to_branch'] = null;
                    $record['transction_bank_to_ifsc'] = null;
                    $record['transction_date'] = $value->transaction_date;
                    $record['entry_date'] = $value->entry_date;
                    ;
                    $record['entry_time'] = $value->entry_time;
                    $record['created_by'] = $value->created_by;
                    $record['created_by_id'] = $value->created_by_id;
                    $record['created_at'] = $value->created_at;
                    $record['updated_at'] = $value->updated_at;
                    $record['ssb_account_tran_id_to'] = null;
                    $record['ssb_account_tran_id_from'] = null;
                    $insertData = AllHeadTransaction::create($record);
                    print_r("success");
                }
            } else {
                $checkFileChargeType = Grouploans::where('id', $value->type_id)->where('file_charge_type', 0)->first();
                if ($checkFileChargeType) {
                    $record['daybook_ref_id'] = $value->daybook_ref_id;
                    $record['branch_id'] = $value->branch_id;
                    $record['type'] = $value->type;
                    $record['sub_type'] = $value->sub_type;
                    $record['head_id'] = 28;
                    $record['type_id'] = $value->type_id;
                    $record['type_transaction_id'] = $value->type_transaction_id;
                    ;
                    $record['associate_id'] = $value->associate_id;
                    $record['member_id'] = $value->member_id;
                    $record['branch_id_to'] = $value->branch_id_to;
                    $record['branch_id_from'] = $value->branch_id_from;
                    $record['opening_balance'] = $value->amount;
                    $record['amount'] = $value->amount;
                    $record['closing_balance'] = $value->amount;
                    $record['description'] = $value->description;
                    $record['payment_type'] = 'DR';
                    $record['payment_mode'] = $value->payment_mode;
                    $record['currency_code'] = $value->currency_code;
                    $record['amount_to_id'] = $value->amount_to_id;
                    $record['amount_to_name'] = $value->amount_to_name;
                    $record['amount_from_id'] = $value->amount_from_id;
                    ;
                    $record['amount_from_name'] = $value->amount_from_name;
                    ;
                    $record['v_no'] = $value->v_no;
                    $record['v_date'] = $value->v_date;
                    ;
                    $record['ssb_account_id_from'] = $value->ssb_account_id_from;
                    $record['ssb_account_id_to'] = $value->ssb_account_id_to;
                    $record['cheque_no'] = null;
                    $record['cheque_date'] = null;
                    $record['cheque_bank_from'] = null;
                    $record['cheque_bank_ac_from'] = null;
                    $record['cheque_bank_ifsc_from'] = null;
                    $record['cheque_bank_branch_from'] = null;
                    $record['cheque_bank_from_id'] = null;
                    $record['cheque_bank_ac_from_id'] = null;
                    $record['cheque_bank_to'] = null;
                    $record['cheque_bank_ac_to'] = null;
                    $record['cheque_bank_to_name'] = null;
                    $record['cheque_bank_to_branch'] = null;
                    $record['cheque_bank_to_ac_no'] = null;
                    $record['cheque_bank_to_ifsc'] = null;
                    $record['transction_no'] = null;
                    $record['transction_bank_from'] = null;
                    $record['transction_bank_ac_from'] = null;
                    $record['transction_bank_ifsc_from'] = null;
                    $record['transction_bank_branch_from'] = null;
                    $record['transction_bank_from_id'] = null;
                    $record['transction_bank_from_ac_id'] = null;
                    $record['transction_bank_to'] = null;
                    $record['transction_bank_ac_to'] = null;
                    $record['transction_bank_to_name'] = null;
                    $record['transction_bank_to_ac_no'] = null;
                    $record['transction_bank_to_branch'] = null;
                    $record['transction_bank_to_ifsc'] = null;
                    $record['transction_date'] = $value->transaction_date;
                    $record['entry_date'] = $value->entry_date;
                    ;
                    $record['entry_time'] = $value->entry_time;
                    $record['created_by'] = $value->created_by;
                    $record['created_by_id'] = $value->created_by_id;
                    $record['created_at'] = $value->created_at;
                    $record['updated_at'] = $value->updated_at;
                    $record['ssb_account_tran_id_to'] = null;
                    $record['ssb_account_tran_id_from'] = null;
                    $insertData = AllHeadTransaction::create($record);
                    print_r("success");
                }
            }
        }
    }
    public function update_loan_amount()
    {
        $branch_id = 1;
        $records = AllHeadTransaction::where('branch_id', $branch_id)->where('type', 5)->where('sub_type', 57)->where('head_id', 90)->get();
        foreach ($records as $data) {
            $existRecord = Memberloans::where('id', $data->type_id)->first();
            $loanAmount = AllHeadTransaction::where('type', 5)->where('sub_type', 51)->whereIn('head_id', [64, 65, 67])->where('type_id', $data->type_id)->first();
            print_r('success');
            if ($existRecord->amount != $loanAmount->amount) {
                $loanAmount->update(['amount' => $existRecord->amount, 'opening_balance' => $existRecord->amount, 'closing_balance' => $existRecord->amount]);
                ;
                print_r('success');
            }
            $branchData = BranchDaybook::where('daybook_ref_id', $loanAmount->daybook_ref_id)->where('type', 5)->where('sub_type', 51)->first();
            $branchData->update(['amount' => $existRecord->amount, 'opening_balance' => $existRecord->amount, 'closing_balance' => $existRecord->amount, 'description_dr' => 'To' . $existRecord->account_number . 'A/C Dr ' . $existRecord->amount]);
            print_r('success');
        }
    }
    public function update_grploan_amount()
    {
        $branch_id = 1;
        $records = AllHeadTransaction::whereIN('branch_id', [$branch_id])->where('type', 5)->where('sub_type', 58)->where('head_id', 90)->where('is_deleted', 0)->get();
        foreach ($records as $data) {
            $existRecord = Grouploans::where('id', $data->type_id)->first();
            $loanAmount = AllHeadTransaction::where('type', 5)->where('sub_type', 54)->whereIn('head_id', [66])->where('type_id', $data->type_id)->where('is_deleted', 0)->first();
            if (isset($existRecord->amount)) {
                if ($loanAmount != '') {
                    if ($existRecord->amount != $loanAmount->amount) {
                        $loanAmount->update(['amount' => $existRecord->amount, 'opening_balance' => $existRecord->amount, 'closing_balance' => $existRecord->amount]);
                        ;
                        print_r('success');
                    }
                    $branchData = BranchDaybook::where('daybook_ref_id', $loanAmount->daybook_ref_id)->where('type', 5)->where('sub_type', 54)->first();
                    $branchData->update(['amount' => $existRecord->amount, 'opening_balance' => $existRecord->amount, 'closing_balance' => $existRecord->amount, 'description_dr' => 'To' . $existRecord->account_number . 'A/C Dr ' . $existRecord->amount]);
                    print_r('success');
                }
            } else {
                dd($data->type_id);
            }
        }
    }
    public function insert_mb_interest_in_deposite()
    {
        $branch_id = 4;
        $records = AllHeadTransaction::where('branch_id', $branch_id)->where('type', 3)->where('sub_type', 36)->where('head_id', 36)->get();
        foreach ($records as $key => $value) {
            $ifexists = AllHeadTransaction::where('head_id', 85)->where('daybook_ref_id', $value->daybook_ref_id)->count();
            //dd($ifexists);
            if ($ifexists == 0) {
                //dd($value->id);
                $record['daybook_ref_id'] = $value->daybook_ref_id;
                $record['branch_id'] = $value->branch_id;
                $record['type'] = $value->type;
                $record['sub_type'] = $value->sub_type;
                $record['head_id'] = 85;
                $record['type_id'] = $value->type_id;
                $record['type_transaction_id'] = $value->type_transaction_id;
                ;
                $record['associate_id'] = $value->associate_id;
                $record['member_id'] = $value->member_id;
                $record['branch_id_to'] = $value->branch_id_to;
                $record['branch_id_from'] = $value->branch_id_from;
                $record['opening_balance'] = $value->amount;
                $record['amount'] = $value->amount;
                $record['closing_balance'] = $value->amount;
                $record['description'] = $value->description;
                $record['payment_type'] = 'CR';
                $record['payment_mode'] = $value->payment_mode;
                $record['currency_code'] = $value->currency_code;
                $record['amount_to_id'] = $value->amount_to_id;
                $record['amount_to_name'] = $value->amount_to_name;
                $record['amount_from_id'] = $value->amount_from_id;
                ;
                $record['amount_from_name'] = $value->amount_from_name;
                ;
                $record['v_no'] = $value->v_no;
                $record['v_date'] = $value->v_date;
                ;
                $record['ssb_account_id_from'] = $value->ssb_account_id_from;
                $record['ssb_account_id_to'] = $value->ssb_account_id_to;
                $record['cheque_no'] = null;
                $record['cheque_date'] = null;
                $record['cheque_bank_from'] = null;
                $record['cheque_bank_ac_from'] = null;
                $record['cheque_bank_ifsc_from'] = null;
                $record['cheque_bank_branch_from'] = null;
                $record['cheque_bank_from_id'] = null;
                $record['cheque_bank_ac_from_id'] = null;
                $record['cheque_bank_to'] = null;
                $record['cheque_bank_ac_to'] = null;
                $record['cheque_bank_to_name'] = null;
                $record['cheque_bank_to_branch'] = null;
                $record['cheque_bank_to_ac_no'] = null;
                $record['cheque_bank_to_ifsc'] = null;
                $record['transction_no'] = null;
                $record['transction_bank_from'] = null;
                $record['transction_bank_ac_from'] = null;
                $record['transction_bank_ifsc_from'] = null;
                $record['transction_bank_branch_from'] = null;
                $record['transction_bank_from_id'] = null;
                $record['transction_bank_from_ac_id'] = null;
                $record['transction_bank_to'] = null;
                $record['transction_bank_ac_to'] = null;
                $record['transction_bank_to_name'] = null;
                $record['transction_bank_to_ac_no'] = null;
                $record['transction_bank_to_branch'] = null;
                $record['transction_bank_to_ifsc'] = null;
                $record['transction_date'] = $value->transaction_date;
                $record['entry_date'] = $value->entry_date;
                ;
                $record['entry_time'] = $value->entry_time;
                $record['created_by'] = $value->created_by;
                $record['created_by_id'] = $value->created_by_id;
                $record['created_at'] = $value->created_at;
                $record['updated_at'] = $value->updated_at;
                $record['ssb_account_tran_id_to'] = null;
                $record['ssb_account_tran_id_from'] = null;
                $insertData = AllHeadTransaction::create($record);
                print_r("success");
            }
        }
    }
    public function update_stationary_payment_mode()
    {
        $branch_id = 19;
        $records = AllHeadTransaction::where('head_id', 122)->whereIn('type', [21])->where('sub_type', 211) /*->where('branch_id',$branch_id)*/->get();
        foreach ($records as $key => $value) {
            $value->update(['payment_type' => 'CR']);
            // $newRewcord = AllHeadTransaction::where('daybook_ref_id',$value->daybook_ref_id)->where('head_id',28)->update(['payment_type'=>'DR']);
        }
        print_r('success');
    }
    public function update_emi_transaction_date()
    {
        $branchId = 30;
        $typeId = 561;
        $records = AllHeadTransaction::where('branch_id', $branchId)->where('type', 5)->whereIN('sub_type', [52, 523])->where('type_id', $typeId)->where('is_deleted', 0)->get();
        foreach ($records as $key => $value) {
            $data = \App\Models\LoanDayBooks::where('day_book_id', $value->type_transaction_id)->first();
            if (isset($data->created_at)) {
                $date = date('Y-m-d', strtotime($data->created_at));
                $d = AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['entry_date' => $date, 'branch_id' => $data->branch_id]);
                //$value->update(['entry_date' =>$date]);
            } else {
                //dd($value->id);
                // $value->update(['is_deleted' =>1]);
                // print_r('ee');
            }
            print_r('done');
        }
    }
    public function update_emi_transaction_delete_status()
    {
        $branchId = 6;
        $typeId = 298;
        $records = AllHeadTransaction::where('branch_id', $branchId)->where('type', 5)->where('sub_type', 52)->where('type_id', $typeId)->where('is_deleted', 0)->get();
        foreach ($records as $key => $value) {
            $data = \App\Models\LoanDayBooks::where('day_book_id', $value->type_transaction_id)->first();
            if ($data == NULL) {
                $value->update(['is_deleted' => 1]);
            } else {
                $value->update(['is_deleted' => 0]);
            }
            print_r('done');
        }
    }
    public function update_same_daybook_entry_date()
    {
        $branchId = 6;
        $typeId = 298;
        $records = AllHeadTransaction::where('branch_id', $branchId)->where('type', 5)->where('sub_type', 52)->where('is_deleted', 0)->whereBetween('entry_date', ['2020-06-01', '2021-03-31'])->get();
        foreach ($records as $key => $value) {
            $date = date('Y-m-d', strtotime($value->entry_date));
            $data = \App\Models\AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['entry_date' => $date]);
            print_r('done');
        }
    }
    public function update_daybook_ssb_type()
    {
        $ssbrecord = SavingAccount::get();
        foreach ($ssbrecord as $key => $value) {
            $record = Daybook::where('account_no', $value->account_no)->where('transaction_type', 2)->first();
            if ($record) {
                if ($record->transaction_type == 2) {
                    $record->update(['transaction_type' => 1]);
                    print_r('success');
                }
            }
        }
    }
    public function getMaturityEliInvestment2()
    {
        $startdate = '2020-06-01';
        $endDate = '2021-03-31';
        $demandRecord = DemandAdvice::with('investment')->where('branch_id', 4)->where('payment_type', 4)->where('is_mature', 0)->whereBetween('date', [$startdate, $endDate])->get();
        foreach ($demandRecord as $key => $value) {
            $DaybookRecord = Daybook::where('account_no', $value['investment']->account_number)->where('transaction_type', 17)->where('is_deleted', 0)->first();
            if ($DaybookRecord) {
                $LastRecordDaybook = Daybook::where('account_no', $value['investment']->account_number)->where('id', '<', $DaybookRecord->id)->where('is_deleted', 0)->orderBy('id', 'desc')->first();
                if ($DaybookRecord->withdrawal != $LastRecordDaybook->opening_balance) {
                    $DaybookRecord->update(['withdrawal' => $LastRecordDaybook->opening_balance]);
                }
                $BranchDaybookRecord = BranchDaybook::where('type', 13)->where('sub_type', 137)->where('type_id', $value->id)->where('type_transaction_id', $value->id)->where('is_deleted', 0)->first();
                if ($BranchDaybookRecord) {
                    if ($BranchDaybookRecord->amount != $LastRecordDaybook->opening_balance) {
                        $BranchDaybookRecord->update(['amount' => $LastRecordDaybook->opening_balance, 'opening_balance' => $LastRecordDaybook->opening_balance, 'closing_balance' => $LastRecordDaybook->opening_balance]);
                        $updateAlltransaction = AllHeadTransaction::where('daybook_ref_id', $BranchDaybookRecord->daybook_ref_id)->where('head_id', 28)->update(['amount' => $LastRecordDaybook->opening_balance]);
                        print_r('success' . $BranchDaybookRecord->daybook_ref_id);
                    }
                }
            }
        }
    }
    public function updateRecordFDAndFFD(Request $request)
    {
        $memberInvestment = Memberinvestments::where('plan_id', 4)->whereIN('account_number', ['R-091503000372'])->get();
        foreach ($memberInvestment as $key => $value) {
            $entry_date = date("Y-m-d", strtotime($value->created_at));
            if ($entry_date == '1970-01-01') {
                $entry_date = '2020-06-01';
                $entry_time = $value->created_at->format('H:i:s');
                $created_at = $entry_date;
            } else {
                $entry_date = $value->created_at;
                $entry_time = $entry_date->format('H:i:s');
                $created_at = $entry_date;
            }
            $data['amount'] = $value->deposite_amount;
            $data['entry_date'] = $entry_date;
            $data['entry_time'] = $entry_time;
            $data['created_at'] = $created_at;
            $data['updated_at'] = $value->updated_at;
            $branch_id = $value->branch_id;
            $branch_ref = \App\Models\BranchDaybookReference::create($data);
            $daybook_ref_id = $branch_ref->id;
            $daybookRecord = Daybook::where('account_no', $value->account_number)->where('transaction_type', 2)->where('branch_id', $branch_id)->orderBy('created_at', 'ASC')->count();
            if ($daybookRecord == 0) {
                $member_name = getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name;
                $data_log['transaction_type'] = 2;
                $data_log['transaction_id'] = null;
                $data_log['saving_account_transaction_reference_id'] = NULL;
                $data_log['investment_id'] = $value->id;
                $data_log['account_no'] = $value->account_number;
                $data_log['associate_id'] = $value->associate_id;
                $data_log['member_id'] = $value->member_id;
                $data_log['opening_balance'] = $value->deposite_amount;
                $data_log['deposit'] = $value->deposite_amount;
                $data_log['withdrawal'] = NULL;
                $data_log['description'] = 'FFD Account Opening';
                $data_log['reference_no'] = $value->account_number;
                $data_log['branch_id'] = $value->branch_id;
                $data_log['branch_code'] = getBranchDetail($value->branch_id)->branch_code;
                $data_log['amount'] = $value->deposite_amount;
                $data_log['currency_code'] = 'INR';
                $data_log['payment_mode'] = $value->payment_mode;
                $data_log['payment_type'] = $value->payment_type;
                $data_log['saving_account_id'] = 0;
                $data_log['cheque_dd_no'] = null;
                $data_log['bank_name'] = null;
                $data_log['branch_name'] = null;
                $data_log['payment_date'] = null;
                $data_log['online_payment_id'] = null;
                $data_log['online_payment_by'] = null;
                $data_log['amount_deposit_by_name'] = null;
                $data_log['received_cheque_id'] = null;
                $data_log['cheque_deposit_bank_id'] = null;
                $data_log['cheque_deposit_bank_ac_id'] = null;
                $data_log['online_deposit_bank_id'] = null;
                $data_log['online_deposit_bank_ac_id'] = null;
                $data_log['amount_deposit_by_id'] = $value->member_id;
                $data_log['created_by_id'] = $value->branch_id;
                $data_log['created_by'] = 2;
                $data_log['is_renewal'] = 1;
                $data_log['status'] = 1;
                $data_log['is_eli'] = 1;
                $data_log['is_deleted'] = 0;
                $data_log['created_at'] = $created_at;
                $data_log['updated_at'] = $value->updated_at;
                $data_log['app_login_user_id'] = NULL;
                $data_log['is_app'] = 0;
                $insertData4 = Daybook::create($data_log);
                print_r('success');
            }
            $existsBranchDaybook = BranchDaybook::where('type', 3)->where('sub_type', 30)->where('branch_id', $branch_id)->where('type_id', $value->id)->count();
            if ($existsBranchDaybook == 0) {
                $data_branch['daybook_ref_id'] = $daybook_ref_id;
                $data_branch['branch_id'] = $value->branch_id;
                $data_branch['type'] = 3;
                $data_branch['sub_type'] = 30;
                $data_branch['type_id'] = $value->id;
                $data_branch['type_transaction_id'] = $insertData4->id;
                $data_branch['associate_id'] = $value->associate_id;
                $data_branch['member_id'] = $value->member_id;
                $data_branch['branch_id_to'] = null;
                $data_branch['branch_id_from'] = null;
                $data_branch['opening_balance'] = $value->deposite_amount;
                $data_branch['amount'] = $value->deposite_amount;
                $data_branch['closing_balance'] = $value->deposite_amount;
                $data_branch['description'] = 'Amount received for FFD A/C Deposit ' . $value->account_number . 'through cash ' . (getBranchDetail($value->branch_id)->branch_code);
                //$data_branch['description'] ='Eli Amount Dr ' .number_format((float)$value->deposite_amount, 2, '.', '').'/-' ;
                $data_branch['description_dr'] = 'Eli Amount Dr ' . number_format((float) $value->deposite_amount, 2, '.', '') . '/-';
                $data_branch['description_cr'] = 'To  ' . $value->account_number . ' A/c Cr ' . number_format((float) $value->deposite_amount, 2, '.', '') . '/-';
                ;
                $data_branch['payment_type'] = 'CR';
                $data_branch['payment_mode'] = $value->payment_mode;
                $data_branch['currency_code'] = $value->currency_code;
                $data_branch['amount_to_id'] = null;
                $data_branch['amount_to_name'] = null;
                $data_branch['amount_from_id'] = null;
                $data_branch['amount_from_name'] = null;
                $data_branch['v_no'] = null;
                $data_branch['v_date'] = null;
                $data_branch['ssb_account_id_from'] = null;
                $data_branch['ssb_account_id_to'] = null;
                $data_branch['cheque_no'] = null;
                $data_branch['cheque_date'] = null;
                $data_branch['cheque_bank_from'] = null;
                $data_branch['cheque_bank_ac_from'] = null;
                $data_branch['cheque_bank_ifsc_from'] = null;
                $data_branch['cheque_bank_branch_from'] = null;
                $data_branch['cheque_bank_from_id'] = null;
                $data_branch['cheque_bank_ac_from_id'] = null;
                $data_branch['cheque_bank_to'] = null;
                $data_branch['cheque_bank_ac_to'] = null;
                $data_branch['cheque_bank_to_name'] = null;
                $data_branch['cheque_bank_to_branch'] = null;
                $data_branch['cheque_bank_to_ac_no'] = null;
                $data_branch['cheque_bank_to_ifsc'] = null;
                $data_branch['transction_no'] = null;
                $data_branch['transction_bank_from'] = null;
                $data_branch['transction_bank_ac_from'] = null;
                $data_branch['transction_bank_ifsc_from'] = null;
                $data_branch['transction_bank_branch_from'] = null;
                $data_branch['transction_bank_from_id'] = null;
                $data_branch['transction_bank_from_ac_id'] = null;
                $data_branch['transction_bank_to'] = null;
                $data_branch['transction_bank_ac_to'] = null;
                $data_branch['transction_bank_to_name'] = null;
                $data_branch['transction_bank_to_ac_no'] = null;
                $data_branch['transction_bank_to_branch'] = null;
                $data_branch['transction_bank_to_ifsc'] = null;
                $data_branch['transction_date'] = null;
                $data_branch['entry_date'] = $entry_date;
                ;
                $data_branch['entry_time'] = $entry_time;
                $data_branch['created_by'] = 2;
                $data_branch['created_by_id'] = $value->branch_id;
                $data_branch['is_contra'] = NULL;
                $data_branch['contra_id'] = NULL;
                $data_branch['created_at'] = $created_at;
                $data_branch['updated_at'] = $value->updated_at;
                $data_branch['ssb_account_tran_id_to'] = null;
                $data_branch['ssb_account_tran_id_from'] = null;
                $data_branch['amount_type'] = 0;
                $insertData1 = BranchDaybook::create($data_branch);
                print_r("success");
            }
            $existsBranchDaybook = MemberTransaction::where('type', 3)->where('sub_type', 30)->where('branch_id', $branch_id)->where('type_id', $value->id)->count();
            if ($existsBranchDaybook == 0) {
                $member_transa['daybook_ref_id'] = $daybook_ref_id;
                $member_transa['branch_id'] = $value->branch_id;
                $member_transa['type'] = 3;
                $member_transa['sub_type'] = 30;
                $member_transa['type_id'] = $value->if;
                $member_transa['type_transaction_id'] = $insertData4->id;
                ;
                $member_transa['associate_id'] = $value->associate_id;
                $member_transa['member_id'] = $value->member_id;
                $member_transa['amount'] = $value->deposite_amount;
                $member_transa['description'] = 'Amount received for FFD A/C Deposit ' . $value->account_number . 'through cash ' . (getBranchDetail($value->branch_id)->branch_code);
                //$member_transa['description'] ='Eli Amount Dr ' .number_format((float)$value->deposite_amount, 2, '.', '').'/-' ;
                $member_transa['payment_type'] = 'CR';
                $member_transa['payment_mode'] = $value->payment_mode;
                $member_transa['currency_code'] = $value->currency_code;
                $member_transa['amount_to_id'] = null;
                $member_transa['amount_to_name'] = null;
                $member_transa['amount_from_id'] = null;
                $member_transa['amount_from_name'] = null;
                $member_transa['v_no'] = null;
                $member_transa['v_date'] = null;
                $member_transa['ssb_account_id_from'] = null;
                $member_transa['ssb_account_id_to'] = null;
                $member_transa['cheque_no'] = null;
                $member_transa['cheque_date'] = null;
                $member_transa['cheque_bank_from'] = null;
                $member_transa['cheque_bank_ac_from'] = null;
                $member_transa['cheque_bank_ifsc_from'] = null;
                $member_transa['cheque_bank_branch_from'] = null;
                $member_transa['cheque_bank_from_id'] = null;
                $member_transa['cheque_bank_ac_from_id'] = null;
                $member_transa['cheque_bank_to'] = null;
                $member_transa['cheque_bank_ac_to'] = null;
                $member_transa['cheque_bank_to_name'] = null;
                $member_transa['cheque_bank_to_branch'] = null;
                $member_transa['cheque_bank_to_ac_no'] = null;
                $member_transa['cheque_bank_to_ifsc'] = null;
                $member_transa['transction_no'] = null;
                $member_transa['transction_bank_from'] = null;
                $member_transa['transction_bank_ac_from'] = null;
                $member_transa['transction_bank_ifsc_from'] = null;
                $member_transa['transction_bank_branch_from'] = null;
                $member_transa['transction_bank_from_id'] = null;
                $member_transa['transction_bank_from_ac_id'] = null;
                $member_transa['transction_bank_to'] = null;
                $member_transa['transction_bank_ac_to'] = null;
                $member_transa['transction_bank_to_name'] = null;
                $member_transa['transction_bank_to_ac_no'] = null;
                $member_transa['transction_bank_to_branch'] = null;
                $member_transa['transction_bank_to_ifsc'] = null;
                $member_transa['transction_date'] = null;
                $member_transa['entry_date'] = $entry_date;
                ;
                $member_transa['entry_time'] = $entry_time;
                $member_transa['created_by'] = 2;
                $member_transa['created_by_id'] = $value->branch_id;
                $member_transa['jv_unique_id'] = NULL;
                $member_transa['cheque_type'] = NULL;
                $member_transa['cheque_id'] = NULL;
                $member_transa['created_at'] = $created_at;
                $member_transa['updated_at'] = $value->updated_at;
                $member_transa['ssb_account_tran_id_to'] = null;
                $member_transa['ssb_account_tran_id_from'] = null;
                $insertData5 = MemberTransaction::create($member_transa);
                print_r("success");
            }
            $existAllTransactionCashInHand = AllHeadTransaction::where('type', 3)->where('sub_type', 30)->where('branch_id', $branch_id)->where('head_id', 89)->where('type_id', $value->id)->count();
            $existAllTransactionDailyDeposite = AllHeadTransaction::where('type', 3)->where('branch_id', $branch_id)->where('sub_type', 30)->where('head_id', 59)->where('type_id', $value->id)->count();
            if ($existAllTransactionCashInHand == 0) {
                $allTransaction['daybook_ref_id'] = $daybook_ref_id;
                $allTransaction['branch_id'] = $value->branch_id;
                $allTransaction['type'] = 3;
                $allTransaction['sub_type'] = 30;
                $allTransaction['head_id'] = 89;
                $allTransaction['type_id'] = $value->id;
                $allTransaction['type_transaction_id'] = $insertData4->id;
                $allTransaction['associate_id'] = $value->associate_id;
                $allTransaction['member_id'] = $value->member_id;
                $allTransaction['branch_id_to'] = null;
                $allTransaction['branch_id_from'] = null;
                $allTransaction['opening_balance'] = null;
                $allTransaction['amount'] = $value->deposite_amount;
                $allTransaction['closing_balance'] = null;
                $allTransaction['description'] = 'Amount received for FFD A/C Deposit ' . $value->account_number . 'through cash ' . (getBranchDetail($value->branch_id)->branch_code);
                $allTransaction['payment_type'] = 'DR';
                $allTransaction['payment_mode'] = $value->payment_mode;
                $allTransaction['currency_code'] = $value->currency_code;
                $allTransaction['amount_to_id'] = null;
                $allTransaction['amount_to_name'] = null;
                $allTransaction['amount_from_id'] = null;
                $allTransaction['amount_from_name'] = null;
                $allTransaction['v_no'] = null;
                $allTransaction['v_date'] = null;
                ;
                $allTransaction['ssb_account_id_from'] = null;
                $allTransaction['ssb_account_id_to'] = null;
                $allTransaction['cheque_no'] = null;
                $allTransaction['cheque_date'] = null;
                $allTransaction['cheque_bank_from'] = null;
                $allTransaction['cheque_bank_ac_from'] = null;
                $allTransaction['cheque_bank_ifsc_from'] = null;
                $allTransaction['cheque_bank_branch_from'] = null;
                $allTransaction['cheque_bank_from_id'] = null;
                $allTransaction['cheque_bank_ac_from_id'] = null;
                $allTransaction['cheque_bank_to'] = null;
                $allTransaction['cheque_bank_ac_to'] = null;
                $allTransaction['cheque_bank_to_name'] = null;
                $allTransaction['cheque_bank_to_branch'] = null;
                $allTransaction['cheque_bank_to_ac_no'] = null;
                $allTransaction['cheque_bank_to_ifsc'] = null;
                $allTransaction['transction_no'] = null;
                $allTransaction['transction_bank_from'] = null;
                $allTransaction['transction_bank_ac_from'] = null;
                $allTransaction['transction_bank_ifsc_from'] = null;
                $allTransaction['transction_bank_branch_from'] = null;
                $allTransaction['transction_bank_from_id'] = null;
                $allTransaction['transction_bank_from_ac_id'] = null;
                $allTransaction['transction_bank_to'] = null;
                $allTransaction['transction_bank_ac_to'] = null;
                $allTransaction['transction_bank_to_name'] = null;
                $allTransaction['transction_bank_to_ac_no'] = null;
                $allTransaction['transction_bank_to_branch'] = null;
                $allTransaction['transction_bank_to_ifsc'] = null;
                $allTransaction['transction_date'] = null;
                $allTransaction['entry_date'] = $entry_date;
                ;
                $allTransaction['entry_time'] = $entry_time;
                $allTransaction['created_by'] = 2;
                $allTransaction['created_by_id'] = $value->branch_id;
                $allTransaction['created_at'] = $created_at;
                $allTransaction['updated_at'] = $value->updated_at;
                $allTransaction['ssb_account_tran_id_to'] = null;
                $allTransaction['ssb_account_tran_id_from'] = null;
                $insertData2 = AllHeadTransaction::create($allTransaction);
                print_r("success");
            }
            if ($existAllTransactionDailyDeposite == 0) {
                $allTransaction['daybook_ref_id'] = $daybook_ref_id;
                $allTransaction['branch_id'] = $value->branch_id;
                $allTransaction['type'] = 3;
                $allTransaction['sub_type'] = 30;
                $allTransaction['head_id'] = 79;
                $allTransaction['type_id'] = $value->id;
                $allTransaction['type_transaction_id'] = $insertData4->id;
                $allTransaction['associate_id'] = $value->associate_id;
                $allTransaction['member_id'] = $value->member_id;
                $allTransaction['branch_id_to'] = null;
                $allTransaction['branch_id_from'] = null;
                $allTransaction['opening_balance'] = null;
                $allTransaction['amount'] = $value->deposite_amount;
                $allTransaction['closing_balance'] = null;
                $allTransaction['description'] = 'Amount received for FFD A/C Deposit ' . $value->account_number . 'through cash ' . (getBranchDetail($value->branch_id)->branch_code);
                $allTransaction['payment_type'] = 'CR';
                $allTransaction['payment_mode'] = $value->payment_mode;
                $allTransaction['currency_code'] = $value->currency_code;
                $allTransaction['amount_to_id'] = null;
                $allTransaction['amount_to_name'] = null;
                $allTransaction['amount_from_id'] = null;
                $allTransaction['amount_from_name'] = null;
                $allTransaction['v_no'] = null;
                $allTransaction['v_date'] = null;
                ;
                $allTransaction['ssb_account_id_from'] = null;
                $allTransaction['ssb_account_id_to'] = null;
                $allTransaction['cheque_no'] = null;
                $allTransaction['cheque_date'] = null;
                $allTransaction['cheque_bank_from'] = null;
                $allTransaction['cheque_bank_ac_from'] = null;
                $allTransaction['cheque_bank_ifsc_from'] = null;
                $allTransaction['cheque_bank_branch_from'] = null;
                $allTransaction['cheque_bank_from_id'] = null;
                $allTransaction['cheque_bank_ac_from_id'] = null;
                $allTransaction['cheque_bank_to'] = null;
                $allTransaction['cheque_bank_ac_to'] = null;
                $allTransaction['cheque_bank_to_name'] = null;
                $allTransaction['cheque_bank_to_branch'] = null;
                $allTransaction['cheque_bank_to_ac_no'] = null;
                $allTransaction['cheque_bank_to_ifsc'] = null;
                $allTransaction['transction_no'] = null;
                $allTransaction['transction_bank_from'] = null;
                $allTransaction['transction_bank_ac_from'] = null;
                $allTransaction['transction_bank_ifsc_from'] = null;
                $allTransaction['transction_bank_branch_from'] = null;
                $allTransaction['transction_bank_from_id'] = null;
                $allTransaction['transction_bank_from_ac_id'] = null;
                $allTransaction['transction_bank_to'] = null;
                $allTransaction['transction_bank_ac_to'] = null;
                $allTransaction['transction_bank_to_name'] = null;
                $allTransaction['transction_bank_to_ac_no'] = null;
                $allTransaction['transction_bank_to_branch'] = null;
                $allTransaction['transction_bank_to_ifsc'] = null;
                $allTransaction['transction_date'] = null;
                $allTransaction['entry_date'] = $entry_date;
                ;
                $allTransaction['entry_time'] = $entry_time;
                $allTransaction['created_by'] = 2;
                $allTransaction['created_by_id'] = $value->branch_id;
                $allTransaction['created_at'] = $created_at;
                $allTransaction['updated_at'] = $value->updated_at;
                $allTransaction['ssb_account_tran_id_to'] = null;
                $allTransaction['ssb_account_tran_id_from'] = null;
                $insertData2 = AllHeadTransaction::create($allTransaction);
                print_r("success");
            }
        }
    }
    public function file_charge_in_branch()
    {
        $record = AllHeadTransaction::whereIn('daybook_ref_id', ['117453', '117459', '117463', '117465', '117503', '117862', '117853'])->get();
        foreach ($record as $value) {
            $existsBranchDaybook = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->count();
            if ($existsBranchDaybook == 0) {
                $data_branch['daybook_ref_id'] = $value->daybook_ref_id;
                $data_branch['branch_id'] = $value->branch_id;
                $data_branch['type'] = $value->type;
                $data_branch['sub_type'] = $value->sub_type;
                $data_branch['type_id'] = $value->type_id;
                $data_branch['type_transaction_id'] = $value->type_transaction_id;
                $data_branch['associate_id'] = $value->associate_id;
                $data_branch['member_id'] = $value->member_id;
                $data_branch['branch_id_to'] = null;
                $data_branch['branch_id_from'] = null;
                $data_branch['opening_balance'] = $value->amount;
                $data_branch['amount'] = $value->amount;
                $data_branch['closing_balance'] = $value->amount;
                $data_branch['description'] = $value->description;
                $data_branch['description_dr'] = 'Cash A/c Dr ' . number_format((float) $value->amount, 2, '.', '') . '/-';
                $data_branch['description_cr'] = 'File Charge ' . number_format((float) $value->amount, 2, '.', '') . '/-';
                ;
                $data_branch['payment_type'] = $value->payment_type;
                $data_branch['payment_mode'] = 5;
                $data_branch['currency_code'] = $value->currency_code;
                $data_branch['amount_to_id'] = null;
                $data_branch['amount_to_name'] = null;
                $data_branch['amount_from_id'] = null;
                $data_branch['amount_from_name'] = null;
                $data_branch['v_no'] = null;
                $data_branch['v_date'] = null;
                $data_branch['ssb_account_id_from'] = null;
                $data_branch['ssb_account_id_to'] = null;
                $data_branch['cheque_no'] = null;
                $data_branch['cheque_date'] = null;
                $data_branch['cheque_bank_from'] = null;
                $data_branch['cheque_bank_ac_from'] = null;
                $data_branch['cheque_bank_ifsc_from'] = null;
                $data_branch['cheque_bank_branch_from'] = null;
                $data_branch['cheque_bank_from_id'] = null;
                $data_branch['cheque_bank_ac_from_id'] = null;
                $data_branch['cheque_bank_to'] = null;
                $data_branch['cheque_bank_ac_to'] = null;
                $data_branch['cheque_bank_to_name'] = null;
                $data_branch['cheque_bank_to_branch'] = null;
                $data_branch['cheque_bank_to_ac_no'] = null;
                $data_branch['cheque_bank_to_ifsc'] = null;
                $data_branch['transction_no'] = null;
                $data_branch['transction_bank_from'] = null;
                $data_branch['transction_bank_ac_from'] = null;
                $data_branch['transction_bank_ifsc_from'] = null;
                $data_branch['transction_bank_branch_from'] = null;
                $data_branch['transction_bank_from_id'] = null;
                $data_branch['transction_bank_from_ac_id'] = null;
                $data_branch['transction_bank_to'] = null;
                $data_branch['transction_bank_ac_to'] = null;
                $data_branch['transction_bank_to_name'] = null;
                $data_branch['transction_bank_to_ac_no'] = null;
                $data_branch['transction_bank_to_branch'] = null;
                $data_branch['transction_bank_to_ifsc'] = null;
                $data_branch['transction_date'] = null;
                $data_branch['entry_date'] = $value->entry_date;
                ;
                $data_branch['entry_time'] = $value->entry_time;
                $data_branch['created_by'] = $value->created_by;
                $data_branch['created_by_id'] = $value->branch_id;
                $data_branch['is_contra'] = NULL;
                $data_branch['contra_id'] = NULL;
                $data_branch['created_at'] = $value->created_at;
                $data_branch['updated_at'] = $value->updated_at;
                $data_branch['ssb_account_tran_id_to'] = null;
                $data_branch['ssb_account_tran_id_from'] = null;
                $data_branch['amount_type'] = 0;
                $insertData1 = BranchDaybook::create($data_branch);
                print_r("success");
            }
        }
    }
    public function insert_cash_in_hand_withdrawal()
    {
        $record = AllHeadTransaction::whereIn('daybook_ref_id', ['180624', '180625', '180626', '180627', '180628', '180629', '180630', '180631', '180632', '180633', '180634', '180635', '180636', '180637', '180638', '180639', '180640', '180641', '180642', '180643', '180644', '180645', '180646', '180647', '180648', '180649', '180650', '180651', '180652', '180653', '180654', '180655', '180656', '180657', '180658', '180659', '180660', '180661'])->where('head_id', 56)->where('is_deleted', 0)->get();
        foreach ($record as $value) {
            $existsBranchDaybook = AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->where('head_id', 28)->count();
            if ($existsBranchDaybook == 0) {
                $allTransaction['daybook_ref_id'] = $value->daybook_ref_id;
                $allTransaction['branch_id'] = $value->branch_id;
                $allTransaction['type'] = 4;
                $allTransaction['sub_type'] = 43;
                $allTransaction['head_id'] = 28;
                $allTransaction['type_id'] = $value->type_id;
                $allTransaction['type_transaction_id'] = $value->type_transaction_id;
                $allTransaction['associate_id'] = $value->associate_id;
                $allTransaction['member_id'] = $value->member_id;
                $allTransaction['branch_id_to'] = null;
                $allTransaction['branch_id_from'] = null;
                $allTransaction['opening_balance'] = $value->amount;
                $allTransaction['amount'] = $value->amount;
                $allTransaction['closing_balance'] = $value->amount;
                $allTransaction['description'] = 'Cash Withdrawal';
                $allTransaction['payment_type'] = 'CR';
                $allTransaction['payment_mode'] = $value->payment_mode;
                $allTransaction['currency_code'] = $value->currency_code;
                $allTransaction['amount_to_id'] = null;
                $allTransaction['amount_to_name'] = null;
                $allTransaction['amount_from_id'] = null;
                $allTransaction['amount_from_name'] = null;
                $allTransaction['v_no'] = null;
                $allTransaction['v_date'] = null;
                ;
                $allTransaction['ssb_account_id_from'] = null;
                $allTransaction['ssb_account_id_to'] = null;
                $allTransaction['cheque_no'] = null;
                $allTransaction['cheque_date'] = null;
                $allTransaction['cheque_bank_from'] = null;
                $allTransaction['cheque_bank_ac_from'] = null;
                $allTransaction['cheque_bank_ifsc_from'] = null;
                $allTransaction['cheque_bank_branch_from'] = null;
                $allTransaction['cheque_bank_from_id'] = null;
                $allTransaction['cheque_bank_ac_from_id'] = null;
                $allTransaction['cheque_bank_to'] = null;
                $allTransaction['cheque_bank_ac_to'] = null;
                $allTransaction['cheque_bank_to_name'] = null;
                $allTransaction['cheque_bank_to_branch'] = null;
                $allTransaction['cheque_bank_to_ac_no'] = null;
                $allTransaction['cheque_bank_to_ifsc'] = null;
                $allTransaction['transction_no'] = null;
                $allTransaction['transction_bank_from'] = null;
                $allTransaction['transction_bank_ac_from'] = null;
                $allTransaction['transction_bank_ifsc_from'] = null;
                $allTransaction['transction_bank_branch_from'] = null;
                $allTransaction['transction_bank_from_id'] = null;
                $allTransaction['transction_bank_from_ac_id'] = null;
                $allTransaction['transction_bank_to'] = null;
                $allTransaction['transction_bank_ac_to'] = null;
                $allTransaction['transction_bank_to_name'] = null;
                $allTransaction['transction_bank_to_ac_no'] = null;
                $allTransaction['transction_bank_to_branch'] = null;
                $allTransaction['transction_bank_to_ifsc'] = null;
                $allTransaction['transction_date'] = null;
                $allTransaction['entry_date'] = $value->entry_date;
                ;
                $allTransaction['entry_time'] = $value->entry_time;
                $allTransaction['created_by'] = $value->created_by;
                $allTransaction['created_by_id'] = $value->branch_id;
                $allTransaction['created_at'] = $value->created_at;
                $allTransaction['updated_at'] = $value->updated_at;
                $allTransaction['ssb_account_tran_id_to'] = null;
                $allTransaction['ssb_account_tran_id_from'] = null;
                $insertData2 = AllHeadTransaction::create($allTransaction);
                print_r("success");
            }
        }
    }
    public function deleteStationaryanddateChangeDummyBranch()
    {
        $data = AllHeadTransaction::where('branch_id', 51)->update(['entry_date' => '2020-06-01', 'created_at' => '2020-06-01']);
        $data = BranchDaybook::where('branch_id', 51)->update(['entry_date' => '2020-06-01', 'created_at' => '2020-06-01']);
        $data = Daybook::where('branch_id', 51)->update(['created_at' => '2020-06-01']);
        $data = MemberTransaction::where('branch_id', 51)->update(['entry_date' => '2020-06-01', 'created_at' => '2020-06-01']);
        $dataDelete = AllHeadTransaction::where('branch_id', 51)->where('type', 3)->where('sub_type', 35)->update(['is_deleted' => 1, 'entry_date' => '2020-06-01', 'created_at' => '2020-06-01']);
        $updateBranchDaybook = BranchDaybook::where('branch_id', 51)->where('type', 3)->where('sub_type', 35)->update(['is_deleted' => 1, 'entry_date' => '2020-06-01', 'created_at' => '2020-06-01']);
        $updateDaybook = Daybook::where('branch_id', 51)->where('transaction_type', 19)->update(['is_deleted' => 1, 'created_at' => '2020-06-01']);
        $updateDaybook = MemberTransaction::where('branch_id', 51)->where('type', 3)->where('sub_type', 35)->update(['is_deleted' => 1, 'entry_date' => '2020-06-01', 'created_at' => '2020-06-01']);
        $updateMemberInvetsment = Memberinvestments::where('branch_id', 51)->update(['created_at' => '2020-06-01']);
        $updateMemberInvetsment = SavingAccountTranscation::where('branch_id', 51)->update(['created_at' => '2020-06-01']);
        print_r("De dana dun");
    }
    public function update_expense_head_date()
    {
        $record = \App\Models\BillExpense::with('expenses')->get();
        foreach ($record as $value) {
            $data = AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['entry_date' => $value['expenses']->bill_date]);
            $data = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->update(['entry_date' => $value['expenses']->bill_date]);
            print_r('De dana dun');
        }
    }
    public function update_salary_branch_id()
    {
        $branch_id = 2;
        $records = AllHeadTransaction::where('branch_id', $branch_id)->where('type', 5)->where('sub_type', 122)->where('is_deleted', 0)->whereBetween('entry_date', ['2020-06-01', '2021-03-31'])->get();
        foreach ($records as $value) {
            $branchDaybook = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->update(['branch_id' => $value->branch_id]);
            $branchDaybook = MemberTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['branch_id' => $value->branch_id]);
        }
        print_r('success');
    }
    public function update_grp_loan_type()
    {
        $data = BranchDaybook::whereIn('payment_mode', [1, 2])->where('type', 5)->where('sub_type', 54)->where('is_deleted', 0)->get();
        foreach ($data as $value) {
            // $updateBranchDaybook = BranchDaybook::where('daybook_ref_id',$value->daybook_ref_id)->update(['sub_type'=>$value->sub_type]);
            $updateSamraddhBankDaybook = SamraddhBankDaybook::where('daybook_ref_id', $value->daybook_ref_id)->update(['sub_type' => $value->sub_type]);
            $updateMemberTransaction = MemberTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['sub_type' => $value->sub_type]);
            print_r('success');
        }
    }
    public function loandaybookDaybookRefId()
    {
        $getEMi = AllHeadTransaction::where('type', 5)->where('sub_type', 55)->wherein('head_id', [66])->get();
        $getPenalty = AllHeadTransaction::where('type', 5)->where('sub_type', 56)->where('head_id', 33)->get();
        foreach ($getEMi as $key => $value) {
            $am = number_format((float) $value->amount, 2, '.', '');
            $updateLoanDaybook = \App\Models\LoanDayBooks::where('loan_id', $value->type_id)->where('principal_amount', $am)->where('payment_date', $value->entry_date)->where('loan_sub_type', 0)->whereIn('loan_type', [3])->update(['daybook_ref_id' => $value->daybook_ref_id]);
            print_r('success' . $value->daybook_ref_id);
        }
        foreach ($getPenalty as $key => $value) {
            $am = number_format((float) $value->amount, 2, '.', '');
            $updateLoanDaybook = \App\Models\LoanDayBooks::where('loan_id', $value->type_id)->where('principal_amount', $am)->where('payment_date', $value->entry_date)->where('loan_sub_type', 1)->whereIn('loan_type', [3])->update(['daybook_ref_id' => $value->daybook_ref_id]);
            print_r('success' . $value->daybook_ref_id);
        }
    }
    // public function registerCIB()
    // {
    //     date_default_timezone_set("Asia/Calcutta");
    //     //Config UAT
    //     $url  = "https://apibankingone.icicibank.com/api/v1/composite-status";
    //     $url_cib_reg = "https://apibankingone.icicibank.com/api/Corporate/CIB/v1/Registration";
    //     $post_cib_reg = [
    //         "CORPID" => "SAM26840",
    //         "AGGRNAME"=>"SAMRADDHB",
    //         "USERID" => "AMRENDRA",
    //         "AGGRID" => "CUST0675",
    //         "BANKID" => "ICI",
    //         "URN" => "1235678"
    //     ];
    //     $apostData = json_encode($post_cib_reg);
    //     // print_r("<<========apostData=========>><br />");
    //     // print_r($apostData);
    //     $sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key
    //    // $encryptedKey = 1234567890123456;
    //     $fp= fopen("/home/mysamraddh/public_html/core/debit-card/LiveCert.txt","r");
    //     $pub_key_string=fread($fp,4096);
    //     //fclose($fp);
    //     openssl_get_publickey($pub_key_string);
    //     openssl_public_encrypt($sessionKey,$encryptedKey,$pub_key_string); // RSA
    //     $iv = 1234567890123456; //str_repeat("\0", 16);
    //     $encryptedData = openssl_encrypt($apostData, 'aes-128-cbc', $sessionKey, OPENSSL_RAW_DATA, $iv); // AES
    //     $request = [
    //         "requestId"=> "req_".time(),
    //         "encryptedKey"=> base64_encode($encryptedKey),
    //         "iv"=> base64_encode($iv),
    //         "encryptedData"=> base64_encode($encryptedData),
    //         "oaepHashingAlgorithm"=> "NONE",
    //         "service"=> "",
    //         "clientInfo"=> "",
    //         "optionalParam"=> ""
    //     ];
    //     // print_r("<<========request=========>><br />");
    //     // print_r($request);
    //    // dd($request);
    //     $apostData = json_encode($request);
    //     // print_r("<<========apostData=========>><br />");
    //     // print_r($apostData);
    //     $httpUrl = $url_cib_reg;
    //     // print_r("<<========httpUrl=========>><br />");
    //     // print_r($httpUrl);
    //     $headers = array(
    //         "cache-control: no-cache",
    //         "accept: application/json",
    //         "content-type: application/json",
    //         "apikey: D7KJ26CSZdIXqGJGtC1N5vatkGhV2xdP",
    //         "x-priority:0010"
    //     );
    //     // print_r("<<========headers=========>><br>");
    //     //  print_r($headers);
    //     $acurl = curl_init();
    //     curl_setopt_array($acurl, array(
    //         CURLOPT_URL => $httpUrl,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => "",
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 300,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => "POST",
    //         CURLOPT_POSTFIELDS => $apostData,
    //         CURLOPT_HTTPHEADER => $headers,
    //     ));
    //     $aresponse = curl_exec($acurl);
    //      dd($httpUrl,$headers,$apostData,$aresponse);
    //     print_r("<<========aresponse=========>><br />");
    //     $aerr = curl_error($acurl);
    //     $httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);
    //     print_r("<<========httpcode=========>><br />");
    //     print_r($httpcode);
    //     print_r("<<========curlresponse=========>><br />");
    //     print_r($aresponse);
    //     if ($aerr) {
    //         echo "cURL Error #:" . $aerr;
    //     } else {
    //         $fp= fopen("/home/mysamraddh/public_html/core/debit-card/uatprivatekey.pem","r");
    //         dd($fp);
    //         $priv_key=fread($fp,4096);
    //         fclose($fp);
    //         $res = openssl_get_privatekey($priv_key, "");
    //         $data = json_decode($aresponse);
    //         dd($data);
    //         openssl_private_decrypt(base64_decode($data->encryptedKey), $key, $priv_key);
    //         $encData = openssl_decrypt(base64_decode($data->encryptedData),"aes-128-cbc",$key,OPENSSL_PKCS1_PADDING);
    //         $newsource = substr($encData, 16);
    //         $log = "\n\n".'GUID - '."================================================================\n <br>";
    //         $log .= 'URL - '.$httpUrl."\n\n <br>";
    //         $log .= 'RESPONSE - '.json_encode($aresponse)."\n\n <br>";
    //         $log .= 'REQUEST ENCRYPTED - '.json_encode($newsource)."\n\n <br>";
    //         // file_put_contents($file, $log, FILE_APPEND | LOCK_EX);
    //         $output = json_decode($newsource);
    //         print_r("<<========output=========>><br />");
    //         print_r($output);
    //     }
    // }
    public function updateTransferdateFundTransfer()
    {
        $amount = '26000';
        $cashAmount = '118818';
        $branchCode = '1023';
        $date = '2022-02-02';
        $updateAmount = '2600';
        $fund = \App\Models\FundTransfer::where('micro_day_book_amount', $cashAmount)->where('amount', $amount)->where('branch_code', $branchCode)->where(\DB::raw('DATE(transfer_date_time)'), $date)->first();
        $updateAllTransaction = AllHeadTransaction::where('type', 7)->where('type_id', $fund->id)->update(['amount' => $updateAmount, 'opening_balance' => $updateAmount, 'closing_balance' => $updateAmount]);
        $updateBranchDaybook = BranchDaybook::where('type', 7)->where('type_id', $fund->id)->update(['amount' => $updateAmount, 'opening_balance' => $updateAmount, 'closing_balance' => $updateAmount]);
        $updatesamraddh = SamraddhBankDaybook::where('type', 7)->where('type_id', $fund->id)->update(['amount' => $updateAmount, 'opening_balance' => $updateAmount, 'closing_balance' => $updateAmount]);
        $fund->update(['amount' => $updateAmount]);
        print_r('success');
    }
    public function sendAmountSher()
    {
        //  DB::beginTransaction();
        // try
        // {
        $url_neft_uat = "https://apibankingone.icicibank.com/api/v1/composite-payment";
        $debitCard = \App\Models\DebitCard::where('status', 1)->where('running_transaction', 0)->get();
        foreach ($debitCard as $ac) {
            $sumAmount = \App\Models\SavingAccountTranscation::with(['savingAc' => function ($q) {
                $q->select('id', 'member_id', 'account_no', 'member_id', 'associate_id', 'branch_id', 'balance');
            }
            ])
                ->where('saving_account_id', $ac['ssb_id'])
                ->orderBy('created_at', 'desc')
                ->first();
            $amount = 500;
            $amountSend = number_format((float) round($sumAmount->opening_balance) - $amount, 0, '.', '');
            $checkAmount = number_format((float) round($sumAmount->opening_balance), 0, '.', '');
            if ($checkAmount > $amount) {
                $memberId = getMemberData($sumAmount['savingAc']->member_id);
                date_default_timezone_set("Asia/Calcutta");
                $date = Carbon::now()->format('dmYhis');
                $trsNo = $memberId->member_id . $date;
                $post_neft = [
                    "tranRefNo" => $trsNo,
                    "amount" => $amountSend,
                    "senderAcctNo" => "675105601216",
                    "beneAccNo" => $ac['card_no'],
                    "beneIFSC" => "ICIC0000106",
                    "beneName" => "SHERABHA",
                    "narration1" => "1235678",
                    "crpId" => "SAMRADDH05102020",
                    "crpUsr" => "AMRENDRA",
                    "aggrName" => "SAMRADDHB",
                    "aggrId" => "CUST0675",
                    "urn" => "SR209617949",
                    "txnType" => "RGS",
                ];
                //dd($post_neft);
                $apostData = json_encode($post_neft);
                $sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key
                $fp = fopen("/home/mysamraddh/public_html/core/debit-card/LiveCert.txt", "r");
                $pub_key_string = fread($fp, 4096);
                fclose($fp);
                openssl_get_publickey($pub_key_string);
                openssl_public_encrypt($sessionKey, $encryptedKey, $pub_key_string); // RSA
                $iv = 1234567890123456; //str_repeat("\0", 16);
                $encryptedData = openssl_encrypt($apostData, 'aes-128-cbc', $sessionKey, OPENSSL_RAW_DATA, $iv); // AES
                $request = [
                    "requestId" => "req_" . time(),
                    "encryptedKey" => base64_encode($encryptedKey),
                    "iv" => base64_encode($iv),
                    "encryptedData" => base64_encode($encryptedData),
                    "oaepHashingAlgorithm" => "NONE",
                    "service" => "",
                    "clientInfo" => "",
                    "optionalParam" => ""
                ];
                $apostData = json_encode($request);
                $httpUrl = $url_neft_uat;
                $headers = array(
                    "cache-control: no-cache",
                    "accept: application/json",
                    "content-type: application/json",
                    "apikey: YqDT8sE0XnGTXvGAHslvTxv2lVzwIRPw",
                    "x-priority:0010"
                );
                $acurl = curl_init();
                curl_setopt_array($acurl, array(
                    CURLOPT_URL => $httpUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 300,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $apostData,
                    CURLOPT_HTTPHEADER => $headers,
                ));
                $aresponse = curl_exec($acurl);
                // print_r("<<========aresponse=========>><br />");
                $aerr = curl_error($acurl);
                $httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);
                if ($aerr) {
                    echo "cURL Error #:" . $aerr;
                } else {
                    $fp = fopen("/home/mysamraddh/public_html/core/debit-card/uatprivatekey.pem", "r");
                    $priv_key = fread($fp, 8192);
                    fclose($fp);
                    $res = openssl_get_privatekey($priv_key, "");
                    $data = json_decode($aresponse);
                    openssl_private_decrypt(base64_decode($data->encryptedKey), $key, $priv_key);
                    $encData = openssl_decrypt(base64_decode($data->encryptedData), "aes-128-cbc", $key, OPENSSL_PKCS1_PADDING);
                    $newsource = substr($encData, 16);
                    // dd(($data->encryptedKey), $key, $priv_key);
                    $log = "\n\n" . 'GUID - ' . "================================================================\n <br>";
                    $log .= 'URL - ' . $httpUrl . "\n\n <br>";
                    $log .= 'RESPONSE - ' . json_encode($aresponse) . "\n\n <br>";
                    $log .= 'REQUEST ENCRYPTED - ' . json_encode($newsource) . "\n\n <br>";
                    // file_put_contents($file, $log, FILE_APPEND | LOCK_EX);
                    $output = json_decode($newsource);
                    // print_r("<<========output=========>><br />");
                    $entryDate = Carbon::now()->format('Y-m-d');
                    $entryTime = Carbon::now()->format('H:m:s');
                    $Debitdata = [
                        'debit_card_id' => $ac['id'],
                        'transaction_id' => $output->UNIQUEID,
                        'payment_type' => 'CR',
                        'amount' => $amountSend,
                        'status' => '0',
                        'entry_date' => $entryDate,
                        'entry_time' => $entryTime,
                    ];
                    $createTransaction = \App\Models\DebitCardTransaction::create($Debitdata);
                    \App\Models\DebitCard::where('id', $ac['id'])->update(['running_transaction' => 1]);
                    \Log::info("Send Amount Request SuccessFully!" . $createTransaction);
                }
            }
        }
        // DB::commit();
        // }
        // catch(\Exception $ex)
        // {
        //     DB::rollback();
        //     return back()->with('alert', $ex->getMessage());
        // }
        \Log::info("Send Amount Request SuccessFully!");
    }
    public function updateamountLoan()
    {
        $branch = 3;
        $AllRecord = AllHeadTransaction::where('head_id', 28)->where('type', 5)->where('sub_type', 51)->whereBetween('entry_date', ['2020-06-01', '2021-03-31'])->where('branch_id', $branch)->get();
        foreach ($AllRecord as $key => $value) {
            $exist = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->exists();
            if ($exist) {
                $updateBranch = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->first();
                if ($value->amount != $updateBranch->amount) {
                    dd($value->daybook_ref_id, $value->amount, $updateBranch->amount);
                }
            }
        }
    }
    public function insertfileCHrage()
    {
        $branch = 32;
        $AllRecord = AllHeadTransaction::where('head_id', 90)->where('type', 5)->where('sub_type', 58)->whereBetween('entry_date', ['2020-06-01', '2021-03-31'])->where('branch_id', $branch)->get();
        foreach ($AllRecord as $key => $value) {
            $alldata = AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->where('head_id', 28)->exists();
            $exist = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->count();
            if ($alldata) {
                print_r($value->daybook_ref_id . ',');
            } else {
                $update = BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->update(['payment_mode' => 5]);
            }
        }
    }
    public function updateemiDate()
    {
        $record = \App\Models\LoanDayBooks::where('account_number', 321171500031)->get();
        foreach ($record as $key => $value) {
            $alldata = AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['entry_date' => $value->payment_date]);
        }
    }
    public function updatessbAmount()
    {
        $updatessbAmount = SavingAccount::get();
        foreach ($updatessbAmount as $key => $value) {
            $record = SavingAccountTranscation::where('saving_account_id', $value->id)->whereIn('account_no', ['101370300011', '321370300028', ''])->where('is_deleted', 0)->orderBy('created_at', 'asc')->get();
            foreach ($record as $key => $value2) {
                $re1 = SavingAccountTranscation::where('saving_account_id', $value->id)->where('id', '>', $value2->id)->where('is_deleted', 0)->orderBy('created_at', 'asc')->first();
                if ($re1) {
                    if ($value2->created_at < $re1->created_at) {
                        print_r($value2->account_no);
                        print_r('</br>');
                    }
                }
            }
        }
    }
    public function updateTimeSSb()
    {
        $account = SavingAccount::select('id', 'account_no')->whereIn('account_no', ['101170300005'])->get();
        foreach ($account as $ac) {
            //$a = SavingAccountTranscation::where('saving_account_id',$ac->id)->where('is_deleted',0)->orderBy('created_at','asc')->get();
            $a = SavingAccountTranscation::where('saving_account_id', $ac->id)->where('is_deleted', 0)->orderBy('id', 'ASC')->orderBy('created_at', 'DESC')->get();
            // dd($a);
            $amount = 0;
            foreach ($a as $trans) {
                //$record = SavingAccountTranscation::where('saving_account_id',31)where('id','>',$trans->id)->where('is_deleted',0)->orderBy('id','asc')->first();
                if (isset($trans->deposit)) {
                    $amount = $trans->deposit + $amount;
                }
                if (isset($trans->withdrawal)) {
                    $amount = $amount - $trans->withdrawal;
                }
                print_r('process');
                print_r('</br>');
                $trans = $trans->update(['opening_balance' => $amount]);
            }
            $ac->update(['balance' => $amount]);
        }
    }
    public function checkstatus()
    {
        date_default_timezone_set("Asia/Calcutta");
        //Config UAT
        DB::beginTransaction();
        try {
            $getData = \App\Models\DebitCardTransaction::get();
            foreach ($getData as $ac) {
                $ssb = \App\Models\DebitCard::with('savingAccount')->findorfail($ac->debit_card_id);
                $transactionLatest = SavingAccountTranscation::where('saving_account_id', $ssb['id'])
                    ->orderBy('created_at', 'desc')
                    ->first();
                //dd($transactionLatest);
                $uniqueId = '10117010000511042022020001';
                $url_neft_uat = "https://apibankingone.icicibank.com/api/v1/composite-status";
                // $post_neft = [
                //     "URN" => "SR209617949",
                //     "AGGRID" => "CUST0675",
                //     "CORPID" => "SAMRADDH05102020",
                //     "USERID" => "AMRENDRA",
                //     "UNIQUEID" => "8898956"
                // ];
                $post_neft = [
                    "URN" => "SR209617949",
                    "AGGRID" => "CUST0675",
                    "CORPID" => "SAMRADDH05102020",
                    "USERID" => "AMRENDRA",
                    "UNIQUEID" => '10117010000511042022020001',
                ];
                $apostData = json_encode($post_neft);
                print_r("<<========apostData=========>><br />");
                print_r($apostData);
                $sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key
                $fp = fopen("/home/mysamraddh/public_html/core/debit-card/LiveCert.txt", "r");
                $pub_key_string = fread($fp, 4096);
                //fclose($fp);
                openssl_get_publickey($pub_key_string);
                openssl_public_encrypt($sessionKey, $encryptedKey, $pub_key_string); // RSA
                $iv = 1234567890123456; //str_repeat("\0", 16);
                $encryptedData = openssl_encrypt($apostData, 'aes-128-cbc', $sessionKey, OPENSSL_RAW_DATA, $iv); // AES
                $request = [
                    "requestId" => "req_" . time(),
                    "encryptedKey" => base64_encode($encryptedKey),
                    "iv" => base64_encode($iv),
                    "encryptedData" => base64_encode($encryptedData),
                    "oaepHashingAlgorithm" => "NONE",
                    "service" => "",
                    "clientInfo" => "",
                    "optionalParam" => ""
                ];
                print_r("<<========request=========>><br />");
                print_r($request);
                $apostData = json_encode($request);
                print_r("<<========apostData=========>><br />");
                print_r($apostData);
                $httpUrl = $url_neft_uat;
                print_r("<<========httpUrl=========>><br />");
                print_r($httpUrl);
                $headers = array(
                    "cache-control: no-cache",
                    "accept: application/json",
                    "content-type: application/json",
                    "apikey: YqDT8sE0XnGTXvGAHslvTxv2lVzwIRPw",
                    "x-priority:0010"
                );
                print_r("<<========headers=========>><br>");
                print_r($headers);
                $acurl = curl_init();
                curl_setopt_array($acurl, array(
                    CURLOPT_URL => $httpUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 300,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $apostData,
                    CURLOPT_HTTPHEADER => $headers,
                ));
                $aresponse = curl_exec($acurl);
                print_r("<<========aresponse=========>><br />");
                $aerr = curl_error($acurl);
                $httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);
                print_r("<<========httpcode=========>><br />");
                print_r($httpcode);
                print_r("<<========curlresponse=========>><br />");
                print_r($aresponse);
                if ($aerr) {
                    echo "cURL Error #:" . $aerr;
                } else {
                    $fp = fopen("/home/mysamraddh/public_html/core/debit-card/uatprivatekey.pem", "r");
                    $priv_key = fread($fp, 8192);
                    fclose($fp);
                    $res = openssl_get_privatekey($priv_key, "");
                    $data = json_decode($aresponse);
                    openssl_private_decrypt(base64_decode($data->encryptedKey), $key, $priv_key);
                    $encData = openssl_decrypt(base64_decode($data->encryptedData), "aes-128-cbc", $key, OPENSSL_PKCS1_PADDING);
                    $newsource = substr($encData, 16);
                    $log = "\n\n" . 'GUID - ' . "================================================================\n <br>";
                    $log .= 'URL - ' . $httpUrl . "\n\n <br>";
                    $log .= 'RESPONSE - ' . json_encode($aresponse) . "\n\n <br>";
                    $log .= 'REQUEST ENCRYPTED - ' . json_encode($newsource) . "\n\n <br>";
                    // file_put_contents($file, $log, FILE_APPEND | LOCK_EX);
                    $output = json_decode($newsource);
                    print_r("<<========output=========>><br />");
                    print_r($output);
                    dd($output);
                    die();
                    if ($output->STATUS == 'SUCCESS' && $ac->status == 0) {
                        $amountSend = $ac->amount;
                        $entryDate = Carbon::now()->format('Y-m-d');
                        $entryTime = Carbon::now()->format('H:m:s');
                        $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($amountSend, $entryDate);
                        // $Debitdata = [
                        //    'debit_card_id' => $ac->id,
                        //    'daybook_ref_id' => $daybookRefRD,
                        //    'payment_type' =>  'CR',
                        //    'amount' => $amountSend,
                        //    'status' => '1',
                        //    'entry_date' => $entryDate,
                        //    'entry_time' =>  $entryTime,
                        // ] ;
                        // SSB Transaction
                        //$createTransaction = DebitCardTransaction::create($Debitdata);
                        $savingAccountId = $ssb['savingAccount']['id'];
                        $savingAccountAccount = $ssb['savingAccount']->account_no;
                        $savingBranch = $ssb['savingAccount']->branch_id;
                        $savingAssociateId = $ssb['savingAccount']->associate_id;
                        $savingMemberId = $ssb['savingAccount']->member_id;
                        $type = 15;
                        $openingBalance = $ssb->opening_balance - $ac->amount;
                        $des = 'SSb Amount Transfer to Debit ';
                        $paymentType = 'CR';
                        $paymentMode = 6;
                        $ssbData = [
                            'daybook_ref_id' => $daybookRefRD,
                            'saving_account_id' => $savingAccountId,
                            'account_no' => $savingAccountAccount,
                            'branch_id' => $savingBranch,
                            'associate_id' => $savingAssociateId,
                            'type' => $type,
                            'opening_balance' => $transactionLatest->opening_balance - $ac->amount,
                            'deposit' => 0,
                            'withdrawal' => $ac->amount,
                            'description' => $des,
                            'currency_code' => 'INR',
                            'payment_type' => $paymentType,
                            'payment_mode' => $paymentMode,
                        ];
                        $ssbTransaction = SavingAccountTranscation::create($ssbData);
                        // Branch Daybook Transaction
                        $branchDaybookData = [
                            'daybook_ref_id' => $daybookRefRD,
                            'branch_id' => $savingBranch,
                            'associate_id' => $savingAssociateId,
                            'type' => 4,
                            'sub_type' => 43,
                            'type_id' => $ac->id,
                            'type_transaction_id' => $ssb['savingAccount']->id,
                            'member_id' => $savingMemberId,
                            'opening_balance' => $ac->amount,
                            'amount' => $ac->amount,
                            'closing_balance' => $ac->amount,
                            'description' => $des,
                            'description_cr' => 'SSB To Debit Card' . $ac->card_no . 'Transfer',
                            'payment_type' => 'DR',
                            'payment_mode' => 8,
                            'entry_date' => $entryDate,
                            'entry_time' => $entryTime,
                        ];
                        $branchDaybookData2 = [
                            'daybook_ref_id' => $daybookRefRD,
                            'branch_id' => $savingBranch,
                            'associate_id' => $savingAssociateId,
                            'type' => 29,
                            'sub_type' => 292,
                            'type_id' => $ac->id,
                            'type_transaction_id' => $ac->ssb_id,
                            'member_id' => $savingMemberId,
                            'opening_balance' => $ac->amount,
                            'amount' => $ac->amount,
                            'closing_balance' => $ac->amount,
                            'description' => $des,
                            'description_cr' => 'SSB To Debit Card ' . $ssb->card_no . 'Transfer',
                            'payment_type' => $paymentType,
                            'payment_mode' => 8,
                            'entry_date' => $entryDate,
                            'entry_time' => $entryTime,
                        ];
                        $memberData = [
                            'daybook_ref_id' => $daybookRefRD,
                            'branch_id' => $savingBranch,
                            'associate_id' => $savingAssociateId,
                            'type' => 29,
                            'sub_type' => 292,
                            'type_id' => $ac->id,
                            'type_transaction_id' => $ssb['savingAccount']->id,
                            'member_id' => $savingMemberId,
                            'amount' => $ac->amount,
                            'description' => $des,
                            'payment_type' => $paymentType,
                            'payment_mode' => 8,
                            'entry_date' => $entryDate,
                            'entry_time' => $entryTime,
                        ];
                        $memberData2 = [
                            'daybook_ref_id' => $daybookRefRD,
                            'branch_id' => $savingBranch,
                            'associate_id' => $savingAssociateId,
                            'type' => 4,
                            'sub_type' => 43,
                            'type_id' => $ac->id,
                            'type_transaction_id' => $ssb['savingAccount']->id,
                            'member_id' => $savingMemberId,
                            'amount' => $ac->amount,
                            'description' => $des,
                            'payment_type' => 'DR',
                            'payment_mode' => 8,
                            'entry_date' => $entryDate,
                            'entry_time' => $entryTime,
                        ];
                        $brDaybook = BranchDaybook::create($branchDaybookData);
                        $brDaybook2 = BranchDaybook::create($branchDaybookData2);
                        $head1 = 56;
                        $head2 = 203;
                        $AllheadTransactionData1 = [
                            'daybook_ref_id' => $daybookRefRD,
                            'branch_id' => $savingBranch,
                            'associate_id' => $savingAssociateId,
                            'type' => 4,
                            'sub_type' => 43,
                            'type_id' => $ac->id,
                            'type_transaction_id' => $ssb['savingAccount']->id,
                            'member_id' => $savingMemberId,
                            'opening_balance' => $ac->amount,
                            'amount' => $ac->amount,
                            'closing_balance' => $ac->amount,
                            'description' => $des,
                            'payment_type' => 'DR',
                            'payment_mode' => 8,
                            'entry_date' => $entryDate,
                            'entry_time' => $entryTime,
                            'head_id' => $head1
                        ];
                        $AllheadTransactionData2 = [
                            'daybook_ref_id' => $daybookRefRD,
                            'branch_id' => $savingBranch,
                            'associate_id' => $savingAssociateId,
                            'type' => 29,
                            'sub_type' => 292,
                            'type_id' => $ac->id,
                            'type_transaction_id' => $ssb['savingAccount']->id,
                            'member_id' => $savingMemberId,
                            'opening_balance' => $ac->amount,
                            'amount' => $ac->amount,
                            'closing_balance' => $ac->amount,
                            'description' => $des,
                            'payment_type' => $paymentType,
                            'payment_mode' => 8,
                            'entry_date' => $entryDate,
                            'entry_time' => $entryTime,
                            'head_id' => $head2
                        ];
                        $allHead1 = \App\Models\AllHeadTransaction::create($AllheadTransactionData1);
                        $allHead2 = \App\Models\AllHeadTransaction::create($AllheadTransactionData2);
                        $memTran = \App\Models\MemberTransaction::create($memberData);
                        $memTran2 = \App\Models\MemberTransaction::create($memberData2);
                        $ac->update(['status' => 1]);
                        $updateSSbAccountBalance = \App\Models\SavingAccount::where('id', $ssb->ssb_id)->update(['balance' => $ssb['savingAccount']->balance - $ac->amount]);
                        \Log::info("Amount Transfer SuccessFully!" . $ac->card_no);
                    }
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
    }
    public function update_BRANHC_samraddh()
    {
        $data = SamraddhBankDaybook::whereNull('branch_id')->get();
        foreach ($data as $key => $value) {
            $d = AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->first();
            $value->update(['branch_id' => $d->branch_id]);
            print_r('success');
        }
    }
    public function insertBilldate()
    {
        $data = \App\Models\BillExpense::where('id', '>', 500)->orderBy('id', 'desc')->get();
        foreach ($data as $key => $value) {
            $a = \App\Models\Expense::where('bill_no', $value->id)->first();
            if ($a) {
                $a->update(['bill_no' => $value->bill_no]);
            }
            print_r('success');
        }
    }
    public function updateChildHead()
    {
        $heads = \App\Models\AccountHeads::get();
        foreach ($heads as $id) {
            $head_ids = array($id->head_id);
            $subHeadsIDS = AccountHeads::where('head_id', $id->head_id)->where('status', 0)->pluck('head_id')->toArray();
            if (count($subHeadsIDS) > 0) {
                $head_ids = array_merge($head_ids, $subHeadsIDS);
                $record = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
            }
            foreach ($record as $key => $value) {
                $ids[] = $value;
                $seralizedArray = json_encode($ids);
                AccountHeads::where('head_id', $id->head_id)->update(['child_head' => $seralizedArray]);
            }
        }
    }
    public function outstandingAmount_update()
    {
        $data = Memberloans::where('loan_type', 1)->where('emi_option', 1)->whereIn('status', [1, 4, 3])->whereIn('account_number', ['102171500021'])->whereNotNull('approve_date')->get();
        foreach ($data as $key => $value) {
            $emi_date = array();
            $initOut = $value->amount;
            $date = date('Y-m-d', strtotime($value->approve_date));
            $datessss = date('Y', strtotime($value->approve_date));
            $datesssss = date('m', strtotime($value->approve_date));
            $dd = date('Y');
            $tgtg = date('m');
            $deposit = 0;
            $emiAmount = $value->emi_amount;
            $diff = (($dd - $datessss) * 12) + ($tgtg - $datesssss);
            for ($i = 0; $i < $diff; $i++) {
                $rr = date('Y-m-d', strtotime($date . ' + 1 months'));
                $date = date('Y-m-d', strtotime($date . ' + 1 months'));
                $emi_date[] = $date;
            }
            $endRecord = end(($emi_date));
            $checkData = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where('is_deleted', 0) /*->where(\DB::raw('Date(created_at)'),'>',$endRecord)*/->get();
            $newDate = array();
            if (count($checkData) > 0) {
                foreach ($checkData as $index => $v) {
                    $newDate[] = date('Y-m-d', strtotime($v->created_at));
                }
            }
            $emi_date = array_merge($emi_date, $newDate);
            $narray = array();
            foreach ($emi_date as $KEY => $edate) {
                if (!in_array($edate, $narray)) {
                    $narray[str_replace('-', '', $edate)] = $edate;
                }
            }
            // if($value->emi_option  == 1)
            // {
            $a = ((($value->ROI) / 12) * $initOut) / 100;
            // }
            $emi_date = ($narray);
            asort($emi_date);
            // $startDate = $rr;
            // $newdate = $date; //date('Y-m-d');
            $emi_date = array_values($emi_date);
            $endRecord = end(($emi_date));
            $ad = date('Y-m-d', strtotime($value->approve_date . ' + 1 months'));
            for ($i = 0; $i < count($emi_date); $i++) {
                $da = $emi_date[$i];
                $deposit = 0;
                $monthCheck = date('m', strtotime($da));
                $yearCheck = date('Y', strtotime($da));
                $wmonthCheck = date('m', strtotime($da));
                $wyearCheck = date('Y', strtotime($da));
                $gdate = '';
                $exdate = '';
                $eDate = '';
                //dd($emi_date[$i],$emi_date[$i+1]);
                $damount = 0;
                $mout = 0;
                $penaltys = 0;
                if (strtotime($da) <= strtotime($endRecord)) {
                    if (strtotime($emi_date[$i]) <= strtotime(date('Y-m-d'))) {
                        $abbb = $emi_date[$i];
                        if (in_array($da, $emi_date)) {
                            if ($i > 0) {
                                $gdate = $emi_date[$i - 1];
                                $exdate = $emi_date[$i - 1];
                            } else {
                                $gdate = $value->created_at;
                                $exdate = $value->created_at;
                            }
                            // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=', $emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $gdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                            // $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=',$emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $exdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                            $exists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $gdate)->where(\DB::raw('Date(created_at)'), '<=', $abbb)->where('is_deleted', 0)->exists();
                            $Countexists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $gdate)->where(\DB::raw('Date(created_at)'), '<=', $abbb)->where('is_deleted', 0)->sum('deposit');
                            $emiDetail = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $gdate)->where(\DB::raw('Date(created_at)'), '<=', $abbb)->where('is_deleted', 0)->get();
                            if ($exists == false) {
                                $EmiId = NULL;
                                $transDate = NULL;
                                $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->exists();
                                if ($checkout == false) {
                                    $mout = ($initOut + $a);
                                    $initOut = $mout;
                                    $ammountArray[] = $initOut;
                                    $interest = $a;
                                    $principalAmount = 0 - $a;
                                    ;
                                } else {
                                    $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->first();
                                    if (isset($checkout->out_standing_amount)) {
                                        $newint = ((($value->ROI) / 12) * $checkout->out_standing_amount) / 100;
                                        $interest = $newint;
                                        $principalAmount = 0 - $interest;
                                        ;
                                        $mout = ($checkout->out_standing_amount + ($newint));
                                    }
                                }
                                if ($mout > 0) {
                                    $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->exists();
                                    if ($ddd == false) {
                                        $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $damount, 'loan_type' => $value->loan_type]);
                                    }
                                }
                            } else {
                                echo (count($emiDetail)) . "<br/>";
                                foreach ($emiDetail as $key => $emiSecond) {
                                    $damount = $emiSecond->principal_amount;
                                    $deposit = $emiSecond->deposit;
                                    $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->where('is_deleted', 0)->exists();
                                    $penalty = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->where('is_deleted', 0)->sum('principal_amount');
                                    $abbb = date('Y-m-d', strtotime($emiSecond->created_at));
                                    // echo($abbb)."<br/>";
                                    $transDate = $emiSecond->created_at;
                                    $EmiId = $emiSecond->id;
                                    $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->first();
                                    if (isset($checkout->out_standing_amount)) {
                                        $newint = ((($value->ROI) / 12) * $checkout->out_standing_amount) / 100;
                                        $principalAmount = $deposit - $newint;
                                        $interest = $newint;
                                        // if(($penalty) > 0)
                                        // {
                                        // $mout = ($checkout->out_standing_amount - $principalAmount - $penalty);
                                        // }
                                        // else{
                                        $mout = ($checkout->out_standing_amount - $principalAmount);
                                        // }
                                    } else {
                                        $interest = $a;
                                        $principalAmount = $deposit - $interest;
                                        $mout = ($initOut - ($principalAmount));
                                    }
                                    if ($mout > 0) {
                                        $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->WHERE('emi_id', $emiSecond->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->exists();
                                        if ($ddd == false) {
                                            $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $deposit, 'loan_type' => $value->loan_type]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                // else{
                //     $abbb = $emi_date[$i];
                // if(in_array($da, $emi_date)){
                //     if($i > 0)
                //     {
                //         $gdate = $emi_date[$i-1];
                //         $exdate =  $emi_date[$i-1];
                //     }
                //     else{
                //         $gdate = $value->created_at;
                //         $exdate = $value->created_at;
                //     }
                // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=', $emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $gdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                // $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=',$emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $exdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                //         $Emimonth = date('m',strtotime($abbb));
                //         $EmiYear= date('Y',strtotime($abbb));
                //         $newstartDate = $EmiYear.'-'.$Emimonth.'-01';
                //         $newendDate = $EmiYear.'-'.$Emimonth.'-31';
                //         $exists = \App\Models\LoanDayBooks::where('loan_sub_type',0)->whereBetween('created_at',[$newstartDate,$newendDate])->exists();
                //         $Countexists = \App\Models\LoanDayBooks::where('loan_sub_type',0)->WHERE('loan_id',$value->id)->whereBetween('created_at',[$newstartDate,$newendDate])->sum('deposit');
                //         $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type',1)->where('account_number',$value->account_number)->whereBetween('created_at',[$newstartDate,$newendDate])->exists();
                //         $penalty = \App\Models\LoanDayBooks::where('loan_sub_type',1)->where('account_number',$value->account_number)->whereBetween('created_at',[$newstartDate,$newendDate])->first();
                //         $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->whereBetween('created_at',[$newstartDate,$newendDate])->first();
                //         if($exists == false){
                //             $EmiId = NULL;
                //             $transDate = NULL;
                //             $checkout =     \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->exists();
                //             if($checkout == false){
                //                 $mout = ($initOut + $a);
                //                 $initOut = $mout;
                //                 $ammountArray[]= $initOut;
                //                 $interest = $a;
                //                 $principalAmount =0 - $a;;
                //             }
                //             else{
                //                 $checkout = \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->first();
                //                     if(isset($checkout->out_standing_amount)){
                //                         $newint =  ((($value->ROI)/12) * $checkout->out_standing_amount)/100;
                //                         $interest = $newint;
                //                         $principalAmount =0 - $interest;;
                //                         $mout = ($checkout->out_standing_amount + ($newint));
                //                     }
                //                 }
                //         }else{
                //             if(($Countexists))
                //             {
                //                     $damount = $Countexists;
                //             }
                //             else{
                //                 $damount =$emiDetail->deposit;
                //             }
                //             $transDate =$abbb;
                //             $EmiId = $emiDetail->id;
                //             $checkout = \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->first();
                //             if(isset($checkout->out_standing_amount))
                //             {
                //                 $newint =  ((($value->ROI)/12) * $checkout->out_standing_amount)/100;
                //                 $principalAmount = $damount - $newint;
                //                 $interest = $newint;
                //                 $penalty = 0;
                //                 if(isset($penalty->principal_amount))
                //                 {
                //                    $mout = ($checkout->out_standing_amount - $principalAmount - $penalty->principal_amount);
                //                    $penalty = $penalty->principal_amount;
                //                 }
                //                 else{
                //                    $mout = ($checkout->out_standing_amount - $principalAmount);
                //                 }
                //             }
                //             else{
                //                 $interest = $a;
                //                 $principalAmount = $damount - $interest;
                //                 $mout = ($initOut - ($principalAmount ));
                //             }
                //         }
                //         $CHECKr = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->where('emi_date',$abbb)->where('out_standing_amount',$mout)->where('roi_amount',$interest)->where('principal_amount',$principalAmount)->exists();
                //         if($mout >  0 )
                //         {
                //             $ddd = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->where('emi_date',$abbb)->exists();
                //             if($ddd == false)
                //             {
                //                 $createRecord = \App\Models\LoanEmisNew::create(['loan_id'=>$value->id,'out_standing_amount'=>$mout,'emi_date'=>$abbb,'roi_amount'=>$interest,'principal_amount'=>$principalAmount,'emi_received_date'=>$transDate,'emi_id'=>$EmiId,'penalty'=>$penaltys,'emi_option'=>$value->emi_option,'deposit'=>$damount]);
                //             }
                //         }
                //         print_r("jhkhkk" );
                //     // }
                // }
                $ad = date('Y-m-d', strtotime($da . ' + 1 months'));
            }
            print_r('success');
        }
    }
    public function updateBalanceSheetInterest()
    {
        $data = \App\Models\LoanDayBooks::where('account_number', 101171500130)->where('loan_sub_type', 0)->get();
        foreach ($data as $key => $value) {
            if (isset($value->day_book_id)) {
                $a = AllHeadTransaction::where('type', 5)->where('sub_type', 52)->where('type_id', $value->loan_id)->where('type_transaction_id', $value->day_book_id)->where('head_id', 64)->first();
                $b = AllHeadTransaction::where('type', 5)->where('sub_type', 523)->where('type_id', $value->loan_id)->where('type_transaction_id', $value->day_book_id)->where('head_id', 31)->first();
            } else {
                $a = AllHeadTransaction::where('type', 5)->where('sub_type', 52)->where('type_id', $value->loan_id)->where('daybook_ref_id', $value->daybook_ref_id)->where('head_id', 64)->first();
                $b = AllHeadTransaction::where('type', 5)->where('sub_type', 52)->where('type_id', $value->loan_id)->where('daybook_ref_id', $value->daybook_ref_id)->where('head_id', 31)->first();
            }
            $emi = \App\Models\LoanEmisNew::where('emi_id', $value->id)->first();
            $a->update(['opening_balance' => $emi->principal_amount, 'amount' => $emi->principal_amount, 'closing_balance' => $emi->principal_amount]);
            $b->update(['opening_balance' => $emi->roi_amount, 'amount' => $emi->roi_amount, 'closing_balance' => $emi->roi_amount]);
            $value->update(['opening_balance' => $emi->out_standing_amount, 'roi_amount' => $emi->roi_amount, 'principal_amount' => $emi->principal_amount]);
            print_r('success');
        }
    }
    public function outstandingAmount_updateDaily()
    {
        $data = Memberloans::where('account_number', 321371500012)->where('loan_type', 1)->where('emi_option', 3)->whereIn('status', [4])->whereNotNull('approve_date')->get();
        foreach ($data as $key => $value) {
            $emi_date = array();
            $initOut = $value->amount;
            $date = date('Y-m-d', strtotime($value->approve_date));
            //$date = Carbon::now()->subDays(2);
            $datessss = date('Y', strtotime($value->approve_date));
            $datesssss = date('m', strtotime($value->approve_date));
            $dd = date('Y-m-d');
            $tgtg = date('m');
            $emiAmount = $value->emi_amount;
            $diff = today()->diffInDays($date);
            for ($i = 0; $i <= $diff; $i++) {
                $rr = date('Y-m-d', strtotime($date . ' + 1 days'));
                $date = date('Y-m-d', strtotime($date . ' + 1 days'));
                $emi_date[] = $date;
            }
            $endRecord = end(($emi_date));
            $checkData = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $endRecord)->where('is_deleted', 0)->get();
            $newDate = array();
            if (count($checkData) > 0) {
                foreach ($checkData as $index => $v) {
                    $newDate[] = date('Y-m-d', strtotime($v->created_at));
                }
            }
            $emi_date = array_merge($emi_date, $newDate);
            // if($value->emi_option  == 1)
            // {
            $a = ((($value->ROI) / 365) * $initOut) / 100;
            $endRecord = end(($emi_date));
            $ad = date('Y-m-d', strtotime($value->approve_date . ' + 1 days'));
            for ($i = 0; $i < count($emi_date); $i++) {
                $da = $ad;
                $damount = 0;
                $monthCheck = date('m', strtotime($da));
                $yearCheck = date('Y', strtotime($da));
                $wmonthCheck = date('m', strtotime($da));
                $wyearCheck = date('Y', strtotime($da));
                $gdate = '';
                $exdate = '';
                $eDate = '';
                //dd($emi_date[$i],$emi_date[$i+1]);
                $mout = 0;
                $penaltys = 0;
                if (strtotime($da) < strtotime($endRecord)) {
                    if (strtotime($emi_date[$i]) <= strtotime(date('Y-m-d'))) {
                        $abbb = $emi_date[$i];
                        // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=', $emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $gdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                        // $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=',$emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $exdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                        $exists = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where(\DB::raw('DATE(created_at)'), '=', $abbb)->where('is_deleted', 0)->exists();
                        $Countexists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('DATE(created_at)'), '=', $abbb)->where('is_deleted', 0)->sum('deposit');
                        $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('DATE(created_at)'), '=', $abbb)->where('is_deleted', 0)->exists();
                        $penalty = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('DATE(created_at)'), '=', $abbb)->where('is_deleted', 0)->first();
                        $emiDetail = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where(\DB::raw('DATE(created_at)'), '=', $abbb)->where('is_deleted', 0)->first();
                        if ($exists == false) {
                            $EmiId = NULL;
                            $transDate = NULL;
                            $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->exists();
                            if ($checkout == false) {
                                $mout = ($initOut + $a);
                                $initOut = $mout;
                                $ammountArray[] = $initOut;
                                $interest = $a;
                                $principalAmount = 0 - $a;
                                ;
                            } else {
                                $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->first();
                                if (isset($checkout->out_standing_amount)) {
                                    $newint = ((($value->ROI) / 365) * $checkout->out_standing_amount) / 100;
                                    $interest = $newint;
                                    $principalAmount = 0 - $interest;
                                    ;
                                    $mout = ($checkout->out_standing_amount + ($newint));
                                }
                            }
                        } else {
                            if ($Countexists > 0) {
                                $damount = $Countexists;
                            } else {
                                $damount = $emiDetail->deposit;
                            }
                            $abbb = date('Y-m-d', strtotime($emiDetail->created_at));
                            $transDate = $emiDetail->created_at;
                            $EmiId = $emiDetail->id;
                            $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->first();
                            if (isset($checkout->out_standing_amount)) {
                                $newint = ((($value->ROI) / 365) * $checkout->out_standing_amount) / 100;
                                $principalAmount = $damount - $newint;
                                $interest = $newint;
                                // if(isset($penalty->principal_amount))
                                // {
                                //    $mout = ($checkout->out_standing_amount - $principalAmount - $penalty->principal_amount);
                                // }
                                // else{
                                $mout = ($checkout->out_standing_amount - $principalAmount);
                                // }
                            } else {
                                $interest = $a;
                                $principalAmount = $damount - $interest;
                                $mout = ($initOut - ($principalAmount));
                            }
                        }
                        $CHECKr = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('emi_date', $abbb)->where('out_standing_amount', $mout)->where('roi_amount', $interest)->where('principal_amount', $principalAmount)->exists();
                        if ($mout > 0) {
                            $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->exists();
                            if ($ddd == false) {
                                $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $damount, 'loan_type' => $value->loan_type]);
                            }
                        }
                    }
                }
                // else{
                //     dd( $emi_date[$i]);
                //     $abbb = $emi_date[$i];
                //     // if(in_array($da, $emi_date)){
                //     //     if($i > 0)
                //     //     {
                //     //         $gdate = $emi_date[$i-1];
                //     //         $exdate =  $emi_date[$i-1];
                //     //     }
                //     //     else{
                //     //         $gdate = $value->created_at;
                //     //         $exdate = $value->created_at;
                //     //     }
                //         // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=', $emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $gdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                //         // $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=',$emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $exdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                //         $Emimonth = date('m',strtotime($abbb));
                //         $EmiYear= date('Y',strtotime($abbb));
                //         $newstartDate = $EmiYear.'-'.$Emimonth.'-01';
                //         $newendDate = $EmiYear.'-'.$Emimonth.'-31';
                //         $exists = \App\Models\LoanDayBooks::where('loan_sub_type',0)->whereBetween('created_at',[$newstartDate,$newendDate])->exists();
                //         $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('DATE(created_at)') ,'=', $abbb )->exists();
                //         $Countexists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('DATE(created_at)') ,'=', $abbb )->get();
                //         //$penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type',1)->where('account_number',$value->account_number)->where(\DB::raw('Month(created_at)') ,'=', $monthCheck )->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )->exists();
                //         //$penalty = \App\Models\LoanDayBooks::where('loan_sub_type',1)->where('account_number',$value->account_number)->where(\DB::raw('Month(created_at)') ,'=', $monthCheck )->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )->first();
                //         $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('DATE(created_at)') ,'=', $abbb )->first();
                //         if($exists == false){
                //             $EmiId = NULL;
                //             $transDate = NULL;
                //             $checkout =     \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->exists();
                //             if($checkout == false){
                //                 $mout = ($initOut + $a);
                //                 $initOut = $mout;
                //                 $ammountArray[]= $initOut;
                //                 $interest = $a;
                //                 $principalAmount =0 - $a;;
                //             }
                //             else{
                //                 $checkout = \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->first();
                //                     if(isset($checkout->out_standing_amount)){
                //                         $newint =  ((($value->ROI)/365) * $checkout->out_standing_amount)/100;
                //                         $interest = $newint;
                //                         $principalAmount =0 - $interest;;
                //                         $mout = ($checkout->out_standing_amount + ($newint));
                //                     }
                //                 }
                //         }else{
                //             if(($Countexists))
                //             {
                //                     $damount = $Countexists;
                //             }
                //             else{
                //                 $damount =$emiDetail->deposit;
                //             }
                //             $transDate =$abbb;
                //             $EmiId = $emiDetail->id;
                //             $checkout = \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->first();
                //             if(isset($checkout->out_standing_amount))
                //             {
                //                 $newint =  ((($value->ROI)/365) * $checkout->out_standing_amount)/100;
                //                 $principalAmount = $emiDetail->deposit - $newint;
                //                 $interest = $newint;
                //                 $penalty = 0;
                //                 if(isset($penalty->principal_amount))
                //                 {
                //                    $mout = ($checkout->out_standing_amount - $principalAmount - $penalty->principal_amount);
                //                    $penalty = $penalty->principal_amount;
                //                 }
                //                 else{
                //                    $mout = ($checkout->out_standing_amount - $principalAmount);
                //                 }
                //             }
                //             else{
                //                 $interest = $a;
                //                 $principalAmount = $emiDetail->deposit  - $interest;
                //                 $mout = ($initOut - ($principalAmount ));
                //             }
                //         }
                //         $CHECKr = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->where('emi_date',$abbb)->where('out_standing_amount',$mout)->where('roi_amount',$interest)->where('principal_amount',$principalAmount)->exists();
                //         if($CHECKr == false )
                //         {
                //             // $ddd = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->whereBetween('emi_date',[$newstartDate,$newendDate])->exists();
                //             // $ddds= \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->whereBetween('emi_date',[$newstartDate,$newendDate])->first();
                //             // if($ddd == False)
                //             // {
                //                 $createRecord = \App\Models\LoanEmisNew::create(['loan_id'=>$value->id,'out_standing_amount'=>$mout,'emi_date'=>$abbb,'roi_amount'=>$interest,'principal_amount'=>$principalAmount,'emi_received_date'=>$transDate,'emi_id'=>$EmiId,'penalty'=>$penalty]);
                //             // }
                //         }
                //         print_r("jhkhkk" );
                //     // }
                // }
                $ad = date('Y-m-d', strtotime($da . ' + 1 days'));
            }
            echo 'success' . $value->account_number . "</br>";
        }
    }
    public function outstandingAmount_updateWeekly()
    {
        $data = Memberloans::where('loan_type', 1)->where('emi_option', 2)->whereIn('status', [1, 4, 3])->where('account_number', 596271500003)->whereNotNull('approve_date')->get();
        foreach ($data as $key => $value) {
            $emi_date = array();
            $initOut = $value->amount;
            $date = date('Y-m-d', strtotime($value->approve_date));
            $datessss = date('W', strtotime($value->approve_date));
            $datesssss = date('m', strtotime($value->approve_date));
            $dd = date('Y-m-d');
            $tgtg = date('m');
            $penaltys = 0;
            $emiAmount = $value->emi_amount;
            $diff = number_format((float) today()->diffInDays($date) / 7, 0, '.', '');
            for ($i = 0; $i <= $diff; $i++) {
                $rr = date('Y-m-d', strtotime($date . ' + 7 days'));
                $date = date('Y-m-d', strtotime($date . ' + 7 days'));
                $emi_date[] = $date;
            }
            $endRecord = end(($emi_date));
            $checkData = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where('is_deleted', 0) /*->where(\DB::raw('Date(created_at)'),'>',$endRecord)*/->get();
            $checkDatalats = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('is_deleted', 0)->where('account_number', $value->account_number)->orderBy('id', 'desc')->first();
            $newDate = array();
            if (count($checkData) > 0) {
                foreach ($checkData as $index => $v) {
                    $newDate[] = date('Y-m-d', strtotime($v->created_at));
                }
            }
            $emi_date = array_merge($emi_date, $newDate);
            $a = ((($value->ROI) / 52.14) * $initOut) / 100;
            $endRecord = end(($emi_date));
            ;
            $ad = date('Y-m-d', strtotime($value->approve_date . ' + 7 days'));
            for ($i = 0; $i < count($emi_date); $i++) {
                $da = $ad;
                $penaltys = 0;
                $monthCheck = date('m', strtotime($da));
                $yearCheck = date('Y', strtotime($da));
                $wmonthCheck = date('m', strtotime($da));
                $wyearCheck = date('Y', strtotime($da));
                $gdate = '';
                $exdate = '';
                $eDate = '';
                //dd($emi_date[$i],$emi_date[$i+1]);
                $mout = 0;
                if (strtotime($da) <= strtotime($endRecord)) {
                    if (strtotime($da) <= strtotime(date('Y-m-d'))) {
                        $abbb = $da;
                        if (in_array($da, $emi_date)) {
                            if ($i > 0) {
                                $gdate = $emi_date[$i - 1];
                                $exdate = $emi_date[$i - 1];
                            } else {
                                $gdate = $value->created_at;
                                $exdate = $value->created_at;
                            }
                            $exists = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('is_deleted', 0)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $gdate)->where(\DB::raw('Date(created_at)'), '<=', $abbb) /*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                            $emiDetail = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('is_deleted', 0)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $gdate)->where(\DB::raw('Date(created_at)'), '<=', $abbb) /*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->get();
                            // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('DATE(created_at)') ,'=', $abbb )->exists();
                            // $Countexists = \App\Models\LoanDayBooks::where('loan_sub_type',0)->where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'>', $gdate)->where(\DB::raw('Date(created_at)') ,'<=', $abbb)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->sum('deposit');
                            // $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type',1)->where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'>', '2022-05-06')->where(\DB::raw('Date(created_at)') ,'<=', '2022-05-11')/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                            // $penalty = \App\Models\LoanDayBooks::where('loan_sub_type',1)->where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'>', '2022-05-06')->where(\DB::raw('Date(created_at)') ,'<=', '2022-05-11')/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                            // if($penaltyExists)
                            // {
                            //     $penaltys =number_format((float) $penalty->principal_amount, 2, '.', '') ;
                            // }
                            $dexists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', $abbb)->where('is_deleted', 0)->exists();
                            if ($exists == false) {
                                $deposit = 0;
                                $damount = 0;
                                $EmiId = NULL;
                                $transDate = NULL;
                                $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->exists();
                                if ($checkout == false) {
                                    $mout = ($initOut + $a);
                                    $initOut = $mout;
                                    $ammountArray[] = $initOut;
                                    $interest = $a;
                                    $principalAmount = 0 - $a;
                                    ;
                                } else {
                                    $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->first();
                                    if (isset($checkout->out_standing_amount)) {
                                        $newint = ((($value->ROI) / 52.14) * $checkout->out_standing_amount) / 100;
                                        $interest = $newint;
                                        $principalAmount = 0 - $interest;
                                        ;
                                        $mout = ($checkout->out_standing_amount + ($newint));
                                    }
                                }
                                if ($mout > 0) {
                                    $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->exists();
                                    if ($ddd == false) {
                                        $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $deposit, 'loan_type' => $value->loan_type]);
                                    }
                                }
                            } else {
                                foreach ($emiDetail as $key => $emiSecond) {
                                    $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->where('is_deleted', 0) /*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                                    $penalty = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->where('is_deleted', 0) /*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                                    $damount = $emiSecond->principal_amount;
                                    $deposit = $emiSecond->deposit;
                                    $abbb = date('Y-m-d', strtotime($emiSecond->created_at));
                                    if (isset($penalty->principal_amount)) {
                                        $penaltys = $penalty->principal_amount;
                                    } else {
                                        $penaltys = 0;
                                    }
                                    // echo($abbb)."<br/>";
                                    $transDate = $emiSecond->created_at;
                                    $EmiId = $emiSecond->id;
                                    $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->first();
                                    if (isset($checkout->out_standing_amount)) {
                                        $newint = ((($value->ROI) / 52.14) * $checkout->out_standing_amount) / 100;
                                        $principalAmount = $deposit - $newint;
                                        $interest = $newint;
                                        $mout = ($checkout->out_standing_amount - $principalAmount);
                                    } else {
                                        $interest = $a;
                                        $principalAmount = $deposit - $interest;
                                        $mout = ($initOut - ($principalAmount));
                                    }
                                    // if($mout >  0 )
                                    // {
                                    $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->WHERE('emi_id', $emiSecond->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->exists();
                                    if ($ddd == false) {
                                        $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $deposit, 'loan_type' => $value->loan_type]);
                                    }
                                    // }
                                }
                                //    if($Countexists > 0)
                                //     {
                                //         $damount = $Countexists;
                                //     }
                                //     else{
                                //     $damount = $emiDetail->deposit;
                                //     }
                                // //$damount = $emiDetail->principal_amount;
                                //     if(!isset($emiDetail->created_at))
                                //     {
                                //         dd($i);
                                //     }
                                //     $abbb =date('Y-m-d',strtotime($emiDetail->created_at));
                                //    echo ( $abbb)."<br/>";
                                //     $transDate =$emiDetail->created_at;
                                //     $EmiId = $emiDetail->id;
                                //     $checkout = \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->first();
                                //     if(isset($checkout->out_standing_amount))
                                //     {
                                //         $newint =  ((($value->ROI)/52.14) * $checkout->out_standing_amount)/100;
                                //         $principalAmount = $damount - $newint;
                                //         $interest = $newint;
                                //         if(isset($penalty->principal_amount))
                                //         {
                                //             echo ( $penalty->principal_amount)."<br/>";
                                //            $mout = ($checkout->out_standing_amount - $principalAmount - $penalty->principal_amount);
                                //         }
                                //         else{
                                //            $mout = ($checkout->out_standing_amount - $principalAmount);
                                //         }
                                //     }
                                //     else{
                                //         $interest = $a;
                                //         $principalAmount = $damount - $interest;
                                //         $mout = ($initOut - ($principalAmount ));
                                //     }
                            }
                            // if($mout > 0)
                            // {
                            //     $ddd = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->where('emi_date',$abbb)->exists();
                            //     if($ddd == false)
                            //     {
                            //         $createRecord = \App\Models\LoanEmisNew::create(['loan_id'=>$value->id,'out_standing_amount'=>$mout,'emi_date'=>$abbb,'roi_amount'=>$interest,'principal_amount'=>$principalAmount,'emi_received_date'=>$transDate,'emi_id'=>$EmiId,'penalty'=>$penaltys,'emi_option'=>$value->emi_option,'deposit'=>$damount]);
                            //     }
                            // }
                        }
                    }
                }
                // else{
                //     $abbb = $emi_date[$i];
                //     // if(in_array($da, $emi_date)){
                //     //     if($i > 0)
                //     //     {
                //     //         $gdate = $emi_date[$i-1];
                //     //         $exdate =  $emi_date[$i-1];
                //     //     }
                //     //     else{
                //     //         $gdate = $value->created_at;
                //     //         $exdate = $value->created_at;
                //     //     }
                //         // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=', $emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $gdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                //         // $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=',$emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $exdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                //         $Emimonth = date('m',strtotime($abbb));
                //         $EmiYear= date('Y',strtotime($abbb));
                //         $newstartDate = $EmiYear.'-'.$Emimonth.'-01';
                //         $newendDate = $EmiYear.'-'.$Emimonth.'-31';
                //         $exists = \App\Models\LoanDayBooks::where('loan_sub_type',0)->whereBetween('created_at',[$newstartDate,$newendDate])->exists();
                //         $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('DATE(created_at)') ,'=', $abbb )->exists();
                //         $Countexists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('DATE(created_at)') ,'=', $abbb )->get();
                //         //$penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type',1)->where('account_number',$value->account_number)->where(\DB::raw('Month(created_at)') ,'=', $monthCheck )->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )->exists();
                //         //$penalty = \App\Models\LoanDayBooks::where('loan_sub_type',1)->where('account_number',$value->account_number)->where(\DB::raw('Month(created_at)') ,'=', $monthCheck )->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )->first();
                //         $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('DATE(created_at)') ,'=', $abbb )->first();
                //         if($exists == false){
                //             $EmiId = NULL;
                //             $transDate = NULL;
                //             $checkout =     \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->exists();
                //             if($checkout == false){
                //                 $mout = ($initOut + $a);
                //                 $initOut = $mout;
                //                 $ammountArray[]= $initOut;
                //                 $interest = $a;
                //                 $principalAmount =0 - $a;;
                //             }
                //             else{
                //                 $checkout = \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->first();
                //                     if(isset($checkout->out_standing_amount)){
                //                         $newint =  ((($value->ROI)/365) * $checkout->out_standing_amount)/100;
                //                         $interest = $newint;
                //                         $principalAmount =0 - $interest;;
                //                         $mout = ($checkout->out_standing_amount + ($newint));
                //                     }
                //                 }
                //         }else{
                //             if(($Countexists))
                //             {
                //                     $damount = $Countexists;
                //             }
                //             else{
                //                 $damount =$emiDetail->deposit;
                //             }
                //             $transDate =$abbb;
                //             $EmiId = $emiDetail->id;
                //             $checkout = \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->first();
                //             if(isset($checkout->out_standing_amount))
                //             {
                //                 $newint =  ((($value->ROI)/365) * $checkout->out_standing_amount)/100;
                //                 $principalAmount = $emiDetail->deposit - $newint;
                //                 $interest = $newint;
                //                 $penalty = 0;
                //                 if(isset($penalty->principal_amount))
                //                 {
                //                    $mout = ($checkout->out_standing_amount - $principalAmount - $penalty->principal_amount);
                //                    $penalty = $penalty->principal_amount;
                //                 }
                //                 else{
                //                    $mout = ($checkout->out_standing_amount - $principalAmount);
                //                 }
                //             }
                //             else{
                //                 $interest = $a;
                //                 $principalAmount = $emiDetail->deposit  - $interest;
                //                 $mout = ($initOut - ($principalAmount ));
                //             }
                //         }
                //         $CHECKr = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->where('emi_date',$abbb)->where('out_standing_amount',$mout)->where('roi_amount',$interest)->where('principal_amount',$principalAmount)->exists();
                //         if($CHECKr == false )
                //         {
                //             // $ddd = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->whereBetween('emi_date',[$newstartDate,$newendDate])->exists();
                //             // $ddds= \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->whereBetween('emi_date',[$newstartDate,$newendDate])->first();
                //             // if($ddd == False)
                //             // {
                //                 $createRecord = \App\Models\LoanEmisNew::create(['loan_id'=>$value->id,'out_standing_amount'=>$mout,'emi_date'=>$abbb,'roi_amount'=>$interest,'principal_amount'=>$principalAmount,'emi_received_date'=>$transDate,'emi_id'=>$EmiId,'penalty'=>$penalty]);
                //             // }
                //         }
                //         print_r("jhkhkk" );
                //     // }
                // }
                $ad = date('Y-m-d', strtotime($da . ' + 7 days'));
            }
            print_r('success');
        }
    }
    public function updateOutstandingGrploan()
    {
        $data = Grouploans::where('emi_option', 2)->whereIn('status', [1, 4, 3])->where('account_number', 321471700001)->get();
        foreach ($data as $key => $value) {
            $emi_date = array();
            $initOut = $value->amount;
            // $date = date('Y-m-d', strtotime($value->created_at));
            // $datessss = date('Y', strtotime($value->created_at));
            // $datesssss = date('m', strtotime($value->created_at));
            $date = date('Y-m-d', strtotime($value->approve_date));
            $approveYear = date('Y', strtotime($value->approve_date));
            $approveMonth = date('m', strtotime($value->approve_date));
            // $dd = date('Y',strtotime('2021-09-30'));
            // $tgtg =date('m',strtotime('2021-09-30'));
            $currentYear = date('Y');
            $currentMonth = date('m');
            $emiAmount = $value->emi_amount;
            $diff = number_format((float) today()->diffInDays($date) / 7, 0, '.', '');
            for ($i = 0; $i < $value->emi_period; $i++) {
                $rr = date('Y-m-d', strtotime($date . ' + 7 days'));
                $date = date('Y-m-d', strtotime($date . ' + 7 days'));
                $emi_date[] = $date;
            }
            $endRecord = end(($emi_date));
            $checkData = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number) /*->where(\DB::raw('Date(created_at)'), '>', $endRecord)*/->where('is_deleted', 0)->get();
            $newDate = array();
            if (count($checkData) > 0) {
                foreach ($checkData as $index => $v) {
                    $newDate[] = date('Y-m-d', strtotime($v->created_at));
                }
            }
            $emi_date = array_merge($emi_date, $newDate);
            $narray = array();
            foreach ($emi_date as $KEY => $edate) {
                if (!in_array($edate, $narray)) {
                    $narray[str_replace('-', '', $edate)] = $edate;
                }
            }
            // if($value->emi_option  == 1)
            // {
            $a = ((($value->ROI) / 52.14) * $initOut) / 100;
            // }
            $emi_date = ($narray);
            asort($emi_date);
            // $startDate = $rr;
            // $newdate = $date; //date('Y-m-d');
            $emi_date = array_values($emi_date);
            $endRecord = end(($emi_date));
            $ad = date('Y-m-d', strtotime($value->approve_date . ' + 1 weeks'));
            for ($i = 0; $i < count($emi_date); $i++) {
                if ($ad == $emi_date[$i]) {
                    $da = $ad;
                } else {
                    $da = $emi_date[$i];
                }
                $monthCheck = date('m', strtotime($da));
                $yearCheck = date('Y', strtotime($da));
                $wmonthCheck = date('m', strtotime($da));
                $wyearCheck = date('Y', strtotime($da));
                $gdate = '';
                $exdate = '';
                $eDate = '';
                $newdailyInterest = 0;
                $differenceDAy = 0;
                //dd($emi_date[$i],$emi_date[$i+1]);
                $mout = 0;
                $penaltys = 0;
                if (strtotime($da) <= strtotime($endRecord)) {
                    if (strtotime($emi_date[$i]) < strtotime(date('Y-m-d'))) {
                        $abbb = $emi_date[$i];
                        if (in_array($da, $emi_date)) {
                            if ($i > 0) {
                                $gdate = $emi_date[$i - 1];
                                $exdate = $emi_date[$i - 1];
                            } else {
                                $gdate = $value->created_at;
                                $exdate = $value->created_at;
                            }
                            // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=', $emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $gdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                            // $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=',$emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $exdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                            $exists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $gdate)->where(\DB::raw('Date(created_at)'), '<=', $abbb)->where('is_deleted', 0)->exists();
                            $dexists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', $abbb)->where('is_deleted', 0)->exists();
                            $Countexists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $gdate)->where(\DB::raw('Date(created_at)'), '<=', $abbb)->where('is_deleted', 0)->sum('deposit');
                            $emiDetail = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $gdate)->where(\DB::raw('Date(created_at)'), '<=', $abbb)->where('is_deleted', 0)->get();
                            if ($exists == false) {
                                $EmiId = NULL;
                                $transDate = NULL;
                                $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', 3)->orderBy('id', 'desc')->exists();
                                $penaltys = 0;
                                $deposit = 0;
                                if ($checkout == false) {
                                    $mout = ($initOut + $a);
                                    $initOut = $mout;
                                    $ammountArray[] = $initOut;
                                    $interest = $a;
                                    $principalAmount = 0 - $a;
                                } else {
                                    $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', 3)->orderBy('id', 'desc')->first();
                                    if (isset($checkout->out_standing_amount)) {
                                        $newint = ((($value->ROI) / 52.14) * $checkout->out_standing_amount) / 100;
                                        $interest = $newint;
                                        $principalAmount = 0 - $newint;
                                        ;
                                        $mout = ($checkout->out_standing_amount - ($principalAmount));
                                    }
                                }
                                // if($mout >  0 )
                                // {
                                $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('loan_type', 3)->where('emi_date', $abbb)->exists();
                                if ($ddd == false) {
                                    $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $deposit, 'loan_type' => 3]);
                                }
                                // }
                            } else {
                                foreach ($emiDetail as $key => $emiSecond) {
                                    $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->where('is_deleted', 0)->exists();
                                    $penalty = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->where('is_deleted', 0)->first();
                                    $damount = $emiSecond->principal_amount;
                                    $deposit = $emiSecond->deposit;
                                    if (isset($penalty->penalty)) {
                                        $penaltys = $penalty->penalty;
                                    }
                                    $abbb = date('Y-m-d', strtotime($emiSecond->created_at));
                                    $emiActualDate = date('Y-m-d', strtotime($emiSecond->created_at));
                                    // echo($abbb)."<br/>";
                                    $transDate = $emiSecond->created_at;
                                    $EmiId = $emiSecond->id;
                                    $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', 3)->orderBy('id', 'desc')->first();
                                    if (isset($checkout->out_standing_amount)) {
                                        $checkDateMonth = date('m', strtotime($checkout->emi_date));
                                        $checkDateYear = date('Y', strtotime($checkout->emi_date));
                                        $newint = ((($value->ROI) / 52.14) * $checkout->out_standing_amount) / 100;
                                        $principalAmount = $deposit - $newint;
                                        $interest = $newint;
                                        // if (isset($penalty->principal_amount)) {
                                        //     $mout = ($checkout->out_standing_amount - $principalAmount - $penalty->principal_amount);
                                        // } else {
                                        $mout = ($checkout->out_standing_amount - $principalAmount);
                                        //}
                                        // if ($emiActualDate != $abbb) {
                                        //     $differenceDAy = \Carbon\Carbon::parse($abbb)->diffInDays(\Carbon\Carbon::parse($emiActualDate));
                                        //     $monthDetail = date('m', strtotime($abbb));
                                        //     $yearDetail = date('Y', strtotime($abbb));
                                        //     $monthDays =  cal_days_in_month(CAL_GREGORIAN, $monthDetail, $yearDetail);
                                        //     $dailyInterest = $interest / $monthDays;
                                        //     $newdailyInterest = number_format((float)$dailyInterest * $differenceDAy, 2, '.', '');
                                        //     $mout = $mout + $newdailyInterest;
                                        //     $principalAmount = $principalAmount - $newdailyInterest;
                                        // }
                                        // if($monthCheck == $checkDateMonth &&  $yearCheck == $checkDateYear)
                                        // {
                                        //     $newint =  0;
                                        // }
                                        // else{
                                        //     $newint = $interest;
                                        // }
                                    } else {
                                        $interest = $a;
                                        $principalAmount = $emiSecond->deposit - $interest;
                                        $mout = ($initOut - ($principalAmount));
                                        // if ($emiActualDate != $abbb) {
                                        //     $differenceDAy = \Carbon\Carbon::parse($abbb)->diffInDays(\Carbon\Carbon::parse($emiActualDate));
                                        //     $monthDetail = date('m', strtotime($abbb));
                                        //     $yearDetail = date('Y', strtotime($abbb));
                                        //     $monthDays =  cal_days_in_month(CAL_GREGORIAN, $monthDetail, $yearDetail);
                                        //     $dailyInterest = $interest / $monthDays;
                                        //     $newdailyInterest = number_format((float)$dailyInterest * $differenceDAy, 2, '.', '');
                                        //     $mout = $mout + $newdailyInterest;
                                        //     $principalAmount = $principalAmount - $newdailyInterest;
                                        // }
                                    }
                                    // if($mout >  0 )
                                    // {
                                    $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('loan_type', 3)->where('emi_id', $emiSecond->id)->where('emi_date', $abbb)->exists();
                                    if ($ddd == false) {
                                        $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $deposit, 'loan_type' => 3]);
                                    }
                                    // }
                                }
                            }
                        }
                    }
                }
                // else {
                //     $abbb = $emi_date[$i];
                //     // if(in_array($da, $emi_date)){
                //     //     if($i > 0)
                //     //     {
                //     //         $gdate = $emi_date[$i-1];
                //     //         $exdate =  $emi_date[$i-1];
                //     //     }
                //     //     else{
                //     //         $gdate = $value->created_at;
                //     //         $exdate = $value->created_at;
                //     //     }
                //     // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=', $emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $gdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                //     // $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=',$emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $exdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                //     $Emimonth = date('m', strtotime($abbb));
                //     $EmiYear = date('Y', strtotime($abbb));
                //     $newstartDate = $EmiYear . '-' . $Emimonth . '-01';
                //     $newendDate = $EmiYear . '-' . $Emimonth . '-31';
                //     $exists = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->whereBetween('created_at', [$newstartDate, $newendDate])->exists();
                //     $Countexists = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->WHERE('loan_id', $value->id)->whereBetween('created_at', [$newstartDate, $newendDate])->sum('deposit');
                //     $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->whereBetween('created_at', [$newstartDate, $newendDate])->exists();
                //     $penalty = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->whereBetween('created_at', [$newstartDate, $newendDate])->first();
                //     $emiDetail = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->whereBetween('created_at', [$newstartDate, $newendDate])->first();
                //     if ($exists == false) {
                //         $EmiId = NULL;
                //         $transDate = NULL;
                //         $checkout =     \App\Models\LoanEmisNew::where('loan_id', $value->id)->orderBy('id', 'desc')->exists();
                //         if ($checkout == false) {
                //             $mout = ($initOut + $a);
                //             $initOut = $mout;
                //             $ammountArray[] = $initOut;
                //             $interest = $a;
                //             $principalAmount = 0 - $a;;
                //         } else {
                //             $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->orderBy('id', 'desc')->first();
                //             if (isset($checkout->out_standing_amount)) {
                //                 $newint =  ((($value->ROI) / 12) * $checkout->out_standing_amount) / 100;
                //                 $interest = $newint;
                //                 $principalAmount = 0 - $interest;;
                //                 $mout = ($checkout->out_standing_amount + ($newint));
                //             }
                //         }
                //     } else {
                //         if (($Countexists)) {
                //             $damount = $Countexists;
                //         } else {
                //             $damount = $emiDetail->deposit;
                //         }
                //         $transDate = $abbb;
                //         $EmiId = $emiDetail->id;
                //         $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->orderBy('id', 'desc')->first();
                //         if (isset($checkout->out_standing_amount)) {
                //             $newint =  ((($value->ROI) / 12) * $checkout->out_standing_amount) / 100;
                //             $principalAmount = $damount - $newint;
                //             $interest = $newint;
                //             $penalty = 0;
                //             if (isset($penalty->principal_amount)) {
                //                 $mout = ($checkout->out_standing_amount - $principalAmount - $penalty->principal_amount);
                //                 $penalty = $penalty->principal_amount;
                //             } else {
                //                 $mout = ($checkout->out_standing_amount - $principalAmount);
                //             }
                //         } else {
                //             $interest = $a;
                //             $principalAmount = $damount - $interest;
                //             $mout = ($initOut - ($principalAmount));
                //         }
                //     }
                //     $CHECKr = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('emi_date', $abbb)->where('out_standing_amount', $mout)->where('roi_amount', $interest)->where('principal_amount', $principalAmount)->exists();
                //     if ($CHECKr == false) {
                //         $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->whereBetween('emi_date', [$newstartDate, $newendDate])->exists();
                //         $ddds = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->whereBetween('emi_date', [$newstartDate, $newendDate])->first();
                //         if ($ddd == False) {
                //             $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penalty]);
                //         }
                //     }
                //     print_r("jhkhkk");
                //     // }
                // }
                $ad = date('Y-m-d', strtotime($da . ' + 1 weeks'));
            }
            print_r('success');
        }
    }
    public function updateOutstandingGrploanDaily()
    {
        $data = Grouploans::where('emi_option', 3)->whereIn('status', [1, 4, 3]) /*->where('account_number',706171700011)*/->get();
        foreach ($data as $key => $value) {
            $emi_date = array();
            $initOut = $value->amount;
            // $date = date('Y-m-d', strtotime($value->created_at));
            // $datessss = date('Y', strtotime($value->created_at));
            // $datesssss = date('m', strtotime($value->created_at));
            $date = date('Y-m-d', strtotime($value->approve_date));
            $datessss = date('Y', strtotime($value->approve_date));
            $datesssss = date('m', strtotime($value->approve_date));
            // $dd = date('Y',strtotime('2021-09-30'));
            // $tgtg =date('m',strtotime('2021-09-30'));
            $dd = date('Y');
            $tgtg = date('m');
            $emiAmount = $value->emi_amount;
            $diff = number_format((float) today()->diffInDays($date), 0, '.', '');
            for ($i = 0; $i < $diff; $i++) {
                $rr = date('Y-m-d', strtotime($date . ' + 1 days'));
                $date = date('Y-m-d', strtotime($date . ' + 1 days'));
                $emi_date[] = $date;
            }
            $endRecord = end(($emi_date));
            $checkData = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $endRecord)->where('is_deleted', 0)->get();
            $newDate = array();
            if (count($checkData) > 0) {
                foreach ($checkData as $index => $v) {
                    $newDate[] = date('Y-m-d', strtotime($v->created_at));
                }
            }
            $emi_date = array_merge($emi_date, $newDate);
            $endRecord = end(($emi_date));
            $a = ((($value->ROI) / 365) * $initOut) / 100;
            $ad = date('Y-m-d', strtotime($value->approve_date . ' + 1 weeks'));
            for ($i = 0; $i < count($emi_date); $i++) {
                if ($ad == $emi_date[$i]) {
                    $da = $ad;
                } else {
                    $da = $emi_date[$i];
                }
                $monthCheck = date('m', strtotime($da));
                $yearCheck = date('Y', strtotime($da));
                $wmonthCheck = date('m', strtotime($da));
                $wyearCheck = date('Y', strtotime($da));
                $gdate = '';
                $exdate = '';
                $eDate = '';
                $newdailyInterest = 0;
                $differenceDAy = 0;
                $mout = 0;
                $penaltys = 0;
                if (strtotime($da) <= strtotime($endRecord)) {
                    if (strtotime($emi_date[$i]) < strtotime(date('Y-m-d'))) {
                        $abbb = $emi_date[$i];
                        if (in_array($da, $emi_date)) {
                            if ($i > 0) {
                                $gdate = $emi_date[$i - 1];
                                $exdate = $emi_date[$i - 1];
                            } else {
                                $gdate = $value->created_at;
                                $exdate = $value->created_at;
                            }
                            // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=', $emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $gdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                            // $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=',$emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $exdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                            $exists = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', $abbb)->where('is_deleted', 0)->exists();
                            $dexists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', $abbb)->where('is_deleted', 0)->exists();
                            $Countexists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', $abbb)->where('is_deleted', 0)->sum('deposit');
                            $emiDetail = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', $abbb)->where('is_deleted', 0)->get();
                            if ($exists == false) {
                                $EmiId = NULL;
                                $transDate = NULL;
                                $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->orderBy('id', 'desc')->exists();
                                $penaltys = 0;
                                $deposit = 0;
                                if ($checkout == false) {
                                    $mout = ($initOut + $a);
                                    $initOut = $mout;
                                    $ammountArray[] = $initOut;
                                    $interest = $a;
                                    $principalAmount = 0 - $a;
                                    ;
                                } else {
                                    $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->orderBy('id', 'desc')->first();
                                    if (isset($checkout->out_standing_amount)) {
                                        $checkDateMonth = date('m', strtotime($checkout->emi_date));
                                        $checkDateYear = date('Y', strtotime($checkout->emi_date));
                                        if ($monthCheck == $checkDateMonth && $yearCheck == $checkDateYear) {
                                            $newint = 0;
                                        } else {
                                            $newint = ((($value->ROI) / 365) * $checkout->out_standing_amount) / 100;
                                        }
                                        $interest = $newint;
                                        $principalAmount = 0 - $interest;
                                        ;
                                        echo $newint . "<br/>";
                                        $mout = ($checkout->out_standing_amount + ($newint));
                                    }
                                }
                                if ($mout > 0) {
                                    $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('loan_type', 3)->where('emi_date', $abbb)->exists();
                                    if ($ddd == false) {
                                        $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $deposit, 'loan_type' => 3]);
                                    }
                                }
                            } else {
                                foreach ($emiDetail as $key => $emiSecond) {
                                    $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->where('is_deleted', 0)->exists();
                                    $penalty = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->where('is_deleted', 0)->first();
                                    $damount = $emiSecond->principal_amount;
                                    $deposit = $emiSecond->deposit;
                                    if (isset($penalty->penalty)) {
                                        $penaltys = $penalty->penalty;
                                    }
                                    $abbb = date('Y-m-d', strtotime($emiSecond->created_at));
                                    $emiActualDate = date('Y-m-d', strtotime($emiSecond->created_at));
                                    // echo($abbb)."<br/>";
                                    $transDate = $emiSecond->created_at;
                                    $EmiId = $emiSecond->id;
                                    $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->orderBy('id', 'desc')->first();
                                    if (isset($checkout->out_standing_amount)) {
                                        $checkDateMonth = date('m', strtotime($checkout->emi_date));
                                        $checkDateYear = date('Y', strtotime($checkout->emi_date));
                                        $newint = ((($value->ROI) / 52.14) * $checkout->out_standing_amount) / 100;
                                        $principalAmount = $deposit - $newint;
                                        $interest = $newint;
                                        // if (isset($penalty->principal_amount)) {
                                        //     $mout = ($checkout->out_standing_amount - $principalAmount - $penalty->principal_amount);
                                        // } else {
                                        $mout = ($checkout->out_standing_amount - $principalAmount);
                                        // }
                                        // if ($emiActualDate != $abbb) {
                                        //     $differenceDAy = \Carbon\Carbon::parse($abbb)->diffInDays(\Carbon\Carbon::parse($emiActualDate));
                                        //     $monthDetail = date('m', strtotime($abbb));
                                        //     $yearDetail = date('Y', strtotime($abbb));
                                        //     $monthDays =  cal_days_in_month(CAL_GREGORIAN, $monthDetail, $yearDetail);
                                        //     $dailyInterest = $interest / $monthDays;
                                        //     $newdailyInterest = number_format((float)$dailyInterest * $differenceDAy, 2, '.', '');
                                        //     $mout = $mout + $newdailyInterest;
                                        //     $principalAmount = $principalAmount - $newdailyInterest;
                                        // }
                                        // if($monthCheck == $checkDateMonth &&  $yearCheck == $checkDateYear)
                                        // {
                                        //     $newint =  0;
                                        // }
                                        // else{
                                        //     $newint = $interest;
                                        // }
                                    } else {
                                        $interest = $a;
                                        $principalAmount = $emiDetail->deposit - $interest;
                                        $mout = ($initOut - ($principalAmount));
                                    }
                                    if ($mout > 0) {
                                        $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('loan_type', 3)->where('emi_id', $emiSecond->id)->where('emi_date', $abbb)->exists();
                                        if ($ddd == false) {
                                            $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $deposit, 'loan_type' => 3]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                // else {
                //     $abbb = $emi_date[$i];
                //     // if(in_array($da, $emi_date)){
                //     //     if($i > 0)
                //     //     {
                //     //         $gdate = $emi_date[$i-1];
                //     //         $exdate =  $emi_date[$i-1];
                //     //     }
                //     //     else{
                //     //         $gdate = $value->created_at;
                //     //         $exdate = $value->created_at;
                //     //     }
                //     // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=', $emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $gdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                //     // $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=',$emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $exdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                //     $Emimonth = date('m', strtotime($abbb));
                //     $EmiYear = date('Y', strtotime($abbb));
                //     $newstartDate = $EmiYear . '-' . $Emimonth . '-01';
                //     $newendDate = $EmiYear . '-' . $Emimonth . '-31';
                //     $exists = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->whereBetween('created_at', [$newstartDate, $newendDate])->exists();
                //     $Countexists = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->WHERE('loan_id', $value->id)->whereBetween('created_at', [$newstartDate, $newendDate])->sum('deposit');
                //     $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->whereBetween('created_at', [$newstartDate, $newendDate])->exists();
                //     $penalty = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->whereBetween('created_at', [$newstartDate, $newendDate])->first();
                //     $emiDetail = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->whereBetween('created_at', [$newstartDate, $newendDate])->first();
                //     if ($exists == false) {
                //         $EmiId = NULL;
                //         $transDate = NULL;
                //         $checkout =     \App\Models\LoanEmisNew::where('loan_id', $value->id)->orderBy('id', 'desc')->exists();
                //         if ($checkout == false) {
                //             $mout = ($initOut + $a);
                //             $initOut = $mout;
                //             $ammountArray[] = $initOut;
                //             $interest = $a;
                //             $principalAmount = 0 - $a;;
                //         } else {
                //             $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->orderBy('id', 'desc')->first();
                //             if (isset($checkout->out_standing_amount)) {
                //                 $newint =  ((($value->ROI) / 12) * $checkout->out_standing_amount) / 100;
                //                 $interest = $newint;
                //                 $principalAmount = 0 - $interest;;
                //                 $mout = ($checkout->out_standing_amount + ($newint));
                //             }
                //         }
                //     } else {
                //         if (($Countexists)) {
                //             $damount = $Countexists;
                //         } else {
                //             $damount = $emiDetail->deposit;
                //         }
                //         $transDate = $abbb;
                //         $EmiId = $emiDetail->id;
                //         $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->orderBy('id', 'desc')->first();
                //         if (isset($checkout->out_standing_amount)) {
                //             $newint =  ((($value->ROI) / 12) * $checkout->out_standing_amount) / 100;
                //             $principalAmount = $damount - $newint;
                //             $interest = $newint;
                //             $penalty = 0;
                //             if (isset($penalty->principal_amount)) {
                //                 $mout = ($checkout->out_standing_amount - $principalAmount - $penalty->principal_amount);
                //                 $penalty = $penalty->principal_amount;
                //             } else {
                //                 $mout = ($checkout->out_standing_amount - $principalAmount);
                //             }
                //         } else {
                //             $interest = $a;
                //             $principalAmount = $damount - $interest;
                //             $mout = ($initOut - ($principalAmount));
                //         }
                //     }
                //     $CHECKr = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('emi_date', $abbb)->where('out_standing_amount', $mout)->where('roi_amount', $interest)->where('principal_amount', $principalAmount)->exists();
                //     if ($CHECKr == false) {
                //         $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->whereBetween('emi_date', [$newstartDate, $newendDate])->exists();
                //         $ddds = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->whereBetween('emi_date', [$newstartDate, $newendDate])->first();
                //         if ($ddd == False) {
                //             $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penalty]);
                //         }
                //     }
                //     print_r("jhkhkk");
                //     // }
                // }
                $ad = date('Y-m-d', strtotime($da . ' + 1 weeks'));
            }
            print_r('success');
        }
    }
    public function updateloanAmountOfinsuranmce()
    {
        $branch_id = 31;
        $records = AllHeadTransaction::where('branch_id', $branch_id)->where('type', 5)->where('sub_type', 54)->where('head_id', 70)->get();
        foreach ($records as $data) {
            $existRecord = Memberloans::where('id', $data->type_id)->first();
            $loanAmount = AllHeadTransaction::where('type', 5)->where('sub_type', 54)->whereIn('head_id', [70])->where('type_id', $data->type_id)->first();
            print_r('success');
            if ($existRecord->amount != $loanAmount->amount) {
                $loanAmount->update(['amount' => $existRecord->amount, 'opening_balance' => $existRecord->amount, 'closing_balance' => $existRecord->amount]);
                ;
                print_r('success');
            }
            print_r('success');
        }
    }
    public function updateInterestOutstanding()
    {
        $data = Memberloans::where('loan_type', 1)->where('emi_option', 1)->whereIn('status', [1, 4, 3])->whereIn('account_number', ['596571500006'])->whereNotNull('approve_date')->get();
        foreach ($data as $key => $value) {
            $emi_date = array();
            $initOut = $value->amount;
            $date = date('Y-m-d', strtotime($value->approve_date));
            $datessss = date('Y', strtotime($value->approve_date));
            $datesssss = date('m', strtotime($value->approve_date));
            $dd = date('Y');
            $tgtg = date('m');
            $deposit = 0;
            $emiAmount = $value->emi_amount;
            $diff = (($dd - $datessss) * 12) + ($tgtg - $datesssss);
            for ($i = 0; $i < $diff; $i++) {
                $rr = date('Y-m-d', strtotime($date . ' + 1 months'));
                $date = date('Y-m-d', strtotime($date . ' + 1 months'));
                $emi_date[] = $date;
            }
            $endRecord = end(($emi_date));
            $checkData = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number) /*->where(\DB::raw('Date(created_at)'),'>',$endRecord)*/->get();
            $newDate = array();
            if (count($checkData) > 0) {
                foreach ($checkData as $index => $v) {
                    $newDate[] = date('Y-m-d', strtotime($v->created_at));
                }
            }
            $emi_date = array_merge($emi_date, $newDate);
            $narray = array();
            foreach ($emi_date as $KEY => $edate) {
                if (!in_array($edate, $narray)) {
                    $narray[str_replace('-', '', $edate)] = $edate;
                }
            }
            // if($value->emi_option  == 1)
            // {
            $a = ((($value->ROI) / 12) * $initOut) / 100;
            // }
            $emi_date = ($narray);
            asort($emi_date);
            // $startDate = $rr;
            // $newdate = $date; //date('Y-m-d');
            $emi_date = array_values($emi_date);
            $endRecord = end(($emi_date));
            $ad = date('Y-m-d', strtotime($value->approve_date . ' + 1 months'));
            for ($i = 0; $i < count($emi_date); $i++) {
                $da = $emi_date[$i];
                $deposit = 0;
                $monthCheck = date('m', strtotime($da));
                $yearCheck = date('Y', strtotime($da));
                $wmonthCheck = date('m', strtotime($da));
                $wyearCheck = date('Y', strtotime($da));
                $gdate = '';
                $exdate = '';
                $eDate = '';
                //dd($emi_date[$i],$emi_date[$i+1]);
                $damount = 0;
                $mout = 0;
                $penaltys = 0;
                if (strtotime($da) <= strtotime($endRecord)) {
                    if (strtotime($emi_date[$i]) <= strtotime(date('Y-m-d'))) {
                        $abbb = $emi_date[$i];
                        if (in_array($da, $emi_date)) {
                            if ($i > 0) {
                                $gdate = $emi_date[$i - 1];
                                $exdate = $emi_date[$i - 1];
                            } else {
                                $gdate = $value->created_at;
                                $exdate = $value->created_at;
                            }
                            // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=', $emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $gdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                            // $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=',$emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $exdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                            $exists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $gdate)->where(\DB::raw('Date(created_at)'), '<=', $abbb)->exists();
                            $Countexists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $gdate)->where(\DB::raw('Date(created_at)'), '<=', $abbb)->sum('deposit');
                            $emiDetail = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '>', $gdate)->where(\DB::raw('Date(created_at)'), '<=', $abbb)->get();
                            if ($exists == false) {
                                $EmiId = NULL;
                                $transDate = NULL;
                                $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->exists();
                                if ($checkout == false) {
                                    $mout = ($initOut + $a);
                                    $initOut = $mout;
                                    $ammountArray[] = $initOut;
                                    $interest = $a;
                                    $principalAmount = 0 - $a;
                                    ;
                                } else {
                                    $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->first();
                                    if (isset($checkout->out_standing_amount)) {
                                        $newint = ((($value->ROI) / 12) * $checkout->out_standing_amount) / 100;
                                        $interest = $newint;
                                        $principalAmount = 0 - $interest;
                                        ;
                                        $mout = ($checkout->out_standing_amount + ($newint));
                                    }
                                }
                                if ($mout > 0) {
                                    $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->exists();
                                    if ($ddd == false) {
                                        $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $damount, 'loan_type' => $value->loan_type]);
                                    }
                                }
                            } else {
                                foreach ($emiDetail as $key => $emiSecond) {
                                    $damount = $emiSecond->principal_amount;
                                    $deposit = $emiSecond->deposit;
                                    $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->exists();
                                    $penalty = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(created_at)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->sum('principal_amount');
                                    $abbb = date('Y-m-d', strtotime($emiSecond->created_at));
                                    // echo($abbb)."<br/>";
                                    $transDate = $emiSecond->created_at;
                                    $EmiId = $emiSecond->id;
                                    $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->orderBy('id', 'desc')->first();
                                    if (isset($checkout->out_standing_amount)) {
                                        $checkDateMonth = date('m', strtotime($checkout->emi_date));
                                        $checkDateYear = date('Y', strtotime($checkout->emi_date));
                                        $monthDetail = date('m', strtotime($transDate));
                                        $yearDetail = date('Y', strtotime($transDate));
                                        $newint = ((($value->ROI) / 12) * $checkout->out_standing_amount) / 100;
                                        $interest = $newint;
                                        // if(($penalty) > 0)
                                        // {
                                        // $mout = ($checkout->out_standing_amount - $principalAmount - $penalty);
                                        // }
                                        // else{
                                        $lastoutDate = date('Y-m-d', strtotime($checkout->emi_date));
                                        $totalDayInterest = Carbon::parse($lastoutDate)->diffInDays(Carbon::parse($transDate));
                                        //$interest = $checkout->roi_amount;
                                        $roids = ($value->ROI / 100);
                                        $perDayInterest = ($deposit * $roids * ($totalDayInterest / 365));
                                        $totalDailyInterest = $perDayInterest;
                                        if ($monthDetail == $checkDateMonth && $yearDetail == $checkDateYear) {
                                            $interest = 0;
                                            $interest = $interest + $totalDailyInterest;
                                        } else {
                                            $interest = $interest;
                                        }
                                        $principal_amount = $deposit - $interest;
                                        $mout = ($checkout->out_standing_amount - $principal_amount);
                                        $principalAmount = $principal_amount;
                                    } else {
                                        $interest = $a;
                                        $principalAmount = $deposit - $interest;
                                        $mout = ($initOut - ($principalAmount));
                                    }
                                    if ($mout > 0) {
                                        $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->WHERE('emi_id', $emiSecond->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->exists();
                                        if ($ddd == false) {
                                            $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $deposit, 'loan_type' => $value->loan_type]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                // else{
                //     $abbb = $emi_date[$i];
                // if(in_array($da, $emi_date)){
                //     if($i > 0)
                //     {
                //         $gdate = $emi_date[$i-1];
                //         $exdate =  $emi_date[$i-1];
                //     }
                //     else{
                //         $gdate = $value->created_at;
                //         $exdate = $value->created_at;
                //     }
                // $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=', $emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $gdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();
                // $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'<=',$emi_date[$i])->where(\DB::raw('Date(created_at)') ,'>=', $exdate)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();
                //         $Emimonth = date('m',strtotime($abbb));
                //         $EmiYear= date('Y',strtotime($abbb));
                //         $newstartDate = $EmiYear.'-'.$Emimonth.'-01';
                //         $newendDate = $EmiYear.'-'.$Emimonth.'-31';
                //         $exists = \App\Models\LoanDayBooks::where('loan_sub_type',0)->whereBetween('created_at',[$newstartDate,$newendDate])->exists();
                //         $Countexists = \App\Models\LoanDayBooks::where('loan_sub_type',0)->WHERE('loan_id',$value->id)->whereBetween('created_at',[$newstartDate,$newendDate])->sum('deposit');
                //         $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type',1)->where('account_number',$value->account_number)->whereBetween('created_at',[$newstartDate,$newendDate])->exists();
                //         $penalty = \App\Models\LoanDayBooks::where('loan_sub_type',1)->where('account_number',$value->account_number)->whereBetween('created_at',[$newstartDate,$newendDate])->first();
                //         $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->whereBetween('created_at',[$newstartDate,$newendDate])->first();
                //         if($exists == false){
                //             $EmiId = NULL;
                //             $transDate = NULL;
                //             $checkout =     \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->exists();
                //             if($checkout == false){
                //                 $mout = ($initOut + $a);
                //                 $initOut = $mout;
                //                 $ammountArray[]= $initOut;
                //                 $interest = $a;
                //                 $principalAmount =0 - $a;;
                //             }
                //             else{
                //                 $checkout = \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->first();
                //                     if(isset($checkout->out_standing_amount)){
                //                         $newint =  ((($value->ROI)/12) * $checkout->out_standing_amount)/100;
                //                         $interest = $newint;
                //                         $principalAmount =0 - $interest;;
                //                         $mout = ($checkout->out_standing_amount + ($newint));
                //                     }
                //                 }
                //         }else{
                //             if(($Countexists))
                //             {
                //                     $damount = $Countexists;
                //             }
                //             else{
                //                 $damount =$emiDetail->deposit;
                //             }
                //             $transDate =$abbb;
                //             $EmiId = $emiDetail->id;
                //             $checkout = \App\Models\LoanEmisNew::where('loan_id',$value->id)->orderBy('id','desc')->first();
                //             if(isset($checkout->out_standing_amount))
                //             {
                //                 $newint =  ((($value->ROI)/12) * $checkout->out_standing_amount)/100;
                //                 $principalAmount = $damount - $newint;
                //                 $interest = $newint;
                //                 $penalty = 0;
                //                 if(isset($penalty->principal_amount))
                //                 {
                //                    $mout = ($checkout->out_standing_amount - $principalAmount - $penalty->principal_amount);
                //                    $penalty = $penalty->principal_amount;
                //                 }
                //                 else{
                //                    $mout = ($checkout->out_standing_amount - $principalAmount);
                //                 }
                //             }
                //             else{
                //                 $interest = $a;
                //                 $principalAmount = $damount - $interest;
                //                 $mout = ($initOut - ($principalAmount ));
                //             }
                //         }
                //         $CHECKr = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->where('emi_date',$abbb)->where('out_standing_amount',$mout)->where('roi_amount',$interest)->where('principal_amount',$principalAmount)->exists();
                //         if($mout >  0 )
                //         {
                //             $ddd = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->where('emi_date',$abbb)->exists();
                //             if($ddd == false)
                //             {
                //                 $createRecord = \App\Models\LoanEmisNew::create(['loan_id'=>$value->id,'out_standing_amount'=>$mout,'emi_date'=>$abbb,'roi_amount'=>$interest,'principal_amount'=>$principalAmount,'emi_received_date'=>$transDate,'emi_id'=>$EmiId,'penalty'=>$penaltys,'emi_option'=>$value->emi_option,'deposit'=>$damount]);
                //             }
                //         }
                //         print_r("jhkhkk" );
                //     // }
                // }
                $ad = date('Y-m-d', strtotime($da . ' + 1 months'));
            }
            print_r('success');
        }
    }
    public function updateDaybookDAte()
    {
        $data = Daybook::where('transaction_type', 4)->where(\DB::raw('DATE(created_at)'), '2022-05-30')->orderBy('created_at', 'desc')->get();
        foreach ($data as $d) {
            $a = BranchDaybook::where('type', 3)->where('sub_type', 32)->where('type_transaction_id', $d->id)->first();
            Daybook::where('id', $d->id)->update(['created_at_default' => $a->created_at_default]);
            print_r('success');
        }
    }
    /**
     * Soft delete of investment stationary charges in all Table
     * @param accountNumber
     * @table AllHeadTransaction,BranchDaybook,MemberTransactions
     */
    public function deleteStationaryCharges()
    {
        $accountNumber = MemberInvestments::whereIn('account_number', ['322270300019', '322270300020', '103070300036', '597270300068', '597270300069', '597570300005', '101970300046', '101970300047', '101170300370', '321970300123', '706270300123', '102470300089', '102470300090', '706170300415'])->get();
        foreach ($accountNumber as $value) {
            $branchdaybookRecord = BranchDaybook::where('type', 3)->where('sub_type', 35)->where('type_id', $value->id)->first();
            $getAllHeadTransaction = AllHeadTransaction::where('daybook_ref_id', $branchdaybookRecord->daybook_ref_id)->where('type', 3)->where('sub_type', 35)->where('type_id', $value->id)->pluck('id')->toArray();
            $getMemberTransaction = MemberTransaction::where('daybook_ref_id', $branchdaybookRecord->daybook_ref_id)->where('type', 3)->where('sub_type', 35)->where('type_id', $value->id)->first();
            $getBookRecord = Daybook::where('transaction_type', 19)->where('investment_id', $value->id)->where('deposit', 50)->first();
            $branchdaybookRecord->update(['is_deleted' => 1]);
            $updateAllHJead = AllHeadTransaction::whereIn('id', $getAllHeadTransaction)->update(['is_deleted' => 1]);
            $getMemberTransaction->update(['is_deleted' => 1]);
            $getBookRecord->update(['is_deleted' => 1]);
        }
        print_r('success');
    }
    public function updateInsurance()
    {
        $data = \App\Models\Daybook::where('transaction_type', 24)->get();
        foreach ($data as $key => $value) {
            $record = Memberloans::where('account_number', $value->account_no)->first();
            $grecord = Grouploans::where('account_number', $value->account_no)->first();
            if (!empty($record)) {
                $record->update(['insurance_charge' => $value->deposit]);
                print_r('success');
            }
            if (!empty($grecord)) {
                $grecord->update(['insurance_charge' => $value->deposit]);
                print_r('success');
            }
        }
    }
    public function mismatchrecord()
    {
        $startdate = new Carbon('2022-12-31');
        $enddate = new Carbon('2023-01-12');
        $dataCR = \App\Models\AllHeadTransaction::where('head_id', 70)->where('payment_type', 'CR')->whereBetween('entry_date', [$startdate, $enddate])->where('is_deleted', 0)->get();
        foreach ($dataCR as $value) {
            $exist = SamraddhBankDaybook::where('daybook_ref_id', $value->daybook_ref_id)->exists();
            if (!$exist) {
                $bank = [
                    'daybook_ref_id' => $value->daybook_ref_id,
                    'branch_id' => $value->branch_id,
                    'bank_id' => 2,
                    'associate_id' => $value->associate_id,
                    'type' => 29,
                    'sub_type' => 292,
                    'type_id' => $value->type_id,
                    'type_transaction_id' => $value->type_transaction_id,
                    'member_id' => $value->member_id,
                    'opening_balance' => $value->amount,
                    'amount' => $value->amount,
                    'closing_balance' => $value->amount,
                    'description' => $value->description,
                    'description_dr' => $value->description,
                    'description_cr' => $value->description,
                    'payment_type' => 'DR',
                    'payment_mode' => 6,
                    'entry_date' => $value->entry_date,
                    'entry_time' => $value->entry_time,
                ];
                SamraddhBankDaybook::create($bank);
                echo $value->daybook_ref_id . "<br>";
            }
        }
    }
    public function cash_balance_update()
    {
        if (check_my_permission(Auth::user()->id, "276") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Update cash Balance';
        $data['banks'] = \App\Models\Branch::get();
        return view('templates.admin.update_balance', $data);
    }
    public function bank_balance_update()
    {
        if (check_my_permission(Auth::user()->id, "275") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Update Bank Balance';
        $data['banks'] = \App\Models\SamraddhBank::get();
        return view('templates.admin.update_balance', $data);
    }
    public function updateDaybookloan()
    {
        $data = Daybook::where('description', 'like', '%Loan File Charge  IGST  Charges%')->get();
        foreach ($data as $value) {
            $value->update(['loan_id' => $value->investment_id, 'investment_id' => NULL]);
        }
        print_r('success');
    }
    public function updatebankIdloan()
    {
        $SmData = SamraddhBankDaybook::where('type', 5)->where('sub_type', 52)->whereNull('bank_id')->whereNull('account_id')->get();
        foreach ($SmData as $val) {
            $val->update(['bank_id' => $val->amount_to_id, 'account_id' => $val->amount_to_id]);
            echo 'success';
        }
        echo '</br>';
    }
    public function getMismAtchDate()
    {
        $startdate = new Carbon('2022-12-31');
        $enddate = new Carbon('2023-01-12');
        $branch = 1;
        for ($i = $startdate; $i <= $enddate; $i->modify('+1 day')) {
            $dataCR = \App\Models\AllHeadTransaction::where('branch_id', $branch)->where('payment_type', 'CR')->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            $dataDR = \App\Models\AllHeadTransaction::where('branch_id', $branch)->where('payment_type', 'DR')->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            if ($dataCR != $dataDR) {
                echo "<table border='1'>";
                echo "<tr>
                            <th>date</th>
                            </tr>";
                echo "<tr><td>" . $i->format('Y-m-d') . "</td>
                        </tr>";
                echo "</table>";
            }
        }
        '<br/>';
    }
    public function maturityRecords()
    {
        $startdate = new Carbon('2021-04-01');
        $enddate = new Carbon('2022-03-31');
        $branch = 1;
        for ($i = $startdate; $i <= $enddate; $i->modify('+1 day')) {
            $records = \App\Models\AllHeadTransaction::where('branch_id', $branch)->where('type', 13)->where('entry_date', $i->format('Y-m-d'))->get();
            foreach ($records as $value) {
                $dataCR = \App\Models\AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->where('payment_type', 'CR')->where('is_deleted', 0)->sum('amount');
                $dataDR = \App\Models\AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->where('payment_type', 'DR')->where('is_deleted', 0)->sum('amount');
                if ($dataCR != $dataDR) {
                    echo "<table border='1'>";
                    echo "<tr>
                                    <th>date</th>
                                    <th>SubType</th>
                                    <th>DayBookRefId</th>
                                    </tr>";
                    echo "<tr><td>" . $i->format('Y-m-d') . "</td>
                                    <td>" . $i->format('Y-m-d') . "</td>
                                    <td>" . $value->daybook_ref_id . "</td>
                                </tr>";
                    echo "</table>";
                }
            }
        }
        '<br/>';
    }
    public function getMismAtchDate2()
    {
        $startdate = new Carbon('2022-12-31');
        $enddate = new Carbon('2023-03-12');
        $branch = 9;
        for ($i = $startdate; $i <= $enddate; $i->modify('+1 day')) {
            $dataCR = \App\Models\AllHeadTransaction::where('head_id', 28)->where('branch_id', $branch)->where('payment_type', 'CR')->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            $dataDR = \App\Models\AllHeadTransaction::where('head_id', 28)->where('branch_id', $branch)->where('payment_type', 'DR')->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            $dataCR2 = \App\Models\BranchDaybook::where('payment_mode', 0)->where('description_dr', 'not like', '%Eli Amount%')->where('branch_id', $branch)->where('payment_type', 'CR')->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            $dataDR2 = \App\Models\BranchDaybook::where('payment_mode', 0)->where('description_dr', 'not like', '%Eli Amount%')->where('branch_id', $branch)->where('payment_type', 'DR')->where('entry_date', $i->format('Y-m-d'))->where('is_deleted', 0)->sum('amount');
            $dataCRff = "Branch CR";
            $dataDRff = "Branch DR";
            if ($dataCR != $dataDR2) {
                echo "<table border='1'>";
                echo "<tr>
                            <th>date</th>
                            </tr>";
                echo "<tr><td>" . $i->format('Y-m-d') . "</td>
                            <td>" . $dataCRff . "</td>
                        </tr>";
                echo "</table>";
            }
            '<br/>';
            if ($dataDR != $dataCR2) {
                echo "<table border='1'>";
                echo "<tr>
                            <th>date</th>
                            <th>type</th>
                            </tr>";
                echo "<tr><td>" . $i->format('Y-m-d') . "</td>
                            <td>" . $dataDRff . "</td>
                        </tr>";
                echo "</table>";
            }
        }
    }
    // public function update_emi_transaction_date2()
    // {
    //     $branchId =6;
    //     $entryDate ='2021-09-04';
    //     $laonId = 341;
    //     $records= \App\Models\LoanDayBooks::where('branch_id',$branchId)->where('loan_sub_type',0)->where('loan_id',$laonId)->where('is_deleted',0)->where(\DB::raw('DATE(created_at)'),$entryDate)->get();
    //     foreach ($records as $key => $value) {
    //     $data = \App\Models\AllHeadTransaction::where('type',5)->whereIn('sub_type',[52,523])->where('type_transaction_id',$value->day_book_id)->get();
    //     foreach($data as $v)
    //     {
    //         $ndate = date('Y-m-d',strtotime($value->created_at));
    //         $date =$v->update(['entry_date'=>$ndate]);
    //     }
    //        print_r('done');
    //     }
    // }
    public function update_emi_transaction_date2()
    {
        $branchId = 6;
        $entryDate = '2021-09-28';
        $laonId = 341;
        $records = \App\Models\AllHeadTransaction::where('branch_id', $branchId)->where('type', 5)->whereIn('sub_type', [52])->where('type_id', $laonId)->where('is_deleted', 0)->where(\DB::raw('DATE(entry_date)'), $entryDate)->get();
        foreach ($records as $key => $value) {
            $recordssss = \App\Models\LoanDayBooks::where('branch_id', $branchId)->where('loan_sub_type', 0)->where('loan_id', $laonId)->where('payment_type', 0)->where('day_book_id', $value->type_transaction_id)->where('is_deleted', 0)->first();
            if (isset($recordssss->day_book_id)) {
                $data = \App\Models\AllHeadTransaction::where('type', 5)->whereIn('sub_type', [52, 523])->where('type_transaction_id', $recordssss->day_book_id)->get();
                foreach ($data as $v) {
                    $ndate = date('Y-m-d', strtotime($recordssss->created_at));
                    $date = $v->update(['entry_date' => $ndate]);
                }
            } else {
                $data = \App\Models\AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 1]);
            }
            print_r('done');
        }
    }
    public function update_emi_transaction_date4()
    {
        $branchId = 4;
        $laonId = 86;
        $records = \App\Models\AllHeadTransaction::where('branch_id', $branchId)->where('head_id', 28)->where('type', 5)->whereIn('sub_type', [55])->where('type_id', $laonId)->where('is_deleted', 0)->get();
        foreach ($records as $key => $value) {
            $recordssss = \App\Models\LoanDayBooks::where('daybook_ref_id', $value->daybook_ref_id)->where('is_deleted', 0)->first();
            if (isset($recordssss->day_book_id)) {
                $data = \App\Models\AllHeadTransaction::where('daybook_ref_id', $recordssss->daybook_ref_id)->get();
                foreach ($data as $v) {
                    $ndate = date('Y-m-d', strtotime($recordssss->created_at));
                    $date = $v->update(['entry_date' => $ndate, 'is_deleted' => 0]);
                }
            } else {
                $data = \App\Models\AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 1]);
                $data2 = \App\Models\BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 1]);
                $data3 = \App\Models\MemberTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 1]);
            }
            echo $value->daybook_ref_id . "</br>";
        }
    }
    public function update_emi_transaction_date3()
    {
        $branchId = 4;
        $laonId = 62;
        $records = \App\Models\LoanDayBooks::where('branch_id', $branchId)->where('loan_id', $laonId)->where('payment_type', 0)->where('is_deleted', 0)->get();
        foreach ($records as $key => $value) {
            $recordssss = \App\Models\LoanDayBooks::where('branch_id', $branchId)->where('loan_sub_type', 0)->where('loan_id', $laonId)->where('payment_type', 0)->where('day_book_id', $value->type_transaction_id)->where('is_deleted', 0)->first();
            if (isset($recordssss->daybook_ref_id)) {
                $data = \App\Models\AllHeadTransaction::where('type', 5)->whereIn('sub_type', [55, 523])->where('type_transaction_id', $recordssss->day_book_id)->get();
                $data2 = \App\Models\BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 0]);
                $data3 = \App\Models\MemberTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 0]);
                foreach ($data as $v) {
                    $ndate = date('Y-m-d', strtotime($recordssss->created_at));
                    $date = $v->update(['entry_date' => $ndate]);
                }
            } else {
                $data = \App\Models\AllHeadTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 1]);
                $data2 = \App\Models\BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 1]);
                $data3 = \App\Models\MemberTransaction::where('daybook_ref_id', $value->daybook_ref_id)->update(['is_deleted' => 1]);
            }
            echo $value->daybook_ref_id . "</br>";
        }
    }
    public function tdsData()
    {
        $branchId = 1;
        $laonId = 100;
        $records = \App\Models\AllHeadTransaction::where('branch_id', $branchId)->where('head_id', 63)->where('type', 9)->whereIn('sub_type', [90])->where('is_deleted', 0)->where('entry_date', '>', '2021-04-31')->get();
        foreach ($records as $key => $value) {
            $records = \App\Models\AllHeadTransaction::where('branch_id', $branchId)->where('daybook_ref_id', $value->daybook_ref_id)->where('payment_type', 'CR')->sum('amount');
            $records2 = \App\Models\AllHeadTransaction::where('branch_id', $branchId)->where('daybook_ref_id', $value->daybook_ref_id)->where('payment_type', 'DR')->sum('amount');
            if ($records != $records2) {
                echo $value->daybook_ref_id . "</br>";
            }
        }
    }
    /**
     * Query for change memver investment date respective to investment opening date
     */
    public function updateInvestmentDate()
    {
        $array = array('101170400001', '101170400002', '101170400003', '101170400004', '101170400005', '101171000004', '101171000163', '101170400055', '101170400105', '101170500038', '101170500039', '101170500040', '101170700012', '101171000484', '101171000536', '101171000725', '101171000726', '101171000849', '101171000850', '101171000851', '101171000852', '101171000853', '101171000876', '101171000900', '101370600001', '101371000134', '101371000151', '101371000156', '101371000342', '101371000343', '101371800002', '101570400007', '101570400008', '101570400015', '101570400016', '101570500005', '101571000058', '101571000060', '101670800034', '101670800035', '101670800036', '101671000137', '101871000024', '101871000027', '101871000093', '102070400012', '102070400015', '102070400016', '102070700067', '102070700068', '102071000018', '102071000148', '102071000218', '102071000263', '102071000319', '102071000320', '102071000328', '102071000329', '102071000330', '102071000331', '102071000332', '102071000333', '102071000336', '102071000343', '102271000124', '102271000125', '102271000126', '102271000127', '102271000128', '102271000129', '102271000130', '102271000131', '102271000132', '102370400006', '102370900002', '102371000013', '102371000014', '102371000084', '102470700009', '103070700015', '103071000004', '103171000011', '103171000035', '103171000036', '103171000037', '103171000038', '103171000039', '103171000040', '103271000003', '103271000004', '103271000007', '103271000008', '321170400026', '321170700018', '321170700019', '321170700020', '321170700021', '321171000056', '321171000390', '321171000393', '321171000415', '321171000517', '321171000518', '321171000548', '321171000549', '321171000550', '321171000552', '321171000553', '321171000554', '321171000556', '321171000573', '321171000611', '321171300001', '321270400024', '321370700016', '321371000187', '321371000188', '321471000054', '321570400058', '321570700099', '321570800036', '321570800037', '321570800038', '321570800039', '321570800064', '321570800069', '321570800070', '321570800073', '321571000071', '321571000072', '321571000073', '321571000109', '321571000110', '321571000157', '321571000166', '321571000167', '321571000178', '321571000188', '321571000189', '321571000190', '321571000191', '321571000224', '321571000225', '321571000226', '321571000227', '321571000233', '321670700008', '321770700020', '321770700021', '321770700022', '321770700026', '321770800004', '321770800005', '321771000130', '321771000131', '321870800018', '321870800019', '321870800020', '322070400004', '322070400005', '322070400006', '322070600001', '322070600002', '322070800003', '322071000013', '322071000039', '322071000044', '322071000045', '322071000046', '322071000064', '322071000065', '322071000066', '322170700006', '322170700007', '322170700008', '322170700031', '322171000007', '322171000029', '322271000017', '322271000019', '322370500002', '322370500003', '322370700012', '322371000018', '322371000019', '322371000020', '322371000021', '322371000066', '322471000016', '322871000005', '322871000011', '322871000012', '322871000013', '322871000017', '322970800002', '322970800003', '322970800004', '322971000003', '322971000004', '596170400027', '596170400028', '596170400029', '596170400034', '596170400035', '596170400053', '596170500039', '596170500040', '596170500041', '596170500047', '596170800023', '596170800024', '596170800035', '596170800036', '596170800042', '596170800043', '596170800051', '596170800052', '596170800053', '596170800068', '596170800069', '596170800070', '596170800071', '596170800098', '596170800099', '596170800100', '596170800237', '596171000029', '596171000249', '596171000250', '596171000296', '596171000297', '596171000298', '596171000299', '596171000300', '596171000301', '596171000305', '596171000306', '596270500012', '596270500020', '596270500021', '596270500022', '596270700018', '596271000368', '596271000391', '596271000392', '596271000393', '596271000394', '596271000395', '596271000396', '596271000397', '596371000024', '596371000025', '596371000030', '596470400014', '596470700021', '596470700023', '596470700024', '596471000116', '596471000117', '596471000219', '596471000220', '596471000301', '596670400023', '596670500005', '596670500006', '596670700049', '596670700058', '596670700059', '596670700060', '596670700061', '596670700062', '596670800094', '596670800095', '596670800096', '596670800121', '596671000120', '596870400003', '596871000019', '597271000003', '597370700003', '706170800030', '706171000122', '706171000134', '706270400006', '706271000043', '706271000044', '706271000045', '706271000053', '706271000054', '706271000055', '706271000056', '321671000199', '101171300002', '706170500025', '321570400006', '321170700002', '321470400001', '321470400002', '321470400003', '706170500011', '321171000036', '321171000539', '321170400003', '321170400004');
        $getData = \App\Models\Memberinvestments::whereIn('account_number', $array)->get();
        foreach ($getData as $data) {
            $record = \App\Models\Daybook::where('account_no', $data->account_number)->where('transaction_type', 2)->first();
            $data->update(['created_at' => $record->created_at]);
            echo 'success' . '</br>';
        }
    }
    /**
     * Query to update account number in demand advice table by fetching record from member investment table
     */
    public function update_demandadvice_newaccountnumberfield_data()
    {
        $demand_data = DemandAdvice::where('is_deleted', 0)->where('status', 1)->whereIn('payment_type', [1, 2, 3, 4])->whereNull('account_number')->get();
        foreach ($demand_data as $data) {
            $getmemberinvestment = Memberinvestments::where('id', $data->investment_id)->where('is_deleted', 0)->first();
            $data->update(['account_number' => $getmemberinvestment->account_number]);
        }
        die("success");
    }
    /**
     * Query to update payment date in demand advice table by fetching record from daybook table
     */
    public function update_demandadvice_paymentdate_data()
    {
        $demand_data = DemandAdvice::where('is_deleted', 0)->where('status', 1)->whereIn('payment_type', [1, 2, 3, 4])->whereNull('payment_date')->get();
        foreach ($demand_data as $data) {
            $getdaybookdata = Daybook::where('transaction_type', 17)->where('account_no', $data->account_number)->where('status', 1)->where('is_deleted', 0)->first();
            $record = DemandAdvice::find($data->id);
            if (isset($getdaybookdata->created_at)) {
                $paymentdate = date("Y-m-d", strtotime($getdaybookdata->created_at));
            }
            if (isset($paymentdate)) {
                $record->update(['payment_date' => $paymentdate]);
            }
        }
        die("success");
    }
    /**
     * Query to update final_amount in demand advice table by fetching record from daybook table
     */
    public function update_demandadvice_finalamount_data()
    {
        $demand_data = DemandAdvice::where('is_deleted', 0)->where('is_mature', 0)->where('status', 1)->whereIn('payment_type', [1, 2, 4])->whereNull('final_amount')->get();
        foreach ($demand_data as $key => $data) {
            $getdaybookdata = Daybook::where('transaction_type', 17)->where('account_no', $data->account_number)->where('status', 1)->where('is_deleted', 0)->first();
            if (isset($getdaybookdata->id)) {
                if (isset($getdaybookdata->withdrawal)) {
                    $data->update(['final_amount' => $getdaybookdata->withdrawal]);
                }
            }
        }
        die("success");
    }
    /**
     * Query to update maturity_date in demand advice table by fetching record from daybook table
     */
    public function update_memberinvestment_maturity_date()
    {
        $data = Memberinvestments::where('plan_id', 9)->where('is_mature', 1)->where('maturity_date', '1970-01-01')->where('is_deleted', 0)->get();
        foreach ($data as $row) {
            if ($row->maturity_date == '1970-01-01') {
                //dd($row->maturity_date);
                $monthsrecord = $row->tenure * 12;
                $maturity_date = $row->created_at->addMonths($monthsrecord);
                $newmaturity_date = date('Y-m-d', strtotime($maturity_date));
                //dd($newmaturity_date);
                $row->update(['maturity_date' => $newmaturity_date]);
            }
        }
        die("success");
    }
    public function interestRecord()
    {
        $get = Daybook::where('transaction_type', '16')->where('account_no', 'not like', '%R-%')->where('is_deleted', 0)->whereBetween(\DB::raw('DATE(created_at)'), ['2022-04-01', '2022-12-31'])->get();
        foreach ($get as $value) {
            $demandRecord = DemandAdvice::where('account_number', $value->account_no)->where('is_deleted', 0)->first();
            $getAll = AllHeadTransaction::where('type', 13)->where('head_id', 36)->where('type_id', $demandRecord->id)->where('type_transaction_id', $demandRecord->id)->where('is_deleted', 0)->first();
            if (isset($getAll->type_id)) {
                if ($demandRecord->id == $getAll->type_id) {
                    print_r($demandRecord->id);
                    print_r('</br>');
                }
            }
        }
    }
    public function insert_loan_data_collector_account()
    {
        $data1 = Memberloans::whereNotNull('associate_member_id')->whereIN('status', [1, 3, 4])->get();
        foreach ($data1 as $value) {
            // dd($value->loan_type);
            $record['type'] = 2;
            $record['type_id'] = $value->id;
            $record['associate_id'] = $value->associate_member_id;
            $record['status'] = 1;
            $record['created_id'] = 1;
            $record['created_by'] = 1;
            $recordInsert = CollectorAccount::create($record);
        }
        die("success");
    }
    public function insert_grouploan_data_collector_account()
    {
        $data1 = Grouploans::whereNotNull('associate_member_id')->whereIN('status', [1, 3, 4])->get();
        //dd($data1);
        foreach ($data1 as $value) {
            $record['type'] = 3;
            $record['type_id'] = $value->id;
            $record['associate_id'] = $value->associate_member_id;
            $record['status'] = 1;
            $record['created_id'] = 1;
            $record['created_by'] = 1;
            $recordInsert = CollectorAccount::create($record);
        }
        die("success");
    }
    public function insert_savingaccount_data_collector_account()
    {
        $data1 = SavingAccount::whereNotNull('associate_id')->get();
        foreach ($data1 as $value) {
            $record['type'] = 4;
            $record['type_id'] = $value->id;
            $record['associate_id'] = $value->associate_id;
            $record['status'] = 1;
            $record['created_id'] = 1;
            $record['created_by'] = 1;
            $recordInsert = CollectorAccount::create($record);
        }
        die("success");
    }
    public function insert_memberinvestmentaccount_data_collector_account()
    {
        $data1 = Memberinvestments::whereNotNull('associate_id')->where('plan_id', '!=', 1)->get();
        foreach ($data1 as $value) {
            $record['type'] = 1;
            $record['type_id'] = $value->id;
            $record['associate_id'] = $value->associate_id;
            $record['status'] = 1;
            $record['created_id'] = 1;
            $record['created_by'] = 1;
            $recordInsert = CollectorAccount::create($record);
        }
        die("success");
    }
    public function updateSalaryDate(Request $request, $id)
    {
        if ($id == 1) {
            $getSalaryData = \App\Models\EmployeeSalaryLeaser::where('year', '>=', '2022')->get();
            foreach ($getSalaryData as $getSalary) {
                $date = $getSalary->year . '-' . $getSalary->month . '-01';
                $lastDate = Carbon::parse($date)->endOfMonth();
                $leaserRecrd = \App\Models\EmployeeSalary::where('leaser_id', $getSalary->id)->get();
                foreach ($leaserRecrd as $rcrd) {
                    \App\Models\EmployeeSalary::where('id', $rcrd->id)->update(['created_at' => $lastDate]);
                    $newRecrdc = \App\Models\EmployeeLedger::where('type', 6)->where('type_id', $rcrd->id)->first();
                    \App\Models\AllHeadTransaction::where('type', 12)->where('sub_type', 121)->where('type_id', $getSalary->id)->update(['created_at' => $lastDate, 'entry_date' => $lastDate]);
                    $newRecrdc->update(['created_at' => $lastDate]);
                }
            }
        }
        if ($id == 2) {
            $getSalaryData = \App\Models\RentLedger::where('year', '>=', '2022')->where('month', '>=', 2)->get();
            foreach ($getSalaryData->chunk(1) as $getSalarysss) {
                foreach ($getSalarysss as $getSalary) {
                    $date = $getSalary->year . '-' . $getSalary->month . '-31';
                    $lastDate = Carbon::parse($date)->startOfMonth();
                    $leaserRecrd = \App\Models\RentPayment::where('ledger_id', $getSalary->id)->get();
                    foreach ($leaserRecrd->chunk(20) as $rcrdss) {
                        foreach ($rcrdss as $rcrd) {
                            \App\Models\RentPayment::where('id', $rcrd->id)->update(['created_at' => $lastDate]);
                            $newRecrdc = \App\Models\RentLiabilityLedger::whereIn('type', [4, 5])->where('type_id', $rcrd->id)->first();
                            //   dd(\App\Models\AllHeadTransaction::where('type', 10)->where('sub_type', 101)->where('type_id', $getSalary->id)->first());
                            \App\Models\AllHeadTransaction::where('type', 10)->where('sub_type', 101)->where('type_id', $getSalary->id)->update(['created_at' => $lastDate, 'entry_date' => $lastDate]);
                            $newRecrdc->update(['created_at' => $lastDate]);
                        }
                    }
                }
            }
        }
        // commission ledger detail & associate transaction update 
        if ($id == 31) {
            $getSalaryData = \App\Models\CommissionLeaser::whereDate('start_date', '=', '2022-12-01')->where('is_deleted', 0)->get();
            foreach ($getSalaryData as $getSalary) {
                $lastDateget = $getSalary->end_date;
                $date_create = date("Y-m-d", strtotime(convertDate($lastDateget))) . ' 05:00:00';
                $lastDateTime = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
                $lastDate = date("Y-m-d", strtotime(convertDate($date_create)));
                $lasttime = date("H:i:s", strtotime(convertDate($date_create)));
                // echo $lastDateTime.'-'. $lastDate.'-'.$lasttime.'-';die();
                $leaserRecrd = \App\Models\CommissionLeaserDetail::where('commission_leaser_id', $getSalary->id)->get();
                $cid = array();
                foreach ($leaserRecrd as $rcrd) {
                    $cid[] = $rcrd->id;
                }
                $type_id = $getSalary->id;
                //echo $getSalary->id.'---'.$type_id;die;
                //               print_r($cid);die;
                \App\Models\CommissionLeaserDetail::whereIn('id', $cid)->update(['created_at' => $lastDateTime]);
                \App\Models\AssociateTransaction::whereIn('type', [1, 2, 3])->whereIn('sub_type', [11, 21, 31])->where('payment_type', 'CR')->where('type_id', $type_id)->whereIn('type_transaction_id', $cid)->update(['created_at' => $lastDateTime, 'entry_date' => $lastDate, 'entry_time' => $lasttime]);
                \App\Models\AllHeadTransaction::whereIn('head_id', [63, 141])->where('payment_type', 'CR')->whereIn('type', [9, 2])->whereIn('sub_type', [90, 25, 21])->where('type_id', $type_id)->whereIn('type_transaction_id', $cid)->update(['created_at' => $lastDateTime, 'entry_date' => $lastDate, 'entry_time' => $lasttime]);
                // echo $type_id;
                \App\Models\AllHeadTransaction::whereIn('head_id', [88, 87])->where('payment_type', 'DR')->whereIn('type', [2])->whereIn('sub_type', [25, 21])->where('type_id', $type_id)->whereIn('type_transaction_id', $cid)->update(['created_at' => $lastDateTime, 'entry_date' => $lastDate, 'entry_time' => $lasttime]);
            }
        }
        if ($id == 32) {
            $getSalaryData = \App\Models\CommissionLeaserMonthly::whereDate('start_date', '=', '2023-02-01')->where('is_deleted', 0)->get();
            foreach ($getSalaryData as $getSalary) {
                $lastDateget = $getSalary->end_date;
                $date_create = date("Y-m-d", strtotime(convertDate($lastDateget))) . ' 05:00:00';
                $lastDateTime = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
                $lastDate = date("Y-m-d", strtotime(convertDate($date_create)));
                $lasttime = date("H:i:s", strtotime(convertDate($date_create)));
                // echo $lastDateTime.'-'. $lastDate.'-'.$lasttime.'-';die();
                $leaserRecrd = \App\Models\CommissionLeaserDetailMonthly::where('commission_leaser_id', $getSalary->id)->get();
                $cid = array();
                foreach ($leaserRecrd as $rcrd) {
                    $cid[] = $rcrd->id;
                }
                $type_id = $getSalary->id;
                //echo $getSalary->id.'---'.$type_id;die;
                //               print_r($cid);die;
                \App\Models\CommissionLeaserDetailMonthly::whereIn('id', $cid)->update(['created_at' => $lastDateTime]);
                \App\Models\AssociateTransaction::whereIn('type', [1, 2, 3])->whereIn('sub_type', [11, 21, 31])->where('payment_type', 'CR')->where('type_id', $type_id)->whereIn('type_transaction_id', $cid)->whereDate('entry_date', '>=', '2022-11-01')->update(['created_at' => $lastDateTime, 'entry_date' => $lastDate, 'entry_time' => $lasttime]);
                \App\Models\AllHeadTransaction::whereIn('head_id', [63, 141])->where('payment_type', 'CR')->whereIn('type', [9, 2])->whereIn('sub_type', [90, 25, 21])->where('type_id', $type_id)->whereIn('type_transaction_id', $cid)->whereDate('entry_date', '>=', '2022-11-01')->update(['created_at' => $lastDateTime, 'entry_date' => $lastDate, 'entry_time' => $lasttime]);
                // echo $type_id;
                \App\Models\AllHeadTransaction::whereIn('head_id', [88, 87])->where('payment_type', 'DR')->whereIn('type', [2])->whereIn('sub_type', [25, 21])->where('type_id', $type_id)->whereIn('type_transaction_id', $cid)->whereDate('entry_date', '>=', '2022-11-01')->update(['created_at' => $lastDateTime, 'entry_date' => $lastDate, 'entry_time' => $lasttime]);
            }
        }
        //   if($id == 3)
        //   {
        //       $getSalaryData  = \App\Models\CommissionLeaser::whereDate('start_date','>=','2022-04-01')->where('status',1)->get();
        //       foreach($getSalaryData as $getSalary)
        //       {
        //           $lastDate = $getSalary->end_date;
        //           $leaserRecrd = \App\Models\CommissionLeaserDetail::where('ledger_id', $getSalary->id)->get();
        //           foreach($leaserRecrd as $rcrd)
        //           {
        //               \App\Models\CommissionLeaserDetail::where('id', $rcrd->id)->update(['created_at' => $lastDate]);
        //               $newRecrdc = \App\Models\AssociateTransaction.php::whereIn('type',[1,2,3])->whereIn('sub_type',[11,21,31])->where('payment_type','CR')->where('type_id',$rcrd->id)->first();
        //               \App\Models\AllHeadTransaction::whereIn('head_id',[63,141])->where('payment_type','CR')->whereIn('type',[9,])->where('sub_type',[90,25,21,])->where('type_id', $getSalary->id)->update(['created_at' => $lastDate, 'entry_date' => $lastDate]);
        //               \App\Models\AllHeadTransaction::whereIn('head_id',[88,87])->where('payment_type','DR')->whereIn('type',[2])->where('sub_type',[21,25,])->where('type_id', $getSalary->id)->update(['created_at' => $lastDate, 'entry_date' => $lastDate]);
        //               $newRecrdc->update(['created_at' => $lastDate]);
        //           }
        //       }      
        //   }
        // if($id == 4)
        // {
        //     $getSalaryData  = \App\Models\CommissionLeaser::whereDate('start_date','>=','2022-04-01')->where('status',1)->offset(0)->limit(1)->get();
        //     foreach($getSalaryData as $getSalary)
        //     {
        //         $lastDate = date('Y-m-d',strtotime($getSalary->end_date));
        //         // $leaserRecrd = \App\Models\CommissionLeaserDetail::where('commission_leaser_id', $getSalary->id)->offset(0)->limit(100)->get();
        //         \App\Models\CommissionLeaserDetail::where('commission_leaser_id', $getSalary->id)->chunk(100, function($leaserRecrd) use( $lastDate,$getSalary) {
        //             foreach ($leaserRecrd as $rcrd) {
        //                 \App\Models\CommissionLeaserDetail::where('id', $rcrd->id)->update(['created_at' => $lastDate]);
        //                 $newRecrdc = \App\Models\AssociateTransaction::whereIn('type',[1,2,3])->whereIn('sub_type',[11,21,31])->where('payment_type','CR')->where('type_id',$getSalary->id)->where('type_transaction_id',$rcrd->id)->first();
        //                 \App\Models\AllHeadTransaction::whereIn('head_id',[63,141])->where('payment_type','CR')->whereIn('type',[9,])->where('sub_type',[90,25,21,])->where('type_id', $getSalary->id)->update(['created_at' => $lastDate, 'entry_date' => $lastDate]);
        //                 \App\Models\AllHeadTransaction::whereIn('head_id',[88,87])->where('payment_type','DR')->whereIn('type',[2])->where('sub_type',[21,25,])->where('type_id', $getSalary->id)->update(['created_at' => $lastDate, 'entry_date' => $lastDate]);
        //                 $newRecrdc->update(['created_at' => $lastDate]); 
        //             }
        //         });
        //     }  
        // }
        if ($id == 5) {
            $getSalaryData = \App\Models\CommissionLeaserMonthly::where('year', '=', '2022')->where('month', '11')->first();
            $lastDate = date('Y-m-d', strtotime($getSalaryData->end_date));
            \App\Models\CommissionLeaserDetailMonthly::where('commission_leaser_id', $getSalaryData->id)->chunk(1000, function ($leaserRecrd) use ($lastDate, $getSalaryData) {
                foreach ($leaserRecrd as $rcrd) {
                    \App\Models\CommissionLeaserDetailMonthly::where('id', $rcrd->id)->update(['created_at' => $lastDate]);
                    $newRecrdc = \App\Models\AssociateTransaction::whereIn('type', [1, 2, 3])->whereIn('sub_type', [11, 21, 31])->where('payment_type', 'CR')->where('type_id', $getSalaryData->id)->where('type_transaction_id', $rcrd->id)->first();
                    \App\Models\AllHeadTransaction::whereIn('head_id', [63, 141])->where('payment_type', 'CR')->whereIn('type', [9,])->where('sub_type', [90, 25, 21,])->where('type_id', $getSalaryData->id)->update(['created_at' => $lastDate, 'entry_date' => $lastDate]);
                    \App\Models\AllHeadTransaction::whereIn('head_id', [88, 87])->where('payment_type', 'DR')->whereIn('type', [2])->where('sub_type', [21, 25,])->where('type_id', $getSalaryData->id)->update(['created_at' => $lastDate, 'entry_date' => $lastDate]);
                    $newRecrdc->update(['created_at' => $lastDate]);
                }
            });
        }
        echo "done";
    }
    public static function ROIAmountUpdate()
    {
        $allHeadAccruedEntry = array();
        $allHeadPrincipleEntry = array();
        $allHeadpaymentEntry = array();
        $allHeadpaymentEntry2 = array();
        $calculatedDate = '';
        try {
            \App\Models\Memberloans::where('status', 4)->whereNotIn('loan_type', [1, 3])->chunk(10, function ($datas) {
                foreach ($datas as $data) {
                    \App\Models\LoanDayBooks::where('account_number', $data->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->chunk(20, function ($records) {
                        foreach ($records as $i => $value) {
                            $d = Memberloans::where('account_number', $value->account_number)->first();
                            // $d->update(['accrued_interest'=>0]);
                            $loansDetail = \App\Models\Loans::where('id', $value->loan_type)->first();
                            $calculatedDate = date('Y-m-d', strtotime($value->created_at));
                            $date = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->whereId($value->id)->orderBY('created_at', 'desc')->first();
                            $rr = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->where('id', '<', $value->id)->orderBY('created_at', 'desc')->first();
                            if (isset($date->created_at)) {
                                $rangeDate = date('Y-m-d', strtotime($date->created_at));
                            } else {
                                $rangeDate = $calculatedDate;
                            }
                            // $state_id = getBranchDetail($value->branch_id)->state_id;
                            $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), 33);
                            $currentDate = date('Y-m-d', strtotime($currentDate));
                            $dataTotalCount = DB::select('call calculate_loan_interest(?,?)', [$currentDate, $value->account_number]);
                            if (isset($rr->created_at)) {
                                $strattDate = date('Y-m-d', strtotime($rr->created_at));
                                ;
                                $endDate = date('Y-m-d', strtotime($date->created_at));
                                ;
                            } else {
                                $strattDate = date('Y-m-d', strtotime($d->approve_date));
                                ;
                                $endDate = $calculatedDate;
                            }
                            $emiData = \App\Models\LoanEmiNew1::where('emi_date', $rangeDate)->where('loan_type', $value->loan_type)->where('loan_id', $value->loan_id)->where('is_deleted', '0')->first();
                            $d = Memberloans::where('account_number', $value->account_number)->first();
                            $accuredSumCR = \App\Models\AllHeadTransactionNew::where('type', '5')->where('sub_type', '545')->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $d->id)->where('payment_type', 'CR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');
                            $accuredSumDR = \App\Models\AllHeadTransactionNew::where('type', '5')->where('sub_type', '545')->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $d->id)->where('payment_type', 'DR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');
                            //    if($calculatedDate == '2021-01-07')
                            //    {
                            //         dd($accuredSumCR,$accuredSumDR,$d->approve_date,$i);
                            //    }
                            //     if(!isset($emiData->id))
                            //     {
                            //         $emiData  = \App\Models\LoanEmiNew1::where('emi_dat',$rangeDate)->where('loan_type',$value->loan_type)->where('loan_id',$value->loan_id)->where('is_deleted','0')->first();
                            //         dd($value->loan_id,$value->id,$rangeDate);
                            //     }
                            $accuredSum = $accuredSumDR - $accuredSumCR;
                            if ($value->deposit <= $accuredSum) {
                                $accruedAmount = $value->deposit;
                                $principalAmount = 0;
                            } else {
                                $accruedAmount = $accuredSum;
                                $principalAmount = $value->deposit - $accuredSum;
                            }
                            $paymentHead = '';
                            if ($value->payment_mode == 0) {
                                $paymentHead = 28;
                            }
                            if ($value->payment_mode == 4) {
                                $paymentHead = 56;
                            }
                            if ($value->payment_mode == 1 || $value->payment_mode == 2 || $value->payment_mode == 3) {
                                $getSamraddhData = \App\Models\SamraddhBankDaybook::where('daybook_ref_id', $value->daybook_ref_id)->first();
                                $getHead = \App\Models\SamraddhBank::where('id', $getSamraddhData->bank_id)->first();
                                $paymentHead = $getHead->account_head_id;
                            }
                            $allHeadAccruedEntry = [
                                'daybook_ref_id' => $value->daybook_ref_id,
                                'branch_id' => $value->branch_id,
                                'head_id' => $loansDetail->ac_head_id,
                                'type' => 5,
                                'sub_type' => 545,
                                'type_id' => $emiData->id,
                                'type_transaction_id' => $value->loan_id,
                                'associate_id' => $value->associate_id,
                                'member_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                                'branch_id_from' => $value->branch_id,
                                'opening_balance' => $accruedAmount,
                                'amount' => $accruedAmount,
                                'closing_balance' => $accruedAmount,
                                'description' => $value->account_number . 'EMI collection',
                                'payment_type' => 'CR',
                                'payment_mode' => $value->payment_mode,
                                'currency_code' => 'INR',
                                'amount_to_id' => $value->branch_id,
                                'amount_to_name' => getBranchCode($value->branch_id)->name,
                                'amount_from_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                                'amount_from_name' => ($value->loan_type != 3) ? getMemberData($value->applicant_id)->first_name . ' ' . getMemberData($value->applicant_id)->last_name : getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name,
                                'transction_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                                'created_by' => $value->created_by,
                                'created_by_id' => 0,
                                'created_at' => $value->created_at,
                                'updated_at' => $value->updated_at,
                            ];
                            $allHeadPrincipleEntry = [
                                'daybook_ref_id' => $value->daybook_ref_id,
                                'branch_id' => $value->branch_id,
                                'head_id' => $loansDetail->head_id,
                                'type' => 5,
                                'sub_type' => 52,
                                'type_id' => $emiData->id,
                                'type_transaction_id' => $value->loan_id,
                                'associate_id' => $value->associate_id,
                                'member_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                                'branch_id_from' => $value->branch_id,
                                'opening_balance' => $principalAmount,
                                'amount' => $principalAmount,
                                'closing_balance' => $principalAmount,
                                'description' => $value->account_number . 'EMI collection',
                                'payment_type' => 'CR',
                                'payment_mode' => $value->payment_mode,
                                'currency_code' => 'INR',
                                'amount_to_id' => $value->branch_id,
                                'amount_to_name' => getBranchCode($value->branch_id)->name,
                                'amount_from_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                                'amount_from_name' => ($value->loan_type != 3) ? getMemberData($value->applicant_id)->first_name . ' ' . getMemberData($value->applicant_id)->last_name : getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name,
                                'transction_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                                'created_by' => $value->created_by,
                                'created_by_id' => 0,
                                'created_at' => $value->created_at,
                                'updated_at' => $value->updated_at,
                            ];
                            $allHeadpaymentEntry = [
                                'daybook_ref_id' => $value->daybook_ref_id,
                                'branch_id' => $value->branch_id,
                                'head_id' => $paymentHead,
                                'type' => 5,
                                'sub_type' => 52,
                                'type_id' => $emiData->id,
                                'type_transaction_id' => $value->loan_id,
                                'associate_id' => $value->associate_id,
                                'member_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                                'branch_id_from' => $value->branch_id,
                                'opening_balance' => $value->deposit,
                                'amount' => $value->deposit,
                                'closing_balance' => $value->deposit,
                                'description' => $value->account_number . 'EMI collection',
                                'payment_type' => 'DR',
                                'payment_mode' => $value->payment_mode,
                                'currency_code' => 'INR',
                                'amount_to_id' => $value->branch_id,
                                'amount_to_name' => getBranchCode($value->branch_id)->name,
                                'amount_from_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                                'amount_from_name' => ($value->loan_type != 3) ? getMemberData($value->applicant_id)->first_name . ' ' . getMemberData($value->applicant_id)->last_name : getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name,
                                'transction_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                                'created_by' => $value->created_by,
                                'created_by_id' => 0,
                                'created_at' => $value->created_at,
                                'updated_at' => $value->updated_at,
                            ];
                            // $d->update(['accrued_interest'=>$accruedAmount]);
                            // $d->update(['accrued_interest'=>$accruedAmount]);
                            // $allHeadpaymentEntry2[] = [
                            //     'daybook_ref_id' => $value->daybook_ref_id,
                            //     'branch_id' =>$value->branch_id ,
                            //     'head_id' => $paymentHead,
                            //     'type' => 5,
                            //     'sub_type' => 52 ,
                            //     'type_id' => $emiData->id  ,
                            //     'type_transaction_id' =>   $value->loan_id,
                            //     'associate_id' =>  $value->associate_id,
                            //     'member_id' =>  ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                            //     'branch_id_from' =>  $value->branch_id,
                            //     'opening_balance' =>  $principalAmount ,
                            //     'amount' =>  $principalAmount,
                            //     'closing_balance' => $principalAmount ,
                            //     'description' =>  $value->account_number.'EMI collection',
                            //     'payment_type' => 'DR' ,
                            //     'payment_mode' =>  $value->payment_mode,
                            //     'currency_code' => 'INR',
                            //     'amount_to_id' =>   $value->branch_id,
                            //     'amount_to_name' =>  getBranchCode($value->branch_id)->name,
                            //     'amount_from_id' =>  ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                            //     'amount_from_name' =>($value->loan_type != 3) ? getMemberData($value->applicant_id)->first_name . ' ' . getMemberData($value->applicant_id)->last_name : getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name,
                            //     'transction_date' => date('Y-m-d',strtotime($value->created_at)),
                            //     'entry_date' =>  date('Y-m-d',strtotime($value->created_at)),
                            //     'entry_time' =>  date('H:i:s',strtotime($value->created_at)),
                            //     'created_by' => $value->created_by,
                            //     'created_by_id' =>0,
                            //     'created_at' => $value->created_at ,
                            //     'updated_at' => $value->updated_at ,
                            // ];
                            $dataInsert1 = \App\Models\AllHeadTransactionNew::insert($allHeadAccruedEntry);
                            $dataInsert2 = \App\Models\AllHeadTransactionNew::insert($allHeadPrincipleEntry);
                            $dataInsert3 = \App\Models\AllHeadTransactionNew::insert($allHeadpaymentEntry);
                        }
                        // $dataInsert4 = \App\Models\AllHeadTransaction1::insert($allHeadpaymentEntry2);
                    });
                    \Log::channel('loan')->info('Loan Entries of --' . $data->account_number);
                }
            });
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
    }
    public static function ROIAmountUpdateOld()
    {
        $allHeadAccruedEntry = array();
        $allHeadPrincipleEntry = array();
        $allHeadpaymentEntry = array();
        $allHeadpaymentEntry2 = array();
        $calculatedDate = '';
        try {
            \App\Models\Memberloans::whereIn('id', [6460, 6414, 6392, 6380, 6358, 6356, 6324, 6322, 6319, 6317, 6300, 6267, 6230, 6227, 6181, 6147])->where('status', 4)->chunk(2000, function ($datas) {
                foreach ($datas as $data) {
                    $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), 33);
                    $currentDate = date('Y-m-d', strtotime($currentDate));
                    $dataTotalCount = DB::select('call calculate_loan_interest(?,?,?)', [$currentDate, $data->account_number, 1]);
                    \Log::info("Run Cron for !" . $data->account_number);
                }
            });
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
    }
    public static function GrpROIAmountUpdate()
    {
        $allHeadAccruedEntry = array();
        $allHeadPrincipleEntry = array();
        $allHeadpaymentEntry = array();
        $allHeadpaymentEntry2 = array();
        $calculatedDate = '';
        try {
            \App\Models\Grouploans::where('status', 4)->whereIn('loan_type', [3])->chunk(10, function ($datas) {
                foreach ($datas as $data) {
                    \App\Models\LoanDayBooks::where('account_number', $data->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->chunk(20, function ($records) {
                        foreach ($records as $i => $value) {
                            $d = Grouploans::where('account_number', $value->account_number)->first();
                            // $d->update(['accrued_interest'=>0]);
                            $loansDetail = \App\Models\Loans::where('id', $value->loan_type)->first();
                            $calculatedDate = date('Y-m-d', strtotime($value->created_at));
                            $date = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->whereId($value->id)->orderBY('created_at', 'desc')->first();
                            $rr = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->where('id', '<', $value->id)->orderBY('created_at', 'desc')->first();
                            if (isset($date->created_at)) {
                                $rangeDate = date('Y-m-d', strtotime($date->created_at));
                            } else {
                                $rangeDate = $calculatedDate;
                            }
                            $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), 33);
                            $currentDate = date('Y-m-d', strtotime($currentDate));
                            $dataTotalCount = DB::select('call calculate_loan_interest(?,?)', [$currentDate, $value->account_number]);
                            if (isset($rr->created_at)) {
                                $strattDate = date('Y-m-d', strtotime($rr->created_at));
                                ;
                                $endDate = date('Y-m-d', strtotime($date->created_at));
                                ;
                            } else {
                                $strattDate = date('Y-m-d', strtotime($d->approve_date));
                                ;
                                $endDate = $calculatedDate;
                            }
                            $emiData = \App\Models\LoanEmiNew1::where('emi_date', $rangeDate)->where('loan_type', $value->loan_type)->where('loan_id', $value->loan_id)->where('is_deleted', '0')->first();
                            $d = Grouploans::where('account_number', $value->account_number)->first();
                            $accuredSumCR = \App\Models\AllHeadTransactionNew::where('type', '5')->where('sub_type', '545')->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $d->id)->where('payment_type', 'CR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');
                            $accuredSumDR = \App\Models\AllHeadTransactionNew::where('type', '5')->where('sub_type', '545')->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $d->id)->where('payment_type', 'DR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');
                            //    if($calculatedDate == '2021-01-07')
                            //    {
                            //         dd($accuredSumCR,$accuredSumDR,$d->approve_date,$i);
                            //    }
                            //     if(!isset($emiData->id))
                            //     {
                            //         $emiData  = \App\Models\LoanEmiNew1::where('emi_dat',$rangeDate)->where('loan_type',$value->loan_type)->where('loan_id',$value->loan_id)->where('is_deleted','0')->first();
                            //         dd($value->loan_id,$value->id,$rangeDate);
                            //     }
                            $accuredSum = $accuredSumDR - $accuredSumCR;
                            if ($value->deposit <= $accuredSum) {
                                $accruedAmount = $value->deposit;
                                $principalAmount = 0;
                            } else {
                                $accruedAmount = $accuredSum;
                                $principalAmount = $value->deposit - $accuredSum;
                            }
                            $paymentHead = '';
                            if ($value->payment_mode == 0) {
                                $paymentHead = 28;
                            }
                            if ($value->payment_mode == 4) {
                                $paymentHead = 56;
                            }
                            if ($value->payment_mode == 1 || $value->payment_mode == 2 || $value->payment_mode == 3) {
                                $getSamraddhData = \App\Models\SamraddhBankDaybook::where('daybook_ref_id', $value->daybook_ref_id)->first();
                                $getHead = \App\Models\SamraddhBank::where('id', $getSamraddhData->bank_id)->first();
                                $paymentHead = $getHead->account_head_id;
                            }
                            $allHeadAccruedEntry = [
                                'daybook_ref_id' => $value->daybook_ref_id,
                                'branch_id' => $value->branch_id,
                                'head_id' => $loansDetail->ac_head_id,
                                'type' => 5,
                                'sub_type' => 545,
                                'type_id' => $emiData->id,
                                'type_transaction_id' => $value->loan_id,
                                'associate_id' => $value->associate_id,
                                'member_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                                'branch_id_from' => $value->branch_id,
                                'opening_balance' => $accruedAmount,
                                'amount' => $accruedAmount,
                                'closing_balance' => $accruedAmount,
                                'description' => $value->account_number . 'EMI collection',
                                'payment_type' => 'CR',
                                'payment_mode' => $value->payment_mode,
                                'currency_code' => 'INR',
                                'amount_to_id' => $value->branch_id,
                                'amount_to_name' => getBranchCode($value->branch_id)->name,
                                'amount_from_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                                'amount_from_name' => ($value->loan_type != 3) ? getMemberData($value->applicant_id)->first_name . ' ' . getMemberData($value->applicant_id)->last_name : getMemberData($d->member_id)->first_name . ' ' . getMemberData($d->member_id)->last_name,
                                'transction_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                                'created_by' => $value->created_by,
                                'created_by_id' => 0,
                                'created_at' => $value->created_at,
                                'updated_at' => $value->updated_at,
                            ];
                            $allHeadPrincipleEntry = [
                                'daybook_ref_id' => $value->daybook_ref_id,
                                'branch_id' => $value->branch_id,
                                'head_id' => $loansDetail->head_id,
                                'type' => 5,
                                'sub_type' => 55,
                                'type_id' => $emiData->id,
                                'type_transaction_id' => $value->loan_id,
                                'associate_id' => $value->associate_id,
                                'member_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                                'branch_id_from' => $value->branch_id,
                                'opening_balance' => $principalAmount,
                                'amount' => $principalAmount,
                                'closing_balance' => $principalAmount,
                                'description' => $value->account_number . 'EMI collection',
                                'payment_type' => 'CR',
                                'payment_mode' => $value->payment_mode,
                                'currency_code' => 'INR',
                                'amount_to_id' => $value->branch_id,
                                'amount_to_name' => getBranchCode($value->branch_id)->name,
                                'amount_from_id' => ($value->loan_type != 3) ? $value->applicant_id : $d->member_id,
                                'amount_from_name' => ($value->loan_type != 3) ? getMemberData($value->applicant_id)->first_name . ' ' . getMemberData($value->applicant_id)->last_name : getMemberData($d->member_id)->first_name . ' ' . getMemberData($d->member_id)->last_name,
                                'transction_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                                'created_by' => $value->created_by,
                                'created_by_id' => 0,
                                'created_at' => $value->created_at,
                                'updated_at' => $value->updated_at,
                            ];
                            $allHeadpaymentEntry = [
                                'daybook_ref_id' => $value->daybook_ref_id,
                                'branch_id' => $value->branch_id,
                                'head_id' => $paymentHead,
                                'type' => 5,
                                'sub_type' => 55,
                                'type_id' => $emiData->id,
                                'type_transaction_id' => $value->loan_id,
                                'associate_id' => $value->associate_id,
                                'member_id' => ($value->loan_type != 3) ? $value->applicant_id : $value->member_id,
                                'branch_id_from' => $value->branch_id,
                                'opening_balance' => $value->deposit,
                                'amount' => $value->deposit,
                                'closing_balance' => $value->deposit,
                                'description' => $value->account_number . 'EMI collection',
                                'payment_type' => 'DR',
                                'payment_mode' => $value->payment_mode,
                                'currency_code' => 'INR',
                                'amount_to_id' => $value->branch_id,
                                'amount_to_name' => getBranchCode($value->branch_id)->name,
                                'amount_from_id' => ($value->loan_type != 3) ? $value->applicant_id : $d->member_id,
                                'amount_from_name' => ($value->loan_type != 3) ? getMemberData($value->applicant_id)->first_name . ' ' . getMemberData($value->applicant_id)->last_name : getMemberData($d->member_id)->first_name . ' ' . getMemberData($d->member_id)->last_name,
                                'transction_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                                'created_by' => $value->created_by,
                                'created_by_id' => 0,
                                'created_at' => $value->created_at,
                                'updated_at' => $value->updated_at,
                            ];
                            $dataInsert1 = \App\Models\AllHeadTransactionNew::insert($allHeadAccruedEntry);
                            $dataInsert2 = \App\Models\AllHeadTransactionNew::insert($allHeadPrincipleEntry);
                            $dataInsert3 = \App\Models\AllHeadTransactionNew::insert($allHeadpaymentEntry);
                        }
                        // $dataInsert4 = \App\Models\AllHeadTransaction1::insert($allHeadpaymentEntry2);
                    });
                    \Log::channel('grploan')->info('Group Loan Entries of --' . $data->account_number);
                }
            });
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
    }
    public function insertStationaryCharge()
    {
        $allHeadTransaction = array();
        $branchDaybook = array();
        $data = Memberinvestments::whereIn('account_number', [323270300014])->chunk(100, function ($record) {
            foreach ($record as $rcrd) {
                try {
                    $entryTime = date("H:i:s");
                    Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($rcrd->created_at))));
                    $dayBookRef = CommanController::createBranchDayBookReference(50);
                    $branch_id = $rcrd->branch_id;
                    $type_id = $rcrd->id;
                    $type = 3;
                    $sub_type = 35;
                    $associate_id = $rcrd->associate_id;
                    $member_id = $rcrd->member_id;
                    $amount = 50;
                    $description = 'Stationary charges on investment plan registration ' . $amount;
                    $description_dr = 'Stationary charges on investment plan registration ' . $amount;
                    $description_cr = 'Stationary charges on investment plan registration ' . $amount;
                    $payment_mode = 0;
                    $currency_code = 'INR';
                    $v_no = NULL;
                    $ssb_account_id_from = NULL;
                    $cheque_no = NULL;
                    $transction_no = NULL;
                    $entry_date = date('Y-m-d', strtotime($rcrd->created_at));
                    $entry_time = date("H:i:s");
                    $created_by = (Auth::user()->role_id == 3) ? 2 : 1;
                    $created_by_id = Auth::user()->id;
                    $created_at = date('Y-m-d H:i:s', strtotime($rcrd->created_at));
                    ;
                    $companyId = $rcrd->company_id;
                    $is_query = 1;
                    $amountArraySsb = array('1' => (50));
                    $memberInvestments = Memberinvestments::with(['branch', 'member'])->where('id', $rcrd->id)->first();
                    $createDayBook = CommanController::createDayBook($dayBookRef, $dayBookRef, 19, $rcrd->id, $associate_id, $rcrd->customer_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $rcrd->account_number, $rcrd->branch_id, getBranchCode($rcrd->branch_id)->branch_code, $amountArraySsb, 0, NULL, $rcrd->customer_id, $rcrd->account_number, 50, NULL, NULL, $created_at, NULL, NULL, NULL, 'CR', $companyId);
                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, 122, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId);
                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId);
                    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, $companyId, NULL, NULL);
                    DB::commit();
                } catch (\Exception $ex) {
                    DB::rollback();
                    $response = [
                        'insertedid' => '',
                        'status' => false,
                        'msg' => $ex->getMessage(),
                        'line' => $ex->getLine(),
                    ];
                }
                print_r("done");
            }
        });
    }
    public function getNotMatche()
    {
        $getAll = AllHeadTransaction::whereIn('payment_mode', [1, 2])->where('is_deleted', 0)->get();
        foreach ($getAll as $val) {
            $getData = SamraddhBankDaybook::where('daybook_ref_id', $val->daybook_ref_id)->where('bank_id', '!=', $val->bank_id)->first();
            if (isset($getData->daybook_ref_id)) {
                print_r($getData->daybook_ref_id);
                echo "</br>";
            }
        }
    }
    public function updateNotMatche()
    {
        $getAll = AllHeadTransaction::whereIn('payment_mode', [1, 2])->whereIn('daybook_ref_id', [105815, 105821, 105825, 105844, 105846, 105858, 105895, 105903, 105920, 105930, 105932, 105934, 105936, 105938, 105940, 105942, 105967, 106024, 107027, 107031, 107033, 108773, 108776, 108778, 108780, 109080, 109440, 110612, 110638, 110640, 110642, 110645, 110647, 110652, 110655, 110657, 110659, 111077, 111086, 111088, 111108, 111110, 111112, 111114, 111116, 111118, 111120, 111123, 111132, 111135, 111137, 111140, 111142, 111157, 111161, 111188, 111273, 111294, 111300, 111302, 111349, 111494, 111513, 111518, 111532, 111534, 111536, 111538, 111549, 111551, 111553, 111555, 111557, 111561, 111568, 111571, 111573, 111612, 112687, 112692, 112764, 112766, 112773, 112783, 115951, 116233, 116236, 116238, 117847, 117850, 117854, 117857, 117859, 119214, 120473, 120475, 120477, 120479, 120481, 120483, 120485, 120487, 120772, 120781, 121588, 121628, 122365, 122378, 123742, 123756, 123769, 123776, 123779, 123794, 123819, 123821, 123824, 123826, 123834, 123837, 124771, 124777, 124789, 124792, 124805, 125206, 125208, 125210, 125212, 125861, 125863, 125865, 125869, 125871, 125873, 125875, 125877, 125879, 125881, 125883, 125885, 126060, 127197, 127201, 127203, 127205, 127208, 127214, 127220, 128226, 128228, 128230, 128235, 128238, 128240, 128242, 128244, 128246, 128248, 128250, 128252, 129313, 129315, 129317, 129319, 129321, 129323, 129325, 129327, 129330, 129822, 129828, 129830, 129832, 129837, 129850, 129852, 129867, 129871, 129876, 129879, 129888, 129893, 129902, 130835, 130847, 130849, 130983, 130992, 131888, 131892, 131905, 131912, 132103, 133926, 133933, 133936, 133938, 133942, 133991, 134003, 134243, 134247, 134249, 134253, 134257, 134264, 134266, 134270, 134284, 134290, 134300, 134305, 134311, 134353, 134357, 134362, 134372, 134379, 134390, 134401, 134432, 134439, 137125, 137131, 137136, 137151, 137450, 137466, 139102, 141121, 141792, 141795, 144149, 144154, 144157, 144621, 144623, 144625, 144627, 145584, 145585, 145597, 145639, 145641, 145644, 145646, 145648, 145651, 145900, 145909, 145930, 145968, 145981, 145999, 146007, 146015, 146030, 146040, 146042, 146049, 148062, 148087, 148098, 148113, 148116, 148129, 148133, 148138, 148152, 148163, 148189, 148193, 148197, 148207, 148215, 148221, 149237, 149239, 149241, 150404, 150411, 150417, 150425, 150433, 150440, 150566, 150577, 150594, 150603, 150612, 150621, 150631, 150639, 150648, 150654, 150665, 150671, 150673, 150675, 153637, 154639, 154641, 154645, 154652, 154656, 154659, 154664, 154667, 154669, 154671, 156983, 157022, 157685, 157687, 157691, 157695, 157700, 157704, 157706, 157708, 157710, 157712, 157715, 157717, 157719, 157721, 157725, 157727, 157728, 157730, 157732, 157734, 157736, 157738, 157740, 157742, 157744, 158537, 158546, 158611, 158627, 158639, 158665, 158698, 158702, 158704, 159022, 159026, 159028, 159069, 159071, 159074, 159080, 159238, 159242, 159261, 162903, 162909, 162913, 162922, 164957, 164957, 164957, 164957, 166231, 166238, 166240, 166243, 166336, 166339, 166342, 167914, 168293, 175531, 175533, 175554, 175559, 175561, 175563, 175567, 175569, 175571, 175573, 175575, 175578, 175580, 175582, 175585, 175742, 175742, 175930, 175930, 180622, 180622, 180622, 180622, 180757, 180759, 180765, 180767, 180771, 181327, 181327, 181327, 181327, 183087, 183090, 183593, 183593, 186283, 186283, 187644, 187647, 187695, 187695, 187706, 187706, 188819, 188854, 188856, 188869, 188875, 188883, 188902, 188909, 188911, 188923, 189605, 189613, 189624, 189636, 189640, 189642, 189646, 190443, 192285, 192293, 192298, 192308, 192313, 192360, 192371, 192381, 192386, 192393, 192415, 193449, 193451, 193526, 193529, 193533, 193537, 195492, 195502, 195510, 195515, 195517, 195520, 195524, 195526, 195529, 195538, 195554, 195560, 195567, 195979, 195985, 195985, 195989, 195995, 195999, 196002, 196007, 196011, 196015, 196018, 197095, 197106, 197109, 197114, 197120, 197126, 197138, 197140, 197145, 197151, 197157, 197173, 197769, 197769, 198516, 199534, 199594, 199603, 202179, 202186, 202190, 202229, 202230, 202235, 202287, 202296, 202307, 202310, 202315, 203469, 203473, 203488, 203491, 203501, 203519, 203743, 203758, 203766, 203781, 203786, 203790, 203794, 203798, 203802, 203807, 203812, 203815, 204929, 206033, 206056, 206058, 206060, 206062, 206064, 206066, 206068, 206070, 206072, 206074, 206076, 206423, 206429, 210129, 210135, 210138, 210142, 210145, 212266, 212266, 212274, 212274, 214675, 214688, 214692, 214726, 214739, 217339, 217341, 217343, 218613, 218623, 218627, 220395, 222987, 223847, 223862, 223862, 224473, 224473, 224473, 224473, 225080, 225082, 225088, 225105, 225112, 225120, 225856, 225860, 225866, 225870, 225936, 225938, 225960, 225967, 225973, 225978, 225988, 225993, 225995, 225997, 226000, 226014, 226017, 226022, 226024, 226028, 226039, 228829, 228846, 228879, 228892, 228896, 228907, 228910, 228925, 228941, 228950, 229813, 229819, 229824, 231766, 231768, 231771, 231779, 231785, 231787, 231789, 231791, 231793, 231795, 231797, 231804, 231810, 232712, 232721, 232727, 232733, 232739, 232746, 232756, 232767, 234145, 234145, 234145, 234160, 234160, 234160, 234169, 234169, 234169, 234214, 234214, 234214, 234215, 234215, 234215, 234217, 234217, 234217, 234242, 234353, 234353, 235013, 235013, 235013, 235017, 235017, 235017, 235018, 235018, 235018, 235019, 235019, 235019, 235020, 235020, 235020, 235021, 235021, 235021, 235022, 235022, 235022, 235023, 235023, 235023, 235024, 235024, 235024, 235025, 235025, 235025, 235026, 235026, 235026, 238336, 238353, 238364, 238368, 238373, 238379, 238531, 238540, 239650, 239658, 239662, 239668, 239680, 239685, 240505, 240505, 240948, 240953, 240988, 240994, 241001, 241009, 241015, 241021, 241030, 241039, 241045, 241049, 241058, 241058, 241058, 241058, 241668, 241668, 241668, 242782, 242787, 244623, 244623, 244623, 244628, 244628, 244628, 244629, 244629, 244629, 244642, 244642, 244642, 244647, 244647, 244647, 244657, 244657, 244657, 244661, 244661, 244661, 244665, 244665, 244665, 244711, 244711, 244711, 244716, 244716, 244716, 244717, 244717, 244717, 244718, 244718, 244718, 244721, 244721, 244721, 244723, 244723, 244723, 244724, 244724, 244724, 244726, 244726, 244726, 244729, 244729, 244729, 244731, 244731, 244731, 244733, 244733, 244733, 244734, 244734, 244734, 244735, 244735, 244735, 244736, 244736, 244736, 244738, 244738, 244738, 244741, 244741, 244741, 244744, 244744, 244744, 244748, 244748, 244748, 244750, 244750, 244750, 244752, 244752, 244752, 244754, 244754, 244754, 244755, 244755, 244755, 245213, 245213, 245213, 247917, 247924, 247965, 247970, 248278, 248278, 248278, 251520, 251520, 251935, 251947, 251955, 251968, 251970, 253227, 253227, 253227, 254057, 254057, 254388, 254397, 254399, 254417, 254419, 254428, 254436, 255662, 255662, 257307, 257307, 257307, 257308, 257308, 257308, 257608, 257617, 257621, 257628, 257635, 257641, 257647, 257656, 257666, 257673, 258372, 258372, 258717, 258717, 258988, 258988, 258988, 260054, 260057, 260064, 260066, 260071, 260073, 260399, 260399, 260399, 260997, 260997, 260997, 261568, 261574, 261578, 261582, 261585, 261595, 262288, 262288, 262288, 262315, 262329, 262357, 262357, 262357, 262678, 262678, 262678, 263522, 263522, 263522, 264795, 264795, 264795, 264795, 265840, 265840, 265840, 266063, 266063, 267829, 267829, 268151, 268153, 268155, 268158, 268160, 268162, 268164, 268166, 268171, 268171, 268173, 268175, 268177, 268179, 268181, 268184, 268186, 268188, 268190, 269062, 269062, 269069, 269069, 269425, 269430, 269441, 269446, 269454, 269457, 269460, 269463, 269465, 269465, 269466, 269469, 269473, 269473, 269474, 269474, 269627, 269627, 269627, 270374, 270381, 270391, 270403, 270416, 270430, 270434, 270439, 270485, 270489, 270493, 271709, 271722, 271728, 271734, 271743, 271756, 271764, 271773, 271780, 271785, 272658, 272658, 272658, 272772, 272772, 272772, 272891, 272891, 272891, 272891, 274880, 274901, 274909, 274996, 274996, 274996, 275113, 275113, 275121, 275121, 275122, 275122, 275123, 275125, 275127, 275219, 275226, 275229, 275242, 275247, 275252, 275255, 275258, 275967, 275967, 275967, 277983, 277983, 278844, 278854, 278858, 278866, 278877, 278890, 278895, 278900, 278905, 279679, 279679, 279679, 279775, 279775, 279775, 279775, 279831, 279831, 279882, 280014, 280016, 282325, 282336, 282347, 282358, 282371, 282379, 282391, 282394, 282400, 282407, 283057, 283057, 283183, 283183, 285930, 285932, 285936, 285939, 285944, 285948, 285954, 285958, 285973, 285975, 285977, 285981, 285983, 286205, 286205, 286205, 286725, 286725, 286746, 286746, 286955, 287167, 287172, 287294, 287782, 287782, 287782, 288258, 288258, 288308, 288308, 288308, 288308, 288314, 288314, 288314, 288314, 289290, 289292, 289300, 289310, 289317, 289329, 289333, 289335, 289682, 289682, 291280, 291280, 291280, 291281, 291281, 291281, 291990, 291990, 291990, 291990, 293607, 293607, 293607, 295434, 295434, 295434, 295677, 295683, 295686, 295691, 295697, 295705, 295710, 295718, 295724, 295727, 295735, 297153, 297153, 297153, 298777, 298777, 298777, 299042, 299046, 299048, 299051, 299068, 299068, 299068, 299068, 299071, 300180, 300180, 300180, 301842, 301842, 301842, 303607, 303607, 303607, 304272, 304276, 304278, 304282, 304287, 304296, 304302, 304310, 304319, 304323, 304337, 304343, 304346, 304348, 304350, 304352, 304356, 305050, 305050, 305050, 305334, 305343, 305354, 305357, 305360, 305362, 305367, 305371, 305401, 305422, 305440, 305453, 306119, 306119, 306119, 307277, 307308, 307310, 307312, 307325, 307327, 307329, 307332, 307334, 307336, 307653, 307653, 307653, 308283, 308288, 308292, 308297, 308306, 308551, 308551, 308551, 309428, 309428, 309428, 310563, 310577, 310579, 310592, 310597, 310634, 310638, 310641, 310644, 310651, 310653, 311678, 311678, 311678, 311680, 311680, 311680, 311833, 311833, 311833, 312700, 312751, 312762, 312779, 314208, 314217, 314500, 314500, 314500, 315742, 315746, 316988, 316988, 316988, 317201, 317208, 317223, 317228, 317235, 317244, 317250, 317255, 318791, 318791, 318791, 319392, 319392, 319548, 319548, 319548, 319933, 319939, 319943, 319948, 319956, 319968, 319997, 320002, 320004, 320006, 320019, 320022, 320024, 320029, 320040, 320044, 320047, 320073, 320082, 321566, 321566, 321566, 322143, 322143, 322143, 322532, 322532, 322532, 323286, 323288, 323290, 323293, 323297, 323303, 323308, 323312, 323320, 323325, 323330, 323438, 323438, 323453, 323453, 323859, 323859, 323859, 325200, 325224, 325226, 325231, 325233, 325235, 325453, 325453, 326319, 326349, 326357, 326765, 326765, 326765, 328003, 328007, 328019, 329065, 329065, 329604, 329604, 329815, 329815, 329815, 330559, 330562, 330564, 330565, 330569, 330571, 330671, 330671, 330671, 331857, 331857, 331857, 331857, 332238, 332238, 332238, 332494, 332494, 332494, 333303, 333306, 333308, 333311, 333315, 333319, 333323, 333327, 334109, 334109, 334109, 335614, 335614, 335614, 336124, 336124, 336124, 336127, 336127])->where('is_deleted', 0)->get();
        foreach ($getAll as $val) {
            $getData = SamraddhBankDaybook::where('daybook_ref_id', $val->daybook_ref_id)->where('bank_id', '!=', $val->bank_id)->first();
            if (isset($getData->bank_id)) {
                AllHeadTransaction::where('daybook_ref_id', $val->daybook_ref_id)->update(['bank_id' => $getData->bank_id, 'bank_ac_id' => $getData->account_id]);
            }
        }
        print_r('success');
    }
    public function updateNotMatche2()
    {
        $getAll = AllHeadTransaction::whereIn('payment_mode', [1, 2])->whereIn('daybook_ref_id', [336132, 336132, 336132, 336913, 336919, 336924, 336931, 338142, 338142, 338142, 338290, 338290, 338290, 338931, 338931, 339047, 339054, 340156, 340156, 340156, 340156, 340660, 340660, 340660, 342521, 342523, 342530, 342532, 342534, 342535, 342539, 342544, 342554, 342558, 342569, 342571, 342578, 342582, 342584, 342591, 342593, 342595, 342597, 342603, 342605, 342608, 342615, 342621, 342626, 342628, 342630, 342632, 342635, 342638, 342641, 342645, 342647, 342651, 342655, 342660, 342671, 342675, 342690, 342705, 342709, 342724, 344011, 344011, 344011, 344140, 344147, 344153, 344155, 344157, 344160, 344162, 344164, 350102, 350102, 350102, 350591, 350597, 350634, 350634, 350634, 350634, 351667, 351667, 351667, 352036, 352048, 352062, 352081, 352159, 352159, 352168, 352168, 352168, 352168, 353318, 353318, 353318, 354092, 354092, 354093, 354093, 354093, 355307, 355311, 355322, 355335, 355341, 355343, 355346, 355348, 355351, 355361, 355373, 355396, 355400, 355409, 355417, 355436, 355470, 359394, 359394, 359394, 359683, 359688, 359693, 366364, 366681, 366681, 366681, 367447, 367450, 367453, 367455, 367458, 367625, 367625, 367625, 369361, 369369, 369381, 369388, 369400, 369410, 369415, 369423, 370893, 370893, 370893, 370943, 370979, 372302, 372307, 372319, 372322, 372327, 372330, 372338, 372348, 372351, 372357, 372363, 372368, 372375, 372399, 372417, 372440, 372448, 374174, 374174, 374174, 376029, 376029, 376029, 376826, 378017, 378017, 378017, 378563, 378563, 380003, 380003, 380080, 380080, 380080, 380401, 380401, 380401, 382326, 382326, 382326, 382522, 382524, 382526, 382528, 382530, 382532, 382534, 382536, 382538, 382540, 382542, 382546, 382548, 382550, 382552, 382554, 382556, 382558, 382560, 382562, 382564, 382566, 382568, 382570, 382572, 382574, 382576, 382578, 382580, 382582, 382584, 382586, 382588, 382590, 382592, 382594, 382596, 382598, 382600, 382602, 382604, 382606, 382608, 382610, 382612, 382614, 382616, 382618, 382620, 382622, 382624, 382630, 382632, 382635, 382637, 385093, 385093, 385093, 385443, 385742, 386533, 386542, 386551, 386563, 386570, 386844, 386856, 386870, 386879, 386889, 386892, 386894, 386973, 386975, 387049, 387075, 387077, 387079, 387081, 387083, 387086, 387088, 387091, 387095, 387098, 387120, 387139, 387205, 387210, 387213, 387215, 387224, 388098, 388098, 388098, 388720, 388725, 388730, 388732, 388736, 388742, 388751, 388760, 388772, 388779, 388787, 388793, 388796, 388804, 391731, 391731, 391731, 392312, 392320, 392329, 392345, 392359, 392367, 392381, 392389, 392401, 392414, 392421, 392426, 392435, 392438, 392441, 392446, 392451, 392460, 392476, 392483, 392512, 392525, 392543, 392560, 392564, 392582, 392588, 392595, 392601, 392605, 393638, 393638, 393638, 393759, 393762, 393768, 393776, 393779, 393781, 393784, 393794, 393796, 393801, 393805, 393812, 393822, 393826, 393837, 393849, 393860, 393862, 396146, 396146, 396146, 396281, 396337, 396340, 396344, 396346, 396941, 396941, 396941, 397810, 397810, 397866, 397869, 397873, 397881, 397887, 397890, 397894, 397899, 397902, 400069, 400079, 400083, 400087, 400092, 400097, 400103, 400111, 400117, 401144, 401164, 401170, 401174, 401180, 401185, 401189, 401193, 401198, 401203, 401210, 401215, 401223, 402887, 402889, 402891, 402895, 402900, 402910, 402918, 402927, 402931, 402935, 402938, 404032, 404435, 404435, 404435, 404926, 404936, 404941, 404948, 404955, 404958, 404960, 404962, 404964, 404966, 404984, 404991, 404998, 405009, 405011, 405015, 405024, 405027, 405029, 405035, 405042, 405048, 405051, 405060, 405065, 405069, 405075, 405082, 405094, 405103, 406416, 406416, 406416, 407892, 407892, 407892, 408478, 408480, 408482, 408484, 408486, 408488, 408495, 408498, 408500, 408502, 409035, 409035, 409737, 412141, 412143, 412145, 412146, 412148, 412150, 414262, 414262, 414262, 414623, 414623, 414638, 414638, 414656, 414656, 415510, 415510, 415510, 415700, 415700, 415700, 416568, 416572, 416576, 416584, 416595, 416600, 416605, 416613, 416617, 416620, 416634, 416636, 418507, 418507, 418507, 418648, 418655, 418660, 418662, 418664, 418666, 418674, 418679, 418693, 418708, 418716, 418727, 418745, 418768, 418778, 418871, 418881, 418893, 420158, 420158, 420158, 420351, 420356, 420358, 420361, 420363, 420365, 420368, 420373, 420375, 420378, 420381, 420386, 420394, 420401, 420407, 420410, 420412, 420416, 420420, 420425, 420433, 420440, 420447, 420451, 420457, 420487, 420492, 420502, 420507, 420510, 420512, 421831, 421831, 421831, 422014, 422018, 422021, 422032, 422039, 422042, 422047, 422062, 422070, 422074, 423541, 423541, 423541, 423777, 423786, 423794, 423807, 423816, 423839, 423843, 423848, 423857, 423867, 423872, 423876, 423878, 423880, 423885, 424692, 424692, 424692, 425701, 425703, 425707, 425710, 425712, 425714, 425716, 425718, 425720, 425722, 425724, 425726, 425728, 425731, 425733, 425735, 425737, 425740, 425743, 425746, 425749, 425751, 425756, 427670, 427670, 427670, 428611, 428614, 428618, 428621, 428624, 428630, 428633, 428636, 428641, 428643, 430072, 430072, 430072, 430099, 430099, 430129, 430131, 430133, 430244, 430262, 430274, 430283, 430380, 430387, 430392, 430394, 431688, 431688, 431688, 431806, 431808, 431811, 431814, 431816, 431819, 431821, 431823, 431826, 431828, 431833, 431838, 431843, 431846, 431849, 432825, 432825, 432825, 433516, 433522, 433555, 433568, 433575, 433580, 433584, 433590, 433606, 433611, 433614, 433623, 433627, 433631, 433639, 433645, 433655, 433661, 433665, 433670, 435246, 435251, 435256, 435262, 435266, 435272, 435278, 435281, 435285, 435288, 435293, 436967, 436967, 436967, 437317, 437323, 437326, 437328, 437330, 437334, 437337, 437340, 437347, 437352, 437360, 439974, 439974, 439974, 440356, 440358, 440360, 440362, 440364, 440366, 440369, 440371, 440373, 440377, 440379, 440380, 440383, 441543, 441543, 441543, 441911, 441911, 442323, 442336, 442350, 442366, 442396, 442412, 442431, 442458, 442475, 442491, 442507, 442530, 442551, 442580, 442598, 443463, 443463, 443463, 443720, 443723, 443728, 443731, 443733, 443737, 443739, 443741, 443743, 443745, 443747, 443749, 443751, 443756, 443758, 443761, 445675, 445677, 445681, 445684, 445688, 445695, 445700, 445704, 445708, 445746, 445750, 445754, 445763, 445770, 445777, 445792, 445805, 445814, 445826, 445838, 445851, 445856, 445864, 445877, 450060, 450060, 450060, 450064, 450064, 450064, 450176, 450176, 450487, 450494, 450501, 450507, 450512, 450517, 450525, 450529, 450530, 450534, 450537, 450539, 450545, 450550, 450564, 450566, 450568, 450571, 450585, 450594, 450596, 450605, 450610, 450649, 450654, 450662, 450666, 450675, 450681, 450695, 450706, 450714, 450716, 450716, 450719, 450762, 450771, 450779, 450788, 450798, 450826, 450832, 450843, 450851, 450889, 450896, 450916, 450918, 450922, 450942, 450946, 450950, 450955, 450981, 452782, 452782, 452782, 454092, 454100, 454104, 454108, 454110, 454112, 454114, 454116, 459269, 459273, 459278, 459280, 459282, 459286, 459294, 459302, 459336, 459357, 459371, 459375, 459379, 459381, 461670, 461670, 463491, 463499, 463505, 463511, 463522, 463531, 463538, 465559, 465562, 465568, 465596, 465612, 465620, 465627, 465631, 465637, 465643, 465650, 465661, 465665, 465671, 465676, 465681, 465689, 465695, 465698, 465702, 466579, 466579, 466579, 467141, 467150, 467154, 467157, 467161, 467164, 467171, 467175, 467183, 467192, 467208, 467215, 467220, 467224, 467231, 467239, 467245, 467248, 468287, 468287, 468287, 468877, 468897, 468912, 468918, 468924, 468932, 468936, 468942, 468946, 468952, 468957, 468967, 471498, 471498, 471498, 473225, 473225, 473225, 473804, 473808, 473814, 473824, 473835, 473840, 473842, 473850, 473857, 473864, 473870, 473873, 473876, 473879, 473884, 473889, 473891, 473897, 473901, 473910, 473912, 473918, 473922, 473924, 473926, 473928, 475416, 475416, 475416, 475823, 475826, 475829, 475831, 475834, 475837, 475839, 478104, 478104, 478104, 478499, 478505, 478510, 478515, 478519, 478521, 478523, 478528, 478532, 478537, 478542, 478544, 478798, 478798, 478803, 478803, 478810, 478810, 478822, 478822, 478836, 478836, 478846, 478846, 478846, 479222, 480480, 480482, 480484, 480488, 480492, 480497, 480499, 480502, 480505, 480509, 482137, 482137, 482137, 482521, 482526, 482554, 482609, 482621, 482626, 482633, 482641, 482643, 482645, 482671, 482677, 482680, 482687, 482689, 482691, 482694, 482698, 482701, 482705, 482709, 484223, 484223, 484223, 484311, 484314, 484316, 484321, 484328, 486603, 486603, 486603, 487022, 487025, 487135, 487138, 487141, 487143, 487152, 487158, 487166, 487176, 487179, 487182, 489244, 489244, 489244, 489454, 489461, 489467, 489474, 489479, 489484, 489487, 490961, 490961, 490961, 494371, 494371, 494371, 494954, 494959, 494963, 494965, 494967, 494970, 494975, 494978, 494980, 494982, 494984, 494986, 494989, 494994, 495004, 495010, 495016, 495020, 495026, 495032, 495037, 495054, 495058, 495064, 495066, 495069, 496277, 496277, 496277, 496577, 496579, 496586, 496589, 496592, 496595, 496599, 496604, 496610, 496617, 496619, 496621, 496627, 496629, 496633, 496635, 496803, 496803, 499103, 499103, 499103, 499626, 499631, 499633, 499635, 499638, 499640, 499648, 499664, 499670, 499672, 499676, 499685, 499688, 499697, 499705, 499708, 499722, 499729, 499734, 499738, 499743, 499750, 499754, 499756, 499759, 499763, 501529, 501529, 501529, 501596, 501600, 501602, 501604, 501606, 501608, 502715, 502715, 502715, 503281, 503285, 503289, 503295, 503305, 503310, 503310, 505152, 505152, 505152, 506119, 506122, 506128, 506143, 506156, 515865, 515865, 515865, 516251, 516265, 516443, 516461, 516469, 517281, 517281, 517281, 517281, 517459, 517459, 517459, 517687, 517690, 517710, 517714, 517717, 517720, 519156, 519156, 519156, 523900, 523900, 523900, 525244, 525244, 527018, 527018, 527018, 527099, 527099, 529032, 529032, 529032, 530822, 530822, 530822, 531612, 531617, 531624, 531633, 531647, 531657, 532032, 532032, 532032, 533985, 533985, 533985, 534740, 534743, 534748, 534753, 534756, 534759, 536365, 536365, 536365, 540048, 540048, 540981, 540981, 540981, 541129, 541145, 541151, 541158, 541161, 541169, 541174, 541179, 541184, 541187, 541190, 541195, 541198, 541200, 541203, 541205, 541551, 541551, 541551, 542891, 542891, 543099, 543099, 543099, 543181, 543181, 543181, 543409, 543409, 543409, 545811, 545817, 545822, 545829, 545833, 548198, 548198, 548198, 550865, 550865, 550865, 553167, 553167, 553167, 555824, 555824, 555824, 557438, 557438, 560243, 560243, 560245, 560245, 560339, 560344, 560349, 560352, 560356, 560443, 560443, 560659, 560659, 560659, 560659, 562690, 562690, 562690, 563018, 563018, 563038, 563043, 563046, 563055, 563060, 568000, 568003, 568007, 568009, 568011, 574938, 574938, 574938, 576519, 576519, 576519, 576863, 576867, 576869, 576875, 576883, 576885, 577071, 577075, 577080, 577089, 577758, 577758, 577758, 577942, 577946, 577954, 577960, 577968, 577977, 578881, 578881, 578881, 580834, 580834, 580834, 582105, 582105, 582105, 582122, 582122, 582122, 582433, 582440, 582444, 582447, 582452, 582467, 582470, 582474, 582479, 582488, 584716, 584716, 584716, 587466, 587479, 587483, 587488, 587495, 587500, 587687, 587687, 590495, 590495, 590495, 592857, 592857, 592857, 595058, 595058, 595058, 596594, 596594, 598087, 598087, 598087, 598765, 598765, 598765, 598766, 598766, 598766, 598767, 598767, 598767, 598784, 598784, 604998, 604998, 604998, 606934, 606934, 607209, 607209, 608950, 608950, 608950, 127155, 610610, 610610, 610610, 613855, 613855, 613855, 615057, 615057, 615057, 615294, 615299, 615301, 615304, 615309, 618182, 618182, 618182, 619791, 619791, 619791, 620221, 620221, 620221, 622473, 622473, 622473, 625586, 625588, 625593, 625597, 625601, 625604, 625762, 625767, 625771, 625775, 625779, 625781, 625785, 626575, 626575, 626575, 628947, 628947, 628947, 629595, 629595, 629595, 631917, 631922, 631926, 633187, 633187, 633187, 633935, 633935, 634327, 634327, 634327, 634994, 634994, 636633, 636633, 636633, 638361, 638361, 638361, 639268, 639268, 639268, 639271, 639271, 639271, 643609, 643609, 643609, 644818, 644818, 644818, 646061, 646061, 646061, 649249, 649249, 649249, 649699, 649699, 649699, 653928, 653928, 653928, 654784, 654784, 659194, 659194, 659194, 660561, 660561, 660617, 660617, 661545, 661545, 661545, 662540, 662540, 662540, 662540, 665504, 665815, 665815, 665815, 665859, 665859, 665859, 665943, 665946, 665952, 665958, 665962, 665968, 665973, 665975, 665978, 665980, 666112, 666117, 666125, 667728, 667728, 667728, 671979, 671979, 672440, 672440, 672440, 673382, 673382, 673382, 674197, 674197, 674197, 675714, 675714, 675714, 675936, 675946, 675956, 675965, 675979, 676029, 680432, 680432, 680432, 680845, 680845, 688518, 688518, 688518, 688519, 688519, 688519, 690202, 690202, 690202, 691355, 691355, 691355, 694354, 694354, 694354, 695118, 695118, 695118, 697231, 697231, 697231, 697443, 697443, 697443, 697522, 697522, 697522, 699587, 699587, 699635, 699635, 699635, 700022, 700022, 700022, 700291, 700294, 700298, 700301, 700303, 702158, 702158, 702158, 702568, 702568, 702568, 702604, 702604, 702604, 704477, 704477, 704477, 704962, 704962, 704962, 706540, 706540, 706759, 706759, 706879, 706879, 706879, 709394, 709394, 709394, 713173, 713173, 713173, 714398, 714398, 715726, 715726, 715726, 717457, 717457, 717457, 718460, 718460, 718460, 719495, 719495, 719495, 719517, 719517, 719517, 720269, 720269, 723040, 723040, 723040, 724349, 724351, 724353, 724356, 726996, 726996, 726996, 727281, 727281, 729343, 729343, 729343, 730993, 730993, 730993, 731009, 731009, 731009, 731042, 731042, 732156, 732156, 732156, 736672, 736672, 736672, 737214, 737214, 737214, 739069, 739073, 739075, 739078, 739086, 739096, 739098, 739100, 739128, 739143, 739145, 739150, 739154, 739158, 739805, 739805, 739805, 741218, 741218, 741218, 746293, 746293, 746293, 747292, 747295, 747304, 747310, 747339, 748900, 748900, 748900, 750363, 750363, 750363, 753009, 753009, 753009, 755711, 755716, 755722, 755728, 755731, 755733, 755739, 755745, 755749, 755773, 755777, 755779, 755781, 757196, 757196, 757197, 757197, 757197, 757276, 757276, 757276, 761031, 761031, 761031, 761634, 761634, 764501, 764501, 764509, 764509, 767905, 767905, 767905, 768215, 768215, 770052, 770052, 772616, 772616, 772616, 772619, 772619, 772619, 773489, 773489, 775438, 775438, 775542, 775542, 775542, 776367, 776372, 776376, 776379, 776384, 778513, 778513, 781291, 781291, 781347, 781347, 782875, 782875, 782875, 783532, 783536, 783541, 783543, 783546, 783549, 783556, 784970, 784970, 784970, 788848, 788848, 788848, 789966, 789966, 789966, 790085, 790085, 790085, 792146, 792146, 792146, 792459, 792459, 792459, 800873, 800873, 800873, 802731, 802731, 802731, 805855, 805855, 805855, 806410, 806410, 806410, 806415, 806415, 806415, 808539, 808539, 808539, 808539, 810556, 810556, 810556, 810686, 810686, 811017, 811017, 811017, 814653, 814653, 814653, 815226, 815226, 815226, 815632, 815632, 815632, 816055, 816055, 816055, 817956, 817956, 817956, 819627, 819627, 819627, 821364, 821364, 821369, 821369, 821587, 821587, 821587, 823232, 823232, 823232, 823232, 825104, 825104, 825104, 826631, 826631, 826631, 828136, 828136, 828136, 828701, 828701, 828701, 830880, 830880, 830880, 833606, 833606, 833606, 836288, 836288, 836288, 836289, 836289, 836289, 836290, 836290, 836290, 838590, 838590, 838590, 841162, 841162, 841162, 844277, 844277, 844277, 845131, 845131, 845986, 845986, 846065, 846065, 846069, 846069, 846069, 847716, 847716, 847716, 848781, 848781, 848781, 848781, 849160, 849166, 849171, 849176, 849182, 849186, 850888, 850888, 850888, 853516, 853516, 853516, 855360, 855360, 855360, 856028, 856028, 856028, 857946, 857946, 857946, 858240, 858240, 858240, 858866, 858866, 880100, 880100, 880100, 880103, 880103, 880103, 880120, 880120, 880120, 880811, 880811, 880811, 883568, 883568, 883571, 883571, 884208, 884208, 884208, 884209, 884209, 884209, 884644, 884644, 884644, 884898, 884898, 884898, 886994, 886994, 886994, 887525, 887525, 887525, 889545, 889545, 889545, 890304, 890304, 890304, 890482, 890482, 890482, 892955, 892955, 892955, 893879, 893879, 896136, 896136, 896136, 898457, 898471, 898477, 898481, 898486, 898493, 898501, 899900, 899900, 899900, 903580, 903580, 903580, 905197, 905197, 905197, 905217, 905217, 905217, 906327, 906327, 906327, 906683, 906683, 906683, 907453, 907453, 907453, 907453, 909083, 909083, 909083, 909297, 909297, 909297, 909297, 909318, 909318, 909318, 909318, 909632, 909632, 909632, 910182, 910182, 910182, 911440, 911447, 911459, 911468, 911476, 911491, 911498, 911506, 911513, 911520, 911531, 913637, 913637, 913637, 916186, 916186, 920626, 920626, 920626, 929657, 929657, 929657, 930337, 930337, 930337, 935012, 935012, 935012, 936681, 936681, 936681, 940348, 940348, 940348, 943701, 943701, 943701, 948335, 948335, 948335, 951205, 951205, 951205, 952703, 952703, 952703, 955510, 955510, 955510, 955550, 955550, 955550, 958637, 958637, 958637, 959201, 959201, 959201, 961398, 961398, 961404, 961404, 962323, 962330, 962334, 962336, 962338, 962340, 962344, 962346, 962349, 962351, 962353, 962355, 962357, 962359, 962690, 964963, 964963, 964963, 967343, 967343, 967343, 970383, 970383, 970383, 973011, 973011, 973011, 973770, 973770, 973770, 974283, 974283, 974283, 974283, 976632, 976632, 976632, 979052, 979052, 979052, 981679, 981679, 982269, 982269, 982269, 983729, 983738, 983747, 983754, 983763, 984753, 984753, 984753, 985930, 985930, 985930, 985932, 985932, 985932, 986752, 986752, 986752, 987221, 987221, 987221, 992664, 992664, 992664, 992727, 992727, 992727, 997031, 997031, 997031, 997039, 997039, 997039, 997121, 997121, 997208, 997208, 1002858, 1002858, 1002858, 1004043, 1004049, 1004053, 1004060, 1004065, 1010748, 1010748, 1010748, 1010768, 1010768, 1010768, 1010899, 1010899, 1010899, 1010899, 1013227, 1013227, 1013227, 1015173, 1015173, 1015173, 1015900, 1015900, 1015900, 1021191, 1021191, 1021191, 1021197, 1021197, 1021197, 1021215, 1021215, 1021215, 1021445, 1021445, 1021445, 1023855, 1023855, 1026494, 1026494, 1026623, 1026623, 1027393, 1027393, 1027393, 1030388, 1030388, 1031109, 1031109, 1031109, 1032100, 1032100, 1032100, 1034661, 1034661, 1034661, 1036708, 1036708, 1036708, 1036708, 1038866, 1038866, 1038866, 1038876, 1038876, 1038876, 1039969, 1039969, 1039969, 1040425, 1040425, 1042814, 1042814, 1042814, 1044081, 1044081, 1044081, 1046634, 1046634, 1046634, 1046635, 1046635, 1046635, 1050745, 1050745, 1051889, 1051889, 1051889, 1053232, 1053232, 1053232, 1054941, 1054941, 1054941, 1056550, 1056550, 1056550, 1056550, 1057007, 1057007, 1057007, 1058428, 1058428, 1058901, 1058901, 1058901, 1064775, 1064775, 1064775, 1066658, 1066658, 1066658, 1072823, 1072823, 1072823, 1072824, 1072824, 1072824, 1405606, 1405606, 1405606, 1406879, 1406879, 1406879, 1407093, 1407093, 1407128, 1407128, 1407316, 1407316, 1407316, 1407681, 1407691, 1407696, 1407700, 1407707, 1407715, 1407729, 1407733, 1407765, 1407773, 1410237, 1410237, 1410237, 1410568, 1410568, 1410568, 1410704, 1410704, 1412734, 1412734, 1412734, 1414268, 1414268, 1416550, 1416550, 1416550, 1419224, 1419224, 1419224, 1419250, 1419250, 1419250, 1443749, 1443749, 1443749, 1445182, 1445182, 1445210, 1445210, 1445213, 1445213, 1445247, 1445247, 1446521, 1446521, 1446521, 1446972, 1446972, 1450458, 1450458, 1451089, 1451089, 1451106, 1451106, 1451116, 1451116, 1451121, 1451121, 1451129, 1451129, 1451159, 1451159, 1451163, 1451163, 1451187, 1451187, 1451192, 1451192, 1451203, 1451203, 1451225, 1451225, 1451573, 1451573, 1451718, 1451718, 1451793, 1451793, 1452018, 1452018, 1452018, 1452453, 1452453, 1454685, 1454685, 1454685, 1460499, 1460499, 1463958, 1463958, 1464020, 1464020, 1468708, 1468708, 1468708, 1468846, 1468846, 1468846, 1471244, 1471244, 1471244, 1472453, 1472471, 1472506, 1472527, 1472539, 1479683, 1479683, 1479683, 1481552, 1481552, 1481552, 1483055, 1483055, 1483055, 1486511, 1486511, 1486511, 1486511, 1490442, 1490442, 1490442, 1491762, 1491762, 1492646, 1492646, 1492646, 1493211, 1493211, 1496166, 1496166, 1496166, 1496177, 1496177, 1496177, 1496178, 1496178, 1496178, 1497427, 1497427, 1497427, 1498292, 1498292, 1498292, 1498292, 1499497, 1499502, 1499514, 1499519, 1499534, 1499537, 1499550, 1499552, 1499556, 1499561, 1500038, 1500038, 1500038, 1503429, 1503429, 1503429, 1508700, 1508700, 1508700, 1513676, 1513676, 1513676, 1513680, 1513680, 1513680, 1527345, 1527345, 1527345, 1527351, 1527351, 1527351, 1527352, 1527352, 1527352, 1533648, 1533648, 1533648, 1533656, 1533656, 1533656, 1557887, 1557887, 1557887, 1557888, 1557888, 1557888, 1558944, 1558944, 1558944, 1561671, 1561675, 1561697, 1561701, 1561703, 1561706, 1561719, 1561721, 1561723, 1561745, 1561750, 1561765, 1561801, 1561803, 1561810, 1561812, 1561816, 1561820, 1563843, 1563843, 1563843, 1563846, 1563846, 1563846, 1563850, 1563850, 1563850, 1565950, 1565950, 1565950, 1571281, 1571281, 1571281, 1571282, 1571282, 1571282, 1581900, 1581900, 1581900, 1581913, 1581913, 1581913, 1581914, 1581914, 1581914, 1581916, 1581916, 1581916, 1581917, 1581917, 1581917, 1581918, 1581918, 1581918, 1584115, 1584115, 1584117, 1584117, 1584122, 1584122, 1584126, 1584126, 1584129, 1584129, 1584131, 1584131, 1584141, 1584141, 1587675, 1587675, 1588824, 1588824, 1588824, 1589480, 1589480, 1592113, 1592113, 1592113, 1592115, 1592115, 1592116, 1592116, 1596291, 1596291, 1596291])->where('is_deleted', 0)->get();
        foreach ($getAll as $val) {
            $getData = SamraddhBankDaybook::where('daybook_ref_id', $val->daybook_ref_id)->where('bank_id', '!=', $val->bank_id)->first();
            if (isset($getData->bank_id)) {
                AllHeadTransaction::where('daybook_ref_id', $val->daybook_ref_id)->update(['bank_id' => $getData->bank_id, 'bank_ac_id' => $getData->account_id]);
            }
        }
        print_r('success');
    }
    public function deleteOldEmiDueLoan()
    {
        \App\Models\Memberloans::where('status', 4)->whereNotIn('loan_type', [3])->where('company_id', 1)->chunk(2000, function ($datas) {
            foreach ($datas as $data) {
                \App\Models\LoanDayBooks::where('account_number', $data->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->chunk(2000, function ($records) {
                    foreach ($records as $i => $value) {
                        $d = Memberloans::where('account_number', $value->account_number)->first();
                        // $d->update(['accrued_interest'=>0]);
                        $date = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->whereId($value->id)->orderBY('created_at', 'desc')->first();
                        \App\Models\AllHeadTransaction::where('daybook_ref_id', $date->daybook_ref_id)->update(['is_deleted' => 1, 'is_query' => 1]);
                        \Log::info("Delete Emi !" . $date->account_number);
                    }
                });
            }
        });
    }
    public function deleteEmiDataLoanDaybook()
    {
        $data = \App\Models\LoanDayBooks::where('is_deleted', 0)->where('account_number', '706790700001')->whereDate('created_at', '>=', '2023-07-28')->pluck('id', 'daybook_ref_id');
        // foreach($data as $key => $val)
        // {
        \App\Models\AllHeadTransaction::whereIn('daybook_ref_id', $data)->update(['is_deleted' => 1, 'is_query' => 1]);
        // }
    }
    public function deleteEmiDataEmiLoan()
    {
        $data = \App\Models\LoanEmiNew1::where('is_deleted', '0')->where('loan_id', '6412')->whereDate('emi_date', '>=', '2023-07-28')->where('loan_type', 7)->pluck('id', 'loan_id');
        foreach ($data as $key => $val) {
            \App\Models\AllHeadTransaction::where('type', 5)->whereIn('sub_type', [546, 545])->where('type_id', $key)->where('type_transaction_id', $val)->update(['is_deleted' => 1, 'is_query' => 1]);
            \App\Models\LoanEmiNew1::where('id', $key)->update(['is_deleted' => '1']);
        }
    }
    public function executeCron()
    {
        $currentDate = date('Y-m-d', strtotime('2023-08-29'));
        $account_number = '706790700001';
        $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $account_number, 1]);
    }
    public function updateDaybookTransaction()
    {
        $allRecorsds = Daybook::where('account_no', 'R-103523001475')->where('transaction_type', '<>', 19)->where('is_deleted', 0)->get();
        $openingBalance = 0;
        foreach ($allRecorsds as $value) {
            if ($value->deposit > 0) {
                $openingBalance += $value->deposit;
            }
            if ($value->withdrawal > 0) {
                $openingBalance -= $value->withdrawal;
            }
            Daybook::where('id', $value->id)->update(['opening_balance' => $openingBalance]);
        }
        print_r("Daybook transaction");
    }
}