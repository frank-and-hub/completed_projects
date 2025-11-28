<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExternalPropertyUserRequest;
use App\Models\Admin;
use App\Models\Country;
use App\Models\ExternalPropertyUser;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;


class ExternalPropertyUserController extends Controller
{
    protected $auth;
    protected $title;
    public function __construct()
    {
        $this->auth = Auth::user();
        $this->title = 'Property Api';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ExternalPropertyUser::latest();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($row) {
                    return $row->id;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    // $btn .= '<a href="' . route('external_property_users.edit', $row->uuid) . '" class="btn btn-xs " data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-edit"></i></a>';
                    $btn .= '<a href="' . route('external_property_users.show', $row->uuid) . '" class="btn btn-xs" data-toggle="tooltip" data-original-title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '<a href="javascript:void(0)" data-id="' . $row->uuid . '" data-toggle="tooltip" data-placement="top" data-original-title="Delete" data-method="DELETE" data-datatable = "APITable" data-url = "' . route('external_property_users.destroy', $row->uuid) . '" class="btn  btn-xs deletemodel "><i class="fa fa-trash"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->addColumn('agency', function ($row) {
                    return $row->agency?->agencyRegister?->business_name ?? 'N/A';
                })
                ->addColumn('status', function ($row) {
                    $status = $row->status;
                    if ($status == 0) {
                        $btnStatus = "Inactive";
                    } else {
                        $btnStatus = "Active";
                    }
                    return $btnStatus;
                })
                ->rawColumns(['action', 'created_at'])
                ->make(true);
        }
        $data['title'] = $data['active_page'] = $this->title;
        $data['countries'] = Country::pluck('name', 'id');
        $data['view'] = false;
        return view('external_property_user.index', $data);
    }

    public function create()
    {
        $data['title'] = $data['active_page'] = $this->title;
        $data['countries'] = Country::pluck('name', 'id');
        $data['agencies'] = Admin::role('agency')
            ->doesntHave('external_api')
            ->select('id', 'name')
            ->pluck('name', 'id')->toArray();
        return view('external_property_user.add', $data);
    }

    public function show($id)
    {
        $user = ExternalPropertyUser::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Record not found',
            ]);
        }
        $data['title'] = $data['active_page'] = $this->title;
        $data['external_property'] = $user;
        $data['countries'] = Country::pluck('name', 'id');
        $data['view'] = true;
        $data['agencies'] = Admin::role('agency')->select('id', 'name')->pluck('name', 'id')->toArray();
        return view('external_property_user.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExternalPropertyUserRequest $request)
    {
        try {
            $countryCode = Country::where('name',  $request?->country)->value('phonecode');
            DB::beginTransaction();
            $createArray = [
                'name' => $request?->name,
                'email' => $request?->email ?? null,
                'password' => Hash::make($request?->password),
                'password_text' => $request?->password,
                'created_by' => $this->auth->id,
                'phone' => $request?->phone ?? null,
                'country' => $request?->country,
                'country_code' => $countryCode,
                'created_at' => now(),
                'updated_at' => now(),
                'api_key' => null,
            ];

            $newExternalPropertyUser = ExternalPropertyUser::create($createArray);
            $token = $newExternalPropertyUser->createToken($request?->name)->accessToken;
            $newExternalPropertyUser->api_key = $token;
            $newExternalPropertyUser->save();

            if (!empty($request?->agencies)) {
                foreach ($request?->agencies as $ag) {
                    $newExternalPropertyUser->agencies()->attach($ag, ['id' => Str::uuid(), 'status' => false]);
                }
            }

            $return = response()->json([
                'status' => 'success',
                'msg' => $newExternalPropertyUser ? 'Added Successfully' : 'Something went wrong !',
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

    public function edit($id)
    {
        $property = ExternalPropertyUser::find($id);
        if (!$property) {
            return response()->json([
                'status' => 'error',
                'message' => 'Record not found',
            ]);
        }
        $data['title'] = $data['active_page'] = $this->title;
        $data['external_property'] = $property;
        $data['agencies'] = Admin::role('agency')->select('id', 'name')->pluck('name', 'id')->toArray();
        $data['countries'] = Country::pluck('name', 'id');
        return view('external_property_user.add', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:' . ExternalPropertyUser::class . ',email,' . $id,
            'phone' => 'required|string|numeric|min:8',
            'country' => 'required|string',
            'password' => 'required|string',
        ]);
        try {
            $countryCode = Country::where('name',  $request->country)->value('phonecode');
            DB::beginTransaction();
            $user = ExternalPropertyUser::find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Record not found',
                ]);
            }
            $user->update([
                'name' => $request->name,
                'email' => $request->email ?? null,
                'phone' => $request->phone ?? null,
                'password' => Hash::make($request->password),
                'password_text' => $request->password,
                'country' => $request->country,
                'country_code' => $countryCode,
            ]);
            $return = response()->json([
                'status' => 'success',
                'msg' => 'Updated Successfully',
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
                'msg' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
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
            $user = ExternalPropertyUser::find($dataId);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Record not found',
                ]);
            }
            $user->delete();
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

    public function status(Request $request, $id)
    {
        $request->validate([
            'status' => 'required',
        ]);
    }

    public function get_agency_list(Request $request)
    {
        $active_page = 'agency';

        $search = $request->input('q');
        $page = $request->input('page', 1);

        $users = Admin::role($active_page)
            ->select('id', 'name')->where('name', 'like', '%' . $search . '%')
            ->paginate(10, ['*'], 'page', $page);

        $results = $users->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'text' => $user->title,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $users->hasMorePages()],
        ]);
    }
}
