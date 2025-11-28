@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Add Exercise')
@push('styles')
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
@endpush

<div class="container-fluid">
    <div class="pate-content-wrapper">
        <div class="page-title-row align-items-center">
            <a href="{{ route('admin.exerciseIndex') }}" class="btn btn-link p-2 py-1 me-2 bg-transparent">
                <img src="{{ asset('assets/images/backbutton.svg') }}" alt="image">
            </a>
            <h5 class="mb-0">Add Exercise</h5>
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
                                        <label for="" class="text-14 font-400">Workout Frequency</label>
                                        <div class="pt-1">
                                            @foreach ($workoutFrequencies as $key => $workoutFrequency)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                        name="workout_frequency"
                                                        @if ($key == 0) checked @endif
                                                        id="{{ $workoutFrequency->days_in_week }}"
                                                        value="{{ $workoutFrequency->id }} ">
                                                    <label class="form-check-label"
                                                        for="{{ $workoutFrequency->days_in_week }}">{{ $workoutFrequency->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <!-- Meso -->
                                <div class="col-sm-12 col-lg-4">
                                    <div class="planfreq">
                                        <label for="" class="text-14 font-400">Meso</label>
                                        <div class="pt-1" id="meso-container">
                                            @forelse($mesos as $index => $meso)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="meso"
                                                        id="meso{{ $meso->id }}" value="{{ $meso->id }}">
                                                    <label class="form-check-label" for="meso{{ $meso->id }}">
                                                        {{ $meso->name ?? 'Meso ' . ($index + 1) }}
                                                    </label>
                                                </div>
                                            @empty
                                                <p class="text-muted">No meso cycles available</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <!-- Week -->
                                <div class="col-sm-12 col-lg-4">
                                    <div class="planfreq">
                                        <label for="" class="text-14 font-400">Week</label>
                                        <div class="pt-1" id="week-container">
                                            @for ($i = 1; $i <= 4; $i++)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input week-radio" type="radio"
                                                        name="week" id="week{{ $i }}"
                                                        value="{{ $i }}" {{ $i === 1 ? '' : '' }}>
                                                    <label class="form-check-label" for="week{{ $i }}">
                                                        Week {{ $i }}
                                                    </label>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-12 mt-4">
                            <h5 class="pb-1">Exercise Plan</h5>
                            <div class="accordion darkaccordian w-100" id="daysAccordion">
                                <!-- Day 1 -->
                                <div class="accordion-item day-item" data-day="1">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseOne" aria-expanded="true"
                                            aria-controls="collapseOne">
                                            Day 1
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse show"
                                        aria-labelledby="headingOne" data-bs-parent="#daysAccordion">
                                        <div class="accordion-body pt-0">
                                            <div class="exercise-box-block-row w-full d-flex flex-column gap-3">
                                                <!-- exercise block -->
                                                <div class="exercise-box-block p-3 rounded-3 bg-white">
                                                    <div class="form-group mb-3">
                                                        <label for="exercise_id" class="form-label">Exercise
                                                            Title</label>
                                                        <div class="position-relative w-100">
                                                            <select name="exercise_id[]"
                                                                class="form-control form-select exercise-select"
                                                                required>
                                                                <option value="">Select Exercise</option>
                                                                @foreach ($exercises as $exercise)
                                                                    <option value="{{ $exercise->id }}"
                                                                        data-type="{{ $exercise->type ?? 'normal' }}">
                                                                        {{ $exercise->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row setcolswidh">
                                                        <div class="col-xxl-8 col-xl-12">
                                                            <div class="d-block w-full mb-3">
                                                                <label for="" class="text-14 font-400">Select
                                                                    Level</label>
                                                                <div class="pt-1">
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="level_day1[]" id="Beginner_day1"
                                                                            value="1" checked>
                                                                        <label class="form-check-label"
                                                                            for="Beginner_day1">Beginner</label>
                                                                    </div>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="level_day1[]" id="Intermediate_day1"
                                                                            value="2">
                                                                        <label class="form-check-label"
                                                                            for="Intermediate_day1">Intermediate</label>
                                                                    </div>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="level_day1[]" id="Advanced_day1"
                                                                            value="3">
                                                                        <label class="form-check-label"
                                                                            for="Advanced_day1">Advanced</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="d-block w-full mb-3 p-2 setsformbox rounded-3 normal-exercise">
                                                                <table class="setsdatatable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Set</th>
                                                                            <th>Reps</th>
                                                                            <th>RPE</th>
                                                                            <th>Rest&nbsp;(seconds)</th>
                                                                            <th width="50px">&nbsp;</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td valign="top">
                                                                                <select
                                                                                    class="form-control form-select">
                                                                                    @for ($i = 1; $i <= 10; $i++)
                                                                                        <option
                                                                                            value="{{ $i }}">
                                                                                            {{ $i }}
                                                                                        </option>
                                                                                    @endfor
                                                                                </select>
                                                                            </td>
                                                                            <td valign="top">
                                                                                <select
                                                                                    class="form-control form-select">
                                                                                    @for ($i = 1; $i <= 30; $i++)
                                                                                        <option
                                                                                            value="{{ $i }}">
                                                                                            {{ $i }}
                                                                                        </option>
                                                                                    @endfor
                                                                                </select>
                                                                            </td>
                                                                            <td valign="top" class="rpevaluebox">
                                                                                <div
                                                                                    class="d-flex gap-1 rpe-container">
                                                                                    <div class="flex-grow-1">
                                                                                        <select
                                                                                            class="form-control form-select rpe-select">
                                                                                            @for ($i = 1; $i <= 10; $i++)
                                                                                                <option
                                                                                                    value="{{ $i }}">
                                                                                                    {{ $i }}
                                                                                                </option>
                                                                                            @endfor
                                                                                            <option value="0">
                                                                                                Other
                                                                                            </option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="input-group rpe-percentage-input"
                                                                                        style="width: 70px; display: none;">
                                                                                        <input type="text"
                                                                                            class="form-control text-center px-1"
                                                                                            aria-label="RPE Percentage"
                                                                                            aria-describedby="basic-addon1">
                                                                                        <span
                                                                                            class="input-group-text px-2"
                                                                                            id="basic-addon1">%</span>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td valign="top">
                                                                                <input type="text"
                                                                                    class="form-control"
                                                                                    value="" />
                                                                            </td>
                                                                            <td valign="top">
                                                                                <button
                                                                                    class="btn btn-outline-primary add-set-btn">+</button>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="d-block w-full mb-3 p-2 setsformbox rounded-3 running-exercise"
                                                                style="display: none!important;">
                                                                <table class="setsdatatable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Set</th>
                                                                            <th>Run</th>
                                                                            <th>RPE</th>
                                                                            <th>Walk&nbsp;(seconds)</th>
                                                                            <th width="50px">&nbsp;</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td valign="top">
                                                                                <select
                                                                                    class="form-control form-select running-type">
                                                                                    <option value="1">Duration
                                                                                    </option>
                                                                                    <option value="2">Distance
                                                                                    </option>
                                                                                </select>
                                                                            </td>
                                                                            <td valign="top">
                                                                                <div
                                                                                    class="input-group w-100 mb-3 running-value">
                                                                                    <input type="text"
                                                                                        class="form-control text-center px-1"
                                                                                        aria-label="Run value"
                                                                                        value="">
                                                                                    <span
                                                                                        class="input-group-text px-2 running-unit"
                                                                                        style="width:40px;">min</span>
                                                                                </div>
                                                                            </td>
                                                                            <td valign="top">
                                                                                <div
                                                                                    class="d-flex gap-1 rpe-container">
                                                                                    <div class="flex-grow-1">
                                                                                        <select
                                                                                            class="form-control form-select rpe-select">
                                                                                            @for ($i = 1; $i <= 10; $i++)
                                                                                                <option
                                                                                                    value="{{ $i }}">
                                                                                                    {{ $i }}
                                                                                                </option>
                                                                                            @endfor
                                                                                            <option value="0">
                                                                                                Other
                                                                                            </option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="input-group rpe-percentage-input"
                                                                                        style="width: 70px; display: none;">
                                                                                        <input type="text"
                                                                                            class="form-control text-center px-1"
                                                                                            aria-label="RPE Percentage"
                                                                                            aria-describedby="basic-addon1">
                                                                                        <span
                                                                                            class="input-group-text px-2"
                                                                                            id="basic-addon1">%</span>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td valign="top">
                                                                                <input type="text"
                                                                                    class="form-control"
                                                                                    value="" />
                                                                            </td>
                                                                            <td valign="top">
                                                                                <button
                                                                                    class="btn btn-outline-primary add-running-set-btn">+</button>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-4 col-xl-12">
                                                            <div class="row">
                                                                <div class="col-sm-4">
                                                                    <div class="form-group">
                                                                        <label
                                                                            class="form-label">Cover&nbsp;Image</label>
                                                                        <div class="customuploadfile">
                                                                            <input type="file"
                                                                                class="cover-image-input"
                                                                                accept="image/png, image/jpeg, image/jpg, image/webp" />

                                                                            <label>
                                                                                <span class="filesizebox"><img
                                                                                        src="{{ asset('assets/images/uploadfile.svg') }}"
                                                                                        alt="image" /></span>
                                                                                <span>Upload file</span>
                                                                            </label>
                                                                        </div>
                                                                        <div class="uploadedfiledisplay d-none">
                                                                            <span class="filesizebox"><img
                                                                                    src="{{ asset('assets/images/login-left-img.png') }}"
                                                                                    alt="image" /></span>
                                                                            <span class="deletelinkbtn">Delete</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <div class="form-group">
                                                                        <label
                                                                            class="form-label">GIF&nbsp;Image</label>
                                                                        <div class="customuploadfile">
                                                                            <input type="file"
                                                                                class="gif-image-input"
                                                                                accept="image/gif" />
                                                                            <label>
                                                                                <span class="filesizebox"><img
                                                                                        src="{{ asset('assets/images/uploadfile.svg') }}"
                                                                                        alt="image" /></span>
                                                                                <span>Upload file</span>
                                                                            </label>
                                                                        </div>
                                                                        <div class="uploadedfiledisplay d-none">
                                                                            <span class="filesizebox"><img
                                                                                    src="{{ asset('assets/images/login-left-img.png') }}"
                                                                                    alt="image" /></span>
                                                                            <span class="deletelinkbtn">Delete</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <div class="form-group">
                                                                        <label class="form-label">Video</label>
                                                                        <div class="customuploadfile">
                                                                            <input type="file" class="video-input"
                                                                                accept="video/*" />
                                                                            <label>
                                                                                <span class="filesizebox"><img
                                                                                        src="{{ asset('assets/images/uploadfile.svg') }}"
                                                                                        alt="image" /></span>
                                                                                <span>Upload file</span>
                                                                            </label>
                                                                        </div>
                                                                        <div class="uploadedfiledisplay d-none">
                                                                            <span class="filesizebox isvideobg"><img
                                                                                    src="{{ asset('assets/images/login-left-img.png') }}"
                                                                                    alt="image" /></span>
                                                                            <span class="deletelinkbtn">Delete</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- exercise block end -->
                                                <button
                                                    class="btn btn-outline-primary whitebtn w-100 btn-sm add-exercise-btn">Add
                                                    More Exercise</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Day 2 -->
                                <div class="accordion-item day-item" data-day="2">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                            aria-expanded="false" aria-controls="collapseTwo">
                                            Day 2
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse"
                                        aria-labelledby="headingTwo" data-bs-parent="#daysAccordion">
                                        <div class="accordion-body pt-0">
                                            <div class="exercise-box-block-row w-full d-flex flex-column gap-3">
                                                <button
                                                    class="btn btn-outline-primary whitebtn w-100 btn-sm add-exercise-btn">Add
                                                    Exercise</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Day 3 -->
                                <div class="accordion-item day-item" data-day="3">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapseThree"
                                            aria-expanded="false" aria-controls="collapseThree">
                                            Day 3
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse"
                                        aria-labelledby="headingThree" data-bs-parent="#daysAccordion">
                                        <div class="accordion-body pt-0">
                                            <div class="exercise-box-block-row w-full d-flex flex-column gap-3">
                                                <button
                                                    class="btn btn-outline-primary whitebtn w-100 btn-sm add-exercise-btn">Add
                                                    Exercise</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Additional days will be added here -->
                            </div>
                            <div class="mt-3 w-100 text-end">
                                <a href="{{ url()->previous() }}"
                                    class="btn btn-sm btn-outline-secondary me-2">Cancel</a>
                                <button class="btn btn-primary btn-sm submit-btn">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Running exercise ID
        const RUNNING_EXERCISE_ID = + "{{ get_running_id() }}";

        // Template for a new exercise block - with default exercise added
        const exerciseBlockTemplate = (dayNumber) => `
        <div class="exercise-box-block p-3 rounded-3 bg-white">
            <div class="form-group mb-3">
                <label class="form-label">Exercise Title</label>
                <div class="position-relative w-100">
                    <select name="exercise_id[]" class="form-control form-select exercise-select" required>
                        <option value="">Select Exercise</option>
                        @foreach ($exercises as $exercise)
                            <option value="{{ $exercise->id }}" data-type="{{ $exercise->type ?? 'normal' }}">{{ $exercise->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row setcolswidh">
                <div class="col-xxl-8 col-xl-12">
                    <div class="d-block w-full mb-3">
                        <label class="text-14 font-400">Select Level</label>
                        <div class="pt-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level_day${dayNumber}_${Date.now()}" value="1" checked>
                                <label class="form-check-label">Beginner</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level_day${dayNumber}_${Date.now()}" value="2">
                                <label class="form-check-label">Intermediate</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level_day${dayNumber}_${Date.now()}" value="3">
                                <label class="form-check-label">Advanced</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-block w-full mb-3 p-2 setsformbox rounded-3 normal-exercise">
                        <table class="setsdatatable">
                            <thead>
                                <tr>
                                    <th width="130px">Set</th>
                                    <th width="130px">Reps</th>
                                    <th>RPE</th>
                                    <th width="5%">Rest&nbsp;(seconds)</th>
                                    <th width="50px">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td valign="top">
                                        <select class="form-control form-select">
                                            @for ($i = 1; $i <= 10; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </td>
                                    <td valign="top">
                                        <select class="form-control form-select">
                                            @for ($i = 1; $i <= 30; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </td>
                                    <td valign="top">
                                        <div class="d-flex gap-1 rpe-container">
                                            <div class="flex-grow-1">
                                                <select class="form-control form-select rpe-select">
                                                    @for ($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                    <option value="0">Other</option>
                                                </select>
                                            </div>
                                            <div class="input-group mb-3 rpe-percentage-input" style="width: 70px; display: none;">
                                                <input type="text" class="form-control text-center px-1" aria-label="RPE Percentage" aria-describedby="basic-addon1">
                                                <span class="input-group-text px-2" id="basic-addon1">%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td valign="top">
                                        <input type="text" class="form-control" value="" />
                                    </td>
                                    <td valign="top">
                                        <button class="btn btn-outline-primary add-set-btn">+</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-block w-full mb-3 p-2 setsformbox rounded-3 running-exercise" style="display: none!important;">
                        <table class="setsdatatable">
                            <thead>
                                <tr>
                                    <th width="130px">Set</th>
                                    <th width="130px">Run</th>
                                    <th>RPE</th>
                                    <th width="5%">Walk&nbsp;(seconds)</th>
                                    <th width="50px">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td valign="top">
                                        <select class="form-control form-select running-type">
                                            <option value="1">Duration</option>
                                            <option value="2">Distance</option>
                                        </select>
                                    </td>
                                    <td valign="top">
                                        <div class="input-group w-100 mb-3 running-value">
                                            <input type="text" class="form-control text-center px-1" aria-label="Run value" value="">
                                            <span class="input-group-text px-2 running-unit" style="width:40px;">min</span>
                                        </div>
                                    </td>
                                    <td valign="top">
                                        <div class="d-flex gap-1 rpe-container">
                                            <div class="flex-grow-1">
                                                <select class="form-control form-select rpe-select">
                                                    @for ($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                    <option value="0">Other</option>
                                                </select>
                                            </div>
                                            <div class="input-group mb-3 rpe-percentage-input" style="width: 70px; display: none;">
                                                <input type="text" class="form-control text-center px-1" aria-label="RPE Percentage" aria-describedby="basic-addon1">
                                                <span class="input-group-text px-2" id="basic-addon1">%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td valign="top">
                                        <input type="text" class="form-control" value="" />
                                    </td>
                                    <td valign="top">
                                        <button class="btn btn-outline-primary add-running-set-btn">+</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-xxl-4 col-xl-12">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="form-label">Cover&nbsp;Image</label>
                                <div class="customuploadfile">
                                    <input type="file" class="cover-image-input" accept="image/png, image/jpeg, image/jpg, image/webp" />
                                    <label>
                                        <span class="filesizebox"><img src="{{ asset('assets/images/uploadfile.svg') }}" alt="image"/></span>
                                        <span>Upload file</span>
                                    </label>
                                </div>
                                <div class="uploadedfiledisplay d-none">
                                    <span class="filesizebox"><img src="{{ asset('assets/images/login-left-img.png') }}" alt="image"/></span>
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
                                        <span class="filesizebox"><img src="{{ asset('assets/images/uploadfile.svg') }}" alt="image"/></span>
                                        <span>Upload file</span>
                                    </label>
                                </div>
                                <div class="uploadedfiledisplay d-none">
                                    <span class="filesizebox"><img src="{{ asset('assets/images/login-left-img.png') }}" alt="image"/></span>
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
                                        <span class="filesizebox"><img src="{{ asset('assets/images/uploadfile.svg') }}" alt="image"/></span>
                                        <span>Upload file</span>
                                    </label>
                                </div>
                                <div class="uploadedfiledisplay d-none">
                                    <span class="filesizebox isvideobg"><img src="{{ asset('assets/images/login-left-img.png') }}" alt="image"/></span>
                                    <span class="deletelinkbtn">Delete</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

        // Template for a new day with default exercise
        const dayTemplate = (dayNumber) => `
        <div class="accordion-item day-item" data-day="${dayNumber}">
            <h2 class="accordion-header" id="headingDay${dayNumber}">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDay${dayNumber}" aria-expanded="false" aria-controls="collapseDay${dayNumber}">
                    Day ${dayNumber}
                </button>
            </h2>
            <div id="collapseDay${dayNumber}" class="accordion-collapse collapse" aria-labelledby="headingDay${dayNumber}" data-bs-parent="#daysAccordion">
                <div class="accordion-body pt-0">
                    <div class="exercise-box-block-row w-full d-flex flex-column gap-3">
                        ${exerciseBlockTemplate(dayNumber)}
                        <button class="btn btn-outline-primary whitebtn w-100 btn-sm add-exercise-btn">Add More Exercise</button>
                    </div>
                </div>
            </div>
        </div>
    `;

        // Initialize the form - add default exercise to existing days that don't have one
        $('.day-item').each(function() {
            const dayNumber = $(this).data('day');
            const exerciseContainer = $(this).find('.exercise-box-block-row');

            // Check if this day already has exercises (like Day 1 in your template)
            if (exerciseContainer.find('.exercise-box-block').length === 0) {
                // Add default exercise and change button text
                exerciseContainer.prepend(exerciseBlockTemplate(dayNumber));
                exerciseContainer.find('.add-exercise-btn').text('Add More Exercise');
            }

            // Ensure proper initial state for all exercise blocks
            exerciseContainer.find('.exercise-box-block').each(function() {
                $(this).find('.normal-exercise').show();
                $(this).find('.running-exercise').hide();
            });
        });

        // Handle workout frequency change
        $('input[name="workout_frequency"]').change(function() {
            const frequencyId = parseInt($(this).val());
            const frequencyDays = parseInt($(this).attr('id'));
            const selectedWeek = $('input[name="week"]:checked').val();

            const currentDays = $('.day-item').length;

            // If 6 days is selected but no week is selected, don't add extra days yet
            if (frequencyDays === 6 && !selectedWeek) {
                // Keep only 3 days for now
                if (currentDays > 3) {
                    $('.day-item').filter(function() {
                        return parseInt($(this).data('day')) > 3;
                    }).remove();
                }
                return; // Exit early, don't add the extra days yet
            }

            if (frequencyDays < currentDays) {
                // Remove extra days
                $('.day-item').filter(function() {
                    return parseInt($(this).data('day')) > frequencyDays;
                }).remove();
            } else if (frequencyDays > currentDays) {
                // Add missing days
                for (let i = currentDays + 1; i <= frequencyDays; i++) {
                    $('#daysAccordion').append(dayTemplate(i));
                }
            }
        });

        // Handle week selection change
        $('input[name="week"]').change(function() {
            const selectedWeek = $(this).val();
            const selectedFrequency = $('input[name="workout_frequency"]:checked');
            const frequencyDays = parseInt(selectedFrequency.attr('id'));
            const currentDays = $('.day-item').length;

            // Remove week validation error when week is selected
            $('.week-error-message').remove();

            // If 6 days frequency is selected and a week is now selected, add the remaining days
            if (frequencyDays === 6 && selectedWeek && currentDays < 6) {
                for (let i = currentDays + 1; i <= 6; i++) {
                    $('#daysAccordion').append(dayTemplate(i));
                }
            }
        });



        // Handle exercise selection change - show/hide running vs normal exercise forms
        $(document).on('change', '.exercise-select', function() {
            const exerciseBlock = $(this).closest('.exercise-box-block');
            const exerciseId = parseInt($(this).val());
            const exerciseType = $(this).find('option:selected').data('type');

            // Show running exercise form only for running exercises (ID 5 or type 'running')
            if (exerciseId === RUNNING_EXERCISE_ID || exerciseType === 'running') {
                exerciseBlock.find('.normal-exercise').attr("style", "display: none !important");
                exerciseBlock.find('.running-exercise').attr("style", "display: block !important");
            } else {
                exerciseBlock.find('.normal-exercise').attr("style", "display: block !important");
                exerciseBlock.find('.running-exercise').attr("style", "display: none !important");
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

        // Add exercise to a day
        $(document).on('click', '.add-exercise-btn', function(e) {
            e.preventDefault();
            const dayItem = $(this).closest('.accordion-body').find('.exercise-box-block-row');
            const dayNumber = $(this).closest('.day-item').data('day');
            const newExercise = exerciseBlockTemplate(dayNumber);
            $(this).before(newExercise);
            // Add delete link only for newly created exercises
            const justAdded = dayItem.find('.exercise-box-block').last();
            if (justAdded.find('.delete-exercise-btn').length === 0) {
                justAdded.append(
                    '<a href="#" class="deletelinkbtn mt-3 delete-exercise-btn">Delete exercise</a>'
                );
            }

            // Update button text
            $(this).text('Add More Exercise');
        });

        // Delete exercise
        $(document).on('click', '.delete-exercise-btn', function(e) {
            e.preventDefault();
            const dayContainer = $(this).closest('.exercise-box-block-row');
            const exerciseBlock = $(this).closest('.exercise-box-block');

            exerciseBlock.remove();

            // If no exercises left, change button text back
            if (dayContainer.find('.exercise-box-block').length === 0) {
                dayContainer.find('.add-exercise-btn').text('Add Exercise');
            }
        });

        // Add set to normal exercise
        $(document).on('click', '.add-set-btn', function() {
            const setTable = $(this).closest('table');
            const rowCount = setTable.find('tbody tr').length;
            const newRow = `
            <tr>
                <td valign="top">
                    <select class="form-control form-select">
                        @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </td>
                <td valign="top">
                    <select class="form-control form-select">
                        @for ($i = 1; $i <= 30; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </td>
                <td valign="top">
                    <div class="d-flex gap-1 rpe-container">
                        <div class="flex-grow-1">
                            <select class="form-control form-select rpe-select">
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                                <option value="0">Other</option>
                            </select>
                        </div>
                        <div class="input-group mb-3 rpe-percentage-input" style="width: 70px; display: none;">
                            <input type="text" class="form-control text-center px-1" aria-label="RPE Percentage" aria-describedby="basic-addon1">
                            <span class="input-group-text px-2" id="basic-addon1">%</span>
                        </div>
                    </div>
                </td>
                <td valign="top">
                    <input type="text" class="form-control" value="" />
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
                    <select class="form-control form-select running-type">
                        <option value="1">Duration</option>
                        <option value="2">Distance</option>
                    </select>
                </td>
                <td valign="top">
                    <div class="input-group w-100 mb-3 running-value">
                        <input type="text" class="form-control text-center px-1" aria-label="Run value" value="">
                        <span class="input-group-text px-2 running-unit" style="width:40px;">min</span>
                    </div>
                </td>
                <td valign="top">
                    <div class="d-flex gap-1 rpe-container">
                        <div class="flex-grow-1">
                            <select class="form-control form-select rpe-select">
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                                <option value="0">Other</option>
                            </select>
                        </div>
                        <div class="input-group mb-3 rpe-percentage-input" style="width: 70px; display: none;">
                            <input type="text" class="form-control text-center px-1" aria-label="RPE Percentage" aria-describedby="basic-addon1">
                            <span class="input-group-text px-2" id="basic-addon1">%</span>
                        </div>
                    </div>
                </td>
                <td valign="top">
                    <input type="text" class="form-control" value="" />
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

            // Re-number the remaining sets
            setTable.find('tbody tr').each(function(index) {
                $(this).find('td:first input').val(index + 1);
            });
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

        // (Removed older filename display handler; preview handler below manages UI)

        // Handle file delete
        $(document).on('click', '.uploadedfiledisplay .deletelinkbtn', function(e) {
            e.preventDefault();
            const displayDiv = $(this).closest('.uploadedfiledisplay');
            const customUpload = displayDiv.siblings('.customuploadfile');

            // Hide uploaded display and show upload area
            displayDiv.addClass('d-none');
            customUpload.show();
            customUpload.find('input').val(''); // Clear the file input
        });

        // Enforce numeric input
        // $(document).on('input', 'input[type="text"]', function() {
        //     this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        // });

        // In sets table default to integers; allow decimals only for running value
        // $(document).on('input', '.setsdatatable input[type="text"]:not(.allow-decimal)', function() {
        //     this.value = this.value.replace(/[^0-9]/g, '');
        // });
        // $(document).on('input', '.running-value input', function() {
        //     $(this).addClass('allow-decimal');
        //     this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        // });

        function showError($element, message) {
            $element.closest('.form-group').find('.error-message').remove();
            $element.after(`<div class="error-message text-danger mt-1">${message}</div>`);
        }

        function validateForm() {
            let isValid = true;
            let hasError = false; // Track any validation errors
            $('.error-message, .week-error-message, .ff-error').remove();

           // Validate Meso selection
            const selectedMeso = $('input[name="meso"]:checked').val();
            $('#meso-container').find('.meso-error-message').remove();

            if (!selectedMeso) {
                $('#meso-container').append(
                    '<div class="meso-error-message text-danger mt-2">Please select a meso</div>'
                );
                isValid = false;
                hasError = true;
            }

            const selectedWeek = $('input[name="week"]:checked').val();
            if (!selectedWeek) {
                const weekContainer = $('#week-container');
                weekContainer.find('.week-error-message').remove();

                const allDisabled = $('input[name="week"]').length === $('input[name="week"]:disabled').length;
                if (allDisabled) {
                    weekContainer.append(
                        '<div class="week-error-message text-danger mt-2">For this meso, all weeks are already added</div>'
                    );
                } else {
                    weekContainer.append(
                        '<div class="week-error-message text-danger mt-2">Please select a week</div>');
                }

                isValid = false;
                hasError = true;
            }

            // ✅ Check each day
            $('.day-item').each(function() {
                const dayNumber = $(this).data('day');
                const exerciseCount = $(this).find('.exercise-box-block').length;

                if (exerciseCount === 0) {
                    const $addBtn = $(this).find('.add-exercise-btn');
                    showError($addBtn, `Please add at least one exercise for Day ${dayNumber}`);
                    isValid = false;
                    hasError = true;
                }
            });

                    // ✅ File validations (Cover Image, GIF, Video)
            $('.cover-image-input').each(function () {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const validExt = ['jpg', 'jpeg', 'png', 'webp'];
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!validExt.includes(ext)) {
                        showError($(this).closest('.form-group'), "Cover image must be JPG, JPEG, PNG, or WEBP");
                        isValid = false;
                        hasError = true;
                    }
                }
            });

            $('.gif-image-input').each(function () {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (ext !== 'gif') {
                        showError($(this).closest('.form-group'), "GIF image must be a .gif file");
                        isValid = false;
                        hasError = true;
                    }
                }
            });

            $('.video-input').each(function () {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const validExt = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!validExt.includes(ext)) {
                        showError($(this).closest('.form-group'), "Video must be MP4, MOV, AVI, MKV, or WEBM");
                        isValid = false;
                        hasError = true;
                    }
                }
            });

            // ✅ Validate exercise selections
            $('.exercise-select').each(function() {
                if (!$(this).val()) {
                    showError($(this), `Please select an exercise`);
                    isValid = false;
                    hasError = true;
                }
            });

            // ✅ Per-block validation (NO :visible → validate even when accordion closed)
            $('.exercise-box-block').each(function() {
                const block = $(this);
                const exerciseId = parseInt(block.find('.exercise-select').val());
                const isRunningExercise = exerciseId === RUNNING_EXERCISE_ID;

                if (isRunningExercise) {
                    block.find('.running-exercise tbody tr').each(function() {
                        const row = $(this);
                        const runType = row.find('.running-type').val();
                        const runVal = row.find('.running-value input');
                        const walkVal = row.find('input[type="text"]').last();
                        const rpeSelect = row.find('.rpe-select');
                        const rpePct = row.find('.rpe-percentage-input input');

                        // Duration/Distance
                        if (runType === '1') {
                            if (!runVal.val() || isNaN(runVal.val()) || runVal.val() <= 0 ||
                                runVal.val() > 120) {
                                markError(runVal, "Duration must be 1–120 minutes");
                            }
                        } else {
                            if (!runVal.val() || isNaN(runVal.val()) || runVal.val() <= 0 ||
                                runVal.val() > 50) {
                                markError(runVal, "Distance must be 1–50 KM");
                            }
                        }

                        // Walk
                        if (walkVal.val() === '' || !/^\d+$/.test(walkVal.val())) {
                            markError(walkVal, "Walk is required");
                        } else if (parseInt(walkVal.val(), 10) < 0 || parseInt(walkVal.val(),
                                10) > 7200) {
                            markError(walkVal, "Walk must be 0–7200 seconds");
                        }

                        // RPE %
                        if (rpeSelect.val() === '0') {
                            if (!rpePct.val() || !/^\d+$/.test(rpePct.val())) {
                                markError(rpePct, "Required");
                            } else if (rpePct.val() <= 0 || rpePct.val() > 100) {
                                markError(rpePct, "RPE must be 1–100%");
                            }
                        }
                    });
                } else {
                    // Normal exercise validation
                    block.find('.normal-exercise tbody tr').each(function() {
                        const row = $(this);
                        const restVal = row.find('input[type="text"]').last();
                        const rpeSelect = row.find('.rpe-select');
                        const rpePct = row.find('.rpe-percentage-input input');

                        if (restVal.val() === '' || !/^\d+$/.test(restVal.val())) {
                            markError(restVal, "Rest is required");
                        } else if (parseInt(restVal.val(), 10) < 0 || parseInt(restVal.val(),
                                10) > 7200) {
                            markError(restVal, "Rest must be 0–7200 seconds");
                        }

                        if (rpeSelect.val() === '0') {
                            if (!rpePct.val() || !/^\d+$/.test(rpePct.val())) {
                                markError(rpePct, "Required");
                            } else if (rpePct.val() <= 0 || rpePct.val() > 100) {
                                markError(rpePct, "RPE must be 1–100%");
                            }
                        }
                    });
                }
            });

            if (hasError && $(".toast-error").length === 0) {
                toastr.error("Please fill all days required fields.");
            }

            return isValid;

            // Helper to mark errors (NO accordion auto-open anymore)
            function markError(input, message) {
                isValid = false;
                hasError = true;

                const wrap = input.closest('td, .running-value, .rpe-percentage-input');
                wrap.addClass('ff-rel');
                wrap.find('.ff-error').remove();
                wrap.append(`<div class="ff-error" style="color:#dc3545;font-size:12px;">${message}</div>`);
            }
        }

        // Helper to show inline errors
        function showError(element, message) {
            element.after(`<div class="error-message text-danger mt-2">${message}</div>`);
        }




        // Add this to your existing JavaScript
        $(document).on('click', '.submit-btn', function(e) {
            e.preventDefault();

            if (!validateForm()) {
                return;
            }

            // Validate all inputs
            let isValid = true;
            $('input[type="text"]').each(function() {
                if ($(this).val() && !$.isNumeric($(this).val())) {
                    alert('Please enter valid numbers only');
                    $(this).focus();
                    isValid = false;
                    return false;
                }
            });

            if (!isValid) return;

            // Serialize form data
            const formData = new FormData();
            const workoutFrequency = $('input[name="workout_frequency"]:checked').val();
            const meso = $('input[name="meso"]:checked').val();
            const week = $('input[name="week"]:checked').val();

            formData.append('workout_frequency', workoutFrequency);
            formData.append('meso', meso);
            formData.append('week', week);

            // Process each day
            $('.day-item').each(function() {
                const dayNumber = $(this).data('day');
                const exercisesMeta = [];

                // Process each exercise in the day
                $(this).find('.exercise-box-block').each(function(index) {
                    const exerciseId = $(this).find('.exercise-select').val();
                    const exerciseType = $(this).find(
                        '.exercise-select option:selected').data('type');
                    const level = $(this).find(
                        `input[name^="level_day${dayNumber}"]:checked`).val();
                    const sets = [];

                    // Check if this is a running exercise
                    const isRunningExercise = exerciseId == RUNNING_EXERCISE_ID ||
                        exerciseType === 'running';

                    if (!isRunningExercise) {
                        // Normal exercise sets
                        $(this).find('.normal-exercise tbody tr').each(function() {
                            const setNumber = $(this).find('td:eq(0) select')
                                .val();
                            const reps = $(this).find('td:eq(1) select').val();
                            const rpe = $(this).find('.rpe-select').val();
                            const rpePercentage = rpe === '0' ? $(this).find(
                                '.rpe-percentage-input input').val() : '';
                            const rest = $(this).find('td:eq(3) input').val();

                            sets.push({
                                set_number: setNumber,
                                reps: reps,
                                rpe: rpe,
                                rpePercentage: rpePercentage,
                                rest: rest
                            });
                        });
                    } else {
                        // Running exercise sets
                        $(this).find('.running-exercise tbody tr').each(function() {
                            const reps = $(this).find('.running-value input')
                                .val();
                            const rpe = $(this).find('.rpe-select').val();
                            const rpePercentage = rpe === '0' ? $(this).find(
                                '.rpe-percentage-input input').val() : '';
                            const walk = $(this).find('td:eq(3) input').val();
                            const setNumber = $(this).find('td:eq(0) select')
                                .val();

                            sets.push({
                                set_number: setNumber,
                                reps: reps,
                                rpe: rpe,
                                rpePercentage: rpePercentage,
                                rest: walk
                            });
                        });
                    }

                    // Add exercise metadata (without files)
                    const exerciseMeta = {
                        exercise_id: exerciseId,
                        level: level,
                        sets: sets
                    };

                    exercisesMeta.push(exerciseMeta);

                    // Handle files
                    const imageInput = $(this).find('.cover-image-input')[0];
                    const gifInput = $(this).find('.gif-image-input')[0];
                    const videoInput = $(this).find('.video-input')[0];

                    if (imageInput && imageInput.files.length > 0) {
                        formData.append(
                            `days[${dayNumber}][exercises][${index}][image]`,
                            imageInput.files[0]);
                    }
                    if (gifInput && gifInput.files.length > 0) {
                        formData.append(`days[${dayNumber}][exercises][${index}][gif]`,
                            gifInput.files[0]);
                    }
                    if (videoInput && videoInput.files.length > 0) {
                        formData.append(
                            `days[${dayNumber}][exercises][${index}][video]`,
                            videoInput.files[0]);
                    }
                });

                // Append exercise metadata JSON for this day
                formData.append(`days[${dayNumber}][meta]`, JSON.stringify({
                    exercises: exercisesMeta
                }));
            });

            // For debugging - log the form data
            for (let pair of formData.entries()) {
                console.log(pair[0] + ':', pair[1]);
            }

            // Submit the form via AJAX
            $.ajax({
                url: "{{ route('admin.exerciseSave') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Store message in localStorage before redirect
                        localStorage.setItem('toastrMessage', response.message);

                        // Redirect to index page
                        window.location.href = response.redirect_url;
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error updating exercise. Please try again.';
                    // If backend sends JSON error message
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    toastr.error(errorMessage);
                    console.error(xhr.responseText);
                }
            });
        });

    });

    // Function to check existing data and disable weeks
    function checkExistingData() {
        const workoutFrequencyId = $('input[name="workout_frequency"]:checked').val();
        const mesoId = $('input[name="meso"]:checked').val();

        if (!workoutFrequencyId || !mesoId) return;

        $.ajax({
            url: "{{ route('admin.checkExistingExerciseData') }}",
            type: 'GET',
            data: {
                workout_frequency_id: workoutFrequencyId,
                meso_id: mesoId
            },
            success: function(response) {
                if (response.success) {
                    let allDisabled = true;

                    // Disable weeks that already have data
                    $('input[name="week"]').each(function() {
                        const weekId = $(this).val();
                        if (response.usedWeeks.includes(parseInt(weekId))) {
                            $(this).prop('disabled', true);
                            $(this).prop('checked', false); // <-- uncheck if disabled
                            $(this).next('label').addClass('text-muted');
                        } else {
                            $(this).prop('disabled', false);
                            $(this).next('label').removeClass('text-muted');
                            allDisabled = false;

                        }
                    });
                    if (allDisabled) {
                    $('.submit-btn').prop('disabled', true);
                } else {
                    $('.submit-btn').prop('disabled', false);
                }
                }
            },
            error: function(xhr) {
                console.error('Error checking existing data:', xhr.responseText);
            }
        });
    }

    // Call the function on page load
    $(document).ready(function() {
        checkExistingData();

        // Also call when workout frequency or meso changes
        $('input[name="workout_frequency"], input[name="meso"]').change(function() {
            checkExistingData();
            $('.week-error-message').remove();
            $('#meso-container').find('.meso-error-message').remove();


        });
    });

    // Handle file upload previews
    $(document).on('change', '.cover-image-input, .gif-image-input, .video-input', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const displayDiv = $(this).closest('.form-group').find('.uploadedfiledisplay');
            const customUpload = $(this).closest('.customuploadfile');
            const fileBox = displayDiv.find('.filesizebox');

            // Hide the upload area and show the uploaded file display
            customUpload.hide();
            displayDiv.removeClass('d-none');

            // Clear previous content
            fileBox.html('');

            // Create preview based on file type
            if (file.type.startsWith('image/') && !file.type.includes('gif')) {
                // Regular image
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = $('<img>').attr('src', e.target.result)
                        .css({
                            'width': '100%',
                            'height': '100%',
                            'object-fit': 'cover',
                            'border-radius': '4px'
                        });
                    fileBox.append(img);
                };
                reader.readAsDataURL(file);
            } else if (file.type.includes('gif')) {
                // GIF image
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = $('<img>').attr('src', e.target.result)
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

            // Make the preview clickable to open in new tab
            fileBox.off('click').on('click', function() {
                if (file.type.startsWith('image/')) {
                    window.open(URL.createObjectURL(file), '_blank');
                } else if (file.type.includes('video/')) {
                    window.open(URL.createObjectURL(file), '_blank');
                }
            });
        }
    });

    // Handle file delete
    $(document).on('click', '.uploadedfiledisplay .deletelinkbtn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const displayDiv = $(this).closest('.uploadedfiledisplay');
        const customUpload = displayDiv.siblings('.customuploadfile');

        // Hide uploaded display and show upload area
        displayDiv.addClass('d-none');
        customUpload.show();
        customUpload.find('input').val(''); // Clear the file input
    });

    // Prevent event bubbling for delete button
    $(document).on('click', '.deletelinkbtn', function(e) {
        e.stopPropagation();
    });

    // Handle level label clicks to check radio buttons
    $(document).on('click', '.form-check-label', function(e) {
        // Only handle labels that are for level radio buttons
        const input = $(this).prev('.form-check-input');
        if (input.attr('name') && input.attr('name').includes('level_day')) {
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
@endpush
