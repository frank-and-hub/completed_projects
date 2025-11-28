@extends('admin.layout.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Terms & Conditions</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Blog</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Bldsog Form</h6>
                    <form method="POST" action="{{ route('admin.term.store') }}" enctype="multipart/form-data">

                        @csrf
                       

                        <!-- Blog Title -->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="col-sm-6 mb-3"> <!-- Adjust column widths as needed -->
                                    <label class="form-label">Scholarships</label>
                                    <select name="scholarship_name" class="form-control select2">
                                        <option value="">Select Scholarship</option>
                                        @foreach ($microsite as $value)
                                            <option value="{{ $value->id }}"
                                                >{{ $value->company_name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>



                            </div><!-- Col -->
                            <!-- Blog Content -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="mb-3">
                                        <label class="form-label">Content</label>
                                        <textarea id="summernote" name="desc"></textarea>
                                        @error('desc')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div><!-- Col -->
                            </div><!-- Row -->


                            <div class="col-sm-6">
                              

                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary submit">Update Blog</button>
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
