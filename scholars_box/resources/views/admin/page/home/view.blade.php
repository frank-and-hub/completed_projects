@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Banner</a></li>
    <li class="breadcrumb-item active" aria-current="page">View Banner</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Banner View</h6>
        

          <!-- Blog Title -->
          <div class="row">
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Banner Title</label>
                <input type="text" class="form-control @error('banner_title') is-invalid @enderror" placeholder="Enter Banner title" name="blog_title" value="{{ old('blog_title', $banner->title) }}" readonly>
                @error('banner_title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->

            <div class="col-sm-6">
                <div class="mb-3">
                    <label class="form-label">Banner Image</label>
                    @if ($banner->image)
                        <img src="{{ asset('uploads/' . $banner->image) }}" alt="Blog Image" class="mb-2" style="max-width: 200px;">
                    @endif
                    <!-- <input type="file" class="form-control @error('blog_image') is-invalid @enderror" name="blog_image" accept="image/*">
                    @error('banner_image')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror -->
                </div>
            </div><!-- Col -->
          <!-- Blog Content -->
          {{-- <div class="row">
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Blog Content</label>
                {!!$blog->description!!}
              </div>
            </div><!-- Col -->
          </div><!-- Row --> --}}

          {{-- <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Created By</label>
                <input type="text" class="form-control @error('blog_title') is-invalid @enderror" placeholder="Enter blog title" name="blog_title" value="{{ old('created_br', $blog->created_by) }}" readonly>
                @error('blog_title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col --> --}}

            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Created Date</label>
                <input type="text" class="form-control @error('blog_title') is-invalid @enderror" placeholder="Enter blog title" name="blog_title" value="{{ old('created_br', $banner->created_at->format('d-m-Y')) }}" readonly>
                @error('blog_title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
      </div>
    </div>
  </div>
</div>
</div>

@endsection
