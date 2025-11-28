@extends('admin.layout.master')

@section('content')
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">MicroSite</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit MicroSite</li>
        </ol>
    </nav>
    <form action="{{ route('admin.microsite.updassssste',$ids) }}" enctype="multipart/form-data" method="POST">
        @csrf

        @method('PUT')
        <div class="row">
            <!-- Logo Section -->
            <div class="col-md-12 stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Logo Section</h6>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Logo *</label>
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                    
                                    @if($microsite->logo)
                                        <img src="{{ asset('/uploads/'.$microsite->logo) }}" style="width: 100px; height:100px;">
                                    @endif
                                </div>
                                @error('logo')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Select company *</label>
                                    
                                    <select class="form-control" name="compmny">
                                        <option>Select Company</option>
                                        @foreach ($companies as $value)
                                        <option value="{{ $value->id }}" @if($value->id == $microsite->company_id) selected @endif>{{ ucwords($value->company_name) }}</option>
                                    @endforeach
                                    
                                    </select>
                                </div>
                                @error('compmny')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div><!-- Row -->
                    </div>
                    <input type="hidden" name="id" value="{{ $microsite->id }}">
                    <hr>
                    <!-- Banner Section -->
                    <div class="card-body">
                        <h6 class="card-title">Banner Section</h6>
                        <div class="row">
                            <!-- Banner Image -->
                            <div class="col-md-12 stretch-card mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Banner *</h6>
                                        <input type="file" id="myDropify" name="banner">
                                    </div>
                                    <img src="{{ asset('/uploads/'.$microsite->banner) }}" style="width: 100px; height:100px;">

                                    @error('banner')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>
                            <!-- Banner Title -->
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">About Title *</label>
                                    
                                    <input type="text" class="form-control" name="banner_title" value="{{ $microsite->banner_titile }}" placeholder="Enter banner title">
                                 
                                    @error('banner_title')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <!-- Banner Link -->
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Banner Link *</label>
                                    <input type="text" class="form-control" name="banner_link"  value="{{ $microsite->banner_link }}" placeholder="Enter banner link">
                                    @error('banner_link')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div><!-- Row -->
                    </div>
                    <hr>
                    <!-- About Section -->
                    <div class="card-body">
                        <h6 class="card-title">About Section *</h6>
                        <div class="row">
                            <!-- About Title -->
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">About Title *</label>
                                    <input type="text" class="form-control" name="about_title" value="{{ $microsite->about_title }}" placeholder="Enter about title">
                                    @error('about_title')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <!-- About Video -->
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">About Video *</label>
                                    <input type="file" name="video" class="form-control"  accept="video/*">
                                    <video width="320" height="240" controls>
                                        <source src="{{asset("/uploads/".$microsite->video)}}" type="video/mp4">
                                      
                                  </video>
                                    @error('video')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <!-- About Content -->
                            <div class="col-sm-12">
                                <div class="mb-3">
                                    <label class="form-label">About Content *</label>
                                    <textarea class="form-control @error('about_description') is-invalid @enderror" id="summernote" name="about_description">{{ strip_tags(htmlspecialchars_decode($microsite->about_description )) }}</textarea>
                                    @error('about_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div><!-- Row -->
                    </div>
                    <hr>
                    <!-- Detail Section -->
                    <div class="card-body">
                        <h6 class="card-title">Detail Section</h6>
                        <div class="row">
                            <!-- Detail Content -->
                            <div class="col-sm-12">
                                <div class="mb-3">
                                    <label class="form-label">Detail Content *</label>
                                    <textarea class="form-control @error('detail_description') is-invalid @enderror" id="summernote" name="detail_description">{{ strip_tags(htmlspecialchars_decode($microsite->detail_description)) }}</textarea>
                                    @error('detail_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div><!-- Row -->
                        <button type="submit" class="btn btn-primary submit">Submit form</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
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
