@extends('admin.layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Banner</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Banner</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Banner Form</h6>
                    <form method="POST" action="{{ route('admin.home.banner.store') }}" enctype="multipart/form-data">

                        @csrf

                        <!-- Blog Title -->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Banner Link</label>
                                    <input type="text" class="form-control @error('banner_link') is-invalid @enderror"
                                        placeholder="Enter Banner Link" name="banner_title" value="{{ old('banner_title') }}">
                                    @error('banner_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col -->

                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Banner Image</label>
                                    <input type="file" class="form-control @error('banner_image') is-invalid @enderror"
                                        name="banner_image" accept="image/*">
                                    @error('banner_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col -->


                            {{-- <div class="row">
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Blog Content</label>
                <textarea id="summernote" name="blog_content"></textarea>
                @error('blog_content')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
          </div><!-- Row --> --}}

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary submit">Submit form</button>
                            </div>
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
