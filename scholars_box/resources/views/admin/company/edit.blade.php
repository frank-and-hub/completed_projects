@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Company Management</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ isset($company) ? 'Edit' : 'Create' }} Company</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Company {{isset($company) ? 'Edit' : ''}} Form ( {{ isset($company) ? old('company_name', $company->social_id) : ''}} )</h6>
        <form method="POST" action="{{isset($company) ? route('company.update', $company->id) : route('company.store')}}" enctype="multipart/form-data">

          @csrf
          @method('POST') <!-- Use PUT method for updating -->

          <!-- FAQ Title -->
          <div class="row">
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Company Name</label>
                <input type="text" class="form-control @error('company_name') is-invalid @enderror" placeholder="Enter Company Name" name="company_name" value="{{ isset($company) ? old('company_name', $company->company_name) : ''}}" required outocomplete="off"/>
                @error('company_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
            <input type="hidden" value="{{empty($company) ? '' : $company->id}}" name="id" />
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Company Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Enter Company Email"  value="{{isset($company) ? old('email', $company->email) : '' }}" required outocomplete="off"/>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
        </div><!-- Col -->

        <div class="row">
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="text" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter Company Password"  value="" required/>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="number" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" placeholder="Enter Company Phone Number"  value="{{isset($company) ? old('phone_number', $company->phone_number) : '' }}" required autocomplete="off" required/>
                @error('phone_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
        </div>
        <div class="row">
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" placeholder="Verify Company Password"  value="" required/>
                @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            {{--<div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Company Profile Image</label>
                <input type="file" class="form-control @error('avatar') is-invalid @enderror" name="avatar" id="avatar" placeholder="Select Image for Profile"   required/>
                @error('avatar')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>--}}
        </div>
        {{--
        <div class="row">
            <div class="col-sm-6">
            </div>
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Company Profile Image Preview</label><br>
                <center>
                    <img src="{{ ($company == null)  ? asset('images/logo.png') : asset($company->avatar) }}" id="avatar-preview" class="center w-100" alt="Profile Image"/>
                    <input type="hidden" name="avatar_hidden" id="" class="" value="{{isset($company) ? old('avatar', $company->avatar) : '' }}" />
                </center>
              </div>
            </div><!-- Col -->
        </div>
        --}}<!-- Col -->
          
        @if($title !== 'show')
          <div class="mb-3">
            <button type="submit" class="btn btn-primary submit">{{ ($company == null) ? 'Create' : 'Update' }} Company</button>
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
          @else
          <script>
              // Get all input elements
                const inputElements = document.querySelectorAll('input');
                
                // Disable all input elements
                inputElements.forEach((input) => {
                  input.disabled = true;
                });
          </script>
          @endif
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
