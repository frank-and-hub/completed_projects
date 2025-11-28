@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Edit Exercise')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .is-invalid {
            border-color: #dc3545 !important;
        }

        .error-message,
        .file-error-message {
            font-size: 0.875rem;
            margin-top: 0.25rem;
            color: #dc3545;
            display: block;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #ffe5e5;
            color: #b30000;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #fff0f0;
            color: #cc0000;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #ffffff !important;
            border: 1px solid #d9d9d9 !important;
            border-radius: 30px !important;
            padding-left: 0 !important;
            padding-right: 10px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            border-right: 0 !important;
            border-top-left-radius: 0 !important;

        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            background-color: #333 !important;
            left: auto !important;
            right: 0 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
            padding-left: 8px !important;
            padding-right: 20px !important;
            font-size: 12px !important;
            font-weight: 500;
            color: #333 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            background-color: #ff7043 !important;
            left: auto !important;
            right: 0 !important;
            border-radius: 50% !important;
            margin-right: 5px;
            width: 16px !important;
            height: 16px !important;
            font-size: 11px !important;
            color: #fff !important;
            top: 2px;
        }
    </style>
@endpush

<form action="{{ route('admin.exerciseUpdate', $exercise->id) }}" method="POST" enctype="multipart/form-data"
    id="editExercise">
    @csrf
    <div class="container-fluid">
        <div class="pate-content-wrapper">
            <div class="page-title-row">
                <h5>Edit Exercise</h5>
            </div>
            <div class="m-card-min-hight">
                <div class="row">

                    <!-- Exercise Name -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Exercise name<span style="color:red;">*</span></label>
                            <input type="text" class="form-control form-control-lg" name="exercise_name"
                                value="{{ old('exercise_name', $exercise->exercise_name) }}"
                                placeholder="Enter exercise name" maxlength="50">
                            @error('exercise_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Level -->
                    <div class="col-md-4 mb-3">
                        <label class="text-14 font-400">Difficulty&nbsp;&nbsp;Level<span
                                style="color:red;">*</span></label>
                        <div class="checkbox-bg">
                            <div class="">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="level" id="levelBeginner"
                                        value="1" {{ old('level', $exercise->level) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="levelBeginner">
                                        Beginner
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="level" id="levelIntermediate"
                                        value="2" {{ old('level', $exercise->level) == 2 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="levelIntermediate">
                                        Intermediate
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="level" id="levelAdvance"
                                        value="3" {{ old('level', $exercise->level) == 3 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="levelAdvance">
                                        Advance
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('level')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Location -->
                    <div class="col-md-2 mb-3">
                        <label class="text-14 font-400">Location<span style="color:red;">*</span></label>
                        <div class="checkbox-bg">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="location" id="locationHome"
                                    value="1" {{ old('location', $exercise->location) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="locationHome">
                                    Home
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="location" id="locationGym"
                                    value="2" {{ old('location', $exercise->location) == 2 ? 'checked' : '' }}>
                                <label class="form-check-label" for="locationGym">
                                    Gym
                                </label>
                            </div>
                        </div>
                        @error('location')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Body Type -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Body Type<span style="color:red;">*</span></label>
                            <select name="body_type_id" class="form-select">
                                <option disabled>Select Body Type</option>
                                @foreach ($bodyTypes as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ old('body_type_id', $exercise->body_type_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}</option>
                                @endforeach
                            </select>
                            @error('body_type_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Muscle -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Muscle Trained<span style="color:red;">*</span></label>
                            <select name="muscle_id[]" multiple class="form-select muscle-trained-select2">
                                <option disabled>Select Muscle</option>
                                @foreach ($muscles as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ in_array($id, old('muscle_id', $exercise->muscle_trained->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('muscle_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Equipment -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Equipment<span style="color:red;">*</span></label>
                            <input type="text" class="form-control form-control-lg" name="equipment"
                                value="{{ old('equipment', $exercise->equipment) }}" placeholder="Enter equipment"
                                maxlength="50">
                            @error('equipment')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description<span
                                    style="color:red;">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter description"
                                maxlength="500">{{ old('description', $exercise->description) }}</textarea>
                            <div id="descriptionError" class="text-danger mt-1" style="display: none;"></div>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6"></div>

                    <div class="row">
                        <!-- Image Preview + Upload -->
                        <div class="col-md-6 uploa">
                            <div class="mb-3">
                                <label for="editBodyTypeImage" class="form-label">Upload Image</label>
                                <div id="imagePreviewContainer"
                                    style="position: relative; display: {{ $exercise->image ? 'block' : 'none' }};">
                                    @if ($exercise->image)
                                        <img id="existingImage" src="{{ asset($exercise->image) }}"
                                            alt="Current Image" class="img-thumbnail mb-2"
                                            style="width: 120px; height: 120px; object-fit: cover;">
                                    @endif
                                    <img id="editImagePreview" src="#" alt="New Image Preview"
                                        class="img-thumbnail mb-2"
                                        style="width: 120px; height: 120px; object-fit: cover; display: none;">
                                </div>
                                <input class="form-control form-control-lg" id="editBodyTypeImage" name="image"
                                    type="file" accept=".jpg,.jpeg,.png">
                                <small class="text-muted">Please upload a 120x120 pixel image for best view.</small>
                                <div id="editImageError" class="text-danger mt-1" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- Video Preview + Upload -->
                        <div class="col-md-6 uploa">
                            <div class="mb-3">
                                <label class="form-label">Upload Video </label>
                                @if ($exercise->video)
                                    <div class="mb-2">
                                        <video width="200" controls>
                                            <source src="{{ asset($exercise->video) }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                @endif
                                <input type="file" class="form-control form-control-lg" name="video"
                                    id="videoUpload" accept="video/mp4,video/webm,video/ogg"
                                    onchange="showVideoFileName(this)">
                                <small class="text-muted">Accepted formats: MP4, WebM, OGG.</small>
                                <div id="videoError" class="text-danger mt-1" style="display: none;"></div>
                                @error('video')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Submit Buttons -->
                <div class="mb-3">
                    <a href="{{ route('admin.exerciseIndex') }}"
                        class="btn btn-outline-primary btn-sm me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize preview containers
        $('#editImagePreview').hide();

        // Enhanced error display function
        function showError(input, message) {
            const $input = $(input);

            // Add red border
            $input.addClass('is-invalid');

            if ($input.hasClass('select2-hidden-accessible')) {
                const $select2Container = $input.next('.select2-container');
                $select2Container.addClass('is-invalid');

                if (!$select2Container.next('.error-message').length) {
                    $select2Container.after(
                        `<span class="text-danger error-message d-block mt-1">${message}</span>`);
                } else {
                    $select2Container.next('.error-message').text(message);
                }

                return; 
            }


            // For file inputs with custom containers
            if ($input.closest('.filesUpload').length) {
                const container = $input.closest('.filesUpload');
                let errorElement = container.next('.error-message');
                if (!errorElement.length) {
                    container.after(`<span class="text-danger error-message d-block mt-1">${message}</span>`);
                } else {
                    errorElement.text(message);
                }
            }
            // For regular inputs and selects
            else {
                let errorElement = $input.next('.error-message');
                if (!errorElement.length) {
                    $input.after(`<span class="text-danger error-message d-block mt-1">${message}</span>`);
                } else {
                    errorElement.text(message);
                }
            }
        }

        // Enhanced clear error function
        function clearError(input) {
            const $input = $(input);
            $input.removeClass('is-invalid');

            if ($input.hasClass('select2-hidden-accessible')) {
                const $select2Container = $input.next('.select2-container');
                $select2Container.removeClass('is-invalid');
                $select2Container.next('.error-message').remove();
            } else {
                $input.next('.error-message').remove();
            }
        }

        // Image validation function
        function validateImage(input) {
            const file = input.files[0];
            const errorElement = $(input).closest('.mb-3').find('.file-error-message');

            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    showError(input, 'Only JPG and PNG images are allowed');
                    return false;
                }

                // Validate file size
                if (file.size > 20 * 1024 * 1024) {
                    showError(input, 'Image size must be less than 20MB');
                    return false;
                }

                return true;
            }

            return true; // No file is valid for edit form
        }

        // Image preview handler
        $('#editBodyTypeImage').change(function() {
            const file = this.files[0];
            const previewContainer = $('#imagePreviewContainer');
            const preview = $('#editImagePreview');

            // Clear previous errors
            clearError(this);

            // Validate the image
            if (!validateImage(this)) {
                previewContainer.hide();
                return;
            }

            previewContainer.show();

            if (file) {
                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.attr('src', e.target.result).show();
                    $('#existingImage').hide();
                };
                reader.onerror = function() {
                    showError(this, 'Error loading image');
                    previewContainer.hide();
                };
                reader.readAsDataURL(file);
            } else {
                // No file selected, show existing image if it exists
                if ($('#existingImage').length) {
                    $('#existingImage').show();
                    preview.hide();
                } else {
                    previewContainer.hide();
                }
            }
        });

        // Video validation function
        function validateVideo(input) {
            const file = input.files[0];

            if (file) {
                // Validate file type
                const allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
                if (!allowedTypes.includes(file.type)) {
                    showError(input, 'Allowed formats: mp4, webm, ogg');
                    return false;
                }

                // Validate file size
                if (file.size > 20 * 1024 * 1024) {
                    showError(input, 'Video size must be less than 20MB');
                    return false;
                }
            }

            return true;
        }

        // Video change handler
        $('#videoUpload').change(function() {
            clearError(this);
            validateVideo(this);
        });

        // Clear errors when user corrects them
        $('input, select').on('input change', function() {
            clearError(this);
        });

        // Comprehensive form validation
        function validateForm() {
            let isValid = true;

            // Validate exercise name
            const exerciseName = $('[name="exercise_name"]');
            if (!exerciseName.val().trim()) {
                showError(exerciseName, "Exercise name is required");
                isValid = false;
            }

            // Validate level selection
            const level = $('[name="level"]:checked');
            if (!level.length) {
                showError($('[name="level"]').first(), "Please select a level");
                isValid = false;
            }

            // Validate location selection
            const location = $('[name="location"]:checked');
            if (!location.length) {
                showError($('[name="location"]').first(), "Please select a location");
                isValid = false;
            }

            // Validate body type selection
            const bodyType = $('[name="body_type_id"]');
            if (!bodyType.val()) {
                showError(bodyType, "Please select body type");
                isValid = false;
            }

            // Validate muscle selection
            const muscle = $('[name="muscle_id[]"]');
            if (!muscle.val() || muscle.val().length === 0) {
                showError(muscle, "Please select at least one muscle");
                isValid = false;
            } else {
                clearError(muscle);
            }

            // Validate equipment input
            const equipment = $('[name="equipment"]');
            if (!equipment.val().trim()) {
                showError(equipment, "Equipment is required");
                isValid = false;
            }

            // Validate image upload (only if a file is selected)
            const imageInput = $('#editBodyTypeImage')[0];
            if (imageInput.files.length > 0) {
                isValid = validateImage(imageInput) && isValid;
            }

            // Validate video upload (only if a file is selected)
            const videoInput = $('#videoUpload')[0];
            if (videoInput.files.length > 0) {
                isValid = validateVideo(videoInput) && isValid;
            }

            return isValid;
        }

        // Form submission handler
        $('#editExercise').on('submit', function(e) {
            // Clear previous errors
            $('.error-message').remove();
            $('.file-error-message').empty();
            $('.is-invalid').removeClass('is-invalid');

            if (!validateForm()) {
                e.preventDefault();
                // Scroll to first error
                // $('html, body').animate({
                //     scrollTop: $('.is-invalid').first().offset().top - 100
                // }, 500);
            }
        });

        $('.muscle-trained-select2').select2({
            placeholder: 'Select meal(s)',
            width: '100%'
        });
    });
</script>
@endpush
