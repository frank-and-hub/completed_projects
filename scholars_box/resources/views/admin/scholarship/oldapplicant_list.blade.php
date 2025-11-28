@extends('admin.layout.master')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @push('plugin-styles')
        <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
        <!-- Add Select2 CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    @endpush
<style>
  .application-status-multiselect {
    display: none;
  }
</style>
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
                    <form method="Post" action="{{route('admin.applicantsFilter')}}" >
                      @csrf
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="application_status" class="form-control select2">
                                        <option value="">Select Status</option>
                                        @foreach (\App\Models\ScholarshipApplication\ScholarshipApplication::STATUS_OPTIONS as $statusKey => $statusValue)
                                            <option value="{{ $statusKey }} {{ old('application_status') == $statusKey ? 'selected' : '' }}">
                                                {{ $statusValue }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="id" value="{{$id}}">
                            <div class="col-sm-3">
                                <div class="mb-3">
                                    <label class="form-label">State</label>
                                    <select name="state" class="form-control select2">
                                        <option value="">Select State</option>
                                        @foreach ($states as $value)
                                            <option value="{{ $value->name }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="mb-3">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-control select2">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Others</option>

                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-control select2">
                                        <option value="">Select Category</option>
                                        <option value="general">
                                            General</option>
                                        <option value="obc c">
                                            OBC C</option>
                                        <option value="obc nc">
                                            OBC NC</option>
                                        <option value="sc">
                                            SC</option>
                                        <option value="st">
                                            ST</option>
                                        <option value="other reservation">
                                            Other Reservation</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="mb-3">
                                    <label class="form-label">Min Annual Income</label>
                         <input type="text" class="form-control" placeholder="Enter Min Annual Income" name="min_income">

                                    
                                </div>
                            </div>
                            <div class="col-sm-3">
                              <div class="mb-3">
                                  <label class="form-label">Max Annual Income</label>
                       <input type="text" class="form-control" placeholder="Enter Max Annual Income" name="max_income">

                                  
                              </div>
                          </div>
                            
                        </div>
                        <div class="col-sm-3" style="float: right">
                          <button type="submit" class="btn btn-primary submit">Apply Filters</button>
                          <button  class="btn btn-primary submit">Reset</button>
                      </div>
                    </form>

                    <!-- Add more select elements if needed -->

                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Applicant List</h6>
                    <a href="{{route('admin.export.applicants',$id)}}"><button style="float:right;" class="btn btn-primary">Export Applicants</button>></a>
                    <div class="form-control" id="multibuttonnotification" style="display: none;">
                        <button type="button" class="btn btn-secondary" onClick="handleStatusChangesss(this.value, {{ $id }}, {{ $value->id }})" >Send Notification</button>
                    </div>
                  
                     <div class="form-group">
                        <div class="form-row" id="multibutton" style="display:none;">
                            <div class="col-md-4" >
                             
                                <label for="application_status_multiselect" class="form-label">Mark Application Status</label>
                                <select id="application_status_multiselect" name="application_status" class="form-control" onchange="handleStatusChangemulti(this.value, {{ $id }}, {{ $value->id }})">
                                    <option value=""> Select Mark Application Status</option>
                                    @foreach (\App\Models\ScholarshipApplication\ScholarshipApplication::STATUS_OPTIONS as $statusKey => $statusValue)
                                        <option value="{{ $statusKey }}" {{ $value->status == $statusKey ? 'selected' : '' }}>
                                            {{ $statusValue }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <input type="checkbox" id="checkAll"> Check All
                                <tr>
                                    <th>Select</th>
                                    <th>S.no</th>
                                    <th>Name</th>
                                    <th>Date of Joining</th>

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
                      
                                @foreach ($applicantsDetails as $key => $value)
                         
                               
                                    <tr>
                                         <td>
                                            <input type="checkbox" name="selected_students[]" value="{{ $value->user_id }}">
                                            </td>
                                        <td>{{ ++$key }}</td>
                                        <td>{{ $value->user->first_name }}</td>
                                        
                                        <td>{{ $value->created_at }}</td>
                                        <td>{{ $value->scholarship->scholarship_name }}</td>


                                        <td>{{ $value->user->email }}</td>
                                        <td>{{ $value->user->phone_number }}</td>
                                        <td>{{ $value->user->gender }}</td>
                                        <td>{{ $value->user->state }}</td>
                                        <td>
                                          
                                                <select id="application_status" name="application_status" class="form-control" style="float: right;"
                                                    onchange="handleStatusChange(this.value, {{ $value->user_id }}, {{ $value->scholarship_id }})">
                                                    <option value="" >Select Mark Application Status</option>
                                                    @foreach (\App\Models\ScholarshipApplication\ScholarshipApplication::STATUS_OPTIONS as $statusKey => $statusValue)
                                                    
                                                        <option value="{{ $statusKey }}" {{ $value->status === $statusKey ? 'selected' : '' }}>
                                                            {{ $statusValue }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLabel">Enter Custom Status</h5>
                                                                {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button> --}}
                                                            </div>
                                                            <input type="hidden" id="userid">
                                                            <input type="hidden" id="scholarshipId">
                                                            <input type="hidden" id="selectedValueInput" name="status">


                                                            <div class="modal-body">
                                                                <textarea id="custom_status_modal" name="custom_status_modal" class="form-control" placeholder="Enter custom status"></textarea>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-primary"
                                                                    onclick="saveCustomStatus()">Save</button>
                                                                {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> --}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <!--<div class="modal fade" id="myModalmulti" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"-->
                                            <!--        aria-hidden="true">-->
                                            <!--        <div class="modal-dialog modal-dialog-centered" role="document">-->
                                            <!--            <div class="modal-content">-->
                                            <!--                <div class="modal-header">-->
                                            <!--                    <h5 class="modal-title" id="exampleModalLabel">Enter Custom Status</h5>-->
                                            <!--                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">-->
                                            <!--                        <span aria-hidden="true">&times;</span>-->
                                            <!--                    </button> --}}-->
                                            <!--                </div>-->
                                            <!--                <div class="modal-body">-->
                                            <!--                    <textarea id="custom_status_modal_multi" name="custom_status_modal" class="form-control" placeholder="Enter custom status"></textarea>-->
                                            <!--                </div>-->
                                            <!--                <div class="modal-footer">-->
                                            <!--                    <button type="button" class="btn btn-primary"-->
                                            <!--                        onclick="saveCustomStatusmulti({{$value->user_id}})">Save</button>-->
                                            <!--                    {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> --}}-->
                                            <!--                </div>-->
                                            <!--            </div>-->
                                            <!--        </div>-->
                                            <!--    </div>-->
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.applicantDetails',[$value->user_id, $id]) }}" title="Applicant Details" >
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.applicantDisbursal',[$value->user_id, $id]) }}" title="Applicant Disbursal" >
                                                <i class="fas fa-money-bill"></i>
                                            </a>
                                            <a href="{{ route('admin.scholarship.notification',[$value->user_id, $id]) }}" title="Notification" >
                                                <i class="fa fa-bell" aria-hidden="true"></i>
                                            </a>
                                                                <a href="#" onclick="confirmDelete('{{ route('admin.scholarship.applicant.delete', [$value->user_id, $id]) }}')" title="Notification">
                        <i class="fa fa-trash" aria-hidden="true"></i>
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
    
    <!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="notificationModalLabel">Send Notification</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="notificationForm" method="Post" action="{{route('admin.multiplesave.notification')}}" >
          @csrf
          <div class="row">
                            <div class="col-sm-6">
                                
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control"> 
                                
                            </div>
                            {{--<input type="hidden" name="userid" value="{{$userid}}">
                             <input type="hidden" name="schid" value="{{$schid}}">--}}
                            <div class="col-sm-6">
                               
                                   
                                    
                                    <label class="form-label">Tag</label>
                                   <select name="teg" class="form-control select2">
                                        <option value="">Select teg</option>
                                        <option value="All Notifications">All Notifications</option>
                                        <option value="New Scholarships">New Scholarships</option>
                                        <option value="Featured Scholarships">Featured Scholarships</option>
                                        <option value="Relevant Scholarships">Relevant Scholarships</option>
                                        <option value="Newsletter">Newsletter</option>
                                        <option value="Application Updates">Application Updates</option>
                                        <option value="Scholarship News">Scholarship News</option>
                                        <option value="Blog Updates">Blog Updates</option>
                                        <option value="Account Notifications">Account Notifications</option>
                                </select>
                               
                                
                            </div>

                            <div class="col-sm-6">
                                
                                    
                                   
                                    <label class="form-label">Description</label>
                                    <textarea name="descrription" class="form-control"></textarea> 
                               
                            </div>
                            <div class="col-sm-6">
                                
                                    <label class="form-label">Author Name</label>
                         <input type="text" class="form-control" placeholder="Author Name" name="author_name">

                                    
                              
                            </div>
                            
                            
                        <input type="hidden" name="user_ids" id="userIds" value="">
          </div>
          <div class="col-sm-3" style="float: right">
            <button type="button" class="btn btn-primary submit" onclick="submitNotificationForm()">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@push('custom-scripts')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="{{ asset('admin/assets/js/data-table.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/select2/select2.min.js') }}"></script>
    <script>
        // Set the CSRF token for every jQuery AJAX request
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            // Initialize Select2 for the dropdowns
            $('.select2').select2();
            $('.tag-select2').select2({
                tags: true,
                tokenSeparators: [',', ' '],
            });
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
    <script>
        function handleStatusChange(selectedValue, userid, scholarshipId) {
            $('#selectedValueInput').val(selectedValue);
            // alert(selectedValue);
            $('#userid').val(userid);
            $('#scholarshipId').val(scholarshipId);
            $('#myModal').modal('show');
        }

        function handleStatusChangesss(selectedValue, id, applicantId) {
            $('#notificationModal').modal('show');
        }
       

        function saveCustomStatus() {
            var customStatusssss = $('#selectedValueInput').val();
           
            // var customStatus = $('#selectedValueInput').val();
            var user_Id = $('#userid').val();
            var sch_id = $('#scholarshipId').val();
            var customStatus = $('#custom_status_modal').val();

            $('#myModal').modal('hide');
            updateApplicationsStatus(customStatus, customStatusssss, sch_id,
            user_Id);
        }
        
      
        
        

        function closeModal() {
            document.getElementById('pdfModal').style.display = 'none';
            document.getElementById('pdfIframe').src = '';
        }

        function downloadPDF() {
            window.location.href = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
        }

        function updateApplicationsStatus(customStatus, status, sch_id, student_id) {
            var url = "{{ route('admin.updateScholorshipStatus') }}";
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
           console.log(student_id);
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    customStatus: customStatus,
                    status: status,
                    sch_id: sch_id,
                    student_id: student_id,
                    _token: csrfToken,
                },
               

                success: function(data) {
                    if (data.message == 'Success') {
                        toastr.success('Status updated successfully', 'Success', {
                            positionClass: 'toast-top-right',
                            timeOut: 3000,
                        });
                    }
                },
                error: function(error) {
                    console.error('Error updating status');
                }
            });
        }
    </script>
 <script>
    function confirmDelete(deleteUrl) {
        if (confirm('Are you sure you want to delete?')) {
            window.location.href = deleteUrl;
        }
    }
