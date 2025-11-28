@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')

@push('custom-style')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">


@endpush
<x-admin.breadcrumb active="{{!empty($customPage)?'Edit Custom Page':'New Custom Page'}}" :breadcrumbs="$breadcrumbs">
</x-admin.breadcrumb>
<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Custom Page</h5>
                {{-- <small class="text-primary float-end">Merged input group</small> --}}
            </div>
            {{-- <div id="summernote"></div> --}}
            <form action="{{!empty($customPage)?route('admin.settings.update.custom.page',$customPage->id):route('admin.settings.save.custom.page')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="text-primary">Custom Page Name:</label>
                        <input type="text" class="form-control" id="name" name='name' value="{{old('name')??(!empty($customPage)?$customPage->name:null)}}">
                    </div>
                </div>
                <div class="col-md-12">
                    <textarea id="summernote" name="text">{{old('text')??(!empty($customPage)?$customPage->text:null)}}</textarea>

                </div>
                <div class="d-flex justify-content-center mt-3 mb-3">
                    <input type='submit' value="{{!empty($customPage)?'Update Custom Page':'Create Custom Page'}}" class="btn btn-primary btn">
                </div>
            </form>
        </div>

    </div>
</div>
@push('script')

<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    $(document).ready(function() {
        $('#summernote').summernote({
        placeholder: 'Write here...',
        tabsize: 4,
        height: 375.65,
        // wdith:90,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture',]],
          ['view', [ 'codeview', 'help']]
        ]
      });
});
    </script>
@endpush
@endsection
