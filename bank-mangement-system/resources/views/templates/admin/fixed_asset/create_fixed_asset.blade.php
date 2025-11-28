@extends('templates.admin.master')

@section('content')

    <div class="loader" style="display: none;"></div>

    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Basic layout-->
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <div class="card-body" >
                            <form method="post" action="{!! route('admin.save.fixed_asset') !!}" id="fixed_asset">
                                 @csrf
                             <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Select Asset<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="asset" id="asset" class="form-control">
                                           <option value="9" selected="true">Fixed Asset</option>
                                       </select>
                                    </div>
                                  <label class="col-form-label col-lg-2">Select Sub Asset<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="child_asset" id="child_asset"  class="form-control">
                                        <option value="">---Select Child Asset--- </option>
                                           @foreach($sub_assets as $s_asset)
                                                <option value="{{$s_asset->head_id}}">{{$s_asset->sub_head}}</option>
                                           @endforeach
                                       </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                     <label class="col-form-label col-lg-2">Select Sub Asset<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name="sub_child_asset" id="sub_child_asset"  class="form-control">
                                          <option value="">---Select Sub Child Asset--- </option> 
                                       </select>
                                    </div>
                                    <label class="col-form-label col-lg-2">Title<sup>*</sup></label>
                                    <div class="col-lg-4  error-msg">
                                        <input type="text" id="title" name="title" class="form-control " >
                                    </div>
                                </div>
                                 <div class="text-right">
                                    <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /basic layout -->
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    @include('templates.admin.fixed_asset.partials.script')
@stop