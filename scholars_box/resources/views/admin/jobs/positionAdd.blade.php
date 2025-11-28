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
                    <h6 class="card-title">Add Positions</h6>
                    <form method="Post" action="{{route('admin.position.save')}}" >
                      @csrf
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <input type="text" class="form-control" name="position" id="position">
                                </div>
                            </div>
                            
                          
                          

                         
                      
                            
                        </div>
                        <div class="col-sm-3" style="float: right">
                          <button type="submit" class="btn btn-primary submit">Add</button>
                         
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
                    <h6 class="card-title">Positions List</h6>
                    
                  
                   
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <input type="checkbox" id="checkAll"> Check All
                                <tr>
                                    
                                    <th>S.no</th>
                                    <th>Name</th>
                                
                                    <th>Status</th>
                                    
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                      
                                @foreach ($positions as $key => $value)
                            
                               
                                    <tr>
                                         
                                        <td>{{ ++$key }}</td>
                                        <td>{{ $value->name}}</td>
                                     
                                        <td>{{ $value->status }}</td>
                                        
                                        <td>
                                            
                                            <a href="#" onclick="confirmDelete('{{ route('admin.scholarship.applicant.delete', $value->id) }}')" title="Notification">
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
