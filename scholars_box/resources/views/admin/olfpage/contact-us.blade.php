@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Blog</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Blog</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Contact Us Form</h6>
        <form method="POST" action="{{route('admin.contact-us.store')}}" enctype="multipart/form-data">

          @csrf
          @method('PUT') <!-- Use PUT method for updating -->

          <div class="row">
            <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Contact short description</label>
                <textarea type="textarea" class="form-control @error('description') is-invalid @enderror" placeholder="Enter contact description" name="description" >{{ old('description', $contact->description) }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Contact email</label>
                <input type="text" class="form-control @error('email') is-invalid @enderror" placeholder="Enter email" name="email" value="{{ old('email', $contact->email) }}">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Contact number </label>
                <input type="text" class="form-control @error('number') is-invalid @enderror" placeholder="Enter contact number" name="number" value="{{ old('number', $contact->number) }}">
                @error('number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Contact Address</label>
                <input type="text" class="form-control @error('address') is-invalid @enderror" placeholder="Enter contact address" name="address" value="{{ old('address', $contact->address) }}">
                @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">title</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" placeholder="Enter contact title" name="title" value="{{ old('title', $contact->title) }}">
                @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
                <div class="col-sm-12">
              <div class="mb-3">
                <label class="form-label">Contact Long description</label>
                <textarea type="textarea" class="form-control @error('long_description') is-invalid @enderror" placeholder="Enter contact long description" name="long_description" >{{ old('long_description', $contact->long_description) }}</textarea>
                @error('long_description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-sm-6">
              <div class="mb-3">
                <label class="form-label">Contact Address Map</label>
                <input type="text" class="form-control @error('map') is-invalid @enderror" placeholder="Enter contact map src" name="map" value="{{ old('map', $contact->map) }}">
                @error('map')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          
          <div class="mb-3">
            <button type="submit" class="btn btn-primary submit">Update Contact</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection