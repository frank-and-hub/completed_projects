<section class="content-header {{ $class ?? '' }}">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <div class="title-1">{{ $title ?? 'Default Title' }}</div>
            </div>
            <div class="col-sm-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</section>
