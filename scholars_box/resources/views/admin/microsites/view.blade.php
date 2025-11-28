@extends('admin.layout.master')

@section('content')
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">MicroSite</a></li>
            <li class="breadcrumb-item active" aria-current="page">View MicroSite</li>
        </ol>
    </nav>
    <form action="{{ route('admin.microsite.store') }}" enctype="multipart/form-data" method="POST">
        @csrf
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
                                    
                                    <img src="{{ asset('/uploads/'.$microsite->logo) }}" style="width: 100px; height:100px;">
                                </div>
                                @error('logo')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">company *</label>
                                    <input type="text"  class="form-control" value="{{$microsite->company_name}}" readonly>
                                    
                                    
                                </div>
                                
                            </div>
                        </div><!-- Row -->
                    </div>
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
                                        {{-- <input type="file" id="myDropify" name="banner"> --}}
                                    <img src="{{ asset('/uploads/'.$microsite->banner) }}" style="width: 100px; height:100px;">

                                    </div>
                                    @error('banner')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                </div>
                            </div>
                            <!-- Banner Title -->
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Banner Title *</label>
                                    <input type="text" class="form-control" name="banner_title" value="{{ $microsite->banner_titile }}" readonly placeholder="Enter banner title">
                                  
                                </div>
                            </div>
                            <!-- Banner Link -->
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Banner Link *</label>
                                    <input type="text" class="form-control" name="banner_link" value="{{ $microsite->banner_link }}" readonly placeholder="Enter banner link">
                                   
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
                                    <input type="text" class="form-control" name="about_title" value="{{ $microsite->about_title }}"  readonly placeholder="Enter about title">
                                   
                                </div>
                            </div>
                            <!-- About Video -->
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">About Video</label>
                                    
                                    <video width="320" height="240" controls>
                                        <source src="{{asset("/uploads/".$microsite->video)}}" type="video/mp4">
                                      
                                  </video>
                                </div>
                            </div>
                            <!-- About Content -->
                            <div class="col-sm-12">
                                <div class="mb-3">
                                    <label class="form-label">About Content</label>
                                    <textarea class="form-control @error('about_description') is-invalid @enderror" name="about_description" readonly> {{ strip_tags(htmlspecialchars_decode($microsite->about_description )) }}</textarea>
                               
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
                                    <textarea class="form-control @error('detail_description') is-invalid @enderror" readonly>{{ strip_tags(htmlspecialchars_decode($microsite->detail_description)) }}</textarea>
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
        $('#summernote').summernote('disable');
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
