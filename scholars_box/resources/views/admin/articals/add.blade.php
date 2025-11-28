@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Artical</a></li>
        <li class="breadcrumb-item active" aria-current="page">Add Artical</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12 stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Artical Form</h6>
                <form method="POST" action="{{ route('admin.blog.save') }}" enctype="multipart/form-data">

                    @csrf

                    <!-- Blog Title -->
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label class="form-label">Artical Title</label>
                                <input type="text" class="form-control @error('artical_title') is-invalid @enderror"
                                    placeholder="Enter artical title" name="artical_title"
                                    value="{{ old('artical_title') }}">
                                @error('artical_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div><!-- Col -->

                        <!-- artical Title -->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Author Name</label>
                                    <input type="text" class="form-control @error('artical_title') is-invalid @enderror"
                                        placeholder="Enter artical title" name="artical_title"
                                        value="{{ old('artical_title') }}">
                                    @error('artical_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col -->

                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Artical Image</label>
                                    <input type="file" class="form-control @error('artical_image') is-invalid @enderror"
                                        name="artical_image" accept="image/*">
                                    @error('artical_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col -->


                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="mb-3">
                                        <label class="form-label">Artical Content</label>
                                        <textarea id="summernote" name="artical_content"></textarea>
                                        @error('artical_content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div><!-- Col -->
                            </div><!-- Row -->
                            <div class="mb-12">
                                <label class="form-label">Tags <span style="color: red">(Please Add values Coma(,)
                                        saprated. Eg: Test, test1, new test )</span></label>
                                <textarea class="form-control" name="tags" id="tags">{{ old('tags') }}</textarea>
                                @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Artical Tag</label>
                                    <input type="text" class="form-control @error('artical_tag') is-invalid @enderror"
                                        placeholder="Enter artical title" name="artical_tag"
                                        value="{{ old('artical_tag') }}">
                                    @error('artical_tag')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col -->
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