@extends('templates.admin.master')
@section('content')
<style>
  #category-error{
    position:absolute;
    top:24px;
    left:0;

  }
</style>
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
      <form action="@if(isset($record->gst_no)) {!! route('admin.gst_setting.update') !!} @else  {!! route('admin.gst_setting.save') !!} @endif" method="post" enctype="multipart/form-data" id="gst_setting" name="gst_setting">
        @csrf
          <div class="">
            <input class="" type="hidden" name="edit_id" value={{isset($record->id) ?   $record->id  : ''}} >
            <!----------------- Gst ----------------->
            <div class="col-lg-12">
              <div class="card bg-white" > 
                <div class="card-body">
                  <h3 class="card-title mb-3">{{$title}}</h3>
                  <div class="row">
                 

                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">State<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">

                         <select class="form-control" name="state_id"  id="state_id"> 
                            <option value="">---Please Select State---</option>
                           @foreach($states as $state)
                           <option value="{{$state->id}}" {{isset($record->state_id) && $record->state_id == $state->id ? 'selected':''}} data-gst_code="{{$state->gst_code}}">{{$state->name}}</option>
                           @endforeach
                         </select>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">GST No. <sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="gst_no" id="gst_no" class="form-control" value ={{isset($record->gst_no) ? $record->gst_no : ''}}>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">Applicability Date<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="applicable_date" id="applicable_date" class="form-control input" readonly  autocomplete="off" value={{isset($record->applicable_date) ? date('d/m/Y',strtotime($record->applicable_date)):''}}>
                        </div>
                      </div>
                    </div>

                    <!-- <div class="col-lg-6">
                      <div class="form-group row">
                        <label class="col-form-label col-lg-4">End date<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                          <input type="text" name="end_date" id="end_date" class="form-control input" autocomplete="off">
                        </div>
                      </div>
                    </div> -->

                    <div class="col-lg-6 error-msg">
                      <div class="row ">
                        <label class="col-form-label col-lg-4">Category<sup class="required">*</sup></label>
                        <div class="form-check col-lg-4">
                          <input class="form-check-input" type="radio" value="0" {{isset($record->category) && $record->category == 0 ? 'checked':''}} id="defaultCheck1" name="category">
                          <label class="form-check-label" for="defaultCheck1">
                            Main
                          </label>
                        </div>
                        <div class="form-check col-lg-4">
                          <input class="form-check-input" type="radio" value="1" id="defaultCheck2"  name="category" {{isset($record->category) && $record->category == 1 ? 'checked':''}}>
                          <label class="form-check-label" for="defaultCheck2">
                            Input Service Distributor (ISD) 
                          </label>
                        </div>
                      </div>
                    </div>

                  

                   
            <!----------------- Gst ----------------->

            <div class="col-lg-12 ">
                  
               
                  <div class="text-center my-2">
                  <button type="submit" class="btn btn-primary legitRipple submit-demand-advice">{{isset($record->gst_no) ? 'Update':'Submit'}}</button>
                
               
              </div>
            </div>
          </div> 
      </form>
    </div> 
</div>

@include('templates.admin.gst.partials.scripts');
@stop
