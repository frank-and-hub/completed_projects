<div style="position: relative; display:inline-block">
    @if (!empty($statusRoute))
        <label class="switch" rel="tooltip" title={{ $status == 'checked' ? 'Active' : 'Inactive' }}>
            <input type="checkbox" link="{{ $statusRoute }}" {{ $status }} id="{{ $id ?? null }}">
            <span class="slider round"></span>
        </label>
    @endif

    @if (!empty($statusRouteUrl))
        <label class="switch" rel="tooltip" title={{ $status == 'checked' ? 'Active' : 'Inactive' }}>
            <input type="checkbox" link="{{ $statusRouteUrl }}" {{ $status }} id="{{ $id ?? null }}"
                is_allowed="{{ $locationStatus ?? null }}">
            <span class="slider round"></span>
        </label>
    @endif

    @if (!empty($editRoute))
        <a href="{{ $editRoute }}" rel="tooltip" class="btn btn-icon ml-1 btn-primary" title="{{ __('Edit') }}">
            <span class="tf-icons bx bx-edit"></span>
        </a>
    @endif

    @if (!empty($seoDescriptionBtn))
        <a href="{{ $seoDescriptionBtn }}" rel="tooltip" class="btn btn-icon ml-1 btn-info" title="{{ __('SEO Content') }}">
            <span class="tf-icons bx bx-chat"></span>
        </a>
    @endif

    @if (!empty($imageUplodRoute))
        <a href="{{ $imageUplodRoute }}" rel="tooltip" class="btn btn-icon ml-1 btn-warning" title="{{ __('Add Images') }}">
            <svg width="29" height="27" viewBox="0 0 29 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="29" height="27" rx="4" fill="#FFAB00" />
                <path
                    d="M6.2 6.10526H20.5V13.4737H22.7V6.10526C22.7 4.94421 21.7133 4 20.5 4H6.2C4.9867 4 4 4.94421 4 6.10526V18.7368C4 19.8979 4.9867 20.8421 6.2 20.8421H15V18.7368H6.2V6.10526Z"
                    fill="white" />
                <path d="M10.6 12.4211L7.29999 16.6316H19.4L15 10.3158L11.7 14.5263L10.6 12.4211Z" fill="white" />
                <path d="M22.7 15.5789H20.5V18.7368H17.2V20.8421H20.5V24H22.7V20.8421H26V18.7368H22.7V15.5789Z"
                    fill="white" />
            </svg>
        </a>
    @endif

    @if (!empty($imageEditRoute))
        <a href="{{ $imageEditRoute }}" rel="tooltip" class="btn btn-icon ml-1 btn-info" title="{{ __('Edit Images') }}">

            <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M2 2H15V7.5L17 8V2C17 0.897 16.103 0 15 0H2C0.897 0 0 0.897 0 2V14C0 15.103 0.897 16 2 16H9V14H2V2Z"
                    fill="white" />
                <path d="M6 7L3 11H14L10 5L7 9L6 7Z" fill="white" />
                <path
                    d="M14.5738 7.63565L15.8553 6.35359L17.6465 8.1447L16.365 9.4268L14.5738 7.63565ZM15.0006 10.7905L10.0411 15.75H8.25V13.9589L13.2095 8.9994L15.0006 10.7905Z"
                    fill="white" stroke="#03C3EC" stroke-width="0.5" />
            </svg>
        </a>
    @endif

    @if (!empty($ShowChlidRoute))
        <a href="{{ $ShowChlidRoute }}" rel="tooltip" class="btn btn-icon ml-1 btn-primary"
            title="{{ __('Show Child Category') }}">
            <span class="tf-icons bx bx-category"></span>
        </a>
    @endif

    @if (!empty($detailsRoute))
        <a href="{{ $detailsRoute }}" rel="tooltip" class="btn btn-icon ml-1 btn-primary"
            title="{{ $detailsRouteTooltipTitle ?? 'Show' }}">
            <span class="tf-icons bx bx-info-circle"></span>
        </a>
    @endif

    @if (!empty($deleteRoute))
        <button link="{{ $deleteRoute }}" rel="tooltip" class="btn btn-icon ml-1 btn-danger dltBtn"
            title="{{ __('Delete') }}">
            <span class="tf-icons bx bx-trash"></span>
        </button>
    @endif

    @if (!empty($changePasswordRoute))
        <a class="btn btn-icon ml-1 btn-primary" rel="tooltip" title="{{ __('Change Password') }}"
            onclick="reset_prompt('{{ $changePasswordRoute }}')">
            <span class='tf-icons bx bxs-lock-open text-white'></span>
        </a>
    @endif

    @if (!empty($other))
        {!! $other !!}
    @endif

    @if (!empty($additionSubtractionButton))
        <button data-id="{{ $additionSubtractionButton }}" rel="tooltip"
            class="btn btn-icon ml-1 btn-{{ $additionSubtractionButton == 'disable' ? 'secondary disabled' : 'success additionSubtractionButton' }}"
            title="{{ __('Add') }}" type="button">
            <input type="checkbox" class="park-select" hidden data-id="{{ $additionSubtractionButton }}">
            @if ($additionSubtractionButton === 'disable')
                <span class="tf-icons bx bx-check"></span>
            @else
                <span class="tf-icons bx bx-plus"></span>
            @endif
        </button>
    @endif

    @if (!empty($subtractionAdditionButton))
        <button data-id="{{ $subtractionAdditionButton }}" rel="tooltip"
            class="btn btn-icon ml-1 btn-danger subtractionAdditionButton" title="{{ __('Remove') }}" type="button">
            <input type="checkbox" class="park-remove" hidden data-id="{{ $subtractionAdditionButton }}">
            <span class="tf-icons bx bx-minus"></span>
        </button>
    @endif

</div>
