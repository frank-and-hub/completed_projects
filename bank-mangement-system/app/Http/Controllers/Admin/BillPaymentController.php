<?php 
namespace App\Http\Controllers\Admin; 

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Member; 
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

class BillPaymentController extends Controller
{

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }


    public function index()
    {
		
		if(check_my_permission( Auth::user()->id,"167") != "1"){
		  return redirect()->route('admin.dashboard');
		}	
		
		$data['title']='Bill Payment'; 
        $data['branch'] = Branch::where('status',1)->get();
         

        return view('templates.admin.bill_payment.index', $data);
    }
	
	
}
