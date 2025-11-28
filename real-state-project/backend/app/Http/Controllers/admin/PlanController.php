<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSubscription;
use App\Models\Plans;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $active_page = 'plans';
        $title = 'Plans';
        if ($request->ajax()) {
            $data = Plans::latest()->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('type', function ($row) {
                    return ucwords($row->type == 'privatelandlord' ? 'Private landlord' : $row->type);
                })
                ->addColumn('amount', function ($row) {
                    return numberFormat($row->amount);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $btn .= '<a href="javascript:void(0)" data-id ="' . $row->id . '" class="btn btn-xs  edit_plan " data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-edit"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->rawColumns(['action', 'created_at'])
                ->make(true);
        }
        return view('plans.index', compact('active_page', 'title'));
    }

    public function insertorupdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                // 'plan_name' => 'required',
                'plan_amount' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            $planId = $request->plan_id;

            $updateFields = [
                'amount' => $request->plan_amount,
            ];

            if ($planId) {
                $plan = Plans::where('id', $planId)->update($updateFields);
                $msg = 'Updated Successfully';
            } else {
                $updateFields['plan_name'] = $request->plan_name;
                $updateFields['plan_month'] = $request->plan_month;
                $plan = Plans::create($updateFields);
                $planId = $plan->id;
                $msg = 'Add Successfully';
            }

            if ($request->plan_name) {
                $adminSub = AdminSubscription::where('status', 'ongoing')
                    ->where('subscription_id', $planId)
                    ->where('amount', 0)
                    ->update([
                        'status' => 'expired'
                    ]);
            }

            return response()->json([
                'status' => 'success',
                'msg' => $msg,
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Something Went Wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit_plan(Request $request)
    {
        $id = $request->dataId;
        $data = Plans::where('id', $id)->first();
        return response()->json([
            'status' => 'success',
            'plan_name' => $data->plan_name,
            'plan_amount' => $data->amount,
            'plan_id' => $id,
        ]);
    }
}
