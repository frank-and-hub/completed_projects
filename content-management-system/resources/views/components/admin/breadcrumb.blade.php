<div class="d-flex align-items-center py-3 mb-4">
    <h5 class="fw-bold mb-0" style="flex: 1">
        <span><a href="{{ route('admin.dashboard') }}">
                <u class="text-primary fw-light">Dashboard</u> </a></span>
        @if (!empty($breadcrumbs))
            @foreach ($breadcrumbs as $bredcrumb)
                <span class="text-primary fw-light"> / </span>
                <span><a href="{{ $bredcrumb['route'] }}"><u
                            class="text-primary fw-light">{{ $bredcrumb['name'] }}</u></a></span>
            @endforeach
        @endif

        <span class="text-primary fw-light"> / </span>
        {{ $active }}
    </h5>

    @if (!empty($headerButtonRoute))
        <div class=" justify-content-end">
            <div class="d-flex align-items-center">
                <a href="{{ $headerButtonRoute }}" class="btn btn-primary">
                    <i class='bx bx-plus-medical'></i>&nbsp; {{ $headerButton }}
                </a>
                @if(!empty($headerSeasonBtn))
                <a href="{{ route('admin.season') }}" class="btn btn-primary ml-2">
                    <i class='bx bx-cloud-rain'></i>&nbsp; {{$headerSeasonBtn}}
                </a>
                <a href="{{route('admin.category.priority')}}" class="btn btn-primary ml-2">
                    <i class='bx bx-table'></i>&nbsp; Priority Table
                </a>
                @endif



            </div>

        </div>
    @endif


</div>
