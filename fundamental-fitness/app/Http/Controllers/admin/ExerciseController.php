<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\{Workout, WorkoutSet, Exercise, MesoCycle};
use Illuminate\Http\Request;
use App\Traits\Common_trait;
use Illuminate\Support\Facades\DB;

class ExerciseController extends Controller
{
    use Common_trait;

    public function index(Request $request)
    {
        $query = Workout::with(['exercise', 'meso', 'sets'])
            ->orderByRaw("
                CASE WHEN meso_id LIKE 'Meso %' THEN
                    CAST(SUBSTRING(meso_id, 6) AS UNSIGNED)
                ELSE meso_id END
            ")
            ->orderBy('week_id')
            ->orderBy('day_id');

        // Filters
        if ($request->filled('search')) {
            $query->whereHas('exercise', function ($q) use ($request) {
                $q->whereAny(['name'], 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('frequency')) {
            $query->whereHas('workout_frequency', function ($q) use ($request) {
                $q->where('workout_frequency_id', $request->frequency);
            });
        }

        if ($request->filled('meso')) {
            $query->where('meso_id', $request->meso);
        }

        if ($request->filled('week')) {
            $query->where('week_id', $request->week);
        }

        if ($request->filled('day')) {
            $query->where('day_id', $request->day);
        }

        $limit = $request->get('limit', 10);
        $exercises = $query->paginate($limit)->withQueryString();

        $mesos = get_meso_cycle();
        $frequencies = all_frequencies_data();
        // session(['exercise_list_url' => url()->full()]);
        session(['exercise_list_url' => request()->fullUrl()]);
        return view('admin.exercise.index', compact('exercises', 'mesos', 'frequencies'));
    }


    public function exerciseAdd()
    {
        $data['exercises'] = Exercise::get();
        $data['mesos'] = MesoCycle::get();
        $data['workoutFrequencies'] = all_frequencies_data();
        return view('admin.exercise.add', $data);
    }

    public function checkExistingData(Request $request)
    {
        $workoutFrequencyId = $request->input('workout_frequency_id');
        $mesoId = $request->input('meso_id');

        $usedWeeks = Workout::where('workout_frequency_id', $workoutFrequencyId)
            ->where('meso_id', $mesoId)
            ->distinct()
            ->pluck('week_id')
            ->toArray();

        return response()->json([
            'success' => true,
            'usedWeeks' => $usedWeeks
        ]);
    }

    public function getMesoWeeks($id)
    {
        $weeks = MesoCycle::where('id', $id)->pluck('week_number');
        return response()->json($weeks);
    }

    public function exerciseSave(Request $request)
    {
        try {
            DB::beginTransaction();

            $workoutFrequencyId = $request->input('workout_frequency');
            $mesoId = $request->input('meso');
            $week   = $request->input('week');
            $days   = $request->input('days', []);

            foreach ($days as $dayNumber => $dayData) {
                // Decode only meta
                $meta = isset($dayData['meta']) ? json_decode($dayData['meta'], true) : [];

                if (empty($meta['exercises'])) {
                    continue;
                }

                foreach ($meta['exercises'] as $exerciseIndex => $exerciseData) {
                    // Files (if uploaded)
                    $imagePath = null;
                    $gifPath   = null;
                    $videoPath = null;

                    if ($request->hasFile("days.$dayNumber.exercises.$exerciseIndex.image")) {
                        $imagePath = $this->file_upload(
                            $request->file("days.$dayNumber.exercises.$exerciseIndex.image"),
                            'exercises/images'
                        );
                    }

                    if ($request->hasFile("days.$dayNumber.exercises.$exerciseIndex.gif")) {
                        $gifPath = $this->file_upload(
                            $request->file("days.$dayNumber.exercises.$exerciseIndex.gif"),
                            'exercises/gifs'
                        );
                    }

                    if ($request->hasFile("days.$dayNumber.exercises.$exerciseIndex.video")) {
                        $videoPath = $this->file_upload(
                            $request->file("days.$dayNumber.exercises.$exerciseIndex.video"),
                            'exercises/videos'
                        );
                    }

                    // Save workout exercise
                    $workoutExercise = new Workout();
                    $workoutExercise->workout_frequency_id = $workoutFrequencyId;
                    $workoutExercise->meso_id              = $mesoId;
                    $workoutExercise->day_id               = $dayNumber;
                    $workoutExercise->week_id              = $week;
                    $workoutExercise->exercise_id          = $exerciseData['exercise_id'];
                    $workoutExercise->level                = $exerciseData['level'];
                    $workoutExercise->image                = $imagePath;
                    $workoutExercise->video                = $videoPath;
                    $workoutExercise->gif                  = $gifPath;
                    $workoutExercise->description          = $exerciseData['description'] ?? null;
                    $workoutExercise->save();

                    // Save sets
                    foreach ($exerciseData['sets'] as $setData) {
                        $repsUnit = null;
                        if ($exerciseData['exercise_id'] == get_running_id()) {
                            $repsUnit = match ($setData['set_number']) {
                                1 => 'min',
                                2 => 'km',
                                default => null,
                            };
                        }

                        $workoutSet = new WorkoutSet();
                        $workoutSet->workout_id      = $workoutExercise->id;
                        $workoutSet->set_number      = $setData['set_number'];
                        $workoutSet->reps            = $setData['reps'] ?? null;
                        $workoutSet->reps_unit       = $repsUnit;
                        $workoutSet->rpe             = $setData['rpe'] ?? null;
                        $workoutSet->rpe_percentage  = !empty($setData['rpePercentage']) ? $setData['rpePercentage'] : null;
                        $workoutSet->rest            = !empty($setData['rest']) ? $setData['rest'] : null;
                        $workoutSet->rest_unit       = 'seconds';
                        $workoutSet->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Exercises added successfully',
                'redirect_url' => route('admin.exerciseIndex')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error saving exercises: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exerciseEdit($id)
    {
        $workout = Workout::with(['exercise', 'sets'])->findOrFail($id);

        $workoutFrequencies = all_frequencies_data();
        $mesos = get_meso_cycle();
        $exercises = all_exercise_data();

        return view('admin.exercise.edit', compact('workout', 'workoutFrequencies', 'mesos', 'exercises'));
    }


    public function exerciseUpdate(Request $request)
    {

        try {
            DB::beginTransaction();

            $workoutId = $request->input('workout_id');
            $existing = Workout::find($workoutId);

            if (!$existing) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This exercise no longer exists.',
                    'redirect_url' => route('admin.exerciseIndex')
                ]);
            }

            $workoutFrequencyId = $request->input('workout_frequency', $existing->workout_frequency_id);
            $mesoId = $request->input('meso', $existing->meso_id);
            $weekId = $request->input('week', $existing->week_id);
            $dayId = (int) $request->input('day_id', $existing->day_id);

            $exercises = [];
            $days = $request->input('days', []);

            if (!empty($days) && isset($days[$dayId]) && isset($days[$dayId]['meta'])) {
                $meta = json_decode($days[$dayId]['meta'], true) ?: [];
                $exercises = $meta['exercises'] ?? [];
            }

            if (!empty($exercises)) {
                foreach ($exercises as $index => $exerciseData) {
                    $extraData = $days[$dayId]['exercises'][$index] ?? [];
                    $exerciseData = array_merge($exerciseData, $extraData);
                    $workoutExercise = null;

                    if ($index === 0) {
                        $workoutExercise = $existing;
                    } else {
                        $workoutExercise = new Workout();
                        $workoutExercise->workout_frequency_id = $workoutFrequencyId;
                        $workoutExercise->meso_id = $mesoId;
                        $workoutExercise->week_id = $weekId;
                        $workoutExercise->day_id = $dayId;
                    }

                    $workoutExercise->exercise_id = $exerciseData['exercise_id'];
                    $workoutExercise->level = $exerciseData['level'];
                    $workoutExercise->description = $exerciseData['description'] ?? null;

                    // ===== Handle IMAGE =====
                    if (!empty($exerciseData['delete_image']) && $exerciseData['delete_image'] == 1) {
                        if ($workoutExercise->image && file_exists(public_path($workoutExercise->image))) {
                            unlink(public_path($workoutExercise->image));
                        }
                        $workoutExercise->image = null;
                    } elseif ($request->hasFile("days.$dayId.exercises.$index.image")) {
                        if ($workoutExercise->image && file_exists(public_path($workoutExercise->image))) {
                            unlink(public_path($workoutExercise->image));
                        }
                        $imagePath = $this->file_upload(
                            $request->file("days.$dayId.exercises.$index.image"),
                            'exercises/images'
                        );
                        $workoutExercise->image = $imagePath;
                    }

                    // ===== Handle GIF =====
                    if (!empty($exerciseData['delete_gif']) && $exerciseData['delete_gif'] == 1) {
                        if ($workoutExercise->gif && file_exists(public_path($workoutExercise->gif))) {
                            unlink(public_path($workoutExercise->gif));
                        }
                        $workoutExercise->gif = null;
                    } elseif ($request->hasFile("days.$dayId.exercises.$index.gif")) {
                        if ($workoutExercise->gif && file_exists(public_path($workoutExercise->gif))) {
                            unlink(public_path($workoutExercise->gif));
                        }
                        $gifPath = $this->file_upload(
                            $request->file("days.$dayId.exercises.$index.gif"),
                            'exercises/gifs'
                        );
                        $workoutExercise->gif = $gifPath;
                    }

                    // ===== Handle VIDEO =====
                    if (!empty($exerciseData['delete_video']) && $exerciseData['delete_video'] == 1) {
                        if ($workoutExercise->video && file_exists(public_path($workoutExercise->video))) {
                            unlink(public_path($workoutExercise->video));
                        }
                        $workoutExercise->video = null;
                    } elseif ($request->hasFile("days.$dayId.exercises.$index.video")) {
                        if ($workoutExercise->video && file_exists(public_path($workoutExercise->video))) {
                            unlink(public_path($workoutExercise->video));
                        }
                        $videoPath = $this->file_upload(
                            $request->file("days.$dayId.exercises.$index.video"),
                            'exercises/videos'
                        );
                        $workoutExercise->video = $videoPath;
                    }


                    $workoutExercise->save();

                    // ===== Handle Sets =====
                    $payloadSetIds = collect($exerciseData['sets'] ?? [])->pluck('id')->filter()->toArray();

                    WorkoutSet::where('workout_id', $workoutExercise->id)
                        ->whereNotIn('id', $payloadSetIds)
                        ->delete();

                    foreach ($exerciseData['sets'] ?? [] as $setIndex => $setData) {
                        if (!empty($setData['id'])) {
                            $workoutSet = WorkoutSet::where('workout_id', $workoutExercise->id)
                                ->where('id', $setData['id'])
                                ->first();

                            if (!$workoutSet) {
                                $workoutSet = new WorkoutSet();
                                $workoutSet->workout_id = $workoutExercise->id;
                            }
                        } else {
                            $workoutSet = new WorkoutSet();
                            $workoutSet->workout_id = $workoutExercise->id;
                        }

                        if (isset($setData['running_type'])) {
                            $workoutSet->set_number = $setIndex + 1;
                            $workoutSet->reps = $setData['reps'] ?? ($setData['running_value'] ?? null);
                            $workoutSet->reps_unit = ($setData['running_type'] ?? 1) == 1 ? 'min' : 'km';
                            $workoutSet->rest = $setData['rest'] ?? ($setData['walk'] ?? '30');
                            $workoutSet->rest_unit = 'seconds';
                        } else {
                            $workoutSet->set_number = $setData['set_number'] ?? ($setIndex + 1);
                            $workoutSet->reps = $setData['reps'] ?? null;
                            $workoutSet->rest = $setData['rest'] ?? '30';
                            $workoutSet->rest_unit = 'seconds';
                        }

                        $workoutSet->rpe = $setData['rpe'] ?? 1;
                        $workoutSet->rpe_percentage =
                            ($setData['rpe'] == 0)
                            ? ($setData['rpe_percentage'] ?? $setData['rpePercentage'] ?? null)
                            : null;

                        $workoutSet->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Exercise updated successfully',
                'redirect_url' => session('exercise_list_url', route('admin.exerciseIndex'))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating exercise: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exerciseDelete(Request $request, $id)
    {
        try {
            $workout = Workout::with('sets')->findOrFail($id);
            $workout->sets()->delete();
            $workout->delete();

            $currentPage = (int) $request->get('page', 1);

            $limit = 20;
            $total = Workout::count();

            $maxPage = (int) ceil($total / $limit);
            $redirectPage = $currentPage > $maxPage ? $maxPage : $currentPage;

            if ($redirectPage < 1) {
                $redirectPage = 1;
            }

            return response()->json([
                'success' => true,
                'message' => 'Exercise deleted successfully',
                'redirect_url' => $redirectPage > 1
                    ? url('admin/exercises?page=' . $redirectPage)
                    : url('admin/exercises'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!'
            ], 500);
        }
    }
}
