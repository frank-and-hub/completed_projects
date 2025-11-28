<?php

namespace App\Http\Controllers\Admin\vendorManagement;

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Member;
use App\Models\ExpenseItem;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\RentLiability;
use App\Models\VendorBill;
use App\Models\VendorBillItem;
use App\Models\VendorBillPayment;
use App\Models\VendorCategory;
use App\Models\Vendor;
use App\Models\SavingAccount;
use App\Models\AccountHeads;


use App\Models\VendorLog;
use App\Models\VendorCreditNode;
use App\Models\AdvancedTransaction;
use App\Models\AssociateTransaction;

use App\Models\VendorTransaction;


use App\Http\Controllers\Admin\CommanController;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Email;
use App\Services\Sms;
use App\Services\ImageUpload;
use Illuminate\Support\Facades\Schema;

class BillCreateController extends Controller
{

	public function __construct()
	{
		// check user login or not
		$this->middleware('auth');
	}


	public function index()
	{
		if (check_my_permission(Auth::user()->id, "227") != "1") {

			return redirect()->route('admin.dashboard');
		}
		$data['venderDate'] = "";
		if (isset($_GET['id'])) {
			$id = $_GET['id'];
			$data['vendor'] = Vendor::where('id', $id)->where('is_deleted',0)->first();
			$data['venderDate'] = date('d/m/Y', strtotime(convertDate($data['vendor']->created_at)));
		} else {
			$id = '';
		}
		$data['title'] = 'Bill Management | Create Bill';
		$data['expense_item'] = ExpenseItem::select('id', 'name', 'company_id')->where('status', 1)->get();
		$data['vendorList'] = Vendor::where('status', 1)->where('is_deleted',0)->get(['id', 'name', 'company_id','created_at']);
		$data['account_heads'] = AccountHeads::select('id', 'head_id', 'sub_head', 'parent_id')->whereIN('parent_id', ['22', '261'])->get();
		$data['vid'] = $id;
		return view('templates.admin.vendor_management.bill_expense.index', $data);
	}

	public function vendordate(Request $request){
		$data['vendors'] = Vendor::where('id', $request->vendor_id)->first('created_at');
		$data['vendors'] =date("d/m/Y", strtotime(convertDate($data['vendors']->created_at)));
		// dd($data['vendors']);
		return json_encode($data['vendors']);
   }

	public function get_item_details(Request $request)
	{
		$itemID = $request->item_id;
		$currentItemRow = $request->currentItemRow;

		$expence_data = ExpenseItem::where('id', $itemID);
		if (isset($request->company_id)) {
			$expence_data = $expence_data->where('company_id', $request->company_id);
		}
		$expence_data = $expence_data->first();

		$viewData = '';

		if (isset($expence_data->head)) {
			$viewData .= '<td  class=" td_remove error-msg"> <input type="hidden" name="itemIDs[]" id="itemIDs_' . $currentItemRow . '" class="form-control itemIDs" value="' . $expence_data->id . '" data-value="' . $currentItemRow . '" >
			<select   name="account_head_item[]" id="account_head_item_' . $currentItemRow . '" class="form-control account_head_item" data-value="' . $currentItemRow . '" > 
				<option  value="">Select Account Head</option>';
			if ($expence_data->head == 9) {
				$viewData .= '<option  value="9" selected >Fixed Assets</option> 
					<option value="86">Indirect Expense</option> ';
			} else if ($expence_data->head == 86) {
				$viewData .= '<option  value="9"  >Fixed Assets</option> 
					<option value="86" selected>Indirect Expense</option> ';
			} else {
				$viewData .= '<option  value="9"  >Fixed Assets</option> 
					<option value="86"  >Indirect Expense</option> ';
			}

			$viewData .= '</select>
				<input type="hidden" name="h[]" id="h_' . $currentItemRow . '" class="form-control h" value="' . ($expence_data->head??0) . '" data-value="' . $currentItemRow . '" >
			</td>';
		} else {
			$viewData .= '<td  class=" td_remove error-msg"> 
			
			<select   name="account_head_item[]" id="account_head_item_' . $currentItemRow . '" class="form-control account_head_item accounthead" data-value="' . $currentItemRow . '" > 
				<option  value="">Select Account Head</option>   
				<option  value="9">Fixed Assets</option> 
				<option value="86">Indirect Expense</option> 
			</select>
			<input type="hidden" name="h[]" id="h_' . $currentItemRow . '" class="form-control h" value="' . ($expence_data->head??0) . '" data-value="' . $currentItemRow . '" >
			</td>';
		}

		$viewData .= '<td  class=" td_remove error-msg">
			<select   name="account_subhead1_item[]" id="account_subhead1_item_' . $currentItemRow . '" class="form-control account_subhead1_item accounthead1" data-value="' . $currentItemRow . '" > 
				<option  value="">Select Account Sub Head1</option>   
			</select>
			<input type="hidden" name="h1[]" id="h1_' . $currentItemRow . '" class="form-control h1" value="' . ($expence_data->sub_head1??0) . '" data-value="' . $currentItemRow . '" >
			
		
		</td>';

		$viewData .= '<td  class=" td_remove error-msg">
			<select   name="account_subhead2_item[]" id="account_subhead2_item_' . $currentItemRow . '" class="form-control account_subhead2_item accounthead2" data-value="' . $currentItemRow . '" > 
				<option  value="">Select Account Sub Head2</option>   
			</select>
			<input type="hidden" name="h2[]" id="h2_' . $currentItemRow . '" class="form-control h2" value="' . ($expence_data->sub_head2??0) . '" data-value="' . $currentItemRow . '" >
			
		
		</td>';
		$viewData .= '<td  class=" td_remove error-msg">
			<select   name="account_subhead3_item[]" id="account_subhead3_item_' . $currentItemRow . '" class="form-control account_subhead3_item accounthead3" data-value="' . $currentItemRow . '" > 
				<option  value="">Select Account Sub Head3</option>   
			</select>
			<input type="hidden" name="h3[]" id="h3_' . $currentItemRow . '" class="form-control h3" value="' . ($expence_data->sub_head3??0) . '" data-value="' . $currentItemRow . '" >
			
		
		</td>';

		$viewData .= '<td  class=" td_remove error-msg">
		<input type="text" name="hsn_code_item[]" id="hsn_code_item_' . $currentItemRow . '" class="form-control hsn_code_item" value="' . ($expence_data->hsn_code??0) . '" data-value="' . $currentItemRow . '" >
			
		
		</td>';
		$viewData .= '<td  class=" td_remove error-msg">
		<input type="text" name="quntity_item[]" id="quntity_item_' . $currentItemRow . '" class="form-control quntity_item" value="1" data-value="' . $currentItemRow . '"  onkeypress="return  integerTrue(event)">		
		</td>';


		$viewData .= '<td  class=" td_remove error-msg">
		<input type="text" name="rate_item[]" id="rate_item_' . $currentItemRow . '" class="form-control rate_item" value="' . number_format((float)($expence_data->price??0), 2, '.', '') . '" data-value="' . $currentItemRow . '" onkeypress="return isNumberKey(event)">		
		</td>';

		$viewData .= '<td class="discountField td_remove error-msg" style="display:none">
			<input type="text" name="discount_item[]" id="discount_item_' . $currentItemRow . '" class="form-control discount_item"  data-value="' . $currentItemRow . '"  value="0" onkeypress="return isNumberKey(event)">
			<select   name="discount_item_type[]" id="discount_item_type_' . $currentItemRow . '" class="form-control discount_item_type" data-value="' . $currentItemRow . '" > 
				<option  value="1">%</option> 
				<option value="0">Rs.</option>  
			</select>		
		</td>';

		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="amount_item[]" id="amount_item_' . $currentItemRow . '" class="form-control amount_item" value="' . number_format((float)($expence_data->price??0), 2, '.', '') . '" data-value="' . $currentItemRow . '"   readonly >		
		</td>';


		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="taxable_item[]" id="taxable_item_' . $currentItemRow . '" class="form-control taxable_item"  data-value="' . $currentItemRow . '"  value="' . number_format((float)($expence_data->price??0), 2, '.', '') . '"  readonly >		
		</td>';
		if ($expence_data->gst_type == 1) {
			$gst_val = $expence_data->gst_per;
			$gst_amount = number_format((float)($expence_data->price??0) * $gst_val / 100, 2, '.', '');
		} else if ($expence_data->gst_type == 0) {

			$gst_val = $expence_data->gst;
			$gst_amount = number_format((float)$gst_val, 2, '.', '');
		} else {
			$gst_val = '';
			$gst_amount = number_format((float)0, 2, '.', '');
		}

		if ($expence_data->igst_type == 1) {
			$igst_val = $expence_data->igst_per;
			$igst_amount = number_format((float)($expence_data->price??0) * $igst_val / 100, 2, '.', '');
		} else if ($expence_data->igst_type == 1) {

			$igst_amount = $igst_val = number_format((float)$expence_data->igst, 2, '.', '');
		} else {
			$igst_val = '';
			$igst_amount = '';
		}


		$tax = 0;
		$rr = '';
		$rr1 = '';
		if ($gst_amount > 0) {
			$tax = number_format((float)$gst_amount, 2, '.', '');
			$rr1 = 'readonly';
		}
		if ($igst_amount > 0) {
			$tax = number_format((float)$igst_amount, 2, '.', '');
			$rr = 'readonly';
		}

		$viewData .= '<td class=" td_remove error-msg">
			<input type="text" name="gst_item[]" id="gst_item_' . $currentItemRow . '" class="form-control gst_item" ' . $rr . ' value="' . $gst_val . '" data-value="' . $currentItemRow . '" onkeypress="return isNumberKey(event)">
			<select   name="gst_item_type[]" id="gst_item_type_' . $currentItemRow . '" class="form-control gst_item_type" data-value="' . $currentItemRow . '" > ';
		if ($expence_data->gst_type == 1) {
			$viewData .= '<option  value="1" selected >%</option> 
					<option value="0">Rs.</option> > ';
			$cgst_val = $expence_data->cgst_per;
			$sgst_val = $expence_data->sgst_per;
		} else if ($expence_data->gst_type == 0) {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" selected>Rs.</option> > ';
			$cgst_val = number_format((float)$expence_data->cgst, 2, '.', '');
			$sgst_val = number_format((float)$expence_data->sgst, 2, '.', '');
		} else {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" >Rs.</option> > ';
			$cgst_val = '';
			$sgst_val = '';
		}
		$viewData .= '	</select>		
		</td>';

		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="cgst_item[]" id="cgst_item_' . $currentItemRow . '" class="form-control cgst_item"  value="' . $cgst_val . '"  readonly data-value="' . $currentItemRow . '" >		
		</td>';
		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="sgst_item[]" id="sgst_item_' . $currentItemRow . '" class="form-control sgst_item"  value="' . $sgst_val . '" readonly data-value="' . $currentItemRow . '" >		
		</td>';



		$viewData .= '<td class=" td_remove error-msg">
			<input type="text" name="igst_item[]" id="igst_item_' . $currentItemRow . '" class="form-control igst_item" ' . $rr1 . ' value="' . $igst_val . '" data-value="' . $currentItemRow . '" onkeypress="return isNumberKey(event)">
			<select   name="igst_item_type[]" id="igst_item_type_' . $currentItemRow . '" class="form-control igst_item_type" data-value="' . $currentItemRow . '" > ';
		if ($expence_data->igst_type == 1) {
			$viewData .= '<option  value="1" selected >%</option> 
					<option value="0">Rs.</option> > ';
		} else if ($expence_data->igst_type == 1) {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" selected>Rs.</option> > ';
		} else {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" >Rs.</option> > ';
		}
		$viewData .= '	</select>		
		</td>';

		// $viewData .= '<td class=" td_remove error-msg">
		// <input type="file" name="bill_upload[]" id="bill_upload_' . $currentItemRow . '" class="form-control bill_upload"  data-value="' . $currentItemRow . '" >		
		// </td>';


		$total_Pay = number_format((float)$expence_data->price + $tax, 2, '.', '');

		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="total_amount_pay[]" id="total_amount_pay_' . $currentItemRow . '" class="form-control total_amount_pay"  data-value="' . $currentItemRow . '" value="' . $total_Pay . '"  readonly>		
		</td>';

		if ($currentItemRow > 0) {
			$viewData .= '<td class=" td_remove error-msg">
						<button type="button" class="btn btn-primary remCF"><i class="icon-trash"></i></button>		
			</td>';
		}


		return \Response::json(['view' => $viewData, 'msg_type' => 'success', 'expence_data' => $expence_data]);
	}



