@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Add Exercise')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .is-invalid {
        border-color: #dc3545 !important;
    }

    .error-message {
        font-size: 0.875rem;
        margin-top: 0.25rem;
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

<form action="{{ route('admin.exerciseSave') }}" method="POST" enctype="multipart/form-data" id="addExerCise">
    @csrf
    <div class="container-fluid">
        <div class="pate-content-wrapper">
            <div class="page-title-row">
                <h5>Add Exercise</h5>
            </div>

            <div class="m-card-min-hight">
                <div class="row">

                    <!-- Exercise Name -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Exercise Name<span style="color:red;">*</span></label>
                            <input type="text" class="form-control form-control-lg" name="exercise_name"
                                value="{{ old('exercise_name') }}" placeholder="Enter exercise name" maxlength="50">
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
                                    <input class="form-check-input" type="radio" name="level" id="beginner"
                                        value="1" checked>
                                    <label class="form-check-label" for="beginner">
                                        Beginner
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="level" id="intermediate"
                                        value="2">
                                    <label class="form-check-label" for="intermediate">
                                        Intermediate
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="level" id="advance"
                                        value="3">
                                    <label class="form-check-label" for="advance">
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
                                <input class="form-check-input" type="radio" name="location" id="home"
                                    value="1" {{ old('location', 1) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="home">
                                    Home
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="location" id="gym"
                                    value="2" {{ old('location') == 2 ? 'checked' : '' }}>
                                <label class="form-check-label" for="gym">
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
                                <option disabled selected>Select Body Type</option>
                                @foreach ($bodyTypes as $id => $name)
                                <option value="{{ $id }}"
                                    {{ old('body_type_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
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
                                <option disabled>Select muscle</option>
                                @foreach ($muscles as $id => $name)
                                <option value="{{ $id }}"
                                    {{ old('muscle_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
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
                                value="{{ old('equipment') }}" placeholder="Enter Equipment" maxlength="50">
                            @error('equipment')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description<span
                                    style="color:red;">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter Description"
                                maxlength="500">{{ old('description') }}</textarea>
                            <div id="descriptionError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Image Upload -->
                        <div class="col-md-6 uploa">
                            <div class="mb-3">
                                <label for="editBodyTypeImage" class="form-label">Upload Image<span
                                        style="color:red;">*</span></label>
                                <input class="form-control form-control-lg" id="editBodyTypeImage" name="image"
                                    type="file" accept=".jpg,.jpeg,.png">
                                <small class="text-muted">Please upload a 120x120 pixel image for best view.</small>
                                <div id="editImageError" class="text-danger mt-1" style="display: none;"></div>
                            </div>
                        </div>

                        <!-- Video Upload -->
                        <div class="col-md-6 uploa">
                            <div class="mb-3">
                                <label class="form-label">Upload Video<span style="color:red;">*</span></label>
                                <input type="file" class="form-control form-control-lg" name="video"
                                    id="videoUpload" accept="video/mp4,video/webm,video/ogg">
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
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
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
        $('#imagePreviewContainer').hide();
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

        // Comprehensive form validation
        // function validateForm() {
        //     let isValid = true;

        //     // Validate exercise name
        //     const name = $('[name="name"]');
        //     if (!name.val().trim()) {
        //         showError(name, "Exercise name is required");
        //         isValid = false;
        //     } else {
        //         clearError(name);
        //     }

        //     // Validate body type selection
        //     const bodyType = $('[name="body_type_id"]');
        //     if (!bodyType.val()) {
        //         showError(bodyType, "Please select body type");
        //         isValid = false;
        //     } else {
        //         clearError(bodyType);
        //     }

        //     // Validate muscle selection
        //     const muscle = $('[name="muscle_id"]');
        //     if (!muscle.val()) {
        //         showError(muscle, "Please select muscle");
        //         isValid = false;
        //     } else {
        //         clearError(muscle);
        //     }

        //     // Validate equipment input
        //     const equipment = $('[name="equipment"]');
        //     if (!equipment.val().trim()) {
        //         showError(equipment, "Equipment is required");
        //         isValid = false;
        //     } else {
        //         clearError(equipment);
        //     }

        //     // Validate image upload
        //     const image = $('#editBodyTypeImage');
        //     if (!image.val()) {
        //         showError(image, "Image is required");
        //         isValid = false;
        //     } else {
        //         const allowedExtensions = ['jpg', 'jpeg', 'png'];
        //         const fileExt = image.val().split('.').pop().toLowerCase();
        //         if (!allowedExtensions.includes(fileExt)) {
        //             showError(image, "Allowed formats: jpg, jpeg, png");
        //             isValid = false;
        //         } else if (image[0].files[0].size > 2 * 1024 * 1024) {
        //             showError(image, "Image size must be less than 2MB");
        //             isValid = false;
        //         } else {
        //             clearError(image);
        //         }
        //     }

        //     // Validate video upload
        //     const video = $('[name="video"]');
        //     if (!video.val()) {
        //         showError(video, "Video is required");
        //         isValid = false;
        //     } else {
        //         const allowedVideoExtensions = ['mp4', 'webm', 'ogg'];
        //         const videoExt = video.val().split('.').pop().toLowerCase();
        //         if (!allowedVideoExtensions.includes(videoExt)) {
        //             showError(video, "Allowed formats: mp4, webm, ogg");
        //             isValid = false;
        //         } else if (video[0].files[0].size > 20 * 1024 * 1024) {
        //             showError(video, "Video size must be less than 20MB");
        //             isValid = false;
        //         } else {
        //             clearError(video);
        //         }
        //     }

        //     return isValid;
        // }
        // Replace your validateForm function with this corrected version
        function validateForm() {
            let isValid = true;

            // Validate exercise name (corrected selector)
            const name = $('[name="exercise_name"]');
            if (!name.val().trim()) {
                showError(name, "Exercise name is required");
                isValid = false;
            } else {
                clearError(name);
            }

            // Validate description
            const description = $('[name="description"]');
            if (!description.val().trim()) {
                showError(description, "Description is required");
                isValid = false;
            } else {
                clearError(description);
            }

            // Validate body type selection
            const bodyType = $('[name="body_type_id"]');
            if (!bodyType.val()) {
                showError(bodyType, "Please select body type");
                isValid = false;
            } else {
                clearError(bodyType);
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
            } else {
                clearError(equipment);
            }

            // Validate image upload
            const image = $('#editBodyTypeImage');
            if (!image.val()) {
                showError(image, "Image is required");
                isValid = false;
            } else {
                const file = image[0].files[0];
                if (file) {
                    const allowedExtensions = ['jpg', 'jpeg', 'png'];
                    const fileExt = image.val().split('.').pop().toLowerCase();
                    if (!allowedExtensions.includes(fileExt)) {
                        showError(image, "Allowed formats: jpg, jpeg, png");
                        isValid = false;
                    } else if (file.size > 20 * 1024 * 1024) {
                        showError(image, "Image size must be less than 20MB");
                        isValid = false;
                    } else {
                        clearError(image);
                    }
                }
            }

            // Validate video upload
            const video = $('[name="video"]');
            if (!video.val()) {
                showError(video, "Video is required");
                isValid = false;
            } else {
                const file = video[0].files[0];
                if (file) {
                    const allowedVideoExtensions = ['mp4', 'webm', 'ogg'];
                    const videoExt = video.val().split('.').pop().toLowerCase();
                    if (!allowedVideoExtensions.includes(videoExt)) {
                        showError(video, "Allowed formats: mp4, webm, ogg");
                        isValid = false;
                    } else if (file.size > 20 * 1024 * 1024) {
                        showError(video, "Video size must be less than 20MB");
                        isValid = false;
                    } else {
                        clearError(video);
                    }
                }
            }

            return isValid;
        }

        // Image preview handler
        $('#editBodyTypeImage').change(function() {
            const file = this.files[0];
            const errorElement = $('#editImageError');
            const previewContainer = $('#imagePreviewContainer');
            const preview = $('#editImagePreview');

            // Reset state
            errorElement.hide().text('');
            previewContainer.hide();

            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    errorElement.text('Only JPG and PNG images are allowed').show();
                    $(this).val('').addClass('is-invalid');
                    return;
                }

                // Validate file size
                if (file.size > 2 * 1024 * 1024) {
                    errorElement.text('Image size must be less than 20MB').show();
                    $(this).val('').addClass('is-invalid');
                    return;
                }

                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.show();
                    preview.attr('src', e.target.result).show();
                    $('#existingImage').hide();
                };
                reader.onerror = function() {
                    errorElement.text('Error loading image').show();
                    $(this).val('').addClass('is-invalid');
                };
                reader.readAsDataURL(file);
            }
        });

        // Clear errors when user corrects them
        $('input, select').on('input change', function() {
            clearError(this);
        });

        // Form submission handler
        $('#addExerCise').submit(function(e) {
            $('.error-message').remove();
            $('.is-invalid').removeClass('is-invalid');

            if (!validateForm()) {
                e.preventDefault();
                // Scroll to first error
                $('html, body').animate({
                    scrollTop: $('.is-invalid').first().offset().top - 100
                }, 500);
            }
        });
        $('.muscle-trained-select2').select2({
            placeholder: 'Select Muscle',
            width: '100%'
        });
    });
</script>
@endpush