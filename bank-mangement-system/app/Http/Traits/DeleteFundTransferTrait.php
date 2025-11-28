<?php
namespace App\Http\Traits;
use DB;
use App\Services\ImageUpload;
use Spatie\Permission\Models\Permission;
use \App\Models\FundTransfer;
use \App\Models\Branch;
use \App\Models\Files;
use \App\Models\AllHeadTransaction;
use \App\Models\BranchDaybook;
use \App\Models\SamraddhCheque;
use \App\Models\SamraddhChequeIssue;
use \App\Models\SamraddhBankDaybook;
use \App\Models\FundTransferLog;
trait DeleteFundTransferTrait
{
    public function delete($request)
    {
        // for delete data from frund tranfer transaction (branch to ho / bank to bank).
        $delete = ['is_deleted' => '1'];
        // Retrieve FundTransfer details based on the given ID
        $ftDetails = FundTransfer::whereId($request->id)->first();
        // Determine the FundTransfer type based on the transfer_type value
        $FundTransfertype = $ftDetails->transfer_type == 1 ? 'Bank To Bank' : 'Branch To Head Office';
        // Retrieve the bank_slip_id from the FundTransfer details
        $fileId = $ftDetails->bank_slip_id;
        // Retrieve the details of the Branch based on the branch_id in FundTransfer details
        $branch = Branch::whereId($ftDetails->branch_id)->first();
        // Determine the role_id of the authenticated user and set the userType variable accordingly
        $userType = auth()->user()->role_id == 3 ? '2' : '1';
        // Determine the user type (Branch or Admin) based on the role_id of the authenticated user
        $u = auth()->user()->role_id == 3 ? 'Branch' : 'Admin';
        // Retrieve the username of the authenticated user
        $user = auth()->user()->username;
        // Get the current date and format it to 'd/m/Y' format
        $currentDateTime = date('d/m/Y');
        DB::beginTransaction();
        try {
            // Create an array to store log data
            $logDate = [
                'funds_transfer_id' => $request->id,
                'type' => $ftDetails->transfer_type, // 0 : branch to ho , 1 : bank to bank
                'old_value' => json_encode(['id' => $request->id, 'is_deleted' => '0', 'status' => $ftDetails->status]),
                'new_value' => json_encode(['id' => $request->id, 'is_deleted' => '1', 'status' => '3']),
                'amount' => $ftDetails->amount,
                'title' => "$FundTransfertype Payment Request",
                'remark' => $request->remark ?? '',
                'created_by' => auth()->user()->role_id == 3 ? '2' : '1',
                'created_by_id' => auth()->user()->id,
                'created_at' => date('Y-m-d ' . date('H:i:s') . "", strtotime(convertdate($request->created_at))),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            // created request
            if ($ftDetails->status == 0) {
                // If the status is 0, update the log data and set the new values
                $logDate['old_value'] = json_encode(['id' => $ftDetails->id, 'is_deleted' => '0', 'status' => $ftDetails->status]);
                $logDate['new_value'] = json_encode(['id' => $ftDetails->id, 'is_deleted' => '1', 'status' => '3']);
                $update = ['is_deleted' => '1', 'status' => '3', 'remark' => $request->remark];
                $msg = "Fund Transfer $FundTransfertype Request Deleted Successfully !";
            } elseif ($ftDetails->status == 1) {
                // If the status is 1
                if ($ftDetails->transfer_type == 0) {
                    // If the transfer type is 0
                    // Get the file path for the given file ID
                    $filename = Files::whereId($fileId)->value('file_path');
                    ImageUpload::deleteImage($filename);
                    // Delete the image using ImageUpload class
                    // Get the daybook_ref_id for the given type_id, considering specific sub_types and types
                    $daybookrefId = AllHeadTransaction::where('type_id', $request->id)
                        ->where(function ($q) {
                            $q->whereIn('sub_type', [70, 71])->orWhereIn('type', [7, 8]);
                        })
                        ->value('daybook_ref_id');
                    // Update BranchDaybook with the delete flag based on daybook_ref_id
                    BranchDaybook::where('daybook_ref_id', $daybookrefId)->update($delete);
                } elseif ($ftDetails->transfer_type == 1) {
                    // If the transfer type is 1
                    // Get the daybook_ref_id for the given type_transaction_id, considering specific sub_types and types
                    $daybookrefId = AllHeadTransaction::where('type_transaction_id', $request->id)
                        ->where(function ($q) {
                            $q->whereIn('sub_type', [81, 82])->orWhereIn('type', [7, 8]);
                        })
                        ->value('daybook_ref_id');
                    if ($ftDetails->btb_tranfer_mode == 0) {
                        // If the btb_tranfer_mode is 0
                        // Update SamraddhCheque with is_use and status based on the cheque_no obtained from getSamraddhChequeData
                        SamraddhCheque::where('cheque_no', getSamraddhChequeData($ftDetails->from_cheque_utr_no)->cheque_no)
                            ->update([
                                'is_use' => '0',
                                'status' => '1'
                            ]);
                        // Update SamraddhChequeIssue with status based on the cheque_id, type, sub_type, and type_id
                        SamraddhChequeIssue::where('cheque_id', getSamraddhChequeData($ftDetails->from_cheque_utr_no)->id)
                            ->where('type', 1)
                            ->where('sub_type', 11)
                            ->where('type_id', $ftDetails->id)
                            ->update(['status' => '0']);
                    }
                    if ($ftDetails->transfer_type === 1 && $ftDetails->to_cheque_utr_no) {
                        // If the transfer_type is 1 and to_cheque_utr_no exists
                        // Update SamraddhCheque with is_use and status based on the cheque_no obtained from getSamraddhChequeData
                        SamraddhCheque::where('cheque_no', getSamraddhChequeData($ftDetails->to_cheque_utr_no)->cheque_no)
                            ->update([
                                'is_use' => '0',
                                'status' => '1'
                            ]);
                        $cheque_no = getSamraddhChequeData($ftDetails->to_cheque_utr_no)->cheque_no;
                        $description = "Cheque No. $cheque_no was cleared by $user via the $u Panel on $currentDateTime for Fund Transfer payment.";
                        // Call cheque_logs helper function with the given parameters to log the cheque renewal
                        cheque_logs(2, getSamraddhChequeData($ftDetails->to_cheque_utr_no)->id, 'Cheque Renew', $description, json_encode(['is_use' => '0', 'status' => '1']), json_encode(['is_use' => '1', 'status' => '3']), $status = 1, $daybookrefId, $userType, auth()->user()->id, date('Y-m-d ' . date('H:i:s') . "", strtotime(convertdate($request->created_at))));
                    }
                }
                // After that Update the AllHeadTransaction table with the delete flag based on daybook_ref_id
                AllHeadTransaction::where('daybook_ref_id', $daybookrefId)->update($delete);
                // Update the SamraddhBankDaybook table with the delete flag based on daybook_ref_id
                SamraddhBankDaybook::where('daybook_ref_id', $daybookrefId)->update($delete);
                // Set the values for update array to mark the payment as not deleted and set the status and remark
                $update = ['is_deleted' => '0', 'status' => '0', 'remark' => ''];
                // Set the success message for deleting the payment
                $msg = "Fund Transfer $FundTransfertype payment Deleted Successfully !";
            } else {
                $msg = "Fund Transfer $FundTransfertype payment Request Already Deleted !";
            }
            // Set the title for the log entry based on the payment status
            $logDate['title'] = 'Deleted ' . $FundTransfertype . ' Payment ' . ($ftDetails->status == '1' ? 'Transaction' : 'Request');
            // Update the $ftDetails model with the $update values
            $ftDetails->update($update);
            // Create a new FundTransferLog entry with the $logDate values
            FundTransferLog::create($logDate);
            if ($ftDetails->transfer_type == 0) {
                // Call the branchbalancecrone function with the manager_id of the branch and all permissions
                branchbalancecrone($branch->manager_id, Permission::all());
            }
            DB::commit();
            $type = 'success';
        } catch (\Exception $e) {
            DB::rollback();
            $type = 'alert';
            $msg = ' ' . $e->getMessage() . ' - ' . $e->getLine() . ' - ' . $e->getCode() . ' ';
        }
        $d = [
            'type' => $type,
            'msg' => $msg
        ];
        $response = json_encode($d);
        // Send response to controller back
        return $response;
    }
}