	public function get_items(Request $request)
	{
		$itemID = $request->item_id;
		$newRowItem = $request->newRowItem;

		$expence_data = ExpenseItem::where('status', 1);
		if (isset($request->company_id)) {
			$expence_data = $expence_data->where('company_id', $request->company_id);
		}
		$expence_data = $expence_data->get();

		$viewData = '';

		$viewData .= '<tr id="trRow' . $newRowItem . '" value="' . $newRowItem . '">';
		$viewData .= '<td id="tdRow' . $newRowItem . '" class="error-msg">';
		$viewData .= '<select class="form-control item_id" id="item_id' . $newRowItem . '" name="item_id[]" data-row-id="' . $newRowItem . '">';

		$viewData .= '<option value="">Choose...</option>';

		for ($i = 0; $i < count($expence_data); $i++) {
			$viewData .= '<option value="' . $expence_data[$i]->id . '">' . $expence_data[$i]->name . '</option>';
		}

		$viewData .= "</select></td></tr>";

		return \Response::json(['view' => $viewData, 'msg_type' => 'success']);
	}

	/**
	 * Save bill.
	 * Route: /bill/create
	 * Method: Post 
	 * @return  array()  Response

	 */

	public function save(Request $request)
	{
		$rules = [
			'vendor_id' => 'required',
			'bill' => 'required',
			'bill_date' => 'required',
			'branch_id' => 'required',
			'discount' => 'required',
			'total_sub' => 'required',
		];
		$customMessages = [
			'required' => 'The :attribute field is required.'
		];
		$this->validate($request, $rules, $customMessages);
		DB::beginTransaction();
		try {
			$companyId = $request->company_id;
			$globaldate = $request->bill_created_at;
			$select_date = $request->bill_date;
			$current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
			$entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
			$entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
			$date_create = $entry_date . ' ' . $entry_time;
			$created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
			$updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));

			$currency_code = 'INR';
			$created_by = 1;
			$created_by_id = \Auth::user()->id;
			$created_by_name = \Auth::user()->username;
			$randNumber = mt_rand(0, 999999999999999);
			$v_no = $randNumber;
			$v_date = $entry_date;
			$type = 27;
			$sub_type = 271;

			$branch_id = $request->branch_id;

			$vendorBill['branch_id'] = $request->branch_id;
			$vendorBill['vendor_id'] = $request->vendor_id;
			$vendorBill['bill_type'] = 0;
			$vendorBill['bill_date'] = date("Y-m-d", strtotime(convertDate($request->bill_date)));
			$vendorBill['bill_number'] = $request->bill;;
			$vendorBill['discount_type'] = $request->discount;


			$daybookRef = CommanController::createBranchDayBookReferenceNew($request->total_amountPay, $globaldate);
			$refId = $daybookRef;


			$vendorBill['total_item'] = 2;
			$vendorBill['total_item_amount'] = $request->total_sub;
			$vendorBill['total_discount_type'] = $request->total_discount_type;
			$vendorBill['total_discount_per'] = $request->total_discount_val;
			$vendorBill['total_discount_amount'] = $request->total_dis_amt;

			$vendorBill['sub_amount'] = $request->total_sub;
			$vendorBill['tds_head'] = $request->tds_head;
			$vendorBill['tds_type'] = 1;
			$vendorBill['tds_per'] = $request->tds_per;
			$vendorBill['tds_amount'] = $request->tds_amt;
			$vendorBill['payble_amount'] = $request->total_amountPay;
			//$vendorBill['due_amount']=2; 
			//$vendorBill['transferd_amount']=2; 
			$vendorBill['balance'] = $request->total_amountPay;
			$vendorBill['status'] = 0;
			$vendorBill['adj_amount'] = $request->final_adj_amount;
			$vendorBill['created_at'] = $created_at;
			$vendorBill['updated_at'] = $updated_at;
			$vendorBill['description'] = $request->description;
			$vendorBill['daybook_ref_id'] = $refId;
			$vendorBill['company_id'] = $companyId;

			$vendorBillCreate = VendorBill::create($vendorBill);
			$vendorBillID = $vendorBillCreate->id;
			if (isset($request->bill_upload)) {
				// $mainFolder = storage_path() . '/images/bill_expense';
				$mainFolder = 'bill_expense';
				$file = $request->file('bill_upload');
				$uploadFile = $file->getClientOriginalName();
				$filename = pathinfo($uploadFile, PATHINFO_FILENAME);
				$fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
				ImageUpload::upload($file, $mainFolder,$fname);
				// $file->move($mainFolder, $fname);
				$fData = [
					'file_name' => $fname,
					'file_path' => $mainFolder,
					'file_extension' => $file->getClientOriginalExtension(),
				];
				$venderBillUpdate = VendorBill::find($vendorBillID);
				$venderBillUpdate->bill_upload = $fname;
				$venderBillUpdate->save();
			}
			$total_gst_amount = 0;
			$bank_id = NULL;
			$bank_ac_id = NULL;
			$member_id = NULL;
			$ssb_account_id_from = NULL;
			$cheque_no = NULL;
			$transction_no = NULL;
			$jv_unique_id = NULL;
			$ssb_account_id_to = NULL;
			$ssb_account_tran_id_to = NULL;
			$ssb_account_tran_id_from = NULL;
			$cheque_type = NULL;
			$cheque_id = NULL;
			$tranId = $vendorBillID;
			$billItemDetail = '';
			$billId = '';
			$vdetail = getVendorDetail($request->vendor_id);

