<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Auth;
use App\Models\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Validator;
use Carbon\Carbon;
use DB;
use URL;
use Session;
use Image;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Admin\CommanController;
use App\Models\TdsDeductionSetting;

class TdsdepositController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }




    public function index()
    {

		if ( check_my_permission(Auth::user()->id, "162") != "1")
        {
            return redirect()
                ->route('admin.dashboard');
        }
        //$data['tds'] = TdsDeductionSetting::where('status',1)->first();
        $data['title'] = "TDS Setting | Listing ";
        return view('templates.admin.tds_deposit.index', $data);
    }

	public function tds_deposite_listing(Request $request)
    {
          if ($request->ajax()) {

	        $data = TdsDeductionSetting::select('id','tds_per','start_date','end_date','created_at','tds_amount','type')->orderBy('created_at','desc');
            $count = $data->count("id");
            $totalCount = $count;
	        $data = $data->orderby('id','DESC')->offset($_POST['start'])->limit($_POST['length'])->get();


            $sno=$_POST['start'];

            $rowReturn = array();

            foreach ($data as $row)

            {

                $sno++;
                $val['DT_RowIndex']=$sno;

                if($row->start_date){
                    $val['start_date']   = date("d/m/Y", strtotime( $row->start_date));
                }else{
                    $val['start_date']   = 'N/A';
                }

                //$val['start_date'] = date("d/m/Y", strtotime( $row->start_date));

                if($row->end_date){
                    $val['end_date']   = date("d/m/Y", strtotime( $row->end_date));
                }else{
                    $val['end_date']   = 'N/A';
                }

                $val['created_at'] = date("d/m/Y", strtotime( $row->created_at));
                $val['tds_percentage'] = $row->tds_per;
                $val['tds_amount'] = number_format((float)$row->tds_amount, 2, '.', '');

                $type ='N/A';
                if($row->type == 1)
                {
                    $type = 'Interest On Deposite With Pencard';
                }
                elseif($row->type == 2)
                {
                    $type = 'Interest On Deposite Senior Citizen';
                }
                elseif($row->type == 3)
                {
                    $type = 'Interest On Commission With Pencard';
                }
                elseif($row->type == 4)
                {
                    $type = 'Interest On Commission WithOut Pencard';
                }
                elseif($row->type == 5)
                {
                    $type = 'Interest On Deposite Without Pencard';
                }

                $val['type'] = $type;

                $rowReturn[] = $val;

            }

        $output = array("branch_id"=>Auth::user()->branch_id, "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn );
        return json_encode($output);
        }
    }

     public function create()
    {

		// if (check_my_permission(Auth::user()->id, "136") != "1")
        // {
        //     return redirect()
        //         ->route('admin.dashboard');
        // }

        $data['title'] = "TDS Setting | Create ";
        return view('templates.admin.tds_deposit.create_tds_deposite', $data);
    }

    public function update(Request $request)
    {
        $rules = ['tds_per' => 'required', 'tds_amount' => 'required'];

        $customMessages = ['required' => 'The :attribute field is required.'];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try
        {

            $globaldate = $request->created_at;

            $data['tds_per'] = $request->tds_per;
            $data['tds_amount'] = $request->tds_amount;
            $data['old_per'] = $request->old_per;
            $data['old_amount'] = $request->old_amount;
            $data['updated_at'] = $globaldate;

            TdsDeductionSetting::where('id', $request->id)->update($data);

            DB::commit();

        }
        catch(\Exception $ex)
        {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.tds_deposit')->with('success', 'Tds Updated  Successfully!');
    }

    public function get_tds_deposite_detail(Request $request){

    	$data = TdsDeductionSetting::select('id','created_date','tds_head','vendor_name','pan_number','dr_entry','cr_entry','balance')->where('type',$request->id)->orderBy('start_date','desc')->first();
        if($data){


    	if($data->end_date !='')
    	{
    		$date = date("d/m/Y", strtotime($data->end_date));;
    	}
    	else
    	{
    		$date =date("d/m/Y", strtotime($data->start_date));;
    	}
    	}
        else{
            $date = '';
        }
    	return  response()->json(['data'=>$data,'date'=>$date]);
    }


     public function save(Request $request){

     	$data = TdsDeductionSetting::where('type',$request->type)->orderBy('start_date','desc')->first();
     	$start_date = date("Y-m-d", strtotime(convertDate($request->start_date)));
        if($data){
     	if($data->start_date == $start_date)
     	{
     		$tds_data['start_date'] =($start_date);
            $tds_data['tds_per'] = $request->tds_per;
            $tds_data['tds_amount'] = $request->tds_amount;
            $tds_data['type'] = $request->type;
            $tds_data['status'] =1;
            $tds_data['end_date'] =null ;

            $tds_data = TdsDeductionSetting::create($tds_data);
			return redirect()->route('admin.tds_deposit')->with('success', 'Tds Setting Created Successfully!');
     	}
     	else{
            $new_date = $request->start_date;
            $date = date("Y-m-d", strtotime(convertDate($request->start_date)));
            $n_date = date('Y-m-d',strtotime('-1 day',strtotime(str_replace('/', '-', $date))));

     		$data->end_date = $n_date;

     		$data->update();
     		$tds_data['start_date'] =($start_date);
     		$tds_data['tds_per'] = $request->tds_per;
     		$tds_data['tds_amount'] = $request->tds_amount;
     		$tds_data['type'] = $request->type;
     		$tds_data['status'] =1;
     		$tds_data['end_date'] =null ;

     		$tds_data = TdsDeductionSetting::create($tds_data);
			return redirect()->route('admin.tds_deposit')->with('success', 'Tds Setting Created Successfully!');
     	}
     	}
        else{
            $tds_data['start_date'] =($start_date);
            $tds_data['tds_per'] = $request->tds_per;
            $tds_data['tds_amount'] = $request->tds_amount;
            $tds_data['type'] = $request->type;
            $tds_data['status'] =1;
            $tds_data['end_date'] =null;;

            $tds_data = TdsDeductionSetting::create($tds_data);
			return redirect()->route('admin.tds_deposit')->with('success', 'Tds Setting Created Successfully!');
        }

    }




}



