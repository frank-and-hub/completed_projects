@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Exercise')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between mb-3 gap-3 flex-wrap">
            <h4 class="">Exercise</h4>
            <div>
                <a href="{{ route('admin.exerciseAdd') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/images/add-circle.svg') }}" alt="plus-icon" style="width:18px;">
                    Add Exercise
                </a>
            </div>
        </div>


            <!-- Filter Section -->
            <div class="filter-row-search">
                <form action="{{ route('admin.exerciseIndex') }}" method="GET" class="filter-row-search-box g-2 mb-3">
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                               placeholder="Search by exercise name">
                    </div>
                    <div>
                        <select name="frequency" class="form-select">
                            <option value="">All Workout Frequency</option>
                            @foreach($frequencies as $frequency)
                                <option value="{{ $frequency->id }}" {{ request('frequency') == $frequency->id ? 'selected' : '' }}>
                                    {{ $frequency->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="meso" class="form-select">
                            <option value="">All Meso</option>
                            @foreach($mesos as $meso)
                                <option value="{{ $meso->id }}" {{ request('meso') == $meso->id ? 'selected' : '' }}>
                                    {{ $meso->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="week" class="form-select">
                            <option value="">All Weeks</option>
                            <option value="1" {{ request('week') == 1 ? 'selected' : '' }}>Week 1</option>
                            <option value="2" {{ request('week') == 2 ? 'selected' : '' }}>Week 2</option>
                            <option value="3" {{ request('week') == 3 ? 'selected' : '' }}>Week 3</option>
                            <option value="4" {{ request('week') == 4 ? 'selected' : '' }}>Week 4</option>
                        </select>
                    </div>
                    <div>
                        <select name="day" class="form-select">
                            <option value="">All Days</option>
                            <option value="1" {{ request('day') == 1 ? 'selected' : '' }}>Day 1</option>
                            <option value="2" {{ request('day') == 2 ? 'selected' : '' }}>Day 2</option>
                            <option value="3" {{ request('day') == 3 ? 'selected' : '' }}>Day 3</option>
                            <option value="4" {{ request('day') == 4 ? 'selected' : '' }}>Day 4</option>
                            <option value="5" {{ request('day') == 5 ? 'selected' : '' }}>Day 5</option>
                            <option value="6" {{ request('day') == 6 ? 'selected' : '' }}>Day 6</option>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="{{ route('admin.exerciseIndex') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Table Section -->
            <div class="table-responsive">
                <table class="table exercisetable" style="white-space: nowrap;">
                    <thead>
                        <tr>
                            <th>S.&nbsp;No.</th>
                            <th>Exercise Name</th>
                            <th>Workout Frequency</th>
                            <th>Meso</th>
                            <th>Week</th>
                            <th>Day</th>
                            <th>Level</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        @forelse($exercises as $index => $workout)
                        <tr>
                            <td style="max-width: 50px; min-width: 10px; width: 50px">{{ ($exercises->currentPage() - 1) * $exercises->perPage() + $loop->iteration }}</td>
                            <td>{{ $workout->exercise->name ?? 'N/A' }}</td>
                            <td>{{ $workout->workout_frequency->name ?? 'N/A' }}</td>
                            <td>{{ $workout->meso->name ?? 'N/A' }}</td>
                            <td>{{ $workout->week_id }}</td>
                            <td>{{ $workout->day_id }}</td>
                            <td>
                                @if($workout->level == 1) Beginner
                                @elseif($workout->level == 2) Intermediate
                                @else Advanced
                                @endif
                            </td>
                            <td class="text-center">

                                    <a href="{{ route('admin.exerciseEdit', $workout->id) }}">
                                        <img src="{{ asset('assets/images/edittbtn.svg') }}" alt="Edit" title="Edit">
                                    </a>
                                    <a href="javascript:void(0)" class="delete-muscle ms-2" data-id="{{ $workout->id }}" data-page="{{ request()->get('page', 1) }}">
                                        <img src="{{ asset('assets/images/delete-circle-btn.svg') }}" alt="Delete" title="Delete">
                                    </a>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center"><strong>No data found</strong></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Section -->
            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mt-3 mb-5">
                <div class="d-flex align-items-center gap-2">
                    <select id="paginationLimit" class="form-select" style="width:90px;" onchange="window.location.href='?limit=' + this.value">
                        <option value="10" {{ request('limit',10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="50" {{ request('limit') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <p class="mb-0">
                        @if ($exercises->total() > 0)
                            Showing {{ $exercises->firstItem() }} to {{ $exercises->lastItem() }} of {{ $exercises->total() }} entries
                        @else
                            No entries found
                        @endif
                    </p>
                </div>
                <div>
                    {{ $exercises->links('pagination.custom') }}
                </div>
            </div>

    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
        const msg = localStorage.getItem('toastrMessage');
        if (msg) {
            toastr.success(msg);
            localStorage.removeItem('toastrMessage');
        }
    });
$(document).on("click", ".delete-muscle", function () {
    let id = $(this).data("id");
    let page = $(this).data("page");

    Swal.fire({
        title: "Are you sure?",
        text: "This will delete the exercise and all related data!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.exerciseDelete', '') }}/" + id,
                type: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}",
                    page: page

                },
                success: function (res) {
                if (res.success) {
                    Swal.fire("Deleted!", res.message, "success").then(() => {
                        window.location.href = res.redirect_url;
                    });
                } else {
                    Swal.fire("Error!", res.message, "error");
                }
            },
            });
        }
    });
});

$(document).ready(function () {
    let message = localStorage.getItem('toastrMessage');
    let error = localStorage.getItem('toastrError');

    if (message) {
        toastr.success(message);
        localStorage.removeItem('toastrMessage');
    }

    if (error) {
        toastr.error(error);
        localStorage.removeItem('toastrError');
    }
});
</script>
@endpush