			if (isset($_POST['item_id'])) {
				foreach (($_POST['item_id']) as $key => $option) {
					$getItemName = ExpenseItem::where('id', $_POST['item_id'][$key])->first();
					$billItem['vendor_bill_id'] = $vendorBillID;
					$billItem['account_head'] = $_POST['account_head_item'][$key];

					$billItem['exp_item_id'] = $_POST['itemIDs'][$key];

					if ($_POST['account_head_item'][$key] > 0) {
						$expHead = $_POST['account_head_item'][$key];
					}
					if ($_POST['account_subhead1_item'][$key] > 0) {
						$expHead = $_POST['account_subhead1_item'][$key];
						$billItem['sub_account_head1'] = $_POST['account_subhead1_item'][$key];
					}
					if ($_POST['account_subhead2_item'][$key] > 0) {
						$expHead = $_POST['account_subhead2_item'][$key];
						$billItem['sub_account_head2'] = $_POST['account_subhead2_item'][$key];
					}
					if ($_POST['account_subhead3_item'][$key] > 0) {
						$expHead = $_POST['account_subhead3_item'][$key];
						$billItem['sub_account_head3'] = $_POST['account_subhead3_item'][$key];
					}


					$billItem['item_name'] = $getItemName->name;
					$billItem['type'] = $getItemName->type;
					$billItem['hsn_code'] = $_POST['hsn_code_item'][$key];
					$billItem['description'] = $getItemName->description;
					$billItem['price'] = $getItemName->price;
					$billItem['gst_type'] = $_POST['gst_item_type'][$key];
					if ($_POST['gst_item'][$key] > 0) {

						if ($_POST['gst_item_type'][$key] == 1) {
							$billItem['cgst_per'] = $_POST['cgst_item'][$key];
							$billItem['sgst_per'] = $_POST['sgst_item'][$key];
							$billItem['gst_per'] = $_POST['gst_item'][$key];
						} else {
							$billItem['cgst'] = $_POST['cgst_item'][$key];
							$billItem['sgst'] = $_POST['sgst_item'][$key];
							$billItem['gst_amount'] = $_POST['gst_item'][$key];
						}
					}
					$billItem['igst_type'] = $_POST['igst_item_type'][$key];
					if ($_POST['igst_item'][$key] > 0) {
						if ($_POST['igst_item_type'][$key] == 1) {
							$billItem['igst_per'] = $_POST['igst_item'][$key];
						} else {
							$billItem['igst'] = $_POST['igst_item'][$key];
						}
					}
					$expAmount =	$_POST['taxable_item'][$key];
					if ($request->discount == 1) {
						$billItem['discount_type'] = $_POST['discount_item_type'][$key];
						if ($_POST['discount_item_type'][$key] == 1) {
							$billItem['discount_per'] = $_POST['discount_item'][$key];
							$itemDiscountAmount = ($_POST['amount_item'][$key] * $_POST['discount_item'][$key]) / 100;
						} else {
							$billItem['discount_amount'] = $_POST['discount_item'][$key];
							$itemDiscountAmount = $_POST['discount_item'][$key];
						}

						$expAmount = $_POST['amount_item'][$key];
					}
					$billItem['quantity'] = $_POST['quntity_item'][$key];
					$billItem['rate'] = $_POST['rate_item'][$key];
					$billItem['amount'] = $_POST['amount_item'][$key];
					$billItem['taxable_amount'] = $_POST['taxable_item'][$key];
					$billItem['total_amount'] = $_POST['total_amount_pay'][$key];
					$billItem['created_at'] = $created_at;
					$billItem['updated_at'] = $updated_at;
					$billItem['daybook_ref_id'] = $refId;
					$billItem['company_id'] = $companyId;


					$BillItemCreate = VendorBillItem::create($billItem);
					$billItemID = $BillItemCreate->id;
					$files = $request->file('bill_upload');

					$billAdvice['payment_type'] = 0;
					$billAdvice['sub_payment_type'] = 0;
					$billAdvice['branch_id'] = $branch_id;
					$billAdvice['date'] = $entry_date;
					$billAdvice['is_mature'] = 0;
					$billAdvice['ta_advanced_adjustment'] = 0;
					$billAdvice['is_print'] = 0;
					$billAdvice['payment_mode'] = 3;
					$billAdvice['status'] = 1;
					$billAdvice['created_at'] = $created_at;
					$billAdvice['updated_at'] = $updated_at;
					$billAdvice['daybook_ref_id'] = $refId;

					$billAdvice['billId'] = $vendorBillID;
					$billAdvice['billItemId'] = $billItemID;
					$billAdvice['is_bill'] = 1;
					$billAdvice['company_id'] = $companyId;
					$billAdviceCreate = \App\Models\DemandAdvice::create($billAdvice);
					$billAdviceID = $billAdviceCreate->id;

					$billAdviceExp['demand_advice_id'] = $billAdviceID;
					$billAdviceExp['party_name'] = $vdetail->name;
					$billAdviceExp['particular'] = $getItemName->name . ' purchase ' . $_POST['taxable_item'][$key];
					$billAdviceExp['mobile_number'] = $vdetail->mobile_no;
					$billAdviceExp['amount'] = $_POST['total_amount_pay'][$key];

					$billAdviceExp['is_assets'] = 0;
					$billAdviceExp['assets_category'] = $_POST['account_head_item'][$key];
					$billAdviceExp['assets_subcategory'] = $expHead;
					$billAdviceExp['status'] = 0;
					$billAdviceExp['current_balance'] = $_POST['taxable_item'][$key];
					$billAdviceExp['purchase_date'] = $entry_date;

					$billAdviceExp['billId'] = $vendorBillID;
					$billAdviceExp['billItemId'] = $billItemID;
					$billAdviceExp['is_bill'] = 1;



					$billAdviceExp['created_at'] = $created_at;
					$billAdviceExp['updated_at'] = $updated_at;
					$billAdviceExp['daybook_ref_id'] = $refId;
					$billAdviceExp['company_id'] = $companyId;
					$billAdviceExp['bill_number'] =$request->bill;
					$billAdviceExpCreate = \App\Models\DemandAdviceExpense::create($billAdviceExp);
					$billAdviceExpCreateID = $billAdviceExpCreate->id;




					$billItemDetail = $getItemName->name . " ( Rs." . number_format((float)$_POST['total_amount_pay'][$key], 2, '.', '') . "), " . $billItemDetail;
					$billId = $getItemName->id . ',' . $billId;


					$gst_amount_item1 = $_POST['total_amount_pay'][$key] - $_POST['taxable_item'][$key];
					$gst_amount_item = round($gst_amount_item1 / 2);
					$total_gst_amount = $total_gst_amount + $gst_amount_item;

					//expence
					$getNature = AccountHeads::where('head_id', $expHead)->first();
					if ($getNature->cr_nature == 1) {
						$eMode = 'CR';
					}
					if ($getNature->dr_nature == 1) {
						$eMode = 'DR';
					}

					$desExp = 'Bill(' . $request->bill . ')(' . $getItemName->name . '-' . $expAmount . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

					$desTran = $desExp;
					$vendorTran1['type'] = 1;
					$vendorTran1['sub_type'] = 11;
					$vendorTran1['type_id'] = $vendorBillID;
					$vendorTran1['vendor_id'] = $request->vendor_id;
					$vendorTran1['branch_id'] = $branch_id;
					$vendorTran1['amount'] = $expAmount;
					$vendorTran1['description'] = $desTran;
					$vendorTran1['payment_type'] = 'CR';
					$vendorTran1['payment_mode'] = 3;
					$vendorTran1['currency_code'] = 'INR';
					$vendorTran1['v_no'] = $v_no;
					$vendorTran1['entry_date'] = $entry_date;
					$vendorTran1['entry_time'] = $entry_time;
					$vendorTran1['created_by'] = 1;
					$vendorTran1['created_by_id'] = $created_by_id;
					$vendorTran1['created_at'] = $created_at;
					$vendorTran1['updated_at'] = $updated_at;
					$vendorTran1['daybook_ref_id'] = $refId;
					$vendorTran1['company_id'] = $companyId;

					$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
					$vendorTranID1 = $vendorTranCreate1->id;

					///libility
					$LibHead = 140;
					$desLibe = 'Bill(' . $request->bill . ')(' . $getItemName->name . '-' . $expAmount . ')  created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');
					$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $expAmount, $desLibe, 'CR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

					$allTran2 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $expHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $expAmount, $desExp, $eMode, 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
					// item level discount ------- 

					if ($request->discount == 1) {
						if ($itemDiscountAmount > 0) {
							$desExpD = 'Disount of Rs.' . number_format((float)$itemDiscountAmount, 2, '.', '') . ' On Bill(' . $request->bill . ') (' . $getItemName->name . '-' . $expAmount . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

							$allTran21 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 240, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $itemDiscountAmount, $desExpD, 'CR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
							///libility
							$LibHead = 140;
							$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $itemDiscountAmount,  $desExpD, 'DR', 3, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


							$vendorTran1['type'] = 1;
							$vendorTran1['sub_type'] = 11;
							$vendorTran1['type_id'] = $vendorBillID;
							$vendorTran1['vendor_id'] = $request->vendor_id;
							$vendorTran1['branch_id'] = $branch_id;
							$vendorTran1['amount'] = $itemDiscountAmount;
							$vendorTran1['description'] = $desExpD;
							$vendorTran1['payment_type'] = 'DR';
							$vendorTran1['payment_mode'] = 3;
							$vendorTran1['currency_code'] = 'INR';
							$vendorTran1['v_no'] = $v_no;
							$vendorTran1['entry_date'] = $entry_date;
							$vendorTran1['entry_time'] = $entry_time;
							$vendorTran1['created_by'] = 1;
							$vendorTran1['created_by_id'] = $created_by_id;
							$vendorTran1['created_at'] = $created_at;
							$vendorTran1['updated_at'] = $updated_at;
							$vendorTran1['daybook_ref_id'] = $refId;
							$vendorTran1['company_id'] = $companyId;

							$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
							$vendorTranID1 = $vendorTranCreate1->id;
						}
					}


					/// GST
					if ($_POST['gst_item'][$key] > 0) {
						$cgstHead = 171;
						if ($gst_amount_item > 0) {

							$desGst = 'CGST of Rs.' . number_format((float)$gst_amount_item, 2, '.', '') . ' On Bill(' . $request->bill . ') (' . $getItemName->name . '-' . $expAmount . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');
							// dd($request->vendor_id);
							$typetransationid = $request->vendor_id;
							$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type, $sub_type, $typetransationid, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $gst_amount_item,  $desGst, 'DR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


							$sgstHead = 172;

							$desSGst = 'SGST of Rs.' . number_format((float)$gst_amount_item, 2, '.', '') . ' On Bill(' . $request->bill . ') (' . $getItemName->name . '-' . $expAmount . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

							$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $sgstHead, $type, $sub_type, $typetransationid, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gst_amount_item,  $desSGst, 'DR', 3, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
							$gstAmount = $gst_amount_item * 2;

							$desgSGst = 'GST of Rs.' . number_format((float)$gstAmount, 2, '.', '') . ' On Bill(' . $request->bill . ') (' . $getItemName->name . '-' . $expAmount . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

							$vendorTran1['type'] = 1;
							$vendorTran1['sub_type'] = 11;
							$vendorTran1['type_id'] = $vendorBillID;
							$vendorTran1['vendor_id'] = $request->vendor_id;
							$vendorTran1['branch_id'] = $branch_id;
							$vendorTran1['amount'] = $gstAmount;
							$vendorTran1['description'] = $desgSGst;
							$vendorTran1['payment_type'] = 'CR';
							$vendorTran1['payment_mode'] = 3;
							$vendorTran1['currency_code'] = 'INR';
							$vendorTran1['v_no'] = $v_no;
							$vendorTran1['entry_date'] = $entry_date;
							$vendorTran1['entry_time'] = $entry_time;
							$vendorTran1['created_by'] = 1;
							$vendorTran1['created_by_id'] = $created_by_id;
							$vendorTran1['created_at'] = $created_at;
							$vendorTran1['updated_at'] = $updated_at;
							$vendorTran1['daybook_ref_id'] = $refId;
							$vendorTran1['company_id'] = $companyId;

							$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
							$vendorTranID1 = $vendorTranCreate1->id;

							///libility
							$LibHead = 140;
							$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $typetransationid, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $gstAmount,  $desgSGst, 'CR', 3, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
						}
					}
					if ($_POST['igst_item'][$key] > 0) {
						if ($gst_amount_item1 > 0) {
							$igstHead = 170;

							$desIGst = 'ISGST of Rs.' . number_format((float)$gst_amount_item1, 2, '.', '') . ' On Bill(' . $request->bill . ') (' . $getItemName->name . '-' . $expAmount . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

							$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $igstHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gst_amount_item1,  $desIGst, 'DR', 3, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

							///libility
							$LibHead = 140;
							$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gst_amount_item1, $desIGst, 'CR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


							$vendorTran1['type'] = 1;
							$vendorTran1['sub_type'] = 11;
							$vendorTran1['type_id'] = $vendorBillID;
							$vendorTran1['vendor_id'] = $request->vendor_id;
							$vendorTran1['branch_id'] = $branch_id;
							$vendorTran1['amount'] = $gst_amount_item1;
							$vendorTran1['description'] = $desIGst;
							$vendorTran1['payment_type'] = 'CR';
							$vendorTran1['payment_mode'] = 3;
							$vendorTran1['currency_code'] = 'INR';
							$vendorTran1['v_no'] = $v_no;
							$vendorTran1['entry_date'] = $entry_date;
							$vendorTran1['entry_time'] = $entry_time;
							$vendorTran1['created_by'] = 1;
							$vendorTran1['created_by_id'] = $created_by_id;
							$vendorTran1['created_at'] = $created_at;
							$vendorTran1['updated_at'] = $updated_at;
							$vendorTran1['daybook_ref_id'] = $refId;
							$vendorTran1['company_id'] = $companyId;

							$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
							$vendorTranID1 = $vendorTranCreate1->id;
						}
					}
				}
			}
			$vendorLog['vendor_id'] = $request->vendor_id;
			$vendorLog['vendor_bill_id'] = $vendorBillID;
			$vendorLog['title'] = 'Bill Created';
			$vendorLog['bill_no'] = $request->bill;
			$vendorLog['description'] = 'Bill created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '') . ' by ' . $created_by_name;
			$vendorLog['amount'] = $request->total_amountPay;
			$vendorLog['item_detail'] = $billItemDetail;
			$vendorLog['item_id'] = $billId;
			$vendorLog['created_by'] = 1;
			$vendorLog['created_by_id'] = $created_by_id;
			$vendorLog['created_by_name'] = $created_by_name;
			$vendorLog['created_at'] = $created_at;
			$vendorLog['updated_at'] = $updated_at;
			$vendorLog['daybook_ref_id'] = $refId;
			$vendorLogCreate = VendorLog::create($vendorLog);
			$vendorLogID = $vendorLogCreate->id;

			// transaction  level discount ------- 

			if ($request->discount == 2) {
				if ($request->total_dis_amt > 0) {
					$desExpD = 'Disount of Rs.' . number_format((float)$request->total_dis_amt, 2, '.', '') . ' On Bill(' . $request->bill . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

					$allTran21 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 240, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $request->total_dis_amt,  $desExpD, 'CR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
					///libility
					$LibHead = 140;
					$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $request->total_dis_amt, $desExpD, 'DR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


					$vendorTran1['type'] = 1;
					$vendorTran1['sub_type'] = 11;
					$vendorTran1['type_id'] = $vendorBillID;
					$vendorTran1['vendor_id'] = $request->vendor_id;
					$vendorTran1['branch_id'] = $branch_id;
					$vendorTran1['amount'] = $request->total_dis_amt;
					$vendorTran1['description'] = $desExpD;
					$vendorTran1['payment_type'] = 'DR';
					$vendorTran1['payment_mode'] = 3;
					$vendorTran1['currency_code'] = 'INR';
					$vendorTran1['v_no'] = $v_no;
					$vendorTran1['entry_date'] = $entry_date;
					$vendorTran1['entry_time'] = $entry_time;
					$vendorTran1['created_by'] = 1;
					$vendorTran1['created_by_id'] = $created_by_id;
					$vendorTran1['created_at'] = $created_at;
					$vendorTran1['updated_at'] = $updated_at;
					$vendorTran1['daybook_ref_id'] = $refId;
					$vendorTran1['company_id'] = $companyId;

					$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
					$vendorTranID1 = $vendorTranCreate1->id;
				}
			}


			///TDS
			$tdsHead = $request->tds_head;
			if ($request->tds_head > 0) {
				if ($request->tds_amt_final > 0) {
					$getNatureTds = AccountHeads::where('head_id', $request->tds_head)->first();
					if ($getNatureTds->cr_nature == 1) {
						$eModeT = 'CR';
						$vendorMode = 'DR';
					}
					if ($getNatureTds->dr_nature == 1) {
						$eModeT = 'DR';
						$vendorMode = 'CR';
					}


					$desT = 'TDS of Rs.' . number_format((float)$request->tds_amt_final, 2, '.', '') . ' On Bill(' . $request->bill . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

					$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $tdsHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $request->tds_amt_final, $desT, $eModeT, 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


					///libility
					$LibHead = 140;
					$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $request->tds_amt_final,  $desT, $vendorMode, 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


					$vendorTran1['type'] = 1;
					$vendorTran1['sub_type'] = 11;
					$vendorTran1['type_id'] = $vendorBillID;
					$vendorTran1['vendor_id'] = $request->vendor_id;
					$vendorTran1['branch_id'] = $branch_id;
					$vendorTran1['amount'] = $request->tds_amt_final;
					$vendorTran1['description'] = $desT;
					$vendorTran1['payment_type'] = $vendorMode;
					$vendorTran1['payment_mode'] = 3;
					$vendorTran1['currency_code'] = 'INR';
					$vendorTran1['v_no'] = $v_no;
					$vendorTran1['entry_date'] = $entry_date;
					$vendorTran1['entry_time'] = $entry_time;
					$vendorTran1['created_by'] = 1;
					$vendorTran1['created_by_id'] = $created_by_id;
					$vendorTran1['created_at'] = $created_at;
					$vendorTran1['updated_at'] = $updated_at;
					$vendorTran1['daybook_ref_id'] = $refId;
					$vendorTran1['company_id'] = $companyId;
					$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
					$vendorTranID1 = $vendorTranCreate1->id;
				}
			}
			///round of 
			if (strpos($request->final_adj_amount, '-') !== false) {
				$emoderr = 'CR';
				$emoderr1 = 'DR';
			} else {
				$emoderr = 'DR';
				$emoderr1 = 'CR';
			}
			$rAMountOff = trim($request->final_adj_amount, '-,+');
			if ($request->final_adj_amount != '') {

				if ($rAMountOff > 0) {
					$roundofHead = 178;

					$desROff = 'Adjustment of Rs.' . number_format((float)$rAMountOff, 2, '.', '') . ' On Bill(' . $request->bill . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

					$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $roundofHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $rAMountOff, $desROff, $emoderr, 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


					///libility
					$LibHead = 140;
					$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $rAMountOff, $desROff, $emoderr1, 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


					$vendorTran1['type'] = 1;
					$vendorTran1['sub_type'] = 11;
					$vendorTran1['type_id'] = $vendorBillID;
					$vendorTran1['vendor_id'] = $request->vendor_id;
					$vendorTran1['branch_id'] = $branch_id;
					$vendorTran1['amount'] = $rAMountOff;
					$vendorTran1['description'] = $desROff;
					$vendorTran1['payment_type'] = $emoderr1;
					$vendorTran1['payment_mode'] = 3;
					$vendorTran1['currency_code'] = 'INR';
					$vendorTran1['v_no'] = $v_no;
					$vendorTran1['entry_date'] = $entry_date;
					$vendorTran1['entry_time'] = $entry_time;
					$vendorTran1['created_by'] = 1;
					$vendorTran1['created_by_id'] = $created_by_id;
					$vendorTran1['created_at'] = $created_at;
					$vendorTran1['updated_at'] = $updated_at;
					$vendorTran1['daybook_ref_id'] = $refId;
					$vendorTran1['company_id'] = $companyId;

					$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
					$vendorTranID1 = $vendorTranCreate1->id;
				}
			}

			DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			// echo $ex->getLine();
			// echo '</br>';
			// echo $ex->getFile();
			// echo '</br>';
			// echo $ex->getMessage();
			// die('done');
			return back()->with('alert', $ex->getMessage());
		}

		return back()->with('success', 'Bill Created Successfully');
	}



	public function createItem(Request $request)
	{
		$id = 0;
		$msg_type = '';
		$error = '';
		DB::beginTransaction();
		try {

			$data['head'] = $request->account_head;
			$data['sub_head1'] = $request->account_subhead1;
			$data['sub_head2'] = $request->account_subhead2;
			$data['sub_head3'] = $request->account_subhead3;
			$data['name'] = $request->name;
			$data['type'] = $request->type;
			$data['hsn_code'] = $request->hsn_code;
			$data['description'] = $request->description;
			$data['price'] = $request->cost_pirce;
			$data['gst_type'] = $request->gst_type;
			$data['company_id'] = $request->company_id;
			if ($request->gst != '') {
				if ($request->gst_type == 1) {
					$data['cgst_per'] = $request->cgst;
					$data['sgst_per'] = $request->sgst;
					$data['gst_per'] = $request->gst;
				} else {
					$data['cgst'] = $request->cgst;
					$data['sgst'] = $request->sgst;
					$data['gst'] = $request->gst;
				}
			}
			$data['igst_type'] = $request->igst_type;
			if ($request->igst != '') {
				if ($request->igst_type == 1) {
					$data['igst_per'] = $request->igst;
				} else {
					$data['igst'] = $request->igst;
				}
			}
			$data['status'] = 1;
			$data['created_at'] = $request->created_at;
			$create = ExpenseItem::create($data);
			$id = $create->id;
			$msg_type = 'success';
			$error = '';
			DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			/// return back()->with('alert', $ex->getMessage());
			$msg_type = 'error';
			$error = $ex->getMessage();
		}
		$data = ExpenseItem::where('status', 1)->orderby('id', 'DESC')->get();

		$return_array = compact('data', 'msg_type', 'error', 'id');

		return json_encode($return_array);
	}


	public function edit($id)
	{


		$data['bill'] = VendorBill::where('id', $id)->with('vendor')->first();
		$data['billItem'] = VendorBillItem::where('vendor_bill_id', $id)->where('is_deleted',0)->get();
		$data['title'] = 'Bill Management | Edit Bill';
		$data['expense_item'] = ExpenseItem::where('status', 1)->get();
		$data['vendorList'] = Vendor::where('status', 1)->get();
		$data['account_heads'] = AccountHeads::select('id', 'head_id', 'sub_head', 'parent_id')->whereIN('parent_id', ['22', '261'])->get();
		$data['vid'] = $data['bill']->vendor_id;
		return view('templates.admin.vendor_management.bill_expense.edit', $data);
	}


	public function view_listing($id)
	{

		if (check_my_permission(Auth::user()->id, "268") != "1") {

			return redirect()->route('admin.dashboard');
		}

		$data['bill'] = VendorBill::where('id', $id)->first();
		$data['billItem'] = VendorBillItem::where('vendor_bill_id', $id)->get();



		$data['title'] = 'Bill Items Listing';
		$data['branch'] = Branch::select('id', 'name', 'branch_code')->where('status', 1)->get();

		$data['expense_item'] = ExpenseItem::where('status', 1)->get();
		$data['vendorList'] = Vendor::where('status', 1)->get();
		$data['account_heads'] = AccountHeads::whereIN('parent_id', ['22', '261'])->get();
		$data['vid'] = $data['bill']->vendor_id;

		return view('templates.admin.vendor_management.bill_expense.view_list', $data);
	}
	public function view_listing_edit($id)
	{
		if (check_my_permission(Auth::user()->id, "268") != "1") {

			return redirect()->route('admin.dashboard');
		}

		$data['records'] = \App\Models\VendorItemTransfer::select('id', 'item_id', 'branch_id', 'transfer_qnty', 'total_qnty', 'rate')
			->with(['bill_item' => function ($q) {
				$q->select('id', 'item_name', 'quantity', 'transferred');
			}])
			->with(['bill_branch' => function ($q) {
				$q->select('id', 'name', 'branch_code');
			}])
			->where('item_id', $id)->get();


		$data['title'] = 'Bill Management | List Bill Items';
		$data['item'] = $id;

		if (!empty($data['records']) && count($data['records']) > 0) {
			$data['itemname'] = $data['records'][0]['bill_item']['item_name'];
			$data['itemquentity'] = $data['records'][0]['bill_item']['quantity'];
		} else {
			$data['itemname'] = '';
			$data['itemquentity'] = '';
		}



		$data['branch'] = Branch::select('id', 'name', 'branch_code')->where('status', 1)->get();

		return view('templates.admin.vendor_management.bill_expense.item_list', $data);
	}


	public function save_vender_transfer(Request $request)
	{

		if ($request->action && $request->action == "vender_transfer") {

			$id = $request->id;
			$inputqnt = $request->inputqnt;
			$sltbrnach = $request->sltbrnach;

			$Item['transferred'] = $request->inputqnt;
			$VendorBillItem = VendorBillItem::find($id);
			$VendorBillItem->update($Item);

			$data = \App\Models\VendorItemTransfer::select('id', 'item_id', 'transfer_qnty')
				->where('branch_id', $request->sltbrnach)
				->where('item_id', $request->id)->get();

			if (!empty($data[0]->id)) {
				$val = ($data[0]->transfer_qnty + $request->inputval);
				$updateItem['item_id'] = $request->id;
				$updateItem['branch_id'] = $request->sltbrnach;
				$updateItem['transfer_qnty'] = $val;
				$updateItem['total_qnty'] = $request->total;


				$findID = \App\Models\VendorItemTransfer::find($data[0]->id);
				$findID->update($updateItem);
			} else {

				$daData = [
					'item_id' => $request->id,
					'branch_id' => $request->sltbrnach,
					'transfer_qnty' => $request->inputval,
					'total_qnty' => $request->total,
					'rate' => $request->rate,
				];

				$save = \App\Models\VendorItemTransfer::create($daData);
			}

			echo json_encode(array("status" => "success"));
		} else {
			echo json_encode(array("status" => "fail"));
		}
	}

	public function delete_vender_transfer(Request $request)
	{

		if ($request->action && $request->action == "delete_transfer") {

			$id = $request->id;
			$transfer = $request->transfer;
			$allitem = $request->allitem;
			$itemid = $request->itemid;

			$del = \App\Models\VendorItemTransfer::where('id', $id)->delete();

			$itemdata = VendorBillItem::find($itemid);
			$minus = ($itemdata->transferred - $transfer);

			$billItem['transferred'] = $minus;

			$itemdata->update($billItem);


			echo json_encode(array("status" => "success"));
		} else {
			echo json_encode(array("status" => "fail"));
		}
	}


	public function edit_vender_transfer(Request $request)
	{

		if ($request->action && $request->action == "edit_transfer") {

			$id = $request->id;
			$sltbrnach = $request->sltbrnach;
			$val = $request->inputval;
			$itemid = $request->itemid;
			$findID = \App\Models\VendorItemTransfer::find($id);

			$itemdata = VendorBillItem::find($itemid);
			$minus = ($itemdata->transferred - $findID->transfer_qnty);
			$total = ($minus + $val);

			$billItem['transferred'] = $total;

			$itemdata->update($billItem);

			$updateItem['branch_id'] = $request->sltbrnach;
			$updateItem['transfer_qnty'] = $val;
			$findID->update($updateItem);

			echo json_encode(array("status" => "success"));
		} else {
			echo json_encode(array("status" => "fail"));
		}
	}



	public function get_item_details_view(Request $request)
	{
		$itemID = $request->item_id;
		$itemIdrow = $request->itemIdrow;
		$currentItemRow = $request->currentItemRow;

		$expence_data = ExpenseItem::where('id', $itemID)->first();
		$itemDetail = VendorBillItem::where('id', $itemIdrow)->first();

		$viewData = '';

		if (isset($itemDetail->account_head)) {
			$viewData .= '<td  class=" td_remove error-msg"><input type="hidden" name="itemIDs[]" id="itemIDs_' . $currentItemRow . '" class="form-control itemIDs" value="' . $itemDetail->exp_item_id . '" data-value="' . $currentItemRow . '" >';

			if ($itemDetail->account_head == 9) {
				$viewData .= '<input type="text" name="account_head_item[]" dataID="9" id="account_head_item_' . $currentItemRow . '"  class="form-control account_head_item" value="Fixed Assets" readonly>';
			} else if ($itemDetail->account_head == 86) {
				$viewData .= '<input type="text" name="account_head_item[]" dataID="86" id="account_head_item_' . $currentItemRow . '"  class="form-control account_head_item" value="Indirect Expense" readonly>';
			} else {
				$viewData .= '<input type="text" name="account_head_item[]" dataID="0" id="account_head_item_' . $currentItemRow . '"  class="form-control account_head_item" value="N/A" readonly>';
			}

			$viewData .= '<input type="hidden" name="h[]" id="h_' . $currentItemRow . '" class="form-control account_head_item" value="' . $itemDetail->account_head . '" data-value="' . $currentItemRow . '" >
			</td>';
		} else {
			$viewData .= '<input type="text" name="account_head_item[]" dataID="0" id="account_head_item_' . $currentItemRow . '"  class="form-control account_head_item" value="N/A" readonly>';
		}

		$viewData .= '<td  class=" td_remove error-msg">

		<input type="text" name="account_subhead1_item[]" id="account_subhead1_item_' . $currentItemRow . '" class="form-control account_subhead1_item accounthead1" data-value="' . $currentItemRow . '" readonly>

			
			<input type="hidden" name="h1[]" id="h1_' . $currentItemRow . '" class="form-control h1" value="' . $itemDetail->sub_account_head1 . '" data-value="' . $currentItemRow . '" >
			
		
		</td>';

		$viewData .= '<td  class=" td_remove error-msg">

		<input type="text" name="account_subhead2_item[]" id="account_subhead2_item_' . $currentItemRow . '" class="form-control account_subhead2_item accounthead2" data-value="' . $currentItemRow . '" readonly>

			
			<input type="hidden" name="h2[]" id="h2_' . $currentItemRow . '" class="form-control h2" value="' . $itemDetail->sub_account_head2 . '" data-value="' . $currentItemRow . '" >
			
		
		</td>';
		$viewData .= '<td  class=" td_remove error-msg">

		<input type="text" name="account_subhead3_item[]" id="account_subhead3_item_' . $currentItemRow . '" class="form-control account_subhead3_item accounthead3" data-value="' . $currentItemRow . '" readonly>

			
			<input type="hidden" name="h3[]" id="h3_' . $currentItemRow . '" class="form-control h3" value="' . $itemDetail->sub_account_head3 . '" data-value="' . $currentItemRow . '" >
			
		
		</td>';

		$viewData .= '<td>
		<input type="text" name="hsn_code_item[]" id="hsn_code_item_' . $currentItemRow . '" class="form-control hsn_code_item" value="' . $itemDetail->hsn_code . '" data-value="' . $currentItemRow . '" readonly>
			
		
		</td>';
		$viewData .= '<td>
		<input type="text" name="quntity_item[]" id="quntity_item_' . $currentItemRow . '" class="form-control quntity_item quntity_' . $itemDetail->id . '" value="' . $itemDetail->quantity . '" data-value="' . $currentItemRow . '" readonly >		
		</td>';
		if (!empty($itemDetail->transferred)) {
			$trnsfrd = $itemDetail->transferred;
		} else {
			$trnsfrd = 0;
		}
		$viewData .= '<td>
		<input type="text" id="transfer_item_' . $currentItemRow . '" class="form-control transfer_item transfer_' . $itemDetail->id . '" value="' . $trnsfrd . '" data-value="' . $currentItemRow . '" readonly dataid="' . $trnsfrd . '" >		
		</td>';


		$viewData .= '<td >
		<input type="text" name="rate_item[]" id="rate_item_' . $currentItemRow . '" class="form-control rate_item rate_' . $itemDetail->id . '"" value="' . number_format((float)$itemDetail->rate, 2, '.', '') . '" data-value="' . $currentItemRow . '" readonly>		
		</td>';
		$d1 = '';
		$d2 = '';
		$discountAmount = 0.00;
		if ($itemDetail->discount_type == 1) {
			$d1 = 'selected ';
			$discountAmount = number_format((float)$itemDetail->discount_per, 2, '.', '');
		}
		if ($itemDetail->discount_type == 0) {
			$d2 = 'selected ';
			$discountAmount = number_format((float)$itemDetail->discount_amount, 2, '.', '');
		}

		$viewData .= '<td class="discountField td_remove error-msg" style="display:none">
			<input type="text" name="discount_item[]" id="discount_item_' . $currentItemRow . '" class="form-control discount_item"  data-value="' . $currentItemRow . '"  value="' . $discountAmount . '" readonly>
			<select   name="discount_item_type[]" id="discount_item_type_' . $currentItemRow . '" class="form-control discount_item_type" data-value="' . $currentItemRow . '" disabled> 
				<option  value="1" ' . $d1 . '>%</option> 
				<option value="0" ' . $d2 . '>Rs.</option>  
			</select>		
		</td>';

		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="amount_item[]" id="amount_item_' . $currentItemRow . '" class="form-control amount_item" value="' . number_format((float)$itemDetail->amount, 2, '.', '') . '" data-value="' . $currentItemRow . '"   readonly >		
		</td>';


		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="taxable_item[]" id="taxable_item_' . $currentItemRow . '" class="form-control taxable_item"  data-value="' . $currentItemRow . '"  value="' . number_format((float)$itemDetail->taxable_amount, 2, '.', '') . '"  readonly >		
		</td>';
		if ($itemDetail->gst_type == 1) {
			$gst_val = $itemDetail->gst_per;
			$gst_amount = number_format((float)$itemDetail->taxable_amount * $gst_val / 100, 2, '.', '');
		} else if ($itemDetail->gst_type == 0) {

			$gst_val = number_format((float)$itemDetail->gst_amount, 2, '.', '');
			$gst_amount = $gst_val;
		} else {
			$gst_val = number_format((float)0, 2, '.', '');
			$gst_amount = number_format((float)0, 2, '.', '');
		}

		if ($itemDetail->igst_type == 1) {
			$igst_val = number_format((float)$itemDetail->igst_per, 2, '.', '');
			$igst_amount = number_format((float)$itemDetail->taxable_amount * $igst_val / 100, 2, '.', '');
		} else if ($itemDetail->igst_type == 1) {

			$igst_amount = $igst_val = number_format((float)$itemDetail->igst, 2, '.', '');
		} else {
			$igst_val = 0.00;
			$igst_amount = 0.00;
		}


		$tax = 0;
		$rr = '';
		$rr1 = '';
		if ($gst_amount > 0) {
			$tax = number_format((float)$gst_amount, 2, '.', '');
			$rr1 = 'readonly';
		}
		if ($igst_amount > 0) {
			$tax = number_format((float)$igst_amount, 2, '.', '');
			$rr = 'readonly';
		}

		$viewData .= '<td class=" td_remove error-msg">
			<input type="text" name="gst_item[]" id="gst_item_' . $currentItemRow . '" class="form-control gst_item" ' . $rr . ' value="' . $gst_val . '" data-value="' . $currentItemRow . '" readonly>
			<select   name="gst_item_type[]" id="gst_item_type_' . $currentItemRow . '" class="form-control gst_item_type" data-value="' . $currentItemRow . '" disabled> ';

		if ($expence_data->gst_type == 1) {
			$viewData .= '<option  value="1" selected >%</option> 
					<option value="0">Rs.</option> > ';
			$cgst_val = number_format((float)$itemDetail->cgst_per, 2, '.', '');
			$sgst_val = number_format((float)$itemDetail->sgst_per, 2, '.', '');
		} else if ($expence_data->gst_type == 0) {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" selected>Rs.</option> > ';
			$cgst_val = number_format((float)$itemDetail->cgst, 2, '.', '');
			$sgst_val = number_format((float)$itemDetail->sgst, 2, '.', '');
		} else {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" >Rs.</option> > ';
			$cgst_val = '';
			$sgst_val = '';
		}
		$viewData .= '	</select>		
		</td>';

		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="cgst_item[]" id="cgst_item_' . $currentItemRow . '" class="form-control cgst_item"  value="' . $cgst_val . '"  readonly data-value="' . $currentItemRow . '" >		
		</td>';
		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="sgst_item[]" id="sgst_item_' . $currentItemRow . '" class="form-control sgst_item"  value="' . $sgst_val . '" readonly data-value="' . $currentItemRow . '" >		
		</td>';



		$viewData .= '<td class=" td_remove error-msg">
			<input type="text" name="igst_item[]" id="igst_item_' . $currentItemRow . '" class="form-control igst_item" readonly value="' . $igst_val . '" data-value="' . $currentItemRow . '" readonly>
			<select   name="igst_item_type[]" id="igst_item_type_' . $currentItemRow . '" class="form-control igst_item_type" data-value="' . $currentItemRow . '" disabled> ';

		if ($itemDetail->igst_type == 1) {
			$viewData .= '<option  value="1" selected >%</option> 
					<option value="0">Rs.</option> > ';
		} else if ($itemDetail->igst_type == 1) {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" selected>Rs.</option> > ';
		} else {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" >Rs.</option> > ';
		}
		$viewData .= '	</select>		
		</td>';
		// <input type="file" name="bill_upload[]" id="bill_upload_'.$currentItemRow.'" class="form-control bill_upload"  data-value="'.$currentItemRow.'" >
		$viewData .= '<td class=" td_remove error-msg">';
		// $url = URL::to("/core/storage/images/bill_expense/" . $itemDetail->bill_upload . "");
		$url = ImageUpload::generatePreSignedUrl('bill_expense/' . $itemDetail->bill_upload);
		$viewData .=	'<a href="' . $url . '" target="blank">' . $itemDetail->bill_upload .  '</a>';
		'</td>';


		$total_Pay = number_format((float)$itemDetail->total_amount, 2, '.', '');
		$url_view = URL::to("admin/bill/view-edit/" . $itemDetail->id . "");

		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="total_amount_pay[]" id="total_amount_pay_' . $currentItemRow . '" class="form-control total_amount_pay"  data-value="' . $currentItemRow . '" value="' . $total_Pay . '" readonly >		
		</td>';

		//if($currentItemRow > 0)
		//{

		if (check_my_permission(Auth::user()->id, "265") == "1" || check_my_permission(Auth::user()->id, "268") == "1") {
			$viewData .= '<td class=" td_remove error-msg">';
		}
		if (check_my_permission(Auth::user()->id, "265") == "1") {
			$viewData .= '<button style="margin-right: 5px;" type="button" dataid="' . $itemDetail->id . '" class="btn btn-primary trnasfer_btn" data-toggle="modal" data-target="#Statement_balance"><i class="fas fa-plus fa-1x" title="Transfer" ></i></button>';
		}

		if (check_my_permission(Auth::user()->id, "268") == "1") {

			$viewData .= '<a href="' . $url_view . '" dataid="' . $itemDetail->id . '" class="btn btn-primary " title="List"><i class="fa fa-list "></i></button>';
		}
		if (check_my_permission(Auth::user()->id, "265") == "1" || check_my_permission(Auth::user()->id, "268") == "1") {
			$viewData .= '</td>';
		}
		//}	


		return \Response::json(['view' => $viewData, 'msg_type' => 'success', 'expence_data' => $itemDetail]);
	}

	public function get_item_details_edit(Request $request)
	{
		$itemID = $request->item_id;
		$itemIdrow = $request->itemIdrow;
		$currentItemRow = $request->currentItemRow;

		$expence_data = ExpenseItem::where('id', $itemID)->first();
		$itemDetail = VendorBillItem::where('id', $itemIdrow)->first();

		$viewData = '';

		if (isset($itemDetail->account_head)) {
			$viewData .= '<td  class=" td_remove error-msg"><input type="hidden" name="itemIDs[]" id="itemIDs_' . $currentItemRow . '" class="form-control itemIDs" value="' . $itemDetail->exp_item_id . '" data-value="' . $currentItemRow . '" >
			<select   name="account_head_item[]" id="account_head_item_' . $currentItemRow . '" class="form-control account_head_item" data-value="' . $currentItemRow . '" > 
				<option  value="">Select Account Head</option>';
			if ($itemDetail->account_head == 9) {
				$viewData .= '<option  value="9" selected >Fixed Assets</option> 
					<option value="86">Indirect Expense</option> ';
			} else if ($itemDetail->account_head == 86) {
				$viewData .= '<option  value="9"  >Fixed Assets</option> 
					<option value="86" selected>Indirect Expense</option> ';
			} else {
				$viewData .= '<option  value="9"  >Fixed Assets</option> 
					<option value="86"  >Indirect Expense</option> ';
			}

			$viewData .= '</select>
				<input type="hidden" name="h[]" id="h_' . $currentItemRow . '" class="form-control h" value="' . $itemDetail->account_head . '" data-value="' . $currentItemRow . '" >
			</td>';
		} else {
			$viewData .= '<td  class=" td_remove error-msg"> 
			
			<select   name="account_head_item[]" id="account_head_item_' . $currentItemRow . '" class="form-control account_head_item accounthead" data-value="' . $currentItemRow . '" > 
				<option  value="">Select Account Head</option>   
				<option  value="9">Fixed Assets</option> 
				<option value="86">Indirect Expense</option> 
			</select>
			<input type="hidden" name="h[]" id="h_' . $currentItemRow . '" class="form-control h" value="' . $itemDetail->account_head . '" data-value="' . $currentItemRow . '" >
			</td>';
		}

		$viewData .= '<td  class=" td_remove error-msg">
			<select   name="account_subhead1_item[]" id="account_subhead1_item_' . $currentItemRow . '" class="form-control account_subhead1_item accounthead1" data-value="' . $currentItemRow . '" > 
				<option  value="">Select Account Sub Head1</option>   
			</select>
			<input type="hidden" name="h1[]" id="h1_' . $currentItemRow . '" class="form-control h1" value="' . $itemDetail->sub_account_head1 . '" data-value="' . $currentItemRow . '" >
			
		
		</td>';

		$viewData .= '<td  class=" td_remove error-msg">
			<select   name="account_subhead2_item[]" id="account_subhead2_item_' . $currentItemRow . '" class="form-control account_subhead2_item accounthead2" data-value="' . $currentItemRow . '" > 
				<option  value="">Select Account Sub Head2</option>   
			</select>
			<input type="hidden" name="h2[]" id="h2_' . $currentItemRow . '" class="form-control h2" value="' . $itemDetail->sub_account_head2 . '" data-value="' . $currentItemRow . '" >
			
		
		</td>';
		$viewData .= '<td  class=" td_remove error-msg">
			<select   name="account_subhead3_item[]" id="account_subhead3_item_' . $currentItemRow . '" class="form-control account_subhead3_item accounthead3" data-value="' . $currentItemRow . '" > 
				<option  value="">Select Account Sub Head3</option>   
			</select>
			<input type="hidden" name="h3[]" id="h3_' . $currentItemRow . '" class="form-control h3" value="' . $itemDetail->sub_account_head3 . '" data-value="' . $currentItemRow . '" >
			
		
		</td>';

		$viewData .= '<td  class=" td_remove error-msg">
		<input type="text" name="hsn_code_item[]" id="hsn_code_item_' . $currentItemRow . '" class="form-control hsn_code_item" value="' . $itemDetail->hsn_code . '" data-value="' . $currentItemRow . '" >
			
		
		</td>';
		$viewData .= '<td  class=" td_remove error-msg">
		<input type="text" name="quntity_item[]" id="quntity_item_' . $currentItemRow . '" class="form-control quntity_item" value="' . $itemDetail->quantity . '" data-value="' . $currentItemRow . '" onkeypress="return  integerTrue(event)">		
		</td>';


		$viewData .= '<td  class=" td_remove error-msg">
		<input type="text" name="rate_item[]" id="rate_item_' . $currentItemRow . '" class="form-control rate_item" value="' . number_format((float)$itemDetail->rate, 2, '.', '') . '" data-value="' . $currentItemRow . '" onkeypress="return isNumberKey(event)">		
		</td>';
		$d1 = '';
		$d2 = '';
		$discountAmount = 0.00;
		if ($itemDetail->discount_type == 1) {
			$d1 = 'selected ';
			$discountAmount = number_format((float)$itemDetail->discount_per, 2, '.', '');
		}
		if ($itemDetail->discount_type == 0) {
			$d2 = 'selected ';
			$discountAmount = number_format((float)$itemDetail->discount_amount, 2, '.', '');
		}

		$viewData .= '<td class="discountField td_remove error-msg" style="display:none">
			<input type="text" name="discount_item[]" id="discount_item_' . $currentItemRow . '" class="form-control discount_item"  data-value="' . $currentItemRow . '"  value="' . $discountAmount . '" onkeypress="return isNumberKey(event)">
			<select   name="discount_item_type[]" id="discount_item_type_' . $currentItemRow . '" class="form-control discount_item_type" data-value="' . $currentItemRow . '" > 
				<option  value="1" ' . $d1 . '>%</option> 
				<option value="0" ' . $d2 . '>Rs.</option>  
			</select>		
		</td>';

		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="amount_item[]" id="amount_item_' . $currentItemRow . '" class="form-control amount_item" value="' . number_format((float)$itemDetail->amount, 2, '.', '') . '" data-value="' . $currentItemRow . '"   readonly >		
		</td>';


		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="taxable_item[]" id="taxable_item_' . $currentItemRow . '" class="form-control taxable_item"  data-value="' . $currentItemRow . '"  value="' . number_format((float)$itemDetail->taxable_amount, 2, '.', '') . '"  readonly >		
		</td>';
		if ($itemDetail->gst_type == 1) {
			$gst_val = $itemDetail->gst_per;
			$gst_amount = number_format((float)$itemDetail->taxable_amount * $gst_val / 100, 2, '.', '');
		} else if ($itemDetail->gst_type == 0) {

			$gst_val = number_format((float)$itemDetail->gst_amount, 2, '.', '');
			$gst_amount = $gst_val;
		} else {
			$gst_val = number_format((float)0, 2, '.', '');
			$gst_amount = number_format((float)0, 2, '.', '');
		}

		if ($itemDetail->igst_type == 1) {
			$igst_val = number_format((float)$itemDetail->igst_per, 2, '.', '');
			$igst_amount = number_format((float)$itemDetail->taxable_amount * $igst_val / 100, 2, '.', '');
		} else if ($itemDetail->igst_type == 1) {

			$igst_amount = $igst_val = number_format((float)$itemDetail->igst, 2, '.', '');
		} else {
			$igst_val = 0.00;
			$igst_amount = 0.00;
		}


		$tax = 0;
		$rr = '';
		$rr1 = '';
		if ($gst_amount > 0) {
			$tax = number_format((float)$gst_amount, 2, '.', '');
			$rr1 = 'readonly';
		}
		if ($igst_amount > 0) {
			$tax = number_format((float)$igst_amount, 2, '.', '');
			$rr = 'readonly';
		}

		$viewData .= '<td class=" td_remove error-msg">
			<input type="text" name="gst_item[]" id="gst_item_' . $currentItemRow . '" class="form-control gst_item" ' . $rr . ' value="' . $gst_val . '" data-value="' . $currentItemRow . '" onkeypress="return isNumberKey(event)">
			<select   name="gst_item_type[]" id="gst_item_type_' . $currentItemRow . '" class="form-control gst_item_type" data-value="' . $currentItemRow . '" > ';
		if ($expence_data->gst_type == 1) {
			$viewData .= '<option  value="1" selected >%</option> 
					<option value="0">Rs.</option> > ';
			$cgst_val = number_format((float)$itemDetail->cgst_per, 2, '.', '');
			$sgst_val = number_format((float)$itemDetail->sgst_per, 2, '.', '');
		} else if ($expence_data->gst_type == 0) {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" selected>Rs.</option> > ';
			$cgst_val = number_format((float)$itemDetail->cgst, 2, '.', '');
			$sgst_val = number_format((float)$itemDetail->sgst, 2, '.', '');
		} else {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" >Rs.</option> > ';
			$cgst_val = '';
			$sgst_val = '';
		}
		$viewData .= '	</select>		
		</td>';

		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="cgst_item[]" id="cgst_item_' . $currentItemRow . '" class="form-control cgst_item"  value="' . $cgst_val . '"  readonly data-value="' . $currentItemRow . '" >		
		</td>';
		$viewData .= '<td class=" td_remove error-msg">
		<input type="text" name="sgst_item[]" id="sgst_item_' . $currentItemRow . '" class="form-control sgst_item"  value="' . $sgst_val . '" readonly data-value="' . $currentItemRow . '" >		
		</td>';



		$viewData .= '<td class=" td_remove error-msg">
			<input type="text" name="igst_item[]" id="igst_item_' . $currentItemRow . '" class="form-control igst_item" ' . $rr1 . ' value="' . $igst_val . '" data-value="' . $currentItemRow . '" onkeypress="return isNumberKey(event)">
			<select   name="igst_item_type[]" id="igst_item_type_' . $currentItemRow . '" class="form-control igst_item_type" data-value="' . $currentItemRow . '" > ';
		if ($itemDetail->igst_type == 1) {
			$viewData .= '<option  value="1" selected >%</option> 
					<option value="0">Rs.</option> > ';
		} else if ($itemDetail->igst_type == 1) {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" selected>Rs.</option> > ';
		} else {
			$viewData .= '<option  value="1"  >%</option> 
					<option value="0" >Rs.</option> > ';
		}
		$viewData .= '	</select>		
		</td>';

		// $viewData .= '<td class=" td_remove error-msg">
		// <input type="file" name="bill_upload[]" id="bill_upload_' . $currentItemRow . '" class="form-control bill_upload"  data-value="' . $currentItemRow . '" >';
		// $url = URL::to("/core/storage/images/bill_expense/" . $itemDetail->bill_upload . "");
		$url = ImageUpload::generatePreSignedUrl('bill_expense/' . $itemDetail->bill_upload);
		$viewData .=	'<a href="' . $url . '" target="blank">' . $itemDetail->bill_upload .  '</a>';
		'</td>';


		$total_Pay = number_format((float)$itemDetail->total_amount, 2, '.', '');

		$viewData .= '<td class=" td_remove error-msg">
						<input type="text" name="total_amount_pay[]" id="total_amount_pay_' . $currentItemRow . '" class="form-control total_amount_pay"  data-value="' . $currentItemRow . '" value="' . $total_Pay . '" readonly >		
					</td>';

		if ($currentItemRow > 0) {
			$viewData .= '<td class=" td_remove error-msg">
							<button type="button" class="btn btn-primary remCF"><i class="icon-trash"></i></button>		
						</td>';
		}


		return \Response::json(['view' => $viewData, 'msg_type' => 'success', 'expence_data' => $itemDetail]);
	}


	/**
	 * Save bill.
	 * Route: /bill/create
	 * Method: Post 
	 * @return  array()  Response

	 */

	public function update(Request $request)
	{
		$rules = [
			'vendor_id' => 'required',
			'bill' =>      'required',
			'bill_date' => 'required',
			'branch_id' => 'required',
			'discount' => 'required',
			'total_sub' => 'required',
		];
		$customMessages = [
			'required' => 'The :attribute field is required.'
		];
		$this->validate($request, $rules, $customMessages);
		DB::beginTransaction();
		try {
			$companyId = $request->company_id;
			$vendorBillID = $bill_id = $request->bill_id;
			$globaldate = $request->bill_created_at;
			$select_date = $request->bill_date;
			$current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
			$entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
			$entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
			$date_create = $entry_date . ' ' . $entry_time;
			$created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
			$updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));

			$currency_code = 'INR';
			$created_by = 1;
			$created_by_id = \Auth::user()->id;
			$created_by_name = \Auth::user()->username;
			$randNumber = mt_rand(0, 999999999999999);
			$v_no = $randNumber;
			$v_date = $entry_date;

			$type = 27;
			$sub_type = 271;

			$branch_id = $request->branch_id;

			$vendorBill['branch_id'] = $request->branch_id;
			$vendorBill['vendor_id'] = $request->vendor_id;
			$vendorBill['bill_type'] = 0;
			$vendorBill['bill_date'] = date("Y-m-d", strtotime(convertDate($request->bill_date)));
			$vendorBill['bill_number'] = $request->bill;;
			$vendorBill['discount_type'] = $request->discount;

			$vendorBill['total_item'] = count($_POST['item_id']);
			$vendorBill['total_item_amount'] = $request->total_sub;
			$vendorBill['total_discount_type'] = $request->total_discount_type;
			$vendorBill['total_discount_per'] = $request->total_discount_val;
			$vendorBill['total_discount_amount'] = $request->total_dis_amt;

			$vendorBill['sub_amount'] = $request->total_sub;
			$vendorBill['tds_head'] = $request->tds_head;
			$vendorBill['tds_type'] = 1;
			$vendorBill['tds_per'] = $request->tds_per;
			$vendorBill['tds_amount'] = $request->tds_amt;
			$vendorBill['payble_amount'] = $request->total_amountPay;
			//$vendorBill['due_amount']=2; 
			//$vendorBill['transferd_amount']=2; 
			$vendorBill['balance'] = $request->total_amountPay;
			$vendorBill['status'] = 0;
			$vendorBill['adj_amount'] = $request->final_adj_amount;
			$vendorBill['created_at'] = $created_at;
			$vendorBill['description'] = $request->description;
			$vendorBill['company_id'] = $request->company_id;

			$vendorBillUpdate = VendorBill::find($bill_id);
			$vendorBillUpdate->update($vendorBill);
			if (isset($request->bill_upload)) {
				// $mainFolder = storage_path() . '/images/bill_expense';
				$mainFolder = 'bill_expense';
				$file = $request->file('bill_upload');
				$uploadFile = $file->getClientOriginalName();
				$filename = pathinfo($uploadFile, PATHINFO_FILENAME);
				$fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
				ImageUpload::upload($file, $mainFolder,$fname);
				// $file->move($mainFolder, $fname);
				$fData = [
					'file_name' => $fname,
					'file_path' => $mainFolder,
					'file_extension' => $file->getClientOriginalExtension(),
				];
				$venderbillUpdate = VendorBill::find($bill_id);
				$venderbillUpdate->bill_upload = $fname;
				$venderbillUpdate->save();
			}
			$total_gst_amount = 0;
			$refIdGet1 = VendorBill::where('id', $bill_id)->first(['daybook_ref_id', 'id']);
			$refId = $refIdGet = $refIdGet1->daybook_ref_id;
			$data['amount'] = $request->total_amountPay;
			$dataUpdate = \App\Models\BranchDaybookReference::find($refId);
			$dataUpdate->update($data);

			$AllHeadTransaction = \App\Models\AllHeadTransaction::where('daybook_ref_id', $refIdGet)->update(['is_deleted' => 1]);
			$VendorBillItem = VendorBillItem::where('daybook_ref_id', $refIdGet)->update(['is_deleted' => 1]);
			$VendorTransaction = VendorTransaction::where('daybook_ref_id', $refIdGet)->update(['is_deleted' => 1]);

			$demandAdvic = \App\Models\DemandAdvice::where('daybook_ref_id', $refIdGet)->update(['is_deleted' => 1]);
			$demandAdviceExpense = \App\Models\DemandAdviceExpense::where('daybook_ref_id', $refIdGet)->update(['is_deleted' => 1]);
			$total_gst_amount = 0;
			$bank_id = NULL;
			$bank_ac_id = NULL;
			$member_id = NULL;
			$ssb_account_id_from = NULL;
			$cheque_no = NULL;
			$transction_no = NULL;
			$jv_unique_id = NULL;
			$ssb_account_id_to = NULL;
			$ssb_account_tran_id_to = NULL;
			$ssb_account_tran_id_from = NULL;
			$cheque_type = NULL;
			$cheque_id = NULL;
			$tranId = $vendorBillID;
			$billItemDetail = '';
			$billId = '';

			if (isset($_POST['item_id'])) {
				foreach (($_POST['item_id']) as $key => $option) {
					if ($_POST['item_id'][$key] > 0) {

						$getItemName = ExpenseItem::where('id', $_POST['item_id'][$key])->first();
						$billItem['vendor_bill_id'] = $vendorBillID;
						$billItem['account_head'] = $_POST['account_head_item'][$key];

						$billItem['exp_item_id'] = $_POST['itemIDs'][$key];

						if ($_POST['account_head_item'][$key] > 0) {
							$expHead = $_POST['account_head_item'][$key];
						}
						if ($_POST['account_subhead1_item'][$key] > 0) {
							$expHead = $_POST['account_subhead1_item'][$key];
							$billItem['sub_account_head1'] = $_POST['account_subhead1_item'][$key];
						}
						if ($_POST['account_subhead2_item'][$key] > 0) {
							$expHead = $_POST['account_subhead2_item'][$key];
							$billItem['sub_account_head2'] = $_POST['account_subhead2_item'][$key];
						}
						if ($_POST['account_subhead3_item'][$key] > 0) {
							$expHead = $_POST['account_subhead3_item'][$key];
							$billItem['sub_account_head3'] = $_POST['account_subhead3_item'][$key];
						}


						$billItem['item_name'] = $getItemName->name;
						$billItem['type'] = $getItemName->type;
						$billItem['hsn_code'] = $_POST['hsn_code_item'][$key];
						$billItem['description'] = $getItemName->description;
						$billItem['price'] = $getItemName->price;
						$billItem['gst_type'] = $_POST['gst_item_type'][$key];
						if ($_POST['gst_item'][$key] > 0) {

							if ($_POST['gst_item_type'][$key] == 1) {
								$billItem['cgst_per'] = $_POST['cgst_item'][$key];
								$billItem['sgst_per'] = $_POST['sgst_item'][$key];
								$billItem['gst_per'] = $_POST['gst_item'][$key];
							} else {
								$billItem['cgst'] = $_POST['cgst_item'][$key];
								$billItem['sgst'] = $_POST['sgst_item'][$key];
								$billItem['gst_amount'] = $_POST['gst_item'][$key];
							}
						}
						$billItem['igst_type'] = $_POST['igst_item_type'][$key];
						if ($_POST['igst_item'][$key] > 0) {
							if ($_POST['igst_item_type'][$key] == 1) {
								$billItem['igst_per'] = $_POST['igst_item'][$key];
							} else {
								$billItem['igst'] = $_POST['igst_item'][$key];
							}
						}
						$expAmount =	$_POST['taxable_item'][$key];
						if ($request->discount == 1) {
							$billItem['discount_type'] = $_POST['discount_item_type'][$key];
							if ($_POST['discount_item_type'][$key] == 1) {
								$billItem['discount_per'] = $_POST['discount_item'][$key];
								$itemDiscountAmount = ($_POST['amount_item'][$key] * $_POST['discount_item'][$key]) / 100;
							} else {
								$billItem['discount_amount'] = $_POST['discount_item'][$key];
								$itemDiscountAmount = $_POST['discount_item'][$key];
							}

							$expAmount = $_POST['amount_item'][$key];
						}
						$billItem['quantity'] = $_POST['quntity_item'][$key];
						$billItem['rate'] = $_POST['rate_item'][$key];
						$billItem['amount'] = $_POST['amount_item'][$key];
						$billItem['taxable_amount'] = $_POST['taxable_item'][$key];
						$billItem['total_amount'] = $_POST['total_amount_pay'][$key];
						$billItem['created_at'] = $created_at;
						$billItem['updated_at'] = $updated_at;
						$billItem['daybook_ref_id'] = $refId;
						$billItem['company_id'] = $request->company_id;
						$BillItemCreate = VendorBillItem::create($billItem);
						$billItemID = $BillItemCreate->id;
						$billAdvice['payment_type'] = 0;
						$billAdvice['sub_payment_type'] = 0;
						$billAdvice['branch_id'] = $branch_id;
						$billAdvice['date'] = $entry_date;
						$billAdvice['is_mature'] = 0;
						$billAdvice['ta_advanced_adjustment'] = 0;
						$billAdvice['is_print'] = 0;
						$billAdvice['payment_mode'] = 3;
						$billAdvice['status'] = 1;
						$billAdvice['created_at'] = $created_at;
						$billAdvice['updated_at'] = $updated_at;
						$billAdvice['daybook_ref_id'] = $refId;

						$billAdvice['billId'] = $vendorBillID;
						$billAdvice['billItemId'] = $billItemID;
						$billAdvice['is_bill'] = 1;
						$billAdvice['company_id'] = $request->company_id;
						$billAdviceCreate = \App\Models\DemandAdvice::create($billAdvice);
						$billAdviceID = $billAdviceCreate->id;

						$billAdviceExp['demand_advice_id'] = $billAdviceID;
						$billAdviceExp['party_name'] = getVendorDetail($request->vendor_id)->name;
						$billAdviceExp['particular'] = $getItemName->name . ' purchase ' . $_POST['taxable_item'][$key];
						$billAdviceExp['mobile_number'] = getVendorDetail($request->vendor_id)->mobile_no;
						$billAdviceExp['amount'] = $_POST['total_amount_pay'][$key];

						$billAdviceExp['is_assets'] = 0;
						$billAdviceExp['assets_category'] = $_POST['account_head_item'][$key];
						$billAdviceExp['assets_subcategory'] = $expHead;
						$billAdviceExp['status'] = 0;
						$billAdviceExp['current_balance'] = $_POST['taxable_item'][$key];
						$billAdviceExp['purchase_date'] = $entry_date;

						$billAdviceExp['billId'] = $vendorBillID;
						$billAdviceExp['billItemId'] = $billItemID;
						$billAdviceExp['is_bill'] = 1;



						$billAdviceExp['created_at'] = $created_at;
						$billAdviceExp['updated_at'] = $updated_at;
						$billAdviceExp['daybook_ref_id'] = $refId;
						$billAdviceExpCreate = \App\Models\DemandAdviceExpense::create($billAdviceExp);
						$billAdviceExpCreateID = $billAdviceExpCreate->id;

						$billItemDetail = $getItemName->name . " ( Rs." . number_format((float)$_POST['total_amount_pay'][$key], 2, '.', '') . "), " . $billItemDetail;
						$billId = $getItemName->id . ',' . $billId;

						$gst_amount_item1 = $_POST['total_amount_pay'][$key] - $_POST['taxable_item'][$key];
						$gst_amount_item = $gst_amount_item1 / 2;
						$total_gst_amount = $total_gst_amount + $gst_amount_item;

						//expence
						$getNature = AccountHeads::where('head_id', $expHead)->first();
						if ($getNature->cr_nature == 1) {
							$eMode = 'CR';
						}
						if ($getNature->dr_nature == 1) {
							$eMode = 'DR';
						}

						$desExp = 'Bill(' . $request->bill . ') (' . $getItemName->name . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

						$desTran = $desExp;
						$vendorTran1['type'] = 1;
						$vendorTran1['sub_type'] = 11;
						$vendorTran1['type_id'] = $vendorBillID;
						$vendorTran1['vendor_id'] = $request->vendor_id;
						$vendorTran1['branch_id'] = $branch_id;
						$vendorTran1['amount'] = $expAmount;
						$vendorTran1['description'] = $desTran;
						$vendorTran1['payment_type'] = 'CR';
						$vendorTran1['payment_mode'] = 3;
						$vendorTran1['currency_code'] = 'INR';
						$vendorTran1['v_no'] = $v_no;
						$vendorTran1['entry_date'] = $entry_date;
						$vendorTran1['entry_time'] = $entry_time;
						$vendorTran1['created_by'] = 1;
						$vendorTran1['created_by_id'] = $created_by_id;
						$vendorTran1['created_at'] = $created_at;
						$vendorTran1['updated_at'] = $updated_at;
						$vendorTran1['daybook_ref_id'] = $refId;
						$vendorTran1['company_id'] = $request->company_id;

						$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
						$vendorTranID1 = $vendorTranCreate1->id;

						///libility
						$LibHead = 140;
						$desLibe = 'Bill(' . $request->bill . ') (' . $getItemName->name . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');
						$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $expAmount,  $desLibe, 'CR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


						$allTran2 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $expHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $expAmount,  $desExp, $eMode, 3, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
						// item level discount ------- 

						if ($request->discount == 1) {
							if ($itemDiscountAmount > 0) {
								$desExpD = 'Disount of Rs.' . number_format((float)$itemDiscountAmount, 2, '.', '') . ' On Bill(' . $request->bill . ') (' . $getItemName->name . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

								$allTran21 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 240, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $itemDiscountAmount, $desExpD, 'CR', 3, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
								///libility
								$LibHead = 140;
								$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $itemDiscountAmount, $desExpD, 'DR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


								$vendorTran1['type'] = 1;
								$vendorTran1['sub_type'] = 11;
								$vendorTran1['type_id'] = $vendorBillID;
								$vendorTran1['vendor_id'] = $request->vendor_id;
								$vendorTran1['branch_id'] = $branch_id;
								$vendorTran1['amount'] = $itemDiscountAmount;
								$vendorTran1['description'] = $desExpD;
								$vendorTran1['payment_type'] = 'DR';
								$vendorTran1['payment_mode'] = 3;
								$vendorTran1['currency_code'] = 'INR';
								$vendorTran1['v_no'] = $v_no;
								$vendorTran1['entry_date'] = $entry_date;
								$vendorTran1['entry_time'] = $entry_time;
								$vendorTran1['created_by'] = 1;
								$vendorTran1['created_by_id'] = $created_by_id;
								$vendorTran1['created_at'] = $created_at;
								$vendorTran1['updated_at'] = $updated_at;
								$vendorTran1['daybook_ref_id'] = $refId;
								$vendorTran1['company_id'] = $request->company_id;

								$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
								$vendorTranID1 = $vendorTranCreate1->id;
							}
						}
						/// GST
						if ($_POST['gst_item'][$key] > 0) {
							if ($gst_amount_item > 0) {
								$cgstHead = 171;

								$desGst = 'CGST of Rs.' . number_format((float)$gst_amount_item, 2, '.', '') . ' On Bill(' . $request->bill . ') (' . $getItemName->name . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

								$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $gst_amount_item,  $desGst, 'DR', 3, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

								$sgstHead = 172;

								$desSGst = 'SGST of Rs.' . number_format((float)$gst_amount_item, 2, '.', '') . ' On Bill(' . $request->bill . ') (' . $getItemName->name . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

								$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $sgstHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gst_amount_item,  $desSGst, 'DR', 3, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
								$gstAmount = $gst_amount_item * 2;

								$desgSGst = 'GST of Rs.' . number_format((float)$gstAmount, 2, '.', '') . ' On Bill(' . $request->bill . ') (' . $getItemName->name . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

								$vendorTran1['type'] = 1;
								$vendorTran1['sub_type'] = 11;
								$vendorTran1['type_id'] = $vendorBillID;
								$vendorTran1['vendor_id'] = $request->vendor_id;
								$vendorTran1['branch_id'] = $branch_id;
								$vendorTran1['amount'] = $gstAmount;
								$vendorTran1['description'] = $desgSGst;
								$vendorTran1['payment_type'] = 'CR';
								$vendorTran1['payment_mode'] = 3;
								$vendorTran1['currency_code'] = 'INR';
								$vendorTran1['v_no'] = $v_no;
								$vendorTran1['entry_date'] = $entry_date;
								$vendorTran1['entry_time'] = $entry_time;
								$vendorTran1['created_by'] = 1;
								$vendorTran1['created_by_id'] = $created_by_id;
								$vendorTran1['created_at'] = $created_at;
								$vendorTran1['updated_at'] = $updated_at;
								$vendorTran1['daybook_ref_id'] = $refId;
								$vendorTran1['company_id'] = $companyId;
								$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
								$vendorTranID1 = $vendorTranCreate1->id;
								///libility
								$LibHead = 140;
								$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $desgSGst, 'CR', 3, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
							}
						}
						if ($_POST['igst_item'][$key] > 0) {
							$igstHead = 170;
							if ($gst_amount_item1 > 0) {
								$desIGst = 'ISGST of Rs.' . number_format((float)$gst_amount_item1, 2, '.', '') . ' On Bill(' . $request->bill . ') (' . $getItemName->name . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

								$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $igstHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gst_amount_item1,  $desIGst, 'DR', 3, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

								///libility
								$LibHead = 140;
								$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gst_amount_item1, $desIGst, 'CR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


								$vendorTran1['type'] = 1;
								$vendorTran1['sub_type'] = 11;
								$vendorTran1['type_id'] = $vendorBillID;
								$vendorTran1['vendor_id'] = $request->vendor_id;
								$vendorTran1['branch_id'] = $branch_id;
								$vendorTran1['amount'] = $gst_amount_item1;
								$vendorTran1['description'] = $desIGst;
								$vendorTran1['payment_type'] = 'CR';
								$vendorTran1['payment_mode'] = 3;
								$vendorTran1['currency_code'] = 'INR';
								$vendorTran1['v_no'] = $v_no;
								$vendorTran1['entry_date'] = $entry_date;
								$vendorTran1['entry_time'] = $entry_time;
								$vendorTran1['created_by'] = 1;
								$vendorTran1['created_by_id'] = $created_by_id;
								$vendorTran1['created_at'] = $created_at;
								$vendorTran1['updated_at'] = $updated_at;
								$vendorTran1['daybook_ref_id'] = $refId;
								$vendorTran1['company_id'] = $companyId;

								$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
								$vendorTranID1 = $vendorTranCreate1->id;
							}
						}
					}
				}
			}
			$vendorLog['vendor_id'] = $request->vendor_id;
			$vendorLog['vendor_bill_id'] = $vendorBillID;
			$vendorLog['title'] = 'Bill Updated';
			$vendorLog['bill_no'] = $request->bill;
			$vendorLog['description'] = 'Bill Updated  for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '') . ' by ' . $created_by_name;
			$vendorLog['amount'] = $request->total_amountPay;
			$vendorLog['item_detail'] = $billItemDetail;
			$vendorLog['item_id'] = $billId;
			$vendorLog['created_by'] = 1;
			$vendorLog['created_by_id'] = $created_by_id;
			$vendorLog['created_by_name'] = $created_by_name;
			$vendorLog['daybook_ref_id'] = $refId;
			$vendorLog['created_at'] = $created_at;
			$vendorLog['updated_at'] = $updated_at;
			$vendorLogCreate = VendorLog::create($vendorLog);
			$vendorLogID = $vendorLogCreate->id;

			// transaction  level discount ------- 

			if ($request->discount == 2) {
				if ($request->total_dis_amt > 0) {
					$desExpD = 'Disount of Rs.' . number_format((float)$request->total_dis_amt, 2, '.', '') . ' On Bill(' . $request->bill . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

					$allTran21 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 240, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $request->total_dis_amt,  $desExpD, 'CR', 3, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
					///libility
					$LibHead = 140;
					$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $request->total_dis_amt, $desExpD, 'DR', 3, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


					$vendorTran1['type'] = 1;
					$vendorTran1['sub_type'] = 11;
					$vendorTran1['type_id'] = $vendorBillID;
					$vendorTran1['vendor_id'] = $request->vendor_id;
					$vendorTran1['branch_id'] = $branch_id;
					$vendorTran1['amount'] = $request->total_dis_amt;
					$vendorTran1['description'] = $desExpD;
					$vendorTran1['payment_type'] = 'DR';
					$vendorTran1['payment_mode'] = 3;
					$vendorTran1['currency_code'] = 'INR';
					$vendorTran1['v_no'] = $v_no;
					$vendorTran1['entry_date'] = $entry_date;
					$vendorTran1['entry_time'] = $entry_time;
					$vendorTran1['created_by'] = 1;
					$vendorTran1['created_by_id'] = $created_by_id;
					$vendorTran1['created_at'] = $created_at;
					$vendorTran1['updated_at'] = $updated_at;
					$vendorTran1['daybook_ref_id'] = $refId;
					$vendorTran1['company_id'] = $companyId;

					$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
					$vendorTranID1 = $vendorTranCreate1->id;
				}
			}


			///TDS
			$tdsHead = $request->tds_head;
			if ($request->tds_head > 0) {
				if ($request->tds_amt_final > 0) {
					$getNatureTds = AccountHeads::where('head_id', $request->tds_head)->first();
					if ($getNatureTds->cr_nature == 1) {
						$eModeT = 'CR';
					}
					if ($getNatureTds->dr_nature == 1) {
						$eModeT = 'DR';
					}


					$desT = 'TDS of Rs.' . number_format((float)$request->tds_amt_final, 2, '.', '') . ' On Bill(' . $request->bill . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

					$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $tdsHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $request->tds_amt_final, $desT, $eModeT, 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


					///libility
					$LibHead = 140;
					$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $request->tds_amt_final, $desT, 'DR', 3, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


					$vendorTran1['type'] = 1;
					$vendorTran1['sub_type'] = 11;
					$vendorTran1['type_id'] = $vendorBillID;
					$vendorTran1['vendor_id'] = $request->vendor_id;
					$vendorTran1['branch_id'] = $branch_id;
					$vendorTran1['amount'] = $request->tds_amt_final;
					$vendorTran1['description'] = $desT;
					$vendorTran1['payment_type'] = 'DR';
					$vendorTran1['payment_mode'] = 3;
					$vendorTran1['currency_code'] = 'INR';
					$vendorTran1['v_no'] = $v_no;
					$vendorTran1['entry_date'] = $entry_date;
					$vendorTran1['entry_time'] = $entry_time;
					$vendorTran1['created_by'] = 1;
					$vendorTran1['created_by_id'] = $created_by_id;
					$vendorTran1['created_at'] = $created_at;
					$vendorTran1['updated_at'] = $updated_at;
					$vendorTran1['daybook_ref_id'] = $refId;
					$vendorTran1['company_id'] = $companyId;

					$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
					$vendorTranID1 = $vendorTranCreate1->id;
				}
			}
			///round of 
			if (strpos($request->final_adj_amount, '-') !== false) {
				$emoderr = 'CR';
				$emoderr1 = 'DR';
			} else {
				$emoderr = 'DR';
				$emoderr1 = 'CR';
			}
			$rAMountOff = trim($request->final_adj_amount, '-,+');
			if ($request->final_adj_amount != '') {
				if ($rAMountOff > 0) {
					$roundofHead = 178;

					$desROff = 'Adjustment of Rs.' . number_format((float)$rAMountOff, 2, '.', '') . ' On Bill(' . $request->bill . ') created for Rs.' . number_format((float)$request->total_amountPay, 2, '.', '');

					$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $roundofHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $rAMountOff, $desROff, $emoderr, 3, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


					///libility
					$LibHead = 140;
					$allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $request->vendor_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $rAMountOff,  $desROff, $emoderr1, 3, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


					$vendorTran1['type'] = 1;
					$vendorTran1['sub_type'] = 11;
					$vendorTran1['type_id'] = $vendorBillID;
					$vendorTran1['vendor_id'] = $request->vendor_id;
					$vendorTran1['branch_id'] = $branch_id;
					$vendorTran1['amount'] = $rAMountOff;
					$vendorTran1['description'] = $desROff;
					$vendorTran1['payment_type'] = $emoderr1;
					$vendorTran1['payment_mode'] = 3;
					$vendorTran1['currency_code'] = 'INR';
					$vendorTran1['v_no'] = $v_no;
					$vendorTran1['entry_date'] = $entry_date;
					$vendorTran1['entry_time'] = $entry_time;
					$vendorTran1['created_by'] = 1;
					$vendorTran1['created_by_id'] = $created_by_id;
					$vendorTran1['created_at'] = $created_at;
					$vendorTran1['updated_at'] = $updated_at;
					$vendorTran1['daybook_ref_id'] = $refId;
					$vendorTran1['company_id'] = $companyId;

					$vendorTranCreate1 = VendorTransaction::create($vendorTran1);
					$vendorTranID1 = $vendorTranCreate1->id;
				}
			}



			DB::commit();
		} catch (\Exception $ex) {
			// DB::rollback();
			// echo $ex->getLine();
			// echo '<br>';
			// echo $ex->getMessage();
			// die;
			return back()->with('alert', $ex->getMessage());
		}

		return back()->with('success', 'Bill Updated Successfully');
	}
}
