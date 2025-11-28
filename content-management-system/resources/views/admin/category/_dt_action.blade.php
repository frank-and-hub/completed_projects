<a href="{{ route('admin.category.edit', $d->id) }}" rel="tooltip" class="btn btn-icon btn-primary"
    title="{{ __('Edit') }}">
    <span class="tf-icons bx bx-edit"></span>
</a>
@if($d->type != 'Stand-Alone')
<a href="{{ route('admin.subcategory.index', $d->id) }}" rel="tooltip" class="btn btn-icon btn-primary"
    title="{{ __('Show Child Category') }}">
    <span class="tf-icons bx bx-category"></span>
</a>
@endif
{{-- <button type="button" class="btn btn-outline-secondary">
    <span class="tf-icons bx bx-bell"></span>&nbsp; Secondary
  </button> --}}
