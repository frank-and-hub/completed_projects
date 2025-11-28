@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Scholarship</a></li>
    <li class="breadcrumb-item active" aria-current="page">View Scholarship</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Scholarship Form - View</h6>
     

        <div class="row">
    <!-- Company Name and Scholarship Name -->
    <div class="col-sm-6">
        <div class="mb-3">
            <label class="form-label">Company Name</label>
            <input type="text" class="form-control" placeholder="Enter company name" name="company_name" value="{{ $scholarship->company_name }}" readonly>
        </div>
    </div><!-- Col -->

    <div class="col-sm-6">
        <div class="mb-3">
            <label class="form-label">Scholarship Name</label>
            <input type="text" class="form-control" placeholder="Enter scholarship name" name="scholarship_name" value="{{ $scholarship->scholarship_name }}" readonly>
        </div>
    </div><!-- Col -->
</div><!-- Row -->

<div class="row">
    <!-- Tag, Published Date, and End Date -->
    <div class="col-sm-4">
        <div class="mb-3">
            <label class="form-label">Tag</label>
            <input type="text" class="form-control" placeholder="Enter tag" name="tag" value="{{ $scholarship->tag }}" readonly>
        </div>
    </div><!-- Col -->

    <div class="col-sm-4">
        <div class="mb-3">
            <label class="form-label">Published Date</label>
            <input type="text" class="form-control" name="published_date" value="{{ date('d-m-y', strtotime($scholarship->published_date)) }}" readonly>
        </div>
    </div><!-- Col -->

    <div class="col-sm-4">
        <div class="mb-3">
            <label class="form-label">End Date</label>
            <input type="text" class="form-control" name="end_date" value="{{ date('d-m-y', strtotime($scholarship->end_date)) }}" readonly>
        </div>
    </div><!-- Col -->
</div><!-- Row -->

 <div class="mb-3">
                    <label class="form-label">Scholarship Image Preview</label>
                    <img src="{{($scholarship == null) ? asset('images/logo.png') : asset($scholarship->avatar) }}" id="avatar-preview" style="width:100px; height:100px;" class="center" alt="Scholarship Image"/>
                    <input type="hidden" name="avatar_hidden" id="" class="" value="{{isset($scholarship) ? old('avatar', $scholarship->avatar) : '' }}" />
                   
                </div>
          <!-- Short Description -->
          <div class="col-sm-6">
            <div class="mb-3">
              <label class="form-label">Short Description</label>
              <input type="text" class="form-control" placeholder="Enter short description" name="short_desc" value="{{ $scholarship->short_desc }}" readonly>
            </div>
          </div><!-- Col -->

          <!-- Scholarship Information -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">SCHOLARSHIP</label>
              <textarea class="form-control" rows="5" placeholder="Enter your scholarship information" name="scholarship_info" readonly>{{ $scholarship->scholarship_info }}</textarea>
            </div>
          </div><!-- Col -->

          <!-- ABOUT THE SPONSOR -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">ABOUT THE SPONSOR</label>
              <textarea class="form-control" rows="5" placeholder="Enter information about the sponsor" name="sponsor_info" readonly>{{ $scholarship->sponsor_info }}</textarea>
            </div>
          </div><!-- Col -->

          <!-- WHO CAN APPLY? -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">WHO CAN APPLY?</label>
              <textarea class="form-control" rows="5" id="summernote" placeholder="Enter information about who can apply" name="who_can_apply_info" readonly>{!! $scholarship->who_can_apply_info !!}</textarea>
           
            </div>
          </div><!-- Col -->

          <!-- HOW CAN YOU APPLY? -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">HOW CAN YOU APPLY?</label>
              <textarea class="form-control" rows="5" id="summernote" placeholder="Enter information about how to apply" name="how_to_apply_info" readonly>{!! $scholarship->how_to_apply_info !!}</textarea>
           
            </div>
          </div><!-- Col -->

          <!-- FAQ'S -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">FAQ'S</label>
              <div>{!! $scholarship->faqs !!}</div>
            </div>
          </div><!-- Col -->

      <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">CONTACT DETAILS</label>
          
              <div id="contactDetailsContainer">
                @if(!empty($scholarshipContactDetails) && is_countable($scholarshipContactDetails) && count($scholarshipContactDetails) > 0)
                @foreach ($scholarshipContactDetails as $index => $val)
                  <div class="row contact-details">
                    <div class="col-sm-4">
                      <input type="text" class="form-control mb-2" placeholder="Enter name" name="contact_names[]" value="{{ old('contact_names.'.$index, $val->name) }}"readonly>
                    </div>
                    <div class="col-sm-4">
                      <input type="email" class="form-control mb-2" placeholder="Enter email" name="contact_emails[]" value="{{ old('contact_emails.'.$index, $val->email) }}"readonly>
                    </div>
                    <div class="col-sm-4">
                      <input type="text" class="form-control mb-2" placeholder="Enter phone number" name="contact_phones[]" value="{{ old('contact_phones.'.$index, $val->phone) }}"readonly>
                    </div>
                    <div class="col-sm-1">
                      <!--<button type="button" class="btn btn-danger btn-sm remove-contact">Remove</button>-->
                    </div>
                  </div>
                @endforeach
                
              @endif
          
              <!--<button type="button" class="btn btn-success btn-sm mt-2" id="addMore">Add More</button>-->
          
              @error('contact_names.*')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
          
              @error('contact_emails.*')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
          
              @error('contact_phones.*')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- Col -->

         
       
      </div>
    </div>
  </div>
</div>

<script>
  function openDatePicker(inputId) {
    const inputField = document.getElementById(inputId);

    // Check if the input field exists
    if (inputField) {
      inputField.type = 'date';
      inputField.focus();
    }
  }
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.js"></script>

    <script>
        $('textarea#summernote').summernote({
            placeholder: 'Write Your Content',
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
@endsection
