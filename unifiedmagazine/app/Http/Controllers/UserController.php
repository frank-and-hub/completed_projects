<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User, Record, Email};
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
        $array = [];
        foreach ($request->form as $k => $v) {
            $array[$v['name']] = $v['value'];
        }
        $user = User::find($array['id']);
        if ($user) {
            $user->update(['name' => $array['name'], 'email' => $array['email']]);
        }
        return $user;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $date = Carbon::now()->format('Y-m-d');
        $dateupdate = ['deleted_at' => $date, 'status' => '0'];
        $user = User::find($request->id);
        $delete = $user->update($dateupdate);
        return $user->name;
    }
    public function all_update(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        if ($status == '1') {
            $data = ['status' => '0'];
        } else {
            $data = ['status' => '1'];
        }
        User::find($id)->update($data);
        return $data;
    }
    public function list(Request $request)
    {
        $start = $request->start;
        $length = $request->length;
        $data = User::where('role', '!=', '1');
        $count = $data->count('id');
        $data = $data->skip($start)->take($length)->orderBy('id', 'desc')->get();
        $totalCount = $count;
        $sno = $_POST['start'];
        $row = array();
        foreach ($data as $val) {
            $sno++;
            $btn = '<div class="btn-group" role="group" aria-label="Basic outlined example">';
            // $btn .= '<button class="btn btn-outline-primary" onclick="status_update('.$val->id.','.$val->status.')" >Status</button>';
            $btn .= '<button class="btn btn-outline-primary" onclick="email_update(id=' . $val->id . ',name=`' . $val->name . '`,email=`' . $val->email . '`)" data-bs-toggle="modal" data-bs-target="#verticalycenteredEdit" >Edit</button>';
            $btn .= '<button class="btn btn-outline-primary" onclick="view_user(id=' . $val->id . ',name=`' . $val->name . '`,email=`' . $val->email . '`,pswd=`********`)" data-bs-toggle="modal" data-bs-target="#verticalycenteredView" >View</button>';
            if ($val->deleted_at == null) {
                $btn .= '<button class="btn btn-outline-primary modeldelete" data-bs-toggle="modal" data-bs-target="#verticalycenteredDelete" onclick="delete_user(' . $val->id . ')" data-id=' . $val->id . ' >Delete</button>';
            }
            $btn .= '</div>';
            $val = [
                's_no' => $sno,
                'name' => $val->name,
                'email' => $val->email,
                'created_at' => date('d-m-Y', strtotime($val->created_at)),
                'status' => '<button onclick="status_update(' . $val->id . ',' . $val->status . ')" class="btn btn-sm btn-' . ($val->status == 1 ? "primary" : "danger") . ' rounded-pill">' . ($val->status == '1' ? 'Active' : 'Inactive') . '</button>',
                'action' => $btn
            ];
            $row[] = $val;
        }
        $output = [
            "draw" => $_POST['draw'],
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $count,
            "data" => $row
        ];
        return json_encode($output);
    }
    public function show_list(Request $request)
    {
        $page = $request->query('page', 1);
        $userId = $request->id;
        $date = date('Y-m-d', strtotime($request->date));
        $perPage = 10; // Number of posts per page
        $email = Email::when($userId != null, function ($q) use ($userId) {
            $q->whereUserId($userId);
        })->when($date != '1970-01-01', function ($q) use ($date) {
            $q->whereDate('created_at', $date);
        })
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();
        return view('admin.recive', compact('email'));
    }
    public function showList(Request $request)
    {
        $array[] = $request->all();
        $date = $array[0]['date'];
        if($array[0]['users']){        
            $userId = $array[0]['users'];
            $start = $request->start;
            $length = $request->length;
            $data = Email::when($userId != 'all',function($q) use ($userId) {
                $q->whereUserId($userId);
            })->whereDate('created_at', $date);
            $data = $data->orderBy('id', 'desc')->get()->toArray();
        }
        return view('admin.table', compact('data'))->render();
    }
    public function showListCSV(Request $request){
        $array[] = $request->all();
        $email_id = $array[0]['email_id'];
        if($email_id){        
            $userId = $array[0]['id'];
            $start = $request->start;
            $length = $request->length;
            $data = Record::when($userId != 'all',function($q) use ($userId) {
                $q->whereUserId($userId);
            })->whereEmailId($email_id)->whereDate('created_at','!=','1970-01-01');
            $data = $data->orderBy('id', 'asc')->get()->toArray();
        }
        return view('admin.show_table', compact('data'))->render();
    }
    public function infinite(Request $request)
    {
        $page = $request->query('page', 1);
        $userId = $request->id;
        $date = date('Y-m-d', strtotime($request->date));
        $perPage = 10; // Number of posts per page
        $recive = Record::when($userId != null, function ($q) use ($userId) {
            $q->whereUserId($userId);
        })
            ->when($date != '1970-01-01', function ($q) use ($date) {
                $q->whereDate('created_at', $date);
            })
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->latest()
            ->get();
        $sno = 1;
        $status = [
            0 => 'panding',
            1 => 'send',
            2 => 'feild',
            3 => 'panding'
        ];
        $html = '';
        if ($recive) {
            foreach ($recive as $key => $val) {
                $arrayData = json_decode($val->data);
                $sno++;
                $s = $val->status;
                $html .= '<tr>';
                $html .= "<td> $sno ";
                $html .= "<td> $val->file ";
                $html .= "<td> $arrayData->name ";
                $html .= "<td> $arrayData->companyname ";
                $html .= "<td> $status[$s] ";
            }
        } else {
            $html .= "<tr><td>No Data Found </td></tr>";
        }
        return json_encode($html);
    }

    public function getEmailCount(Request $request){
        return Email::whereId($request->id)->value('sent_email_count');
    }
}

