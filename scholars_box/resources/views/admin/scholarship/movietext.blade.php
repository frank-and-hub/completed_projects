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
        <form method="POST" action="{{ route('admin.scholarship.moving.text') }}"  enctype="multipart/form-data">
          @csrf

          <!-- Company Name -->
          <div class="row">
           

            <!-- Scholarship Name -->
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Scholarship Name</label>
                <input type="text" class="form-control" placeholder="Enter Moving name" name="texts" value="{{ $gettext->texts }}">
                @error('scholarship_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->

         
              <div class="col-sm-6">
                
           <button type="submit" class="btn btn-primary submit">Save</button>   
           </div>
          </div>
    </form>
         
         
    </div>
  </div>
</div>

            

@endsection
