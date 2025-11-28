<a href="{{ route('admin.subcategory.edit', $d->id) }}" rel="tooltip" class="btn btn-icon btn-primary"
    title="{{ __('Edit') }}">
    <span class="tf-icons bx bx-edit"></span>
</a>

<button link="{{ route('admin.delete.child.category', $d->id) }}"  rel="tooltip" class="btn btn-icon btn-danger dltBtn"
    title="{{ __('Delete') }}">
    <span class="tf-icons bx bx-trash"></span>
</button>

