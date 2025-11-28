<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\{Auth, Hash};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Interfaces\RepositoryInterface;
use DB;
use App\Http\Requests\GstEditRequest;
use App\Http\Requests\HeadSettingRequest;
use URL;
use Illuminate\Support\Facades\Cache;
use Session;
use App\Http\Controllers\Admin\CommanController;

use App\Services\ImageUpload;

class DutiesAndTaxesController extends Controller
{
	public function __construct(RepositoryInterface $repository)
	{
		$this->middleware('auth');
		$this->repository = $repository;
	}
	// get routes functions
	public function company_settings(Request $req)
	{
		if (check_my_permission(auth()->user()->id, "267") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = "Duties & Taxes | GST Company Setting";
		$data['view'] = 0;
		return view('templates.admin.duties_taxes.gst.setting.company_settings', $data);
	}

	public function head_settings(Request $req)
	{
		if (check_my_permission(auth()->user()->id, "268") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Head Setting List';
		return view('templates.admin.duties_taxes.gst.setting.head_settings', $data);
	}

	public function add_head_settings()
	{
		if (check_my_permission(auth()->user()->id, "274") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Duties & Taxes | Add GST Head Setting';
		$data['heads'] = $this->repository->getAllAccountHeads()->whereIn('head_id', ['33', '90', '294', '35', '139', '203', '122'])->where('status', '!=', '9')->pluck('sub_head', 'head_id');

		return view('templates.admin.duties_taxes.gst.setting.add_head_settings', $data);
	}

	public function edit_head_settings($id)
	{
		$data['title'] = 'Duties & Taxes | Edit GST Head Setting';
		$i = base64_decode($id);
		$data['heads'] = $this->repository->getAllAccountHeads()->whereIn('head_id', ['33', '90', '294', '35', '139', '203', '122'])->where('status', '!=', '9')->pluck('sub_head', 'head_id');
		$data['record'] = $this->repository->getHeadSettingById($i)->first();
		return view('templates.admin.duties_taxes.gst.setting.add_head_settings', $data);
	}

	public function log_detail_listing(Request $req)
	{
		return $this->repository->getDutiesTaxesSettingLogById($req->id)->first();
	}

	public function outward_supply(Request $req)
	{
		if (check_my_permission(auth()->user()->id, "270") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'GST Report - Outward Supply';
		$data['data'] = $this->repository->getAllGstTransaction()->has('company')
			->select('id', 'type_id', 'invoice_number', 'created_at', 'total_amount', 'amount_of_tax_igst', 'customer_gst_no', 'amount_of_tax_cgst', 'amount_of_tax_sgst', 'tax_value', 'head_id')
			->with([
				'memberDetails' => function ($q) {
					$q->select('id', 'branch_id', 'first_name')
						->with([
							'branch' => function ($q) {
								$q->select('id', 'state_id')
									->with([
										'branchStatesCustom' => function ($q) {
											$q->select('id', 'name');
										}
									]);
							}
						]);
				},
				'gstHeadrate' => function ($q) {
					$q->select('id', 'head_id', 'gst_percentage');
				}
			])->paginate(20);
		return view('templates.admin.duties_taxes.gst.report.outward_supply', $data);
	}

	public function export_outward_supply(Request $req)
	{
		if ($req['export_outward_report_extension'] == 0) {
			$input = $req->all();

			$start = $input["start"];
			$limit = $input["limit"];

			$returnURL = URL::to('/') . "/asset/GstOutwardReport.csv";
			$fileName = "/home/mysamraddh/public_html/asset/GstOutwardReport.csv";

			global $wpdb;
			$postCols = array(
				'post_title',
				'post_content',
				'post_excerpt',
				'post_name',
			);
			header("Content-type: text/csv");
		}
		$data = $this->repository->getAllGstTransaction()->has('company')->with('memberDetails')->get();
		if ($req['export_outward_report_extension'] == 0) {
			$sno = $_POST['start'];
			$totalResults = count($data);
			$results = $data;

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
			$i = 0;
			foreach ($results as $value) {
				$gstNum = '';
				if (isset($value->customer_gst_no)) {
					$gstNum = $value->customer_gst_no;
				}
				$val['NAME OF RECEIPIENT'] = $value['memberDetails'] ? $value['memberDetails']->first_name : '';
				$val['GSTIN'] = $gstNum;
				$val['State Name'] = $value['memberDetails'] ? ($value['memberDetails']['branch'] ? ($value['memberDetails']['branch']['branchStatesCustom'] ? $value['memberDetails']['branch']['branchStatesCustom']->name : '') : '') : '';
				$val['POS'] = '-';
				$val['INVOICE NUMBER'] = $value->invoice_number;
				$val['INVOICE DATE'] = date('d-m-Y', strtotime($value->created_at));
				$val['INVOICE VALUE'] = $value->total_amount;
				$val['INVOICE HSN/SAC'] = '-';
				$val['INVOICE GOODS/SERVICE DESCRIPTION'] = '-';
				$val['INVOICE TAXABLE VALUE'] = $value->tax_value;
				$val['QUANTITY'] = '-';
				$val['QUANTITY UNIT'] = '-';
				if ($value->gstHeadrate && $value->amount_of_tax_igst > 0) {
					$per = $value->gstHeadrate->gst_percentage;
					$val['IGST RATE'] = $per;
					$val['IGST AMOUNT'] = $value->amount_of_tax_igst;
				} else {
					$per = $value->gstHeadrate->gst_percentage / 2;
					$val['CGST RATE'] = $per;
					$val['CGST AMOUNT'] = $value->amount_of_tax_cgst;
					$val['SGST RATE'] = $per;
					$val['SGST AMOUNT'] = $value->amount_of_tax_sgst;
				}
				$val['CESS RATE'] = '-';
				$val['CESS AMOUNT'] = '-';
				$val['REVERSE CHARGE'] = '-';
				$val['NAME OF ECOMMERCE OPERATOR'] = '-';
				$val['GSTIN OF ECOMMERCE OPERATOR'] = '-';
				$val['SHIPPING EXPORT TYPE'] = '-';
				$val['SHIPPING NO.'] = '-';
				$val['SHIPPING DATE'] = '-';
				$val['SHIPPING PORT CODE'] = '-';
				$val['RECIPIENT CATEGORY'] = '-';
				$val['ITEM TYPE'] = '-';

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

		}
	}

	public function cr_dr_note(Request $req)
	{
		if (check_my_permission(auth()->user()->id, "271") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'GST Setting Report - CR DR Note';
		$data['data'] = $this->repository->getAllGstTransaction()->has('company')->select('id', 'type_id', 'invoice_number', 'created_at', 'total_amount', 'customer_gst_no', 'amount_of_tax_igst', 'amount_of_tax_cgst', 'amount_of_tax_sgst', 'tax_value', 'head_id')->with([
			'memberDetails' => function ($q) {
				$q->select('id', 'branch_id', 'first_name')->with([
					'branch' => function ($q) {
						$q->select('id', 'state_id')->with([
							'branchStatesCustom' => function ($q) {
								$q->select('id', 'name');
							}
						]);
					}
				]);
			},
			'gstHeadrate' => function ($q) {
				$q->select('id', 'head_id', 'gst_percentage');
			}
		])->paginate(20);
		// $data['title'] = "cr_dr_note";
		return view('templates.admin.duties_taxes.gst.report.cr_dr_note', $data);
	}

	public function summary_supply(Request $req)
	{
		if (check_my_permission(auth()->user()->id, "272") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'GST Setting Report - Summary';
		return view('templates.admin.duties_taxes.gst.report.summary_supply', $data);
	}

	public function tds_log_detail(Request $req)
	{
		$data['title'] = "tds_log_detail";
		return view('templates.admin.blank', $data);
	}

	public function payable_list(Request $req)
	{
		if (check_my_permission(auth()->user()->id, "167") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Duties & Taxes | Payable List';
		$data['branch'] = $this->repository->getAllBranch()->where('status', 1)->get()->toArray();
		$data['SamraddhBanks'] = $this->repository->getAllSamraddhBank()->where('status', 1)->pluck('bank_name', 'id');
		$data['head_type'] = $this->repository->getAllAccountHeads()->whereIn('parent_id', [168])->pluck('sub_head', 'head_id');
		$data['SamraddhBankAccounts'] = $this->repository->getAllSamraddhBankAccounts()->where('status', 1)->get(['id', 'bank_id', 'account_no', 'status']);
		$data['SamraddhBankAccount'] = $this->repository->getAllSamraddhBankAccounts()->where('status', 1)->pluck('account_no', 'id');
		return view('templates.admin.duties_taxes.payable_listing.index', $data);
	}
	public function payable_listing(Request $req)
	{
		if ($req->ajax()) {
			$arrFormData = array();
			if (!empty($_POST['searchform'])) {
				foreach ($_POST['searchform'] as $frm_data) {
					$arrFormData[$frm_data['name']] = $frm_data['value'];
				}
			}

			if ($arrFormData['payable_export'] == '1') {
				$data = $this->repository->getAllDutiesTaxesPayable()->has('company')
					->with([
						'bank:id,bank_name',
						'bankAccount:id,account_no',
						'challan:id,file_name',
						'company:id,short_name',
						'accountHead:sub_head,head_id'
					])
					->where('is_deleted', 0);

				if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
					if ($arrFormData['start_date'] != '') {
						$startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
						if ($arrFormData['end_date'] != '') {
							$endDate = date("Y-m-d", strtotime(convertDate($arrFormData['end_date'])));
						} else {
							$endDate = '';
						}
						$data = $data->whereBetween(\DB::raw('DATE(payment_date)'), [$startDate, $endDate]);
					}

					if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
						$company_id = $arrFormData['company_id'];
						if ($company_id > '0') {
							$data = $data->where('company_id', (int) $company_id);
						}
					}
				} else {
					$data = $data->where('id', 0);
				}
				$accountHead = $this->repository->getAllAccountHeads()->where('parent_id', 168)->pluck('sub_head', 'head_id');

				$count = $data->count('id');
				$token = Session::get('_token');
				$export = $data->orderby('id', 'DESC')->get()->toArray();
				Cache::put('duties_taxes_payable_listing_admin_' . $token, $export);
				Cache::put('duties_taxes_payable_listing_count_admin_' . $token, $count);

				$data = $data->offset($_POST['start'])->limit($_POST['length'])->orderby('id', 'DESC')->get();
				$totalCount = $count;
				$sno = $_POST['start'];
				$rowReturn = array();
				$payable_head_type = $this->repository->getAllAccountHeads()->where('parent_id', 168)->pluck('child_head', 'head_id');
				foreach ($data as $row) {
					$selectedKey = '';
					$v = $row->head_id;
					foreach ($payable_head_type as $key => $array) {
						if (in_array($v, $array)) {
							$selectedKey = $key;
							break;
						}
					}
					$urlImage = $row->challan ? ImageUpload::generatePreSignedUrl('duties_taxes-payable/challan/' . ($row->challan->file_name)) : '';
					$viewfile = $row->challan ? '<a href="' . $urlImage . '" title="Vew File" target="_blank" class="">' . ($row->challan->file_name) . '</a>' : 'N/A';
					$sno++;
					$val = [
						'DT_RowIndex' => $sno,
						'payable_head_type' => $selectedKey ? u($accountHead[$selectedKey]) : 'N/A',
						'company' => $row->company ? ($row->company->short_name) : 'N/A',
						'head' => $row->accountHead ? u($row->accountHead->sub_head) : 'N/A',
						'payable_amount' => number_format($row->amount, 2),
						'payament_date' => date('d/m/Y', strtotime(convertdate($row->payment_date))),
						'bank_name' => $row->bank ? u($row->bank->bank_name) : 'N/A',
						'bank_account' => $row->bankAccount ? $row->bankAccount->account_no : 'N/A',
						'late_penalty' => number_format($row->late_penalty, 2),
						'total_paid_amount' => number_format($row->final_amount, 2),
						'transaction_number' => $row->transaction_number,
						'neft_charges' => number_format($row->neft_charge, 2),
						'challan' => $row->challan ? $viewfile : 'N/A',
						'remark' => $row->remark,
					];
					$rowReturn[] = $val;
				}
				$output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
				return json_encode($output);
			} else {
				$output = array("draw" => $_POST['draw'], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
				return json_encode($output);
			}
		}
	}

	public function add_company_settings()
	{
		if (check_my_permission(auth()->user()->id, "273") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Duties & Taxes | Add GST Company Setting';
		$data['states'] = $this->repository->getAllStates()->select('id', 'name', 'gst_code')->get();

		return view('templates.admin.duties_taxes.gst.setting.add_company_setting', $data);
	}

	// post routes functions
	public function save_company_settings(Request $req)
	{
		$rules = [
			'category' => ['required'],
			'company_id' => ['required'],
			'applicable_date' => ['required'],
			'state_id' => ['required'],
			'gst_no' => ['required', 'regex:/^[a-zA-Z0-9]*$/', 'unique:gst_setting,gst_no'],
		];

		$customMessages = [
			'required' => ':Attribute is required.',
			'unique' => ' :Attribute already exists.'
		];
		$this->validate($req, $rules, $customMessages);
		$exists = $this->repository->getAllGstSetting()
			->where('state_id', $req->state_id)
			->where('category', $req->category)
			->whereNull('end_date')
			->whereCompanyId($req->company_id)
			->exists();

		if ($exists) {
			return redirect()->back()->with('alert', 'Gst Setting Already created for selected company and state, please edit it first!');
		}

		DB::beginTransaction();
		try {
			$data = [
				'gst_no' => $req->gst_no,
				'state_id' => $req->state_id,
				'applicable_date' => date('Y-m-d', strtotime(convertDate($req->applicable_date))),
				'end_date' => null,
				'category' => $req->category,
				'company_id' => $req->company_id,
			];
			$gstDate = $this->repository->createGstSetting($data);
			$old = $this->repository->getGstSettingById($gstDate->id)->first()->toArray();
			$u = auth()->user()->role_id == 3 ? 'Branch' : 'Admin';
			$user = auth()->user()->username;
			$currentDateTime = date('d/M/Y H:i:s', strtotime(convertDate($req->created_at)));
			$logData = [
				'type_id' => $gstDate->id,
				'title' => 'created Company Gst Settings',
				'description' => "Gst Settings id - $req->edit_id was created by $user via the $u Panel on $currentDateTime for Gst Settings.",
				'new_value' => json_encode($old),
				'old_value' => json_encode($old),
				'type' => 1,
				'created_by' => auth()->user()->role_id == 3 ? '2' : '1',
				'created_by_id' => auth()->user()->id,
				'created_at' => date('Y-m-d H:i:s', strtotime(convertDate($req->created_at))),
				'updated_at' => date('Y-m-d H:i:s')
			];
			$this->repository->createDutiesTaxesSettingLog($logData);
			DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			return back()->with('alert', $ex->getMessage());
		}
		return redirect()->route('admin.duties_taxes.gst.setting.company_settings')->with('success', 'Gst Setting Generated Successfully');
	}

	public function company_settings_listing(Request $req)
	{
		if ($req->ajax()) {
			$data = $this->repository->getAllGstSetting()->has('company')->with('company:id,name')->orderBy('created_at', 'desc');
			$count = $data->count();
			$data = $data->offset($_POST['start'])->limit($_POST['length'])->get();
			$rowReturn = array();
			$totalCount = $count;
			foreach ($data as $sno => $value) {
				$sno++;
				$val['DT_RowIndex'] = $sno;
				$val['company_name'] = $value->company->name;
				$val['gst_number'] = $value->gst_no;
				$val['state'] = $value->state->name;
				$val['application_date'] = date('d/m/Y', strtotime($value->applicable_date));
				$cateGory = 'N/A';
				if ($value->category == 0) {
					$cateGory = '<span class="badge badge-primary">Main</span>';
				} else {
					$cateGory = '<span class="badge badge-secondary">ISD</span>';
				}
				$val['category'] = $cateGory;
				$btn = '';
				$btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
				$editUrl = route('admin.duties_taxes.gst.setting.edit_company_settings', base64_encode($value->id));
				$logUrl = route('admin.duties_taxes.gst.setting.log_company_settings', [base64_encode($value->id), base64_encode(1)]);
				if (!$value->end_date) {
					$btn .= '<a class="dropdown-item" href="' . $editUrl . '" title="Edit Gst Setting"><i class="fa fa-edit" aria-hidden="true"></i> Edit</a>';
				}
				$btn .= '<a class="dropdown-item" href="' . $logUrl . '" title="View Gst Setting Log"><i class="fa fa-eye" aria-hidden="true"></i> Logs</a>';
				$btn .= '</div></div></div>';
				$val['action'] = $btn;
				$rowReturn[] = $val;
			}
			$output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn, );
			return json_encode($output);
		}
	}

	public function log_company_settings($id, $type)
	{
		$i = base64_decode($id);
		$t = base64_decode($type);

		$log = $this->repository->getDutiesTaxesSettingLogByTypeId($i)->whereType($t);
		$gst = $this->repository->getGstSettingById($i);

		$data['msg_type'] = 'success';
		$data['msg'] = '';

		if (!$gst || !$log->exists() || !$i) {
			$data['msg_type'] = 'error';
			$data['msg'] = 'Record Not Found !';
			return redirect()->back()->with(['alert' => $data['msg']]);
		} elseif (!$gst && $log->exists() && $i) {
			$data['msg_type'] = 'error';
			$data['msg'] = 'No Log Data Found !';
			return redirect()->back()->with(['alert' => $data['msg']]);
		}
		$data['gst'] = $gst ? $gst->get()->toArray() : [];
		$data['log'] = $log->orderBy('created_at', 'DESC')->get();
		$data['all'] = ($i && $i != 0) || $gst ? true : false;
		$type = [
			1 => 'Company GST Setting',
			2 => 'Head GST Setting'
		];
		$t = $type[$data['log'][0]['type']];
		$data['title'] = "Duties & Taxes | $t Log";
		return view('templates.admin.duties_taxes.gst.setting.log_detail', $data);
	}

	public function edit_company_settings($id)
	{
		$i = base64_decode($id);
		$data['record'] = $this->repository->getGstSettingById($i)->first();
		$data['title'] = "Duties & Taxes | Edit GST Company Setting";
		$data['states'] = $this->repository->getAllStates()->get();
		return view('templates.admin.duties_taxes.gst.setting.edit_company_settings', $data);
	}

	public function update_company_settings(GstEditRequest $req)
	{
		$rules = [
			'category' => ['required'],
			'company_id' => ['required'],
			'applicable_date' => ['required'],
			'state_id' => ['required'],
			'gst_no' => ['required', 'regex:/^[a-zA-Z0-9]*$/'],
		];

		$customMessages = [
			'required' => ':Attribute is required.',
		];

		$this->validate($req, $rules, $customMessages);
		// try{
		$exists = $this->repository->getAllGstTransaction()
			->where('gst_setting_id', $req->edit_id)
			->exists();
		$o = $this->repository->getGstSettingById($req->edit_id)->first()->toArray();
		$old = json_encode($o);
		$d = date('Y-m-d', strtotime(convertDate($req->applicable_date)));
		$updateRecord = [
			'gst_no' => $req->gst_no,
			'state_id' => $req->state_id,
			'applicable_date' => $d,
			'end_date' => $req->end_date ? date('Y-m-d', strtotime(convertDate($req->end_date))) : null,
			'category' => $req->category,
			'company_id' => $req->company_id,
		];
		$t = 'success';
		$msg = 'Update Record Successfully!';
		if ($exists && ($o['applicable_date'] != $d || $o['state_id'] != $req->state_id || $o['company_id'] != $req->company_id || $o['category'] != $req->category)) {
			$updateRecord['applicable_date'] = $o['applicable_date'];
			$updateRecord['state_id'] = $o['state_id'];
			$updateRecord['company_id'] = $o['company_id'];
			$updateRecord['category'] = $o['category'];
			$t = 'alert';
			$msg = 'GST transaction is found, you cannot change the company, state, or applicability date on gst settings !';
		}
		$this->repository->getGstSettingById($req->edit_id)->update($updateRecord);
		$new = json_encode($this->repository->getGstSettingById($req->edit_id)->first()->toArray());
		$u = auth()->user()->role_id == 3 ? 'Branch' : 'Admin';
		$user = auth()->user()->username;
		$currentDateTime = date('d/M/Y H:i:s', strtotime(convertDate($req->created_at)));

		$n = [];
		$o = [];
		$nv = json_decode($new);
		$ov = json_decode($old);
		if ($nv) {
			foreach ($nv as $key => $value) {
				if ($value !== $ov->$key) {
					$n[$key] = $value;
				}
			}
		}
		if ($nv) {
			foreach ($nv as $key => $value) {
				if ($value !== $ov->$key) {
					$o[$key] = $ov->$key;
				}
			}
		}
		$logData = [
			'type_id' => $req->edit_id,
			'title' => 'Update Company Gst Settings',
			'description' => "Gst Settings id - $req->edit_id was updated by $user via the $u Panel on $currentDateTime for Gst Settings.",
			'new_value' => json_encode($n),
			'old_value' => json_encode($o),
			'type' => 1,
			'created_by' => auth()->user()->role_id == 3 ? '2' : '1',
			'created_by_id' => auth()->user()->id,
			'created_at' => date('Y-m-d H:i:s', strtotime(convertDate($req->created_at))),
			'updated_at' => date('Y-m-d H:i:s')
		];
		$this->repository->createDutiesTaxesSettingLog($logData);
		// }catch(\Exception $ex){
		// 	dd($ex->getMessage() . ' - ' .$ex->getLine() . ' - ' .$ex->getCode() . ' - ' .$ex->getFile());
		// }		
		return redirect()->route('admin.duties_taxes.gst.setting.company_settings')->with($t, $msg, 'Success');
	}

	public function update_head_settings(Request $req)
	{
		$rules = [
			'id' => ['required']
		];

		$customMessages = [
			'required' => ':Attribute is required.',
		];

		$this->validate($req, $rules, $customMessages);
		$old = json_encode($this->repository->getHeadSettingById($req->id)->first()->toArray());
		$updateData = $this->repository->getHeadSettingById($req->id);
		$updateRecord = [
			'gst_percentage' => $req->gst_percentage,
		];
		$updateData->update($updateRecord);
		$new = json_encode($this->repository->getHeadSettingById($req->id)->first()->toArray());
		$u = auth()->user()->role_id == 3 ? 'Branch' : 'Admin';
		$user = auth()->user()->username;
		$currentDateTime = date('d/M/Y H:i:s', strtotime(convertDate($req->created_at)));
		$n = [];
		$o = [];
		$nv = json_decode($new);
		$ov = json_decode($old);
		if ($nv) {
			foreach ($nv as $key => $value) {
				if ($value !== $ov->$key) {
					$n[$key] = $value;
				}
			}
		}
		if ($nv) {
			foreach ($nv as $key => $value) {
				if ($value !== $ov->$key) {
					$o[$key] = $ov->$key;
				}
			}
		}
		$logData = [
			'type_id' => $req->id,
			'title' => 'update Head Settings',
			'description' => "Head Settings id - $req->id was updated by $user via the $u Panel on $currentDateTime for Head Settings.",
			'new_value' => json_encode($n),
			'old_value' => json_encode($o),
			'type' => 2,
			'created_by' => auth()->user()->role_id == 3 ? '2' : '1',
			'created_by_id' => auth()->user()->id,
			'created_at' => date('Y-m-d H:i:s', strtotime(convertDate($req->created_at))),
			'updated_at' => date('Y-m-d H:i:s')
		];
		$this->repository->createDutiesTaxesSettingLog($logData);

		return redirect()->route('admin.duties_taxes.gst.setting.head_settings')->with('success', 'Update Record Successfully!', 'Success');
	}

	public function save_head_settings(HeadSettingRequest $req)
	{
		DB::beginTransaction();
		try {
			$rules = [
				'head_id' => ['required'],
			];

			$customMessages = [
				'required' => ':Attribute is required.',
			];

			$this->validate($req, $rules, $customMessages);
			$exists = $this->repository->getAllHeadSetting()
				->where('head_id', $req->head_id)
				->whereCompanyId(1)
				->exists();

			if ($exists) {
				return redirect()->back()->with('alert', 'Head Setting Already created for selected company and head, please edit it first!');
			}

			$data = [
				'head_id' => $req->head_id,
				'gst_percentage' => $req->gst_percentage,
			];

			$createData = $this->repository->createHeadSetting($data);
			$old = $this->repository->getHeadSettingById($createData->id)->first()->toArray();

			$u = auth()->user()->role_id == 3 ? 'Branch' : 'Admin';
			$user = auth()->user()->username;
			$currentDateTime = date('d/M/Y H:i:s', strtotime(convertDate($req->created_at)));
			$logData = [
				'type_id' => $createData->id,
				'title' => 'created Head Settings',
				'description' => "Head Settings id - $createData->id was created by $user via the $u Panel on $currentDateTime for Head Settings.",
				'new_value' => json_encode($old),
				'old_value' => json_encode($old),
				'type' => 2,
				'created_by' => auth()->user()->role_id == 3 ? '2' : '1',
				'created_by_id' => auth()->user()->id,
				'created_at' => date('Y-m-d H:i:s', strtotime(convertDate($req->created_at))),
				'updated_at' => date('Y-m-d H:i:s')
			];
			$this->repository->createDutiesTaxesSettingLog($logData);

			DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			return back()->with('alert', $ex->getMessage());
		}
		return redirect()->route('admin.duties_taxes.gst.setting.head_settings_list')->with('success', 'Gst Head  Setting Generated Successfully');
	}

	public function head_settings_list(Request $req)
	{
		if (check_my_permission(auth()->user()->id, "268") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Head Setting List';
		return view('templates.admin.duties_taxes.gst.setting.head_settings_list', $data);
	}

	public function head_settings_listing(Request $req)
	{
		if ($req->ajax()) {
			$data = $this->repository->getAllHeadSetting()->orderBy('created_at', 'desc');
			$count = $data->count();
			$data = $data->offset($_POST['start'])->limit($_POST['length'])->get();
			$rowReturn = array();
			$totalCount = $count;
			foreach ($data as $sno => $value) {
				$sno++;
				$val['DT_RowIndex'] = $sno;
				$val['head_name'] = $value->HeadDetail->sub_head;
				$val['gst_percentage'] = $value->gst_percentage . '%';

				$btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
				$editUrl = route('admin.duties_taxes.gst.setting.edit_head_settings', base64_encode($value->id));
				$logUrl = route('admin.duties_taxes.gst.setting.log_company_settings', [base64_encode($value->id), base64_encode(2)]);
				if (!$value->end_date) {
					$btn .= '<a class="dropdown-item" href="' . $editUrl . '" title="Edit Gst Setting"><i class="fa fa-edit" aria-hidden="true"></i> Edit</a>';
				}
				$btn .= '<a class="dropdown-item" href="' . $logUrl . '" title="View Gst Setting Log"><i class="fa fa-eye" aria-hidden="true"></i> Logs</a>';
				$btn .= '</div></div></div>';
				$val['action'] = $btn;
				$rowReturn[] = $val;
			}
			$output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn, );
			return json_encode($output);
		}
	}

	public function gst_customer_transactions()
	{
		if (check_my_permission(auth()->user()->id, "349") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = "Duties & Taxes | GST Customer Transactions";
		$data['branch'] = $this->repository->getAllBranch()->where('status', 1)->get();
		$data['Heads'] = $this->repository->getAllAccountHeads()->where('parent_id', 298)->pluck('sub_head', 'head_id');
		$data['SamraddhBanks'] = $this->repository->getAllSamraddhBank()->where('status', 1)->get();
		$data['SamraddhBankAccounts'] = $this->repository->getAllSamraddhBankAccounts()->where('status', 1)->where('status', 1)->get();
		$data['view'] = 0;
		return view('templates.admin.duties_taxes.gst.gst_customer_transactions', $data);
	}
	public function gst_customer_transactions_listing(Request $req)
	{
		if ($req->ajax()) {
			$arrFormData = array();
			if (!empty($_POST['searchform'])) {
				foreach ($_POST['searchform'] as $frm_data) {
					$arrFormData[$frm_data['name']] = $frm_data['value'];
				}
			}
			$company_id = $arrFormData['company_id'];
			$data = $this->repository->getAllAllHeadTransaction()->has('company')->whereIn('head_id', [170, 171, 172])
				->select('id', 'created_at', 'head_id', 'member_id', 'branch_id', 'daybook_ref_id', 'amount', 'payment_type', 'company_id')
				->with([
					'member:id,member_id,first_name,last_name',
					'member.memberIdProof:id,first_id_no,second_id_no,member_id,first_id_type_id,second_id_type_id',
					'AccountHeads:id,head_id,sub_head,cr_nature',
					'branch:id,name',
					'company:id,name,short_name'
				])
				->where('payment_type', 'CR')
				->where('is_deleted', 0);
			/******* fillter query start ****/
			if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
				if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
					$id = $arrFormData['branch_id'];
					if ($id != '0') {
						$data = $data->where('branch_id', $id);
					}
				}
				if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
					$company_id = $arrFormData['company_id'];
					if ($company_id != '0') {
						$data = $data->where('company_id', $company_id);
					}
				}
				if ($arrFormData['start_date'] != '') {
					$startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
					if ($arrFormData['end_date'] != '') {
						$endDate = date("Y-m-d", strtotime(convertDate($arrFormData['end_date'])));
					} else {
						$endDate = '';
					}
					$data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
				}
			} else {
				$data = $data->where('id', 0);
			}
			/******* fillter query End ****/
			$count = $data->count('id');
			$totalAmount = 0;
			$totalAmountData = $data->limit($_POST['start'])->orderby('id', 'DESC')->get();
			foreach ($totalAmountData as $item) {
				$totalAmount = ($totalAmount + (float) $item->amount) - (float) getTdsDrAmount($item->daybook_ref_id, $item->head_id);
			}
			$data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
			$totalCount = $count; //AllTransaction::where('payment_type','CR')->count('id');
			$sno = $_POST['start'];
			$rowReturn = array();
			foreach ($data as $row) {
				$sno++;
				$getTdsDrAmount = getTdsDrAmount($row->daybook_ref_id, $row->head_id);
				$totalAmount = ($totalAmount + (float) $row->amount) - (float) $getTdsDrAmount;
				$val = [
					'DT_RowIndex' => $sno,
					'created_date' => date("d/m/Y", strtotime(convertDate($row->created_at))),
					'company' => $row->company ? $row->company->short_name : 'N/A',
					'branch' => $row->branch ? $row->branch->name : 'N/A',
					'head' => $row['AccountHeads'] ? $row['AccountHeads']->sub_head : 'N/A',
					'name' => $row['member'] ? $row['member']->first_name . ' ' . $row['member']->last_name ?? '' : 'N/A',
					'customer_id' => $row['member'] ? $row['member']->member_id : 'N/A',
					'dr_entry' => number_format($getTdsDrAmount, 2),
					'cr_entry' => number_format($row->amount, 2),
					'balance' => number_format($totalAmount, 2),
				];
				$rowReturn[] = $val;
			}
			$output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
			return json_encode($output);
		}
	}
	//tds Setting
	public function tds_settings()
	{
		if (check_my_permission(auth()->user()->id, "351") != "1") {
			return redirect()
				->route('admin.dashboard');
		}
		$data['title'] = "Duties & Taxes | TDS Setting ";
		return view('templates.admin.duties_taxes.tds.settings.tds_settings', $data);
	}

	public function add_tds_settings()
	{
		if (check_my_permission(auth()->user()->id, "216") != "1") {
			return redirect()
				->route('admin.dashboard');
		}
		$data['title'] = "Duties & Taxes | Create TDS Setting ";
		return view('templates.admin.duties_taxes.tds.settings.create_tds_settings', $data);
	}

	public function tds_customer_transactions(Request $req)
	{
		if (check_my_permission(auth()->user()->id, "350") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Duties & Taxes | Tds Customer Transactions';
		$data['branch'] = $this->repository->getAllBranch()->select('id', 'name')->where('status', 1)->get();
		$data['tdsHeads'] = $this->repository->getAllAccountHeads()->whereIn('parent_id', [22, 322, 330])->pluck('sub_head', 'head_id');
		$data['view'] = 0;
		return view('templates.admin.duties_taxes.tds.tds_customer_transactions', $data);
	}

	public function transfer()
	{
		if (check_my_permission(auth()->user()->id, "166") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Duties & Taxes | Transfer';
		$data['branch'] = $this->repository->getAllBranch()->where('status', 1)->get();
		$data['SamraddhBankAccounts'] = $this->repository->getAllSamraddhBankAccounts()
			->has('getCompanyDetail')
			->where('status', 1)
			->get(['id', 'bank_id', 'account_no', 'status']);
		$data['view'] = 0;
		$data['head_type'] = $this->repository->getAllAccountHeads()->whereIn('parent_id', [168])->pluck('sub_head', 'head_id');
		return view('templates.admin.duties_taxes.transfer.index', $data);
	}

	public function transferReq(Request $req)
	{
		$rules = [
			'payable_start_date' => 'required',
			'payable_end_date' => 'required',
			'payable_head_id' => 'required',
			'payable_tds_amount' => 'required',
			// Add 'gt:0' to check if the amount is greater than zero
			'company_id' => 'required',
		];
		$customMessages = [
			'required' => 'The :attribute field is required.',
			'numeric' => 'The :attribute field must be a number.',
			'gt' => 'The :attribute field must be greater than zero.',
		];

		$transaction_type = 9;
		$cashInHand = 29;
		$payment_mode = 3;
		$created_by = Auth::user()->role_id === 3 ? '2' : '1';
		$companyId = $req->company_id;
		$this->validate($req, $rules, $customMessages);
		$created_by_id = Auth::user()->id;
		$heads = $this->repository->getAllAccountHeads()->pluck('sub_head', 'head_id');
		$transferType = in_array($req->head_type, [22, 322, 330]) ? 92 : 94;
		$transferSubType = in_array($req->head_type, [22, 322, 330]) ? 93 : 95;
		DB::beginTransaction();
		try {
			$t = date("H:i:s");
			$tDate = date("Y-m-d", strtotime(convertdate($req->payable_end_date)));
			$fromDate = date("Y-m-d", strtotime(convertdate($req->payable_start_date)));
			$toDate = date("Y-m-d " . $t . "", strtotime(convertdate($req->payable_end_date)));
			$payable_start_date = date("Y-m-d " . $t . "", strtotime(convertdate($req->payable_start_date)));
			$payable_end_date = date("Y-m-d " . $t . "", strtotime(convertdate($req->payable_end_date)));
			Session::put('created_at', $toDate);
			Session::put('created_atUpdate', $toDate);
			$referenceId = CommanController::createBranchDayBookReference($req->payable_tds_amount);
			$data = $this->repository->getAllAllHeadTransaction()
				->when(in_array($req->head_type, [22, 322, 330]), function ($q) use ($req) {
					$q->whereHeadId((int) $req->payable_head_id);
				})
				->when($req->head_type == 169, function ($q) {
					$q->whereIn('head_id', [169, 170, 171, 172]);
				})
				->whereBetween(\DB::raw('DATE(created_at)'), [$fromDate, $tDate])
				->where('is_deleted', 0)
				->when($req->payable_head_id != 63, function ($q) {
					$q->whereNotIn('type', [9]);
				})
				->where('company_id', $companyId)
				->whereNotIn('sub_type', [$transferType, $transferSubType]);
			$transacion = clone $data;
			$transacions = $transacion->where('payment_type', 'CR')->get();
			$sumAmountBranch = $data->where('payment_type', 'CR')->get()->groupBy('branch_id')->map(function ($g) {
				return $g->sum('amount');
			})->toArray();

			$old_daybook_id = [];
			$sumAmount = 0;
			$insert = [];
			$insert2 = [];
			foreach ($transacions as $val) {
				if ($val->amount > 0) {
					$insert[] = [
						'daybook_ref_id' => $referenceId,
						'branch_id' => $val->branch_id,
						'bank_id' => NULL,
						'bank_ac_id' => NULL,
						'head_id' => $val->head_id,
						'type' => $transaction_type, // $val->type,
						'sub_type' => $transferType, // $val->sub_type,
						'type_id' => $val->type_id,
						'type_transaction_id' => $val->type_transaction_id,
						'associate_id' => $val->associate_id,
						'member_id' => $val->member_id,
						'branch_id_to' => NULL,
						'branch_id_from' => NULL,
						'amount' => $val->amount,
						'description' => ucfirst($heads[$req->head_type]) . ' Transfer ' . ucfirst($heads[$val->head_id]) . ' Amount ' . $val->amount . '',
						'payment_type' => 'DR',
						'payment_mode' => $payment_mode,
						'currency_code' => 'INR',
						'jv_unique_id' => NULL,
						'v_no' => NULL,
						'ssb_account_id_from' => NULL,
						'ssb_account_id_to' => NULL,
						'ssb_account_tran_id_to' => NULL,
						'ssb_account_tran_id_from' => NULL,
						'cheque_type' => NULL,
						'cheque_id' => NULL,
						'cheque_no' => NULL,
						'transction_no' => NULL,
						'entry_date' => date("Y-m-d", strtotime(convertDate($toDate))),
						'entry_time' => $t,
						'created_by' => $created_by,
						'created_by_id' => $created_by_id,
						'company_id' => $companyId,
						'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($toDate))),
						'is_app' => 0,
						'is_query' => 0,
						'is_cron' => 0,
					];
				}
				$sumAmount += $val->amount;
				array_push($old_daybook_id, $val->daybook_ref_id);
			}
			$allHeadTransaction_Branch = $this->repository->insertAllHeadTransaction($insert);
			$tdsTransferData = [
				'daybook_ref_id' => $referenceId,
				'head_id' => $req->payable_head_id,
				'transfer_date' => $toDate,
				'is_paid' => 1,
				'start_date' => $payable_start_date,
				'end_date' => $payable_end_date,
				'tds_amt' => number_format((float) $sumAmount, 2, '.', ''),
				'deleted_at' => NULL,
				'old_daybook_id' => json_encode($old_daybook_id),
				'transfer_daybook_ref_id' => $referenceId,
				'payment_ref_id' => NULL,
				'company_id' => $companyId,
			];
			// dd($tdsTransferData, $req->all());
			$TransferId = $this->repository->createTdsTransfer($tdsTransferData);
			$lth_headId = 408; // head_id for LIABILITY TRANSFER
			foreach ($sumAmountBranch as $key => $value) {
				// LTH CR In loop  as per branch total amount
				if ($value > 0) {
					$insert2[] = [
						'daybook_ref_id' => $referenceId,
						'branch_id' => $key, // branch ID
						'bank_id' => NULL,
						'head_id' => $lth_headId,
						'type' => $transaction_type, // TDS Type
						'sub_type' => $transferType,  // tds transafer head id
						'type_id' => $TransferId, // tds transafer table auto ID
						'type_transaction_id' => $TransferId,  // tds transafer table auto ID
						'associate_id' => NULL,
						'member_id' => $transferType,
						'branch_id_to' => NULL,
						'branch_id_from' => NULL,
						'amount' => $value,
						'description' => ucfirst($heads[$req->head_type]) . ' Transfer ' . ucfirst($heads[$lth_headId]) . ' Amount ' . $value . '',
						'payment_type' => 'CR',
						'payment_mode' => $payment_mode,
						'currency_code' => 'INR',
						'jv_unique_id' => NULL,
						'v_no' => NULL,
						'ssb_account_id_from' => NULL,
						'ssb_account_id_to' => NULL,
						'ssb_account_tran_id_to' => NULL,
						'ssb_account_tran_id_from' => NULL,
						'cheque_type' => NULL,
						'cheque_id' => NULL,
						'cheque_no' => NULL,
						'transction_no' => NULL,
						'entry_date' => date("Y-m-d", strtotime(convertDate($toDate))),
						'entry_time' => $t,
						'created_by' => $created_by,
						'created_by_id' => $created_by_id,
						'company_id' => $companyId,
						'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($toDate))),
						'is_app' => 0,
						'is_query' => 0,
						'is_cron' => 0,
					];
				}
			}
			$allHeadTransaction_LTH = $this->repository->insertAllHeadTransaction($insert2);

			//LTH CR
			$allHeadTransactionLTH_DR = CommanController::createAllHeadTransaction($referenceId, $cashInHand, NULL, NULL, $req->payable_head_id, $transaction_type, $transferType, $TransferId, $TransferId, NULL, NULL, NULL, NULL, $sumAmount, ucfirst($heads[$req->payable_head_id]) . ' Transfer amount ' . $sumAmount . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $created_by, $created_by_id, $companyId);

			//TDS DR
			$allHeadTransaction_LTH_CR = CommanController::createAllHeadTransaction($referenceId, $cashInHand, NULL, NULL, $lth_headId, $transaction_type, $transferType, $TransferId, $TransferId, NULL, NULL, NULL, NULL, $sumAmount, ucfirst($heads[$lth_headId]) . ' Transfer amount ' . $sumAmount . '', 'DR', 3, 'INR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $created_by, $created_by_id, $companyId);

			DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			return back()->with('alert', $ex->getMessage() . ' - ' . $ex->getLine());
		}
		// return redirect()->route('admin.duties_taxes.transfer_list')->with('success', u($heads[$req->payable_head_id]) . ' Transfer Request Created Successfully!');
		return back()->with('success', u($heads[$req->payable_head_id]) . ' Transfer Request Created Successfully!');
	}

	public function payable()
	{
		if (check_my_permission(auth()->user()->id, "214") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Duties & Taxes | TDS Payable';
		$data['branch'] = $this->repository->getAllBranch()->where('status', 1)->get()->toArray();
		$data['SamraddhBanks'] = $this->repository->getAllSamraddhBank()->where('status', 1)->pluck('bank_name', 'id');
		$data['head_type'] = $this->repository->getAllAccountHeads()->whereIn('parent_id', [168])->pluck('sub_head', 'head_id');
		$data['SamraddhBankAccounts'] = $this->repository->getAllSamraddhBankAccounts()->where('status', 1)->get(['id', 'bank_id', 'account_no', 'status']);
		$data['view'] = 0;
		$data['SamraddhBankAccount'] = $this->repository->getAllSamraddhBankAccounts()->where('status', 1)->pluck('account_no', 'id');
		return view('templates.admin.duties_taxes.payable.index', $data);
	}

	public function payableHeadAmount(Request $req)
	{
		$startDate = date("Y-m-d", strtotime(convertDate($req->startDate)));
		$start = date("Y-m-d H:i:s", strtotime(convertDate($req->startDate)));
		$endDate = date("Y-m-d", strtotime(convertDate($req->endDate)));
		$end = date("Y-m-d H:i:s", strtotime(convertDate($req->endDate)));
		$headId = $req->headId;
		$companyId = $req->companyId;
		$type = $req->type;
		$transferType = in_array($type, [22, 322, 330]) ? 92 : 94;
		$transferSubType = in_array($type, [22, 322, 330]) ? 93 : 95;

		$checkStartDate = $this->repository->getAllDutiesTaxesPayable()/*->where('from_date', '<=', $startDate)->where('to_date', '>=', $startDate)*/ ->where('company_id', $companyId)->where('head_id', $headId)->where('is_deleted', 0)->count();

		$checkEndDate = $this->repository->getAllDutiesTaxesPayable()/*->where('from_date', '<=', $endDate)->where('to_date', '>=', $endDate)*/ ->where('company_id', $companyId)->where('head_id', $headId)->where('is_deleted', 0)->count();

		$checkTDSStartDate = $this->repository->getAllTdsTransfer()->where('deleted_at', NULL)->where(function ($query) use ($start, $headId, $companyId) {
			$query->whereDate('start_date', '<=', $start)->whereDate('end_date', '>=', $start)->where('head_id', $headId)->where('company_id', $companyId)->where('deleted_at', NULL);
		})->orWhereBetween(\DB::raw('DATE(start_date)'), [$start, $end])->where('head_id', $headId)->where('company_id', $companyId)->where('deleted_at', NULL)->count();

		$checkTDSEndDate = $this->repository->getAllTdsTransfer()->where('deleted_at', NULL)->where(function ($query) use ($end, $headId, $companyId) {
			$query->whereDate('start_date', '>=', $end)->whereDate('end_date', '<=', $end)->where('head_id', $headId)->where('company_id', $companyId)->where('deleted_at', NULL);
		})->orWhereBetween(\DB::raw('DATE(end_date)'), [$start, $end])->where('head_id', $headId)->where('company_id', $companyId)->where('deleted_at', NULL)->count();

		// $childHeads = $this->repository->getAllAccountHeads()->whereHeadId($type)->value('child_head');

		$sumAmount = $this->repository->getAllAllHeadTransaction()->when(in_array($type, [22, 322, 330]), function ($q) use ($headId) {
			$q->where('head_id', $headId);
		})->when($type == 169, function ($q) {
			$q->whereIn('head_id', [170, 171, 172]);
		})->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->whereNotIn('sub_type', [$transferType, $transferSubType])->where('payment_type', 'CR')->where('company_id', $companyId)->where('is_deleted', 0)->sum('amount');

		$data = number_format((float) $sumAmount, 2, '.', '');

		if ($checkTDSStartDate > 0) {
			$transfer = $this->repository->getAllTdsTransfer()->where('deleted_at', NULL)->where('head_id', $headId)->where('company_id', $companyId)->orWhereBetween(\DB::raw('DATE(start_date)'), [$start, $end]);
			$id = $transfer->value('id');
		} else {
			$id = '';
		}
		$return_array = compact('data', 'checkStartDate', 'checkEndDate', 'checkTDSEndDate', 'checkTDSStartDate', 'id');
		return json_encode($return_array);
	}

	public function gettdspayabledetails(Request $req)
	{
		$head_type = $req->head_type ?? 0;

		$data['tdsHeads'] = $this->repository->getAllAccountHeads()
			->where('status', '!=', '9')
			->when($head_type > 0, function ($q) use ($head_type) {
				return $q->when(in_array($head_type, [22, 169, 330, 322]), function ($query) use ($head_type) {
					switch ($head_type) {
						case 22:
							$headIds = [62, 63, 175, 176, 177, 264, 265, 318, 327];
							break;
						case 169:
							$headIds = [169];
							break;
						case 330:
							$headIds = [331, 332, 333, 334, 335];
							break;
						case 322:
							$headIds = [324, 325];
							break;
						default:
							$headIds = [];
					}
					return $query->whereIn('head_id', $headIds);
				});
			})
			->pluck('sub_head', 'head_id');
		$c = $this->repository->getAllGstSetting()->whereNull('deleted_at')->pluck('company_id', 'id')->toArray();
		$data['company'] = $this->repository->getAllCompanies()
			->when($head_type > 0 && $head_type == 169, function ($q) use ($c) {
				$q->whereIn('id', $c);
			})->pluck('name', 'id');
		return json_encode($data);
	}
	public function pay(Request $req)
	{
		$rules = [
			// 'payable_start_date' => 'required',
			// 'payable_end_date' => 'required',
			'payable_head_id' => 'required',
			'payable_amount' => 'required',
			'payable_payment_date' => 'required',
			'final_payable_amount' => 'required',
			'bank_id' => 'required',
			'account_id' => 'required',
			'upload_challan' => 'required',
			'remark' => 'required',
		];
		$messages = [
			'required' => 'The :attribute field is required.'
		];
		$this->validate($req, $rules, $messages);

		$heads = $this->repository->getAllAccountHeads()->pluck('sub_head', 'head_id');
		$type = str_replace(' ', "-", $heads[$req->head_type]);
		$companyId = $req->company_id;
		$t = date("H:i:s");
		$created_by = Auth::user()->role_id === 3 ? '2' : '1';
		$transaction_type = 9;
		$cashInHand = 29;
		$transaction_sub_type_id = in_array($req->head_type, [22, 322, 330]) ? 93 : 95;
		$created_by_id = Auth::user()->id;
		$late_penalty = $req->payable_late_penalty;
		$nft = $req->neft_charge ?? 0;
		$paymentDate = date("Y-m-d " . $t . "", strtotime(convertdate($req->payable_payment_date)));
		DB::beginTransaction();
		try {
			$referenceId = CommanController::createBranchDayBookReference($req->total_paid_amount);
			Session::put('created_at', $paymentDate);
			Session::put('created_atUpdate', $paymentDate);
			if ($req->has('upload_challan')) {
				try {
					$mainFolder = 'duties_taxes-payable/challan';
					$file = $req->file('upload_challan');
					$fname = time() . '.' . $file->getClientOriginalExtension();
					ImageUpload::upload($file, $mainFolder, $fname);
					$fData = [
						'file_name' => $fname,
						'file_path' => $mainFolder,
						'file_extension' => $file->getClientOriginalExtension(),
					];
					$file_id = $this->repository->createFiles($fData);
				} catch (\Exception $ex) {
					$file_id = $ex->getMessage();
				}
			} else {
				$file_id = NULL;
			}
			// $tdsTrasfer = $req->id;

			$payableData = [
				'head_id' => $req->payable_head_id,
				'amount' => $req->payable_amount,
				'final_amount' => $req->final_payable_amount,
				'late_penalty' => $late_penalty,
				'neft_charge' => $req->neft_charge,
				'paid_amount' => $req->total_paid_amount,
				'daybook_ref_id' => $referenceId,
				'payment_date' => $paymentDate,
				'bank_id' => $req->bank_id,
				'account_id' => $req->account_id,
				'transaction_number' => $req->transaction_number,
				'challan_id' => $file_id,
				'remark' => $req->remark,
				'created_at' => $req->created_at,
				'company_id' => $companyId,
				'is_deleted' => 0,
			];
			$payableId = $this->repository->createDutiesTaxesPayable($payableData);
			// bank payable entry
			if ($req->total_paid_amount > 0) {
				$insertpayable[] = [
					'daybook_ref_id' => $referenceId,
					'branch_id' => $cashInHand,
					'bank_id' => $req->bank_id,
					'bank_ac_id' => $req->account_id,
					'head_id' => $req->payable_head_id,
					'type' => $transaction_type,
					'sub_type' => $transaction_sub_type_id,
					'type_id' => $payableId,
					'type_transaction_id' => $payableId,
					'associate_id' => NULL,
					'member_id' => NULL,
					'branch_id_to' => NULL,
					'branch_id_from' => NULL,
					'amount' => ($req->total_paid_amount - $late_penalty),
					'description' => ucfirst($heads[$req->head_type]) . ' Payable ' . ucfirst($heads[$req->payable_head_id]) . ' Bank Payable A/C ' . ($req->total_paid_amount - $late_penalty) . '',
					'payment_type' => 'DR',
					'payment_mode' => 2,
					'currency_code' => 'INR',
					'jv_unique_id' => NULL,
					'v_no' => NULL,
					'ssb_account_id_from' => NULL,
					'ssb_account_id_to' => NULL,
					'ssb_account_tran_id_to' => NULL,
					'ssb_account_tran_id_from' => NULL,
					'cheque_type' => NULL,
					'cheque_id' => NULL,
					'cheque_no' => NULL,
					'transction_no' => $req->transaction_number,
					'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
					'entry_time' => $t,
					'created_by' => $created_by,
					'created_by_id' => $created_by_id,
					'company_id' => $companyId,
					'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
					'is_app' => 0
				];
			}
			// LATE PENALTY entry
			if ($late_penalty > 0) {
				$insertlate = [
					'daybook_ref_id' => $referenceId,
					'branch_id' => $cashInHand,
					'bank_id' => $req->bank_id,
					'bank_ac_id' => $req->account_id,
					'head_id' => 33,  // LATE PENALTY head 
					'type' => $transaction_type,
					'sub_type' => $transaction_sub_type_id, //  bank payable
					'type_id' => $payableId,
					'type_transaction_id' => $payableId,
					'associate_id' => NULL,
					'member_id' => NULL,
					'branch_id_to' => NULL,
					'branch_id_from' => NULL,
					'amount' => $late_penalty,
					'description' => ucfirst($heads[$req->head_type]) . ' Payable ' . $heads[$req->payable_head_id] . ' Late Panalty A/c Dr ' . $late_penalty . '',
					'payment_type' => 'DR',
					'payment_mode' => 2,
					'currency_code' => 'INR',
					'jv_unique_id' => NULL,
					'v_no' => NULL,
					'ssb_account_id_from' => NULL,
					'ssb_account_id_to' => NULL,
					'ssb_account_tran_id_to' => NULL,
					'ssb_account_tran_id_from' => NULL,
					'cheque_type' => NULL,
					'cheque_id' => NULL,
					'cheque_no' => NULL,
					'transction_no' => $req->transaction_number,
					'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
					'entry_time' => $t,
					'created_by' => $created_by,
					'created_by_id' => $created_by_id,
					'company_id' => $companyId,
					'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
					'is_app' => 0,
					'is_query' => 0,
					'is_cron' => 0,
				];
			}
			// total amount late penalty + tds_amount + nft if any
			$totalamount = $req->final_payable_amount;
			//credit ampount on bank crarges entry 
			if ($totalamount > 0) {
				$insertbank = [
					'daybook_ref_id' => $referenceId,
					'branch_id' => $cashInHand,
					'bank_id' => $req->bank_id,
					'bank_ac_id' => $req->account_id,
					'head_id' => getSamraddhBank($req->bank_id)->account_head_id, // Bnak head id,
					'type' => $transaction_type,
					'sub_type' => $transaction_sub_type_id, //  bank payable
					'type_id' => $payableId,
					'type_transaction_id' => $payableId,
					'associate_id' => NULL,
					'member_id' => NULL,
					'branch_id_to' => NULL,
					'branch_id_from' => NULL,
					'amount' => $totalamount,
					'description' => ucfirst($heads[$req->head_type]) . ' Payable ' . ucfirst($heads[$req->payable_head_id]) . ' Bank Transaction A/c Cr ' . $totalamount . '',
					'payment_type' => 'CR',
					'payment_mode' => 2,
					'currency_code' => 'INR',
					'jv_unique_id' => NULL,
					'v_no' => NULL,
					'ssb_account_id_from' => NULL,
					'ssb_account_id_to' => NULL,
					'ssb_account_tran_id_to' => NULL,
					'ssb_account_tran_id_from' => NULL,
					'cheque_type' => NULL,
					'cheque_id' => NULL,
					'cheque_no' => NULL,
					'transction_no' => $req->transaction_number,
					'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
					'entry_time' => $t,
					'created_by' => $created_by,
					'created_by_id' => $created_by_id,
					'company_id' => $companyId,
					'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
					'is_app' => 0,
					'is_query' => 0,
					'is_cron' => 0,
				];
			}
			//nft charges if any
			if ($req->neft_charge > 0) {
				$insertnft = [
					'daybook_ref_id' => $referenceId,
					'branch_id' => $cashInHand,
					'bank_id' => $req->bank_id,
					'bank_ac_id' => $req->account_id,
					'head_id' => 92, // Bank Charge head // Bnak head id,
					'type' => $transaction_type,
					'sub_type' => $transaction_sub_type_id, //  bank payable
					'type_id' => $payableId,
					'type_transaction_id' => $payableId,
					'associate_id' => NULL,
					'member_id' => NULL,
					'branch_id_to' => NULL,
					'branch_id_from' => NULL,
					'amount' => $nft,
					'description' => ucfirst($heads[$req->head_type]) . ' Payable ' . 'NEFT Charge on ' . ucfirst($heads[$req->payable_head_id]) . ' A/c Dr ' . $nft,
					'payment_type' => 'DR',
					'payment_mode' => 2,
					'currency_code' => 'INR',
					'jv_unique_id' => NULL,
					'v_no' => NULL,
					'ssb_account_id_from' => NULL,
					'ssb_account_id_to' => NULL,
					'ssb_account_tran_id_to' => NULL,
					'ssb_account_tran_id_from' => NULL,
					'cheque_type' => NULL,
					'cheque_id' => NULL,
					'cheque_no' => NULL,
					'transction_no' => $req->transaction_number,
					'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
					'entry_time' => $t,
					'created_by' => $created_by,
					'created_by_id' => $created_by_id,
					'company_id' => $companyId,
					'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
					'is_app' => 0,
					'is_query' => 0,
					'is_cron' => 0,
				];
			}
			// one entry for all head transaction table
			if (!empty($insertpayable)) {
				$allHeadTransaction_DR_insertpayable = $this->repository->insertAllHeadTransaction($insertpayable);
			}
			if (!empty($insertbank)) {
				$allHeadTransaction_DR_insertbank = $this->repository->insertAllHeadTransaction($insertbank);
			}
			if (!empty($insertlate)) {
				$allHeadTransaction_DR_insertlate = $this->repository->insertAllHeadTransaction($insertlate);
			}
			if (!empty($insertnft)) {
				$allHeadTransaction_DR_insertnft = $this->repository->insertAllHeadTransaction($insertnft);
			}

			// one entry for SamraddhBankDaybook table
			$samraddhBankDaybook = CommanController::samraddhBankDaybookNew($referenceId, $req->bank_id, $req->account_id, $transaction_type, $transaction_sub_type_id, $payableId, $payableId, NULL, NULL, $cashInHand, $totalamount, $totalamount, $totalamount, ucfirst($heads[$req->head_type]) . ' Payable Amount ' . $totalamount . '', ucfirst($heads[$req->head_type]) . ' Payable Dr ' . $totalamount . '', ucfirst($heads[$req->head_type]) . ' Payable Cr ' . $totalamount . '', 'DR', 2, 'INR', NULL, NULL, $req->bank_id, getSamraddhBank($req->bank_id)->bank_name, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $req->transaction_number, $req->bank_id, $req->account_id, getSamraddhBankAccountId($req->account_id)->ifsc_code, NULL, $req->bank_id, $req->account_id, NULL, NULL, NULL, NULL, $paymentDate, $paymentDate, $t, $created_by, $created_by_id, $req->created_at, NULL, NULL, NULL, NULL, NULL, $companyId);

			// one entry for branch_dayBook table
			$createBranchDayBookModify = CommanController::createBranchDayBookModify($referenceId, $cashInHand, $transaction_type, $transaction_sub_type_id, $payableId, $payableId, NULL, NULL, null, null, $totalamount, ucfirst($heads[$req->head_type]) . ' Payable Amount ' . $totalamount . '', ucfirst($heads[$req->head_type]) . ' Payable Dr ' . $totalamount . '', ucfirst($heads[$req->head_type]) . ' Payable Cr ' . $totalamount . '', 'DR', 2, 'INR', NULL, NULL, NULL, NULL, $req->created_at, $t, $created_by, $created_by_id, $paymentDate, $companyId);

			// tds transfer update
			// $this->repository->getAllTdsTransfer()->where('id', $req->id)
			// 	->update([
			// 		'is_paid' => 1,
			// 		'payment_ref_id' => $referenceId
			// 	]);

			// dd('stop',$insertpayable,$insertbank,$insertlate,$insertnft);
			DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			return back()->with('alert', $ex->getMessage() . ' - ' . $ex->getLine() . ' - ' . $ex->getFile() . ' - ' . $ex->getCode());
		}
		// return redirect()->route('admin.duties_taxes.payable_list')->with('success', 'Payment Completed Successfully!');
		return back()->with('success', 'Payment Completed Successfully!');
	}
	public function payable_listing_export(Request $req)
	{
		$token = Session::get('_token');
		$data = Cache::get('duties_taxes_payable_listing_admin_' . $token);
		$count = Cache::get('duties_taxes_payable_listing_count_admin_' . $token);

		$input = $req->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/report/duties_taxes_payable_listing.csv";
		$fileName = env('APP_EXPORTURL') . "report/duties_taxes_payable_listing.csv";

		// header("Content-type: text/csv");
		$totalResults = $count;
		$result = 'next';
		if (($start + $limit) >= $totalResults) {
			$result = 'finished';
		}
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
		$payable_head_type = $this->repository->getAllAccountHeads()->where('parent_id', 168)->pluck('child_head', 'head_id');
		$accountHead = $this->repository->getAllAccountHeads()->where('parent_id', 168)->pluck('sub_head', 'head_id');
		$records = array_slice($data, $start, $limit);
		foreach ($records as $row) {
			$selectedKey = '';
			$v = $row['head_id'];
			foreach ($payable_head_type as $key => $array) {
				if (in_array($v, $array)) {
					$selectedKey = $key;
					break;
				}
			}

			$sno++;
			$val = [
				'S/No' => $sno,
				'PAYABLE HEAD TYPE' => $selectedKey ? u($accountHead[$selectedKey]) : 'N/A',
				'COMPANY' => $row['company'] ? ($row['company']['short_name']) : 'N/A',
				'HEAD' => $row['account_head'] ? u($row['account_head']['sub_head']) : 'N/A',
				'PAYABLE AMOUNT' => number_format($row['amount'], 2),
				'PAYAMENT DATE' => date('d/m/Y', strtotime(convertdate($row['payment_date']))),
				'BANK NAME' => $row['bank'] ? u($row['bank']['bank_name']) : 'N/A',
				'BANK ACCOUNT' => $row['bank_account'] ? $row['bank_account']['account_no'] : 'N/A',
				'LATE PENALTY' => number_format($row['late_penalty'], 2),
				'TOTAL PAID AMOUNT' => number_format($row['final_amount'], 2),
				'TRANSACTION NUMNER' => $row['transaction_number'],
				'NEFT CHANRGES' => number_format($row['neft_charge'], 2),
				'CHALLAN' => $row['challan'] ? $row['challan']['file_name'] : 'N/A',
				'REMARK' => $row['remark'],
			];

			if (!$headerDisplayed) {
				fputcsv($handle, array_keys($val));
				$headerDisplayed = true;
			}
			fputcsv($handle, $val);
		}
		fclose($handle);
		if ($totalResults == 0) {
			$percentage = 100;
		} else {
			$percentage = ($start + $limit) * 100 / $totalResults;
			$percentage = number_format((float) $percentage, 1, '.', '');
		}
		$response = array(
			'result' => $result,
			'start' => $start,
			'limit' => $limit,
			'totalResults' => $totalResults,
			'fileName' => $returnURL,
			'percentage' => $percentage
		);
		echo json_encode($response);
	}

	public function transferlist()
	{
		if (check_my_permission(auth()->user()->id, "351") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['tdsHeads'] = $this->repository->getAllAccountHeads()->whereIn('parent_id', [22, 322, 330])->pluck('sub_head', 'head_id');
		$data['SamraddhBanks'] = $this->repository->getAllSamraddhBank()->has('company')->where('status', 1)->get();
		$data['SamraddhBankAccounts'] = $this->repository->getAllSamraddhBankAccounts()->has('getCompanyDetail')->where('status', 1)->get(['id', 'bank_id', 'account_no', 'status']);
		$data['title'] = 'Duties & Taxes | Transfer List';
		$data['SamraddhBankAccounts'] = $this->repository->getAllSamraddhBankAccounts()
			->has('getCompanyDetail')
			->where('status', 1)
			->get(['id', 'bank_id', 'account_no', 'status']);
		$data['view'] = 0;
		$data['head_type'] = $this->repository->getAllAccountHeads()->whereIn('parent_id', [168])->pluck('sub_head', 'head_id');
		return view('templates.admin.duties_taxes.transfer.list', $data);
	}

	public function transferlisting(Request $req)
	{
		if ($req->ajax()) {
			$arrFormData = array();
			if (!empty($_POST['searchform'])) {
				foreach ($_POST['searchform'] as $frm_data) {
					$arrFormData[$frm_data['name']] = $frm_data['value'];
				}
			}
			$data = $this->repository->getAllTdsTransfer()->has('company')->whereNull('deleted_at')
				->with([
					'company:id,name,short_name',
					'AllHeadTransaction:id,head_id,daybook_ref_id,branch_id,amount,description,payment_type,payment_mode,entry_date,entry_time,is_deleted,company_id,created_by_id',
					'AllHeadTransaction.AccountHeads:id:head_id,sub_head',
				]);
			$headName = $this->repository->getAllAccountHeads()->pluck('sub_head', 'head_id');
			$totalCount = $data->count('id');
			if (!empty($arrFormData)) {
				$data->where(
					function ($query) use ($arrFormData) {
						if ($arrFormData['is_search'] == 'no') {
							$query->whereId(0);
						}
						if ($arrFormData['transfer_date'] != NULL) {
							$transfer_date = date('Y-m-d', strtotime(convertDate($arrFormData['transfer_date'])));
							$query->whereDate('transfer_date', $transfer_date);
						}
						if ($arrFormData['payable_head_id'] != NULL) {
							$query->whereHas('AllHeadTransaction.AccountHeads', function ($q) use ($arrFormData) {
								$q->where('head_id', 'LIKE', '%' . $arrFormData['payable_head_id'] . '%');
							});
						} elseif ($arrFormData['head_type'] != NULL) {
							if ($arrFormData['head_type'] == 22) {
								$headId = [22, 62, 63, 175, 176, 177, 264, 265, 318, 327];
							} elseif ($arrFormData['head_type'] == 169) {
								$headId = [];
							} elseif ($arrFormData['head_type'] == 322) {
								$headId = [322, 324, 325];
							} elseif ($arrFormData['head_type'] == 330) {
								$headId = [330, 331, 332, 333, 334, 335];
							}
							$query->whereHas('AllHeadTransaction.AccountHeads', function ($q) use ($headId) {
								$q->whereIn('head_id', $headId);
							});
						}
					}
				);
			}
			$count = $data->count('id');
			$data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
			$sno = $_POST['start'];
			$rowReturn = array();
			foreach ($data as $row) {
				$sno++;
				// pd($row->toArray());
				$val = [
					'DT_RowIndex' => $sno,
					'transfer_date' => date('d/m/Y', strtotime($row->transfer_date)),
					'date_range' => date("d/m/Y", strtotime(convertDate($row->start_date))) . ' - ' . date("d/m/Y", strtotime(convertDate($row->end_date))),
					'head_name' => $headName[$row->head_id],
					'head_amount' => '&#8377 ' . number_format((float) ($row->tds_amt), 2, '.', ''),
					'company' => $row->company->short_name,
				];
				$rowReturn[] = $val;
			}
			$output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
			return json_encode($output);
		}
	}
	public function export_transafer_list(Request $request)
	{
		if ($request->ajax()) {
			$input = $request->all();
			$start = $input["start"];
			$limit = $input["limit"];
			$_fileName = Session::get('_fileName');
			$returnURL = URL::to('/') . "/asset/tds_transfer" . $_fileName . ".csv";
			$fileName = env('APP_EXPORTURL') . "asset/tds_transfer" . $_fileName . ".csv";
			global $wpdb;
			$postCols = array(
				'post_title',
				'post_content',
				'post_excerpt',
				'post_name',
			);
			header("Content-type: text/csv");
		}
		$data = $this->repository->getAllTdsTransfer()->has('company')->whereNull('deleted_at')
			->with([
				'company:id,name,short_name',
				'AllHeadTransaction:id,head_id,daybook_ref_id,branch_id,amount,description,payment_type,payment_mode,entry_date,entry_time,is_deleted,company_id,created_by_id',
				'AllHeadTransaction.AccountHeads:id:head_id,sub_head',
				// 'payable',
				// 'payable.challan'
			]);

		if (!empty($input)) {
			$data->where(
				function ($query) use ($input) {
					if ($input['transfer_date'] != NULL) {
						$transfer_date = date('Y-m-d', strtotime(convertDate($input['transfer_date'])));
						$query->whereDate('transfer_date', $transfer_date);
					}
					if ($input['payable_head_id'] != NULL) {
						$query->whereHas('AllHeadTransaction.AccountHeads', function ($q) use ($input) {
							$q->where('head_id', 'LIKE', '%' . $input['payable_head_id'] . '%');
						});
					} elseif ($input['head_type'] != NULL) {
						if ($input['head_type'] == 22) {
							$headId = [22, 62, 63, 175, 176, 177, 264, 265, 318, 327];
						} elseif ($input['head_type'] == 169) {
							$headId = [];
						} elseif ($input['head_type'] == 322) {
							$headId = [322, 324, 325];
						} elseif ($input['head_type'] == 330) {
							$headId = [330, 331, 332, 333, 334, 335];
						}
						$query->whereHas('AllHeadTransaction.AccountHeads', function ($q) use ($headId) {
							$q->whereIn('head_id', $headId);
						});
					}
				}
			);
		}
		$headName = $this->repository->getAllAccountHeads()->pluck('sub_head', 'head_id');
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
			$val = [
				'S/No' => $sno,
				'TRANSFER DATE' => date('d/m/Y', strtotime($row->transfer_date)),
				'DATE RANGE' => date("d/m/Y", strtotime(convertDate($row->start_date))) . ' - ' . date("d/m/Y", strtotime(convertDate($row->end_date))),
				'HEAD NAME' => $headName[$row->head_id],
				'HEAD AMOUNT' => number_format((float) $row->tds_amt, 2, '.', ''),
				// 'PANALTY AMOUNT' => number_format((float) ($row->payable ? ($row->payable->paid_amount) : 0), 2, '.', ''),
				// 'PAYMENT DATE' => $row->payable ? date('d/m/Y', strtotime($row->payable->payment_date)) : 'N/A',
				// 'IS PAID' => $row->is_paid == 0 ? 'No' : 'Yes',
				// 'CHALLAN SLIP' => $row->payable ? $row->payable->challan->file_name : 'N/A',
				'COMPANY' => $row->company->short_name,
			];
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
	}
	public function collection()
	{
		if (check_my_permission(auth()->user()->id, "364") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = "Duties & taxes | Gst Collection List";
		$data['state'] = $this->repository->getAllStates()->where('status', '1')->pluck('name', 'id');
		return view('templates.admin.duties_taxes.gst.report.collection ', $data);
	}
	public function collection_listing(Request $req)
	{
		if ($req->ajax()) {
			$arrFormData = [];
			if (!empty($_POST['searchform'])) {
				foreach ($_POST['searchform'] as $frm_data) {
					$arrFormData[$frm_data['name']] = $frm_data['value'];
				}
			}
			if ($arrFormData['company_id'] != '' && isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
				$state = isset($arrFormData['state']) && $arrFormData['state'] != '' ? $arrFormData['state'] : '0';
				$company_id = isset($arrFormData['company_id']) ? $arrFormData['company_id'] : NULL;
				$start_date = $arrFormData['start_date'] ? date('Y-m-d', strtotime(convertdate($arrFormData['start_date']))) : 0;
				$end_date = $arrFormData['end_date'] ? date('Y-m-d', strtotime(convertdate($arrFormData['end_date']))) : 0;
				$data = DB::select('call gst_collection(?,?,?,?)', [$state, $company_id, $start_date, $end_date]);
				$count = $totalCount = count($data);
				$sno = $_POST['start'];
				$rowReturn = array();
				if ($sno == 0) {
					$token = Session::get('_token');
					Cache::put('gst_collection_listing_admin_' . $token, $data);
					Cache::put('gst_collection_listing_count_admin_' . $token, $count);
				}
				$result = array_slice($data, $_POST['start'], $_POST['length']);
				foreach ($result as $row) {
					$sno++;
					$val = [
						'DT_RowIndex' => $sno,
						'state' => $row->state ?? 'N/A',
						'branch' => $row->branch ?? 'N/A',
						'date' => $row->date ? (date('d/m/Y', strtotime(convertdate($row->date)))) : 'N/A',
						'customer_id' => $row->member_id ?? 'N/A',
						'name' => u($row->name) ?? 'N/A',
						'amount' => isset($row->amount) ? number_format($row->amount, 2) . ' &#8377;' : 'N/A',
						'gst' => isset($row->gst) ? $row->gst . ' &#8377;' : 'N/A',
						'head' => $row->head ?? 'N/A'
					];
					$rowReturn[] = $val;
				}
				$output = ["draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn];
			} else {
				$output = array(
					"draw" => 0,
					"recordsTotal" => 0,
					"recordsFiltered" => 0,
					"data" => 0,
				);
			}
			return json_encode($output);
		}
	}
	public function collection_listing_export(Request $req)
	{
		$token = Session::get('_token');
		$data = Cache::get('gst_collection_listing_admin_' . $token);
		$count = Cache::get('gst_collection_listing_count_admin_' . $token);

		$input = $req->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/report/gst_collection_listing.csv";
		$fileName = env('APP_EXPORTURL') . "report/gst_collection_listing.csv";

		// header("Content-type: text/csv");
		$totalResults = $count;
		$result = 'next';
		if (($start + $limit) >= $totalResults) {
			$result = 'finished';
		}
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
		$records = array_slice($data, $start, $limit);
		foreach ($records as $row) {
			$sno++;
			$val = [
				'S/No' => $sno,
				'STATE' => $row->state ?? 'N/A',
				'BRANCH' => $row->branch ?? 'N/A',
				'DATE' => $row->date ? (date('d/m/Y', strtotime(convertdate($row->date)))) : 'N/A',
				'CUSTOMER ID' => $row->member_id ?? 'N/A',
				'CUSTOMER NAME' => u($row->name) ?? 'N/A',
				'AMOUNT' => $row->amount ?? 'N/A',
				'GST' => $row->gst ?? 'N/A',
				'HEAD' => $row->head ?? 'N/A'
			];
			if (!$headerDisplayed) {
				fputcsv($handle, array_keys($val));
				$headerDisplayed = true;
			}
			fputcsv($handle, $val);
		}
		fclose($handle);
		if ($totalResults == 0) {
			$percentage = 100;
		} else {
			$percentage = ($start + $limit) * 100 / $totalResults;
			$percentage = number_format((float) $percentage, 1, '.', '');
		}
		$response = array(
			'result' => $result,
			'start' => $start,
			'limit' => $limit,
			'totalResults' => $totalResults,
			'fileName' => $returnURL,
			'percentage' => $percentage
		);
		echo json_encode($response);
	}
}