@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Scholarship</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Scholarship</li>
  </ol>
</nav>
<?php 
$tag = \DB::table('tags')->select('slug')->groupBy('slug')->pluck('slug');
$tagName = \DB::table('tags')->pluck('slug','name')->toArray();
?>
<div class="row">
  <div class="col-md-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Marquee Form - Edit</h6>
        <form method="POST" action="{{ route('admin.scholarship.updateMarquee',1) }}"  enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="row">
          <!-- FAQ'S -->
          <div class="col-sm-12">
            <div class="mb-3">
              <label class="form-label">Marquee Description</label>
              <textarea class="form-control @error('description') is-invalid @enderror" rows="5" placeholder="Enter Description" name="description">{{ old('description', $marquee->description) }}</textarea>
              @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary submit">Update Marquee</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
</script>
          
@endsection
