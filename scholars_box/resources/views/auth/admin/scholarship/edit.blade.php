@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Scholarship</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Scholarship</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Scholarship Form - Edit</h6>
        <form method="POST" action="{{ route('admin.scholarship.update', $scholarship->id) }}">
          @csrf
          @method('PUT')

          <div class="row">
    <!-- Company Name and Scholarship Name -->
    <div class="col-sm-6">
        <div class="mb-3">
            <label class="form-label">Company Name</label>
            <input type="text" class="form-control @error('company_name') is-invalid @enderror" placeholder="Enter company name" name="company_name" value="{{ old('company_name', $scholarship->company_name) }}">
            @error('company_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Scholarship Name</label>
            <input type="text" class="form-control @error('scholarship_name') is-invalid @enderror" placeholder="Enter scholarship name" name="scholarship_name" value="{{ old('scholarship_name', $scholarship->scholarship_name) }}">
            @error('scholarship_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div><!-- Col -->

    <!-- Tag, Published Date, and End Date -->
    <div class="col-sm-6">
        <div class="mb-3">
            <label class="form-label">Tag</label>
            <input type="text" class="form-control @error('tag') is-invalid @enderror" placeholder="Enter tag" name="tag" value="{{ old('tag', $scholarship->tag) }}">
            @error('tag')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

 


        <div class="col-sm-4">
              <div class="mb-3">
                  <label class="form-label">Published Date</label>
                         <input type="text" class="form-control @error('published_date') is-invalid @enderror" name="published_date" id="published_date" value="{{ old('published_date', $scholarship->published_date) }}">
                  @error('published_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
              </div>
          </div>

            <div class="col-sm-4">
                <div class="mb-3">
                    <label class="form-label">End Date</label>
                    <input type="text" class="form-control @error('end_date') is-invalid @enderror" name "end_date" id="end_date" value="{{ old('end_date', $scholarship->end_date) }}">
                    @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                   
                </div>
            </div><!-- Col -->
          </div><!-- Row -->


          <!-- Short Description -->
          <div class="col-sm-6">
            <div class="mb-3">
              <label class="form-label">Short Description</label>
              <input type="text" class="form-control @error('short_desc') is-invalid @enderror" placeholder="Enter short description" name="short_desc" value="{{ old('short_desc', $scholarship->short_desc) }}">
              @error('short_desc')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- Col -->

          <!-- Scholarship Information -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">SCHOLARSHIP</label>
              <textarea class="form-control @error('scholarship_info') is-invalid @enderror" rows="5" placeholder="Enter your scholarship information" name="scholarship_info">{{ old('scholarship_info', $scholarship->scholarship_info) }}</textarea>
              @error('scholarship_info')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- Col -->

          <!-- ABOUT THE SPONSOR -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">ABOUT THE SPONSOR</label>
              <textarea class="form-control @error('sponsor_info') is-invalid @enderror" rows="5" placeholder="Enter information about the sponsor" name="sponsor_info">{{ old('sponsor_info', $scholarship->sponsor_info) }}</textarea>
              @error('sponsor_info')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- Col -->

          <!-- WHO CAN APPLY? -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">WHO CAN APPLY?</label>
              <textarea class="form-control @error('who_can_apply_info') is-invalid @enderror" rows="5" placeholder="Enter information about who can apply" name="who_can_apply_info">{{ old('who_can_apply_info', $scholarship->who_can_apply_info) }}</textarea>
              @error('who_can_apply_info')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- Col -->

          <!-- HOW CAN YOU APPLY? -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">HOW CAN YOU APPLY?</label>
              <textarea class="form-control @error('how_to_apply_info') is-invalid @enderror" rows="5" placeholder="Enter information about how to apply" name="how_to_apply_info">{{ old('how_to_apply_info', $scholarship->how_to_apply_info) }}</textarea>
              @error('how_to_apply_info')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- Col -->

          <!-- FAQ'S -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">FAQ'S</label>
              <textarea class="form-control @error('faqs') is-invalid @enderror" rows="5" placeholder="Enter frequently asked questions" name="faqs">{{ old('faqs', $scholarship->faqs) }}</textarea>
              @error('faqs')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- Col -->

          <!-- CONTACT DETAILS -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">CONTACT DETAILS</label>
              <textarea class="form-control @error('contact_details') is-invalid @enderror" rows="5" placeholder="Enter contact details" name="contact_details">{{ old('contact_details', $scholarship->contact_details) }}</textarea>
              @error('contact_details')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- Col -->

          <button type="submit" class="btn btn-primary submit">Update Scholarship</button>
        </form>
      </div>
    </div>
  </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
<script>
    flatpickr('#published_date', {
        enableTime: false, // Set to true if you want to include time in the picker
        dateFormat: "Y-m-d", // Define your desired date format
    });
</script>
<script>
    flatpickr('#end_date', {
        enableTime: false, // Set to true if you want to include time in the picker
        dateFormat: "Y-m-d", // Define your desired date format
    });
</script>
          
@endsection
