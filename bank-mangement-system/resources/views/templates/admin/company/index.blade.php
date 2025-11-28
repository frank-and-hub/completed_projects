@extends('templates.admin.master')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline" id="company_register">
                    <div class="card-body">
                        <div class="company_register">
                            @include('templates.admin.company.firstStep')
                            @include('templates.admin.company.secondStep')
                            @include('templates.admin.company.thirdStep')
                            @include('templates.admin.company.fifthStep')
                            @include('templates.admin.company.fourthStep')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('script')
@include('templates.admin.company.partials.index_script')
@stop