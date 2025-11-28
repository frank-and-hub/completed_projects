@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Muscles')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Muscles</h4>
            <a href="" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMuscleModal">
                <img src="{{ asset('assets/images/add-circle.svg') }}" alt="plus-icon"> Add Muscle
            </a>
        </div>
        <div class="white-body-card ">
            <div class="filter-row align-items-center mb-3 d-flex">
                <form action="{{ route('admin.muscleIndex') }}" method="GET" class="d-flex align-items-center">
                    <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <input type="text" class="form-control" placeholder="Search by muscle name" name="search" value="{{ request('search') }}">
                        <span class="input-icon">
                            <img src="{{ asset('assets/images/search.svg') }}" alt="Search">
                        </span>
                    </div>
                    <button type="submit" class="btn btn-outline-primary search-btn mx-2">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('admin.muscleIndex') }}'">
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
                            <th style="width:80%">Muscle&nbsp;&nbsp;Name</th>
                            <th style="width:15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($muscleMasters as $singleInfo)
                            <tr>
                                <td>{{ ($muscleMasters->currentPage() - 1) * $muscleMasters->perPage() + $loop->iteration }}</td>
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
                                <td colspan="3" class="text-center"><strong>No data found</strong></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
              <!-- Pagination Section -->
              <div class="table-result mt-3">
                <select id="paginationLimit" class="form-select me-3" style="width:90px;">
                    <option value="10" {{ request('limit', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('limit') == 15 ? 'selected' : '' }}>15</option>
                    <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>20</option>
                </select>
                    <p class="mb-0">
                    @if ($muscleMasters->total() > 0)
                        Showing {{ $muscleMasters->firstItem() }} to {{ $muscleMasters->lastItem() }} of {{ $muscleMasters->total() }} entries
                    @else
                        No entries found
                    @endif
                </p>
                <nav class="ms-auto">
                    {{ $muscleMasters->links('pagination.custom') }}
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
                <h1 class="modal-title fs-5" id="addMuscleModalLabel">Add Muscle</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addMuscleForm" action="{{ route('admin.muscleSave') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label for="muscleName" class="form-label">Muscle Name<span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="muscleName" name="name" placeholder="Enter muscle name" maxlength="50">
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
                <h1 class="modal-title fs-5" id="editMuscleModalLabel">Edit Muscle</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMuscleForm" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label for="editMuscleName" class="form-label">Muscle Name<span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="editMuscleName" name="name" placeholder="Enter muscle name" maxlength="50">
                        </div>
                    </div>
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
                <h5 class="mb-3">Are you sure you want to delete this muscle?</h5>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
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

        // Handle add form submission
        $('#addMuscleForm').submit(function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // Basic client-side validation
            let isValid = true;
            if ($('#muscleName').val().trim() === '') {
                $('#muscleName').addClass('is-invalid');
                $('#muscleName').after('<div class="invalid-feedback">Muscle name is required</div>');
                isValid = false;
            }

            if (!isValid) return false;

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#addMuscleModal').modal('hide');
                    $('#successMessage').text('A new muscle added successfully!');
                    $('#successModal').modal('show');
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation errors from server
                        const errors = xhr.responseJSON.errors;
                        for (const field in errors) {
                            const input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');
                            input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                        }
                    } else {
                        // Other errors
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        });

        // Handle edit button click
        $(document).on('click', '.edit-muscle', function() {
            var muscleId = $(this).data('id');

            $.ajax({
                url: "{{ route('admin.muscleEdit', '') }}/" + muscleId,
                type: 'GET',
                success: function(response) {
                    $('#editMuscleName').val(response.name);
                    $('#editMuscleForm').attr('action', "{{ route('admin.muscleUpdate', '') }}/" + muscleId);
                    $('#editMuscleModal').modal('show');
                },
                error: function(xhr) {
                    alert('An error occurred while fetching muscle data.');
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
                $('#editMuscleName').after('<div class="invalid-feedback">Muscle name is required</div>');
                isValid = false;
            }

            if (!isValid) return false;

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editMuscleModal').modal('hide');
                    $('#successMessage').text('Muscle updated successfully!');
                    $('#successModal').modal('show');
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation errors from server
                        const errors = xhr.responseJSON.errors;
                        for (const field in errors) {
                            const input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');
                            input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                        }
                    } else {
                        // Other errors
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
                url: "{{ route('admin.muscleDelete', '') }}/" + muscleId,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#deleteMuscleModal').modal('hide');
                    $('#successMessage').text('Muscle deleted successfully!');
                    $('#successModal').modal('show');
                },
                error: function(xhr) {
                    alert('An error occurred while deleting the muscle.');
                }
            });
        });

        // Clear validation when modals are closed
        $('.modal').on('hidden.bs.modal', function() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#addMuscleForm')[0].reset();
        });
    });
</script>
@endpush
