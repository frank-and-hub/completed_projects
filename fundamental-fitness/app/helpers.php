<?php

use App\Models\{User, UserProgress, UserWorkout, MesoCycle, Week, Exercise, WorkoutFrequency};
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

if (!function_exists('pre')) {
    function pre($data = '', $status = FALSE)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if (!$status) {
            die;
        }
    }
}
if (!function_exists('pree')) {
    function pree($data = '', $status = FALSE)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        if (!$status) {
            die;
        }
    }
}
if (!function_exists('p')) {
    function p($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}
if (!function_exists('pd')) {
    function pd($data)
    {
        p($data);
        die();
    }
}
if (!function_exists('getDateInFormat')) {
    function getDateInFormat($date)
    {
        if (!empty($date)) {
            $dateTimeObject = new DateTime($date);
            return $dateTimeObject->format('d M, Y');
        } else {
            return '-';
        }
    }
}
if (!function_exists('get_avatar')) {
    function get_avatar($avatar = '')
    {
        return $avatar == '' ? asset('assets/img/user.png') : asset($avatar);
    }
}
if (!function_exists('is_exercise_completed')) {
    function is_exercise_completed($uId, $exerciseId, $dId, $wId, $mId)
    {
        return UserProgress::whereUserId($uId)->whereExerciseId($exerciseId)->whereDayId($dId)->whereWeekId($wId)->whereMesoId($mId)->whereStatus(2)->exists();
    }
}
if (!function_exists('is_day_completed')) {
    function is_day_completed($uId, $dId, $wId, $mId)
    {
        $total = UserProgress::whereUserId($uId)->whereMesoId($mId)->whereWeekId($wId)->whereDayId($dId)->count();
        $completed = UserProgress::whereUserId($uId)->whereMesoId($mId)->whereWeekId($wId)->whereDayId($dId)->whereNotNull('completed_at')->whereIn('status', [2])->count();
        return $total > 0 && $total == $completed;
    }
}
if (!function_exists('is_week_completed')) {
    function is_week_completed($uId, $wId, $mId)
    {
        $days = UserProgress::whereUserId($uId)->whereMesoId($mId)->whereWeekId($wId)->select('day_id')->distinct()->pluck('day_id');
        foreach ($days as $dId) {
            if (!is_day_completed($uId, $dId, $wId, $mId)) {
                return false;
            }
        }
        return $days->count() > 0;
    }
}
if (!function_exists('is_meso_completed')) {
    function is_meso_completed($uId, $mId)
    {
        $weeks = UserProgress::whereUserId($uId)->whereMesoId($mId)->select('week_id')->distinct()->pluck('week_id');
        foreach ($weeks as $wId) {
            if (!is_week_completed($uId, $wId, $mId)) {
                return false;
            }
        }
        return $weeks->count() > 0;
    }
}
if (!function_exists('get_active_pointer')) {
    function get_active_pointer($uId, $mesoStartDate = null)
    {
        $today = Carbon::now();
        $todayWorkouts = UserWorkout::whereUserId($uId)
            ->whereDate('execution_date', '<=', $today)
            ->orderBy('meso_id')
            ->orderBy('week_id')
            ->orderBy('day_id')
            ->orderByDesc('execution_date')
            ->get()
            ->groupBy(['meso_id', 'week_id', 'day_id']);

        if (!$mesoStartDate) {
            return ['meso_id' => 1, 'week_id' => 1, 'day_id'  => 1];
        }

        if ($todayWorkouts->isEmpty()) {
            $last = UserWorkout::whereUserId($uId)
                ->whereNotNull('execution_date')
                ->orderByDesc('execution_date')
                ->first();
            if ($last) {
                return ['meso_id' => (int) $last->meso_id, 'week_id' => (int) $last->week_id, 'day_id'  => (int) $last->day_id];
            }
            return ['meso_id' => 1, 'week_id' => 1, 'day_id'  => 1];
        }
        $startDate = Carbon::parse($mesoStartDate)->startOfDay();
        $daysSinceStart = $startDate->diffInDays(Carbon::today());
        $daysPerMeso = 4 * 7;
        $daysPerWeek = 7;
        $mId = floor($daysSinceStart / $daysPerMeso) + 1;
        $remainingDays = $daysSinceStart % $daysPerMeso;
        $wId = floor($remainingDays / $daysPerWeek) + 1;
        $dId = ($remainingDays % $daysPerWeek) + 1;

        $mx_m = $todayWorkouts?->keys()?->max() ?? 1;
        $mId = min((int) $mId, (int) $mx_m);
        $mx_w = optional($todayWorkouts[(int)$mId])?->keys()?->max() ?? 1;
        $wId = min((int) $wId, (int) $mx_w);
        $mx_d = optional(optional($todayWorkouts[(int) $mId])[(int) $wId])?->keys()?->max() ?? 1;
        $dId = min((int) $dId, (int) $mx_d);

        if (isset($todayWorkouts[(int)$mId][$wId][$dId])) {
            if (!is_day_completed($uId, $dId, $wId, $mId)) {
                return ['meso_id' => $mId, 'week_id' => $wId, 'day_id'  => $dId];
            }
        }

        return ['meso_id' => (int) $mx_m, 'week_id' => (int) $mx_w, 'day_id'  => (int) $mx_d];
    }
}
if (!function_exists('get_weekly_completion_rate')) {
    function get_weekly_completion_rate(User $user, $wId, $mId)
    {
        $t = UserProgress::where('user_id', $user->id)->where('week_id', $wId)->where('meso_id', $mId)->count();
        $c = UserProgress::where('user_id', $user->id)->where('week_id', $wId)->where('meso_id', $mId)->where('status', 2)->count();
        if ($t === 0) {
            return 0;
        }
        return round(($c / $t) * 100);
    }
}
if (!function_exists('get_meso_cycle')) {
    function get_meso_cycle()
    {
        $c = Cache::get('meso_cycle');
        if (!$c) {
            $data = MesoCycle::all();
            Cache::put('meso_cycle', $data, now()->addDay());
            $c = $data;
        }
        return  $c;
    }
}
if (!function_exists('get_weeks')) {
    function get_weeks()
    {
        $c = Cache::get('weeks');
        if (!$c) {
            $data = Week::all();
            Cache::put('weeks', $data, now()->addDay());
            $c = $data;
        }
        return  $c;
    }
}
if (!function_exists('all_exercise_data')) {
    function all_exercise_data()
    {
        $c = Cache::get('all_exercise');
        if (!$c) {
            $data = Exercise::all();
            Cache::put('all_exercise', $data, now()->addDay());
            $c = $data;
        }
        return  $c;
    }
}
if (!function_exists('all_frequencies_data')) {
    function all_frequencies_data()
    {
        $c = Cache::get('all_frequencies');
        if (!$c) {
            $data = WorkoutFrequency::all();
            Cache::put('all_frequencies', $data, now()->addDay());
            $c = $data;
        }
        return $c;
    }
}
if (!function_exists('completion_rate')) {
    function completion_rate($a, $c): float|int
    {
        return $a > 0 ? round(($c / $a) * 100, 2) : 0;
    }
}
if (!function_exists('get_running_id')) {
    function get_running_id()
    {
        $exercise = Exercise::where(function ($q) {
            $q->where('name', 'like', '%Running%')->orWhere('name', 'like', '%run%');
        })->first();
        if (!$exercise) {
            return 1;
        } else {
            return $exercise->id;
        }
    }
}
if (!function_exists('make_transaction_date')) {
    function make_transaction_date(string $date, $formate = 'Y-m-d H:i:s'): string
    {
        if (is_numeric($date)) {
            if (strlen($date) > 10) {
                $date = intval($date) / 1000;
            }
            return date($formate, $date);
        } else {
            return date($formate, strtotime($date));
        }
    }
}
if (!function_exists('get_user_predicted_completion_time')) {
    function get_user_predicted_completion_time(int $uId): ?string
    {
        $completedRecords = UserProgress::where('user_id', $uId)->whereIn('status', [2])->whereNotNull('completed_at')->get(['completed_at']);
        if ($completedRecords->isEmpty()) {
            return null;
        }
        $secondsArray = $completedRecords->map(function ($record) {
            $time = $record->completed_at;
            return $time->hour * 3600 + $time->minute * 60 + $time->second;
        })->toArray();
        $avgSeconds = round(array_sum($secondsArray) / count($secondsArray));
        $hours = floor($avgSeconds / 3600);
        $minutes = floor(($avgSeconds % 3600) / 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
