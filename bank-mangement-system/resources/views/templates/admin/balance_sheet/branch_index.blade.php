@extends('templates.admin.master')

@section('content')
    <style type="text/css">
        .text_upper {
            text-transform: uppercase;
        }
    </style>
    <div class="content">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12"> Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  " name="start_date" id="start_date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if (Auth::user()->branch_id < 1)
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch" name="branch_name">
                                                <option value="">Select Branch</option>
                                                @foreach ($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="branch_name" id="branch"
                                    value="{{ Auth::user()->branch_id }}">
                            @endif
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="fund_transfer_export" id="fund_transfer_export"
                                            value="">
                                        <button type="button" class=" btn bg-dark legitRipple"
                                            onClick="searchForm()">Submit</button>
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
        <div id="filter_data">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between mx-2 mb-2">
                        <span class="font-weight-bold text_upper">LIABILITIES & OWNERS' EQUITY</span>
                        <span class="font-weight-bold text_upper">Amount</span>
                    </div>
                    <div class="card">
                        <div class="">
                            <table id="capital" class="table  datatable-show-all">
                                @foreach ($libalityHead as $lib)
                                    <thead>
                                        <tr>
                                            <th class="text_upper"><a href="{{ URL::to('admin/balance-sheet/detail/' . $lib->head_id . '/3?date=' . $end_date . '&branch=' . $branch_id) }}">{{ $lib->sub_head }}</a></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <?php $libalityHeadThree = getHead($lib->head_id, 3); ?>
                                    @if (count($libalityHeadThree) > 0)
                                        @foreach ($libalityHeadThree as $valLib)
                                            <tbody>
                                                <tr>
                                                    <td class="text_upper">{{ $valLib->sub_head }}</td>
                                                    <td></td>
                                                    <td>&#X20B9;{{ number_format((float) (($valLib->head_id == 17) ? $profit_loss : headTotalFilter($valLib->head_id, 'head3', $end_date, $branch_id)), 2, '.', '') }}</td>
                                                </tr>
                                            </tbody>
                                        @endforeach
                                    @endif
                                @endforeach

                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="font-weight-bold text_upper">ASSETS</span>
                        <span class="font-weight-bold text_upper">Amount</span>
                    </div>
                    <div class="card">
                        <div class="">
                            <table id="current_asset" class="table  datatable-show-all">
                                @foreach ($assestHead as $assest)
                                    <thead>
                                        <tr>
                                            <th class="text_upper"> <a href="{{ URL::to('admin/balance-sheet/detail/' . $assest->head_id . '/3?date=' . $end_date . '&branch=' . $branch_id) }}">{{ $assest->sub_head }}</th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <?php $assestHeadThree = getHead($assest->head_id, 3); ?>
                                    @if (count($assestHeadThree) > 0)
                                        @foreach ($assestHeadThree as $val1)
                                            <tbody>
                                                <tr>
                                                    <td class="text_upper">{{ $val1->sub_head }}</td>
                                                    <td></td>
                                                    <td>&#X20B9;{{ number_format((float) headTotalFilter($val1->head_id, 'head3', $end_date, $branch_id), 2, '.', '') }}</td>
                                                </tr>
                                            </tbody>
                                        @endforeach
                                    @endif
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-flex justify-content-between ">
                <h4 class="text_upper">Interbranch transactions </h4>
                <span class="mr-1 text_upper">Total Amount:0000</span>
            </div>
            <div class="row  d-flex align-items-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="">
                            <table id="total_liability" class="table datatable-show-all">
                                <thead>
                                    <tr>
                                        <th class="text_upper">Total Liabilities</th>
                                        <th></th>
                                        <th>&#X20B9;{{ number_format((float) $totalLibality, 2, '.', '') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="">
                            <table id="total_assets" class="table datatable-show-all">
                                <thead>
                                    <tr>
                                        <th class="text_upper">Total Assets[E]</th>
                                        <th></th>
                                        <th>&#X20B9;{{ number_format((float) $totalAssest, 2, '.', '') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>


    </div>
    </div>
    @include($script)

@stop
