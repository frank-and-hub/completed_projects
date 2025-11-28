@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Page</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit About Us</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">About Us Form</h6>
        <form method="POST" action="{{route('admin.about-us.store')}}" enctype="multipart/form-data">

          @csrf
          @method('PUT') <!-- Use PUT method for updating -->

          <div class="row">
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">About Title</label>
                <input type="text" class="form-control @error('session_title') is-invalid @enderror" placeholder="Enter first session title" name="session_title" value="{{ old('session_title', $about->session_title) }}">
                @error('session_title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-sm-6">
                <div class="mb-3">
                    <label class="form-label">About first Session Image</label>
                    <input type="hidden" class="form-control" name="hidden_session_image" value="{{$about->session_image??''}}" accept="image/*">
                    @if ($about->session_image)
                        <img src="{{ asset($about->session_image) }}" alt="About Us First session Image" class="mb-2" style="max-width: 200px;">
                    @endif
                    <input type="file" class="form-control @error('session_image') is-invalid @enderror" name="session_image" accept="image/*">
                    @error('session_image')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div><!-- Col -->
            <!-- Blog Content -->
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">About Content</label>
                <textarea id="summernote" name="session_description">{{ old('session_description', $about->session_description) }}</textarea>
                @error('session_description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">About Title Second</label>
                <input type="text" class="form-control @error('session_title_second') is-invalid @enderror" placeholder="Enter first session title second" name="session_title_second" value="{{ old('session_title_second', $about->session_title_second) }}">
                @error('session_title_second')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-sm-6">
                <div class="mb-3">
                    <label class="form-label">About first Session Image</label>
                    <input type="hidden" class="form-control" name="hidden_session_image_second" value="{{$about->session_image_second??''}}" accept="image/*">
                    @if ($about->session_image_second)
                        <img src="{{ asset($about->session_image_second) }}" alt="About Us First session Image second" class="mb-2" style="max-width: 200px;">
                    @endif
                    <input type="file" class="form-control @error('session_image_second') is-invalid @enderror" name="session_image_second" accept="image/*">
                    @error('session_image_second')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div><!-- Col -->
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">About Content</label>
                <textarea id="summernote2" name="session_description_second">{{ old('session_description_second', $about->session_description_second) }}</textarea>
                @error('session_description_second')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div>How It Works ?</div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label class="form-label">Title first</label>
                    <input type="text" class="form-control @error('title_1') is-invalid @enderror" placeholder="Enter first title " name="title_1" value="{{ old('title_1', $about->title_1) }}">
                    @error('title_1')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-sm-12">
                  <div class="mb-3">
                    <label class="form-label">Description first</label>
                    <textarea id="summernote" name="description_1">{{ old('description_1', $about->description_1) }}</textarea>
                    @error('description_1')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label class="form-label">Title Second</label>
                    <input type="text" class="form-control @error('title_2') is-invalid @enderror" placeholder="Enter second title " name="title_2" value="{{ old('title_2', $about->title_2) }}">
                    @error('title_2')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-sm-12">
                  <div class="mb-3">
                    <label class="form-label">Description second</label>
                    <textarea id="summernote2" name="description_2">{{ old('description_2', $about->description_2) }}</textarea>
                    @error('description_2')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label class="form-label">About Title Second</label>
                    <input type="text" class="form-control @error('title_3') is-invalid @enderror" placeholder="Enter third title" name="title_3" value="{{ old('title_3', $about->title_3) }}">
                    @error('title_3')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-sm-12">
                  <div class="mb-3">
                    <label class="form-label">Description third</label>
                    <textarea id="summernote3" name="description_3">{{ old('description_3', $about->description_3) }}</textarea>
                    @error('description_3')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                
          
          <div class="mb-3">
            <button type="submit" class="btn btn-primary submit">Update About</button>
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
   $('textarea#summernote2').summernote({
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
   $('textarea#summernote3').summernote({
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