<?php

namespace App\Http\Controllers\Branch;

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
use Illuminate\Support\Facades\Auth;
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
		if (!in_array('View Notice Board', auth()->user()->getPermissionNames()->toArray())) {
			return redirect()->route('branch.dashboard');
		}
		$data['title'] = 'Notice Board';
		$getBranchId = getUserBranchId(Auth::user()->id);

		$noticeIds = AssignNoticeToBranch::where('status', 1)->where('branch_id', $getBranchId->id)->pluck('notice_id')->toArray();
		if (count($noticeIds) > 0) {
			$data['noticeLists'] = Noticeboard::select(['id', 'title', 'start_date'])->whereIn('id', $noticeIds)->orderBy('created_at', 'desc')->where('status', 1)->get();

			$data['noticeDocuments'] = Noticeboard::select(['id', 'title', 'start_date'])
				->where('id', end($noticeIds))
				->where('status', 1)
				->with(['files' => function ($q) {
					$q->select(['id', 'file_name', 'file_path']); }])->first();

		} else {
			$data['noticeLists'] = [];

			$data['noticeDocuments'] = null;
		}

		return view('templates.branch.notice_board.index', $data);
		//
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function listing()
	{
		$data = Noticeboard::with('files');

		return Datatables::of($data)

			->addColumn('document', function ($row) {

				return Files::where('notice_id', $row->id)->pluck('file_name')->first();
			})
			->rawColumns(['document'])
			->addColumn('status', function ($row) {
				if ($row->status == 0) {
					$status = 'Blocked';
				} else {
					$status = 'Active';

				}


				return $status;
			})
			->rawColumns(['status'])
			->addColumn('action', function ($row) {
				return 'Action';
			})
			->rawColumns(['action'])
			->addColumn('created', function ($row) {
				$re_date = date("d/m/Y", strtotime($row->created_at));
				return $re_date;
			})
			->rawColumns(['created'])
			->make(true);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$data['title'] = 'Add Notice';
		$data['states'] = array();
		foreach (State::pluck('name', 'id') as $key => $val) {
			$stateDate['state'] = array('id' => $key, 'name' => $val);
			$stateDate['branch'] = Branch::where('state_id', $key)->pluck('name', 'id');
			if (count($stateDate['branch']) > 0) {
				$data['states'][] = $stateDate;
			}
		}
		return view('templates.admin.notice_board.create', $data);

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
		]);

		if ($validator->fails()) {
			return back()->with('alert', 'Please fill required field!');
		}

		$fileId = null;

		$noticeData = array('title' => $request->title, 'file_id' => $fileId, 'start_date' => $request->application_date);

		$nodiceId = Noticeboard::create($noticeData);

		if ($nodiceId) {
			if ($request->hasFile('document')) {
				foreach ($request->file('document') as $document) {
					$photo_image = $document;
					$photo_filename = time() . '.' . $photo_image->getClientOriginalExtension();
					// $photo_location = 'asset/notice-board/';
					$photo_location = 'notice-board/';
					ImageUpload::upload($document, $photo_location,$photo_filename);
					// $photo_image->move($photo_location, $photo_filename);
					$data = [
						'notice_id' => $nodiceId->id,
						'file_name' => $photo_filename,
						'file_path' => $photo_location,
						'file_extension' => $photo_image->getClientOriginalExtension(),
					];
					Files::create($data);
				}
			}
			if (count($request->branch) > 0) {
				//AssignNoticeToBranch::where('notice_id', $nodiceId->id)->delete();
				foreach ($request->branch as $branch) {
					AssignNoticeToBranch::create(['notice_id' => $nodiceId->id, 'branch_id' => $branch]);
				}
			}

			return back()->with('success', 'Notice created Successful!');
		}
	}

	public function uploadStoreImage($file, $loanId, $folder, $prooffolder)
	{
		$stateid = getBranchState(Auth::user()->username);
		// $mainFolder = storage_path() . '/images/loan/document/' . $loanId;
		$mainFolder = 'loan/document/' . $loanId;
		// File::makeDirectory($mainFolder, $mode = 0777, true, true);
		$loanTypeFolder = $mainFolder . '/' . $folder;
		// File::makeDirectory($loanTypeFolder, $mode = 0777, true, true);
		$loanTypeProffFolder = $loanTypeFolder . '/' . $prooffolder . '/';
		//File::makeDirectory($loanTypeProffFolder, $mode = 0777, true, true);
		$uploadFile = $file->getClientOriginalName();
		$filename = pathinfo($uploadFile, PATHINFO_FILENAME);
		$fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
		ImageUpload::upload($file, $loanTypeProffFolder,$fname);
		// $file->move($loanTypeProffFolder, $fname);
		$data = [
			'file_name' => $fname,
			'file_path' => $loanTypeProffFolder,
			'file_extension' => $file->getClientOriginalExtension(),
			'created_at' => checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid),
		];
		$res = Files::create($data);
		return $res->id;
	}

	public function getDocument(Request $request)
	{
		$id = $request->id;
		$documents = Files::where('notice_id', $id)->select('file_name', 'file_path')->get();
		$image = [];
		foreach($documents as $key => $val){
			$image[$key] = ImageUpload::generatePreSignedUrl('notice-board/'.($val->file_name));
		}
		if (count($documents) > 0) {
			$response = [
				'data' => $documents,
				'image' => $image,
			];
			return $response;
		} else {
			return null;
		}
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
	public function destroy(Noticeboard $noticeboard)
	{
		//
	}
}