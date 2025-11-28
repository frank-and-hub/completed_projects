
@extends('templates.admin.master')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <div class="card-body">
                        <form name="company_detail" id="company_detail" method="POST" action="{!! route('admin.companies.save') !!}">
                            @csrf
                            <input type="hidden" name="created_at" class="created_at" id="created_at">
                            <input type="hidden" name="globalDate" class="create_application_date" id="globalDate">
                            @include('templates.admin.company.firstStep')
                            @include('templates.admin.company.secondStep')
                            @include('templates.admin.company.fifthStep')
                            @include('templates.admin.company.thirdStep')
                            @include('templates.admin.company.fourthStep')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('script')
@include('templates.admin.company.partials.script')
@stop