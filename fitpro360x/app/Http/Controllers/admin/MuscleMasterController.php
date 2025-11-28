<?php

namespace App\Http\Controllers\admin;

use App\Traits\HasSearch;
use App\Http\Controllers\Controller;
use App\Models\MuscleMaster;
use Illuminate\Http\Request;
use App\Traits\Common_trait;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class MuscleMasterController extends Controller
{
    use Common_trait;
    public function index(Request $request)
{
    $limit = $request->input('limit', 10);
    $search = $request->input('search');
    $page = $request->input('page', 1);

    $query = MuscleMaster::query();
    $this->applySearch($query, $search); // Apply search logic if needed

    $total = $query->count();
    $maxPage = ceil($total / $limit);

    // Redirect to named route if current page is invalid
    if ($page > 1 && $page > $maxPage && $total > 0) {
        return redirect()->route('admin.muscleIndex', ['limit' => $limit]);
    }

    $muscleMasters = $query->orderBy('id', 'desc')->paginate($limit);

    if ($search) {
        $muscleMasters->appends(['search' => $search]);
    }

    $muscleMasters->appends(['limit' => $limit]);

    return view('admin.muscle-master.index', compact('muscleMasters'));
}


    public function muscleSave(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique(config('tables.muscle_trained'))->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
        ]);

        MuscleMaster::create([
            'name' => Str::title(preg_replace('/\s+/', ' ', trim($request->name))),
        ]);


        return response()->json(['success' => true]);
    }

    public function muscleEdit($id)
    {
        $muscle = MuscleMaster::findOrFail($id);
        return response()->json($muscle);
    }

    public function muscleUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique(config('tables.muscle_trained'))
                    ->ignore($id) // ✅ Ignore current record
                    ->where(function ($query) {
                        return $query->whereNull('deleted_at'); // ✅ Handle soft deletes
                    }),
            ],
        ]);

        $muscle = MuscleMaster::findOrFail($id);
        $muscle->update([
            'name' => Str::title(preg_replace('/\s+/', ' ', trim($request->name))),
        ]);

        return response()->json(['success' => true]);
    }

    public function muscleDelete($id)
    {
        $muscle = MuscleMaster::findOrFail($id);
        $muscle->delete();

        return response()->json(['success' => true]);
    }
}
