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
    <li class="breadcrumb-item"><a href="#">Scholarship</a></li>
    <li class="breadcrumb-item active" aria-current="page">Scholarship List</li>
  </ol>
</nav>
<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        @if(condication(auth()->user(),'1','add'))
        <a href="{{route('admin.scholarship.add')}}"><h6 style="float:right;" class="card-title">Add New Scholarship</h6></a>
        @endif
        <a href="{{route('admin.scholarship.marquee')}}"><h6 style="float:center; color:green;" class="card-title">Update Marquee</h6></a>
        <h6 class="card-title">Scholarship List</h6>
        
        <div class="table-responsive">
          <table id="dataTableExample" class="table">
            <thead>
              <tr>
                <!--<th>Comapny Name</th>-->
                <th>Scholarship Name</th>
                <th>Published Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($scholarships as $scholarship)
              <tr>
                {{--<td>{{ucwords($scholarship->company[0]->company_name)}}</td>--}}
                <td>{{ucwords($scholarship->scholarship_name)}}</td>
                <!--<td>{{$scholarship->published_date}}</td>-->
                <td>{{ date('d-m-y', strtotime($scholarship->published_date)) }}</td>
                
                
                <td>{{ date('d-m-y', strtotime('-1 day', strtotime($scholarship->end_date))) }}</td>
                <td>{{ucwords( ($scholarship->status == 1) ? 'Active' : (($scholarship->status == 0) ? 'Inactive' : 'Panding') )}}</td>
                <td>
                    @if(condication(auth()->user(),'1','edit'))
                    <a href="{{ route('admin.scholarship.edit', $scholarship->id) }}" title="edit" class="btn btn-none bg-none border-0" >
                        <i class="fas fa-edit"></i> <!-- Edit icon -->
                    </a> | @endif @if(condication(auth()->user(),'1','view'))
                    <a href="{{ route('admin.scholarship.view', $scholarship->id) }}"title="view" class="btn btn-none bg-none border-0" >
                        <i class="fas fa-eye"></i> <!-- View icon -->
                    </a> | @endif @if(condication(auth()->user(),'1','delete'))
                    <!--<a href="{{ route('admin.scholarship.delete', $scholarship->id) }}" title="delete">-->
                    <button type="button" class="btn btn-none bg-none border-0" title="delete" data-toggle="modal" data-target="#exampleModal_{{$scholarship->id}}">
                      <i class="fas fa-trash"></i> <!-- Delete icon -->
                    </button>
                    | @endif @if(condication(auth()->user(),'1','add'))
                    <a href="{{ route('admin.scholarship.application_questions',$scholarship->id)}}" title="scholarship Question" class="btn btn-none bg-none border-0" >
                        <i class="fas fa-file"></i> 
                    </a> | @endif @if(condication(auth()->user(),'1','add'))
                    <a href="{{ route('admin.scholarship.applicants',$scholarship->id)}}" title="Applicants" class="btn btn-none bg-none border-0" >
                        <i class="fas fa-user"></i> 
                    </a> | @endif @if(condication(auth()->user(),'1','edit'))
                    <a href="{{ route('scholarship.apply_now',$scholarship->id)}}" title="Form" class="btn btn-none bg-none border-0" >
                       <i class="fas fa-file-alt"></i> 
                    </a>@endif 
                    @if(auth()->user()->role_id == '1')
                        @if($scholarship->status == 2) | 
                        <a href="{{ route('scholarship.approve',$scholarship->id)}}" title="Publishe" class="btn btn-none bg-none border-0" >
                           <i class="fas fa-check"></i>
                        </a>
                        @endif 
                    @endif
                </td>
            </tr>
            <div class="modal fade" id="exampleModal_{{$scholarship->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Scholarship</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    Are you Sure Want to Delete This Scholarship ?
                  </div>
                  <div class="modal-footer">
                    <a href="{{ route('admin.scholarship.delete', $scholarship->id) }}" class="text-white btn btn-primary">
                        Yes
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" >No</button>
                  </div>
                </div>
              </div>
            </div>
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