@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Scholarship</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Scholarship</li>
  </ol>
</nav>
<?php 
$tag = \DB::table('tags')->select('slug')->groupBy('slug')->pluck('slug');
$tagName = \DB::table('tags')->pluck('slug','name')->toArray();
?>
<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Scholarship Form - Edit</h6>
        <form method="POST" action="{{ route('admin.scholarship.update', $scholarship->id) }}"  enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
    <!-- Company Name and Scholarship Name -->
    <div class="col-sm-6">
        <div class="mb-3">
            <label class="form-label">Company Name</label>
            <select class="form-control @error('company_id') is-invalid @enderror" name="company_id" required>
                <option value="0">Select Company</option>
                @forelse($company as $k => $v)
                    <option value="{{$k}}" {{ $k == old('company_id',$scholarship->company_id) ? 'selected' : ((auth()->user()->role_id == 3) ? 'disabled' : '') }} > {{$v}}</option>
                @empty
                @endforelse
            </select>
            @error('company_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-sm-6">
    <div class="mb-3">
        <label class="form-label">Scholarship Type</label>
        <select name="looking_for" class="form-control" autocomplete="off">
            <option value="" <?php echo ($scholarship->contact_details == '' ? 'selected' : ''); ?>>Please Select</option>
            <option value="School Scholarships" <?php echo ($scholarship->contact_details == 'School Scholarships' ? 'selected' : ''); ?>>School Scholarships</option>
            <option value="Bachelors Scholarships" <?php echo ($scholarship->contact_details == 'Bachelors Scholarships' ? 'selected' : ''); ?>>Bachelors Scholarships</option>
            <option value="Master Scholarships" <?php echo ($scholarship->contact_details == 'Master Scholarships' ? 'selected' : ''); ?>>Master Scholarships</option>
            <option value="PhD. Scholarships" <?php echo ($scholarship->contact_details == 'PhD. Scholarships' ? 'selected' : ''); ?>>PhD. Scholarships</option>
            <option value="ITIs/Diploma/Polytechnic/Certificate Scholarships" <?php echo ($scholarship->contact_details == 'ITIs/Diploma/Polytechnic/Certificate Scholarships' ? 'selected' : ''); ?>>ITIs/Diploma/Polytechnic/Certificate Scholarships</option>
            <option value="Competitive Exams Scholarships" <?php echo ($scholarship->contact_details == 'Competitive Exams Scholarships' ? 'selected' : ''); ?>>Competitive Exams Scholarships</option>
            <option value="Exchange program scholarships" <?php echo ($scholarship->contact_details == 'Exchange program scholarships' ? 'selected' : ''); ?>>Exchange program scholarships</option>
        </select>
    </div>
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
                    <select name="tag[]" id="tag" multiple class="form-control @error('tag') is-invalid @enderror">
                        @foreach($tag as $k => $v)
                        <optgroup label="{{$v}}">
                             @foreach($tagName as $key => $value)
                                @if($value === $v)
                                    <option value="{{ $key }}" 
                                            {{ (is_array(json_decode($scholarship->tag)) && in_array($key, json_decode($scholarship->tag)) ? 'selected' : '') }}>
                                        {{ $key }}
                                    </option>
                                @endif
                             @endforeach 
                        </optgroup>
                        @endforeach
                    </select>
                    
                    @error('tag')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                  <label class="form-label">Short Description</label>
                  <input type="text" class="form-control @error('short_desc') is-invalid @enderror" placeholder="Enter short description" name="short_desc" value="{{ old('short_desc', $scholarship->short_desc) }}">
                  @error('short_desc')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
            </div>
            <div class="col-sm-6">
                <div class="mb-3">
                  <label class="form-label">Published Date</label>
                         <input type="text" class="form-control @error('published_date') is-invalid @enderror" name="published_date" id="published_date" value="{{ old('published_date', date('d-m-y', strtotime($scholarship->published_date))) }}">
                  @error('published_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
 
                <div class="mb-3">
                  <label class="form-label">End Date</label>
                  <input type="text" class="form-control @error('end_date') is-invalid @enderror" name="end_date" id="end_date" value="{{ date('d-m-y', strtotime('-1 day', strtotime($scholarship->end_date))) }}">
                  @error('end_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                 
              </div>
                <div class="container mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" {{ $scholarship->is_featured ? 'checked' : '' }}>
        <label class="form-check-label" for="is_featured">
            Featured
        </label>
    </div>
</div>

<div class="container mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_scholarsip" id="is_scholarsip" {{ $scholarship->is_scholarsip ? 'checked' : '' }}>
        <label class="form-check-label" for="is_scholarsip">
            Is Scholarbox Scholarship?
        </label>
    </div>
</div>
                 <div class="col-sm-6">
                    <div class="mb-3">
                        <label class="form-label">Education Requirement</label>
                        <select  class="form-control @error('education_req') is-invalid @enderror" multiple name="education_req[]" id="education_req" autocomplete="off">
                            
                                <option value="">Please Education Requirement</option>
                            @foreach (\App\Models\EducationDetail::DEGREES as $key => $value)
                                <option value="{{ $key }}"
                                    {{ in_array($key,json_decode($scholarship->education_req)) ? 'selected' : ''}} >
                                    {{ ucwords($value) }}
                                </option>
                            @endforeach
                        </select>
                        @error('education_req')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                    
                    
                
                <div class="mb-3">
                  <label class="form-label">APPLICATION PROCESS STEPS (Please Add values Coma(,) saprated )</label>
                  <textarea  class="form-control" name="application_processs" id="application_processs">{{ old('application_processs', $scholarship->application_processs) }}</textarea>
                  @error('application_processs')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
              </div>
          </div>
          
          <div class="col-sm-6">
                <div class="mb-3">
                  <label class="form-label">Scholarship Image</label>
                <input type="file" class="form-control @error('avatar') is-invalid @enderror" name="avatar" id="avatar" placeholder="Select Image for Scholarship"  />
                  @error('avatar')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Scholarship Image Preview</label>
                    <img src="{{($scholarship == null) ? asset('images/logo.png') : asset($scholarship->avatar) }}" id="avatar-preview" class="center w-100" alt="Scholarship Image"/>
                    <input type="hidden" name="avatar_hidden" id="" class="" value="{{isset($scholarship) ? old('avatar', $scholarship->avatar) : '' }}" />
                   
                </div>
          </div>
          <script>
              function previewImage() {
                var input = document.getElementById('avatar');
                var preview = document.getElementById('avatar-preview');
    
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
    
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                    };
    
                    reader.readAsDataURL(input.files[0]);
                }
            }
            document.getElementById('avatar').addEventListener('change', previewImage);
          </script>
            
          </div><!-- Col -->
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Scholarship Minimum Age</label>
                <select class="form-control @error('min_age') is-invalid @enderror" rows="5" name="min_age" required >
                    <option value="">Please Scholarship Minimum Age</option>
                    @for($l=10;$l<=60;$l++)
                    <option value="{{$l}}" {{(old('min_age',$scholarship->min_age)==$l) ? 'selected' : ''}} class="">{{$l}}</option>    
                    @endfor
                </select>
                @error('min_age')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
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
              <textarea class="form-control @error('who_can_apply_info') is-invalid @enderror" id="summernote" rows="5" placeholder="Enter information about who can apply" name="who_can_apply_info">{{ old('who_can_apply_info', $scholarship->who_can_apply_info) }}</textarea>
            
              @error('who_can_apply_info')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- Col -->

          <!-- HOW CAN YOU APPLY? -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">HOW CAN YOU APPLY?</label>
              <textarea class="form-control @error('how_to_apply_info') is-invalid @enderror" id="summernote" rows="5" placeholder="Enter information about how to apply" name="how_to_apply_info">{{ old('how_to_apply_info', $scholarship->how_to_apply_info) }}</textarea>
              @error('how_to_apply_info')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- Col -->

          <!-- FAQ'S -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">FAQ'S</label>
              <textarea class="form-control @error('faqs') is-invalid @enderror"  id="summernote" rows="5" placeholder="Enter frequently asked questions" name="faqs">{{ old('faqs', $scholarship->faqs) }}</textarea>
              @error('faqs')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- Col -->

           <!-- CONTACT DETAILS -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">CONTACT DETAILS</label>
          
              <div id="contactDetailsContainer">
                @if(!empty($scholarshipContactDetails) && is_countable($scholarshipContactDetails) && count($scholarshipContactDetails) > 0)
                @foreach ($scholarshipContactDetails as $index => $val)
                  <div class="row contact-details">
                    <div class="col-sm-4">
                      <input type="text" class="form-control mb-2" placeholder="Enter name" name="contact_names[]" value="{{ old('contact_names.'.$index, $val->name) }}">
                    </div>
                    <div class="col-sm-4">
                      <input type="email" class="form-control mb-2" placeholder="Enter email" name="contact_emails[]" value="{{ old('contact_emails.'.$index, $val->email) }}">
                    </div>
                    <div class="col-sm-4">
                      <input type="text" class="form-control mb-2" placeholder="Enter phone number" name="contact_phones[]" value="{{ old('contact_phones.'.$index, $val->phone) }}">
                    </div>
                    <div class="col-sm-1">
                      <button type="button" class="btn btn-danger btn-sm remove-contact">Remove</button>
                    </div>
                  </div>
                @endforeach
                @else

                <div class="row contact-details">
                  <div class="col-sm-4">
                    <input type="text" class="form-control mb-2" placeholder="Enter name" name="contact_names[]" value="{{ old('contact_names') }}">
                  </div>
                  <div class="col-sm-4">
                    <input type="email" class="form-control mb-2" placeholder="Enter email" name="contact_emails[]" value="{{ old('contact_emails') }}">
                  </div>
                  <div class="col-sm-4">
                    <input type="text" class="form-control mb-2" placeholder="Enter phone number" name="contact_phones[]" value="{{ old('contact_phones') }}">
                  </div>
                  <div class="col-sm-1">
                    <button type="button" class="btn btn-danger btn-sm remove-contact">Remove</button>
                  </div>
                </div>
              </div>
              @endif
          
              <button type="button" class="btn btn-success btn-sm mt-2" id="addMore">Add More</button>
          
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
<div class="mb-3">
            <label class="form-label">Status</label>
           <select name="status" class="form-control">
               <option>Select Status</option>
               <option value='1' {{ $scholarship->status == 1 ? 'selected' : '' }}>Active</option>
               <option value='0' {{ $scholarship->status == 0 ? 'selected' : '' }}>Inactive</option>
           </select>
            @error('scholarship_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Scholarship link</label>
                <input type="text" class="form-control @error('scholarship_link') is-invalid @enderror" value="{{ old('scholarship_link', $scholarship->comany_link) }}" placeholder="Enter scholarship Link" name="scholarship_link">
                @error('scholarship_link')
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
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

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
        dateFormat: "d-m-Y", // Define your desired date format
    });
</script>
<script>
    flatpickr('#end_date', {
        enableTime: false, // Set to true if you want to include time in the picker
        dateFormat: "d-m-Y", // Define your desired date format
    });
</script>

<script>
  // Add more functionality using jQuery
  $(document).ready(function() {
    // Counter for dynamic field IDs
  let contactCounter = {{ count($scholarshipContactDetails ?? []) }};

    // Add more button click event
    $('#addMore').click(function() {
      contactCounter++;

      // Clone the original set of fields and update IDs
      let clonedFields = $('.contact-details:first').clone();
      clonedFields.find('input').each(function() {
        let newName = $(this).attr('name').replace(/\[\d+\]/, '[' + contactCounter + ']');
        $(this).attr('name', newName);
        $(this).val(''); // Clear values in cloned fields
      });

      // Remove the remove button from the cloned fields
      clonedFields.find('.remove-contact').remove();

      // Append the cloned fields to the container
      $('#contactDetailsContainer').append(clonedFields);
    });

    // Remove button click event (event delegation for dynamically added fields)
    $('#contactDetailsContainer').on('click', '.remove-contact', function() {
      $(this).closest('.contact-details').remove();
    });
  });
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
