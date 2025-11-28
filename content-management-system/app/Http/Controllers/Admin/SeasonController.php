<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Seasons;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

class SeasonController extends Controller
{
    public function season()
    {

        // $active_page = "seasons";
        // $page_title = "Seasons";

        $active_page = "category";
        $page_title = "Seasons";
        $breadcrumbs = [['name' => 'Categories', 'route' => route('admin.category.index')]];
        return view('admin.seasons.index', compact('active_page', 'page_title', 'breadcrumbs'));
    }

    public function create(Seasons $season)
    {
        $breadcrumbs =
            [
                ['name' => 'Categories', 'route' => route('admin.category.index')],
                ['name' => 'Seasons', 'route' => route('admin.season')]
            ];
        // $active_page = "seasons";
        // $page_title = "Seasons";
        $active_page = "category";
        $page_title = "Seasons";
        return view('admin.seasons.create', compact('season', 'breadcrumbs', 'active_page', 'page_title'));
    }
    public function dt_list(Request $request)
    {

        if ($request->ajax()) {

            $_order = request('order');
            $_columns = request('columns');
            $order_by = $_columns[$_order[0]['column']]['name'];
            $order_dir = $_order[0]['dir'];
            $search = request('search');
            $skip = request('start');
            $take = request('length');

            if (empty($request->season_type)) {
                $query = Seasons::where('hemisphere', 'north');
            } else {
                $query = Seasons::where('hemisphere', $request->season_type);
            }
            // $query = Seasons::where('hemisphere', 'north');


            $recordsTotal = $query->count();

            if (isset($search['value'])) {
                $query->Where(function ($q) use ($search) {
                    $q->whereRaw("season LIKE '%" . $search['value'] . "%' ")
                        ->OrwhereRaw("hemisphere LIKE '% " . $search['value'] . "%' ")
                        ->OrWhereRaw("start_date like '%" . $search['value'] . "%'")
                        ->OrWhereRaw("end_date like '%" . $search['value'] . "%'");
                });
            }

            $recordsFiltered = $query->count();

            $data = $query
                // ->orderBy($order_by, $order_dir)->skip($skip)->take($take)->get();
                ->orderBy('season_start_date', 'asc')->skip($skip)->take($take)->get();

            foreach ($data as &$d) {
                $d->season = ucwords($d->season);
                $d->hemisphere = ucwords($d->hemisphere);
                $d->start_date = $d->start_date;
                $d->end_date = $d->end_date;
                $editRoute =  route('admin.season.create', $d->id);
                $d->action = View::make('components.admin.actioncomponent', compact('editRoute'))->render();
            }

            return [
                "draw" => request('draw'),
                "recordsTotal" => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                "data" => $data,
            ];
        }
    }

    public function update_season(Request $request, Seasons $season)
    {
        if (!$season) {
            return back()->with('error', __('admin.season_not_found'));
        }

        $hemisphere = Seasons::where('id', $season->id)->value('hemisphere');

        $validator = Validator::make($request->all(), [
            // 'season' => 'required|in:autumn,spring,summer,winter|unique:seasons,season,except'.$season->id.',id',
            'season' => ['required', 'in:autumn,spring,summer,winter', Rule::unique('seasons', 'season')->where(fn($q) =>
            $q->where('id', '!=', $season->id)->where('hemisphere', $request->hemisphere))],
            'hemisphere' => $request->hemisphere != $hemisphere ? [Rule::unique('seasons', 'hemisphere')] : '',
            ['in:north,south'],
            'start_date' => 'required|date_format:M d',
            'end_date' => 'required|date_format:M d',
            'season_start_date' => 'required|date_format:Y-m-d',
            'season_end_date' => 'required|date_format:Y-m-d|after:season_start_date',
        ], [
            'season_end_date.after' => 'End date must be greater than start date'
        ]);


        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        $data = $request->except('_token');
        $data['start_date'] = Carbon::parse($request->season_start_date)->format('M d');
        $data['end_date'] = Carbon::parse($request->season_end_date)->format('M d');
        Seasons::where('id', $season->id)->update($data);


        return redirect()->route('admin.season')->with('success', 'Data Saved Successfully');
    }
}
