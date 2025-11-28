<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Country;
use App\Models\PropertyNeedsApiUser;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PropertyNeedApiUser extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $data = PropertyNeedsApiUser::with(['agency'])->latest()->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($row) {
                    return $row->id;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $btn .= '<a href="' . route('property-need-api-user.show', $row->id) . '" class="btn btn-xs" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-toggle="tooltip" data-placement="top" data-original-title="Delete" data-method="DELETE" data-datatable = "ApiUserTable" data-url = "' . route('property-need-api-user.destroy', $row->id) . '" class="btn  btn-xs deletemodel "><i class="fa fa-trash"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->addColumn('agency', function ($row) {
                    return ucwords($row->agency?->agencyRegister?->business_name ?? null) ?? '';
                })
                ->rawColumns(['action', 'created_at'])
                ->make(true);
        }

        $data['title'] = 'Property Needs Api';
        $data['active_page'] = 'property-need';

        return view('propertyneedapi.index', $data);
    }

    public function create()
    {
        $data['title'] = 'Property Needs Api';
        $data['active_page'] = 'property-need';
        $data['countries'] = Country::select('phonecode', 'name')->get();
        $data['agencies'] = Admin::role('agency')
            ->doesntHave('external_api')
            ->select('id', 'name')
            ->pluck('name', 'id')->toArray();
        return view('propertyneedapi.add', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'country' => 'required|string',
            'name' => 'required|string',
            'city' => 'nullable|string',
            'suburb_name' => 'nullable|string',
            'property_type' => 'nullable|string',
            'email' => 'nullable|string|email',
            'dial_code' => 'nullable|string',
            'contact' => 'nullable|string',
            'password' => 'required|string',
            'agency' => 'required||exists:' . Admin::class . ',id',
        ]);

        try {
            DB::beginTransaction();
            PropertyNeedsApiUser::create([
                'country' => $request->country,
                'name' => $request->name,
                'user_name' => $request->name,
                'suburb_name' => $request->suburb_name ?? null,
                'city' => $request->city ?? null,
                'property_type' => $request->property_type ?? null,
                'email' => $request->email ?? null,
                'contact' => $request->contact ?? null,
                'password' => $request->password,
                'dial_code' => $request->dial_code ?? null,
                'created_at' => now(),
                'updated_at' => now(),
                'admin_id' => $request->agency
            ]);
            $return = response()->json([
                'status' => 'success',
                'msg' => 'Added Successfully',
            ]);
            DB::commit();
            return $return;
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong: ' . $e->getMessage() . ' - ' . $e->getLine(),
            ], 500);
        }
    }

    public function show($id)
    {
        $data['title'] = 'Property Needs Api User Details';
        $data['active_page'] = 'property-need';
        $data['countries'] = Country::select('phonecode', 'name')->get();
        $data['api'] = PropertyNeedsApiUser::findOrFail($id);
        $data['agencies'] = Admin::role('agency')
            ->doesntHave('external_api')
            ->select('id', 'name')
            ->pluck('name', 'id')->toArray();

        return view('propertyneedapi.add', $data);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            $user = Auth::user();
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Incorrect password',
                ]);
            }
            $dataId = $request->dataId;
            $apiUser = PropertyNeedsApiUser::find($dataId);
            if (!$apiUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Record not found',
                ]);
            }
            $apiUser->delete();
            return response()->json([
                'status' => 'success',
                'msg' => 'Deleted Successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Deletion process encountered an error: ' . $e->getMessage(),
            ]);
        }
    }
}
