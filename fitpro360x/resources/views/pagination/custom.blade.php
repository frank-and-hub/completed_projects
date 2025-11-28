@if ($paginator->hasPages())
<ul class="pagination mb-0">

    {{-- First Page --}}
    <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
        <a class="page-link" href="{{ $paginator->url(1) }}" aria-label="First">
            <span aria-hidden="true">
                <img src="{{ asset('assets/images/first-page-icon.png') }}">
            </span>
        </a>
    </li>

    {{-- Previous Page --}}
    <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous">
            <span aria-hidden="true">
                <img src="{{ asset('assets/images/pre-page.png') }}">
            </span>
        </a>
    </li>

    {{-- Page Numbers --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
            @endforeach
        @endif
    @endforeach

    {{-- Next Page --}}
    <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" aria-label="Next">
            <span aria-hidden="true">
                <img src="{{ asset('assets/images/next-page-icon.png') }}">
            </span>
        </a>
    </li>

    {{-- Last Page --}}
    <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
        <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Last">
            <span aria-hidden="true">
                <img src="{{ asset('assets/images/last-page-icon.png') }}">
            </span>
        </a>
    </li>
</ul>
@endif
