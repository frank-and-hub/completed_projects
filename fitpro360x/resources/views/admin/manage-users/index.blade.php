@extends('admin.layout.index')
@section('content')
@section('admin-title', 'Users')

<div class="container-fluid pt-3 bg">
    <div class="page-content-wrapper">
        <div class="page-title-row justify-content-between">
            <h4 class="mb-3 ms-3">Users</h4>
            {{-- <a href="{{ route('admin.userAdd') }}" class="btn btn-outline-dark  mb-2"><img src=""
                    class="mb-1">Add</a> --}}
        </div>
        <div class="m-card-min-hight">
            <div class="filter-row-search justify-content-between">
                <form method="GET" action="{{ route('admin.userIndex') }}" class="d-flex mb-2 align-items-center">

                    <div class="tp-search-input me-2" style="flex-grow: 1;"> <!-- Search Input -->
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                            class="form-control icon-holder me-2" placeholder="Search with email or name">
                    </div>
                    <!-- Account Status Filter -->
                    <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <select class="form-select" name="status" style="width:190px;">
                            <option value="">Account Status</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }} alt="active">
                                Active</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }} alt="inactive">
                                Inactive</option>
                        </select>
                    </div>
                    <!-- Membership Plan Filter -->
                    <div class="tp-search-input me-2" style="flex-grow: 1;">
                        <select class="form-select" name="plan_name" style="width:210px;">
                            <option value="">Membership Plan</option>
                            <option value="Workout Only" {{ request('plan_name') == 'Workout Only' ? 'selected' : '' }}>
                                Workout Only</option>
                            <option value="Gold" {{ request('plan_name') == 'Gold' ? 'selected' : '' }}>Gold</option>
                            <option value="Platinum" {{ request('plan_name') == 'Platinum' ? 'selected' : '' }}>Platinum
                            </option>
                            {{-- <option value="No Subscription" {{ request('plan_name') == 'No Subscription' ? 'selected' : '' }}>No Subscription</option> --}}
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary search-btn mx-2">Search</button>
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
                            <th>MEMBERSHIP PLAN</th>
                            <th>REGISTRATION DATE</th>
                            <th style="width:15%">ACTIVE/INACTIVE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($allInfo as $singleInfo)
                            <tr>
                                <td>{{ ($allInfo->currentPage() - 1) * $allInfo->perPage() + $loop->iteration }}</td>
                                <td>{{ $singleInfo->fullname }}</td>
                                <td>{{ $singleInfo->email }}</td>
                                <td>
                                    @php
                                        $activeSubscription = $singleInfo->subscriptions->firstWhere(
                                            'status',
                                            'active',
                                        );
                                    @endphp

                                    {{ $activeSubscription && $activeSubscription->package
                                        ? $activeSubscription->package->plan_name
                                        : 'No Subscription' }}
                                </td>

                                <td>{{ $singleInfo->created_at->format('d M, Y') }}</td>
                                <td><label class="switch">
                                        <input type="checkbox" data-status="{{ $singleInfo->status }}"
                                            @if ($singleInfo->status) checked @endif
                                            onchange="updateStatus({{ $singleInfo->id }},'User','{{ route('admin.updateStatus') }}',this)">
                                        <span class="slider">
                                        </span>
                                    </label></td>

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
                <select id="paginationLimit" class="form-select me-3" style="width:70px;">
                    <option value="10" {{ request('limit', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('limit') == 15 ? 'selected' : '' }}>15</option>
                    <option value="20" {{ request('limit') == 20 ? 'selected' : '' }}>20</option>
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
<script>
    $('#paginationLimit').on('change', function() {
        let limit = $(this).val();
        let url = new URL(window.location.href);

        // Set or update the `limit` param
        url.searchParams.set('limit', limit);

        // Reset the page number to 1
        url.searchParams.set('page', 1);

        // Preserve all existing query parameters
        const form = $('form[method="GET"]');
        form.find('input, select').each(function() {
            if (this.name && this.value) {
                url.searchParams.set(this.name, this.value);
            }
        });

        window.location.href = url.toString();
    });
</script>
@endpush
