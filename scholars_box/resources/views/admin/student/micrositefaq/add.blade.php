@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">FAQ</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add FAQ</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">FAQ Form</h6>
        <form method="POST" action="{{ route('admin.mfaq.store') }}" enctype="multipart/form-data">

          @csrf

          <!-- Blog Title -->
          <div class="row">
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Faq Title</label>
                <input type="text" class="form-control @error('faq_title') is-invalid @enderror" placeholder="Enter Faq title" name="faq_title" value="{{ old('faq_title') }}">
                @error('faq_title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->

    


          <div class="row">
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Faq Content</label>
                <textarea class="form-control @error('faq_content') is-invalid @enderror"  name="faq_content"></textarea>
                @error('faq_content')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
          </div><!-- Row -->

          <div class="mb-3">
            <button type="submit" class="btn btn-primary submit">Submit form</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


@endsection
