<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Auth;
use App\Http\Controllers\Controller;
use URL;
use App\Models\{MemberCompany,Form15G};
use App\Services\ImageUpload;
class FormGController extends Controller
{    
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index($id)
    {	
		if(check_my_permission( Auth::user()->id,"238") != "1"){
			return redirect()->route('admin.dashboard');
		  }
		if($id){	
			$check = MemberCompany::with(['member' => function($q){ 
					$q->select('id','first_name','last_name','dob','email','mobile_no','status','signature','photo','village','pin_code','state_id','district_id','city_id','associate_id','branch_id','address','gender','company_id','member_id')
						->with(['memberIdProof'=>function($q){ 
							$q->select('id','first_id_no','second_id_no','member_id','first_id_type_id','second_id_type_id');
						}]);   
				}])
				->whereId($id)
				->first();
			$first_id_type_id = $check->member->memberIdProof->first_id_type_id;
			$second_id_type_id = $check->member->memberIdProof->second_id_type_id;
			if($first_id_type_id == 5 || $second_id_type_id == 5){
				$data['company_id'] = MemberCompany::whereId($id)->with('member')->first(['id','company_id','customer_id','member_id']);			
				$data['member_id'] = $id;  
				$data['title'] = "Member | Update  15G/15H";
				return view('templates.admin.form_g.index', $data);
		    }else{
				$previousUrl = URL::previous();
				return redirect('https://my.samraddhbestwin.com/admin/dashboard');			
		   }
		}        
    }
     public function getData(Request $request)
    {
		if(check_my_permission( Auth::user()->id,"238") != "1"){
			return redirect()->route('admin.dashboard');
		  }
		$companyId = $request->companyId;
		$memberId = $request->memberId;
        $data = Form15G::where('year','!=','NULL')
			->with([
				'member:id,first_name,last_name',
				'memberCompany:id,customer_id',
				'company:id,name'
			])
			->where('member_id',$memberId)
			->whereIsDeleted('0')
			->orderBy('created_at','desc');
		$data = $data->get();
		$image = [];
		foreach($data as $key => $val){
			$image[$key] = ImageUpload::generatePreSignedUrl('update_15g/'.($val->file));
		}
		return response()->json(['form' => $data,'image'=>$image]);
    }
    public function save(Request $request)
    {
		if($request->status == 0 || !isset($request->status)){
			$form_update = Form15G::where([
				'member_id'=>$request->member_id,
				'customer_id'=>$request->customer_id,
				'year'=>$request->year,
				'status'=>'1',
				'company_id'=>$request->customerId
			])->first();
			if($form_update){
				$form_update->update(['status'=>'0']);
			}
		}
		$allmember = MemberCompany::whereCustomerId($request->customer_id)->whereCompanyId($request->customerId)->first(['id','customer_id','company_id']);
        $data['year'] = $request->year; 
        $data['customer_id'] = $allmember->customer_id;
		$data['company_id'] = $request->customerId;
		$data['member_id'] = $request->member_id;
        $data['max_year'] = $request->max_year;
		$data['status'] = 1;
		$data['is_deleted'] = '0';
        if($request->year || $request->year){
			$form = Form15G::create($data);
			$id=$form->id;
			if($request->hasFile('file')){
				$mainFolder = 'update_15g';
				$file =$request->file;
				$uploadFile = $file->getClientOriginalName();
				$filename = pathinfo($uploadFile, PATHINFO_FILENAME);
				$fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();
				ImageUpload::upload($file, $mainFolder,$fname);
				// $file->move($mainFolder,$fname);
				$fData = [
					'file_name' => $fname,
					'file_path' => $mainFolder,
					'file_extension' => $file->getClientOriginalExtension(),
				];
				$form15GUpdate = Form15G::find($id);
				$form15GUpdate->file=$fname; 
				$form15GUpdate->save();
			}
		}
		$record = Form15G::where('year','!=','NULL')->get();
		
        return response()->json($record);
    }
	  public function delete(Request $request)
      {
        $form = Form15G::find($request->id);
        // $res = $form->delete();
        $res = $form->update(['is_deleted'=>'1']);
        return response()->json($res);
      } 
	public function datacheck(Request $request){
		$data = Form15G::whereCompanyId($request->selectedcustomerId)
			->whereIsDeleted('0')
			->whereStatus(1)
			->whereMemberId($request->memberId)
			->whereCustomerId($request->customerId)
			->where('year',$request->year)
			->exists();		
	   $response = [
		'data' => $data?1:0,
	   ];
		return response()->json($response);
	}
}