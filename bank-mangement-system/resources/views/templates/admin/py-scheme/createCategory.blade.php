@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Create</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-danger"></p>
                        <!--Form Start-->
                        <form action="{{route('add_category')}}" method="post" name="plan_category_form" id="plan_category_form">
                            @csrf
                            <!--Name Div Start-->
                            <div class="form-group row">
                                {!!Form::hidden('created_at',null,['class'=>'created_at'])!!}
                                <label class="col-form-label col-lg-2" for="name">Name:<span class="error">*</span></label>
                                <div class="col-lg-6">
                                    <input type="text" name="name" id="name" class="form-control" value="{{old('name')}}" reqiured>
                                    <!--Error Message Start-->
                                    <span class="error">@error('name'){{$message}}@enderror</span>
                                    <!--Error Message End-->
                                </div>
                            </div>
                            <!--Name Div End-->
                            <!--Plan Code Div Start-->
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2" for="plan_code">Category Code:<span class="error">*</span></label>
                                <div class="col-lg-6">
                                    <input type="text" name="plan_code" id="plan_code" class="form-control" value="{{old('plan_code')}}" reqiured>
                                    <!--Error Message Start-->
                                    <span class="error">@error('plan_code'){{$message}}@enderror</span>
                                    <!--Error Message End-->
                                </div>
                            </div>
                            <!--Plan Code Div End-->
                            <div class="text-right">
                                <button type="submit" class="btn bg-dark">Submit<i class="icon-paperplane ml-2"></i></button>
                            </div>
                        </form>
                        <!--Form End-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.admin.py-scheme.partials.planCategoryScript')
@stop
