@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Add Fitness Challenge')

<!-- Add jQuery CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<form action="{{ route('admin.fitnessChallengeSave') }}" method="POST" enctype="multipart/form-data" id="addExerCise">
    @csrf
    <input type="hidden" name="weeks_data" id="weeks_data">
    <div class="container-fluid">
        <div class="pate-content-wrapper">
            <div class="page-title-row">
                <h5>Add Fitness Challenge</h5>
            </div>
            <div class="m-card-min-hight">
                <div class="row create-workout-form">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="challengeName" class="form-label">Challenge Name<span
                                    style="color:red;">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg" id="challengeName"
                                    name="challenge_name" placeholder="Enter challenge name"
                                    value="{{ old('challenge_name') }}" maxlength="50">
                                <div id="challengeNameError" class="text-danger mt-1" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="goal" class="form-label">Goal<span style="color:red;">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg" id="goal" name="goal"
                                    placeholder="Enter goal" value="{{ old('goal') }}" maxlength="50">
                                <div id="goalError" class="text-danger mt-1" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration (Week)<span
                                    style="color:red;">*</span></label>
                            <div class="position-relative">
                                <input type="number" class="form-control form-control-lg" id="duration"
                                    name="duration_weeks" placeholder="Enter duration in weeks (max-100)" min="1"
                                    min="1" max="100" step="1" oninput="validateDuration(this)"
                                    value="{{ old('duration_weeks') }}">
                                <div id="durationError" class="text-danger mt-1" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Select Plan<span style="color:red;">*</span></label>
                            <select name="plan_id" class="form-select" id="planSelect">
                                <option disabled selected>Select Plan</option>
                                @foreach ($plans as $id => $name)
                                    <option value="{{ $id }}" {{ old('plan_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}</option>
                                @endforeach
                            </select>
                            <div id="planSelectError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="col-md-6 uploa">
                        <div class="mb-3">
                            <label for="challengeImage" class="form-label">Upload Image<span
                                    style="color:red;">*</span></label>
                            <input class="form-control form-control-lg" id="challengeImage" name="image"
                                type="file" accept=".jpg,.jpeg,.png">
                            <small class="text-muted">Please upload a 120x120 pixel image for best view.</small>
                            <div id="challengeImageError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description<span
                                    style="color:red;">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter description"
                                maxlength="1000">{{ old('description') }}</textarea>
                            <div id="descriptionError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="">Add Challenge</label>
                    <div id="weeks-container">
                        <!-- Week 1 will be added here by default -->
                        <div class="create-exercise-section week" data-week-number="1">
                            <p class="text-14 font-500">Week 1</p>
                            <hr
                                style="margin: 1rem -20px; color: #cccccc; border: 0; border-top: var(--bs-border-width) solid; opacity: .25;">
                            <div class="days-container">
                                <!-- Day 1 will be added here by default -->
                                <div class="pink-bg mb-3 day" data-day-number="1">
                                    <p class="text-14 font-500">Day 1</p>
                                    <hr
                                        style="margin: 1rem -10px; color: #cccccc; border: 0; border-top: var(--bs-border-width) solid; opacity: .25;">
                                    <div class="filter-row-search justify-content-between">
                                        <div class="mb-3">
                                            <input type="text" class="form-control exercise-search"
                                                placeholder="Search">
                                        </div>
                                        <div>
                                            <span>Rest Day</span>
                                            <label class="switch me-2">
                                                <input type="checkbox" name="rest_day[]" class="rest-day-toggle">
                                                <span class="slider"></span>
                                            </label>
                                            <a href="#" class="btn btn-primary add-exercise-btn"
                                                data-bs-toggle="modal" data-bs-target="#exampleModal">Add Exercise</a>
                                        </div>
                                    </div>
                                    <div class="table-responsive mt-2">
                                        <!-- In the initial week template -->
                                        <table class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>S.No.</th>
                                                    <th>Exercise</th>
                                                    <th>Reps</th>
                                                    <th>Sets</th>
                                                    <th>Difficulty&nbsp;&nbsp;Level</th>
                                                    <th>Location</th>
                                                    <th>Body Type</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="no-exercises">
                                                    <td colspan="8" class="text-center">No exercises added yet</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <a href="#" class="btn btn-outline-primary w-100 add-day-btn">Add More Days</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <a href="#" class="btn btn-primary w-100" id="add-week-btn">Add New Week</a>
                </div>

                <div class="text-end mb-3">
                    <a href="{{ route('admin.fitnessChallengeIndex') }}"
                        class="btn btn-outline-primary btn-sm me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Exercise Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Add Exercise</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label for="exerciseSelect" class="form-label">Select Exercise<span
                                    style="color:red;">*</span></label>
                            <select class="form-select" name="exercise_id" id="exerciseSelect"
                                aria-label="Default select example">
                                <option selected value="">Select</option>
                                @foreach ($exercises as $exercise)
                                    <option value="{{ $exercise->id }}">{{ $exercise->exercise_name }}</option>
                                @endforeach
                            </select>
                            <div id="exerciseSelectError" class="text-danger mt-1" style="display: none;"></div>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="exerciseReps" class="form-label">Reps<span
                                    style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="exerciseReps" placeholder=" Enter Reps (max-100)"
                                min="0" max="100" step="1" oninput="validateRestSeconds(this)">
                            <div id="exerciseRepsError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="exerciseSets" class="form-label">Sets<span
                                    style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="exerciseSets" placeholder="Enter Sets (max-100)"
                                min="0" max="100" step="1" oninput="validateRestSeconds(this)">
                            <div id="exerciseSetsError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="exerciseRestTime" class="form-label">Rest Time (Seconds)<span
                                    style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="exerciseRestTime"
                                placeholder="Enter Rest Time (max-60)" min="0" max="60" step="1"
                                oninput="validateRestSeconds(this)">
                            <div id="exerciseRestTimeError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="text-center">
                        <button type="button" class="btn btn-primary" id="saveExerciseBtn">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Variables to track current week and day for exercise addition
        let currentWeek = null;
        let currentDay = null;
        let exercises = @json($exercises);
        let editingExerciseRow = null; // Variable to track which row is being edited

        // Set up the first week and day as current
        currentWeek = $('.week').first();
        currentDay = currentWeek.find('.day').first();

        // Fetch exercise details when selected
        $('#exerciseSelect').on('change', function() {
            const exerciseId = $(this).val();
            if (!exerciseId) return;

            $.ajax({
                url: "{{ route('admin.exercises.details') }}",
                type: 'GET',
                data: {
                    id: exerciseId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const exercise = response.exercise;
                        // Update the exercises array with the new data
                        const existingIndex = exercises.findIndex(e => e.id == exerciseId);
                        if (existingIndex >= 0) {
                            exercises[existingIndex] = exercise;
                        } else {
                            exercises.push(exercise);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching exercise details:', error);
                }
            });
        });

        // Function to validate all days
        function validateAllDays() {
            let isValid = true;

            // Clear all existing day errors
            $('.day-error').remove();

            $('.day').each(function() {
                const dayContainer = $(this);
                const isRestDay = dayContainer.find('.rest-day-toggle').is(':checked');
                const hasExercises = dayContainer.find('tbody tr').not('.no-exercises').length > 0;

                if (!isRestDay && !hasExercises) {
                    // Add error message below the day
                    dayContainer.append(`
                    <div class="day-error text-danger mt-2">
                        Please add exercises or mark this day as a rest day
                    </div>
                `);
                    isValid = false;
                }
            });

            return isValid;
        }

        // Form validation on submit
        $('#addExerCise').on('submit', function(e) {
            e.preventDefault();
            let isValid = true;

            // Reset error messages
            $('.text-danger').hide();
            $('.day-error').remove();

            // Validate Challenge Name
            const challengeName = $('#challengeName').val().trim();
            if (!challengeName) {
                $('#challengeNameError').text('Challenge name is required').show();
                isValid = false;
            } else if (challengeName.length > 100) {
                $('#challengeNameError').text('Challenge name must be less than 100 characters').show();
                isValid = false;
            }

            // Validate Goal
            const goal = $('#goal').val().trim();
            if (!goal) {
                $('#goalError').text('Goal is required').show();
                isValid = false;
            } else if (goal.length > 255) {
                $('#goalError').text('Goal must be less than 255 characters').show();
                isValid = false;
            }

            // Validate Duration
            const duration = $('#duration').val();
            if (!duration) {
                $('#durationError').text('Duration is required').show();
                isValid = false;
            } else if (isNaN(duration) || duration < 1) {
                $('#durationError').text('Duration must be a positive number').show();
                isValid = false;
            }

            // Validate Plan Selection
            const planSelect = $('#planSelect').val();
            if (!planSelect) {
                $('#planSelectError').text('Please select a plan').show();
                isValid = false;
            }

            // Validate Image
            const image = $('#challengeImage')[0].files[0];
            if (!image) {
                $('#challengeImageError').text('Image is required').show();
                isValid = false;
            } else {
                // Check file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(image.type)) {
                    $('#challengeImageError').text('Only JPG, JPEG, and PNG files are allowed').show();
                    isValid = false;
                }

                // Check file size (2MB max)
                if (image.size > 20 * 1024 * 1024) {
                    $('#challengeImageError').text('Image size must be less than 20MB').show();
                    isValid = false;
                }
            }

            // Validate Description
            const description = $('#description').val().trim();
            if (!description) {
                $('#descriptionError').text('Description is required').show();
                isValid = false;
            } else if (description.length > 1000) {
                $('#descriptionError').text('Description must be less than 1000 characters').show();
                isValid = false;
            }

            // Validate at least one week exists
            if ($('.week').length === 0) {
                alert('Please add at least one week to the challenge');
                isValid = false;
            } else {
                // Validate all days have either exercises or are rest days
                isValid = validateAllDays() && isValid;

                // Validate at least one exercise exists somewhere in the challenge
                let hasExercise = false;
                $('.day').each(function() {
                    if (!$(this).find('.rest-day-toggle').is(':checked')) {
                        const exerciseCount = $(this).find('tbody tr').not('.no-exercises')
                            .length;
                        if (exerciseCount > 0) {
                            hasExercise = true;
                        }
                    } else {
                        hasExercise = true; // Rest day counts as valid
                    }
                });

                // if (!hasExercise) {
                //     alert('Please add at least one exercise to one of the days');
                //     isValid = false;
                // }
            }

            // If all validations pass, prepare the structured data and submit the form
            if (isValid) {
                prepareWeeksData();
                this.submit();
            }
        });

        // Prepare the structured weeks data for submission
        function prepareWeeksData() {
            const weeksData = [];

            $('.week').each(function() {
                const weekNumber = $(this).data('week-number');
                const weekData = {
                    week: weekNumber,
                    days: []
                };

                $(this).find('.day').each(function() {
                    const dayNumber = $(this).data('day-number');
                    const isRestDay = $(this).find('.rest-day-toggle').is(':checked');
                    const dayData = {
                        day: dayNumber,
                        is_rest_day: isRestDay,
                        exercises: []
                    };

                    if (!isRestDay) {
                        $(this).find('tbody tr').not('.no-exercises').each(function() {
                            const exerciseId = $(this).data('exercise-id');
                            const exerciseName = $(this).find('td:nth-child(2)').text();
                            const reps = $(this).find('td:nth-child(3)').text();
                            const sets = $(this).find('td:nth-child(4)').text();
                            const restTime = $(this).find('td:nth-child(4)').attr('data-rest-time');
                            // const restTime = $(this).data('rest-time');
                            const level = $(this).find('input[name="level[]"]').val();
                            const location = $(this).find('input[name="location[]"]')
                                .val();
                            const bodyPart = $(this).find('td:nth-child(8)').text()
                                .trim();

                                
                            const order = $(this).index();

                            dayData.exercises.push({
                                exercise_id: exerciseId,
                                name: exerciseName,
                                reps: reps,
                                sets: sets,
                                rest_time: restTime,
                                level: level,
                                location: location,
                                body_part: bodyPart,
                                order: order
                            });
                        });
                    }

                    weekData.days.push(dayData);
                });

                weeksData.push(weekData);
            });

            $('#weeks_data').val(JSON.stringify(weeksData));
        }

        // Real-time validation for fields
        $('#challengeName').on('input', function() {
            const val = $(this).val().trim();
            if (!val) {
                $('#challengeNameError').text('Challenge name is required').show();
            } else if (val.length > 100) {
                $('#challengeNameError').text('Challenge name must be less than 100 characters').show();
            } else {
                $('#challengeNameError').hide();
            }
        });

        $('#goal').on('input', function() {
            const val = $(this).val().trim();
            if (!val) {
                $('#goalError').text('Goal is required').show();
            } else if (val.length > 255) {
                $('#goalError').text('Goal must be less than 255 characters').show();
            } else {
                $('#goalError').hide();
            }
        });

        $('#duration').on('input', function() {
            const val = $(this).val();
            if (!val) {
                $('#durationError').text('Duration is required').show();
            } else if (isNaN(val) || val < 1) {
                $('#durationError').text('Duration must be a positive number').show();
            } else {
                $('#durationError').hide();
            }
        });

        $('#planSelect').on('change', function() {
            if (!$(this).val()) {
                $('#planSelectError').text('Please select a plan').show();
            } else {
                $('#planSelectError').hide();
            }
        });

        $('#challengeImage').on('change', function() {
            const file = this.files[0];
            if (!file) {
                $('#challengeImageError').text('Image is required').show();
                return;
            }

            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                $('#challengeImageError').text('Only JPG, JPEG, and PNG files are allowed').show();
                return;
            }

            if (file.size > 20 * 1024 * 1024) {
                $('#challengeImageError').text('Image size must be less than 20MB').show();
                return;
            }

            $('#challengeImageError').hide();
        });

        $('#description').on('input', function() {
            const val = $(this).val().trim();
            if (!val) {
                $('#descriptionError').text('Description is required').show();
            } else if (val.length > 1000) {
                $('#descriptionError').text('Description must be less than 1000 characters').show();
            } else {
                $('#descriptionError').hide();
            }
        });

        // Function to update the state of the "Add More Week" button
        function updateAddWeekButtonState() {
            const duration = parseInt($('#duration').val()) || 1;
            const currentWeeksCount = $('.week').length;

            if (currentWeeksCount < duration) {
                $('#add-week-btn').prop('disabled', false).removeClass('disabled');
            } else {
                $('#add-week-btn').prop('disabled', true).addClass('disabled');
            }
        }

        // Function to add a new week
        function addNewWeek(weekNumber) {
            const newWeekHtml = `
            <div class="create-exercise-section week mt-3" data-week-number="${weekNumber}">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="mb-0 text-14 font-500">Week ${weekNumber}</p>
                <button class="btn btn-primary delete-week-btn">Delete Week</button>
            </div>

                <hr style="margin: 1rem -20px; color: #cccccc; border: 0; border-top: var(--bs-border-width) solid; opacity: .25;">
                <div class="days-container">
                    <div class="pink-bg mb-3 day" data-day-number="1">
                        <p class="text-14 font-500">Day 1</p>
                        <hr style="margin: 1rem -10px; color: #cccccc; border: 0; border-top: var(--bs-border-width) solid; opacity: .25;">
                        <div class="filter-row-search justify-content-between">
                            <div class="mb-3 headerserarch">
                                <input type="text" class="form-control exercise-search" placeholder="Search">
                            </div>
                            <div>
                                <span>Rest Day</span>
                                <label class="switch me-2">
                                    <input type="checkbox" name="rest_day[]" class="rest-day-toggle">
                                    <span class="slider"></span>
                                </label>
                                <a href="#" class="btn btn-primary add-exercise-btn" data-bs-toggle="modal" data-bs-target="#exampleModal">Add Exercise</a>
                            </div>
                        </div>
                        <div class="table-responsive mt-2">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Exercise</th>
                                        <th>Reps</th>
                                        <th>Sets</th>
                                        <th>Difficulty&nbsp;&nbsp;Level</th>
                                        <th>Location</th>
                                        <th>Body Part</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="no-exercises">
                                        <td colspan="8" class="text-center">No exercises added yet</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <a href="#" class="btn btn-outline-primary w-100 add-day-btn">Add More Days</a>
                </div>
            </div>
        `;

            $('#weeks-container').append(newWeekHtml);

            // Update the button state after adding a week
            updateAddWeekButtonState();
        }

        // Add new week button click handler
        $('#add-week-btn').click(function(e) {
            e.preventDefault();

            const duration = parseInt($('#duration').val()) || 1;
            const currentWeeksCount = $('.week').length;

            // Only add a new week if we haven't reached the max
            if (currentWeeksCount < duration) {
                const newWeekNumber = currentWeeksCount + 1;
                addNewWeek(newWeekNumber);
            } else {
                alert(`You can only add up to ${duration} weeks based on your selected duration.`);
            }
        });

        // Delete week functionality
        $(document).on('click', '.delete-week-btn', function(e) {
            e.preventDefault();
            const weekToDelete = $(this).closest('.week');

            // Always allow deletion if there's more than one week
            if ($('.week').length > 1) {
                weekToDelete.remove();

                // Renumber remaining weeks
                $('.week').each(function(index) {
                    const newWeekNumber = index + 1;
                    $(this).attr('data-week-number', newWeekNumber);
                    $(this).find('p.text-14:first').text(`Week ${newWeekNumber}`);
                });

                // Update the add week button state
                updateAddWeekButtonState();
            } else {
                alert('You must have at least one week in the challenge.');
            }
        });

        // Add new day to a week (using event delegation for dynamically added elements)
        $(document).on('click', '.add-day-btn', function(e) {
            e.preventDefault();

            const weekContainer = $(this).closest('.week');
            const daysContainer = weekContainer.find('.days-container');
            const dayCount = weekContainer.find('.day').length;

            const newDayNumber = dayCount + 1;

            // Create the new day HTML
            const newDayHtml = `
            <div class="pink-bg mb-3 day" data-day-number="${newDayNumber}">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="mb-0 text-14 font-500">Day ${newDayNumber}</p>
                <button class="btn btn-primary delete-day-btn">Delete Day</button>
            </div>

                <hr style="margin: 1rem -10px; color: #cccccc; border: 0; border-top: var(--bs-border-width) solid; opacity: .25;">
                <div class="filter-row-search justify-content-between">
                    <div class="mb-3 headerserarch">
                        <input type="text" class="form-control exercise-search" placeholder="Search">
                    </div>
                    <div>
                        <span>Rest Day</span>
                        <label class="switch me-2">
                            <input type="checkbox" name="rest_day[]" class="rest-day-toggle">
                            <span class="slider"></span>
                        </label>
                        <a href="#" class="btn btn-primary add-exercise-btn" data-bs-toggle="modal" data-bs-target="#exampleModal">Add Exercise</a>
                    </div>
                </div>
                <div class="table-responsive mt-2">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Exercise</th>
                                <th>Reps</th>
                                <th>Sets</th>
                                <th>Difficulty&nbsp;&nbsp;Level</th>
                                <th>Location</th>
                                <th>Body Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="no-exercises">
                                <td colspan="8" class="text-center">No exercises added yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;

            // Append the new day to the week's days container
            daysContainer.append(newDayHtml);

            // Scroll to the newly added day
            const newDayElement = daysContainer.find('.day').last();
            newDayElement[0].scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });

            if (weekContainer.find('.day').length >= 7) {
                $(this).closest('.mb-3').hide();
            }
        });

        // Delete day functionality
        $(document).on('click', '.delete-day-btn', function(e) {
            e.preventDefault();
            const dayToDelete = $(this).closest('.day');
            const daysContainer = dayToDelete.closest('.days-container');
            const weekContainer = dayToDelete.closest('.week');

            if (daysContainer.find('.day').length > 1) {
                dayToDelete.remove();

                // Renumber remaining days
                daysContainer.find('.day').each(function(index) {
                    const newDayNumber = index + 1;
                    $(this).attr('data-day-number', newDayNumber);
                    $(this).find('p.text-14:first').text(`Day ${newDayNumber}`);
                });

                // Show the add button again if less than 7 days now
                if (weekContainer.find('.day').length < 7) {
                    weekContainer.find('.add-day-btn').closest('.mb-3').show();
                }
            } else {
                alert('You must have at least one day in the week.');
            }
        });

        // Rest day toggle functionality
        $(document).on('change', '.rest-day-toggle', function() {
            const dayContainer = $(this).closest('.day');
            const tableBody = dayContainer.find('tbody');
            const addExerciseBtn = dayContainer.find('.add-exercise-btn');

            // Clear any existing error
            dayContainer.find('.day-error').remove();

            if ($(this).is(':checked')) {
                tableBody.html('<tr><td colspan="8" class="text-center">Rest Day Enabled</td></tr>');
                addExerciseBtn.addClass('disabled').attr('disabled', true);
            } else {
                tableBody.html(
                    '<tr class="no-exercises"><td colspan="8" class="text-center">No exercises added yet</td></tr>'
                );
                addExerciseBtn.removeClass('disabled').attr('disabled', false);
            }
        });

        // Set current week and day when clicking "Add Exercise" button
        $(document).on('click', '.add-exercise-btn', function(e) {
            e.preventDefault();
            currentDay = $(this).closest('.day');
            currentWeek = currentDay.closest('.week');

            // Reset the modal title and button label for adding new exercise
            $('#exampleModalLabel').text('Add Exercise');
            $('#saveExerciseBtn').text('Save');

            // Clear form fields
            $('#exerciseSelect').val('');
            $('#exerciseReps').val('');
            $('#exerciseSets').val('');
            $('#exerciseRestTime').val('');

            // Reset editing row indicator
            editingExerciseRow = null;
        });

        // Edit exercise (event delegation for dynamically added elements)
        $(document).on('click', '.edit-exercise', function(e) {
            e.preventDefault();

            // Get the row being edited
            editingExerciseRow = $(this).closest('tr');

            // Set current day and week
            currentDay = editingExerciseRow.closest('.day');
            currentWeek = currentDay.closest('.week');

            // Get exercise data from the row
            const exerciseId = editingExerciseRow.data('exercise-id');
            const reps = editingExerciseRow.find('td:nth-child(3)').text();
            const sets = editingExerciseRow.find('td:nth-child(4)').text();
            const restTime = editingExerciseRow.find('td:nth-child(4)').attr('data-rest-time');
            const level = editingExerciseRow.find('input[name="level[]"]').val();
            const location = editingExerciseRow.find('input[name="location[]"]').val();
            const bodyPart = editingExerciseRow.find('td:nth-child(8)').text().trim();

            // Populate the modal fields
            $('#exerciseSelect').val(exerciseId);
            $('#exerciseReps').val(reps);
            $('#exerciseSets').val(sets);
            $('#exerciseRestTime').val(restTime);

            // Change modal title and button label
            $('#exampleModalLabel').text('Edit Exercise');
            $('#saveExerciseBtn').text('Update');

            // Open the modal
            $('#exampleModal').modal('show');
        });

        function isExerciseAlreadyAdded(dayContainer, exerciseId, excludeRow = null) {
            let isDuplicate = false;
            dayContainer.find('tbody tr').each(function() {
                // Skip the row we're excluding (for edit operations)
                if (excludeRow && $(this).is(excludeRow)) return;

                if ($(this).data('exercise-id') == exerciseId) {
                    isDuplicate = true;
                    return false; // break the loop
                }
            });
            return isDuplicate;
        }

        // Save exercise from modal
        $('#saveExerciseBtn').click(function() {
            let isValid = true;

            // Reset error messages
            $('#exerciseSelectError, #exerciseRepsError, #exerciseSetsError, #exerciseRestTimeError')
                .hide();

            // Validate exercise selection
            const exerciseId = $('#exerciseSelect').val();
            if (!exerciseId) {
                $('#exerciseSelectError').text('Please select an exercise').show();
                isValid = false;
            }

            // Validate reps
            const reps = $('#exerciseReps').val();
            if (!reps) {
                $('#exerciseRepsError').text('Reps are required').show();
                isValid = false;
            } else if (isNaN(reps) || reps < 0) {
                $('#exerciseRepsError').text('Reps must be a positive number').show();
                isValid = false;
            }

            // Validate sets
            const sets = $('#exerciseSets').val();
            if (!sets) {
                $('#exerciseSetsError').text('Sets are required').show();
                isValid = false;
            } else if (isNaN(sets) || sets < 0) {
                $('#exerciseSetsError').text('Sets must be a positive number').show();
                isValid = false;
            }

            // Validate rest time
            const restTime = $('#exerciseRestTime').val();
            if (!restTime) {
                $('#exerciseRestTimeError').text('Rest time is required').show();
                isValid = false;
            }

            if (!isValid) return;

            // Check for duplicate exercise (skip if editing)
            if (!editingExerciseRow && isExerciseAlreadyAdded(currentDay, exerciseId)) {
                $('#exerciseSelectError').text('This exercise is already added to this day').show();
                return;
            } else if (editingExerciseRow && isExerciseAlreadyAdded(currentDay, exerciseId,
                    editingExerciseRow)) {
                // Only show error if the exercise exists elsewhere in the day (not counting the current row being edited)
                $('#exerciseSelectError').text('This exercise is already added to this day').show();
                return;
            }

            // Get other values
            const level = 1; // Default level
            const location = 1; // Default location

            // Level and location mappings
            const levelMap = {
                1: 'Beginner',
                2: 'Intermediate',
                3: 'Advance'
            };

            const locationMap = {
                1: 'Home',
                2: 'Gym'
            };

            // Find exercise name by ID
            const exercise = exercises.find(e => e.id == exerciseId);
            const exerciseName = exercise ? exercise.exercise_name : 'Unknown Exercise';

            const levelName = levelMap[exercise.level] || 'Beginner';
            const locationName = locationMap[exercise.location] || 'Home';

            // Get body part from exercise data
            const bodyPart = exercise ? (exercise.body_part || exercise.name || '') : '';

            // Check if we're editing or adding a new exercise
            if (editingExerciseRow) {
                // Update existing row
                editingExerciseRow.data('exercise-id', exerciseId);
                editingExerciseRow.find('td:nth-child(2)').text(exerciseName);
                editingExerciseRow.find('td:nth-child(3)').text(reps);
                editingExerciseRow.find('td:nth-child(4)').text(sets);
                editingExerciseRow.find('td:nth-child(4)').attr('data-rest-time',restTime);
                // editingExerciseRow.find('td:nth-child(5)').text(restTime);
                editingExerciseRow.find('td:nth-child(5)').text(levelName);
                editingExerciseRow.find('td:nth-child(6)').text(locationName);
                editingExerciseRow.find('td:nth-child(7)').text(bodyPart);

                // Reset editing row indicator
                editingExerciseRow = null;
            } else {
                // Create new exercise row
                const exerciseCount = currentDay.find('tbody tr').not('.no-exercises').length;
                const newExerciseNumber = exerciseCount + 1;

                const newExerciseRow = `
    <tr data-exercise-id="${exerciseId}">
        <td>${newExerciseNumber}.</td>
        <td>${exerciseName}</td>
        <td>${reps}</td>
        <td data-rest-time="${restTime}">${sets}</td>
        <td>${levelName}</td>
        <td>${locationName}</td>
        <td>${bodyPart}</td>
        <td>
            <a href="#" class="edit-exercise me-2"><img src="{{ asset('assets/images/edittbtn.svg') }}" alt="Edit"></a>
            <a href="#" class="delete-exercise"><img src="{{ asset('assets/images/deletebtn.svg') }}" alt="Delete"></a>
        </td>
    </tr>
`;

                // Add to table
                if (currentDay.find('tbody tr.no-exercises').length > 0) {
                    currentDay.find('tbody').html(newExerciseRow);
                } else {
                    currentDay.find('tbody').append(newExerciseRow);
                }

                // Clear any day error since we've added an exercise
                currentDay.find('.day-error').remove();
            }

            // Reset modal fields
            $('#exerciseSelect').val('');
            $('#exerciseReps').val('');
            $('#exerciseSets').val('');
            $('#exerciseRestTime').val('');

            // Close modal
            $('#exampleModal').modal('hide');
        });

        // Delete exercise row
        $(document).on('click', '.delete-exercise', function(e) {
            e.preventDefault();
            const row = $(this).closest('tr');
            const table = $(this).closest('tbody');
            const dayContainer = $(this).closest('.day');

            row.remove();

            // Update serial numbers
            table.find('tr').each(function(index) {
                $(this).find('td:first').text((index + 1) + '.');
            });

            // If no exercises left, show placeholder
            if (table.find('tr').not('.no-exercises').length === 0) {
                table.html(
                    '<tr class="no-exercises"><td colspan="8" class="text-center">No exercises added yet</td></tr>'
                );

                // Check if rest day is not selected and show error
                if (!dayContainer.find('.rest-day-toggle').is(':checked')) {
                    dayContainer.append(`
                    <div class="day-error text-danger mt-2">
                        Please add exercises or mark this day as a rest day
                    </div>
                `);
                }
            }
        });

        // Exercise search functionality
        $(document).on('input', '.exercise-search', function() {
            const searchTerm = $(this).val().toLowerCase();
            const dayContainer = $(this).closest('.day');
            const tableBody = dayContainer.find('tbody');
            let hasResults = false;

            // First, hide the "no exercises" and "no results" rows if they exist
            tableBody.find('.no-exercises, .no-results').remove();

            // Show all rows initially if search is empty
            if (searchTerm === '') {
                tableBody.find('tr').show();
                // If there are no exercises at all, show the placeholder
                if (tableBody.find('tr[data-exercise-id]').length === 0) {
                    tableBody.append(
                        '<tr class="no-exercises"><td colspan="8" class="text-center">No exercises added yet</td></tr>'
                    );
                }
                return;
            }

            // Search through each exercise row across all columns
            tableBody.find('tr[data-exercise-id]').each(function() {
                const $row = $(this);
                let rowMatches = false;

                // Check each column (except the action column)
                $row.find('td:not(:last-child)').each(function() {
                    const cellText = $(this).text().toLowerCase();
                    if (cellText.includes(searchTerm)) {
                        rowMatches = true;
                        return false; // break out of the column loop if match found
                    }
                });

                if (rowMatches) {
                    $row.show();
                    hasResults = true;
                } else {
                    $row.hide();
                }
            });

            // If no results were found and there are exercises in the table
            if (!hasResults && tableBody.find('tr[data-exercise-id]').length > 0) {
                tableBody.append('<tr class="no-results"><td colspan="8" class="text-center">No results found</td></tr>');
            }
        });

        // Duration change handler
        $('#duration').on('change input', function() {
            const duration = parseInt($(this).val()) || 1;
            const currentWeeksCount = $('.week').length;

            // If duration is less than current weeks, remove excess weeks
            if (duration < currentWeeksCount) {
                // Keep only the first 'duration' number of weeks
                $('.week').each(function(index) {
                    if (index >= duration) {
                        $(this).remove();
                    }
                });
            }

            // If we need more weeks, add them
            if (duration > currentWeeksCount) {
                for (let i = currentWeeksCount + 1; i <= duration; i++) {
                    addNewWeek(i);
                }
            }

            // Update the add week button state
            updateAddWeekButtonState();
        });

        // Initialize the add week button state
        updateAddWeekButtonState();
    });

    function validateDuration(input) {
        const errorDiv = document.getElementById(input.id + 'Error');
        let value = input.value;

        // Remove all non-digit characters
        value = value.replace(/[^\d]/g, '');

        // Remove leading zeros
        value = value.replace(/^0+/, '');

        // Set the cleaned value back
        input.value = value;

        if (value === '') {
            errorDiv.style.display = 'block';
            return;
        }

        const number = parseInt(value, 10);

        if (number < 1) {
            errorDiv.style.display = 'block';
            errorDiv.textContent = 'Value must be at least 1. Zero is not allowed.';
        } else if (number > 100) {
            input.value = '100';
            errorDiv.style.display = 'block';
            // errorDiv.textContent = 'Value cannot exceed 100. It has been set to 100.';
        } else {
            errorDiv.style.display = 'none';
        }
    }

    function validateRestSeconds(input) {
        const errorDiv = document.getElementById(input.id + 'Error');
        let value = input.value;

        // Remove all non-digit characters
        value = value.replace(/[^\d]/g, '');

        // Remove leading zeros
        //value = value.replace(/^0+/, '');

        // Set the cleaned value back
        input.value = value;
    /*
        if (value === '') {
            errorDiv.style.display = 'block';
            return;
        }
    */

        if (input.value.length > 3) {
            input.value = input.value.slice(0, 3);
        }

        if(input.value == '00' || input.value == '000') {
            input.value = '0';
            return;
        }
        
        const number = parseInt(value, 10);
        
        if (number < 0) {
            errorDiv.style.display = 'block';
            errorDiv.textContent = 'Value must be at least 0. Negative numbers are not allowed.';
        } else if(input.id == 'exerciseRestTime' && number > 60) {
            input.value = '60';
            errorDiv.style.display = 'block';
        } else if (number > 100) {
            input.value = '100';
            errorDiv.style.display = 'block';
        } else {
            errorDiv.style.display = 'none';
        }
    }
</script>
@endsection
