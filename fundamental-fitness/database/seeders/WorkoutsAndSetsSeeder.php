<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkoutsAndSetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(config('tables.workouts'))->truncate();
        DB::table(config('tables.workout_sets'))->truncate();
        DB::statement("
            INSERT INTO " . config('tables.workouts') . " 
            (workout_frequency_id, meso_id, week_id, day_id, exercise_id, level, image, video, gif, description, created_at, updated_at) 
            SELECT 
                wf.id AS workout_frequency_id, 
                m.id AS meso_id, 
                w.id AS week_id, 
                d.id AS day_id, 
                e.id AS exercise_id, 
                1 AS level, 
                NULL AS image, 
                NULL AS video, 
                NULL AS gif, 
                '' AS description, 
                NOW() AS created_at, 
                NOW() AS updated_at 
            FROM 
                (SELECT 1 AS id UNION ALL SELECT 4) wf 
            CROSS JOIN 
                (SELECT 1 AS id UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) m 
            CROSS JOIN 
                (SELECT 1 AS id UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) w 
            CROSS JOIN 
                (SELECT 1 AS id UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6) d 
            CROSS JOIN 
                " . config('tables.exercises') . " e
        ");

        // Insert Workout Sets
        DB::statement("
            INSERT INTO " . config('tables.workout_sets') . " 
            (workout_id, set_number, reps, reps_unit, rpe, rpe_percentage, rest, rest_unit, created_at, updated_at) 
            SELECT 
                w.id AS workout_id, 
                s.set_number, 
                4 + s.set_number AS reps, 
                1 AS reps_unit, 
                4 + s.set_number AS rpe, 
                NULL AS rpe_percentage, 
                s.set_number * 20 AS rest, 
                'seconds' AS rest_unit, 
                NOW() AS created_at, 
                NOW() AS updated_at 
            FROM 
                " . config('tables.workouts') . " w 
            CROSS JOIN 
                (SELECT 1 AS set_number UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) s
        ");
    }
}
