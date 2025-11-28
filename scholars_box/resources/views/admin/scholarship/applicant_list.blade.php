@extends('admin.layout.master')

@push('plugin-styles')
<link href="{{ asset('admin/assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"><script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
@endpush
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
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
                <form method="Post" action="{{route('admin.applicantsFilter')}}">
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
                                    <option value="general">General</option>
                                    <option value="obc c">OBC C</option>
                                    <option value="obc nc">OBC NC</option>
                                    <option value="sc">SC</option>
                                    <option value="st">ST</option>
                                    <option value="other reservation">Other Reservation</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label class="form-label">Min Annual Income</label>
                                <input type="text" class="form-control" placeholder="Enter Min Annual Income" name="min_income" />
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label class="form-label">Max Annual Income</label>
                                <input type="text" class="form-control" placeholder="Enter Max Annual Income" name="max_income" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3" style="float: right">
                        <button type="submit" class="btn btn-primary submit">Apply Filters</button>
                        <button class="btn btn-primary submit">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Applicant List</h6>
                <div class="row">
                    <div class="col-2">
                        <a href="{{route('admin.export.applicants',$id)}}"><button class="btn btn-primary">Export Applicants</button></a>
                    </div>
                    <div class="col-6">
                        <form action="{{ route('import.csv') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="csv_file" required />
                            <button type="submit">Upload status CSV</button>
                        </form>
                    </div>
<br>
<br>
<br>
<br>
<br>
<br>
                    <div class="col-6">
                        <form action="{{ route('importdata.csv') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="csvdata_file" required />
                            <button type="submit">Upload Data CSV</button>
                        </form>
                    </div>

                </div>
                <hr>
                <div class="form-control" id="multibutton" style="display: none;">
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#notificationModal">Send Notification</button>
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#notificationModalMail">Send Mail</button>
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#notificationModaResource">Send Resource</button>
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#updateStatus">Update Status</button>
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#deleteStatus">Delete</button>
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#events">Event & Webinars</button>
                </div>
                <div class="table-responsive">
                    <form id="studentForm" action="#" method="post">
                        @csrf
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>S.nos</th>
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
                                @if (isset($applicantsDetails))
                                @foreach ($applicantsDetails as $key => $value)
                                @php
                                 $carbonDate = \Carbon\Carbon::parse($value->created_at);
                                $formattedDate = $carbonDate->format('d M Y');
                                $formattedTime = $carbonDate->format('h:i A');
                                @endphp
                                <tr data-student-id="{{ $value->user->id ?? $value->id }}">
                                    <td><input type="checkbox" class="studentCheckbox"></td>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $value->user->first_name }}</td>
                                    {{-- <td>{{ $formattedDate }} {{ $formattedTime }}</td> --}}
                                    <td>{{ $value->created_at->format('d-m-Y H:i') }}</td>
                                    <td>{{ $value->scholarship->scholarship_name }}</td>
                                    <td>{{ $value->user->email }}</td>
                                    <td>{{ $value->user->phone_number }}</td>
                                    <td>{{ $value->user->gender }}</td>
                                    <td>
    @if($value->user->state)
        {{ $value->user->state }}
    @elseif(isset($value->user->student->addressDetails[0]->state))
        {{ $value->user->student->addressDetails[0]->state }}
    @else
        N/A <!-- or any other default text -->
    @endif
