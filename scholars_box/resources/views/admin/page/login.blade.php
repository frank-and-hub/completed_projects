@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Page</a></li>
    <li class="breadcrumb-item active" aria-current="page">login Page</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">login Form</h6>
        <form method="POST" action="{{route('admin.login.page.store')}}" enctype="multipart/form-data">

          @csrf
          @method('PUT') <!-- Use PUT method for updating -->

          <div class="row">
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Contact Title</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" placeholder="Enter Title" name="title" value="{{ old('title', $log->title) }}">
                @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Login Page Description</label>
                <textarea type="textarea" class="form-control @error('description') is-invalid @enderror" placeholder="Enter contact description" name="description" >{{ old('description', $log->description) }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            {{--
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Button Url </label>
                <input type="text" class="form-control @error('url') is-invalid @enderror" placeholder="Enter Url Button" name="url" value="{{ old('url', $log->url) }}">
                @error('url')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            --}}
          <div class="mb-3">
            <button type="submit" class="btn btn-primary submit">Update Login Page Details</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection