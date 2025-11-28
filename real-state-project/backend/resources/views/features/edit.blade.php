@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Features'))
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            Add Features
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('features_list') }}">List</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
    <div class="row grid-margin">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title"></h4>
                    <form class="cmxform" id="edit_features" name="edit_features" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="feature_id" id="feature_id" value="{{ $featureData->id }}">
                        <div class="form-group">
                            <label for="heading">Heading <span class="text-danger">*</span></label>
                            <input id="heading" class="form-control" name="heading" type="text" value="{{ $featureData->heading}}">
                        </div>
                        <div class="form-group">
                            <label for="description">Description <span class="text-danger">*</span></label>
                            <textarea id="description" class="form-control" name="description" rows="8">{{  $featureData->description }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-6 form-group">
                                <label for="exampleTextarea1"> Image <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="features_img" id="features_img" onchange="upload()">
                            </div>
                            <div class="col-6">
                                <canvas id="features_img_canv1" class="imgcanvas"></canvas>
                                @if(isset($featureData))
                                <img src="{{ asset('storage/'.$featureData->image) }}" style="max-width:219px; max-height:175px;" class="features_img">
                                @endif
                            </div>
                        </div>
                        <div class="alert alert-success success_msg" role="alert"></div>
                        <div class="alert alert-danger error_msg" role="alert"></div>
                        <button type="submit" class="btn btn-primary mr-2">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('custom-script')
<script src="https://www.dukelearntoprogram.com/course1/common/js/image/SimpleImage.js"></script>
<script type="text/javascript">
    function upload() {
        $('.features_img').css('display', 'none')
        $('.imgcanvas').css('display', 'inline-block')
        var imgcanvas = document.getElementById("features_img_canv1");
        var fileinput = document.getElementById("features_img");
        var image = new SimpleImage(fileinput);
        image.drawTo(imgcanvas);
    }

    $(document).ready(function() {
        $("form[name='edit_features']").validate({
            rules: {
                heading: {
                    required: true
                },
                description: {
                    required: true,
                    maxWords: 500
                }
            , }
            , messages: {
                heading: {
                    required: 'Enter Heading'
                },
                description: {
                    required: 'Enter Description',
                    maxWords: 'Please enter less than 500 words.'
                }
            , }
            , submitHandler: function(form) {
                $.ajax({
                    url: "{{ route('update_features') }}"
                    , type: "POST"
                    , data: new FormData(form)
                    , processData: false
                    , contentType: false
                    , success: function(response) {
                        if (response.status == 'success') {
                            $("form[name='edit_features']").find('.serverside_error').remove();
                            $('.success_msg').html(response.msg);
                            $('.success_msg').fadeIn();
                            setTimeout(function() {
                                $('.success_msg').fadeOut();
                            }, 5000);
                            $('#edit_features')[0].reset();
                            window.location.href = "{{ route('features_list') }}"
                        } else {
                            $("form[name='edit_features']").find('.serverside_error').remove();
                            $('.error_msg').html(response.msg);
                            $('.error_msg').fadeIn();
                            setTimeout(function() {
                                $('.error_msg').fadeOut();
                            }, 5000);
                        }
                    }
                    , error: function(xhr, status, error) {
                        handleServerError('edit_features', xhr.responseJSON.errors);
                    }
                });
            }
        });
    });

</script>
@endpush
@endsection
