@extends('templates.admin.master')

@section('content')
@section('css')
@endsection
<div class="content">
    <div class="row">
        <div class="col-md-12">@php $stateid = getBranchState(Auth::user()->username); @endphp
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold"></h6>
                </div>
                <div class="card-body">
                    {{Form::open(['url'=>'#','method'=>'post','id'=>'filter_holiday','class'=>'','name'=>'filter_holiday','enctype'=>'multipart/form-data'])}}            
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Select Year</label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="">
                                            <select class="form-control" id="year" name="year">
                                                @foreach( getUpcomingYears() as $key => $value )
                                                    <option value="{{ $value }}" >{{ $value }} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group text-right">
                                    <div class="col-lg-12 page">
                                        {{Form::hidden('is_search','no',['id'=>'is_search','class'=>'form-control'])}}
                                        {{Form::hidden('created_at','',['id'=>'created_at','class'=>'form-control created_at'])}}
                                        <button type="submit" class=" btn bg-dark legitRipple">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {{Form::close()}}
                </div>
            </div>
        </div> 
    </div>
</div>
@stop
@section('script')
@include('templates.admin.cron.partial.script')
@stop