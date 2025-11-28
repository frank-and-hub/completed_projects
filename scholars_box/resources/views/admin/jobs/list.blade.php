@extends('admin.layout.master')

@push('plugin-styles')
  <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59taRfschk3Bf8+8C5JAFA00" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js" integrity="sha384-iS4kLp5rbDTs79t68qPVr2JcF1fkMzU02iyiU6YBVBtdEz/YX8h6Q1RVtnN50I+5" crossorigin="anonymous"></script>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Student</a></li>
    <li class="breadcrumb-item active" aria-current="page">Student List</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Student List</h6>
        <a href="{{route('admin.add.position')}}"><button type="button" class="btn btn-primary">Add Positions</button>
        <!-- <p>PLease note :- Emails and mobile no in excel sheet should be unique</p> -->
        <form action="{{ route('admin.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- <input type="file" name="file" class="form-control" required>
                <br> -->
                <!-- <button type="submit" class="btn btn-success">Import User Data</button> -->
            </form>
        <!-- <div class="form-control" id="multibutton" style="display: none;">
            <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#notificationModal">Send Notification</button>
        </div> -->
        <div class="table-responsive">
          <form id="studentForm" action="#" method="post">
            @csrf
            <table id="dataTableExample" class="table">
              <thead> 
                  <!-- <input type="checkbox" id="selectAllCheckbox"> Select All -->
                <tr>
                   
                  <!-- <th>Select</th> -->
                  <th>First Name</th>
                  
                  <th>Email</th>
                  <th>Mobile No</th>
                  <th>Category</th>

                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              
                @foreach($data as $student)

        
                  <tr>
                    <!-- <td>
                      <input type="checkbox" name="selected_students[]" value="{{ $student->id }}">
                    </td> -->
                    <td>{{$student->first_name}}</td>
                    
                    <td>{{$student->last_name}}</td>
                    <td>{{$student->filed1}}</td>
                    <td>{{$student->category}}</td>
                    <td>
                      <!-- <button type="button" class="btn btn-none bg-none border-0" title="delete" data-toggle="modal" data-target="#exampleModal_{{$student->id}}">
                        <i class="fas fa-trash"></i> 
                      </button> -->
                      <!-- <a href="{{ route('admin.scholarship.notification',[$student->id]) }}" title="Notification" >
                        <i class="fas fa-eye"></i>
                      </a> -->
                     <a href="{{ route('admin.jobrequestDetails',$student->id) }}" title="Notification" >
                        Details
                      </a> 
                    </td>
                  </tr>
                  <div class="modal fade" id="exampleModal_{{$student->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Delete Student</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                Are you Sure Want to Delete This Student ?
                              </div>
                              <div class="modal-footer">
                                    <a  href="{{ route('student.delete', $student->id) }}" class="text-white btn btn-primary ">
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
          </form>
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
                            
                            <hr>

                            <label>Mail Content  </label>
                            <textarea id="newdata" name="mail_content" value="" class="form-control"></textarea>
                            
                            <input type="hidden" name="mail_content" id="mail_content">
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

@push('plugin-scripts')
  <script src="{{ asset('admin/assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
@endpush

@push('custom-scripts')
  <script src="{{ asset('admin/assets/js/data-table.js') }}"></script>
  <script>
    $(document).ready(function(){
      // Handle checkbox change event
      $('input[name="selected_students[]"]').change(function(){
        var checked = $('input[name="selected_students[]"]:checked').length > 0;
        $('#multibutton').toggle(checked);

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
    // Get the value of mail_content from the Summernote editor
    var mailContent = $('#newdata').summernote('code');

    // Set the value of mail_content to a hidden input field
    $('#mail_content').val(mailContent);  
 
    // Submit the form
    $('#notificationForm').submit();
}
</script>
<script>
  $(document).ready(function(){
    // Handle checkbox change event
    $('#selectAllCheckbox').change(function(){
      var isChecked = $(this).prop('checked');
      $('input[name="selected_students[]"]').prop('checked', isChecked);

      // Update multibutton visibility
      $('#multibutton').toggle(isChecked);

      // Update user ids in the modal form
      if (isChecked) {
        var userIds = $('input[name="selected_students[]"]').map(function(){
          return this.value;
        }).get().join(',');
        $('#userIds').val(userIds);
      }
    });

    // Handle individual checkbox change event
    $('input[name="selected_students[]"]').change(function(){
      var checked = $('input[name="selected_students[]"]:checked').length > 0;
      $('#multibutton').toggle(checked);

      // Update user ids in the modal form
      if (checked) {
        var userIds = $('input[name="selected_students[]"]:checked').map(function(){
          return this.value;
        }).get().join(',');
        $('#userIds').val(userIds);
      }

      // Update the "Select All" checkbox state
      $('#selectAllCheckbox').prop('checked', checked);
    });

    // Initialize Select2 for the modal form
    $('.select2').select2();
  });
</script>

{{-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"> --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.js"></script>

<script>
    $('textarea#summernote').summernote({
        
        tabsize: 5,
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'hr']],
            ['help', ['help']]
        ],
    });
</script>  

@endpush
