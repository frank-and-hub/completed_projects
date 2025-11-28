@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Add Exercise')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Exercises</h4>
            <div>
                <a href="{{ route('admin.exerciseAdd') }}" class="btn btn-primary"><img src="{{ asset('assets/images/add-circle.svg') }}" alt="plus-icon">  Add Exercise</a>
               </div>
        </div>
        <div class="white-body-card ">
            <div class="filter-row align-items-center mb-3 d-flex">
                @php
                $muscles = \App\Models\MuscleMaster::orderBy('name')->get();
            @endphp

            <form action="{{ route('admin.exerciseIndex') }}" method="GET" class="d-flex align-items-center">
                <!-- Search Input -->
                <div class="tp-search-input me-2" style="flex-grow: 1;">
                    <input type="text" class="form-control" placeholder="Search by exercise name and equipment" name="search" value="{{ request('search') }}" style="width:280px;">
                    <span class="input-icon">
                        <img src="{{ asset('assets/images/search.svg') }}" alt="Search">
                    </span>
                </div>

                <!-- Level Dropdown -->
                <select name="level" class="form-select me-2" style="width: 190px;">
                    <option value="">Select Difficulty Level</option>
                    <option value="1" {{ request('level') == '1' ? 'selected' : '' }}>Beginner</option>
                    <option value="2" {{ request('level') == '2' ? 'selected' : '' }}>Intermediate</option>
                    <option value="3" {{ request('level') == '3' ? 'selected' : '' }}>Advance</option>
                </select>

                <!-- Muscle Dropdown -->
                <select name="muscle_id" class="form-select me-2" style="width: 180px;">
                    <option value="">Select Muscle</option>
                    @foreach($muscles as $muscle)
                        <option value="{{ $muscle->id }}" {{ request('muscle_id') == $muscle->id ? 'selected' : '' }}>
                            {{ $muscle->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Buttons -->
                <button type="submit" class="btn btn-outline-primary search-btn mx-2">
                    <i class="fas fa-search"></i> Search
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('admin.exerciseIndex') }}'">
                    <i class="fas fa-times"></i> Reset
                </button>

                <!-- Limit -->
                <input type="hidden" name="limit" value="{{ request('limit', 10) }}">
            </form>

                <div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Exercise&nbsp;&nbsp;name</th>
                            <th>Difficulty&nbsp;&nbsp;Level</th>
                            <th>Equipment</th>
                            <th>Muscle&nbsp;&nbsp;trained</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                <tbody>
                    @forelse ($exercises as $singleInfo)
                        <tr>
                            <td>{{ ($exercises->currentPage() - 1) * $exercises->perPage() + $loop->iteration }}</td>
                            <td>{{ $singleInfo->exercise_name }}</td>
                            <td>
                                @php
                                    $levelMap = [1 => 'Beginner', 2 => 'Intermediate', 3 => 'Advance'];
                                    echo $levelMap[$singleInfo->level] ?? 'Unknown';
                                @endphp
                            </td>
                            <td>{{ $singleInfo->equipment }}</td>
                            <td> {{ $singleInfo->muscle_trained->pluck('name')->join(', ') }}</td>

                            <td>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <a href="{{ route('admin.exerciseEdit', $singleInfo->id) }}" class="edit-muscle">
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
                            <td colspan="6" class="text-center"><strong>No data found</strong></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
          <!-- Pagination Section -->
          <div class="table-result mt-3">
            <select id="paginationLimit" class="form-select me-3" style="width:70px;" onchange="window.location.href='?limit=' + this.value">
                <option value="10" {{ request('limit', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="15" {{ request('limit') == 15 ? 'selected' : '' }}>15</option>
                <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>20</option>
            </select>
                <p class="mb-0">
                @if ($exercises->total() > 0)
                    Showing {{ $exercises->firstItem() }} to {{ $exercises->lastItem() }} of {{ $exercises->total() }} entries
                @else
                    No entries found
                @endif
            </p>
            <nav class="ms-auto">
                {{ $exercises->links('pagination.custom') }}
            </nav>
        </div>

        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteExerciseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <input type="hidden" id="deleteExerciseId">
                <h5 class="mb-3">Are you sure you want to delete this exercise?</h5>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="confirmDeleteExercise">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Message Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <h5 class="mb-3" id="successMessageTitle"></h5>
                <h6 class="mb-3" id="successMessage"></h6>
                <button type="button" class="btn btn-primary" id="successModalOk">OK</button>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        // Delete Exercise Modal
        $(document).on('click', '.delete-muscle', function() {
            const exerciseId = $(this).data('id');
            const title = 'This exercise cannot be deleted.';
            $.ajax({
                url: "{{ route('admin.check_exercise_status', '') }}/" + exerciseId,
                type: 'GET',
                success: function(response) {
                    
                    if (response.status === 'error' && response.can_delete === false) {
                        // Exercise is attached to workout plans - cannot delete
                        console.clear();
                        console.log(title);
                        $('#successMessage').text(response.message);
                        $('#successModal').modal('show');
                    } else {
                        // Safe to delete
                        $('#deleteExerciseId').val(exerciseId);
                        $('#deleteExerciseModal').modal('show');
                    }
                },
                error: function(xhr) {
                    var errorMessage = 'Error checking exercise status.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    $('#successMessageTitle').text(title);
                    $('#successMessage').text(errorMessage);
                    $('#successModal').modal('show');
                }
            });
        });

        // Confirm Delete
        $('#confirmDeleteExercise').click(function() {
            var exerciseId = $('#deleteExerciseId').val();

            $.ajax({
                url: "{{ route('admin.exerciseDelete', '') }}/" + exerciseId,
                type: 'DELETE',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#deleteExerciseModal').modal('hide');

                    if(response.success) {
                        $('#successMessage').text('Exercise deleted successfully!');
                        $('#successModal').modal('show');
                    }
                },
                error: function(xhr) {
                    $('#deleteExerciseModal').modal('hide');
                    $('#successMessage').text('Error deleting exercise. Please try again.');
                    $('#successModal').modal('show');
                }
            });
        });

        // Success Modal OK Button
        $('#successModalOk').click(function() {
            $('#successModal').modal('hide');
            location.reload(); // Refresh the page to see changes
        });

        @if(Session::has('toastr'))
            toastr.{{ Session::get('toastr.type') }}(
                '{{ Session::get('toastr.message') }}',
                '', // Title
                {
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    timeOut: 5000
                }
            );
        @endif
    });
</script>
@endpush
