@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Manage Meso')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Manage Meso</h4>
            {{-- <button class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#addMesoModal">Add Meso</button> --}}
        </div>
        <div class="m-card-min-hight">
            <div class="filter-row-search justify-content-between">
                <form method="GET" action="{{ route('admin.mesoCycleIndex') }}" class="d-flex mb-2 align-items-center">
                    <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                            class="form-control icon-holder me-2" placeholder="Search with name">
                    </div>

                    <!-- Workout Frequency Filter -->
                    {{-- <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <select class="form-select" name="workout_frequency" style="width:190px;">
                            <option value="">All Workout Frequency</option>
                            @foreach($frequencies as $frequency)
                                <option value="{{ $frequency->id }}" {{ request('workout_frequency') == $frequency->id ? 'selected' : '' }}>{{ $frequency->name }}</option>
                            @endforeach
                        </select>
                    </div> --}}

                    <!-- Weeks Filter -->
                    <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <select class="form-select" name="weeks" style="width:150px;">
                            <option value="">All Weeks</option>
                            @foreach(\App\Constants\WeekConstants::Frequency['mesho_weeks'] as $week)
                                <option value="{{ $week }}" {{ request('weeks') == $week ? 'selected' : '' }}>
                                    {{ $week }} {{ $week > 1 ? 'Weeks' : 'Week' }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <button type="submit" class="btn btn-outline-primary search-btn mx-2">Search</button>
                    <a href="{{ route('admin.mesoCycleIndex') }}" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width:5%">S. NO.</th>
                            <th style="width:20%">NAME</th>
                            {{-- <th>WORKOUT FREQUENCY</th> --}}
                            <th style="width:25%">WEEKS</th>
                            <th style="width:25%">EXERCISES</th>
                            <th style="width:25%">CREATED DATE</th>
                            {{-- <th style="width:10%">ACTION</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($allMeso as $meso)
                            <tr>
                                <td>{{ ($allMeso->currentPage() - 1) * $allMeso->perPage() + $loop->iteration }}</td>
                                <td>{{ $meso->name }}</td>
                                {{-- <td>{{ $meso->frequency->name ?? '-' }}</td> --}}
                                <td>{{ $meso->week_number }} {{ $meso->week_number > 1 ? 'Weeks' : 'Week' }}</td>

                                <td>{{ $meso->exercises ?? '10' }}</td>
                                <td>{{ $meso->created_at->format('d/m/Y') }}</td>
                                {{-- <td>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <a href="javascript:void(0)" class="edit-meso" data-id="{{ $meso->id }}">
                                            <img src="{{ asset('assets/images/edittbtn.svg') }}" alt="Edit" title="Edit">
                                        </a>
                                        <a href="javascript:void(0)" class="delete-meso" data-id="{{ $meso->id }}">
                                            <img src="{{ asset('assets/images/delete-circle-btn.svg') }}" alt="Delete" title="Delete">
                                        </a>
                                    </div>
                                </td> --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center"><strong>No data found</strong></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-result mt-3">
                <select id="paginationLimit" class="form-select me-3" style="width:90px;">
                    <option value="10" {{ request('limit', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('limit') == 15 ? 'selected' : '' }}>15</option>
                    <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>20</option>
                </select>
                <p class="mb-0">
                    @if ($allMeso->total() > 0)
                        Showing {{ $allMeso->firstItem() }} to {{ $allMeso->lastItem() }} of {{ $allMeso->total() }} entries
                    @else
                        No entries found
                    @endif
                </p>
                <nav class="ms-auto">
                    {{ $allMeso->appends(request()->query())->links('pagination.custom') }}
                </nav>

            </div>
        </div>
    </div>
</div>

<!-- Add Meso Modal -->
<div class="modal fade" id="addMesoModal" tabindex="-1" aria-labelledby="addMesoModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMesoModalLabel">Add Mesho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMesoForm">
                    @csrf
                    <input type="hidden" name="meso_id" id="meso_id" value="">

                    <div class="mb-3 mt-4">
                        <label for="mesoTitle" class="form-label">Title<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mesoTitle" name="meso_title" maxlength="50" placeholder="Enter meso title   ">
                        <span class="text-danger d-none" id="mesoTitleError">Meso Title is required</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Week</label>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach(\App\Constants\WeekConstants::Frequency['mesho_weeks'] as $week)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="week" id="week{{ $week }}" value="{{ $week }}" {{ $week == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="week{{ $week }}">
                                    {{ $week }} {{ $week > 1 ? 'Weeks' : 'Week' }}
                                </label>
                            </div>
                        @endforeach
                        </div>
                    </div>

                </form>
            </div>
            <div class="justify-content-end d-flex mb-3">
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"  data-bs-dismiss="modal" aria-label="Close">Close</button>
                    <button type="button" class="btn btn-primary" id="submitMesoForm">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <input type="hidden" id="deleteMealsPlanId">
                <h5 class="mb-3">Are you sure you want to delete this meso?</h5>
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
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-3" id="successMessage"></h5>
                <button type="button" class="btn btn-primary" id="successModalOk">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-exclamation-circle text-danger" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-3" id="errorMessage"></h5>
                <button type="button" class="btn btn-primary" id="errorModalOk">OK</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .action-icons {
        display: flex;
        gap: 10px;
    }
    .action-icons i {
        cursor: pointer;
        font-size: 18px;
    }
    .modal-content {
        border-radius: 10px;
        border: none;
    }
    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    .form-check {
        margin-bottom: 5px;
    }
</style>
@endpush

@push('scripts')
<script>
    let deleteId = null;

    $(document).ready(function() {
        // Pagination limit change
        $('#paginationLimit').on('change', function() {
            let limit = $(this).val();
            let url = new URL(window.location.href);
            url.searchParams.set('limit', limit);
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        });

        // Reset form button
            $('#resetMesoForm').on('click', function() {
                $('#addMesoForm')[0].reset();
                $('.form-check-input[name="workout_frequency"]').first().prop('checked', true);
                $('.form-check-input[name="week"]').first().prop('checked', true);
                $('.text-danger').addClass('d-none');

                if ($('#meso_id').val()) {
                    $('#addMesoModalLabel').text('Edit Mesho');
                } else {
                    $('#addMesoModalLabel').text('Add Mesho');
                }
            });

        // Edit meso
        $('.edit-meso').on('click', function() {
            let id = $(this).data('id');

            $.ajax({
                url: "{{ route('admin.mesoCycleEdit', '') }}/" + id,
                method: "GET",
                success: function(response) {
                    $('#meso_id').val(id);
                    $('#mesoTitle').val(response.name);
                    // $(`input[name="workout_frequency"][value="${response.workout_frequency_id}"]`).prop('checked', true);
                    $(`input[name="week"][value="${response.week_number}"]`).prop('checked', true);
                    $('#addMesoModalLabel').text('Edit Mesho');
                    $('#addMesoModal').modal('show');
                },
                error: function(xhr) {
                    showError('Error loading meso data. Please try again.');
                }
            });
        });

        // Delete meso
        $('.delete-meso').on('click', function() {
            deleteId = $(this).data('id');
            $('#deleteConfirmModal').modal('show');
        });

        // Confirm delete
        $('#confirmDelete').on('click', function() {
            if (deleteId) {
                $.ajax({
                    url: "{{ route('admin.mesoCycleDelete', '') }}/" + deleteId,
                    method: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#deleteConfirmModal').modal('hide');
                            showSuccess(response.message);
                        } else {
                            showError(response.message || 'Something went wrong.');
                        }
                    },
                    error: function(xhr) {
                        showError('Error deleting meso. Please try again.');
                    }
                });
            }
        });

        // Submit form (add/edit)
        $('#submitMesoForm').on('click', function(e) {
            e.preventDefault();
            let isValid = true;

            let title = $('#mesoTitle').val().trim();
            // let workout_frequency = $('input[name="workout_frequency"]:checked').val();
            let week = $('input[name="week"]:checked').val();
            let meso_id = $('#meso_id').val();

            // Validation
            if (!title || title.length > 50) {
                $('#mesoTitleError').removeClass('d-none');
                isValid = false;
            } else {
                $('#mesoTitleError').addClass('d-none');
            }



            if (isValid) {
                let url = meso_id ? "{{ route('admin.mesoCycleUpdate', '') }}/" + meso_id : "{{ route('admin.mesoCycleSave') }}";
                let method = meso_id ? "POST" : "POST";

                $.ajax({
                    url: url,
                    method: method,
                    data: {
                        _token: "{{ csrf_token() }}",
                        meso_title: title,
                        // coach_notes: notes,
                        // workout_frequency: workout_frequency,
                        week: week
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#addMesoModal').modal('hide');
                            showSuccess(response.message);
                        } else {
                            showError(response.message || 'Something went wrong.');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error saving meso. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = '';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorMessage += value + '<br>';
                            });
                        }
                        showError(errorMessage);
                    }
                });
            }
        });

        // Success Modal OK button
        $('#successModalOk').on('click', function() {
            $('#successModal').modal('hide');
            location.reload();
        });

        // Error Modal OK button
        $('#errorModalOk').on('click', function() {
            $('#errorModal').modal('hide');
        });

        // Reset form on modal close
        $('#addMesoModal').on('hidden.bs.modal', function () {
            $('#addMesoForm')[0].reset();
            $('#meso_id').val('');
            // $('.form-check-input[name="workout_frequency"]').first().prop('checked', true);
            $('.form-check-input[name="week"]').first().prop('checked', true);
            $('.text-danger').addClass('d-none');
            $('#addMesoModalLabel').text('Add Mesho');
        });
    });

    function showSuccess(message) {
        $('#successMessage').html(message);
        $('#successModal').modal('show');
    }

    function showError(message) {
        $('#errorMessage').html(message);
        $('#errorModal').modal('show');
    }
</script>
@endpush
