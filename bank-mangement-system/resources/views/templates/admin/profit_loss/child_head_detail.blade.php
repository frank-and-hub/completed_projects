@extends('templates.admin.master')
@section('content')
    <?php
    $date = '';
    $branch_id = '';
    $to_date = '';
    if (isset($_GET['date'])) {
        $date = trim($_GET['date']);
        if ($date != '') {
            $date = date('d/m/Y', strtotime(convertDate($date)));
        }
    }
    if (isset($_GET['to_date'])) {
        $to_date = trim($_GET['to_date']);
        if ($to_date != '') {
            $to_date = date('d/m/Y', strtotime(convertDate($to_date)));
        }
    }
    if (isset($_GET['branch_id'])) {
        $branch_id = trim($_GET['branch_id']);
        if ($branch_id != '') {
            $branch_id = $branch_id;
        }
    }
    
    $t_amount = 0;
    $finacialYear = getFinacialYear();
    $branchIddd = 33;
    $globalDate1 = headerMonthAvailability(date('d'), date('m'), date('Y'), $branchIddd);
    $startDate = date('d/m/Y', strtotime($finacialYear['dateStart']));
    $endDatee = date('d/m/Y', strtotime(convertDate($globalDate1)));
    $currentYear = date('Y');
    $endYear = date('Y') + 1;
    $finacialCurrentYear = $_GET['financial_year'];
    ?>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Search Filter</h6>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                            @csrf
                            <input type="hidden" class="form-control  " name="default_date" id="default_date"
                                value="{{ $startDate }}">
                            <input type="hidden" class="form-control " name="default_end_date" id="default_end_date"
                                value="{{ $endDatee }}">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Financial Year </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="financial_year" name="financial_year">
                                                @foreach (getFinancialYear() as $key => $value)
                                                    <option value="{{ $value }}"
                                                        @if ($value == $finacialCurrentYear) selected @endif>
                                                        {{ $value }} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="date" id="date"
                                                    value="{{ $date }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="to_date" id="to_date"
                                                    value="{{ $to_date }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <div class="col-lg-12 text-right">
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="export" id="export" value="">
                                            <input type="hidden" name="head" id="head"
                                                value="{{ $headDetail->head_id }}">
                                            <input type="hidden" name="labels" id="labels"
                                                value="{{ $headDetail->labels }}">
                                            <button type="button"
                                                class=" btn bg-dark legitRipple submit"onClick="searchForm()">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form"
                                                onClick="resetForm()">Reset </button>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mt-2">
                <div class="card">
                    <div class="float-right card-header"> 
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                    </div>
                    <div class="card-header header-elements-inline">
                        <th><a target="_blank">{{ $headDetail->sub_head }}</a></th>
                        <th><a target="_blank">{{ $headDetail->head_id }}</a></th>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.admin.profit_loss.partials.search_script_detail')
@stop
