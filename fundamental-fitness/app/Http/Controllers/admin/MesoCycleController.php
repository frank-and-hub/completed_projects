<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\{MesoCycle};
use Illuminate\Http\Request;

class MesoCycleController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);

        $query = MesoCycle::with('workout_frequency');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('workout_frequency')) {
            $query->where('workout_frequency_id', $request->workout_frequency);
        }

        if ($request->filled('weeks')) {
            $query->where('week_number', $request->weeks);
        }

        $allMeso = $query->orderBy('id', 'desc')->paginate($limit);

        $frequencies = all_frequencies_data();

        return view('admin.mesho-cycle.index', compact('allMeso', 'frequencies'));
    }

    public function mesoCycleSave(Request $request)
    {
        $validated = $request->validate([
            'meso_title' => 'required|string|max:50',
            'coach_notes' => 'nullable|string|max:255',
            'week' => 'required|integer',
        ]);

        $meso = new MesoCycle();
        $meso->name = $validated['meso_title'];
        $meso->notes = $validated['coach_notes'] ?? null;
        // $meso->workout_frequency_id = $validated['workout_frequency'];
        $meso->week_number = $validated['week'];
        $meso->save();

        return response()->json([
            'success' => true,
            'message' => 'Meso saved successfully',
            'data' => $meso
        ]);
    }

    public function mesoCycleEdit($id)
    {
        $meso = MesoCycle::findOrFail($id);
        return response()->json($meso);
    }

    public function mesoCycleUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'meso_title' => 'required|string|max:50',
            'coach_notes' => 'nullable|string|max:255',
            // 'workout_frequency' => 'required|integer',
            'week' => 'required|integer',
        ]);

        $meso = MesoCycle::findOrFail($id);
        $meso->name = $validated['meso_title'];
        $meso->notes = $validated['coach_notes'] ?? null;
        // $meso->workout_frequency_id = $validated['workout_frequency'];
        $meso->week_number = $validated['week'];
        $meso->save();

        return response()->json([
            'success' => true,
            'message' => 'Meso updated successfully',
            'data' => $meso
        ]);
    }

    public function mesoCycleDelete($id)
    {
        $meso = MesoCycle::findOrFail($id);
        $meso->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meso deleted successfully'
        ]);
    }
}
