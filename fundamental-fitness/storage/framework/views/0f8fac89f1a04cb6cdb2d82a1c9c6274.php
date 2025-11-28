<?php $__env->startSection('content'); ?>
<?php $__env->startSection('admin-title', 'Edit workout plan'); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<form action="<?php echo e(route('admin.workoutPlansUpdate', $workout->id)); ?>" method="POST" enctype="multipart/form-data"
    id="editExerCise">
    <?php echo csrf_field(); ?>
    <?php echo method_field('POST'); ?>
    <input type="hidden" name="weeks_data" id="weeks_data" value="<?php echo e(json_encode($workout->getFormattedWeeksData())); ?>">

    <div class="container-fluid">
        <div class="pate-content-wrapper">
            <div class="page-title-row">
                <h5>Edit workout plan</h5>
            </div>
            <div class="m-card-min-hight">
                <div class="row create-workout-form">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title<span style="color:red;">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control form-control-lg" id="title" name="title"
                                    placeholder="Enter title" value="<?php echo e(old('title', $workout->title)); ?>"
                                    maxlength="50">
                                <div id="workoutTitleError" class="text-danger mt-1" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration (Weeks)<span
                                    style="color:red;">*</span></label>
                            <div class="position-relative">
                                <input type="number" class="form-control form-control-lg" id="duration"
                                    name="duration_weeks" placeholder="Enter duration in weeks" min="1"
                                    value="<?php echo e(old('duration_weeks', $workout->duration_weeks)); ?>">
                                <div id="durationError" class="text-danger mt-1" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-6 uploa">
                        <div class="mb-3">
                            <label for="workoutImage" class="form-label">Upload Image</label>
                            <input class="form-control form-control-lg" id="workoutImage" name="image" type="file"
                                accept=".jpg,.jpeg,.png">
                            <div id="workoutImageError" class="text-danger mt-1" style="display: none;"></div>
                            <small class="text-muted">Please upload a 120x120 pixel image for best view.</small>
                            <div id="editImageError" class="text-danger mt-1" style="display: none;"></div>
                            <?php if($workout->image): ?>
                                <div class="my-2 workout-plan-image">
                                    <img src="<?php echo e(asset($workout->image)); ?>" width="100" class="img-thumbnail">
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-14 font-400">Fitness&nbsp;&nbsp;Level<span
                                style="color:red;">*</span></label>
                        
                        <div class="">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level" id="level"
                                    value="1" <?php echo e($workout->level == 1 ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="level">
                                    Beginner
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level" id="level-2"
                                    value="2" <?php echo e($workout->level == 2 ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="level-2">
                                    Intermediate
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="level" id="level-3"
                                    value="3" <?php echo e($workout->level == 3 ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="level-3">
                                    Advance
                                </label>
                            </div>
                        </div>

                        <?php $__errorArgs = ['level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="text-danger"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description<span
                                    style="color:red;">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter description"
                                maxlength="255"><?php echo e(old('description', $workout->description)); ?></textarea>
                            <div id="descriptionError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="exercise-container">Create Exercises</label>
                    <div id="weeks-container">
                        <!-- Weeks will be dynamically added here -->
                    </div>
                </div>

                <div class="mb-3">
                    <a href="#" class="btn btn-primary w-100" id="add-week-btn">Add New Week</a>
                </div>

                <div class="text-end mb-3">
                    <a href="<?php echo e(route('admin.workoutPlansIndex')); ?>"
                        class="btn btn-outline-primary btn-sm me-2">Cancel</a>
                    <button type="submit" name="action" value="save" class="btn btn-primary btn-sm"
                        id="submitBtn">Save</button>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Exercise Modal -->
    <div class="modal fade" id="exerciseModal" tabindex="-1" aria-labelledby="exerciseModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exerciseModalLabel">Add Exercise</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label for="exerciseSelect" class="form-label">Select Exercise<span
                                    style="color:red;">*</span></label>
                            <select class="form-select" id="exerciseSelect" aria-label="Default select example">
                                <option selected value="">Select</option>
                                <?php $__currentLoopData = $exercises; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exercise): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($exercise->id); ?>"><?php echo e($exercise->exercise_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div id="exerciseSelectError" class="text-danger mt-1" style="display: none;"></div>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="exerciseReps" class="form-label">Reps<span
                                    style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="exerciseReps"
                                placeholder="Enter Reps (max-100)" min="1" max="100"
                                oninput="validateDuration(this)">
                            <div id="exerciseRepsError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="exerciseSets" class="form-label">Sets<span
                                    style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="exerciseSets"
                                placeholder="Enter Sets (max-100)" min="1" max="100"
                                oninput="validateDuration(this)">
                            <div id="exerciseSetsError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="exerciseRestTime" class="form-label">Rest Time (Seconds)<span
                                    style="color:red;">*</span></label>
                            <input type="number" class="form-control" id="exerciseRestTime"
                                placeholder="Enter Rest Time (max-1800)"
                                value="<?php echo e(old('restTime', $workout->restTime)); ?>" oninput="validateRestSeconds(this)"
                                max="1800" min="1">
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
        //  console.log('++++====== hhhhhhhhhhhh ===========');
        // Variables to track current week and day for exercise addition
        let currentWeek = null;
        let currentDay = null;
        let exercises = <?php echo json_encode($exercises, 15, 512) ?>;
        let editingExerciseRow = null;
        let exerciseCounter = 1;

        // Initialize the form with existing data
        function initializeForm() {
            const weeksData = JSON.parse($('#weeks_data').val());

            // Clear existing content
            $('#weeks-container').empty();
            // Add weeks and days
            weeksData.forEach(weekData => {
                addWeek(weekData.week);

                const weekElement = $(`[data-week-number="${weekData.week}"]`);

                weekData.days.forEach(dayData => {
                    addDay(weekElement, dayData.day);

                    const dayElement = weekElement.find(`[data-day-number="${dayData.day}"]`);

                    // Set rest day status
                    if (dayData.is_rest_day) {
                        dayElement.find('.rest-day-toggle').prop('checked', true).trigger(
                            'change');
                    }

                    // Add exercises
                    dayData.exercises.forEach(exercise => {
                        addExerciseToDay(dayElement, exercise);
                    });
                });
            });
        }

        // Add a new week to the container
        function addWeek(weekNumber) {
            const weekHtml = `
            <div class="create-exercise-section week mt-3" data-week-number="${weekNumber}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="mb-0 text-14 font-500">Week ${weekNumber}</p>
                    <button class="btn btn-primary delete-week-btn">Delete Week</button>
                </div>
                <hr style="margin: 1rem -20px; color: #cccccc; border: 0; border-top: var(--bs-border-width) solid; opacity: .25;">
                <div class="days-container" id="days-container-${weekNumber}"></div>
                <div class="mb-3">
                    <a href="#" class="btn btn-outline-primary w-100 add-day-btn">Add More Days</a>
                </div>
            </div>
        `;
            $('#weeks-container').append(weekHtml);
        }

        // Add a new day to a week
        function addDay(weekElement, dayNumber) {
            const daysContainer = weekElement.find('.days-container');
            const dayHtml = `
            <div class="pink-bg mb-3 day" data-day-number="${dayNumber}" id="day-${dayNumber}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="mb-0 text-14 font-500">Day ${dayNumber}</p>
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
                        <a href="#" class="btn btn-primary add-exercise-btn" data-bs-toggle="modal" data-bs-target="#exerciseModal">Add Exercise</a>
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
                                <th>Rest Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="exercises-table-${dayNumber}">
                            <tr class="no-exercises">
                                <td colspan="6" class="text-center">No exercises added yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
            daysContainer.append(dayHtml);

            // Hide add day button if we have 7 days
            if (daysContainer.find('.day').length >= 7) {
                daysContainer.next('.mb-3').hide();
            }
        }

        // Add an exercise to a day
        function addExerciseToDay(dayElement, exercise) {
            console.log('++++', exercise);

            const exerciseTable = dayElement.find('tbody');
            const exerciseRow = `
            <tr data-exercise-id="${exercise.exercise_id}" id="exercise-row-${exerciseCounter++}">
                <td>${exerciseTable.find('tr').not('.no-exercises').length + 1}.</td>
                <td>${exercise.name}</td>
                <td>${exercise.reps}</td>
                <td>${exercise.sets}</td>
                <td>${exercise.rest_time}</td>
                <td>
                    <a href="#" class="edit-exercise me-2"><img src="<?php echo e(asset('assets/images/edittbtn.svg')); ?>" alt="Edit"></a>
                    <a href="#" class="delete-exercise"><img src="<?php echo e(asset('assets/images/deletebtn.svg')); ?>" alt="Delete"></a>
                </td>
            </tr>
        `;

            if (exerciseTable.find('tr.no-exercises').length > 0) {
                exerciseTable.html(exerciseRow);
            } else {
                exerciseTable.append(exerciseRow);
            }
        }

        // Initialize the form when page loads
        initializeForm();

        // Form validation on submit
        $('#editExerCise').on('submit', function(e) {
            e.preventDefault();
            let isValid = true;

            // Reset error messages
            $('.text-danger').hide();

            // Validate fields
            const title = $('#title').val().trim();
            if (!title) {
                $('#workoutTitleError').text('Title is required').show();
                isValid = false;
            }

            /*const goal = $('#goal').val().trim();
            if (!goal) {
                $('#goalError').text('Goal is required').show();
                isValid = false;
            }*/

            const duration = $('#duration').val();
            if (!duration) {
                $('#durationError').text('Duration is required').show();
                isValid = false;
            }

            const description = $('#description').val().trim();
            if (!description) {
                $('#descriptionError').text('Description is required').show();
                isValid = false;
            }

            // Validate Image
            const image = $('#workoutImage')[0].files[0];
            if (!image) {
                //$('#workoutImageError').text('Image is required').show();
                //isValid = false;
            } else {
                // Check file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(image.type)) {
                    $('#workoutImageError').text('Only JPG, JPEG, and PNG files are allowed').show();
                    isValid = false;
                }

                // Check file size (2MB max)
                if (image.size > 20 * 1024 * 1024) {
                    $('#workoutImageError').text('Image size must be less than 20MB').show();
                    isValid = false;
                }
            }


            // Validate at least one week and day exists
            if ($('.week').length === 0) {
                alert('Please add at least one week to the workout');
                isValid = false;
            } else {
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

                if (!hasExercise) {
                    alert('Please add at least one exercise to one of the days');
                    isValid = false;
                }
            }

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
                            const restTime = $(this).find('td:nth-child(5)').text();

                            dayData.exercises.push({
                                exercise_id: exerciseId,
                                name: exerciseName,
                                reps: reps,
                                sets: sets,
                                rest_time: restTime,
                                order: $(this).index()
                            });
                        });
                    }

                    weekData.days.push(dayData);
                });

                weeksData.push(weekData);
            });

            $('#weeks_data').val(JSON.stringify(weeksData));
        }


        $('#workoutImage').on('change', function() {

            console.log('workoutImageError===============');

            $('.workout-plan-image').hide(); // Remove existing image preview
            $('#workoutImageError').hide(); // Hide error message
            const file = this.files[0];
            if (!file) {
                $('#workoutImageError').hide(); // Image is optional for updates
                return;
            }

            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                $('#workoutImageError').text('Only JPG, JPEG, and PNG files are allowed').show();
                return;
            }

            if (file.size > 20 * 1024 * 1024) {
                $('#workoutImageError').text('Image size must be less than 20MB').show();
                return;
            }

            $('#workoutImageError').hide();
        });
        // Add new week
        $('#add-week-btn').click(function(e) {
            e.preventDefault();
            const weekCount = $('.week').length;
            const newWeekNumber = weekCount + 1;
            addWeek(newWeekNumber);
        });

        // Add new day to a week
        $(document).on('click', '.add-day-btn', function(e) {
            e.preventDefault();
            const weekContainer = $(this).closest('.week');
            const dayCount = weekContainer.find('.day').length;
            const newDayNumber = dayCount + 1;
            addDay(weekContainer, newDayNumber);
        });

        // Delete week
        $(document).on('click', '.delete-week-btn', function(e) {
            e.preventDefault();
            const weekToDelete = $(this).closest('.week');
            if ($('.week').length > 1) {
                weekToDelete.remove();
                // Renumber remaining weeks
                $('.week').each(function(index) {
                    const newWeekNumber = index + 1;
                    $(this).attr('data-week-number', newWeekNumber);
                    $(this).find('p.text-14:first').text(`Week ${newWeekNumber}`);
                });
            } else {
                alert('You must have at least one week in the workout.');
            }
        });

        // Delete day
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

                // Show add button if less than 7 days
                if (weekContainer.find('.day').length < 7) {
                    weekContainer.find('.add-day-btn').closest('.mb-3').show();
                }
            } else {
                alert('You must have at least one day in the week.');
            }
        });

        // Rest day toggle
        $(document).on('change', '.rest-day-toggle', function() {
            const dayContainer = $(this).closest('.day');
            const tableBody = dayContainer.find('tbody');
            const addExerciseBtn = dayContainer.find('.add-exercise-btn');

            if ($(this).is(':checked')) {
                tableBody.html('<tr><td colspan="6" class="text-center">Rest Day Enabled</td></tr>');
                addExerciseBtn.addClass('disabled').attr('disabled', true);
            } else {
                tableBody.html(
                    '<tr class="no-exercises"><td colspan="6" class="text-center">No exercises added yet</td></tr>'
                );
                addExerciseBtn.removeClass('disabled').attr('disabled', false);
            }
        });

          $('#exerciseSelect').on('change', function() {
            const exerciseId = $(this).val();

              $('#exerciseSelectError').hide();

            // console.log('+++',exerciseId);
            
            if (!exerciseId) return;
          });
        // Set current day when clicking add exercise
        $(document).on('click', '.add-exercise-btn', function(e) {
            e.preventDefault();
            currentDay = $(this).closest('.day');
            currentWeek = currentDay.closest('.week');
            $('#exerciseModalLabel').text('Add Exercise');
            $('#saveExerciseBtn').text('Save');
            editingExerciseRow = null;
            $('#exerciseSelect').val('');
            $('#exerciseReps').val('');
            $('#exerciseSets').val('');
            $('#exerciseRestTime').val('');
        });

        // Edit exercise
        $(document).on('click', '.edit-exercise', function(e) {
            e.preventDefault();
            editingExerciseRow = $(this).closest('tr');
            currentDay = editingExerciseRow.closest('.day');
            currentWeek = currentDay.closest('.week');

            const exerciseId = editingExerciseRow.data('exercise-id');
            const reps = editingExerciseRow.find('td:nth-child(3)').text();
            const sets = editingExerciseRow.find('td:nth-child(4)').text();
            const restTime = editingExerciseRow.find('td:nth-child(5)').text();
        
            $('#exerciseSelect').val(exerciseId);
            $('#exerciseReps').val(reps);
            $('#exerciseSets').val(sets);
            $('#exerciseRestTime').val(restTime);
            $('#exerciseModalLabel').text('Edit Exercise');
            $('#saveExerciseBtn').text('Update');
            $('#exerciseModal').modal('show');
        });

        // Save exercise
        $('#saveExerciseBtn').click(function() {
            let isValid = true;

            // Reset error messages
            $('#exerciseSelectError, #exerciseRepsError, #exerciseSetsError, #exerciseRestTimeError')
                .hide();

            // Validate fields
            const exerciseId = $('#exerciseSelect').val();

              $('#exerciseSelectError').hide();
            if (!exerciseId) {
                $('#exerciseSelectError').text('Please select an exercise').show();
                isValid = false;
            }

            const reps = $('#exerciseReps').val();
            if (!reps) {
                $('#exerciseRepsError').text('Reps are required').show();
                isValid = false;
            }

            const sets = $('#exerciseSets').val();
            if (!sets) {
                $('#exerciseSetsError').text('Sets are required').show();
                isValid = false;
            }

            const restTime = $('#exerciseRestTime').val();
            if (!restTime) {
                $('#exerciseRestTimeError').text('Rest time is required').show();
                isValid = false;
            }

            if (!isValid) return;

            const exercise = exercises.find(e => e.id == exerciseId);
            const exerciseName = exercise ? exercise.exercise_name : 'Unknown Exercise';

            if (editingExerciseRow) {
                // Update existing row
                editingExerciseRow.data('exercise-id', exerciseId);
                editingExerciseRow.find('td:nth-child(2)').text(exerciseName);
                editingExerciseRow.find('td:nth-child(3)').text(reps);
                editingExerciseRow.find('td:nth-child(4)').text(sets);
                editingExerciseRow.find('td:nth-child(5)').text(restTime);
            } else {
                // Add new exercise
                const exerciseTable = currentDay.find('tbody');
                const exerciseCount = exerciseTable.find('tr').not('.no-exercises').length;
                const exerciseRow = `
                <tr data-exercise-id="${exerciseId}" id="exercise-row-${exerciseCounter++}">
                    <td>${exerciseCount + 1}.</td>
                    <td>${exerciseName}</td>
                    <td>${reps}</td>
                    <td>${sets}</td>
                    <td>${restTime}</td>
                    <td>
                        <a href="#" class="edit-exercise me-2"><img src="<?php echo e(asset('assets/images/edittbtn.svg')); ?>" alt="Edit"></a>
                        <a href="#" class="delete-exercise"><img src="<?php echo e(asset('assets/images/deletebtn.svg')); ?>" alt="Delete"></a>
                    </td>
                </tr>
            `;

                if (exerciseTable.find('tr.no-exercises').length > 0) {
                    exerciseTable.html(exerciseRow);
                } else {
                    exerciseTable.append(exerciseRow);
                }
            }

            $('#exerciseModal').modal('hide');
        });

        // Delete exercise
        $(document).on('click', '.delete-exercise', function(e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            const table = $(this).closest('tbody');

            // Update serial numbers
            table.find('tr').not('.no-exercises').each(function(index) {
                $(this).find('td:first').text((index + 1) + '.');
            });

            // Show placeholder if no exercises left
            if (table.find('tr').not('.no-exercises').length === 0) {
                table.html(
                    '<tr class="no-exercises"><td colspan="6" class="text-center">No exercises added yet</td></tr>'
                );
            }
        });

        // Exercise search
        $(document).on('input', '.exercise-search', function() {
            const searchTerm = $(this).val().toLowerCase();
            const dayContainer = $(this).closest('.day');

            dayContainer.find('tbody tr').each(function() {
                const exerciseName = $(this).find('td:nth-child(2)').text().toLowerCase();
                if (exerciseName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
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
        } else if (number > 1800) {
            input.value = '1800';
            errorDiv.style.display = 'block';
            // errorDiv.textContent = 'Value cannot exceed 100. It has been set to 100.';
        } else {
            errorDiv.style.display = 'none';
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/app.fundamental-fit.co.uk/releases/20251124_065248/resources/views/admin/workout-plan/edit.blade.php ENDPATH**/ ?>