</script>
  <script>
  
    $(document).ready(function(){
      // Handle checkbox change event
      $('input[name="selected_students[]"]').change(function(){
        var checked = $('input[name="selected_students[]"]:checked').length > 0;
        $('#multibutton').toggle(checked);
        $('#multibuttonnotification').toggle(checked);

        // Update user ids in the modal form
        if (checked) {
          var userIds = $('input[name="selected_students[]"]:checked').map(function(){
            return this.value;
          }).get().join(',');
          $('#userIds').val(userIds);
        }
      });

      // Initialize Select2 for the modal form
      $('.select2').select2();
    });
  </script>
  <script>
  function submitNotificationForm() {
    // Get selected user_ids
    var userIds = $('input[name="selected_students[]"]:checked').map(function(){
      return this.value;
    }).get().join(',');

    // Update hidden input value
    $('#userIds').val(userIds);

    // Submit the form
    $('#notificationForm').submit();
  }
</script>

<script>
   function saveCustomStatusmulti(applicantId) {
            var customStatus = $('#custom_status_modal_multi').val();
            $('#myModalmulti').modal('hide');
            // Get selected user_ids
                var userIds = $('input[name="selected_students[]"]:checked').map(function () {
                    return this.value;
                }).get().join(',');

                // Update hidden input value
                $('#userIds').val(userIds);
                console.log(userIds);
            updateApplicationStatusForSelectedUsers(customStatus, $('#application_status_multiselect').val(), userIds,
            {{$id}});
        }
    $(document).ready(function () {
        $('#application_status_multiselect').change(function () {
            $('#myModalmulti').modal('show');
           
        });
    });

    // Function to update application status for selected users
    function updateApplicationStatusForSelectedUsers(customStatus, status, sch_id, student_id) {
        var url = "{{ route('admin.updateApplicationStatusForSelectedUsers') }}";
console.log(customStatus, status, sch_id, student_id);
        $.ajax({
            type: 'POST',
            url: url,
            data: {
               customStatus: customStatus,
                    status: status,
                    user_id: sch_id,
                    student_id: student_id,
                   
                // Add any other data you need to send to the server
            },
            success: function (data) {
                if (data.message == 'Success') {
                    toastr.success('Status updated successfully', 'Success', {
                        positionClass: 'toast-top-right',
                        timeOut: 3000, // You can adjust the time the notification stays visible
                    });
                }
            },
            error: function (error) {
                console.error('Error updating status');
            }
        });
    }
</script>
<script>
    $(document).ready(function(){
        // Handle "Check All" checkbox change event
        $('#checkAll').change(function(){
            var isChecked = $(this).prop('checked');
            $('input[name="selected_students[]"]').prop('checked', isChecked);
            $('#multibutton').toggle(isChecked);
            $('#multibuttonnotification').toggle(isChecked);

            

            // Update user ids in the modal form
            if (isChecked) {
                var userIds = $('input[name="selected_students[]"]:checked').map(function(){
                    return this.value;
                }).get().join(',');
                $('#userIds').val(userIds);
            }
        });

        // Handle individual checkbox change event
        $('input[name="selected_students[]"]').change(function(){
            var checked = $('input[name="selected_students[]"]:checked').length > 0;
            $('#multibutton').toggle(checked);
            $('#multibuttonnotification').toggle(checked);
            

            // Update user ids in the modal form
            if (checked) {
                var userIds = $('input[name="selected_students[]"]:checked').map(function(){
                    return this.value;
                }).get().join(',');
                $('#userIds').val(userIds);
            }

            // Update the "Check All" checkbox state
            $('#checkAll').prop('checked', checked);
        });

        // Initialize Select2 for the modal form
        $('.select2').select2();
    });
</script>




@endpush
