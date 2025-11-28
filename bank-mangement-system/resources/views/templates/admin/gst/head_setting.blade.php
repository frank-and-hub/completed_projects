@extends('templates.admin.master')
@section('content')
<div class="content"> 
    <div class=""> 
      @if ($errors->any())
          <div class="col-md-12">
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
          </div>
      @endif
      <form action="@if(isset($record->head_id)) {!! route('admin.head_setting.update') !!} @else  {!! route('admin.head_setting.save') !!} @endif" method="post" enctype="multipart/form-data" id="head_setting" name="head_setting">
        @csrf
        <input class="" type="hidden" name="edit_id" value={{isset($record->id) ?   $record->id  : ''}} >

          <div class="">

            <!----------------- Head Setting ----------------->
            <div class="col-lg-12">
              <div class="card bg-white" > 
                <div class="card-body">
                  <h3 class="card-title mb-3">Add GST Head Setting</h3>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Heads <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                        <select class="form-control" name="head_id" {{isset($record->head_id) ? 'disabled':''}}> 
                            <option value="">---Please Select Head---</option>
                           @foreach($heads as $head)
                           <option value="{{$head->head_id}}"  {{isset($record->head_id) && $record->head_id == $head->head_id ? 'selected':''}}>{{$head->sub_head}}</option>
                           @endforeach
                         </select>
                        </div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">GST %<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" class="form-control" name="gst_percentage" id="gst_percentage" value="{{isset($record->gst_percentage) ? $record->gst_percentage : ''}}"/>
                        </div>
                      </div>
                    </div>
            <!----------------- Head Setting ----------------->

                <div class="col-lg-12 ">
                  <div class="text-center my-2">
                  <button type="submit" class="btn btn-primary legitRipple submit-demand-advice">{{isset($record->id)? 'Update':'Submit'}}</button>
              </div>
            </div>
          </div> 
      </form>
    </div> 
</div>
@stop
@section('script')
    @include('templates.admin.gst.partials.scripts')
@stop