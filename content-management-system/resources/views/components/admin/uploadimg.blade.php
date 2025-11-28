<div class="card-body">
    <div class="d-flex align-items-start align-items-sm-center gap-4">
        <img src="{{ $imgpath }}" alt="user-avatar" class="d-block rounded" height="100" width="100"
            style="object-fit: cover;" id="uploadedAvatar" />
        <div class="button-wrapper">
            <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                <span class="d-none d-sm-block">Upload new image</span>
                <i class="bx bx-upload d-block d-sm-none"></i>
                <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg"
                    name="image" />
            </label>

            <button type="button" class="btn btn-outline-secondary account-image-reset mb-4" id="{{ $id }}"
                link="{{ $imgdeletelink }}" onclick="rst(this)" default-img-url="{{$defaultimgurl ?? asset('images/default.jpg') }}">
                <i class="bx bx-reset d-block d-sm-none"></i>
                <span class="d-none d-sm-block">Reset</span>
            </button>

            <p class="text-primary mb-0">{{$imageSizeWarning??"Allowed JPG or PNG. Max size of 2 MB"}}<br>
            {{ $other }}
        </p>
        </div>
    </div>
</div>

@push('script')
    <script src={{ asset('js/image.js') }}></script>
@endpush