</td>

                                    <td>
                                        <select id="application_status" name="application_status" class="form-control" style="float: right;" onchange="handleStatusChange(this.value, {{ $value->user_id }}, {{ $value->scholarship_id }})">
                                            <option value="">Select Mark Application Status</option>
                                            @foreach (\App\Models\ScholarshipApplication\ScholarshipApplication::STATUS_OPTIONS as $statusKey => $statusValue)
                                            <option value="{{ $statusKey }}" {{ $value->status === $statusKey ? 'selected' : '' }}>
                                                {{ $statusValue }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Enter Custom Status</h5>
                                                    </div>
                                                    <input type="hidden" id="userid">
                                                    <input type="hidden" id="scholarshipId">
                                                    <input type="hidden" id="selectedValueInput" name="status">
                                                    <div class="modal-body">
                                                        <textarea id="custom_status_modal" name="custom_status_modal" class="form-control" placeholder="Enter custom status"></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-primary" onclick="saveCustomStatus()">Save</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if(condication(auth()->user(),'2','view'))
                                        <a href="{{ route('admin.applicantDetails',[$value->user_id, $id]) }}" title="Applicant Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('admin.applicantDisbursal',[$value->user_id, $id]) }}" title="Applicant Disbursal">
                                            <i class="fas fa-money-bill"></i>
                                        </a>
                                        <!--
                                        <a href="{{ route('admin.scholarship.notification',[$value->user_id, $id]) }}" title="Notification">
                                            <i class="fa fa-bell" aria-hidden="true"></i>
                                        </a>
                                        -->
                                        @if(condication(auth()->user(),'2','delete'))
                                        <a href="{{ route('admin.scholarship.applicant.delete', [$value->user_id, $id]) }}" onclick="return confirmDelete();" title="Notification">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                <div class="modal fade" id="exampleModal_{{ $student->user->id ?? '' }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Delete Student
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you Sure Want to Delete This Student ?
                                            </div>
                                            <div class="modal-footer">
                                                <a href="{{ route('student.delete', $student->user->id ?? '') }}" class="text-white btn btn-primary "> Yes</a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
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
                                    <td>{{ date('d-m-Y', strtotime($student->created_at ?? '')) }}</td>
                                    <td>
                                        <span class="btn btn-sm btn-success">
                                            {{ isset($student->site_name) ? $student->site_name : 'ScholarsBox' }}
                                        </span>
                                    </td>
                                    <td>{{ isset($student->microsite) && $student->microsite == 1 ? 'Yes' : 'No' }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-none bg-none border-0" title="delete" data-toggle="modal" data-target="#exampleModal_{{ $student->id }}"> <i class="fas fa-trash"></i></button>
                                        <a href="{{ route('admin.scholarship.notification', [$student->id]) }}" title="Notification">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <div class="modal fade" id="exampleModal_{{ $student->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Delete Student
                                                </h5>
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
                        <button type="button" class="btn btn-primary submit"  onclick="submitNotificationForm()">Save</button>
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
                        <textarea class="form-control @error('who_can_apply_info') is-invalid @enderror" id="summernote" rows="5" placeholder="Enter Email Body" name="who_can_apply_info">{{ old('who_can_apply_info') }}</textarea>
                    </div>
                    <div class="row">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control">
                    </div>
                    <hr>
                    <div class="col-sm-3">
                        <button type="button" class="btn btn-primary submit" onclick="submitEmailForm()">Save</button>
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
                        <button type="button" class="btn btn-primary submit" onclick="submitAssementForm()">Save</button>
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
                        <select name="scholarship_name" class="form-control select2">
                            <option value="">Select Scholarship</option>
                            <input type="hidden" name="scholarship_name" value="{{ $scholarship_id }}">
                            <div class="col-sm-6">
                                <label class="form-label">Doc Name</label>
                                <input type="text" name="document_name" class="form-control">
                            </div>

                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Resource</label>
                        <input type="file" name="document" class="form-control">
                    </div>
                </div>
                <hr>
                <div class="col-sm-3">
                    <button type="button" class="btn btn-primary submit" onclick="submitResourseForm()">Save</button>
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
                            <input type="hidden" name="scholarship_name" value="{{ $scholarship_id }}">
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
                    <input type="checkbox" id="documentsCheckbox" name="extra1" class="studentCheckboxsss"> Documents ?
                    <hr>
                    <div class="row" id="buttons">
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
                        <button type="button" class="btn btn-primary submit" onclick="submitChangeStatusForm()">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteStatus" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel"
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
                <form id="change_statusssss" enctype="multipart/form-data">
                    @csrf
                    <p>Are You Sure You wnat to delete? </p>
                    <hr>
                    <input type="hidden" name="scholarship_id" id="scholarship_id" value="{{ $scholarship_id }}">

                    <div class="col-sm-3">
                        <button type="button" class="btn btn-primary submit" onclick="submitMultideleteForm()">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="events" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel"
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
                <form id="eventsWeb" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 mb-3"> <!-- Adjust column widths as needed -->
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="title" placeholder="Enter Title" class="form-control">
                            <input type="hidden" name="scholarship_name" value="{{ $scholarship_id }}">
                        </div>
                        <div class="col-sm-6 mb-3"> <!-- Adjust column widths as needed -->
                            <label class="form-label">Instructor Name</label>
                            <input type="text" name="assignBy" id="assignBy" placeholder="Enter Instructor Name" class="form-control">

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3"> <!-- Adjust column widths as needed -->
                            <label class="form-label">Title 2</label>
                            <input type="text" name="title2" id="title2" placeholder="Enter Title 2" class="form-control">

                        </div>
                        <div class="col-sm-6 mb-3"> <!-- Adjust column widths as needed -->
                            <label class="form-label">Date</label>
                            <input type="date" name="date" id="date" placeholder="Enter Title 2" class="form-control">

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3"> <!-- Adjust column widths as needed -->
                            <label class="form-label">img</label>
                            <input type="file" name="img" id="img" placeholder="Enter Title 2" class="form-control">
                        </div>
                    </div>
                    <hr>

                    <hr>
                    <div class="row" id="buttons">
                        <div class="col-sm-6">
                            <label class="form-label">Button Title</label>
                            <input type="text" name="button_title" class="form-control">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Link</label>
                            <input type="text" name="link" class="form-control">
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-sm-6">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_Time" class="form-control">
                        </div>
                    </div>

                    <div class="col-sm-3 mt-3">
                        <button type="button" class="btn btn-primary submit" onclick="submitWebninarForm()">Save</button>
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
        var formData = new FormData($('#notificationForm')[0]);
        formData.append('student_ids', JSON.stringify(selectedStudentIds));

        // Add studentIds to the formData
     
        // Send the data to your server using AJAX
        $.ajax({
            url: "{{ route('admin.multiplesave.notification') }}",
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
                console.error('Error saving notification:', error);
                // Optionally, show an error message
            }
        });
    }



    function submitEmailForm() {
        // Get the form data
        var formData = new FormData($('#EmailForm')[0]);

        // Get the IDs of the checked rows
        
        formData.append('student_ids', JSON.stringify(selectedStudentIds));

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
      
        var formData = new FormData($('#assessment_form')[0]);
        // Get the IDs of the checked rows
        formData.append('student_ids', JSON.stringify(selectedStudentIds));

        // Add studentIds to the formData
       

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
        formData.append('student_ids', JSON.stringify(selectedStudentIds));

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

// Array to store selected student IDs
var selectedStudentIds = [];

// When a checkbox is clicked
$(document).on('change', '.studentCheckbox', function() {
    var studentId = $(this).closest('tr').data('student-id');

    if ($(this).is(':checked')) {
        // Add the student ID to the array if checked
        if (!selectedStudentIds.includes(studentId)) {
            selectedStudentIds.push(studentId);
        }
    } else {
        // Remove the student ID from the array if unchecked
        var index = selectedStudentIds.indexOf(studentId);
        if (index !== -1) {
            selectedStudentIds.splice(index, 1);
        }
    }

    // Optional: Console log to verify selected IDs
    console.log(selectedStudentIds);
});

// When selecting all checkboxes
$('#selectAll').on('change', function() {
    if ($(this).is(':checked')) {
        $('.studentCheckbox').each(function() {
            var studentId = $(this).closest('tr').data('student-id');
            $(this).prop('checked', true);
            if (!selectedStudentIds.includes(studentId)) {
                selectedStudentIds.push(studentId);
            }
        });
    } else {
        $('.studentCheckbox').each(function() {
            var studentId = $(this).closest('tr').data('student-id');
            $(this).prop('checked', false);
            var index = selectedStudentIds.indexOf(studentId);
            if (index !== -1) {
                selectedStudentIds.splice(index, 1);
            }
        });
    }

    // Optional: Console log to verify selected IDs
    console.log(selectedStudentIds);
});

// When the form is submitted
function submitChangeStatusForm() {
    var formData = new FormData($('#change_status')[0]);

    // Append the selected student IDs to the form data
    formData.append('student_ids', selectedStudentIds);

    // Send the data to the server using AJAX
    $.ajax({
        url: "{{ route('admin.multiplechnage.status') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            toastr.success('Data updated successfully', 'Success', {
                positionClass: 'toast-top-right',
                timeOut: 3000,
            });
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error('Error saving resource:', error);
        }
    });
}

