<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use App\Models\Noticeboard;
use App\Models\Branch;
use App\Models\State;
use App\Models\Files;
use App\Models\AssignNoticeToBranch;
use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Image;
use Validator;
use App\Services\ImageUpload;
class NoticeboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
	    if(check_my_permission( Auth::user()->id,"103") != "1"){
		  return redirect()->route('admin.dashboard');
		}	
		$data['title']='Notice Listing';
    	return view('templates.admin.notice_board.index',$data);
        //
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
    public function listing()
    {
    	$data = Noticeboard::orderBy('id','desc');
    	return Datatables::of($data)
		    ->addIndexColumn()
		    ->addColumn('document', function($row){
			    return Files::where('notice_id', $row->id)->pluck('file_extension')->first();
		    })
		    ->rawColumns(['document'])
		    ->addColumn('files', function($row){
			    $files = Files::where('notice_id', $row->id)->select('file_name','file_path')->get();
			    $fileName = '';
			    if ($files && count($files) > 0 ) {
					foreach ( $files as $key => $file )
			    	$url = ImageUpload::generatePreSignedUrl($file->file_path . $file->file_name);
			    	$fileName .= '<a href="'.$url.'" target="_blank">'.$file->file_name.'</a><br/>';
			    }
			    return $fileName;
		    })
		    ->rawColumns(['files'])
		    ->addColumn('status', function($row){
			    if($row->status==0)
			    {
				    $status = 'Blocked';
			    }
			    else
			    {
				    $status = 'Active';
			    }
			    return $status;
		    })
		    ->rawColumns(['status'])
		    ->addColumn('created', function($row){
			                     $re_date = date("d/m/Y", strtotime($row->start_date));
			                     return $re_date;
		                     })
		    ->rawColumns(['created'])
		    ->addColumn('action', function($row){
			     $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
			    $btn  .=  '<button class="dropdown-item delete-notice "  href="#" data-id="'.$row->id.'"><i class="icon-fixed-width icon-trash"></i>Delete</button>';
                if ($row->status == 0)
                {
                    $btn  .=  '<button class="dropdown-item status-notice "  href="#" data-id="'.$row->id.'"><i class="icon-checkmark4 "></i>Active</button>';
                }
                else
                {
                     $btn  .=  '<button class="dropdown-item status-notice "  href="#" data-id="'.$row->id.'"><i class="icon-checkmark4 "></i>Blocked</button>';;
                }
                 $btn .= '</div></div></div>';
                return $btn;
		    })
		    ->rawColumns(['action'])
		   ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
	    $data['title']='Add Notice';
	    $data['states']= array();
	    foreach( State::pluck('name','id') as $key => $val ) {
	    	$stateDate['state'] = array('id'=>$key,'name'=>$val);
	    	$stateDate['branch'] = Branch::where('state_id', $key)->pluck('name','id');
	    	if ( count( $stateDate['branch'] ) > 0 ) {
			    $data['states'][] = $stateDate;
		    }
	    }
	    return view('templates.admin.notice_board.create',$data);
        //
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
	    $validator = Validator::make($request->all(), [
		    'title' => 'required',
		    'branch' => 'required',
	    ]);
	    if ($validator->fails()) {
		    return back()->with('alert', 'Please fill required field!');
	    }
	    $fileId = null;
	    $todayDate = explode('/', $request->application_date);
	    $startDate = $todayDate[2].'-'.$todayDate[1].'-'.$todayDate[0];
	    $noticeData = array('title' => $request->title,'file_id' => $fileId, 'start_date' => $startDate );
    	$nodiceId = Noticeboard::create($noticeData);
    	if($nodiceId){
		    if ($request->hasFile('document')) {
		    	foreach ( $request->file('document') as $key =>  $document ) {
		    		$extension = $document->getClientOriginalExtension();
		    		if ( $extension == 'pdf' || $extension == 'jpeg' || $extension == 'jpg' || $extension == 'png'){
					    $photo_filename = time().'_'. $key . '.' .$extension;
					    // $photo_location = 'asset/notice-board/';
					    $photo_location = 'notice-board/';
						ImageUpload::upload($document, $photo_location,$photo_filename);
					    // $document->move($photo_location,$photo_filename);
					    $data = [
						    'notice_id' => $nodiceId->id,
						    'file_name' => $photo_filename,
						    'file_path' => $photo_location,
						    'file_extension' => $extension,
					    ];
					    Files::create($data);
				    }
			    }
		    }
    		if ( count($request->branch) > 0){
			    //AssignNoticeToBranch::where('notice_id', $nodiceId->id)->delete();
    			foreach ( $request->branch as $branch ){
				    AssignNoticeToBranch::create(['notice_id' => $nodiceId->id, 'branch_id' => $branch]);
			    }
		    }
		    return redirect('admin/notice-board')->with('success', 'Notice created Successful!');
	    }
    }
	public function uploadStoreImage($file,$loanId,$folder,$prooffolder)
	{
		$stateid = getBranchState(Auth::user()->username);
		// $mainFolder = storage_path().'/images/loan/document/' . $loanId;
		$mainFolder = 'loan/document/' . $loanId;
		// File::makeDirectory($mainFolder, $mode = 0777, true, true);
		$loanTypeFolder = $mainFolder.'/'.$folder;
		// File::makeDirectory($loanTypeFolder, $mode = 0777, true, true);
		$loanTypeProffFolder = $loanTypeFolder.'/'.$prooffolder.'/';
		//File::makeDirectory($loanTypeProffFolder, $mode = 0777, true, true);
		$uploadFile = $file->getClientOriginalName();
		$filename = pathinfo($uploadFile, PATHINFO_FILENAME);
		$fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();
		ImageUpload::upload($file, $loanTypeProffFolder,$fname);
		// $file->move($loanTypeProffFolder,$fname);
		$data = [
			'file_name' => $fname,
			'file_path' => $loanTypeProffFolder,
			'file_extension' => $file->getClientOriginalExtension(),
			'created_at' => checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid),
		];
		$res = Files::create($data);
		return $res->id;
	}
    public function getData()
    {
    	$data = array();
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Noticeboard  $noticeboard
     * @return \Illuminate\Http\Response
     */
    public function show(Noticeboard $noticeboard)
    {
        //
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Noticeboard  $noticeboard
     * @return \Illuminate\Http\Response
     */
    public function edit(Noticeboard $noticeboard)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Noticeboard  $noticeboard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Noticeboard $noticeboard)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Noticeboard  $noticeboard
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
    	$response = false;
	    $response = Files::where('notice_id', $request->id)->delete();
	    $response = Noticeboard::where('id',$request->id )->delete();
	    $response = AssignNoticeToBranch::where('notice_id',$request->id )->delete();
	    return $response;
    }
      public function update_status(Request $request)
    {
        $id = $request->id;
		$response = false;
		$response2 = false;
		DB::beginTransaction();
		try{
			$response = Noticeboard::whereId($id);
			if($response->first()->status==1){
			    $response->update(['status'=>0]);
			}else{
			    $response->update(['status'=>1]);
			}
			$response2 = AssignNoticeToBranch::where('notice_id',$id);
			if($response2->first()->status==1){
			    $response2->update(['status'=>0]);
			}else{
			    $response2->update(['status'=>1]);
			}
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
		}
		$message = [$response,$response2];
	    return response()->json($message);
    }
}