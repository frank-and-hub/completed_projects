@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Scholarship</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Scholarship</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Scholarship Form</h6>
        <form method="POST" action="{{ route('admin.scholarship.save') }}"  enctype="multipart/form-data">
          @csrf

          <!-- Company Name -->
          <div class="row">
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Company Name</label>
                <select class="form-control @error('company_id') is-invalid @enderror" name="company_id" >
                    <option value="">Select Company</option>
                    @forelse($company as $k => $v)
                    @if(auth()->user()->role_id == 3)
                    <option value="{{$k}}" {{ $k == auth()->user()->id ? 'selected' : 'disabled' }} > {{$v}}</option>
                    @else
                    <option value="{{$k}}" {{ $k == old('company_id') ? 'selected' : '' }} > {{$v}}</option>
                    @endif
                    @empty
                    @endforelse
                </select>
                @error('company_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->

            <div class="col-sm-6">
              <div class="mb-3">
              <label class="form-label">Scholarship Type</label>
                      <select name="looking_for" class="form-control" autocomplete="off">
                          <option value="">Please Select</option>
                          <option value="School Scholarships">School Scholarships</option>
                          <option value="Bachelors Scholarships">Bachelors Scholarships</option>
                          <option value="Master Scholarships">Master Scholarships</option>
                          <option value="PhD. Scholarships">PhD. Scholarships</option>
                          <option value="ITIs/Diploma/Polytechnic/Certificate Scholarships">ITIs/Diploma/Polytechnic/Certificate Scholarships
                          </option>
                          <option value="Competitive Exams Scholarships">Competitive Exams Scholarships</option>
                          <option value="Exchange program scholarships">Exchange program scholarships</option>
                      </select>
                  </div>
</div>

            <!-- Scholarship Name -->
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Scholarship Name</label>
                <input type="text" class="form-control @error('scholarship_name') is-invalid @enderror" placeholder="Enter scholarship name" name="scholarship_name" value="{{ old('scholarship_name') }}">
                @error('scholarship_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->

          <!-- Tag --> 
          <?php 
          $tag = \DB::table('tags')->select('slug')->groupBy('slug')->pluck('slug');
$tagName = \DB::table('tags')->pluck('slug', 'name')->toArray();
          ?>
                        <div class="col-sm-6">
                          <div class="mb-3">
                            <label class="form-label">Tag</label>
                            <select name="tag[]" id="tag" multiple class="form-control @error('tag') is-invalid @enderror">
                                    @foreach($tag as $k => $v)
                                    <optgroup label="{{$v}}">
                                         @foreach($tagName as $key => $value)
                                            @if($value === $v)
                                                <option value="{{$key}}" {{ (old('tag') == $key ? 'selected' : '')}} >{{($key)}}</option>
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
                              <label class="form-label">Published Date</label>
                              <input type="text" class="form-control @error('published_date') is-invalid @enderror" name="published_date" id="published_date" value="{{ old('published_date') }}">
                              @error('published_date')
                              <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                          </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label class="form-label">Short Description</label>
                                <input type="text" class="form-control @error('short_desc') is-invalid @enderror" placeholder="Enter short description" name="short_desc" value="{{ old('short_desc') }}">
                                @error('short_desc')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                          <div class="mb-3">
                            <label class="form-label">Scholarship Image</label>
                            <input type="file" class="form-control @error('avatar') is-invalid @enderror" name="avatar" id="avatar" placeholder="Select Image for Scholarship" requried>
                            @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                          
                      </div>

                    <div class="col-sm-6">
                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="text" class="form-control @error('end_date') is-invalid @enderror" name="end_date" id="end_date" value="{{ old('end_date') }}">
                            @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror 
                        </div>
                    </div>
                    <div class="container mb-3">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                      <label class="form-check-label" for="is_featured" >
                          Featured
                      </label>
                  </div>
                </div>
              <div class="container mb-3">
                  <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="is_scholarsip" id="is_scholarsip">
                      <label class="form-check-label" for="is_scholarsip">
                          Is Scholarbox Schorarship ?
                      </label>
                  </div>
              </div>
                    <div class="col-sm-6">
                        <div class="mb-3">
                            <label class="form-label">Education Requirement</label>
                            <select  class="form-control @error('education_req') is-invalid @enderror" multiple name="education_req[]" id="education_req" autocomplete="off">
                                <option value="">Please select</option>
                                @foreach (\App\Models\EducationDetail::DEGREES as $key => $value)
                                    <option value="{{ $key }}"
                                        {{ old('education_req') == $key ? 'selected' : ''}} >
                                        {{ ucwords($value) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('education_req')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                     <div class="mb-12">
                          <label class="form-label">APPLICATION PROCESS STEPS <span style="color: red">(Please Add values Coma(,) saprated. Eg: Test, test1, new test )</span></label>
                          <textarea  class="form-control" name="application_processs" id="application_processs">{{ old('application_processs') }}</textarea>
                          @error('application_processs')
                          <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                      </div>
                    <div class="col-sm-6">
                        <div class="mb-3">
                            <label class="form-label">Scholarship Image Preview</label>
                            <img src="asset('images/logo.png')}}" id="avatar-preview" class="center w-100" alt="Scholarship Image"/>
                            <input type="hidden" name="avatar_hidden" id="" class="" value="" />
                        </div>
                    </div>
          </div>

          <!-- Short Description -->
          <div class="row">
            <div class="col-sm-6"> 
              
            </div><!-- Col -->
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Scholarship Minimum Age</label>
                <select class="form-control @error('min_age') is-invalid @enderror" rows="5" name="min_age" required >
                    <option value="">Select Minimum Age</option>
                    @for($l = 10; $l <= 60; $l++)
                    <option value="{{$l}}" {{(old('min_age') == $l) ? 'selected' : ''}} class="">{{$l}}</option>    
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
                <textarea class="form-control @error('scholarship_info') is-invalid @enderror" rows="5" placeholder="Enter your scholarship information" name="scholarship_info">{{ old('scholarship_info') }}</textarea>
                @error('scholarship_info')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
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
            <!-- ABOUT THE SPONSOR -->
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">ABOUT THE SPONSOR</label>
                <textarea class="form-control @error('sponsor_info') is-invalid @enderror" rows="5" placeholder="Enter information about the sponsor" name="sponsor_info">{{ old('sponsor_info') }}</textarea>
                @error('sponsor_info')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->

            <!-- WHO CAN APPLY? -->
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">WHO CAN APPLY?</label>
                <textarea class="form-control @error('who_can_apply_info') is-invalid @enderror" id="summernote" rows="5" placeholder="Enter information about who can apply" name="who_can_apply_info">{{ old('who_can_apply_info') }}</textarea>
                <!--<textarea class="form-control @error('how_to_apply_info') is-invalid @enderror" id="summernote" name="how_to_apply_info"></textarea>-->
                @error('who_can_apply_info')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->

            <!-- HOW CAN YOU APPLY? -->
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">HOW CAN YOU APPLY?</label>
                <!--<textarea class="form-control @error('how_to_apply_info') is-invalid @enderror" rows="5" placeholder="Enter information about how to apply" name="how_to_apply_info">{{ old('how_to_apply_info') }}</textarea>-->
              <textarea class="form-control @error('how_to_apply_info') is-invalid @enderror" id="summernote" name="how_to_apply_info"></textarea>
                @error('how_to_apply_info')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->

            <!-- FAQ'S -->
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">FAQ'S</label>
                <textarea class="form-control @error('faqs') is-invalid @enderror"  id="summernote" rows="5" placeholder="Enter frequently asked questions" name="faqs">{{ old('faqs') }}</textarea>
                @error('faqs')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
          <!-- Scholarship Name -->
          <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Scholarship link</label>
                <input type="text" class="form-control @error('scholarship_link') is-invalid @enderror" placeholder="Enter scholarship Link" name="scholarship_link" value="{{ old('scholarship_link') }}">
                @error('scholarship_link')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
             <!-- CONTACT DETAILS -->
      <div class="col-sm-12">
        <div class="mb-3">
            <label class="form-label">CONTACT DETAILS</label>
            <div id="contactDetailsContainer">
                <!-- Initial set of fields -->
                <div class="row contact-details">
                    <div class="col-sm-4">
                        <input type="text" class="form-control mb-2" placeholder="Enter name" name="contact_names[]">
                    </div>
                    <div class="col-sm-4">
                        <input type="email" class="form-control mb-2" placeholder="Enter email" name="contact_emails[]">
                    </div>
                    <div class="col-sm-4">
                        <input type="text" class="form-control mb-2" placeholder="Enter phone number" name="contact_phones[]">
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-contact col-sm-1">Remove</button>
                </div>
            </div>
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

          <button type="submit" class="btn btn-primary submit">Submit form</button>
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
      let contactCounter = 1;

      // Add more button click event
      $('#addMore').click(function() {
          contactCounter++;

          // Clone the original set of fields and update IDs
          let clonedFields = $('.contact-details:first').clone();
          clonedFields.find('input').each(function() {
              let newName = $(this).attr('name').replace('[]', '[' + contactCounter + ']');
              $(this).attr('name', newName);
              $(this).val(''); // Clear values in cloned fields
          });

          // Append the cloned fields to the container
          $('#contactDetailsContainer').append(clonedFields);
      });

      // Remove button click event (event delegation for dynamically added fields)
      $('#contactDetailsContainer').on('click', '.remove-contact', function() {
          $(this).closest('.contact-details').remove();
      });
  });
</script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

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
