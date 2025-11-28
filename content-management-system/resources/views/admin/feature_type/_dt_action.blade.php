<a href="{{ route('admin.feature_type.edit', $d->id) }}" rel="tooltip" @class(['btn','btn-icon','btn-primary'=>(count($d->features)>0),'btn-danger'])
    title="{{ __('Edit') }}">
    <span class="tf-icons bx bx-edit"></span>
</a>

{{-- <a href="{{ route('admin.feature.index', $d->id) }}" rel="tooltip" class="btn btn-icon btn-primary"
    title="{{ __('Show Features') }}">
    <span class="tf-icons bx bx-category"></span>
</a> --}}
