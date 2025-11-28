@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Banner</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Banner</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Banner Edit Form</h6>
        <form method="POST" action="{{ route('admin.home.banner.update', $baneer->id) }}" enctype="multipart/form-data">

          @csrf
          @method('PUT') <!-- Use PUT method for updating -->

          <!-- FAQ Title -->
          <div class="row">
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Banner Link</label>
                <input type="text" class="form-control @error('banner_title') is-invalid @enderror" placeholder="Enter banner link" name="banner_title" value="{{ old('banner_title', $baneer->title) }}">
                @error('banner_title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div><!-- Col -->
          </div><!-- Row -->

          <div class="col-sm-6">
            <div class="mb-3">
                <label class="form-label">Banner Image </label>
                @if ($baneer->image)
                    <img src="{{ asset('uploads/' . $baneer->image) }}" alt="Blog Image" class="mb-2" style="max-width: 200px;">
                @endif
                <input type="file" class="form-control @error('banner_image') is-invalid @enderror" name="banner_image" accept="image/*">
                @error('banner_image')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div><!-- Col -->

          <div class="mb-3">
            <button type="submit" class="btn btn-primary submit">Update Banner</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
