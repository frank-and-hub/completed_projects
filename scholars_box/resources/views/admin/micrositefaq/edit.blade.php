@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">FAQ</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit FAQ</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">FAQ Edit Form</h6>
        <form method="POST" action="{{ route('admin.mfaq.update', $faq->id) }}" enctype="multipart/form-data">

          @csrf
          @method('PUT') <!-- Use PUT method for updating -->

          <!-- FAQ Title -->
          <div class="row">
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Faq Title</label>
                <input type="text" class="form-control @error('faq_title') is-invalid @enderror" placeholder="Enter Faq title" name="faq_title" value="{{ old('title', $faq->title) }}">
                @error('faq_title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
          </div><!-- Row -->

          <!-- <div class="row">
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Faq Content</label>
                <textarea class="form-control @error('faq_content') is-invalid @enderror" name="faq_content">{{ old('description', $faq->description) }}</textarea>
                @error('faq_content')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div> -->

          <div class="row">
          <div class="col-sm-12">
                                <div class="mb-3">
                                    <label class="form-label">Faq Content *</label>
                                    <textarea class="form-control @error('faq_content') is-invalid @enderror" id="summernote" name="faq_content">{{ old('description', $faq->description) }}</textarea>
                                    @error('faq_content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
          </div><!-- Row -->


          <div class="mb-3">
            <button type="submit" class="btn btn-primary submit">Update FAQ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
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
