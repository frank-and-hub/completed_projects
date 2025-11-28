@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Company Management</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ isset($companyDetailTable) ? 'Edit' : 'Create' }} Company</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title"><i class="fas fa-building"></i> Company {{ isset($companyDetailTable) ? 'Edit' : '' }} Form</h6>
        <form method="POST" action="{{ isset($companyDetailTable) ? route('companyDetail.update', $companyDetailTable->id) : route('companyDetail.store') }}" enctype="multipart/form-data">

          @csrf
          @if(isset($companyDetailTable))
              @method('PUT') <!-- Use PUT method for updating -->
          @else
              @method('POST')
          @endif

          <div class="row">
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Company Name</label>
                <input type="text" class="form-control @error('company_name') is-invalid @enderror" placeholder="Enter Company Name" name="company_name" value="{{ isset($companyDetail) ? old('company_name', $companyDetail->company_name) : '' }}" readonly autocomplete="off"/>
                @error('company_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <input type="hidden" value="{{ empty($companyDetail) ? '' : $companyDetail->id }}" name="id" />
            <input type="hidden" value="{{ empty($companyDetailTable) ? '' : $companyDetailTable->id }}" name="companyDetailTableid" />
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Company Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Enter Company Email" value="{{ isset($companyDetail) ? old('email', $companyDetail->email) : '' }}" readonly autocomplete="off"/>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6">
    <div class="mb-3">
        <label class="form-label">Company Banner</label>
        <input type="file" class="form-control @error('banner') is-invalid @enderror" name="banner" required/>
        @error('banner')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if(isset($companyDetailTable) && $companyDetailTable->banner)
            <img src="{{ asset($companyDetailTable->banner) }}" alt="Company Banner" style="max-width: 100px; max-height: 100px; margin-top: 10px;">
        @endif
    </div>
</div>

<div class="col-sm-6">
    <div class="mb-3">
        <label class="form-label">Company Logo</label>
        <input type="file" class="form-control @error('logo') is-invalid @enderror" name="logo" required/>
        @error('logo')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if(isset($companyDetailTable) && $companyDetailTable->logo)
            <img src="{{ asset($companyDetailTable->logo) }}" alt="Company Logo" style="max-width: 100px; max-height: 100px; margin-top: 10px;">
        @endif
    </div>
</div>

<div class="col-sm-6">
    <div class="mb-3">
        <label class="form-label">About Image 1</label>
        <input type="file" class="form-control @error('about_image1') is-invalid @enderror" name="about_image1" required/>
        @error('about_image1')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if(isset($companyDetailTable) && $companyDetailTable->about_image1)
            <img src="{{ asset($companyDetailTable->about_image1) }}" alt="About Image 1" style="max-width: 100px; max-height: 100px; margin-top: 10px;">
        @endif
    </div>
</div>

            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">About Image 2</label>
                <input type="file" class="form-control @error('about_image2') is-invalid @enderror" name="about_image2" value="{{ isset($companyDetailTable) ? old('about_image2', $companyDetailTable->about_image2) : '' }}" required/>
                @error('about_image2')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if(isset($companyDetailTable) && $companyDetailTable->about_image2)
                  <img src="{{ asset($companyDetailTable->about_image2) }}" alt="About Image 2" style="max-width: 100px; max-height: 100px; margin-top: 10px;">
                @endif
              </div>
            </div>

            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">About Image Title 1</label>
                <input type="text" name="about_image_title1" id="about_image_title1" class="form-control" value="{{ isset($companyDetailTable) ? old('about_image_title1', $companyDetailTable->about_image_title1) : '' }}">
                @error('about_image_title1')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">About Image Title 2</label>
                <input type="text" name="about_image_title2" id="about_image_title2" class="form-control" value="{{ isset($companyDetailTable) ? old('about_image_title2', $companyDetailTable->about_image_title2) : '' }}">
                @error('about_image_title2')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="col-sm-6">
            <div class="mb-3">
              <label class="form-label">About Title</label>
              <input type="text" name="about_title" id="about_title" class="form-control" value="{{ isset($companyDetailTable) ? old('about_title', $companyDetailTable->about_title) : '' }}">
              @error('about_title')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">About Company</label>
              <textarea name="about_company" id="about_company" class="form-control">{{ isset($companyDetailTable) ? old('about_company', $companyDetailTable->about_company) : '' }}</textarea>
              @error('about_company')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-sm-6">
            <div class="mb-3">
              <label class="form-label">About Listing 1</label>
              <input type="text" name="about_listing1" id="about_listing1" class="form-control" value="{{ isset($companyDetailTable) ? old('about_listing1', $companyDetailTable->about_listing1) : '' }}">
              @error('about_listing1')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-sm-6">
            <div class="mb-3">
              <label class="form-label">About Listing 2</label>
              <input type="text" name="about_listing2" id="about_listing2" class="form-control" value="{{ isset($companyDetailTable) ? old('about_listing2', $companyDetailTable->about_listing2) : '' }}">
              @error('about_listing2')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-sm-6">
            <div class="mb-3">
              <label class="form-label">circle text 1</label>
              <input type="text" name="circle_listing1" id="about_listing2" class="form-control" value="{{ isset($companyDetailTable) ? old('circle_listing1', $companyDetailTable->circle_listing1) : '' }}">
              @error('about_listing2')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>


          <div class="col-sm-6">
            <div class="mb-3">
              <label class="form-label">circle text 2</label>
              <input type="text" name="circle_listing2" id="circle_listing2" class="form-control" value="{{ isset($companyDetailTable) ? old('circle_listing2', $companyDetailTable->circle_listing2) : '' }}">
              @error('about_listing2')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-sm-6">
            <div class="mb-3">
              <label class="form-label">Small Title</label>
              <input type="text" name="samall_title" id="samall_title" class="form-control" value="{{ isset($companyDetailTable) ? old('samall_title', $companyDetailTable->samall_title) : '' }}">
              @error('about_listing2')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="col-sm-6">
            <div class="mb-3">
              <label class="form-label">Main title</label>
              <input type="text" name="main_title" id="main_title" class="form-control" value="{{ isset($companyDetailTable) ? old('main_title', $companyDetailTable->main_title) : '' }}">
              @error('about_listing2')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

  


          <div class="row">
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Page Content</label>
                <textarea id="summernote" name="descs">{{ isset($companyDetailTable) ? old('descs', $companyDetailTable->desc) : '' }}</textarea>
                @error('descs')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">{{ isset($companyDetailTable) ? 'Update' : 'Save' }}</button>

        </form>
      </div>
    </div>
  </div>
</div>

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
