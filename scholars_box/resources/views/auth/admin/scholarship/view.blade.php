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
            <input type="text" class="form-control" name="published_date" value="{{ $scholarship->published_date }}" readonly>
        </div>
    </div><!-- Col -->

    <div class="col-sm-4">
        <div class="mb-3">
            <label class="form-label">End Date</label>
            <input type="text" class="form-control" name="end_date" value="{{ $scholarship->end_date }}" readonly>
        </div>
    </div><!-- Col -->
</div><!-- Row -->


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
              <textarea class="form-control" rows="5" placeholder="Enter information about who can apply" name="who_can_apply_info" readonly>{{ $scholarship->who_can_apply_info }}</textarea>
            </div>
          </div><!-- Col -->

          <!-- HOW CAN YOU APPLY? -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">HOW CAN YOU APPLY?</label>
              <textarea class="form-control" rows="5" placeholder="Enter information about how to apply" name="how_to_apply_info" readonly>{{ $scholarship->how_to_apply_info }}</textarea>
            </div>
          </div><!-- Col -->

          <!-- FAQ'S -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">FAQ'S</label>
              <textarea class="form-control" rows="5" placeholder="Enter frequently asked questions" name="faqs" readonly>{{ $scholarship->faqs }}</textarea>
            </div>
          </div><!-- Col -->

          <!-- CONTACT DETAILS -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">CONTACT DETAILS</label>
              <textarea class="form-control" rows="5" placeholder="Enter contact details" name="contact_details" readonly>{{ $scholarship->contact_details }}</textarea>
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

@endsection
