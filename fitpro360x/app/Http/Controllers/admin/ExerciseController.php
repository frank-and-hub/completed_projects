<?php

namespace App\Http\Controllers\admin;

use App\Models\ExerciseMuscleTrained;
use App\Traits\HasSearch;
use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\BodyType;
use App\Models\MuscleMaster;
use App\Models\Workout_Program_Exercises;
use Illuminate\Http\Request;
use App\Traits\Common_trait;
use Illuminate\Support\Facades\File; // Add at top if not already
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;


class ExerciseController extends Controller
{
    use Common_trait;

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $level = $request->input('level');
        $muscleId = $request->input('muscle_id');
        $page = $request->input('page', 1);

        $query = Exercise::query()->with(['bodyType', 'muscle_trained']);

        // Apply search (title or description)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('exercise_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('equipment', 'LIKE', '%' . $search . '%');
            });
        }
        // Apply level filter
        if ($level) {
            $query->where('level', $level);
        }

        // Apply muscle filter (if one-to-many)
        if ($muscleId) {
            $query->whereHas('muscle_trained', function ($q) use ($muscleId) {
                $q->where('muscle_trained_id', $muscleId); // matches pivot column
            });
        }
        $total = $query->count();
        $maxPage = ceil($total / $limit);

        // Prevent showing empty pages
        if ($page > 1 && $page > $maxPage && $total > 0) {
            return redirect()->route('admin.exerciseIndex', ['limit' => $limit]);
        }

        $exercises = $query->orderBy('id', 'desc')->paginate($limit);

        // Append parameters to pagination
        $exercises->appends([
            'search' => $search,
            'level' => $level,
            'muscle_id' => $muscleId,
            'limit' => $limit,
        ]);

        return view('admin.exercise.index', compact('exercises'));
    }


    public function exerciseAdd()
    {
        $bodyTypes = BodyType::whereNull('deleted_at')->pluck('name', 'id');
        $muscles = MuscleMaster::whereNull('deleted_at')->pluck('name', 'id');

        return view('admin.exercise.add', compact('bodyTypes', 'muscles'));
    }

    public function exerciseSave(Request $request)
    {
        $request->validate([
            'exercise_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique(config('tables.exercises'))->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'level' => 'required|in:1,2,3',
            'location' => 'required|in:1,2',
            'body_type_id' => 'required|exists:ft_ms_body_types,id',
            'muscle_id' => 'required|array',
            'muscle_id.*' => 'exists:ft_ms_muscle_trained,id',
            'equipment' => 'required|string|max:50',
            'description' => 'required|string|max:1200',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            //'video' => 'required|file|mimes:mp4,webm,ogv|max:20480'
            'video' => 'required|file|max:20480'
        ]);

        $imagePath = null;
        $videoPath = null;

        if ($request->hasFile('image')) {
            $imagePath = $this->file_upload($request->file('image'), 'exercise/images');
        }


        if ($request->hasFile('video')) {
            $videoPath = $this->file_upload($request->file('video'), 'exercise/videos');
        }

        $exercise = Exercise::create([
            'exercise_name' => Str::title(preg_replace('/\s+/', ' ', trim($request->exercise_name))),
            'level' => $request->level,
            'location' => $request->location,
            'body_type_id' => $request->body_type_id,
            // 'muscles_trained_id' => $request->muscle_id,
            'equipment' => Str::title(preg_replace('/\s+/', ' ', trim($request->equipment))),
            'image' => $imagePath,
            'video' => $videoPath,
            'description' => $request->description
        ]);

        // Attach selected muscles
        foreach ($request->muscle_id as $muscleId) {
            $relation = new ExerciseMuscleTrained();
            $relation->exercise_id = $exercise->id;
            $relation->muscle_trained_id = $muscleId;
            $relation->save();
        }


        return redirect()->route('admin.exerciseIndex')->with('toastr', [
            'type' => 'success',
            'message' => 'A new exercise added successfully!'
        ]);
    }

    public function exerciseEdit($id)
    {
        $exercise = Exercise::with('muscle_trained')->findOrFail($id);
        $bodyTypes = BodyType::pluck('name', 'id');
        $muscles = MuscleMaster::pluck('name', 'id');

        return view('admin.exercise.edit', compact('exercise', 'bodyTypes', 'muscles'));
    }

    public function exerciseUpdate(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'exercise_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique(config('tables.exercises'))->ignore($id) // Ignore the current record if updating
                    ->where(function ($query) {
                        return $query->whereNull('deleted_at'); // Handle soft deletes
                    }),
            ],
            'level' => 'required|in:1,2,3',
            'location' => 'required|in:1,2',
            'body_type_id' => 'required|exists:ft_ms_body_types,id',
            // 'muscles_trained_id' => 'required|exists:ft_ms_muscle_trained,id',
            'muscle_id' => 'required|array',
            'muscle_id.*' => 'exists:ft_ms_muscle_trained,id',
            'equipment' => 'required|string',
            'image' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            //'video' => 'file|mimes:mp4,webm,ogv|max:20480',
            'video' => 'file|max:20480',
            'description' => 'required|string|max:1200'
        ]);

        // Find the exercise to update
        // $exercise = Exercise::findOrFail($id);
        $exercise = Exercise::with('muscle_trained')->findOrFail($id);


        // Handle image upload
        $imagePath = $exercise->image; // Keep existing image if not updated
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($exercise->image && file_exists(public_path($exercise->image))) {
                unlink(public_path($exercise->image));
            }
            $imagePath = $this->file_upload($request->file('image'), 'exercise/images');
        }

        // Handle video upload
        $videoPath = $exercise->video; // Keep existing video if not updated
        if ($request->hasFile('video')) {
            // Delete old video if exists
            if ($exercise->video && file_exists(public_path($exercise->video))) {
                unlink(public_path($exercise->video));
            }
            $videoPath = $this->file_upload($request->file('video'), 'exercise/videos');
        }

        // Update the exercise
        $exercise->update([
            'exercise_name' => Str::title(preg_replace('/\s+/', ' ', trim($request->exercise_name))),
            'level' => $request->level,
            'location' => $request->location,
            'body_type_id' => $request->body_type_id,
            'muscles_trained_id' => $request->muscles_trained_id,
            'equipment' => Str::title(preg_replace('/\s+/', ' ', trim($request->equipment))),
            'image' => $imagePath,
            'video' => $videoPath,
            'description' => $request->description,
        ]);

        // Sync muscles trained (delete removed, add new)
        $exercise->muscle_trained()->sync($request->muscle_id);

        return redirect()->route('admin.exerciseIndex')->with('toastr', [
            'type' => 'success',
            'message' => 'Exercise updated successfully!'
        ]);
    }


    public function exerciseDelete($id)
    {
        try {
            $exercise = Exercise::findOrFail($id);
            $exercise->delete();

            return response()->json([
                'success' => true,
                'message' => 'Exercise deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting exercise: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkExerciseStatus($id = null)
    {
        if (!$id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No Exercise ID provided'
            ], 400);
        }

        $exercise = Exercise::find($id);

        if (!$exercise) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exercise not found'
            ], 404);
        }

        // Check if exercise is used in any workout program exercises
        $isUsedInWorkoutProgram = Workout_Program_Exercises::where('exercise_id', $id)->exists();

        if ($isUsedInWorkoutProgram) {
            return response()->json([
                'status' => 'error',
                'can_delete' => false,
                'message' => 'It is currently being used in one or more workout programs on App. To remove this exercise, please delete the associated workout(s) first.',
                'active' => $exercise->is_active ?? true
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'can_delete' => true,
            'active' => $exercise->is_active ?? true
        ]);
    }
}
