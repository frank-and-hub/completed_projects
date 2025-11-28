<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyContact;
use App\Models\SentPropertyUser;
use App\Models\UserSearchProperty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Property as HelpersProperty;


class SubmittedProperty extends Controller
{
    public function index(Request $request)
    {
        $active_page = 'Submitted Property Needs';
        $title = 'Submitted Property Needs';
        if ($request->ajax()) {
            $data = UserSearchProperty::with('user')->latest()->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                // ->addColumn('created_at', function ($row) {
                //     return $this->convertToSouthAfricaTime($row->created_at);
                // })
                ->addColumn('user_name', function ($row) {
                    if ($row->user) {
                        return '<a href="' . route('user_view', $row->user->id) . '">' . $row->user->name . '</a>';
                        // return '<a href="javascript:void">'.$row->user->name.'</a>';
                    } else {
                        return 'No User';
                    }
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $btn .= '<a href="' . route('submitted_property_view', $row->id) . '"  class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['created_at', 'user_name', 'action'])
                ->make(true);
        }
        return view('submittedproperty.index', compact('active_page', 'title'));
    }

    public function view(Request $request, $id)
    {
        $active_page = 'Submitted Property Needs';
        $title = 'Submitted Property Needs';
        $data = UserSearchProperty::findorfail($id);
        $additionalFeatures = json_decode($data->additional_features, true);
        // $data->request_date = Carbon::parse($data->created_at)->format('d M y');
        $data->request_date = $this->convertToSouthAfricaTime($data->created_at);
        $move_in_date = isset($additionalFeatures['move_in_date']) ? $additionalFeatures['move_in_date'] : null;
        $data->move_in_date = $move_in_date ? Carbon::parse($move_in_date)->format('d M y') : 'N/A';
        $property_feature_columns = HelpersProperty::featureColumnsByCategory();

        return view('submittedproperty.view', compact('active_page', 'data', 'property_feature_columns', 'title'));
    }

    public function metching_property(Request $request)
    {
        $title = 'Matches Property';
        if ($request->ajax()) {
            $data = SentPropertyUser::with(['property', 'property.contacts'])
                ->where('search_id', $request->search_id)
                ->latest()
                ->get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('DT_RowId', function ($row) {
                    return $row->id;
                })
                ->addColumn('property_title', function ($row) {
                    return  $row->property->title;
                })
                ->addColumn('agent_name', function ($row) {
                    $contact = $row->property->contacts->first();
                    return $contact ? $contact->fullName : '';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="actions-container">';
                    $btn .= '<a href="' . url('/') . '/property-detail?property_id=' . $row->property_id . '" target="_blank" class="btn btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="View"><i class="fa fa-eye"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['agent_name', 'property_title', 'action'])
                ->make(true);
        }
        return view('submittedproperty.view', compact('title'));
    }
}
