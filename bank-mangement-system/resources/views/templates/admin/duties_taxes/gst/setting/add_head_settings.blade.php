@extends('templates.admin.master')
@section('content')
<div class="content"> 
    <div class=""> 
      
      <form action="@if(isset($record->head_id)) {!! route('admin.duties_taxes.gst.setting.update_head_settings') !!} @else  {!! route('admin.duties_taxes.gst.setting.save_head_settings') !!} @endif" method="post" enctype="multipart/form-data" id="head_setting" name="head_setting">
        @csrf
        <input class="" type="hidden" name="id" value={{isset($record->id) ? $record->id  : ''}} >
        <input type="hidden" value=""  class="created_at"  id="created_at" name="created_at" />

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
                        <select class="form-control" required name="head_id" {{isset($record->head_id) ? 'disabled':''}} autocomplete="off"> 
                            <option value="">---Please Select Head---</option>
                           @foreach($heads as $key=>$view)
                           <option value="{{$key}}"  {{isset($record->head_id) && $record->head_id == $key ? 'selected':''}} >{{$view}}</option>
                           @endforeach
                         </select>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">GST %<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" class="form-control" required name="gst_percentage" id="gst_percentage" value="{{isset($record->gst_percentage) ? $record->gst_percentage : ''}}"/>
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
    @include('templates.admin.duties_taxes.gst.setting.partials.script')
@stop