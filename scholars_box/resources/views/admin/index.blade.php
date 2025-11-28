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
    <li class="breadcrumb-item"><a href="#">User Management</a></li>
    <li class="breadcrumb-item active" aria-current="page">Users List</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">

        <h6 class="card-title">Company List</h6>
        
        
        <div class="table-responsive">
          <table id="dataTableExample" class="table">
            <thead>
              <tr>
                <th>User Name</th>
                <th>email</th>
                <th>Created at</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($user as $value)
            <tr>
                <td>{{$value->first_name . ' ' . $value->last_name}}</td>
                <td>{{$value->email}}</td>
                 <th>{{date('Y-m-d',strtotime($value->created_at))}}</th>
                <td>
                    <a href="{{ route('user.edit', $value->id) }}" class="btn btn-none bg-none border-0" >
                        <i class="fas fa-edit"></i> <!-- Edit icon -->
                    </a><span>  </span> | <span>  </span> 
                    <a href="{{ route('user.show', $value->id) }}" class="btn btn-none bg-none border-0" >
                        <i class="fas fa-eye"></i> <!-- View icon -->
                    </a><span>  </span> | <span>  </span> 
                    <button type="button" class="btn btn-none bg-none border-0" title="delete" data-toggle="modal" data-target="#exampleModal_{{$value->id}}">
                          <i class="fas fa-trash"></i> <!-- Delete icon -->
                        </button> <span>  </span> | <span>  </span> 
                    <a href="{{ route('user.permission',$value->id)}}" class="btn btn-none bg-none border-0" >
                        <i class="fas fa-lock"></i> <!-- Application icon -->
                    </a>
                </td>

            </tr>
            <div class="modal fade" id="exampleModal_{{$value->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    Are you Sure Want to Delete This User ?
                  </div>
                  <div class="modal-footer">
                    <!--<button type="button" class="btn btn-primary" data-dismiss="modal">-->
                        <a href="{{ route('user.delete', $value->id) }}" class="text-white btn btn-primary">
                            Yes
                        </a>
                    <!--</button>-->
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" >No</button>
                  </div>
                </div>
              </div>
            </div>
            @empty
            <tr>
                <td></td>
                <td></td>
                <td>No Data Found</td>
                <td></td>
            </tr>
         @endforelse
          
             
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