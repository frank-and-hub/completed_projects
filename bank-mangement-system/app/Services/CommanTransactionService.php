<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\{
   AdvancedTransaction,
   BranchDaybook,
   Member,
   MemberBankDetail,
   MemberIdProof,
   MemberNominee,
   SamraddhBank,
   VendorTransaction,
   Receipt,
   ReceiptAmount,
   AllHeadTransaction
};

class CommanTransactionService
{

   public function insertMember($request, $type, $getBranchId, $panelType)
   {


      // pass fa id 1 for member
      $getfaCode = getFaCode(1);
      $faCode = $getfaCode->code;
      $branch_id = ($getBranchId == null) ? $request['branch_id'] : $getBranchId->id;

      // pass role_id(5 for member),branch_id,fa_code
      $getMiCode = getLastMiCode(5, $branch_id, $faCode);

      if (!empty($getMiCode)) {
         if ($getMiCode->mi_code == 9999998) {
            $miCodeAdd = $getMiCode->mi_code + 2;
         } else {
            $miCodeAdd = $getMiCode->mi_code + 1;
         }
      } else {
         $miCodeAdd = 1;
      }

      $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
      $getBranchCode = getBranchCode($branch_id);
      $branchCode = $getBranchCode->branch_code;

      // genarate Member id 
      $getmemberID = $branchCode . $faCode . $miCode;
      // save member details

      $branchMi = $branchCode . $miCode;

      // get data function
      if ($type == 'create') {
         $data['form_no'] = $request['form_no'];
         $data['re_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['application_date'])));
      }

      $data['first_name'] = $request['first_name'];
      $data['associate_id'] = $request['associate_id'];
      $data['associate_code'] = $request['associate_code'];
      $data['last_name'] = $request['last_name'];
      $data['mobile_no'] = $request['mobile_no'];
      $data['email'] = $request['email'];
      $data['dob'] = date("Y-m-d", strtotime(convertDate($request['dob'])));
      $data['age'] = $request['age'];
      if (isset($request['gender'])) {
         $data['gender'] = $request['gender'];
      }
      $data['annual_income'] = $request['annual_income'];
      $data['occupation_id'] = $request['occupation'];
      if (isset($request['marital_status'])) {
         $data['marital_status'] = $request['marital_status'];
      }
      $data['anniversary_date'] = date("Y-m-d", strtotime(convertDate($request['anniversary_date'])));
      $data['father_husband'] = $request['f_h_name'];
      $data['address'] = $request['address'];
      $data['state_id'] = $request['state_id'];
      $data['district_id'] = $request['district_id'];
      $data['city_id'] = $request['city_id'];
      $data['village'] = $request['village_name'];
      $data['pin_code'] = $request['pincode'];
      $data['religion_id'] = $request['religion'];
      $data['mother_name'] = $request['mother_name'];

      $data['special_category_id'] = $request['special_category'];
      $data['status'] = 1;
      $data['created_at'] = $request['created_at'];

      $data['role_id'] = 5;
      $data['branch_id'] = $branch_id;
      $data['branch_code'] = $branchCode;
      $data['branch_mi'] = $branchMi;
      $data['member_id'] = $getmemberID;
      $data['mi_code'] = $miCode;
      $data['fa_code'] = $faCode;
      $data['created_at'] = $request['created_at'];
      $member = Member::create($data);
      return $member;
   }

   public function getBankData($request, $type, $memberId)
   {
      if ($type == 'create') {
         $data['member_id'] = $memberId;
         $data['bank_name'] = $request['bank_name'];
         $data['branch_name'] = $request['bank_branch_name'];
         $data['account_no'] = $request['bank_account_no'];
         $data['ifsc_code'] = $request['bank_ifsc'];
         $data['address'] = $request['bank_branch_address'];
         $data['created_at'] = $request['created_at'];
         return MemberBankDetail::create($data);
      }
   }
   public function getNomineeData($request, $type, $memberId)
   {
      if ($type == 'create') {
         $data['member_id'] = $memberId;
         $data['name'] = $request['nominee_first_name'];
         $data['relation'] = $request['nominee_relation'];
         if (isset($request['nominee_gender'])) {
            $data['gender'] = $request['nominee_gender'];
         }
         if (isset($request['nominee_dob']) && $request['nominee_dob'] != '') {
            $data['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['nominee_dob'])));
         }
         $data['age'] = $request['nominee_age'];
         $data['mobile_no'] = $request['nominee_mobile_no'];
         if (isset($request['is_minor']) && !empty($request['is_minor'])) {
            $data['is_minor'] = $request['is_minor'];
            $data['parent_name'] = $request['parent_nominee_name'];
            $data['parent_no'] = $request['parent_nominee_mobile_no'];
         } else {
            $data['is_minor'] = 0;
         }
         $data['created_at'] = $request['created_at'];
         return MemberNominee::create($data);
      }
   }
   public function getIdProofData($request, $type, $memberId)
   {
      if ($type == 'create') {
         $data['member_id'] = $memberId;
         $data['first_id_type_id'] = $request['first_id_type'];
         $data['first_id_no'] = $request['first_id_proof_no'];
         $data['first_address'] = $request['first_address_proof'];
         $data['second_id_type_id'] = $request['second_id_type'];
         $data['second_id_no'] = $request['second_id_proof_no'];
         $data['second_address'] = $request['second_address_proof'];
         $data['created_at'] = $request['created_at'];
         MemberIdProof::create($data);
      }
   }
   public function createPaymentRecipt($tranID, $ssbAccountId, $memberId, $branch_id, $branchCode, $amountArray, $typeArray, $receipts_for, $account_no)
   {

      $globaldate = Session::get('created_at');
      $data['transaction_id'] = $tranID;
      $data['receipt_by_id'] = $ssbAccountId;
      $data['account_no'] = $account_no;
      $data['member_id'] = $memberId;
      $data['branch_id'] = $branch_id;
      $data['branch_code'] = $branchCode;
      $data['created_by_id'] = Auth::user()->id;
      $data['created_by'] = 2;
      $data['receipts_for'] = $receipts_for;
      $data['created_at'] = $globaldate;
      $recipt = Receipt::create($data);
      $recipt_id = $recipt->id;

      foreach ($amountArray as $key => $option) {
         $data_amount['receipt_id'] = $recipt_id;
         $data_amount['amount'] = $option;
         $data_amount['type_label'] = $typeArray[$key];
         $data_amount['currency_code'] = 'INR';
         $data['receipts_for'] = $receipts_for;
         $data['created_at'] = $globaldate;
         $re = ReceiptAmount::create($data_amount);
      }
      return $recipt_id;
   }
   public function createBranchDayBookReferenceNew($amount, $created_at)
   {
      if(isset($created_at) && $created_at){
         $globaldate = $created_at;
      }else{
         $globaldate = Session::get('created_at');
      }

      $data['amount'] = $amount;
      $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
      $data['entry_time'] = date("H:i:s", strtotime(convertDate($globaldate)));
      $data['created_at'] = date("Y-m-d", strtotime(convertDate($globaldate)));
      $data['updated_at'] = date("Y-m-d", strtotime(convertDate($globaldate)));
      $transcation = \App\Models\BranchDaybookReference::create($data);
      return $transcation->id;
   }
   public function createBranchDayBookNew($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $companyId, $cheque_id = NULL, $cheque_no = NULL)
   {
      if(isset($created_at) && $created_at){
         $globaldate = $created_at;
      }else{
         $globaldate = Session::get('created_at');
      }
      $data['daybook_ref_id'] = $daybook_ref_id;
      $data['branch_id'] = $branch_id;
      $data['type'] = $type;
      $data['sub_type'] = $sub_type;
      $data['type_id'] = $type_id;
      $data['type_transaction_id'] = $type_transaction_id;
      $data['associate_id'] = $associate_id;
      $data['member_id'] = $member_id;
      $data['branch_id_to'] = $branch_id_to;
      $data['branch_id_from'] = $branch_id_from;
      $data['amount'] = $amount;
      $data['description'] = $description;
      $data['description_dr'] = $description_dr;
      $data['description_cr'] = $description_cr;
      $data['payment_type'] = $payment_type;
      $data['payment_mode'] = $payment_mode;
      $data['currency_code'] = $currency_code;
      $data['v_no'] = $v_no;
      $data['ssb_account_id_from'] = $ssb_account_id_from;
      $data['transction_no'] = $transction_no;
      $data['entry_date'] = $entry_date;
      $data['entry_time'] = $entry_time;
      $data['cheque_id'] = $cheque_id;
      $data['cheque_no'] = $cheque_no;
      $data['created_by'] = $created_by;
      if ($created_by == 3) {
         $data['app_login_user_id'] = $created_by_id;
         $data['is_app'] = 1;
      }
      $data['created_by_id'] = $created_by_id;
      $data['created_at'] = $globaldate;
      $data['updated_at'] = $updated_at;
      $data['company_id'] = $companyId ?? 0;
      $transcation = \App\Models\BranchDaybook::create($data);
      return $transcation->id;
   }
   public function headTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId, $isApp = null)
   {
      if(isset($created_at) && $created_at){
         $globaldate = $created_at;
      }else{
         $globaldate = Session::get('created_at');
      }
      $data['daybook_ref_id'] = $daybook_ref_id;
      $data['branch_id'] = $branch_id;
      $data['bank_id'] = $bank_id;
      $data['bank_ac_id'] = $bank_ac_id;
      $data['head_id'] = $head_id;
      $data['type'] = $type;
      $data['sub_type'] = $sub_type;
      $data['type_id'] = $type_id;
      $data['type_transaction_id'] = $type_transaction_id;
      $data['associate_id'] = $associate_id;
      $data['member_id'] = $member_id;
      $data['branch_id_to'] = $branch_id_to;
      $data['branch_id_from'] = $branch_id_from;
      $data['amount'] = $amount;
      $data['description'] = $description;
      $data['payment_type'] = $payment_type;
      $data['payment_mode'] = $payment_mode;
      $data['currency_code'] = $currency_code;
      $data['jv_unique_id'] = $jv_unique_id;
      $data['v_no'] = $v_no;
      $data['ssb_account_id_from'] = $ssb_account_id_from;
      $data['ssb_account_id_to'] = $ssb_account_id_to;
      $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
      $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
      $data['cheque_type'] = $cheque_type;
      $data['cheque_id'] = $cheque_id;
      $data['cheque_no'] = $cheque_no;
      $data['transction_no'] = $transction_no;
      $data['entry_date'] = $entry_date;
      $data['entry_time'] = $entry_time;
      $data['created_by'] = $created_by;
      $data['created_by_id'] = $created_by_id;
      $data['created_at'] = $globaldate;
      $data['updated_at'] = $updated_at;
      $data['company_id'] = $companyId ?? 0;
      if ($isApp != null) {
         $data['app_login_user_id'] = $created_by_id;
         $data['is_app'] = 1;
      }
      $transcation = \App\Models\AllHeadTransaction::create($data);
      // $transcation = \App\Models\AllHeadTransaction::insertGetId($data);
      
      return true;
   }
}
