<?php 

namespace App\Http\Controllers\Admin; 



use App\Models\MemberIdProof;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Validator;

use App\Models\Member; 

use App\Models\ExpenseItem; 

use App\Models\Branch; 

use App\Models\Receipt;

use App\Models\ReceiptAmount;

use App\Models\Grouploanmembers;

use App\Models\Memberloans;

use App\Models\Loans;

use App\Models\Memberinvestments;

use App\Models\Memberinvestmentspayments;

use App\Models\CorrectionRequests;

use App\Models\MemberTransaction;

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

use Illuminate\Support\Facades\Schema;



class BillExpenseController extends Controller

{

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }

    public function index()
    {
		

		if(check_my_permission( Auth::user()->id,"166") != "1"){

		  return redirect()->route('admin.dashboard');

		}	

		

		$data['title']='Bill Expense'; 

        $data['branch'] = Branch::where('status',1)->get();

		$data['expense_item'] = ExpenseItem::where('status',1)->get();

        return view('templates.admin.bill_expense.index', $data);

    }

	public function get_item_details(Request $request)
    {

		$itemID = $request->item_id;

		$currentItemRow = $request->currentItemRow;

		$expence_data = ExpenseItem::where('id',$itemID)->where('status',1)->first();

		$viewData = '';

		if(isset($expence_data->account)){

			$viewData .= '<td><input type="text" name="account" class="expense-item" data-item-id="'.$expence_data->id.'" data-filed="" value="'.$expence_data->account.'"></td>';

		} else {

			$viewData .= '<td></td>';

		}

		if(isset($expence_data->sub_head_id)){

			$viewData .= '<td>'.$expence_data->sub_head_id.'</td>';

		} else {

			$viewData .= '<td></td>';

		}

		if(isset($expence_data->hsn_code)){

			$viewData .= '<td><input type="text" name="hsn_code" class="expense-item" data-item-id="'.$expence_data->id.'" data-filed="hsn_code" value="'.$expence_data->account.'"></td>';

		} else {

			$viewData .= '<td></td>';

		}

		if(isset($expence_data->qty)){

			$viewData .= '<td>'.$expence_data->qty.'</td>';

		} else {

			$viewData .= '<td></td>';

		}

		if(isset($expence_data->price)){

			$viewData .= '<td>'.$expence_data->price.'</td>';

		} else {

			$viewData .= '<td></td>';

		}

		if(isset($expence_data->price)){

			$viewData .= '<td>'.$expence_data->price.'</td>';

		} else {

			$viewData .= '<td></td>';

		}

		if(isset($expence_data->tax)){

			$viewData .= '<td>'.$expence_data->tax.'</td>';

		} else {

			$viewData .= '<td></td>';

		}

		if(isset($expence_data->gst)){

			$viewData .= '<td>'.$expence_data->gst.'</td>';

		} else {

			$viewData .= '<td></td>';

		}

		if(isset($expence_data->bill)){

			$viewData .= '<td>'.$expence_data->bill.'</td>';

		} else {

			$viewData .= '<td></td>';

		}

		if(isset($expence_data->total)){

			$viewData .= '<td>'.$expence_data->total.'</td>';

		} else {

			$viewData .= '<td></td>';

		}

		return \Response::json(['view' => $viewData,'msg_type'=>'success']);

	}

	public function get_items(Request $request)
    {

		$itemID = $request->item_id;

		$newRowItem = $request->newRowItem;

		$expence_data = ExpenseItem::where('status',0)->get();

		$viewData = '';

		$viewData .= '<tr id="trRow'.$newRowItem.'">';

		$viewData .= '<td id="tdRow'.$newRowItem.'">';

		$viewData .= '<select class="form-control item_id" id="item_id" name="item_id" data-row-id="'.$newRowItem.'">';

		$viewData .= '<option value="">Choose...</option>';

		for($i=0; $i<count($expence_data); $i++){

			$viewData .= '<option value="'.$expence_data[$i]->id.'">'.$expence_data[$i]->name.'</option>';

		}

		$viewData .= "</select></td></tr>";

		return \Response::json(['view' => $viewData,'msg_type'=>'success']);

	}

}

