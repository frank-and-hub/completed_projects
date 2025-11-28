@push('custom-style')
    <link rel="stylesheet" href="{{ asset('assets/image-gallery/css/lc_lightbox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/image-gallery/skins/minimal.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/park-image-gallery.css') }}">
@endpush
@foreach ($parkImages as $parkimage)
    @php
        if ($parkimage->is_verified == 1) {
            $verify_check_mark_html =
                "<div class='check-mark'>
                <span class='bx bxs-badge-check'
                    style='color:#48D33A; font-size:2rem;'></span>
                <input type='hidden' value='" .
                $parkimage->id .
                "' class='parkimage_id'
                    checked-mark='true'>
            </div>";
        } else {
            $verify_check_mark_html =
                "<div class='check-mark d-none'>
                <span class='bx bxs-badge-check'
                    style='color:#48D33A; font-size:2rem;'></span>
                <input type='hidden' value='" .
                $parkimage->id .
                "' class='parkimage_id' checked-mark='false'>
            </div>";
        }
    @endphp

    <div class='image-preview-box ml-3 mb-3 mt-3 image_box' style="width: 320px;
    height: 220px; position: relative;"
        onclick='selectImg({{ $parkimage->id }},this)' style='position: relative'>
        <input type='hidden' select-img='false' class='selected-img' value='{{ $parkimage->id }}'>

        {{-- <img draggable='false' onclick="viewImg(this)" class='fill-img' src='{{ $parkimage->media->full_path }}'>{!! $verify_check_mark_html !!} --}}
        <img draggable='false'  class='fill-img' src='{{ $parkimage->media->full_path }}'>{!! $verify_check_mark_html !!}

        <a alt="preview" class="gallery-img" src="{{ Storage::url($parkimage->media->path) }}"
            href="{{ $parkimage->media->full_path }}" data-lcl-author=""
            data-lcl-thumb="{{ $parkimage->media->full_path }}">
        </a>
    </div>
@endforeach

