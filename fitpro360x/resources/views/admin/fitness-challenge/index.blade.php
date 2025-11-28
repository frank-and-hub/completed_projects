@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Add Fitness Challenge')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Fitness Challenges</h4>
            <div>
                <a href="{{ route('admin.fitnessChallengeAdd') }}" class="btn btn-primary"><img src="{{ asset('assets/images/add-circle.svg') }}" alt="plus-icon">  Add Challenge</a>
               </div>
        </div>
        <div class="white-body-card ">
            <div class="filter-row align-items-center mb-3 d-flex">

            <form action="{{ route('admin.fitnessChallengeIndex') }}" method="GET" class="d-flex align-items-center">
                <!-- Search Input -->
                <div class="tp-search-input me-2" style="flex-grow: 1;">
                    <input type="text" class="form-control" placeholder="Search by challenge name and goal" name="search" value="{{ request('search') }}" style="width: 250px;">
                    <span class="input-icon">
                        <img src="{{ asset('assets/images/search.svg') }}" alt="Search">
                    </span>
                </div>

                <!-- Buttons -->
                <button type="submit" class="btn btn-outline-primary search-btn mx-2">
                    <i class="fas fa-search"></i> Search
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('admin.fitnessChallengeIndex') }}'">
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
                            <th>challenge&nbsp;&nbsp;name</th>
                            <th>Goal</th>
                            <th>duration&nbsp;&nbsp;( Weeks )</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($fitnessChallenges as $singleInfo)
                            <tr>
                                <td>{{ ($fitnessChallenges->currentPage() - 1) * $fitnessChallenges->perPage() + $loop->iteration }}</td>
                                <td>{{ $singleInfo->challenge_name }}</td>
                                <td>{{ $singleInfo->goal }}</td>
                                <td>{{ $singleInfo->duration_weeks ?? 'N/A' }}</td>

                                <td>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <a href="{{ route('admin.fitnessChallengeEdit', $singleInfo->id) }}" class="edit-muscle">
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
            {{-- <div class="table-result mt-3">
                <select id="paginationLimit" class="form-select me-3" style="width:70px;">
                    <option value="10" {{ request('limit', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('limit') == 15 ? 'selected' : '' }}>15</option>
                    <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>20</option>
                </select>
                    <p class="mb-0">
                    @if ($fitnessChallenges->total() > 0)
                        Showing {{ $fitnessChallenges->firstItem() }} to {{ $fitnessChallenges->lastItem() }} of {{ $fitnessChallenges->total() }} entries
                    @else
                        No entries found
                    @endif
                </p>
                <nav class="ms-auto">
                    {{ $fitnessChallenges->links('pagination.custom') }}
                </nav>
            </div> --}}
            <div class="table-result mt-3">
                <select id="paginationLimit" class="form-select me-3" style="width:70px;">
                    <option value="10" {{ request('limit', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('limit') == 15 ? 'selected' : '' }}>15</option>
                    <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>20</option>
                </select>
                <p class="mb-0">
                    @if ($fitnessChallenges->total() > 0)
                        Showing {{ $fitnessChallenges->firstItem() }} to {{ $fitnessChallenges->lastItem() }} of {{ $fitnessChallenges->total() }}
                        entries
                    @else
                        No entries found
                    @endif
                </p>
                <nav class="ms-auto">
                    {{ $fitnessChallenges->appends(request()->query())->links('pagination.custom') }}
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
                <input type="hidden" id="deletefitnessChallengeId">
                <h5 class="mb-3">Are you sure you want to delete this fitness challenge?</h5>
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

@endsection
@push('scripts')
<script>
    $(document).ready(function() {

         $('#paginationLimit').on('change', function() {
            let limit = $(this).val();
            let url = new URL(window.location.href);
            url.searchParams.set('limit', limit);
            url.searchParams.set('page', 1);

            const search = $('input[name="search"]').val();
            if (search) {
                url.searchParams.set('search', search);
            }
            window.location.href = url.toString();
        });
        // Delete Exercise Modal
        $(document).on('click', '.delete-muscle', function() {
            var fitnessChallengeId = $(this).data('id');
            $('#deletefitnessChallengeId').val(fitnessChallengeId);
            $('#deleteExerciseModal').modal('show');
        });

        // Confirm Delete
        $('#confirmDeleteExercise').click(function() {
            var fitnessChallengeId = $('#deletefitnessChallengeId').val();

            $.ajax({
                url: "{{ route('admin.fitnessChallengeDelete', '') }}/" + fitnessChallengeId,
                type: 'DELETE',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#deleteExerciseModal').modal('hide');

                    if(response.success) {
                        $('#successMessage').text('Fitness challenge deleted successfully!');
                        $('#successModal').modal('show');
                    }
                },
                error: function(xhr) {
                    $('#deleteExerciseModal').modal('hide');
                    $('#successMessage').text('Error deleting fitness challenge. Please try again.');
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
