@extends('admin.layout.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@push('plugin-styles')
  <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Applicant</a></li>
    <li class="breadcrumb-item active" aria-current="page">Applicant List</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
      

        <h6 class="card-title">Applicant List</h6>
        
        
        <div class="table-responsive">
          <table id="dataTableExample" class="table">
            <thead>
              <tr>
              <th>S.no</th>

                <th>Name</th>
                <th>Scholarship Name</th>
                <th>Email</th>
                <th>Mobile Number</th>
                <th>Gender</th>
                <th>State</th>
                <th>Application Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            @foreach($applicantsDetails as $key => $value)
            <tr>
                <td>{{ ++$key }}</td>
                <td>{{ $value->user->first_name }}</td>
                <td>{{ $value->scholarship->scholarship_name }}</td>
                <td>{{ $value->user->email }}</td>
                <td>{{ $value->user->phone_number }}</td>
                <td>{{ $value->user->gender }}</td>
                <td>{{ $value->user->state }}</td>
                
                <td>
                    <select name="application_status" class="form-control" onchange="updateApplicationStatus(this.value, {{ $value->id }})">
                        @foreach(\App\Models\ScholarshipApplication\ScholarshipApplication::STATUS_OPTIONS as $statusKey => $statusValue)
                            <option value="{{ $statusKey }}" {{ $value->status == $statusKey ? 'selected' : '' }}>{{ $statusValue }}</option>
                        @endforeach
                    </select>
                </td>
                <td>   <a href="{{ route('admin.applicantDetails', $value->user_id) }}">
        <i class="fas fa-eye"></i> <!-- Edit icon -->
    </a> </td>
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

@push('custom-scripts')
  <script src="{{ asset('admin/assets/js/data-table.js') }}"></script>
  <script src="{{ asset('admin/assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script>
    // Set the CSRF token for every jQuery AJAX request
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function updateApplicationStatus(status, sch_id) {
        var url = "{{ route('admin.updateApplicationStatus') }}";

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                status: status,
                sch_id: sch_id,
                // Add any other data you need to send to the server
            },
            success: function(data) {
                if (data.message == 'Success') {
                    toastr.success('Status updated successfully', 'Success', {
                        positionClass: 'toast-top-right',
                        timeOut: 3000, // You can adjust the time the notification stays visible
                    });

                   
                }
               
            },
            error: function(error) {
                console.error('Error updating status');
            }
        });
    }
</script>



@endpush
