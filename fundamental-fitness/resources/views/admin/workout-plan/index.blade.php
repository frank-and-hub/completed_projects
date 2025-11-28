@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Workout-plan')

<div class="container-fluid">
    <div class="pate-content-wrapper">
        <div class="page-title-row justify-content-between ">
            <h4 class="mb-3 ms-3">Workout Plan</h5>
            <div>
                <a href="{{ route('admin.workoutPlansAdd') }}" class="btn btn-primary"><img
                        src="{{ asset('assets/images/add-circle.svg') }}" alt="plus-icon"> Create Workout</a>
            </div>
        </div>
        <div class="m-card-min-hight">
            <div class="filter-row-search justify-content-between">
                <div>
                    <form method="GET" action="{{ route('admin.workoutPlansIndex') }}" class="d-flex mb-2 align-items-center">
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                            class="form-control icon-holder me-2" placeholder="Search workout plan name">
                        <button type="submit" class="btn btn-outline-primary search-btn mx-2">Search</button>
                        <a href="{{ route('admin.workoutPlansIndex') }}" class="btn btn-outline-secondary">Reset</a>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width:5%">S. NO.</th>
                            <th>WORKOUT PLAN NAME</th>
                            <th>DATE CREATED</th>
                            <th>STATUS</th>
                            <th style="width:15%">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workout as $singleInfo)
                            <tr>
                                <td>{{ ($workout->currentPage() - 1) * $workout->perPage() + $loop->iteration }}</td>
                                <td>{{ $singleInfo->title }}</td>
                                <td>{{ $singleInfo->created_at->format('d M, Y') }}</td>
                                <td><label class="switch">
                                        <input type="checkbox" data-status="{{ $singleInfo->status }}"
                                            @if ($singleInfo->status) checked @endif
                                            onchange="updateStatus({{ $singleInfo->id }},'WorkoutPlan','{{ route('admin.updateStatus') }}',this)">
                                        <span class="slider">
                                        </span>
                                    </label></td>
                                <td>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <a href="{{ route('admin.workoutPlansEdit', $singleInfo->id) }}" class="edit-workout">
                                            <img src="{{ asset('assets/images/edittbtn.svg') }}" alt="Edit" title="Edit">
                                        </a>
                                        <a href="javascript:void(0)" class="delete-workoutPlan" data-id="{{ $singleInfo->id }}">
                                            <img src="{{ asset('assets/images/delete-circle-btn.svg') }}" alt="Delete" title="Delete">
                                        </a>
                                    </div>
                                </td>

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
                <select id="paginationLimit" class="form-select me-3" style="width:90px;" onchange="window.location.href='?limit=' + this.value">
                    <option value="10" {{ request('limit', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('limit') == 15 ? 'selected' : '' }}>15</option>
                    <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>20</option>
                </select>
                <p class="mb-0">
                    @if ($workout->total() > 0)
                        Showing {{ $workout->firstItem() }} to {{ $workout->lastItem() }} of {{ $workout->total() }}
                        entries
                    @else
                        No entries found
                    @endif
                </p>
    
                <nav class="ms-auto">
                    {{ $workout->links('pagination.custom') }}
                </nav>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" >  </script>

<!-- Delete Confirmation Modal -->
{{-- <div class="modal fade" id="deleteExerciseModal" tabindex="-1" aria-hidden="true"> --}}
<!-- Then your script -->
<script>
    let deleteModal;

    $(document).on('click', '.delete-workoutPlan', function () {
        var workoutPlanId = $(this).data('id');
        $('#deleteworkoutPlanId').val(workoutPlanId);

        // Bootstrap 5 modal instance
        deleteModal = new bootstrap.Modal(document.getElementById('deleteworkoutPlanModal'));
        deleteModal.show();
    });

    $('#confirmDelete').click(function () {
        var workoutPlanId = $('#deleteworkoutPlanId').val();

        $.ajax({
            url: "{{ route('admin.workoutPlanDelete', '') }}/" + workoutPlanId,
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function (response) {
                deleteModal.hide();
                location.reload();
            },
            error: function (xhr) {
                alert('An error occurred.');
            }
        });
    });
</script>

</script>

<div class="modal fade" id="deleteworkoutPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <input type="hidden" id="deleteworkoutPlanId">
                <h5 class="mb-3">Are you sure you want to delete this workout plan?</h5>
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
                <h5 class="mb-3" id="successMessage"></h5>
                <button type="button" class="btn btn-primary" id="successModalOk">OK</button>
            </div>
        </div>
    </div>
</div>

</body>


@push('scripts')
<script>
    $(document).ready(function() {
        // Delete Exercise Modal
        $(document).on('click', '.delete-workoutPlan', function() {
            var workoutPlanId = $(this).data('id');
            $('#deleteworkoutPlanId').val(workoutPlanId);
            $('#deleteExerciseModal').modal('show');
        });

        // Confirm Delete
        $('#confirmDeleteExercise').click(function() {
            var workoutPlanId = $('#deleteworkoutPlanId').val();

            $.ajax({
                url: "{{ route('admin.workoutPlansDelete', '') }}/" + workoutPlanId,
                type: 'DELETE',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#deleteExerciseModal').modal('hide');

                    if(response.success) {
                        $('#successMessage').text('Workout plan deleted successfully!');
                        $('#successModal').modal('show');
                    }
                },
                error: function(xhr) {
                    $('#deleteExerciseModal').modal('hide');
                    $('#successMessage').text('Cannot delete this workout plan because it is currently assigned to one or more users.');
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

@endsection
