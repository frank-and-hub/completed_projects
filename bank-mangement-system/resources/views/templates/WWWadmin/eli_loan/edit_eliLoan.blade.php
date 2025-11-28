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
                            <form method="post" action="{!! route('admin.eliLoan.update') !!}" id="eli_loan">
                                 @csrf
                                <input type="hidden" name="id" value="{{$head->id}}"> 
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Title{{$head->sub_head}}<sup>*</sup></label>
                                    <div class="col-lg-4  error-msg">
                                        <input type="text" id="title" name="title" class="form-control " 
                                        value="{{$head->sub_head}}" >
                                    </div>
                                </div>
                                 <div class="text-right">
                                    <input type="submit" name="submitform" value="Update" class="btn btn-primary submit">
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
    @include('templates.admin.eli_loan.partials.script')
@stop