@extends('admin.layout.master')

@push('plugin-styles')
  <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

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
      <a href="{{route('admin.scholarship.add')}}"><h6 style="float:right;" class="card-title">Add New</h6></a>

        <h6 class="card-title">Scholarship List</h6>
        
        
        <div class="table-responsive">
          <table id="dataTableExample" class="table">
            <thead>
              <tr>
                <th>Comapny Name</th>
                <th>Scholarship Name</th>
                <th>Published Date</th>
                <th>End Date</th>
                <th>tag</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($scholarships as $scholarship)
              <tr>
                <td>{{$scholarship->company_name}}</td>
                <td>{{$scholarship->scholarship_name}}</td>
                <td>{{$scholarship->published_date}}</td>
                <td>{{$scholarship->end_date}}</td>
                <td>{{$scholarship->tag}}</td>
                <td>
    <a href="{{ route('admin.scholarship.edit', $scholarship->id) }}">
        <i class="fas fa-edit"></i> <!-- Edit icon -->
    </a> | 
    <a href="{{ route('admin.scholarship.view', $scholarship->id) }}">
        <i class="fas fa-eye"></i> <!-- View icon -->
    </a> | 
    <a href="{{ route('admin.scholarship.delete', $scholarship->id) }}">
        <i class="fas fa-trash"></i> <!-- Delete icon -->
    </a> | 
    <a href="{{ route('admin.scholarship.application_questions',$scholarship->id)}}">
        <i class="fas fa-file"></i> <!-- Application icon -->
    </a>|
    <a href="{{ route('admin.scholarship.applicants',$scholarship->id)}}">
        <i class="fas fa-user"></i> <!-- Application icon -->
    </a>
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