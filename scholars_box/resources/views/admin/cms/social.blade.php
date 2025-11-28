@extends('admin.layout.master')

@push('plugin-styles')
    <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endpush

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">CMS</a></li>
            <li class="breadcrumb-item active" aria-current="page">Social Media</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    @if(condication(auth()->user(),'8','add'))
                    <a href="#" id="addNewRow">
                        <h6 style="float:right;" class="card-title">Add New</h6>
                    </a>
@endif
                    <h6 class="card-title">Social List</h6>

                    <form action="{{route('admin.cms.update.social_media')}}" method="post" class="table-responsive">
                         @csrf
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Title</th>
                                    <th>Icon</th>
                                    <th>URL</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($social as $key => $val)
                                    <tr class="row-container">
                                        
                                        <td>{{ ++$key }}</td>
                                        <td class="title" >{{$val->title }}</td>
                                        <td class="icon" ><i class="fa fa-{{$val->icon}}"  style="font-size:24px; " > </i></td>
                                        <td class="link" >{{$val->link}}</td>
                                        <td>
                                            @if(condication(auth()->user(),'8','edit'))
                                                <a href="#" title="edit" class="btn btn-none bg-none border-0 edit-btn">
                                                    <i class="fas fa-edit"></i> 
                                                </a> 
                                                <button type="submit" name="submit" value={{$val->id}} class="d-none submit-btn">
                                                    Submit
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    </form>
                                @endforeach
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script>
    $(document).ready(function() {
        $('.edit-btn').on('click', function() {
            var row = $(this).closest('tr');

            // Switch to edit mode
            row.find('.title').html('<input type="text" name="title" value="' + row.find('.title').text() + '">');
            row.find('.icon').html('<input type="text" name="icon" value="' + row.find('.icon i').text() + '">');
            row.find('.link').html('<input type="text" name="link" value="' + row.find('.link').text() + '">');

            // Show the submit button
            row.find('.submit-btn').removeClass('d-none');
            row.find('.edit-btn').addClass('d-none');
        });
        $('#addNewRow').on('click', function() {
            var newRowHtml = '<tr class="row-container">' +
                                '<td>New</td>' +
                                '<td class="title"><input type="text" name="title" value=""></td>' +
                                '<td class="icon"><input type="text" name="icon" value=""></td>' +
                                '<td class="link"><input type="text" name="link" value=""></td>' +
                                '<td>' +
                                    '<a href="#" title="edit" class="btn btn-none bg-none border-0 edit-btn d-none ">' +
                                        '<i class="fas fa-edit"></i>' +
                                    '</a>' +
                                    '<button type="submit" name="submit" value="" class="submit-btn">' +
                                        'Submit' +
                                    '</button>' +
                                '</td>' +
                            '</tr>';
            $('#dataTableExample tbody').append(newRowHtml);
        });
    });
</script>
@endsection

@push('plugin-scripts')
    <script src="{{ asset('admin/assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
@endpush

@push('custom-scripts')
    <script src="{{ asset('admin/assets/js/data-table.js') }}"></script>
@endpush
