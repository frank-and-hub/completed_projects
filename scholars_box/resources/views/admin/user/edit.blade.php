@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">User Management</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ isset($user) ? 'Edit' : 'Create' }} User</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">User {{isset($user) ? 'Edit' : ''}} Form</h6>
        <form method="POST" action="{{isset($user) ? route('user.update', $user->id) : route('user.store')}}" enctype="multipart/form-data">

          @csrf
          @method('POST') <!-- Use PUT method for updating -->

          <!-- FAQ Title -->
          <div class="row">
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">User First Name</label>
                <input type="text" class="form-control @error('first_name') is-invalid @enderror" placeholder="Enter First Name" name="first_name" value="{{ isset($user) ? old('name', $user->first_name) : ''}}" required autocomplete="off"/>
                @error('first_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
            <input type="hidden" value="{{empty($user) ? '' : $user->id}}" name="id" />
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">User Last Name</label>
                <input type="text" class="form-control @error('last_name') is-invalid @enderror" placeholder="Enter Last Name" name="last_name" value="{{ isset($user) ? old('name', $user->last_name) : ''}}" required autocomplete="off"/>
                @error('last_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <!-- Col -->
        </div><!-- Col -->
        <div class="row">
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">user Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Enter User Email"  value="{{isset($user) ? old('email', $user->email) : '' }}" required autocomplete="off"/>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
        </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter User Password"  value="" required/>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="number"  class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" placeholder="Enter User Phone Number"  value="{{isset($user) ? old('phone_number', $user->phone_number) : '' }}" required autocomplete="off" required/>
                @error('phone_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <!-- Col -->
        </div>
         <div class="row">
             <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" placeholder="Verify User Password"  value="" required/>
                @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            {{--
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">User Profile Image</label>
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
                <label class="form-label">Profile Image Preview</label><br>
                <center>
                    <img src="{{ ($user == null)  ? asset('images/logo.png') : asset($user->avatar) }}" id="avatar-preview" class="center w-100" alt="Profile Image"/>
                    <input type="hidden" name="avatar_hidden" id="" class="" value="{{isset($user) ? old('avatar', $user->avatar) : '' }}" />
                </center>
              </div>
            </div>
          </div>
          --}}
        @if($title !== 'show')
          <div class="mb-3">
            <button type="submit" class="btn btn-primary submit">{{ ($user == null) ? 'Create' : 'Update' }} User</button>
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
