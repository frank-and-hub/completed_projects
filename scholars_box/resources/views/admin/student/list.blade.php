@extends('admin.layout.master')

@push('plugin-styles')
    <link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
    </script>
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
                    <h6 class="card-title">Applicant List</h6>
                    <form method="POST" action="{{ route('admin.student.filters') }}">
                        @csrf
                        <div class="row">
                            <!-- Add align-items-center class to vertically center elements -->
                            <div class="col-sm-6"> <!-- Use a larger column width to accommodate both elements -->
                                <div class="row">
                                    <div class="col-sm-6 mb-3"> <!-- Adjust column widths as needed -->
                                        <label class="form-label">Scholarships</label>
                                        <select name="scholarship_name" class="form-control select2">
                                            <option value="">Select Scholarship</option>
                                            @foreach ($scholarships as $value)
                                            
                                            <option value="{{ $value->id }}" @if($value->id == $scholarshipid) selected @endif>{{ $value->scholarship_name }}</option>
                                            @endforeach

                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <div class="form-check form-check-inline">

                                            <input type="checkbox" class="form-check-input" name="award" id="checkInline">
                                            <label class="form-check-label" for="checkInline">
                                                Award
                                            </label>
                                        </div>
                                    </div>
                                
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12"> <!-- Adjust column widths as needed -->
                                    <label class="form-label">Search</label>
                                    <textarea name="search" class="form-control"></textarea>
                                </div>
                            </div>
                            
                            <div class="col-sm-12" style="text-align: right; margin-top:22px;"> <!-- Adjust alignment as needed -->
                                <button type="submit" class="btn btn-primary submit">Apply Filters</button>
                                <a href="{{ route('admin.student.list') }}">
                                    <button type="button" class="btn btn-primary">Reset</button>
                                </a>
                            </div>
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
                    <h6 class="card-title">Student List</h6>

                    <div class="form-control" id="multibutton" style="display: none;">
                        <button type="button" class="btn btn-secondary" data-toggle="modal"
                            data-target="#notificationModal">Send Notification</button>
                        <button type="button" class="btn btn-secondary" data-toggle="modal"
                            data-target="#notificationModalMail">Send Mail</button>
                        <button type="button" class="btn btn-secondary" data-toggle="modal"
                            data-target="#notificationModaAssessment">Send Assessment</button>
                        <button type="button" class="btn btn-secondary" data-toggle="modal"
                            data-target="#notificationModaResource">Send Resource</button>
                        <button type="button" class="btn btn-secondary" data-toggle="modal"
                            data-target="#updateStatus">Update Status</button>
                    </div>

                    <div class="table-responsive">
                        <form id="studentForm" action="#" method="post">
                            @csrf
                            <table id="dataTableExample" class="table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>Save As Draft</th>
                                        <th>Mobile Number </th>
                                        <th>Joining Date</th>
                                        <th>Login From</th>
                                        <th>Login From MicroSite</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($data))
                                        @foreach ($data as $student)
                                            <tr data-student-id="{{ $student->user->id ?? $student->id }}">
                                                <td><input type="checkbox" class="studentCheckbox"></td>
                                                <td>{{ $student->user->first_name ?? '' }}</td>
                                                <td>{{ $student->user->last_name ?? '' }}</td>
                                                <td>{{ $student->user->email ?? '' }}</td>

                                                @if(isset($student->student) && isset($student->student->draft))

                                                <td>Yes</td>
                                                @else
                                                <td>No</td>
                                                    @endif
                                                <td>{{ $student->user->phone_number ?? '' }}</td>
                                                <td>{{ date('d-m-Y', strtotime($student->user->created_at ?? '')) }}</td>
                                                <td>
                                                    <span class="btn btn-sm btn-success">
                                                        {{ isset($student->site_name) ? $student->user->site_name : 'ScholarsBox' }}
                                                    </span>
                                                </td>
                                                <td>{{ isset($student->microsite) && $student->user->microsite == 1 ? 'Yes' : 'No' }}
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-none bg-none border-0"
                                                        title="delete" data-toggle="modal"
                                                        data-target="#exampleModal_{{ $student->user->id ?? '' }}">
                                                        <i class="fas fa-trash"></i> <!-- Delete icon -->
                                                    </button>
                                                </td>
                                            </tr>
                                            <div class="modal fade" id="exampleModal_{{ $student->user->id ?? '' }}"
                                                tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                                aria-hidden="true">

                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">Delete Student
                                                            </h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you Sure Want to Delete This Student ?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <a href="{{ route('student.delete', $student->user->id ?? '') }}"
                                                                class="text-white btn btn-primary ">
                                                                Yes
                                                            </a>
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">No</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        @foreach ($students as $student)
                                            <tr data-student-id="{{ $student->id ?? '' }}">
                                                <td><input type="checkbox" class="studentCheckbox"></td>
                                                <td>{{ $student->first_name ?? '' }}</td>
                                                <td>{{ $student->last_name ?? '' }}</td>
                                                <td>{{ $student->email ?? '' }}</td>
                                                @if(isset($student->draft) )

                                                <td>Yes</td>
                                                @else
                                                <td>No</td>
                                                    @endif

                                                <td>{{ $student->phone_number ?? ''  }}</td>
                                                <td>{{ date('d-m-Y', strtotime($student->created_at ?? '')) }}</td>
                                                <td>
                                                    <span class="btn btn-sm btn-success">
                                                        {{ isset($student->site_name) ? $student->site_name : 'ScholarsBox' }}
                                                    </span>
                                                </td>
                                                <td>{{ isset($student->microsite) && $student->microsite == 1 ? 'Yes' : 'No' }}
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-none bg-none border-0"
                                                        title="delete" data-toggle="modal"
                                                        data-target="#exampleModal_{{ $student->id }}">
                                                        <i class="fas fa-trash"></i> <!-- Delete icon -->
                                                    </button>
                                                </td>
                                            </tr>
                                            <div class="modal fade" id="exampleModal_{{ $student->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

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
                                                            <a href="{{ route('student.delete', $student->id) }}" class="text-white btn btn-primary "> Yes</a>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" role="dialog"
        aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document"> <!-- Added modal-lg class here -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Send Notification</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="notificationForm">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Tag</label>
                                <select name="teg" class="form-control">
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
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Description</label>
                                <textarea name="descrription" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="form-label">Author Name</label>
                                <input type="text" class="form-control" placeholder="Author Name" name="author_name">
                            </div>
                        </div>
                        <hr>
                        <div class="col-sm-3">
                            <button type="button" class="btn btn-primary submit"
                                onclick="submitNotificationForm()">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="notificationModalMail" tabindex="-1" role="dialog"
        aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document"> <!-- Added modal-lg class here -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Send Notification</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="EmailForm">
                        @csrf
                        <div class="row">
                            <textarea class="form-control @error('who_can_apply_info') is-invalid @enderror" id="summernote" rows="5"
                                placeholder="Enter Email Body" name="who_can_apply_info">{{ old('who_can_apply_info') }}</textarea>

                        </div>
                        <div class="row">
                            <label>Subject</label>
                            <input type="text" name="subject" class="form-control">
                        </div>
                        <hr>
                        <div class="col-sm-3">
                            <button type="button" class="btn btn-primary submit"
                                onclick="submitEmailForm()">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="notificationModaAssessment" tabindex="-1" role="dialog"
        aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document"> <!-- Added modal-lg class here -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Send Assessment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="assessment_form">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="form-label">Button Title</label>
                                <input type="text" name="title" class="form-control">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Link</label>
                                <input type="text" name="link" class="form-control">
                            </div>
                        </div>
                        <hr>
                        <div class="col-sm-3">
                            <button type="button" class="btn btn-primary submit"
                                onclick="submitAssementForm()">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="notificationModaResource" tabindex="-1" role="dialog"
        aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document"> <!-- Added modal-lg class here -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Send Resourse</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="resourse_form" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6 mb-3"> <!-- Adjust column widths as needed -->
                                <label class="form-label">Scholarships</label>
                                <select name="scholarship_name" class="form-control select2" id="scholarship_name">
                                    <option value="">Select Scholarship</option>
                                    @foreach ($scholarships as $value)
                                        <option value="{{ $value->id }}">{{ $value->scholarship_name }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Resource</label>
                                <input type="file" name="document" class="form-control">
                            </div>
                        </div>
                        <hr>
                        <div class="col-sm-3">
                            <button type="button" class="btn btn-primary submit"
                                onclick="submitResourseForm()">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="updateStatus" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document"> <!-- Added modal-lg class here -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Update Status </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="change_status" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6 mb-3"> <!-- Adjust column widths as needed -->
                                <label class="form-label">Scholarships</label>
                                <select name="scholarship_name" class="form-control select2">
                                    <option value="">Select Scholarship</option>
                                    @foreach ($scholarships as $value)
                                        <option value="{{ $value->id }}">{{ $value->scholarship_name }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="col-sm-6 mb-3"> <!-- Adjust column widths as needed -->
                                <label class="form-label">Scholarships</label>
                                <select name="application_status" class="form-control select2">
                                    <option value="">Select Mark Application Status</option>
                                    @foreach (\App\Models\ScholarshipApplication\ScholarshipApplication::STATUS_OPTIONS as $statusKey => $statusValue)
                                        <option value="{{ $statusKey }}"
                                            {{ $value->status === $statusKey ? 'selected' : '' }}>
                                            {{ $statusValue }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <label>Custom Message</label>
                            <textarea class="form-control" name="description"></textarea>
                        </div>
                        <hr>
                        <div class="col-sm-3">
                            <button type="button" class="btn btn-primary submit"
                                onclick="submitChangeStatusForm()">Save</button>
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
        // JavaScript for Select All functionality
        $(document).ready(function() {
            // Event listener for "Select All" checkbox
            $('#selectAll').change(function() {
                // Check if "Select All" checkbox is checked
                if ($(this).prop('checked')) {
                    // If checked, select all checkboxes
                    $('.studentCheckbox').prop('checked', true);
                    // Show the button
                    $('#multibutton').show();
                } else {
                    // If unchecked, deselect all checkboxes
                    $('.studentCheckbox').prop('checked', false);
                    // Hide the button
                    $('#multibutton').hide();
                }
            });

            // Event listener for individual checkbox change
            $('.studentCheckbox').change(function() {
                // Check if any checkbox is checked
                if ($('.studentCheckbox:checked').length > 0) {
                    // If at least one checkbox is checked, show the button
                    $('#multibutton').show();
                } else {
                    // If no checkbox is checked, hide the button
                    $('#multibutton').hide();
                }

                // Check if all checkboxes are checked and update "Select All" checkbox
                if ($('.studentCheckbox:checked').length === $('.studentCheckbox').length) {
                    $('#selectAll').prop('checked', true);
                } else {
                    $('#selectAll').prop('checked', false);
                }
            });
        });
    </script>
    <script>
        function submitNotificationForm() {
            // Get the form data
            var formData = $('#notificationForm').serializeArray();

            // Get the IDs of the checked rows
            var studentIds = [];
            $('.studentCheckbox:checked').each(function() {
                studentIds.push($(this).closest('tr').data('student-id'));
            });

            // Add studentIds to the formData
            formData.push({
                name: 'student_ids',
                value: studentIds
            });

            // Send the data to your server using AJAX
            $.ajax({
                url: "{{ route('admin.multiplesave.notification') }}",
                type: 'POST',
                data: formData,
                success: function(response) {

                    toastr.success('Data updated successfully', 'Success', {
                        positionClass: 'toast-top-right',
                        timeOut: 3000,
                    });

                    location.reload();

                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error('Error saving notification:', error);
                    // Optionally, show an error message
                }
            });
        }



        function submitEmailForm() {
            // Get the form data
            var formData = $('#EmailForm').serializeArray();

            // Get the IDs of the checked rows
            var studentIds = [];
            $('.studentCheckbox:checked').each(function() {
                studentIds.push($(this).closest('tr').data('student-id'));
            });

            // Add studentIds to the formData
            formData.push({
                name: 'student_ids',
                value: studentIds
            });

            // Send the data to your server using AJAX
            $.ajax({
                url: "{{ route('admin.multiplesave.email') }}",
                type: 'POST',
                data: formData,
                success: function(response) {

                    toastr.success('Data updated successfully', 'Success', {
                        positionClass: 'toast-top-right',
                        timeOut: 3000,
                    });

                    location.reload();

                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error('Error saving notification:', error);
                    // Optionally, show an error message
                }
            });
        }

        function submitAssementForm() {
            // Get the form data
            var formData = $('#assessment_form').serializeArray();

            // Get the IDs of the checked rows
            var studentIds = [];
            $('.studentCheckbox:checked').each(function() {
                studentIds.push($(this).closest('tr').data('student-id'));
            });

            // Add studentIds to the formData
            formData.push({
                name: 'student_ids',
                value: studentIds
            });

            // Send the data to your server using AJAX
            $.ajax({
                url: "{{ route('admin.multiplesave.assestment') }}",
                type: 'POST',
                data: formData,
                success: function(response) {

                    toastr.success('Data updated successfully', 'Success', {
                        positionClass: 'toast-top-right',
                        timeOut: 3000,
                    });

                    location.reload();

                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error('Error saving notification:', error);
                    // Optionally, show an error message
                }
            });
        }

        function submitResourseForm() {
            // Get the form data
            var formData = new FormData($('#resourse_form')[0]);

            // Get the IDs of the checked rows
            var studentIds = [];
            $('.studentCheckbox:checked').each(function() {
                studentIds.push($(this).closest('tr').data('student-id'));
            });

            // Append studentIds to the formData
            formData.append('student_ids', studentIds);

            
            // Send the data to your server using AJAX
            $.ajax({
                url: "{{ route('admin.multiplesave.resourse') }}",
                type: 'POST',
                data: formData,
                processData: false, // Prevent jQuery from processing the data
                contentType: false, // Prevent jQuery from setting contentType
                success: function(response) {
                    toastr.success('Data updated successfully', 'Success', {
                        positionClass: 'toast-top-right',
                        timeOut: 3000,
                    });
                    location.reload();
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error('Error saving resource:', error);
                    // Optionally, show an error message
                }
            });
        }

        function submitChangeStatusForm() {
            // Get the form data
            var formData = new FormData($('#change_status')[0]);

            // Get the IDs of the checked rows
            var studentIds = [];
            $('.studentCheckbox:checked').each(function() {
                studentIds.push($(this).closest('tr').data('student-id'));
            });

            // Append studentIds to the formData
            formData.append('student_ids', studentIds);

            // Send the data to your server using AJAX
            $.ajax({
                url: "{{ route('admin.multiplechnage.status') }}",
                type: 'POST',
                data: formData,
                processData: false, // Prevent jQuery from processing the data
                contentType: false, // Prevent jQuery from setting contentType
                success: function(response) {
                    toastr.success('Data updated successfully', 'Success', {
                        positionClass: 'toast-top-right',
                        timeOut: 3000,
                    });
                    location.reload();
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error('Error saving resource:', error);
                    // Optionally, show an error message
                }
            });
        }
    </script>
@endpush
