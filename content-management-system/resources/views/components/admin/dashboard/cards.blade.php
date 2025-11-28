<div class="card">
    <div class="card-header bg-lightblue">
        <h6 class="mb-0 text-primary">Database Statistics</h6>
    </div>
    <div class="card-body">
        <div class="row">
            @can('users-show')
                {{-- TOTAL USERS --}}
                <x-admin.dashboard.dashboardcard count="0" id="total_users" color="white" title="TOTAL USERS"
                    icon='bxs-user' route="{{ route('admin.user.index') }}" />
            @endcan
            {{-- TOTAL PARKS --}}
            <x-admin.dashboard.dashboardcard count="0" id="total_parks" color="green"
                title="{{ Auth::user()->hasRole('admin') ? 'TOTAL PARKS' : 'TOTAL PARKS ADDED BY YOU' }}"
                icon='bxs-parking' route="{{ route('admin.park.index') }}" />

            {{-- TOTAL CATEGORIES --}}
            @can('users-show')
                <x-admin.dashboard.dashboardcard count="0" id="total_categories" color="white"
                    title="TOTAL CATEGORIES" icon='bx-category' route="{{ route('admin.category.index') }}" />
            @endcan

            {{-- TOTAL FEATURES --}}
            <x-admin.dashboard.dashboardcard count="0" id="total_features" color="green" title="TOTAL FEATURES"
                icon='bxs-spreadsheet' route="{{ route('admin.feature_type.index') }}" />


        </div>
    </div>
</div>

@can('users-show')
    <div class="card">
        <div class="card-header bg-lightblue">
            <h6 class="mb-0 text-primary" style="text-up">User Uploaded Content</h6>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- TOTAL PENDING IMAGES --}}
                <x-admin.dashboard.dashboardcard count="0" id="total_pending_images" color="green"
                    title="TOTAL PENDING IMAGES" icon='bx-images' route="{{ route('admin.park.pendingimage') }}" />



                {{-- TOTAL PENDING REVIEWS --}}
                <x-admin.dashboard.dashboardcard count="0" id="total_pending_reviews" color="white"
                    title="TOTAL PENDING REVIEWS" icon='bxs-star-half' route="{{ route('admin.park.review') }}" />

                @can('users-show')
                    <x-admin.dashboard.dashboardcard count="0" id="total_delete_account" color="green"
                        title="DELETE USER REQUESTS" icon='bx-category' route="{{ route('admin.delete.index') }}" />
                @endcan

            </div>
        </div>
    </div>
@endcan
