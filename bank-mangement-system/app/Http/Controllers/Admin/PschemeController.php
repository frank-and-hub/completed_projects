<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Interfaces\RepositoryInterface;
use App\Models\{Profits, FaCode, Carder, Memberinvestments, Plans, PlanCategory, PlanTenures, Loans, Companies, PlanLogDetails};
use URL;
use DB;

class PschemeController extends Controller
{
    /**
     * Instantiate a new controller instance.
     * @return void
     */
    protected $repository;
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->middleware('auth');
    }
    /**
     * Display a listing of the investment plans module.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "336") != "1") {
            return redirect()->route('admin.dashboard');
        }      
        $filterType = $request->filled('filter_type') ? ($request->filter_type == 1 ? 1 : 2) : null;
        $planId = $request->filled('plan_name') ? $request->plan_name : null;
        $data['title'] = 'Loan/Investment Logs';

        $data['log'] = PlanLogDetails::when(
            $filterType !== null,
            function ($query) use ($filterType) {
                return $query->where('type', $filterType);
            }
        )->when(
            $planId !== null,
            function ($query) use ($planId) {
                return $query->where('type_id', $planId);
            }
        )->orderBy('created_at', 'DESC')->get();

        if($filterType != NULL){
            if ($data['log']->isEmpty()) {
                return \Response::json(['view' => view('templates.admin.py-scheme.logDetails'), 'msg_type' => 'Data Not Found!']);
            }else{
                return \Response::json(['view' => view('templates.admin.py-scheme.logDetails', $data)->render(), 'msg_type' => 'success']);
            }
        }
        return view('templates.admin.py-scheme.logs', $data);
    }
    public function getPlanName(Request $request)
    {
        $planId = $request->type_id;
        if ($planId == 2) {
            $planName = Plans::has('company')->select('id', 'name')->get();
            return json_encode($planName);
        } else {
            $planName = Loans::has('company')->select('id', 'name')->get();
            return json_encode($planName);
        }
    }
}
