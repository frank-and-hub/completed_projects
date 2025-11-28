<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\{UserProgressResource, UserWorkoutResource, WorkoutLogResource, MesoCycleResource, SimpleExerciseResource, SimpleWeekResource};
use App\Models\{Exercise, MesoCycle, UserWorkout, WorkoutSet, Week, UserProgress, User};
use App\Services\NotificationService;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class WorkOutController extends Controller
{
    use Common_trait;

    public function show(Request $req)
    {
        $cUser = $req->user();
        $mId = $req->meso_id;
        $wId = $req->week_id;
        $dId  = $req->day_id;
        $wfId = $cUser->workout_frequency;
        $query = $cUser->user_workouts()->whereWorkoutFrequencyId($wfId)->with(['sets', 'workout_frequency', 'exercise'])->withCount('exercise')->when($mId, fn($q) => $q->where('meso_id', $mId))->when($wId, fn($q) => $q->where('week_id', $wId))->when($dId, fn($q) => $q->where('day_id', $dId));
        $rs = $query->orderBy('meso_id')->orderBy('week_id')->orderBy('day_id')->get();
        return ApiResponse::success(new UserWorkoutResource($rs));
    }

    public function updateWorkoutProcess(Request $req)
    {
        $mId = $req->meso_id;
        $wId = $req->week_id;
        $dId  = $req->day_id;
        $eid  = $req->exercise_id;

        $v = Validator::make($req->all(), [
            'meso_id'  => 'required|exiests:' . MesoCycle::class . ',id',
            'week_id'  => 'required|exiests:' . Week::class . ',id',
            'day_id'   => 'required|in:1,2,3,4,5,6',
            'exercise_id'   => 'required|exiests:' . Exercise::class . ',id',
            'sets'   => 'required|array',
            'sets.*.id'   => 'required|integer|exists:' . WorkoutSet::class . ',id',
            'sets.*.weight' => 'required|array',
            'sets.*.weight.*' => 'numeric|min:1',
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'data'    => false,
                'message' => $v->errors()->first(),
            ], 422);
        }

        $cUser = $req->user();

        foreach ($req->sets as $set) {
            UserProgress::where('user_id', $cUser->id)
                ->where('week_id', $wId)
                ->where('meso_id', $mId)
                ->where('day_id', $dId)
                ->where('exercise_id', $eid)
                ->where('set_id', $set['id'])
                ->update([
                    'weight'  => json_encode($set['weight']),
                    'status' => 2,
                    'completed_at' => now()
                ]);
        }
    }

    public function markCompleted_new(Request $req)
    {
        $v = Validator::make($req->all(), [
            'meso_id'     => 'required|exists:' . MesoCycle::class . ',id',
            'week_id'     => 'required|exists:' . Week::class . ',id',
            'day_id'      => 'required|in:1,2,3,4',
            'exercise_id' => 'required|exists:' . Exercise::class . ',id',
        ]);
        if ($v->fails()) {
            return ApiResponse::error($v->errors()->first(), 422);
        }
        $cUser = $req->user();
        $user  = User::findOrFail($cUser->id);
        $with = ['exercise', 'sets'];
        if ($this->is_eligible_for_reset($user)) {
            return ApiResponse::error(__('messages.restart_meso'), 422);
        }
        $where = [
            'user_id'     => $cUser->id,
            'meso_id'     => (int) $req->meso_id,
            'week_id'     => (int) $req->week_id,
            'day_id'      => (int) $req->day_id,
            'exercise_id' => (int) $req->exercise_id,
        ];
        $sets = UserProgress::where(array_merge($where, ['status' => 0]))->orderBy('id')->get();
        $nextSet = null;
        if ($sets->isEmpty()) {
            $this->setReset($req);
            $data = UserProgress::with($with)->where(array_merge($where, ['status' => 0]))->orderBy('id')->first();
            return ApiResponse::success(new UserProgressResource($data));
        }
        foreach ($sets as $set) {
            $weightArray = is_array($set->weight) ? $set->weight : json_decode($set->weight, true);
            $weightCount = count($weightArray);
            if ($set->processed_count < $weightCount) {
                $set->processed_count += 1;
                if ($set->processed_count >= $weightCount) {
                    $set->status = 1;
                }
                $set->save();
                $nextSet = $set;
                break;
            }
        }
        if (!$nextSet) {
            $nextSet = $sets->last();
        }
        $nextSet?->load($with);
        return ApiResponse::success(new UserProgressResource($nextSet));
    }

    public function markCompleted(Request $req)
    {
        $v = Validator::make($req->all(), [
            'meso_id' => 'required|exists:' . MesoCycle::class . ',id',
            'week_id' => 'required|exists:' . Week::class . ',id',
            'day_id' => 'required|in:1,2,3,4',
            'exercise_id' => 'required|exists:' . Exercise::class . ',id',
            'set_id' => 'required|exists:' . UserProgress::class . ',id',
        ]);
        if ($v->fails()) {
            return ApiResponse::error($v->errors()->first(), 422);
        }
        $cUser = $req->user();
        $user = User::findorfail($cUser->id);
        if ($this->is_eligible_for_reset($user)) {
            return ApiResponse::error(__('messages.restart_meso'), 422);
        }
        $with = ['exercise', 'sets'];
        $where = ['user_id' => $cUser->id, 'meso_id' => (int) $req->meso_id, 'week_id' => (int) $req->week_id, 'day_id' => (int) $req->day_id, 'exercise_id' => (int) $req->exercise_id];
        // UserProgress::where(array_merge($where, ['id' => $req->set_id]))->update(['status'  => 1]);
        $r = UserProgress::with($with)->where(array_merge($where, ['status' => 0]))->orderBy('set_id')->first();
        if (!$r) {
            $r = UserProgress::with($with)->where($where)->orderBy('set_id')->first();
        }
        return ApiResponse::success(new UserProgressResource($r));
    }

    public function getExercise(Request $req)
    {
        $v = Validator::make($req->all(), [
            'meso_id' => 'required|exists:' . MesoCycle::class . ',id',
            'week_id' => 'required|exists:' . Week::class . ',id',
            'day_id' => 'required|in:1,2,3,4',
            'exercise_id' => 'required|exists:' . Exercise::class . ',id',
        ]);
        if ($v->fails()) {
            return ApiResponse::error($v->errors()->first(), 422);
        }
        $cUser = $req->user();
        $mId = $v['meso_id'];
        $wId = $v['week_id'];
        $dId = $v['day_id'];
        $eid = $v['exercise_id'];
        $eSet = UserProgress::with(['exercise', 'sets'])
            ->where('user_id', $cUser->id)
            ->where('meso_id', $mId)
            ->where('week_id', $wId)
            ->where('day_id', $dId)
            ->where('exercise_id', $eid)
            ->orderBy('set_id')
            ->get();
        if ($eSet->isEmpty()) {
            return ApiResponse::notFound('No sets found for this exercise.');
        }
        // $completedCount = $eSet->whereIn('status', [1, 2])->count();
        // if ($completedCount === $eSet->count()) {
        //     $eSet->each->update(['status' => 0]);
        // }
        return ApiResponse::success(UserProgressResource::collection($eSet));
    }

    public function getCompletedExerciseSetId(Request $req)
    {
        $mId = $req->meso_id;
        $wId = $req->week_id;
        $dId  = $req->day_id;
        $eid  = $req->exercise_id;
        $cUser = $req->user();
        $user = User::findorfail($cUser->id);
        if ($this->is_eligible_for_reset($user)) {
            return ApiResponse::error(__('messages.restart_meso'), 422);
        }
      
        $completedSets = UserProgress::where('user_id', $cUser->id)
            ->with(['exercise', 'sets'])
            ->where('week_id', $wId)
            ->where('meso_id', $mId)
            ->where('day_id', $dId)
            ->where('exercise_id', $eid)
            // ->whereIn('status', [0, 1])
            ->get();

        if ($completedSets->isEmpty()) {
            return ApiResponse::error('No completed exercise sets found.', 404);
        }

        $grouped = $completedSets->groupBy(function ($item) {
            return $item->exercise ? $item->exercise->name : 'Unknown Exercise';
        });

        $formatted = $grouped->map(function ($sets, $exerciseName) use($cUser) {
            return [
                'week_status' => $this->checkWeekStatus($sets[0], $cUser),
                'exercise_name' => $exerciseName,
                'sets' => $sets->map(function ($set) {
                    return [
                        'set_id' => $set->set_id,
                        'rpe'    => $set->rpe,
                        'weight' => json_decode($set->weight),
                        'set_number' => $set->exercise_id == get_running_id() ? 1 : $set->set_number,
                    ];
                })->values()
            ];
        })->first();

        return ApiResponse::success($formatted);
    }

    private function checkWeekStatus($sets, $cUser){
        $activePointer = get_active_pointer($cUser->id, $cUser->meso_start_date);
        $umId = $activePointer['meso_id'];
        $uwId = $activePointer['week_id'];
        $udId = $activePointer['day_id'];

        $weeksStatus = false;

        // Previous meso → true
        if ($sets->meso_id < $umId) {
            $weeksStatus = true;
        }
        // Same meso but previous week → true
        elseif ($sets->meso_id == $umId && $sets->week_id < $uwId) {
            $weeksStatus = true;
        }
        // Same meso & week but previous day → true
        elseif ($sets->meso_id == $umId && $sets->week_id == $uwId && $sets->day_id < $udId) {
            $weeksStatus = true;
        }
        // Current meso + current week + current day → false
        else {
            $weeksStatus = false;
        }

        return $weeksStatus;
    }

    public function updateSetsWeight(Request $req)
    {
        $req->validate([
            'meso_id' => 'required|exists:' . MesoCycle::class . ',id',
            'week_id' => 'required|exists:' . Week::class . ',id',
            'day_id' => 'required|in:1,2,3,4',
            'exercise_id' => 'required|exists:' . Exercise::class . ',id',
            'sets' => 'required|array',
            'sets.*.set_id' => 'required|integer|exists:' . WorkoutSet::class . ',id',
            'sets.*.weight' => 'required|array',
            'sets.*.weight.*' => 'numeric|min:0',
        ]);
        $cUser = $req->user();
        $user = User::findorfail($cUser->id);
        if ($this->is_eligible_for_reset($user)) {
            return ApiResponse::error(__('messages.restart_meso'), 422);
        }
        $updatedSets = [];
        $hitNewPersonalBest = false;
        $startDate = Carbon::now();
        $notificationService  = app(NotificationService::class);

        foreach ($req->sets as $item) {
            $newWeights = $item['weight'];
            $maxNewWeight = max($newWeights);

            $previousMaxWeight = UserProgress::where('user_id', $cUser->id)
                // ->where('meso_id', $req->meso_id)
                // ->where('week_id', $req->week_id)
                ->where('exercise_id', $req->exercise_id)
                ->where('status', 2)
                ->whereNotNull('weight')
                ->get()
                ->flatMap(function ($progress) {
                    $weights = json_decode($progress->weight, true);
                    return is_array($weights) ? $weights : [(float)$progress->weight];
                })
                ->max();

            $set = UserProgress::whereUserId($cUser->id)
                ->where('meso_id', $req->meso_id)
                ->where('week_id', $req->week_id)
                ->where('day_id', $req->day_id)
                ->where('exercise_id', $req->exercise_id)
                ->where('set_id', $item['set_id'])
                ->first();

            if ($set) {
                $set->update([
                    'status' => '2',
                    'weight' => json_encode($item['weight']),
                    'completed_at' => now()
                ]);
                $updatedSets[] = [
                    'status' => '2',
                    'set_id' => $set->set_id,
                    'weight' => json_decode($set->weight),
                    'completed_at' => now()
                ];

                if ($previousMaxWeight && $maxNewWeight != 0 && $maxNewWeight > $previousMaxWeight) {
                    $hitNewPersonalBest = true;
                }
            }
        }

        $workouts = UserWorkout::where('meso_id', $req->meso_id)
            ->where('week_id', $req->week_id)
            ->whereNull('execution_date')
            ->orderBy('day_id')
            ->get();

        $grouped = $workouts->groupBy('day_id');

        foreach ($grouped as $dId => $dayWorkouts) {
            $executionDate = $startDate->copy()->addDays($dId - 1);
            foreach ($dayWorkouts as $workout) {
                $workout->execution_date = $executionDate;
                $workout->save();
            }
        }

        if (!$cUser->meso_start_date) {
            $cUser->update([
                'meso_start_date' =>  $startDate
            ]);
        }

        if (empty($updatedSets)) {
            return ApiResponse::error(__('messages.something_went_wrong'));
        }

        if ($hitNewPersonalBest) {
            $notificationService->sendDailyMotivationalBoost($cUser->id);
        }

        return ApiResponse::success($updatedSets, 'Weights updated successfully.');
    }

    public function videoCount(Request $req)
    {
        $cUser = $req->user();

        $req->validate([
            'exercise_id' => 'required|exists:' . Exercise::class . ',id'
        ]);

        $eid = $req->exercise_id;
        $existing = $cUser->watchedExercises()->where('exercise_id', $eid)->first();

        if ($existing) {
            $cUser->watchedExercises()->updateExistingPivot(
                $eid,
                ['video_count' => $existing->pivot->video_count + 1]
            );
        } else {
            $cUser->watchedExercises()->attach($eid, ['video_count' => 1]);
        }

        return ApiResponse::success([
            'video_count' => $cUser->watchedExercises()->where('exercise_id', $eid)->first()->pivot->video_count
        ]);
    }

    public function workoutLogs(Request $req)
    {
        $cUser = $req->user();
        $today = Carbon::now()->format('Y-m-d');

        $mId = $req->meso_id;
        $wId = $req->week_id;
        $dId = $req->day_id;
        $eid = $req->exercise_id;

        if (empty($mId) && empty($wId) && empty($dId)) {
            $activePointer = get_active_pointer($cUser->id, $cUser->meso_start_date);
            $mId = $activePointer['meso_id'] ?? null;
            $wId = $activePointer['week_id'] ?? null;
            $dId = $activePointer['day_id'] ?? null;
        }

        $logs = UserWorkout::with([
            'exercise',
            'sets.sets'
        ])
            ->whereHas('sets', function ($q) {
                $q->where('status', '!=', 0);
            })
            ->whereUserId($cUser->id)
            ->whereNotNull('execution_date')
            ->whereDate('execution_date', '<=', $today)
            ->when(!is_null($mId) && $mId > 0, fn($q) => $q->where('meso_id',  $mId))
            ->when(!is_null($wId) && $wId > 0, fn($q) => $q->where('week_id', $wId))
            ->when(!is_null($dId) && $dId > 0, fn($q) => $q->where('day_id', $dId))
            ->when(!is_null($eid) && $eid > 0, fn($q) => $q->where('exercise_id', $eid))
            ->orderBy('meso_id')
            ->orderBy('week_id')
            ->orderBy('day_id')
            ->orderBy('exercise_id')
            ->get();

        if ($logs->isEmpty()) {
            return ApiResponse::notFound('No workout logs found. Please adjust your filters to view logged workouts.');
        }

        $grouped = WorkoutLogResource::collection($logs)
            ->collection
            ->groupBy('day_id')
            ->map(function ($dayLogs, $dId) {
                return [
                    'day_id' => (int) $dId,
                    'meso_id' => (int) optional($dayLogs->first())['meso_id'],
                    'week_id' => (int) optional($dayLogs->first())['week_id'],
                    'exercises' => $dayLogs->values(),
                ];
            })
            ->values();

        return ApiResponse::success($grouped);
    }

    public function logFilterData(Request $req)
    {
        $cUser = $req->user();
        $format = fn($item) => [
            'id' => is_array($item) ? $item['id'] : $item->id,
            'name' => is_array($item) ? $item['name'] : $item->name,
        ];
        $allMesos = MesoCycleResource::collection(
            collect(get_meso_cycle())->map($format)
        );
        $allWeeks = SimpleWeekResource::collection(collect(get_weeks())->map($format));
        $allDays = collect(range(1, 6))->map(function ($day) {
            return [
                'id' => $day,
                'name' => "Day {$day}",
            ];
        });
        $allExercises = SimpleExerciseResource::collection(
            collect(all_exercise_data())->map($format)
        );
        return ApiResponse::success([
            'mesos' => $allMesos,
            'weeks' => $allWeeks,
            'days' => $allDays,
            'exercises' => [[
                'id' => 0,
                "name" => "All Exercise"
            ], ...$allExercises],
        ]);
    }

    public function setReset(Request $req)
    {
        $v = $req->validate([
            'meso_id'     => 'required|exists:' . MesoCycle::class . ',id',
            'week_id'     => 'required|exists:' . Week::class . ',id',
            'day_id'      => 'required|in:1,2,3,4',
            'exercise_id' => 'required|exists:' . Exercise::class . ',id',
            'set_id' => 'required',
        ]);

        $cUser = $req->user();
        $mId = $v['meso_id'];
        $wId = $v['week_id'];
        $dId = $v['day_id'];
        $eid = $v['exercise_id'];
        $sId = $v['set_id'];

        UserProgress::whereUserId($cUser->id)
            ->whereId($sId)
            ->where('meso_id', $mId)
            ->where('week_id', $wId)
            ->where('day_id', $dId)
            ->where('exercise_id', $eid)
            ->update([
                'status' => 0,
                'completed_at' => null
            ]);

        return ApiResponse::success([], 'reset workout updated successfully.', 200);
    }
}
