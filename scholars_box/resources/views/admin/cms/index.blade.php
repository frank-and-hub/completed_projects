@extends('admin.layout.master')

@push('plugin-styles')
    <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">CMS</a></li>
            <li class="breadcrumb-item active" aria-current="page">CMS List</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    @if(condication(auth()->user(),'8','add'))
                    <a href="{{ route('admin.cms.add') }}">
                        <h6 style="float:right;" class="card-title">Add New</h6>
                    </a>
@endif
                    <h6 class="card-title">CMS List</h6>


                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>S.No</th>

                                    <th>Page Name</th>
                                    <th>Status</th>

                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cmsPages as $key=>$cmsPage)
                                    <tr>
                                        <td>{{ ++$key}}</td>

                                        <td>{{ $cmsPage->page_name }}</td>
                                        <td>{{ ($cmsPage->status==1)? 'Active':'InActive'; }}</td>

                                        <td>
                                            @if(condication(auth()->user(),'8','edit'))
                                            <a href="{{ route('admin.cms.edit', $cmsPage->id) }}"
                                                title="edit" class="btn btn-none bg-none border-0" >
                                                <i class="fas fa-edit"></i> <!-- Edit icon -->
                                            </a> | @endif @if(condication(auth()->user(),'8','view'))
                                            <a href="{{ route('admin.cms.view', $cmsPage->id) }}"title="view" class="btn btn-none bg-none border-0" >
                                                <i class="fas fa-eye"></i> <!-- View icon -->
                                            </a> @endif {{--@if(condication(auth()->user(),'1','delete'))
                                            <a href="{{ route('admin.cms.delete', $cmsPage->id) }}"
                                                title="delete" class="btn btn-none bg-none border-0" >
                                                <i class="fas fa-trash"></i> <!-- Delete icon -->
                                            </a> | @endif--}}
                                            
                                        </td>

                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('admin/assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
@endpush

@push('custom-scripts')
    <script src="{{ asset('admin/assets/js/data-table.js') }}"></script>
@endpush
