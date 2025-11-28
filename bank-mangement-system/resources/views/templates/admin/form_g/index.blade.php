@extends('templates.admin.master')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                    @csrf
                    <input type="hidden" name="member_id" class="member_id" value="{{$member_id}}">
                    <input type="hidden" name="customer_id" class="customer_id" id="customer_id" value="{{$company_id->customer_id}}">
                    <input type="hidden" name="customerId" class="customerId" id="customerId" value="{{$company_id->company_id}}">
                    <input type="hidden" name="status" id="status" value="">
                    <input type="hidden" name="export" id="export">
                </form>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="update_15_g" name="update_15_g">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at" id="created_at">
                        <input type="hidden" name="member_id" class="member_id" id="member_id" value="{{$member_id}}">
                        <input type="hidden" name="customer_id" class="customer_id" id="customer_id" value="{{$company_id->customer_id}}">
                        <input type="hidden" name="customerId" class="customerId" id="customerId" value="{{$company_id->company_id}}">
                        <input type="hidden" name="status" id="status" value="">
                        <div class="row">
                            <!----------------- Maturity ----------------->
                            <div class="col-lg-12">
                                <div class="card bg-white">
                                    <div class="card-body">
                                        <h4 class="card-title mb-3"><b>{{($company_id ? $company_id->member_id : '').' | '.($company_id->member ?($company_id->member->first_name??'').' '.($company_id->member->last_name??''):'')}}</b></h4>
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-3">Company<sup class="required">*</sup></label>
                                                    <div class="col-lg-9 error-msg">
                                                        <select name="company_id" id="company_id" class="form-control" disabled>
                                                            <option value=""> --- Please Select company --- </option>
                                                            @foreach($company as $key => $val)
                                                            <option value="{{$key}}"
                                                                {{($key==($company_id->company_id))?'selected':''}}>
                                                                {{$val}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-3">Year<sup
                                                            class="required">*</sup></label>
                                                    <div class="col-lg-9 error-msg">
                                                        <select name="year" id="year" class="form-control">
                                                            <option value="">Select Year</option>
                                                            @php
                                                                $last = date('Y')-10 ;
                                                                $now = date('Y') + 1
                                                            @endphp
                                                            {{--
                                                            @for ($i = $now; $i >= $last; $i--)
                                                            <!-- <option value="{{ $i }}" max-year="{{$i + 1}}">{{ $i }} - {{$i + 1}}</option>-->
                                                            @endfor
                                                            --}}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group row">
                                                    <label class="col-form-label col-lg-3">Upload File <sup class="required">*</sup></label>
                                                    <div class="col-lg-9 error-msg">
                                                        <input type="file" name="file" id="file" class="form-control" required accept="application/pdf">
                                                    </div>
                                                </div>
                                            </div>
                                            <!----------------- Maturity ----------------->
                                            <div class="col-lg-12">
                                                <div class="text-right">
                                                    <input type="submit" name="submit" class="btn btn-primary submitBtn"
                                                        value="SUBMIT" />
                                                </div>
                                            </div>
                                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="data container-fluid mb-4">
            <div class="container">
                <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0"
                    style="float: right;">Export xslx</button>
                <!-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1" style="float: right;">Export PDF</button> -->
            </div>
        </div> <br /><br />
        <div class="col-md-12">
            <div class="card">
                <div class="">
                    <table class="table datatable-show-all" id="update_g">
                        <thead>
                            <tr>
                                <th>S.N</th>
                                <th>Company</th>
                                <th>Member Name</th>
                                <th>Year</th>
                                <th>File Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="emergancy-maturity-table">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('script')
@include('templates.admin.form_g.partials.script')
@stop
