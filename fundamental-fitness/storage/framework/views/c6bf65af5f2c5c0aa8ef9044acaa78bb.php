<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Edit Exercise'); ?>
<?php $__env->startPush('styles'); ?>
    <style>
        .ff-error {
            left: 0;
            top: 100%;
            margin-top: 2px;
            font-size: 12px;
            color: #dc3545;
            line-height: 1.1;
            white-space: nowrap;
        }

        .ff-rel {
            position: relative;
        }

        .rpe-percentage-input .ff-error {
            left: auto;
            right: 0;
        }
    </style>
<?php $__env->stopPush(); ?>
 <?php
    $runId = get_running_id();
?>
<div class="container-fluid">
    <div class="pate-content-wrapper">
        <div class="page-title-row align-items-center">
            <a href="<?php echo e(route('admin.exerciseIndex')); ?>" class="btn btn-link p-2 py-1 me-2 bg-transparent">
                <img src="<?php echo e(asset('assets/images/backbutton.svg')); ?>" alt="image">
            </a>
            <h5 class="mb-0">Edit Exercise</h5>
        </div>
        <div class="m-card-min-hight">
            <div class="addexercisemainbox">
                <div class="addboxmax">
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="pb-1">Plan Frequency</h5>
                            <div class="row">

                                <!-- Workout Frequency -->
                                <div class="col-sm-12 col-lg-4">
                                    <div class="planfreq">
                                        <label class="text-14 font-400">Workout Frequency</label>
                                        <div class="pt-1">
                                            <?php if(!empty($workout) && $workout->workout_frequency_id): ?>
                                                
                                                <p class="mb-0 showcasevalue">
                                                    <?php echo e($workoutFrequencies->firstWhere('id', $workout->workout_frequency_id)->name ?? 'N/A'); ?>

                                                </p>
                                                <input type="hidden" name="workout_frequency"
                                                    value="<?php echo e($workout->workout_frequency_id); ?>">
                                            <?php else: ?>
                                                
                                                <?php $__currentLoopData = $workoutFrequencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $workoutFrequency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio"
                                                            name="workout_frequency"
                                                            id="<?php echo e($workoutFrequency->days_in_week); ?>"
                                                            value="<?php echo e($workoutFrequency->id); ?>"
                                                            <?php if($key == 0): ?> checked <?php endif; ?>>
                                                        <label class="form-check-label"
                                                            for="<?php echo e($workoutFrequency->days_in_week); ?>"><?php echo e($workoutFrequency->name); ?></label>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Meso -->
                                <div class="col-sm-12 col-lg-4">
                                    <div class="planfreq">
                                        <label class="text-14 font-400">Meso</label>
                                        <div class="pt-1">
                                            <?php if(!empty($workout) && $workout->meso_id): ?>
                                                <p class="mb-0 showcasevalue">
                                                    <?php echo e($mesos->firstWhere('id', $workout->meso_id)->name ?? 'N/A'); ?>

                                                </p>
                                                <input type="hidden" name="meso" value="<?php echo e($workout->meso_id); ?>">
                                            <?php else: ?>
                                                <?php $__empty_1 = true; $__currentLoopData = $mesos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $meso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="meso"
                                                            id="meso<?php echo e($meso->id); ?>" value="<?php echo e($meso->id); ?>"
                                                            <?php echo e($loop->first ? 'checked' : ''); ?>>
                                                        <label class="form-check-label" for="meso<?php echo e($meso->id); ?>">
                                                            <?php echo e($meso->name ?? 'Meso ' . ($index + 1)); ?>

                                                        </label>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <p class="text-muted">No meso cycles available</p>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Week -->
                                <div class="col-sm-12 col-lg-4">
                                    <div class="planfreq">
                                        <label class="text-14 font-400">Week</label>
                                        <div class="pt-1" id="week-container">
                                            <?php if(!empty($workout) && $workout->week_id): ?>
                                                <p class="mb-0 showcasevalue">Week <?php echo e($workout->week_id); ?></p>
                                                <input type="hidden" name="week" value="<?php echo e($workout->week_id); ?>">
                                            <?php else: ?>
                                                <?php for($i = 1; $i <= 4; $i++): ?>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input week-radio" type="radio"
                                                            name="week" id="week<?php echo e($i); ?>"
                                                            value="<?php echo e($i); ?>"
                                                            <?php echo e($i === 1 ? 'checked' : ''); ?>>
                                                        <label class="form-check-label" for="week<?php echo e($i); ?>">
                                                            Week <?php echo e($i); ?>

                                                        </label>
                                                    </div>
                                                <?php endfor; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 mt-4">
                            <h5 class="pb-1">Exercise Plan</h5>
                            <div class="accordion darkaccordian w-100" id="daysAccordion">
                                <?php
                                    // Determine the day based on workout frequency
                                    $maxDays = $workout->workoutFrequency->days_in_week ?? 7;
                                    $currentDay = $workout->day_id ?? 1;
                                ?>

                                <?php for($day = 1; $day <= $maxDays; $day++): ?>
                                    <div class="accordion-item day-item" data-day="<?php echo e($day); ?>"
                                        style="<?php echo e($currentDay == $day ? '' : 'display: none;'); ?>">
                                        <h2 class="accordion-header"
                                            id="heading<?php echo e(ucfirst(Str::words(Str::studly('Day' . $day), 1, ''))); ?>">
                                            <button
                                                class="accordion-button <?php echo e($currentDay != $day ? 'collapsed' : ''); ?>"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse<?php echo e(ucfirst(Str::words(Str::studly('Day' . $day), 1, ''))); ?>"
                                                aria-expanded="<?php echo e($currentDay == $day ? 'true' : 'false'); ?>"
                                                aria-controls="collapse<?php echo e(ucfirst(Str::words(Str::studly('Day' . $day), 1, ''))); ?>">
                                                Day <?php echo e($day); ?>

                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo e(ucfirst(Str::words(Str::studly('Day' . $day), 1, ''))); ?>"
                                            class="accordion-collapse collapse <?php echo e($currentDay == $day ? 'show' : ''); ?>"
                                            aria-labelledby="heading<?php echo e(ucfirst(Str::words(Str::studly('Day' . $day), 1, ''))); ?>"
                                            data-bs-parent="#daysAccordion">
                                            <div class="accordion-body pt-0">
                                                <div class="exercise-box-block-row w-full d-flex flex-column gap-3">
                                                    <?php if($currentDay == $day): ?>
                                                        <!-- exercise block -->
                                                        <div class="exercise-box-block p-3 rounded-3 bg-white">
                                                            <input type="hidden" name="workout_id"
                                                                value="<?php echo e($workout->id); ?>">
                                                            <input type="hidden" name="day_id"
                                                                value="<?php echo e($day); ?>">

                                                            <div class="form-group mb-3">
                                                                <label for="exercise_id" class="form-label">Exercise
                                                                    Title</label>
                                                                <div
                                                                    class="position-relative w-100 exercise-select-block">
                                                                    <select name="exercise_id[]"
                                                                        class="form-control form-select exercise-select"
                                                                        required>
                                                                        <option value="">Select Exercise</option>
                                                                        <?php $__currentLoopData = $exercises; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exercise): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                            <option value="<?php echo e($exercise->id); ?>"
                                                                                data-type="<?php echo e($exercise->type ?? 'normal'); ?>"
                                                                                <?php echo e($workout->exercise_id == $exercise->id ? 'selected' : ''); ?>>
                                                                                <?php echo e($exercise->name); ?>

                                                                            </option>
                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- Level Selection -->
                                                            <div class="row setcolswidh">
                                                                <div class="col-xxl-8 col-xl-12">
                                                                    <div class="d-block w-full mb-3">
                                                                        <label for=""
                                                                            class="text-14 font-400">Select
                                                                            Level</label>
                                                                        <div class="pt-1">
                                                                            <div class="form-check form-check-inline">
                                                                                <input class="form-check-input"
                                                                                    type="radio"
                                                                                    name="level_day<?php echo e($day); ?>[]"
                                                                                    id="Beginner_day<?php echo e($day); ?>"
                                                                                    value="1"
                                                                                    <?php echo e($workout->level == 1 ? 'checked' : ''); ?>>
                                                                                <label class="form-check-label"
                                                                                    for="Beginner_day<?php echo e($day); ?>">Beginner</label>
                                                                            </div>
                                                                            <div class="form-check form-check-inline">
                                                                                <input class="form-check-input"
                                                                                    type="radio"
                                                                                    name="level_day<?php echo e($day); ?>[]"
                                                                                    id="Intermediate_day<?php echo e($day); ?>"
                                                                                    value="2"
                                                                                    <?php echo e($workout->level == 2 ? 'checked' : ''); ?>>
                                                                                <label class="form-check-label"
                                                                                    for="Intermediate_day<?php echo e($day); ?>">Intermediate</label>
                                                                            </div>
                                                                            <div class="form-check form-check-inline">
                                                                                <input class="form-check-input"
                                                                                    type="radio"
                                                                                    name="level_day<?php echo e($day); ?>[]"
                                                                                    id="Advanced_day<?php echo e($day); ?>"
                                                                                    value="3"
                                                                                    <?php echo e($workout->level == 3 ? 'checked' : ''); ?>>
                                                                                <label class="form-check-label"
                                                                                    for="Advanced_day<?php echo e($day); ?>">Advanced</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                   
                                                                    <!-- Normal Exercise Table -->
                                                                    <div class="d-block w-full mb-3 p-2 setsformbox rounded-3 normal-exercise"
                                                                        style="<?php echo e((isset($workout->exercise) && $workout->exercise->type == 'running') || $workout->exercise_id == $runId ? 'display: none !important;' : ''); ?>">
                                                                        <table class="setsdatatable">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Set</th>
                                                                                    <th>Reps</th>
                                                                                    <th>RPE</th>
                                                                                    <th>Rest (seconds)</th>
                                                                                    <th>&nbsp;</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php if(
                                                                                    $workout->sets &&
                                                                                        $workout->sets->count() > 0 &&
                                                                                        !((isset($workout->exercise) && $workout->exercise->type == 'running') || $workout->exercise_id == $runId)): ?>
                                                                                    <?php $__currentLoopData = $workout->sets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $set): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                        <tr>
                                                                                            <td valign="top">
                                                                                                <input type="hidden"
                                                                                                    name="set_id[]"
                                                                                                    value="<?php echo e($set->id); ?>">
                                                                                                <select
                                                                                                    class="form-control form-select"
                                                                                                    name="set_number[]">
                                                                                                    <?php for($i = 1; $i <= 10; $i++): ?>
                                                                                                        <option
                                                                                                            value="<?php echo e($i); ?>"
                                                                                                            <?php echo e($set->set_number == $i ? 'selected' : ''); ?>>
                                                                                                            <?php echo e($i); ?>

                                                                                                        </option>
                                                                                                    <?php endfor; ?>
                                                                                                </select>
                                                                                            </td>
                                                                                            <td valign="top">
                                                                                                <select
                                                                                                    class="form-control form-select"
                                                                                                    name="reps[]">
                                                                                                    <?php for($i = 1; $i <= 30; $i++): ?>
                                                                                                        <option
                                                                                                            value="<?php echo e($i); ?>"
                                                                                                            <?php echo e($set->reps == $i ? 'selected' : ''); ?>>
                                                                                                            <?php echo e($i); ?>

                                                                                                        </option>
                                                                                                    <?php endfor; ?>
                                                                                                </select>
                                                                                            </td>
                                                                                            <td valign="top"
                                                                                                class="rpevaluebox">
                                                                                                <div
                                                                                                    class="d-flex gap-1 rpe-container">
                                                                                                    <div
                                                                                                        class="flex-grow-1">
                                                                                                        <select
                                                                                                            class="form-control form-select rpe-select"
                                                                                                            name="rpe[]">
                                                                                                            <?php for($i = 1; $i <= 10; $i++): ?>
                                                                                                                <option
                                                                                                                    value="<?php echo e($i); ?>"
                                                                                                                    <?php echo e($set->rpe == $i ? 'selected' : ''); ?>>
                                                                                                                    <?php echo e($i); ?>

                                                                                                                </option>
                                                                                                            <?php endfor; ?>
                                                                                                            <option
                                                                                                                value="0"
                                                                                                                <?php echo e($set->rpe == 0 ? 'selected' : ''); ?>>
                                                                                                                Other
                                                                                                            </option>
                                                                                                        </select>
                                                                                                    </div>
                                                                                                    <div class="input-group rpe-percentage-input"
                                                                                                        style="width: 70px; <?php echo e($set->rpe == 0 ? '' : 'display: none;'); ?>">
                                                                                                        <input
                                                                                                            type="text"
                                                                                                            class="form-control text-center px-1"
                                                                                                            aria-label="RPE Percentage"
                                                                                                            aria-describedby="basic-addon1"
                                                                                                            name="rpe_percentage[]"
                                                                                                            value="<?php echo e($set->rpe_percentage); ?>">
                                                                                                        <span
                                                                                                            class="input-group-text px-2"
                                                                                                            id="basic-addon1">%</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </td>
                                                                                            <td valign="top">
                                                                                                <input type="text"
                                                                                                    class="form-control"
                                                                                                    name="rest[]"
                                                                                                    value="<?php echo e($set->rest); ?>" />
                                                                                            </td>
                                                                                            <td valign="top">
                                                                                                <?php if($loop->first): ?>
                                                                                                    <button
                                                                                                        class="btn btn-outline-primary add-set-btn">+</button>
                                                                                                <?php else: ?>
                                                                                                    <button
                                                                                                        class="btn btn-outline-primary remove-set-btn">-</button>
                                                                                                <?php endif; ?>
                                                                                            </td>
                                                                                        </tr>
                                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                                <?php else: ?>
                                                                                    <!-- Empty state for normal exercises when no data or running exercise selected -->
                                                                                    <tr>
                                                                                        <td valign="top">
                                                                                            <select
                                                                                                class="form-control form-select"
                                                                                                name="set_number[]">
                                                                                                <?php for($i = 1; $i <= 10; $i++): ?>
                                                                                                    <option
                                                                                                        value="<?php echo e($i); ?>"
                                                                                                        <?php echo e($i == 1 ? 'selected' : ''); ?>>
                                                                                                        <?php echo e($i); ?>

                                                                                                    </option>
                                                                                                <?php endfor; ?>
                                                                                            </select>
                                                                                        </td>
                                                                                        <td valign="top">
                                                                                            <select
                                                                                                class="form-control form-select"
                                                                                                name="reps[]">
                                                                                                <?php for($i = 1; $i <= 30; $i++): ?>
                                                                                                    <option
                                                                                                        value="<?php echo e($i); ?>"
                                                                                                        <?php echo e($i == 1 ? 'selected' : ''); ?>>
                                                                                                        <?php echo e($i); ?>

                                                                                                    </option>
                                                                                                <?php endfor; ?>
                                                                                            </select>
                                                                                        </td>
                                                                                        <td valign="top">
                                                                                            <div
                                                                                                class="d-flex gap-1 rpe-container">
                                                                                                <div
                                                                                                    class="flex-grow-1">
                                                                                                    <select
                                                                                                        class="form-control form-select rpe-select"
                                                                                                        name="rpe[]">
                                                                                                        <?php for($i = 1; $i <= 10; $i++): ?>
                                                                                                            <option
                                                                                                                value="<?php echo e($i); ?>"
                                                                                                                <?php echo e($i == 1 ? 'selected' : ''); ?>>
                                                                                                                <?php echo e($i); ?>

                                                                                                            </option>
                                                                                                        <?php endfor; ?>
                                                                                                        <option
                                                                                                            value="0">
                                                                                                            Other
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                                <div class="input-group rpe-percentage-input"
                                                                                                    style="width:100px; display: none;">
                                                                                                    <input
                                                                                                        type="text"
                                                                                                        class="form-control text-center px-1"
                                                                                                        aria-label="RPE Percentage"
                                                                                                        aria-describedby="basic-addon1"
                                                                                                        name="rpe_percentage[]"
                                                                                                        value="">
                                                                                                    <span
                                                                                                        class="input-group-text px-2"
                                                                                                        id="basic-addon1">%</span>
                                                                                                </div>
                                                                                            </div>
                                                                                        </td>
                                                                                        <td valign="top">
                                                                                            <input type="text"
                                                                                                class="form-control"
                                                                                                name="rest[]"
                                                                                                value="" />
                                                                                        </td>
                                                                                        <td valign="top">
                                                                                            <button
                                                                                                class="btn btn-outline-primary add-set-btn">+</button>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php endif; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>

                                                                    <!-- Running Exercise Table -->
                                                                    <div class="d-block w-full mb-3 p-2 setsformbox rounded-3 running-exercise"
                                                                        style="<?php echo e((isset($workout->exercise) && $workout->exercise->type == 'running') || $workout->exercise_id == $runId ? '' : 'display: none !important;'); ?>">
                                                                        <table class="setsdatatable">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Set</th>
                                                                                    <th>Duration/Distance</th>
                                                                                    <th>RPE</th>
                                                                                    <th>Walk (seconds)</th>
                                                                                    <th>&nbsp;</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php if(
                                                                                    $workout->sets &&
                                                                                        $workout->sets->count() > 0 &&
                                                                                        ((isset($workout->exercise) && $workout->exercise->type == 'running') || $workout->exercise_id == $runId)): ?>
                                                                                    <?php $__currentLoopData = $workout->sets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $set): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                        <tr>
                                                                                            <td valign="top">
                                                                                                <input type="hidden"
                                                                                                    name="set_id[]"
                                                                                                    value="<?php echo e($set->id); ?>">
                                                                                                <select
                                                                                                    class="form-control form-select running-type"
                                                                                                    name="running_type[]">
                                                                                                    <option
                                                                                                        value="1"
                                                                                                        <?php echo e($set->reps_unit == 'min' ? 'selected' : ''); ?>>
                                                                                                        Duration
                                                                                                    </option>
                                                                                                    <option
                                                                                                        value="2"
                                                                                                        <?php echo e($set->reps_unit == 'km' ? 'selected' : ''); ?>>
                                                                                                        Distance
                                                                                                    </option>
                                                                                                </select>
                                                                                            </td>
                                                                                            <td valign="top">
                                                                                                <div
                                                                                                    class="input-group w-100 mb-3 running-value">
                                                                                                    <input
                                                                                                        type="text"
                                                                                                        class="form-control text-center px-1"
                                                                                                        aria-label="Run value"
                                                                                                        value="<?php echo e($set->reps); ?>"
                                                                                                        name="running_value[]">
                                                                                                    <span
                                                                                                        class="input-group-text px-2 running-unit"
                                                                                                        style="width:40px;"><?php echo e($set->reps_unit); ?></span>
                                                                                                </div>
                                                                                            </td>
                                                                                            <td valign="top">
                                                                                                <div
                                                                                                    class="d-flex gap-1 rpe-container">
                                                                                                    <div
                                                                                                        class="flex-grow-1">
                                                                                                        <select
                                                                                                            class="form-control form-select rpe-select"
                                                                                                            name="rpe[]">
                                                                                                            <?php for($i = 1; $i <= 10; $i++): ?>
                                                                                                                <option
                                                                                                                    value="<?php echo e($i); ?>"
                                                                                                                    <?php echo e($set->rpe == $i ? 'selected' : ''); ?>>
                                                                                                                    <?php echo e($i); ?>

                                                                                                                </option>
                                                                                                            <?php endfor; ?>
                                                                                                            <option
                                                                                                                value="0"
                                                                                                                <?php echo e($set->rpe == 0 ? 'selected' : ''); ?>>
                                                                                                                Other
                                                                                                            </option>
                                                                                                        </select>
                                                                                                    </div>
                                                                                                    <div class="input-group mb-3 rpe-percentage-input"
                                                                                                        style="width: 70px; <?php echo e($set->rpe == 0 ? '' : 'display: none;'); ?>">
                                                                                                        <input
                                                                                                            type="text"
                                                                                                            class="form-control text-center px-1"
                                                                                                            aria-label="RPE Percentage"
                                                                                                            aria-describedby="basic-addon1"
                                                                                                            name="rpe_percentage[]"
                                                                                                            value="<?php echo e($set->rpe_percentage); ?>">
                                                                                                        <span
                                                                                                            class="input-group-text px-2"
                                                                                                            id="basic-addon1">%</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </td>
                                                                                            <td valign="top">
                                                                                                <input type="text"
                                                                                                    class="form-control"
                                                                                                    name="walk[]"
                                                                                                    value="<?php echo e($set->rest); ?>" />
                                                                                            </td>
                                                                                            <td valign="top">
                                                                                                <?php if($loop->first): ?>
                                                                                                    <button
                                                                                                        class="btn btn-outline-primary add-running-set-btn">+</button>
                                                                                                <?php else: ?>
                                                                                                    <button
                                                                                                        class="btn btn-outline-primary remove-set-btn">-</button>
                                                                                                <?php endif; ?>
                                                                                            </td>
                                                                                        </tr>
                                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                                <?php else: ?>
                                                                                    <!-- Empty state for running exercises when no data -->
                                                                                    <tr>
                                                                                        <td valign="top">
                                                                                            <select
                                                                                                class="form-control form-select running-type"
                                                                                                name="running_type[]">
                                                                                                <option value="1"
                                                                                                    selected>Duration
                                                                                                </option>
                                                                                                <option value="2">
                                                                                                    Distance</option>
                                                                                            </select>
                                                                                        </td>
                                                                                        <td valign="top">
                                                                                            <div
                                                                                                class="input-group w-100 mb-3 running-value">
                                                                                                <input type="text"
                                                                                                    class="form-control text-center px-1"
                                                                                                    aria-label="Run value"
                                                                                                    value=""
                                                                                                    name="running_value[]">
                                                                                                <span
                                                                                                    class="input-group-text px-2 running-unit"
                                                                                                    style="width:40px;">min</span>
                                                                                            </div>
                                                                                        </td>
                                                                                        <td valign="top">
                                                                                            <div
                                                                                                class="d-flex gap-1 rpe-container">
                                                                                                <div
                                                                                                    class="flex-grow-1">
                                                                                                    <select
                                                                                                        class="form-control form-select rpe-select"
                                                                                                        name="rpe[]">
                                                                                                        <?php for($i = 1; $i <= 10; $i++): ?>
                                                                                                            <option
                                                                                                                value="<?php echo e($i); ?>"
                                                                                                                <?php echo e($i == 1 ? 'selected' : ''); ?>>
                                                                                                                <?php echo e($i); ?>

                                                                                                            </option>
                                                                                                        <?php endfor; ?>
                                                                                                        <option
                                                                                                            value="0">
                                                                                                            Other
                                                                                                        </option>
                                                                                                    </select>
                                                                                                </div>
                                                                                                <div class="input-group mb-3 rpe-percentage-input"
                                                                                                    style="width: 70px; display: none;">
                                                                                                    <input
                                                                                                        type="text"
                                                                                                        class="form-control text-center px-1"
                                                                                                        aria-label="RPE Percentage"
                                                                                                        aria-describedby="basic-addon1"
                                                                                                        name="rpe_percentage[]"
                                                                                                        value="">
                                                                                                    <span
                                                                                                        class="input-group-text px-2"
                                                                                                        id="basic-addon1">%</span>
                                                                                                </div>
                                                                                            </div>
                                                                                        </td>
                                                                                        <td valign="top">
                                                                                            <input type="text"
                                                                                                class="form-control"
                                                                                                name="walk[]"
                                                                                                value="" />
                                                                                        </td>
                                                                                        <td valign="top">
                                                                                            <button
                                                                                                class="btn btn-outline-primary add-running-set-btn">+</button>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php endif; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>

                                                                <!-- Media Upload Section (Cover, GIF, Video) -->
                                                                <div class="col-xxl-4 col-xl-12">
                                                                    <div class="row">
                                                                        <div class="col-sm-4">
                                                                            <div class="form-group">
                                                                                <label
                                                                                    class="form-label">Cover&nbsp;Image</label>
                                                                                    <input type="hidden" name="delete_image[]" class="delete_image" value="0">

                                                                                <div class="customuploadfile">
                                                                                    <input type="file"
                                                                                        class="cover-image-input"
                                                                                        accept="image/png, image/jpeg, image/jpg, image/webp" />
                                                                                    <label>
                                                                                        <span class="filesizebox"><img
                                                                                                src="<?php echo e(asset('assets/images/uploadfile.svg')); ?>"
                                                                                                alt="image" /></span>
                                                                                        <span>Upload file</span>
                                                                                    </label>
                                                                                </div>
                                                                                <?php if($workout->image): ?>
                                                                                    <div class="uploadedfiledisplay">
                                                                                        <span class="filesizebox">
                                                                                            <a href="<?php echo e(asset('/' . $workout->image)); ?>"
                                                                                                target="_blank"
                                                                                                rel="noopener noreferrer">
                                                                                                <img src="<?php echo e(asset('/' . $workout->image)); ?>"
                                                                                                    alt="Cover Image"
                                                                                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; cursor: pointer;" />
                                                                                            </a>
                                                                                        </span>
                                                                                        <span
                                                                                            class="deletelinkbtn" data-type="image">Delete</span>
                                                                                    </div>
                                                                                <?php else: ?>
                                                                                    <div
                                                                                        class="uploadedfiledisplay d-none">
                                                                                        <span class="filesizebox">
                                                                                            <img src="<?php echo e(asset('assets/images/login-left-img.png')); ?>"
                                                                                                alt="image" />
                                                                                        </span>
                                                                                        <span
                                                                                            class="deletelinkbtn" data-type="image">Delete</span>
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-sm-4">
                                                                            <div class="form-group">
                                                                                <label
                                                                                    class="form-label">GIF&nbsp;Image</label>
                                                                                    <input type="hidden" name="delete_gif[]" class="delete_gif" value="0">

                                                                                <div class="customuploadfile">
                                                                                    <input type="file"
                                                                                        class="gif-image-input"
                                                                                        accept="image/gif" />
                                                                                    <label>
                                                                                        <span class="filesizebox"><img
                                                                                                src="<?php echo e(asset('assets/images/uploadfile.svg')); ?>"
                                                                                                alt="image" /></span>
                                                                                        <span>Upload file</span>
                                                                                    </label>
                                                                                </div>
                                                                                <!-- GIF Image Display -->
                                                                                <?php if($workout->gif): ?>
                                                                                    <div class="uploadedfiledisplay">
                                                                                        <span class="filesizebox">
                                                                                            <a href="<?php echo e(asset('/' . $workout->gif)); ?>"
                                                                                                target="_blank"
                                                                                                rel="noopener noreferrer">
                                                                                                <img src="<?php echo e(asset('/' . $workout->gif)); ?>"
                                                                                                    alt="GIF Image"
                                                                                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; cursor: pointer;" />
                                                                                            </a>
                                                                                        </span>
                                                                                        <span
                                                                                            class="deletelinkbtn" data-type="gif">Delete</span>
                                                                                    </div>
                                                                                <?php else: ?>
                                                                                    <div
                                                                                        class="uploadedfiledisplay d-none">
                                                                                        <span class="filesizebox">
                                                                                            <img src="<?php echo e(asset('assets/images/login-left-img.png')); ?>"
                                                                                                alt="image" />
                                                                                        </span>
                                                                                        <span
                                                                                            class="deletelinkbtn" data-type="gif">Delete</span>
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-sm-4">
                                                                            <div class="form-group">
                                                                                <label class="form-label">Video</label>
                                                                                <input type="hidden" name="delete_video[]" class="delete_video" value="0">

                                                                                <div class="customuploadfile">
                                                                                    <input type="file"
                                                                                        class="video-input"
                                                                                        accept="video/*" />
                                                                                    <label>
                                                                                        <span class="filesizebox"><img
                                                                                                src="<?php echo e(asset('assets/images/uploadfile.svg')); ?>"
                                                                                                alt="image" /></span>
                                                                                        <span>Upload file</span>
                                                                                    </label>
                                                                                </div>
                                                                                <?php if($workout->video): ?>
                                                                                    <div class="uploadedfiledisplay">
                                                                                        <span
                                                                                            class="filesizebox isvideobg">
                                                                                            <a href="<?php echo e(asset('/' . $workout->video)); ?>"
                                                                                                target="_blank"
                                                                                                rel="noopener noreferrer">
                                                                                                <video
                                                                                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; cursor: pointer;"
                                                                                                    onmouseover="this.controls=true"
                                                                                                    onmouseout="this.controls=false">
                                                                                                    <source
                                                                                                        src="<?php echo e(asset('/' . $workout->video)); ?>"
                                                                                                        type="video/mp4">
                                                                                                    Your browser does
                                                                                                    not support the
                                                                                                    video tag.
                                                                                                </video>
                                                                                            </a>
                                                                                        </span>
                                                                                        <span
                                                                                            class="deletelinkbtn" data-type="video">Delete</span>
                                                                                    </div>
                                                                                <?php else: ?>
                                                                                    <div
                                                                                        class="uploadedfiledisplay d-none">
                                                                                        <span
                                                                                            class="filesizebox isvideobg">
                                                                                            <img src="<?php echo e(asset('assets/images/login-left-img.png')); ?>"
                                                                                                alt="image" />
                                                                                        </span>
                                                                                        <span
                                                                                            class="deletelinkbtn" data-type="video">Delete</span>
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button
                                                            class="btn btn-outline-primary whitebtn w-100 btn-sm add-exercise-btn">Add
                                                            More Exercise</button>
                                                    <?php else: ?>
                                                        <!-- Empty state for other days -->
                                                        <div class="text-center py-4">
                                                            <p class="text-muted">No exercises configured for Day
                                                                <?php echo e($day); ?></p>
                                                            <button
                                                                class="btn btn-outline-primary add-exercise-btn">Add
                                                                Exercise</button>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <div class="mt-3 w-100 text-end">
                                <a href="<?php echo e(url()->previous()); ?>"
                                    class="btn btn-sm btn-outline-secondary me-2">Cancel</a>
                                <button class="btn btn-primary btn-sm submit-btn">Update Exercise</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    $(document).ready(function() {
        // Running exercise ID
        const RUNNING_EXERCISE_ID = + "<?php echo e(get_running_id()); ?>";

        // Handle exercise selection change - show/hide running vs normal exercise forms
        $(document).on('change', '.exercise-select', function() {
            const exerciseBlock = $(this).closest('.exercise-box-block');
            const exerciseId = parseInt($(this).val());
            const exerciseType = $(this).find('option:selected').data('type');

            // Show running exercise form only for running exercises (ID 5 or type 'running')
            if (exerciseId === RUNNING_EXERCISE_ID || exerciseType === 'running') {
                exerciseBlock.find('.normal-exercise').attr("style", "display: none !important");
                exerciseBlock.find('.running-exercise').attr("style", "display: block !important");

                // Clear normal exercise data and populate running exercise with default data if empty
                const runningTable = exerciseBlock.find('.running-exercise tbody');
                if (runningTable.find('tr').length === 0) {
                    const defaultRunningRow = `
                        <tr>
                            <td valign="top">
                                <select class="form-control form-select running-type" name="running_type[]">
                                    <option value="1" selected>Duration</option>
                                    <option value="2">Distance</option>
                                </select>
                            </td>
                            <td valign="top">
                                <div class="input-group w-100 mb-3 running-value">
                                    <input type="text" class="form-control text-center px-1" aria-label="Run value" value="" name="running_value[]">
                                    <span class="input-group-text px-2 running-unit" style="width:40px;">min</span>
                                </div>
                            </td>
                            <td valign="top">
                                <div class="d-flex gap-1 rpe-container">
                                    <div class="flex-grow-1">
                                        <select class="form-control form-select rpe-select" name="rpe[]">
                                            <?php for($i = 1; $i <= 10; $i++): ?>
                                                <option value="<?php echo e($i); ?>" <?php echo e($i == 1 ? 'selected' : ''); ?>><?php echo e($i); ?></option>
                                            <?php endfor; ?>
                                            <option value="0">Other</option>
                                        </select>
                                    </div>
                                    <div class="input-group mb-3 rpe-percentage-input" style="width: 70px; display: none;">
                                        <input type="text" class="form-control text-center px-1" aria-label="RPE Percentage" aria-describedby="basic-addon1" name="rpe_percentage[]" value="">
                                        <span class="input-group-text px-2" id="basic-addon1">%</span>
                                    </div>
                                </div>
                            </td>
                            <td valign="top">
                                <input type="text" class="form-control" name="walk[]" value="" />
                            </td>
                            <td valign="top">
                                <button class="btn btn-outline-primary add-running-set-btn">+</button>
                            </td>
                        </tr>
                    `;
                    runningTable.html(defaultRunningRow);
                }
            } else {
                exerciseBlock.find('.normal-exercise').attr("style", "display: block !important");
                exerciseBlock.find('.running-exercise').attr("style", "display: none !important");

                // Clear running exercise data and populate normal exercise with default data if empty
                const normalTable = exerciseBlock.find('.normal-exercise tbody');
                if (normalTable.find('tr').length === 0) {
                    const defaultNormalRow = `
                        <tr>
                            <td valign="top">
                                <select class="form-control form-select" name="set_number[]">
                                    <?php for($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?php echo e($i); ?>" <?php echo e($i == 1 ? 'selected' : ''); ?>><?php echo e($i); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                            <td valign="top">
                                <select class="form-control form-select" name="reps[]">
                                    <?php for($i = 1; $i <= 30; $i++): ?>
                                        <option value="<?php echo e($i); ?>" <?php echo e($i == 1 ? 'selected' : ''); ?>><?php echo e($i); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                            <td valign="top">
                                <div class="d-flex gap-1 rpe-container">
                                    <div class="flex-grow-1">
                                        <select class="form-control form-select rpe-select" name="rpe[]">
                                            <?php for($i = 1; $i <= 10; $i++): ?>
                                                <option value="<?php echo e($i); ?>" <?php echo e($i == 1 ? 'selected' : ''); ?>><?php echo e($i); ?></option>
                                            <?php endfor; ?>
                                            <option value="0">Other</option>
                                        </select>
                                    </div>
                                    <div class="input-group mb-3 rpe-percentage-input" style="width: 70px; display: none;">
                                        <input type="text" class="form-control text-center px-1" aria-label="RPE Percentage" aria-describedby="basic-addon1" name="rpe_percentage[]" value="">
                                        <span class="input-group-text px-2" id="basic-addon1">%</span>
                                    </div>
                                </div>
                            </td>
                            <td valign="top">
                                <input type="text" class="form-control" name="rest[]" value="" />
                            </td>
                            <td valign="top">
                                <button class="btn btn-outline-primary add-set-btn">+</button>
                            </td>
                        </tr>
                    `;
                    normalTable.html(defaultNormalRow);
                }
            }
        });

        // Handle running type change (Duration/Distance)
        $(document).on('input', '.setsdatatable input[type="text"]:not(.allow-decimal)', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Running type switch (min = int only, km = allow .5)
            $(document).on('change', '.running-type', function() {
                const row = $(this).closest('tr');
                const runningValue = row.find('.running-value input');
                const runningUnit = row.find('.running-unit');

                if ($(this).val() === '1') {
                    runningUnit.text('min');
                    runningValue.removeClass('allow-decimal');
                    runningValue.val('');
                } else {
                    runningUnit.text('km');
                    runningValue.addClass('allow-decimal');
                    runningValue.val('');
                }
            });

            // Enforce input in running fields
            $(document).on('input', '.running-value input', function() {
                let val = this.value;

                if ($(this).hasClass('allow-decimal')) {
                    val = val.replace(/[^0-9.]/g, '');
                    val = val.replace(/(\..*)\./g, '$1');
                    val = val.replace(/^0+(?!\.)/, '');

                    if (val.includes('.')) {
                        let parts = val.split('.');
                        parts[1] = parts[1].substring(0, 1);
                        val = parts[0] + '.' + parts[1];
                    }
                } else {
                    val = val.replace(/[^0-9]/g, '');
                }

                this.value = val;
            });

        // Ensure unit reflects current selection on page load for edit mode
        $('.running-type').each(function() {
            const runningValue = $(this).closest('tr').find('.running-value');
            const runningUnit = runningValue.find('.running-unit');
            if ($(this).val() === '1') {
                runningUnit.text('min');
            } else {
                runningUnit.text('km');
            }
        });

        // Add exercise block for current visible day
        $(document).on('click', '.add-exercise-btn', function(e) {
            e.preventDefault();
            const dayItem = $(this).closest('.day-item');
            const container = dayItem.find('.exercise-box-block-row');
            let template = container.find('.exercise-box-block').first();

            // If no template exists (empty state), create a basic structure
            if (template.length === 0) {
                const basicExerciseBlock = `
                    <div class="exercise-box-block p-3 rounded-3 bg-white">
                        <input type="hidden" name="day_id" value="${dayItem.data('day')}">

                        <div class="form-group mb-3">
                            <label for="exercise_id" class="form-label">Exercise Title</label>
                            <div class="position-relative w-100">
                                <select name="exercise_id[]" class="form-control form-select exercise-select" required>
                                    <option value="">Select Exercise</option>
                                    <?php $__currentLoopData = $exercises; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exercise): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($exercise->id); ?>" data-type="<?php echo e($exercise->type ?? 'normal'); ?>">
                                            <?php echo e($exercise->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>

                        <div class="row setcolswidh">
                            <div class="col-xxl-8 col-xl-12">
                                <div class="d-block w-full mb-3">
                                    <label for="" class="text-14 font-400">Select Level</label>
                                    <div class="pt-1">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="level_${Date.now()}" value="1" checked>
                                            <label class="form-check-label">Beginner</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="level_${Date.now()}" value="2">
                                            <label class="form-check-label">Intermediate</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="level_${Date.now()}" value="3">
                                            <label class="form-check-label">Advanced</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Normal Exercise Table -->
                                <div class="d-block w-full mb-3 p-2 setsformbox rounded-3 normal-exercise">
                                    <table class="setsdatatable">
                                        <thead>
                                            <tr>
                                                <th width="130px">Set</th>
                                                <th width="130px">Reps</th>
                                                <th>RPE</th>
                                                <th width="5%">Rest (seconds)</th>
                                                <th width="50px">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Empty - will be populated when exercise is selected -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Running Exercise Table -->
                                <div class="d-block w-full mb-3 p-2 setsformbox rounded-3 running-exercise" style="display: none !important;">
                                    <table class="setsdatatable">
                                        <thead>
                                            <tr>
                                                <th width="130px">Set</th>
                                                <th width="130px">Duration/Distance</th>
                                                <th>RPE</th>
                                                <th width="5%">Walk (seconds)</th>
                                                <th width="50px">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Empty - will be populated when running exercise is selected -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Media Upload Section -->
                            <div class="col-xxl-4 col-xl-12">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="form-label">Cover&nbsp;Image</label>
                                            <div class="customuploadfile">
                                                <input type="file" class="cover-image-input" accept="image/png, image/jpeg, image/jpg, image/webp" />
                                                <label>
                                                    <span class="filesizebox"><img src="<?php echo e(asset('assets/images/uploadfile.svg')); ?>" alt="image" /></span>
                                                    <span>Upload file</span>
                                                </label>
                                            </div>
                                            <div class="uploadedfiledisplay d-none">
                                                <span class="filesizebox">
                                                    <img src="<?php echo e(asset('assets/images/login-left-img.png')); ?>" alt="image" />
                                                </span>
                                                <span class="deletelinkbtn">Delete</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="form-label">GIF&nbsp;Image</label>
                                            <div class="customuploadfile">
                                                <input type="file" class="gif-image-input" accept="image/gif" />
                                                <label>
                                                    <span class="filesizebox"><img src="<?php echo e(asset('assets/images/uploadfile.svg')); ?>" alt="image" /></span>
                                                    <span>Upload file</span>
                                                </label>
                                            </div>
                                            <div class="uploadedfiledisplay d-none">
                                                <span class="filesizebox">
                                                    <img src="<?php echo e(asset('assets/images/login-left-img.png')); ?>" alt="image" />
                                                </span>
                                                <span class="deletelinkbtn">Delete</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="form-label">Video</label>
                                            <div class="customuploadfile">
                                                <input type="file" class="video-input" accept="video/*" />
                                                <label>
                                                    <span class="filesizebox"><img src="<?php echo e(asset('assets/images/uploadfile.svg')); ?>" alt="image" /></span>
                                                    <span>Upload file</span>
                                                </label>
                                            </div>
                                            <div class="uploadedfiledisplay d-none">
                                                <span class="filesizebox isvideobg">
                                                    <img src="<?php echo e(asset('assets/images/login-left-img.png')); ?>" alt="image" />
                                                </span>
                                                <span class="deletelinkbtn">Delete</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="#" class="deletelinkbtn mt-3 delete-exercise-btn">Delete exercise</a>
                    </div>
                `;
                container.prepend(basicExerciseBlock);
                $(this).text('Add More Exercise');
                return;
            }

            const clone = template.clone(false);
            // Reset values in clone
            clone.find('.exercise-select').val('');
            clone.find('.ff-error, .error-message').remove();  
            clone.find('.ff-rel').removeClass('ff-rel');       
            
            // Assign unique radio group so selecting in this clone doesn't uncheck others
            const uniqueRadioName =
                `level_day${dayItem.data('day')}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
            clone.find('input[type="radio"]').each(function() {
                $(this).attr('name', uniqueRadioName);
                $(this).prop('checked', false);
            });
            // Ensure clones don't carry existing workout_id
            clone.find('input[name="workout_id"]').remove();
            // Inject one default normal set row so inputs are visible immediately
            const defaultNormalRow = `
            <tr>
                <td valign="top">
                    <select class="form-control form-select" name="set_number[]">
                        <?php for($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo e($i); ?>" <?php echo e($i == 1 ? 'selected' : ''); ?>><?php echo e($i); ?></option>
                        <?php endfor; ?>
                    </select>
                </td>
                <td valign="top">
                    <select class="form-control form-select" name="reps[]">
                        <?php for($i = 1; $i <= 30; $i++): ?>
                            <option value="<?php echo e($i); ?>" <?php echo e($i == 1 ? 'selected' : ''); ?>><?php echo e($i); ?></option>
                        <?php endfor; ?>
                    </select>
                </td>
                <td valign="top">
                    <div class="d-flex gap-1 rpe-container">
                        <div class="flex-grow-1">
                            <select class="form-control form-select rpe-select" name="rpe[]">
                                <?php for($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo e($i); ?>" <?php echo e($i == 1 ? 'selected' : ''); ?>><?php echo e($i); ?></option>
                                <?php endfor; ?>
                                <option value="0">Other</option>
                            </select>
                        </div>
                        <div class="input-group mb-3 rpe-percentage-input" style="width: 70px; display: none;">
                            <input type="text" class="form-control text-center px-1" aria-label="RPE Percentage" aria-describedby="basic-addon1" name="rpe_percentage[]" value="">
                            <span class="input-group-text px-2" id="basic-addon1">%</span>
                        </div>
                    </div>
                </td>
                <td valign="top">
                    <input type="text" class="form-control" name="rest[]" value="" />
                </td>
                <td valign="top">
                    <button class="btn btn-outline-primary add-set-btn">+</button>
                </td>
            </tr>`;

            clone.find('.normal-exercise tbody').html(defaultNormalRow);
            clone.find('.running-exercise tbody').html('');
            clone.find('.normal-exercise').attr('style', 'display: block !important');
            clone.find('.running-exercise').attr('style', 'display: none !important');

            // Default Beginner selected in new clone
            clone.find('input[type="radio"][value="1"]').first().prop('checked', true);

            // Reset uploads
            clone.find('.cover-image-input, .gif-image-input, .video-input').val('');
            clone.find('.uploadedfiledisplay').addClass('d-none');
            clone.find('.customuploadfile').show();

            // Add delete link only for newly created clones
            if (clone.find('.delete-exercise-btn').length === 0) {
                clone.append(
                    '<a href="#" class="deletelinkbtn mt-3 delete-exercise-btn">Delete exercise</a>'
                );
            }
            $(this).before(clone);
            $(this).text('Add More Exercise');
        });

        // Delete exercise block
        $(document).on('click', '.delete-exercise-btn', function(e) {
            e.preventDefault();
            const container = $(this).closest('.exercise-box-block-row');
            $(this).closest('.exercise-box-block').remove();
            if (container.find('.exercise-box-block').length === 0) {
                container.find('.add-exercise-btn').text('Add Exercise');
            }
        });

        // Add set to normal exercise
        $(document).on('click', '.add-set-btn', function() {
            const setTable = $(this).closest('table');
            const rowCount = setTable.find('tbody tr').length;
            const newRow = `
            <tr>
                <td valign="top">
                    <select class="form-control form-select" name="set_number[]">
                        <?php for($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo e($i); ?>"><?php echo e($i); ?></option>
                        <?php endfor; ?>
                    </select>
                </td>
                <td valign="top">
                    <select class="form-control form-select" name="reps[]">
                        <?php for($i = 1; $i <= 30; $i++): ?>
                            <option value="<?php echo e($i); ?>"><?php echo e($i); ?></option>
                        <?php endfor; ?>
                    </select>
                </td>
                <td valign="top">
                    <div class="d-flex gap-1 rpe-container">
                        <div class="flex-grow-1">
                            <select class="form-control form-select rpe-select" name="rpe[]">
                                <?php for($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo e($i); ?>"><?php echo e($i); ?></option>
                                <?php endfor; ?>
                                <option value="0">Other</option>
                            </select>
                        </div>
                        <div class="input-group mb-3 rpe-percentage-input" style="width: 70px; display: none;">
                            <input type="text" class="form-control text-center px-1" aria-label="RPE Percentage" aria-describedby="basic-addon1" name="rpe_percentage[]">
                            <span class="input-group-text px-2" id="basic-addon1">%</span>
                        </div>
                    </div>
                </td>
                <td valign="top">
                    <input type="text" class="form-control" name="rest[]" value="" />
                </td>
                <td valign="top">
                    <button class="btn btn-outline-primary remove-set-btn">-</button>
                </td>
            </tr>
        `;
            setTable.find('tbody').append(newRow);
        });

        // Add set to running exercise
        $(document).on('click', '.add-running-set-btn', function() {
            const setTable = $(this).closest('table');
            const newRow = `
            <tr>
                <td valign="top">
                    <select class="form-control form-select running-type" name="running_type[]">
                        <option value="1">Duration</option>
                        <option value="2">Distance</option>
                    </select>
                </td>
                <td valign="top">
                    <div class="input-group w-100 mb-3 running-value">
                        <input type="text" class="form-control text-center px-1" aria-label="Run value" value="" name="running_value[]">
                        <span class="input-group-text px-2 running-unit" style="width:40px;">min</span>
                    </div>
                </td>
                <td valign="top">
                    <div class="d-flex gap-1 rpe-container">
                        <div class="flex-grow-1">
                            <select class="form-control form-select rpe-select" name="rpe[]">
                                <?php for($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo e($i); ?>"><?php echo e($i); ?></option>
                                <?php endfor; ?>
                                <option value="0">Other</option>
                            </select>
                        </div>
                        <div class="input-group mb-3 rpe-percentage-input" style="width: 70px; display: none;">
                            <input type="text" class="form-control text-center px-1" aria-label="RPE Percentage" aria-describedby="basic-addon1" name="rpe_percentage[]">
                            <span class="input-group-text px-2" id="basic-addon1">%</span>
                        </div>
                    </div>
                </td>
                <td valign="top">
                    <input type="text" class="form-control" name="walk[]" value="" />
                </td>
                <td valign="top">
                    <button class="btn btn-outline-primary remove-set-btn">-</button>
                </td>
            </tr>
        `;
            setTable.find('tbody').append(newRow);
        });

        // Remove set from exercise
        $(document).on('click', '.remove-set-btn', function() {
            const setTable = $(this).closest('table');
            $(this).closest('tr').remove();
        });

        // Handle RPE selection change
        $(document).on('change', '.rpe-select', function() {
            const rpeContainer = $(this).closest('.rpe-container');
            const percentageInput = rpeContainer.find('.rpe-percentage-input');

            if ($(this).val() === '0') {
                percentageInput.show();
            } else {
                percentageInput.hide();
            }
        });

        // Handle file upload clicks - trigger file input
        $(document).on('click', '.customuploadfile label', function(e) {
            e.preventDefault();
            $(this).siblings('input[type="file"]').click();
        });

        // Handle file upload display
        $(document).on('change', '.cover-image-input, .gif-image-input, .video-input', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const displayDiv = $(this).closest('.form-group').find('.uploadedfiledisplay');
                const customUpload = $(this).closest('.customuploadfile');
                const fileBox = displayDiv.find('.filesizebox');

                // Clear existing content
                fileBox.empty();

                if (file.type.startsWith('image/')) {
                    // Image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = $('<img>')
                            .attr('src', e.target.result)
                            .css({
                                'width': '100%',
                                'height': '100%',
                                'object-fit': 'cover',
                                'border-radius': '4px'
                            });
                        fileBox.append(img);
                    };
                    reader.readAsDataURL(file);
                } else if (file.type.includes('video/')) {
                    // Video
                    const video = $('<video>')
                        .attr('src', URL.createObjectURL(file))
                        .prop('controls', true)
                        .css({
                            'width': '100%',
                            'height': '100%',
                            'object-fit': 'cover',
                            'border-radius': '4px'
                        });
                    fileBox.append(video);
                }

                // Hide the upload area and show the uploaded file display
                customUpload.hide();
                displayDiv.removeClass('d-none');

                // Make preview open in a new tab
                fileBox.off('click').on('click', function() {
                    window.open(URL.createObjectURL(file), '_blank');
                });
            }
        });

        // Handle file delete
        $(document).on('click', '.uploadedfiledisplay .deletelinkbtn', function(e) {
            e.preventDefault();
            const displayDiv = $(this).closest('.uploadedfiledisplay');
            const customUpload = displayDiv.siblings('.customuploadfile');

            // Check media type from data attribute
            const type = $(this).data('type'); // e.g. "image" / "gif" / "video"

            if (type) {
                displayDiv.siblings(`.delete_${type}`).val('1'); // mark for deletion
            }

            // Hide uploaded display and show upload area
            displayDiv.addClass('d-none');
            customUpload.show();
            customUpload.find('input').val(''); // clear input
        });


        // Open uploaded video in a new tab when clicked (both existing and newly uploaded previews)
        $(document).on('click', '.uploadedfiledisplay video', function(e) {
            // Avoid toggling play and instead open source in new tab
            e.preventDefault();
            e.stopPropagation();
            const sourceTag = $(this).find('source');
            const videoSrc = sourceTag.length ? sourceTag.attr('src') : $(this).attr('src');
            if (videoSrc) {
                window.open(videoSrc, '_blank');
            }
        });

        // Fallback: clicking the video container opens the anchor's href in a new tab for existing uploads
        $(document).on('click', '.uploadedfiledisplay .filesizebox.isvideobg', function(e) {
            const anchor = $(this).find('a');
            const href = anchor.attr('href');
            if (href) {
                e.preventDefault();
                window.open(href, '_blank');
            }
        });

        // Enforce numeric input
        // Generic: allow digits and one dot (for decimal) – will be narrowed below per-field
        // $(document).on('input', 'input[type="text"]', function() {
        //     this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        // });

        // // In sets table: default to integers only, except fields that explicitly allow decimals
        // $(document).on('input', '.setsdatatable input[type="text"]:not(.allow-decimal)', function() {
        //     this.value = this.value.replace(/[^0-9]/g, '');
        // });

        // // Running distance/duration should allow decimals
        // $(document).on('input', '.running-value input', function() {
        //     $(this).addClass('allow-decimal');
        //     this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        // });

        // Submit form
        $(document).on('click', '.submit-btn', function(e) {
            e.preventDefault();

            // Validate form
            let isValid = true;
            $('.error-message, .ff-error').remove();

            // Validate sets in visible exercise blocks
            $('.exercise-box-block').each(function() {
                const block = $(this);
                const exerciseSelect = block.find('.exercise-select');
                const exerciseId = exerciseSelect.val();

                // Check if exercise is selected
                if (!exerciseId || isNaN(exerciseId)) {
                    isValid = false;
                    const wrap = exerciseSelect.closest('.exercise-select-block');
                    wrap.addClass('ff-rel');
                    wrap.append(
                        '<div class="error-message text-danger mt-2">Please select an exercise</div>'
                    );
                    return; // skip further validation for this block
                }

                const isRunningExercise =
                    parseInt(exerciseId) === parseInt(RUNNING_EXERCISE_ID) ||
                    exerciseSelect.find("option:selected").data("type") === "running";

                // Running rows
                if (isRunningExercise) {
                    block.find('.running-exercise:visible tbody tr').each(function() {
                        const row = $(this);
                        const runType = row.find('.running-type')
                            .val(); // 1 = Duration, 2 = Distance
                        const runVal = row.find('.running-value input');
                        const walkVal = row.find('input[name="walk[]"]');
                        const rpeSelect = row.find('.rpe-select');
                        const rpePct = row.find('.rpe-percentage-input input');

                        // Clear previous errors for this row
                        row.find('.ff-error').remove();

                        // running value validation based on type
                        const rv = runVal.val();
                        if (runType === '1') { // Duration
                            if (!runVal.val() || isNaN(runVal.val()) || runVal.val() <=
                                0 || runVal.val() > 120) {
                                isValid = false;
                                const wrap = runVal.closest('.running-value');
                                wrap.addClass('ff-rel');
                                wrap.find('.ff-error').remove();
                                wrap.append(
                                    '<div class="ff-error">Duration must be 1–120 minutes</div>'
                                );
                            }
                        } else { // Distance
                            if (!runVal.val() || isNaN(runVal.val()) || runVal.val() <=
                                0 || runVal.val() > 50) {
                                isValid = false;
                                const wrap = runVal.closest('.running-value');
                                wrap.addClass('ff-rel');
                                wrap.find('.ff-error').remove();
                                wrap.append(
                                    '<div class="ff-error">Distance must be 1–50 KM</div>'
                                );
                            }
                        }

                        // walk validation (1-7200 seconds)
                        if (walkVal.val() === '' || !/^\d+$/.test(walkVal.val())) {
                            // Empty or not a number
                            isValid = false;
                            const wrap = walkVal.closest('td');
                            wrap.addClass('ff-rel');
                            wrap.append(
                                '<div class="ff-error" style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">Walk is required</div>'
                            );
                        } else if (parseInt(walkVal.val(), 10) < 0 || parseInt(walkVal
                                .val(), 10) > 7200) {
                            // Out of range (0 allowed now)
                            isValid = false;
                            const wrap = walkVal.closest('td');
                            wrap.addClass('ff-rel');
                            wrap.append(
                                '<div class="ff-error" style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">Walk must be 0–7200 seconds</div>'
                            );
                        }


                        // RPE validation
                        if (rpeSelect.val() === '0') {
                            if (!rpePct.val() || !/^\d+$/.test(rpePct.val())) {
                                isValid = false;
                                const wrap = rpePct.closest('.rpe-percentage-input');
                                wrap.addClass('ff-rel');
                                wrap.append(
                                    '<div class="ff-error" style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">Required</div>'
                                );
                            } else if (rpePct.val() <= 0 || rpePct.val() > 100) {
                                isValid = false;
                                const wrap = rpePct.closest('.rpe-percentage-input');
                                wrap.addClass('ff-rel');
                                wrap.append(
                                    '<div class="ff-error" style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">RPE must be 1–100%</div>'
                                );
                            }
                        }
                    });
                } else {
                    // Normal rows
                    block.find('.normal-exercise:visible tbody tr').each(function() {
                        const row = $(this);
                        const restVal = row.find('input[name="rest[]"]');
                        const rpeSelect = row.find('.rpe-select');
                        const rpePct = row.find('.rpe-percentage-input input');

                        // Clear previous errors for this row
                        row.find('.ff-error').remove();

                        // rest validation (1-7200 seconds)
                        if (restVal.val() === '' || !/^\d+$/.test(restVal.val())) {
                            // Empty or not a number
                            isValid = false;
                            const wrap = restVal.closest('td');
                            wrap.addClass('ff-rel');
                            wrap.append(
                                '<div class="ff-error" style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">Rest is required</div>'
                            );
                        } else if (parseInt(restVal.val(), 10) < 0 || parseInt(restVal
                                .val(), 10) > 7200) {
                            // Out of range (0 allowed now)
                            isValid = false;
                            const wrap = restVal.closest('td');
                            wrap.addClass('ff-rel');
                            wrap.append(
                                '<div class="ff-error" style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">Rest must be 0–7200 seconds</div>'
                            );
                        }


                        // RPE validation
                        if (rpeSelect.val() === '0') {
                            if (!rpePct.val() || !/^\d+$/.test(rpePct.val())) {
                                isValid = false;
                                const wrap = rpePct.closest('.rpe-percentage-input');
                                wrap.addClass('ff-rel');
                                wrap.append(
                                    '<div class="ff-error" style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">Required</div>'
                                );
                            } else if (rpePct.val() <= 0 || rpePct.val() > 100) {
                                isValid = false;
                                const wrap = rpePct.closest('.rpe-percentage-input');
                                wrap.addClass('ff-rel');
                                wrap.append(
                                    '<div class="ff-error" style="display: block; margin-top: 5px; color: #dc3545; font-size: 12px;">RPE must be 1–100%</div>'
                                );
                            }
                        }
                    });
                }
            const imageInput = block.find('.cover-image-input')[0];
            const gifInput = block.find('.gif-image-input')[0];
            const videoInput = block.find('.video-input')[0];

            // Allowed extensions
            const imageExts = ['jpg', 'jpeg', 'png', 'webp'];
            const gifExts = ['gif'];
            const videoExts = ['mp4', 'mov', 'avi', 'mkv', 'webm'];

            function validateFile(input, allowedExts, errorMsg) {
                if (input && input.files.length > 0) {
                    const file = input.files[0];
                    const ext = file.name.split('.').pop().toLowerCase();

                    const formGroup = $(input).closest('.form-group');
                    formGroup.find('.ff-error').remove();

                    if (!allowedExts.includes(ext)) {
                        isValid = false;
                        formGroup.append(
                            `<div class="ff-error" style="color:#dc3545;font-size:12px;">${errorMsg}</div>`
                        );
                    }
                }
            }


            validateFile(imageInput, imageExts, "Cover image must be JPG, JPEG, PNG, or WEBP");
            validateFile(gifInput, gifExts, "GIF image must be a .gif file");
            validateFile(videoInput, videoExts, "Video must be MP4, MOV, AVI, MKV, or WEBM");

            });

            if (!isValid) return;

            // Rest of your AJAX submission code remains the same...
            // Serialize form data for multiple exercises in current day
            const formData = new FormData();
            const workoutId = $('input[name="workout_id"]').val();
            const workoutFrequency = $('input[name="workout_frequency"]').val() || $(
                'input[name="workout_frequency"]:checked').val();
            const meso = $('input[name="meso"]').val() || $('input[name="meso"]:checked').val();
            const week = $('input[name="week"]').val() || $('input[name="week"]:checked').val();
            const dayId = $('input[name="day_id"]').val();

            formData.append('workout_id', workoutId);
            if (workoutFrequency) formData.append('workout_frequency', workoutFrequency);
            if (meso) formData.append('meso', meso);
            if (week) formData.append('week', week);
            if (dayId) formData.append('day_id', dayId);

            const exercisesMeta = [];

            // Only collect data from visible exercise blocks on the current day
            const currentDayItem = $('.day-item[data-day="' + dayId + '"]:visible');
            const exerciseBlocks = currentDayItem.find('.exercise-box-block');

            exerciseBlocks.each(function(index) {
                const exerciseId = $(this).find('.exercise-select').val();
                const exerciseType = $(this).find('.exercise-select option:selected').data(
                    'type');
                const levelVal = $(this).find(`input[name^="level_day${dayId}"]:checked`)
                    .val() ||
                    $(this).find('input[type="radio"]:checked').val();
                const sets = [];

                const isRunningExercise = parseInt(exerciseId) === RUNNING_EXERCISE_ID ||
                    exerciseType === 'running';

                if (!isRunningExercise) {
                    $(this).find('.normal-exercise:visible tbody tr').each(function() {
                        const setId = $(this).find('input[name="set_id[]"]').val() ||
                            null;
                        const setNumber = $(this).find('select[name="set_number[]"]')
                            .val();
                        const reps = $(this).find('select[name="reps[]"]').val();
                        const rpe = $(this).find('.rpe-select').val();
                        const rpePercentage = rpe === '0' ? $(this).find(
                            '.rpe-percentage-input input').val() : '';
                        const rest = $(this).find('input[name="rest[]"]').val();
                        sets.push({
                            id: setId,
                            set_number: setNumber,
                            reps: reps,
                            rpe: rpe,
                            rpePercentage: rpePercentage,
                            rest: rest
                        });
                    });
                } else {
                    $(this).find('.running-exercise:visible tbody tr').each(function(idx) {
                        const setId = $(this).find('input[name="set_id[]"]').val() ||
                            null;
                        const runningType = $(this).find('.running-type').val();
                        const runningValue = $(this).find('.running-value input').val();
                        const rpe = $(this).find('.rpe-select').val();
                        const rpePercentage = rpe === '0' ? $(this).find(
                            '.rpe-percentage-input input').val() : '';
                        const walk = $(this).find('input[name="walk[]"]').val();
                        sets.push({
                            id: setId,
                            set_number: idx + 1,
                            reps: runningValue,
                            rpe: rpe,
                            rpePercentage: rpePercentage,
                            rest: walk,
                            running_type: runningType
                        });
                    });
                }

                exercisesMeta.push({
                    exercise_id: exerciseId,
                    level: levelVal,
                    sets: sets
                });

                // Handle files for this exercise
                const imageInput = $(this).find('.cover-image-input')[0];
                const gifInput = $(this).find('.gif-image-input')[0];
                const videoInput = $(this).find('.video-input')[0];

                const deleteImage = $(this).find('.delete_image').val();
                const deleteGif = $(this).find('.delete_gif').val();
                const deleteVideo = $(this).find('.delete_video').val();

                formData.append(`days[${dayId}][exercises][${index}][delete_image]`, deleteImage);
                formData.append(`days[${dayId}][exercises][${index}][delete_gif]`, deleteGif);
                formData.append(`days[${dayId}][exercises][${index}][delete_video]`, deleteVideo);


                if (imageInput && imageInput.files.length > 0) {
                    formData.append(`days[${dayId}][exercises][${index}][image]`, imageInput
                        .files[0]);
                }
                if (gifInput && gifInput.files.length > 0) {
                    formData.append(`days[${dayId}][exercises][${index}][gif]`, gifInput.files[
                        0]);
                }
                if (videoInput && videoInput.files.length > 0) {
                    formData.append(`days[${dayId}][exercises][${index}][video]`, videoInput
                        .files[0]);
                }
            });

            formData.append(`days[${dayId}][meta]`, JSON.stringify({
                exercises: exercisesMeta
            }));
            // Submit the form via AJAX
            $.ajax({
                url: "<?php echo e(route('admin.exerciseUpdate')); ?>",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        localStorage.setItem('toastrMessage', response.message);
                        window.location.href = response.redirect_url;
                    } else {
                        if (response.redirect_url) {
                            localStorage.setItem('toastrError', response.message);
                            window.location.href = response.redirect_url;
                        } else {
                            toastr.error(response.message);
                        }
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error updating exercise. Please try again.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.redirect_url) {
                            localStorage.setItem('toastrError', errorMessage);
                            window.location.href = xhr.responseJSON.redirect_url;
                            return;
                        }
                    }
                    toastr.error(errorMessage);
                    console.error(xhr.responseText);
                }
            });
        });
    });
    // Handle level label clicks to check radio buttons
    $(document).on('click', '.form-check-label', function(e) {
        // Only handle labels that are for level radio buttons
        const input = $(this).prev('.form-check-input');
        if (input.attr('name') && input.attr('name').includes('level')) {
            e.preventDefault();
            input.prop('checked', true);
        }
    });
    $(document).on("input", ".rpe-percentage-input input", function() {
        let value = $(this).val().replace(/^0+(?=\d)/, "");
        let num = parseInt(value, 10);

        if (isNaN(num) || num < 0 || num > 100) {
            num = '';
        }
        $(this).val(num);
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/exercise/edit.blade.php ENDPATH**/ ?>