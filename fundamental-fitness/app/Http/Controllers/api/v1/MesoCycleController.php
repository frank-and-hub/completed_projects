<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Resources\UserWorkoutResource;
use App\Models\{Workout, MesoCycle, UserProgress, Exercise};
use App\Models\UserWorkout;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MesoCycleController extends BaseApiController
{
    use Common_trait;

    public function index(Request $req)
    {
        $cUser = $req->user();
        $mId = $req->input('meso_id');
        $wId = $req->input('week_id');
        $dId  = $req->input('day_id');
        // base query with relations
        $withRelations = ['sets.sets', 'workout_frequency:id,name,days_in_week', 'exercise:id,name,status'];
        // user assigned workout query
        $query = UserWorkout::whereUserId($cUser->id)->with($withRelations)->withCount('exercise');
        $adminQuery = Workout::query();
        // apply filters for meso_id, week_id, day_id
        if ($mId) {
            $query->where('meso_id', $mId);
            $adminQuery->where('meso_id', '!=', $mId);
        }
        if ($wId) {
            $query->where('week_id', $wId);
            $adminQuery->where('week_id', '!=', $wId);
        }
        if ($dId) {
            $query->where('day_id', $dId);
            $adminQuery->where('day_id', '!=', $dId);
        }
        $records = $query->orderBy('meso_id')->orderBy('week_id')->orderBy('day_id')->get();
        // get data for all exercised
        if ($dId) {
            return ApiResponse::success(UserWorkoutResource::collection($records));
        }
        // get data for week daya
        if ($wId) {
            $days = $records->groupBy('day_id')->sortKeys()->map(function ($dayGroup, $dId) use ($cUser, $wId, $mId) {
                $get_workout_unlock_status = $this->get_workout_unlock_status($cUser, $mId, $wId, $dId);
                return ['id' => (int) $dId, 'is_completed' => is_week_completed($cUser->id,    $mId,    $wId), 'status' => $get_workout_unlock_status['day_unlocked'], 'exercise_count' => $dayGroup->sum('exercise_count'),];
            })->values();
            return ApiResponse::success(['week_id' => (int) $wId, 'days'    => $days,]);
        }
        // $catchData = Cache::get($cUser->id . '_meso_data');
        // if ($catchData) {
        //     return ApiResponse::success($catchData);
        // }
        // get all data if there is not meso_id, week_id, day_id
        $mNames = get_meso_cycle()->whereIn('id', $records->pluck('meso_id'))->pluck('name', 'id');
        $wNames = get_weeks()->whereIn('id', $records->pluck('week_id'))->pluck('name', 'id');
        $weeksCount = get_weeks()->count();
        // this is a thrid funtion for calcualation user locked and unlocked weeks
        $grouped = $this->calculateGroupedData($records, $mNames, $wNames, $cUser, $weeksCount);
        
        $userMesoIds = $grouped->pluck('meso_id')->toArray();
        // get user meso ids to filter admin meso cycles
        $adminMesoIds = $adminQuery->pluck('meso_id')->unique()->diff($userMesoIds);
        $adminGrouped = $adminMesoIds->sort()->values()->map(function ($mId) {
            $mesoName = MesoCycle::find($mId)?->name ?? "Meso $mId";
            return [
                'meso_id' => (int) $mId,
                'name' => $mesoName,
                'is_completed' => false,
                'status' => false,
                'weeks_count' => 0,
                'weeks' => []
            ];
        });
        $mergedData = array_merge($grouped->toArray(), $adminGrouped->toArray());
        // Cache::put($cUser->id . '_meso_data', $mergedData, now()->addMinutes(1));
        return ApiResponse::success($mergedData);
    }

    public function userDashboard(Request $req)
    {
        $cUser = $req->user();
        $userId = $cUser->id;
        $activePointer = get_active_pointer($cUser->id, $cUser->meso_start_date);
        $trainingMax = $this->calculateTrainingMax($userId, $activePointer);
        $weightLifted = $this->calculateWeightLifted($userId, $activePointer);
        return ApiResponse::success([
            'traning_max' => $trainingMax ? 1 : 0,
            'traning_max_weights' => empty($trainingMax) ? null : $trainingMax,
            'weight_lefted' => $weightLifted ? 1 : 0,
            'active_plans' => $activePointer,
            'notification_count' => $cUser->user_notifications()->where('read_at', null)->count(),
            'is_subscribe' => $cUser->is_subscribe == 1 ? true : false,
            'is_meso_completed' => !$this->is_eligible_for_reset($cUser)
        ]);
    }

    private function calculateTrainingMax($userId, $activePointer)
    {
        if (!$activePointer || $activePointer['meso_id'] < 2) {
            return null;
        }
        $mainExercises = Exercise::whereIn('name', ['Bench Press', 'Squat', 'Deadlift'])->pluck('id', 'name')->toArray();
        if (empty($mainExercises)) {
            return null;
        }
        $trainingMaxData = [];
        $trainingMaxPercentage = 0.75;
        foreach ($mainExercises as $name => $exerciseId) {
            $lastStats = UserProgress::where('user_id', $userId)
                ->where('exercise_id', $exerciseId)
                ->where('meso_id', $activePointer['meso_id'])
                 ->where('week_id', '<', $activePointer['week_id'])
                ->where('status', 2)
                ->orderByDesc('created_at')
                ->get();
            if ($lastStats->isNotEmpty()) {
                $weights = [];
                foreach ($lastStats as $stat) {
                    $decodedWeights = json_decode($stat->weight, true);
                    if (is_array($decodedWeights)) {
                        $weights = array_merge($weights, array_values($decodedWeights));
                    } elseif (is_numeric($stat->weight)) {
                        $weights[] = $stat->weight;
                    }
                }
                $avgWeight = array_sum($weights) / count($weights);
                // Apply Epley formula (example: estimated 1RM = w * (1 + reps/30))
                $reps = $lastStat->reps ?? 6;
                $oneRepMax = $avgWeight * (1 + $reps / 30);
                // Take 70% of 1RM as training max
                $trainingMaxData[Str::slug($name)] = round($oneRepMax * $trainingMaxPercentage, 2);
            }
        }
        return $trainingMaxData;
    }

    private function calculateWeightLifted($userId, $activePointer, bool $includeReps = true)
    {
        $totalWeightLifted = 0;
        $completedProgress = UserProgress::where('user_id', $userId)->where('status', 2)->when($activePointer['meso_id'] ?? null, fn($q, $mId) => $q->where('meso_id', $mId))->when($activePointer['week_id'] ?? null, fn($q, $wId) => $q->where('week_id', $wId))->when($activePointer['day_id'] ?? null, fn($q, $dId) => $q->where('day_id', $dId))->whereNotNull('weight')->get();
        foreach ($completedProgress as $progress) {
            $weights = json_decode($progress->weight, true);
            $reps = (int) $progress->reps;
            $setWeight = is_array($weights) ? array_sum($weights) : (float) $progress->weight;
            if ($includeReps && $reps > 0) {
                $totalWeightLifted += $setWeight * $reps;
            } else {
                $totalWeightLifted += $setWeight;
            }
        }
        return round($totalWeightLifted, 2);
    }

    public function list()
    {
        return ApiResponse::success(MesoCycle::all());
    }
}
