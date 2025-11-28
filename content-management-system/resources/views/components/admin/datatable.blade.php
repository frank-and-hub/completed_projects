<div class="row">
    <div class="col-xl">

        <div class="card mb-4">

            @if (!empty($title))
                <div @class([
                    'card-header',
                    'd-flex',
                    'justify-content-between',
                    'align-items-center',
                    'bg-lightblue',
                ])>
                    <h5 @class(['mb-0', 'form-label'])>{{ $title }}</h5>
                    @if (!empty($custom_headings))
                        {{ $custom_headings }}
                    @endif
                </div>
                @if (!empty($other))
                    {{ $other }}
                @endif
            @endif


            <div class="card-body table-responsive text-nowrap mt-3">
                <x-admin.loader id="{{$loaderID?? 'dt-loader'}}" />

                <table class="table table-striped table-hover w-100" id="{{ $id }}">
                    <thead>
                        <tr class="text-nowrap">
                            {{ $headings }}
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
