@extends('admin.layout.master')
<!-- Content wrapper -->
<!-- Content -->
@section('content')
    {{-- Breadcrumb --}}
    <x-admin.breadcrumb :breadcrumbs="$breadcrumbs" active="{{ ucfirst($category->name) }}" />
   
<div @class(['d-none'=>$category->type=='no-child'])>
    <x-admin.datatable id="dt-table" title="{{ ucfirst($category->name) }} " loaderID='dt-loader'>
        <x-slot:headings>
            <th>Name</th>
            <th>Action</th>
        </x-slot:headings>
    </x-admin.datatable>
</div>
     {{-- DataTable --}}
     <div  @class([
        'd-none'=>($category->type=='parent')
     ]) id="parkslist">
    <x-admin.datatable id="parks-dt-table" title="{{ ucfirst($category->name) }}" loaderID='park'>
        <x-slot:headings>
            <th>Park</th>
            <th>Action</th>
        </x-slot:headings>
    </x-admin.datatable>
</div>
@endsection
@push('script')
    <script>
        var uRL = "{{ route('admin.category.parkcategory.dt_list') }}";
        var id = "{{ $category->id }}";
        const subcategory_dt_tbl_url = "{{ route('admin.subcategory.dt_list') }}";
        const category_id = "{{$category->id}}";
        
    </script>

    @if ($category->type=='no-child'){
    <script src="{{ asset('assets/js/dt/parks-category-dt.js') }}"></script>

    }
    @else
    <script src="{{asset('assets/js/dt/parks-subcategory-dt.js')}}"></script>
    @endif
@endpush