// Ensure checkboxes remain checked after filtering/searching
function maintainSelectedCheckboxes() {
    $('.studentCheckbox').each(function() {
        var studentId = $(this).closest('tr').data('student-id');
        if (selectedStudentIds.includes(studentId)) {
            $(this).prop('checked', true);
        }
    });
}

// Call maintainSelectedCheckboxes function after the table is reloaded
$(document).on('draw.dt', function() { // This is assuming you're using DataTables
    maintainSelectedCheckboxes();
});
    

function submitWebninarForm() {
    // Get the form data
    var formData = new FormData($('#eventsWeb')[0]);

    // Append the selected student IDs to the form data
    formData.append('student_ids', JSON.stringify(selectedStudentIds));

    // Send the data to your server using AJAX
    $.ajax({
        url: "{{ route('admin.multiplewebninars.add') }}",
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




    function submitMultideleteForm() {
        var formData = new FormData($('#change_statusssss')[0]);


        // Get the IDs of the checked rows
        formData.append('student_ids', JSON.stringify(selectedStudentIds));

        // Send the data to your server using AJAX
        $.ajax({
            url: "{{ route('admin.multiDelete.status') }}",
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
<script>
    function handleStatusChange(selectedValue, userid, scholarshipId) {
        $('#selectedValueInput').val(selectedValue);
        // alert(selectedValue);
        $('#userid').val(userid);
        $('#scholarshipId').val(scholarshipId);
        $('#myModal').modal('show');
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
    function confirmDelete() {
        return confirm('Are you sure you want to delete this item?');
    }
</script>
<script>
    document.getElementById('documentsCheckbox').addEventListener('change', function() {
        var buttonsDiv = document.getElementById('buttons');
        if (this.checked) {
            buttonsDiv.style.display = 'none';
        } else {
            buttonsDiv.style.display = 'flex'; // Use 'flex' to maintain the row layout
        }
    });
</script>
@endpush