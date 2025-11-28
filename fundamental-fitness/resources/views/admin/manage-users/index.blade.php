@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Users')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4>Users</h4>

        </div>
        <div class="m-card-min-hight">
            <div class="filter-row-search justify-content-between my-2">
                <form method="GET" action="{{ route('admin.userIndex') }}" class="d-flex mb-2 align-items-end">
                    <div class="tp-search-input me-2" style="flex-grow: 1;">
                        {{-- <label for="search" class="form-label">Keyword</label> --}}
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                            class="form-control icon-holder me-2" placeholder="Search with name or email">
                    </div>
                    <!-- Account Status Filter -->
                    <div class="tp-search-input me-2" style="flex-grow: 1;">
                        {{-- <label for="status" class="form-label">Account Status</label> --}}
                        <select class="form-select" name="status" style="width:190px;">
                            <option value="">Select Status</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }} alt="Active">
                                Active</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }} alt="Inactive">
                                Deactive</option>
                        </select>
                    </div>
                    <!-- Start Date Filter -->
                    {{-- <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date"
                            value="{{ request('start_date') }}" onkeydown="return false">
                    </div> --}}

                    <!-- End Date Filter -->
                    {{-- <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date"
                            value="{{ request('end_date') }}" onkeydown="return false">
                    </div> --}}


                    <button type="submit" class="btn btn-primary search-btn mx-2">Search</button>
                    <a href="{{ route('admin.userIndex') }}" class="btn btn-outline-secondary">Reset</a>
                </form>


            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width:5%">S. No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>WORKOUT FREQUENCY</th>
                            <th>REGISTRATION DATE</th>
                            <th style="width:15%">ACTIVE/DEACTIVE</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($allInfo as $singleInfo)
                            <tr>
                                <td>{{ ($allInfo->currentPage() - 1) * $allInfo->perPage() + $loop->iteration }}</td>
                                <td>{{ $singleInfo->fullname }}</td>
                                <td>{{ $singleInfo->email }}</td>
                                <td>
                                    {{ucwords($singleInfo->work_out_frequency?->name)}}
                                </td>

                                <td>{{ $singleInfo->created_at->format('d M, Y') }}</td>
                                <td><label class="switch">
                                        <input type="checkbox" data-status="{{ $singleInfo->status }}"
                                            @if ($singleInfo->status) checked @endif
                                            onchange="updateStatus({{ $singleInfo->id }},'User','{{ route('admin.updateStatus') }}',this)">
                                        <span class="slider">
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <a href="javascript:void(0)" class="delete-user ms-2"
                                        data-id="{{ $singleInfo->id }}" data-page="{{ request()->get('page', 1) }}">
                                        <img src="{{ asset('assets/images/delete-circle-btn.svg') }}" alt="Delete"
                                            title="Delete">
                                    </a>
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
                <select id="paginationLimit" class="form-select me-3" style="width:90px;">
                    <option value="10" {{ request('limit',10) == 10  ? 'selected' : '' }}>10</option>
                    <option value="50" {{ request('limit') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <p class="mb-0">
                    @if ($allInfo->total() > 0)
                        Showing {{ $allInfo->firstItem() }} to {{ $allInfo->lastItem() }} of {{ $allInfo->total() }}
                        entries
                    @else
                        No entries found
                    @endif
                </p>
                <nav class="ms-auto">
                    {{ $allInfo->appends(request()->query())->links('pagination.custom') }}
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('#paginationLimit').on('change', function() {
        let limit = $(this).val();
        let url = new URL(window.location.href);
        url.searchParams.set('limit', limit);
        url.searchParams.set('page', 1);
        const form = $('form[method="GET"]');
        form.find('input, select').each(function() {
            if (this.name && this.value) {
                url.searchParams.set(this.name, this.value);
            }
        });

        window.location.href = url.toString();
    });

    $(document).on("click", ".delete-user", function() {
        let id = $(this).data("id");
        let page = $(this).data("page");

        Swal.fire({
            title: "Are you sure?",
            text: "This will delete the user and all related data!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.userDelete', ['id' => ':id']) }}".replace(':id', id),
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}",
                        page: page

                    },
                    success: function(res) {
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
    $(document).ready(function() {
        // Check when End Date changes
        $('input[name="end_date"]').on('change', function() {
            let startDate = $('input[name="start_date"]').val();
            let endDate = $(this).val();

            if (endDate && !startDate) {
                if (!$(".toast-error").length) {
                    toastr.error("Please select a Start Date first!");
                }
                $(this).val("");
            }
        });
    });
</script>
@endpush
