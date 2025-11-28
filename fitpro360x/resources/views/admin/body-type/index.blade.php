@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Body Type')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Body Types</h4>
            <a href="" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMuscleModal">
                <img src="{{ asset('assets/images/add-circle.svg') }}" alt="plus-icon"> Add Body Type
            </a>
        </div>
        <div class="white-body-card ">
            <div class="filter-row align-items-center mb-3 d-flex">
                <form action="{{ route('admin.bodyTypeIndex') }}" method="GET" class="d-flex align-items-center">
                    <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <input type="text" class="form-control" placeholder="Search by body type" name="search" value="{{ request('search') }}">
                        <span class="input-icon">
                            <img src="{{ asset('assets/images/search.svg') }}" alt="Search">
                        </span>
                    </div>
                    <button type="submit" class="btn btn-outline-primary search-btn mx-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('admin.bodyTypeIndex') }}'">
                        <i class="fas fa-times"></i> Reset
                    </button>

                    <!-- Hidden field to maintain the current limit -->
                    <input type="hidden" name="limit" value="{{ request('limit', 10) }}">
                </form>

                <div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width:5%">S. No.</th>
                            <th style="width:10%">Image</th>
                            <th style="width:70%">Body&nbsp;&nbsp;Type</th>
                            <th style="width:25%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bodyTypes as $singleInfo)
                            <tr>
                                <td>{{ ($bodyTypes->currentPage() - 1) * $bodyTypes->perPage() + $loop->iteration }}</td>
                                <td>
                                    @if(!empty($singleInfo->image) && file_exists(public_path($singleInfo->image)))
                                        <img src="{{ asset($singleInfo->image) }}" alt="Image"
                                             style="width: 50px; height: 50px; object-fit: cover;border-radius:50%;">
                                    @else
                                        No image available
                                    @endif
                                </td>
                                <td>{{ $singleInfo->name }}</td>

                                <td>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <a href="javascript:void(0)" class="edit-muscle" data-id="{{ $singleInfo->id }}">
                                            <img src="{{ asset('assets/images/edittbtn.svg') }}" alt="Edit" title="Edit">
                                        </a>
                                        <a href="javascript:void(0)" class="delete-muscle" data-id="{{ $singleInfo->id }}">
                                            <img src="{{ asset('assets/images/delete-circle-btn.svg') }}" alt="Delete" title="Delete">
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center"><strong>No data found</strong></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
              <!-- Pagination Section -->
              <div class="table-result mt-3">
                <select id="paginationLimit" class="form-select me-3" style="width:70px;">
                    <option value="10" {{ request('limit', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('limit') == 15 ? 'selected' : '' }}>15</option>
                    <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>20</option>
                </select>
                    <p class="mb-0">
                    @if ($bodyTypes->total() > 0)
                        Showing {{ $bodyTypes->firstItem() }} to {{ $bodyTypes->lastItem() }} of {{ $bodyTypes->total() }} entries
                    @else
                        No entries found
                    @endif
                </p>
                <nav class="ms-auto">
                    {{ $bodyTypes->links('pagination.custom') }}
                </nav>
            </div>

        </div>
    </div>
</div>

<!-- Add Muscle Modal -->
<div class="modal fade" id="addMuscleModal" tabindex="-1" aria-labelledby="addMuscleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="addMuscleModalLabel">Add Body Type</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addMuscleForm" action="{{ route('admin.bodyTypeSave') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label for="muscleName" class="form-label">Body Type Name<span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="muscleName" name="name" placeholder="Enter body type name" maxlength="50">
                        </div>
                    </div>
                    <div class="mb-3 col-md-12">
                        <div id="addImagePreviewContainer" style="position: relative; display: none;">
                            <img id="addImagePreview" src="#" alt="Image Preview" class="img-thumbnail"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        </div>
                    </div>
                    <div class="col-md-12 uploa">
                        <div class="mb-3">
                            <label for="bodyTypeImage" class="form-label">Body Type Image<span style="color:red;">*</span></label>
                            <input class="form-control form-control-lg" id="bodyTypeImage" name="image" type="file" accept=".jpg,.jpeg,.png">
                            <small class="text-muted">Please upload a 120x120 pixel image for best view.</small>
                            <div id="addImageError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer ">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Muscle Modal -->
<div class="modal fade" id="editMuscleModal" tabindex="-1" aria-labelledby="editMuscleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editMuscleModalLabel">Edit Body Type</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMuscleForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label for="editMuscleName" class="form-label">Body Type Name<span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="editMuscleName" name="name" placeholder="Enter body type name" maxlength="50">
                        </div>
                    </div>
                    <div class="mb-3 col-md-12">
                        <div id="imagePreviewContainer" style="position: relative;">
                            <!-- Existing Image -->
                            <img id="existingImage" src="" alt="Current Image"
                                 class="img-thumbnail"
                                 style="width: 120px; height: 120px; object-fit: cover;">

                            <!-- New Image Preview (hidden initially) -->
                            <img id="editImagePreview" src="#" alt="New Image Preview"
                                 class="img-thumbnail"
                                 style="width: 120px; height: 120px; object-fit: cover; display: none;">
                        </div>
                    </div>

                    <div class="col-md-12 uploa">
                        <div class="mb-3">
                            <label for="editBodyTypeImage" class="form-label">Body Type Image<span style="color:red;">*</span></label>
                            <input class="form-control form-control-lg" id="editBodyTypeImage" name="image" type="file" accept=".jpg,.jpeg,.png">
                            <small class="text-muted">Please upload a 120x120 pixel image for best view.</small>
                            <div id="editImageError" class="text-danger mt-1" style="display: none;"></div>
                        </div>
                    </div>
                    <input type="hidden" name="delete_image" id="deleteImage" value="0">
                </div>
                <div class="modal-footer ">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteMuscleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <input type="hidden" id="deleteMuscleId">
                <h5 class="mb-3">Are you sure you want to delete this body type?</h5>
                <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <h5 class="mb-3" id="successMessage"></h5>
                <button type="button" class="btn btn-primary" id="successModalOk">OK</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $('#paginationLimit').on('change', function () {
        let limit = $(this).val();
        let url = new URL(window.location.href);

        // Set or update the `limit` param
        url.searchParams.set('limit', limit);

        // Reset the page number to 1
        url.searchParams.set('page', 1);

        // Preserve `search` filter
        const search = $('input[name="search"]').val();
        if (search) {
            url.searchParams.set('search', search);
        }
        window.location.href = url.toString();
    });

    $(document).ready(function() {
        // Handle success modal OK button click
        $(document).on('click', '#successModalOk', function() {
            $('#successModal').modal('hide');
            location.reload();
        });

        // Image validation and preview for add form
        $('#bodyTypeImage').on('change', function() {
            validateAndPreviewImage(this, 'addImagePreview', 'addImagePreviewContainer', 'addImageError');
        });

        // Image validation and preview for edit form
        $('#editBodyTypeImage').on('change', function() {
            validateAndPreviewImage(this, 'editImagePreview', 'imagePreviewContainer', 'editImageError');
        });

        // Handle add form submission
        $('#addMuscleForm').submit(function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let isValid = true;
            const name = $('#muscleName').val().trim();
            const image = $('#bodyTypeImage').prop('files')[0];

            if (name === '') {
                $('#muscleName').addClass('is-invalid');
                $('#muscleName').after('<div class="invalid-feedback">Body type name is required</div>');
                isValid = false;
            }

            if (!image) {
                $('#bodyTypeImage').addClass('is-invalid');
                $('#bodyTypeImage').after('<div class="invalid-feedback">Image is required</div>');
                isValid = false;
            }

            if (!isValid) return false;

            const formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#addMuscleModal').modal('hide');
                    $('#successMessage').text('A new body type added successfully!');
                    $('#successModal').modal('show');
                    $('#addMuscleForm')[0].reset();
                    $('#addImagePreviewContainer').hide();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        for (const field in errors) {
                            const input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');
                            input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                        }
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        });

        // Handle edit button click
        $(document).on('click', '.edit-muscle', function() {
            var muscleId = $(this).data('id');

            $.ajax({
                url: "{{ route('admin.bodyTypeEdit', '') }}/" + muscleId,
                type: 'GET',
                success: function(response) {
                    $('#editMuscleName').val(response.name);
                    $('#editMuscleForm').attr('action', "{{ route('admin.bodyTypeUpdate', '') }}/" + muscleId);
                    $('#deleteImage').val('0');

                    // Set image source and show container
                    if (response.image) {
                        var imageUrl = "{{ asset('') }}" + response.image;
                        $('#existingImage').attr('src', imageUrl).show();
                        $('#imagePreviewContainer').show();
                        $('#editImagePreview').hide();
                    } else {
                        $('#existingImage').attr('src', '');
                        $('#imagePreviewContainer').hide();
                    }

                    $('#editMuscleModal').modal('show');
                },
                error: function(xhr) {
                    alert('An error occurred while fetching body type data.');
                }
            });
        });

        // Handle edit form submission
        $('#editMuscleForm').submit(function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // Basic client-side validation
            let isValid = true;
            if ($('#editMuscleName').val().trim() === '') {
                $('#editMuscleName').addClass('is-invalid');
                $('#editMuscleName').after('<div class="invalid-feedback">Body type name is required</div>');
                isValid = false;
            }

            if (!isValid) return false;

            const formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#editMuscleModal').modal('hide');
                    $('#successMessage').text('Body type updated successfully!');
                    $('#successModal').modal('show');
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        for (const field in errors) {
                            const input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');
                            input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                        }
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        });

        // Handle delete button click
        $(document).on('click', '.delete-muscle', function() {
            var muscleId = $(this).data('id');
            $('#deleteMuscleId').val(muscleId);
            $('#deleteMuscleModal').modal('show');
        });

        // Handle confirm delete
        $('#confirmDelete').click(function() {
            var muscleId = $('#deleteMuscleId').val();

            $.ajax({
                url: "{{ route('admin.bodyTypeDelete', '') }}/" + muscleId,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#deleteMuscleModal').modal('hide');
                    $('#successMessage').text('Body type deleted successfully!');
                    $('#successModal').modal('show');
                },
                error: function(xhr) {
                    alert('An error occurred while deleting the body type.');
                }
            });
        });

        // Clear validation when modals are closed
        $('.modal').on('hidden.bs.modal', function() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#addMuscleForm')[0].reset();
            $('#addImagePreviewContainer').hide();
        });
    });

    function validateAndPreviewImage(input, previewId, previewContainerId, errorId) {
        const file = input.files[0];
        const errorElement = document.getElementById(errorId);
        const preview = document.getElementById(previewId);
        const previewContainer = document.getElementById(previewContainerId);

        // Reset previous state
        errorElement.style.display = 'none';
        errorElement.textContent = '';

        if (!file) return;

        // Check file type
        if (!file.type.match('image.*')) {
            errorElement.textContent = 'Please select an image file.';
            errorElement.style.display = 'block';
            input.value = '';
            return;
        }

        // Check file size (optional)
        if (file.size > 20 * 1024 * 1024) { // 2MB limit
            errorElement.textContent = 'Image size should be less than 20MB.';
            errorElement.style.display = 'block';
            input.value = '';

            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = '';
                preview.style.display = 'none'; // hide the image
            }
            return;
        }

        // Create image object to check dimensions
        const img = new Image();
        img.onload = function() {

            // If validation passes, show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                previewContainer.style.display = 'block';

                // For edit form, hide the existing image when new one is selected
                if (previewId === 'editImagePreview') {
                    document.getElementById('existingImage').style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        };
        img.onerror = function() {
            errorElement.textContent = 'Invalid image file.';
            errorElement.style.display = 'block';
            input.value = '';
        };
        img.src = URL.createObjectURL(file);
    }
    $('#editMuscleModal').on('show.bs.modal', function () {
    // Reset file input
    $('#editBodyTypeImage').val('');

    // Hide new image preview
    $('#editImagePreview').attr('src', '#').hide();

    // Reset any previous image error
    $('#editImageError').hide().text('');

    // Reset delete image value
    $('#deleteImage').val('0');
});
</script>
@endpush
