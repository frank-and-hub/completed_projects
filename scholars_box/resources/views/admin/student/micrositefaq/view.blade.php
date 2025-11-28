@extends('admin.layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Faq</a></li>
    <li class="breadcrumb-item active" aria-current="page">View Faq</li>
  </ol>
</nav>

<div class="row">
    <div class="col-sm-12">
        <div class="mb-3">
            <label class="form-label">Faq Title</label>
            <textarea class="form-control @error('faq_content') is-invalid @enderror" name="faq_content" readonly>{{ old('title', $faq->title) }}</textarea>
        </div>
    </div><!-- Col -->
</div>
    <div class="row">

    <div class="col-sm-12">
        <div class="mb-3">
            <label class="form-label">Faq Description</label>
            <textarea class="form-control @error('faq_content') is-invalid @enderror" name="faq_content" readonly>{{ old('description', $faq->description) }}</textarea>
        </div>
    </div>
</div>


@endsection