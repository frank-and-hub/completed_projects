@extends('templates.admin.master')



@section('content')
@section('css')
    <style>
        .hideTableData {
            display: none;
        }
    </style>
@endsection
<style type="text/css">
    .datepicker-dropdown {
        z-index: 100 !important;
    }
</style>


<?php
$finacialYear = getFinacialYear();
$date = date('Y-m-d', strtotime($finacialYear['dateStart']));
$branchIddd = 33;
$globalDate1 = headerMonthAvailability(date('d'), date('m'), date('Y'), $branchIddd);
$to_date = date('d/m/Y', strtotime(convertDate($globalDate1)));
?>
<?php
$branch_id = '';
if (Auth::user()->branch_id > 0) {
    $branch_id = Auth::user()->branch_id;
}

$income = [3];
$subHeadsIDS = App\Models\AccountHeads::where('head_id', 3)
    ->where('status', 0)
    ->pluck('head_id')
    ->toArray();
if (count($subHeadsIDS) > 0) {
    $head_ids = array_merge($income, $subHeadsIDS);
    $return_array = get_change_sub_account_head($income, $subHeadsIDS, true);
}
foreach ($return_array as $key => $value) {
    $ids[] = $value;
}
$expense = [4];
$subHeadsIDS = App\Models\AccountHeads::where('head_id', 4)
    ->where('status', 0)
    ->pluck('head_id')
    ->toArray();
if (count($subHeadsIDS) > 0) {
    $head_ids = array_merge($expense, $subHeadsIDS);
    $return_array = get_change_sub_account_head($expense, $subHeadsIDS, true);
}
foreach ($return_array as $key => $value) {
    $expenseIds[] = $value;
}

$currentYear = date('Y');
$endYear = date('Y') + 1;
$finacialCurrentYear = date('Y') . ' - ' . $endYear;
?>


<div class="content">

    <div class="col-md-12">

        <div class="card">

            <div class="card-header header-elements-inline">

                <h6 class="card-title font-weight-semibold">Search Filter</h6>

            </div>

            <div class="card-body">

                <form action="{!! route('admin.profit&loss') !!}" method="post" enctype="multipart/form-data" id="profit-loss-filter"
                    name="profit-loss-filter">

                    @csrf


                    <div class="row">
                        <?php
                        $finacialYear = getFinacialYear();
                        $date = date('d/m/Y', strtotime($finacialYear['dateStart']));
                        $to_date = date('d/m/Y', strtotime(convertDate($globalDate1)));
                        //$endDate=date("d/m/Y");
                        ?>
                        <!-- <input type="hidden" class="form-control  " name="default_date" id="default_date"  value="{{ $date }}" >
                    <input type="hidden" class="form-control  " name="default_end_date" id="default_end_date"  value="{{ $to_date }}" > -->
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Financial Year </label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control" id="financial_year" name="financial_year">
                                        @foreach (getFinancialYear() as $key => $value)
                                            <option value="{{ $value }}"
                                                @if ($value == $finacialCurrentYear) selected @endif>{{ $value }}
                                            </option>
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
                                        <input type="text" class="form-control  " name="start_date" id="start_date"
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
                      @include('templates.GlobalTempletes.both_company_filter' ,['allNot' => 'no','branchName' =>'branch_name'])
                        {{-- <div class="col-md-3">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">Branch</label>
                                <div class="col-lg-12 error-msg">
                                    <select class="form-control" id="branch" name="branch_name">
                                        <option value="">Select Branch</option>
                                        @if (is_null(Auth::user()->branch_ids))
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        @else
                                            <?php //$an_array = explode(',', Auth::user()->branch_ids); ?>
                                            @foreach ($branches as $branch)
                                                @if (in_array($branch->id, $an_array))
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div> --}}

                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-lg-12 text-right">
                                    <input type="hidden" name="is_search" id="is_search" value="no">
                                    <input type="hidden" name="export" id="export" value="">
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
    <div class="data container-fluid table-section hideTableData">

        <div class="container">
            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0"
                style="float: right;">Export xslx</button>
            <button type="button" class="btn bg-dark legitRipple export" data-extension="1"
                style="float: right;">Export PDF</button>
        </div>
    </div>

    <br /><br />
    <div id="filter_data">
        <!--
              <div class="row">

                <div class="col-md-6">

                  <div class="d-flex justify-content-between mx-2 mb-2">

                    <span class="font-weight-bold">INCOME</span>

                    <span class="font-weight-bold">AMOUNT</span>

                  </div>

                  <div class="card">

                    <div class="">

                      <table id="direct_income" class="table  datatable-show-all">

                        @foreach ($incomeHead as $val)
<?php $countExpense = getHead($val->head_id, 4); ?>
                          <?php $countExpenseLabel3 = getHead($val->head_id, 3); ?>
                          <thead >

                            <tr >
                              @if (count($countExpenseLabel3) > 0)
<th><a href="{{ URL::to('admin/profit-loss/detail/' . $val->head_id . '/' . $val->head_id . '/?date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ $val->sub_head }} </a> </th>
@else
<th><a href="{{ URL::to('admin/profit-loss/head_detail_report/' . $val->head_id . '/' . $val->labels . '?date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ strtoupper($val->sub_head) }}</a></th>
@endif
                              <th></th>
                              @php
                                  $headAmount = getHeadClosingNew($val->head_id, $date);
                              @endphp
                              <th>&#X20B9; {{ $headAmount != null ? number_format((float) $headAmount, 2, '.', '') : number_format((float) headTotalNew($val->head_id, $date, $to_date, $branch_id, 1), 2, '.', '') }}</th>

                            </tr>

                          </thead>

                          @php  $incomeHeadThree = getHead($val->head_id,3); @endphp

                          @if (count($incomeHeadThree) > 0)
@foreach ($incomeHeadThree as $valincome)
<tbody>

                                <tr style="background-color: #dff9fb;">
                                  @if (count($countExpense) > 0)
<th><a href="{{ URL::to('admin/profit-loss/detail/' . $valincome->head_id . '/' . $valincome->labels . '/?date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ strtoupper($valincome->sub_head) }} </a> </th>
@else
<th><a href="{{ URL::to('admin/profit-loss/detail/branch_wise/' . $valincome->head_id . '/' . $valincome->labels . '?date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ $valincome->sub_head }}</a></th>
@endif
                                   <td style="padding-right:150px"></td>
                                   @php
                                       $headAmount = getHeadClosingNew($val->head_id, $date);
                                   @endphp
                                  <td>&#X20B9; {{ $headAmount != null ? number_format((float) $headAmount, 2, '.', '') : number_format((float) headTotalNew($valincome->head_id, $date, $to_date, $branch_id, 1), 2, '.', '') }}</td>
                                
                                  
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

                      <span class="font-weight-bold">EXPENSE</span>

                      <span class="font-weight-bold">AMOUNT</span>

                    </div>

                    <div class="card">

                      <div class="">

                          <table id="current_asset" class="table  datatable-show-all">



                            @foreach ($expenseHead as $val)
<?php $countExpense = getHead($val->head_id, 4); ?>

                            <thead >

                              <tr >
                               
                                  <th><a href="{{ URL::to('admin/profit-loss/detail/' . $val->head_id . '/' . $val->labels . '/?date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ $val->sub_head }} </a> </th>
                                
                                <th></th>
                                @php
                                    $headAmount = getHeadClosingNew($val->head_id, $date);
                                @endphp
                                <th>&#X20B9; {{ $headAmount != null ? number_format((float) $headAmount, 2, '.', '') : number_format((float) headTotalNew($val->head_id, $date, $to_date, $branch_id, 1), 2, '.', '') }}</th>
                                

                              </tr>

                            </thead>

                            @php  $expenseHeadThree = getHead($val->head_id,3); @endphp

                            @if (count($expenseHeadThree) > 0)
@foreach ($expenseHeadThree as $index => $valexpense)
@php  $CountexpenseHeadThree = getHead($valexpense->head_id,4);  @endphp

                                <tbody>
                                
                                 

                                  <tr style="background-color: #dff9fb;">
                                  @if (count($CountexpenseHeadThree) > 0)
<th><a href="{{ URL::to('admin/profit-loss/detail/' . $valexpense->head_id . '/' . $valexpense->labels . '/?date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ strtoupper($valexpense->sub_head) }} </a> </th>
@elseif(in_array(86, $expenseIds) &&
        in_array(40, $expenseIds) == false &&
        in_array(53, $expenseIds) == false &&
        in_array(87, $expenseIds) == false &&
        in_array(88, $expenseIds) == false)
<th><a href="{{ URL::to('admin/profit-loss/head_detail_report/' . $valexpense->head_id . '/' . $valexpense->labels . '?date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ strtoupper($valexpense->sub_head) }}</a></th>
@elseif($valexpense->head_id == 87)
<th><a href="{{ URL::to('admin/profit-loss/detailed/commission/' . '?head=' . $valexpense->head_id . '&date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ $valexpense->sub_head }}</a></th>
@elseif($valexpense->head_id == 40)
<th><a href="{{ URL::to('admin/profit-loss/detailed/depreciation/' . '?head=' . $valexpense->head_id . '&date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ strtoupper($valexpense->sub_head) }}</a></th>
@elseif($valexpense->head_id == 46)
<th><a href="{{ URL::to('admin/profit-loss/detail/' . $valexpense->head_id . '/' . $valexpense->labels . '/?date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ strtoupper($valexpense->sub_head) }} </a> </th>
@elseif($valexpense->head_id == 97)
<th><a href="{{ URL::to('admin/profit-loss/detailed/interest_on_loan_taken/' . $valexpense->head_id . '/' . $valexpense->labels . '?date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ strtoupper($valexpense->sub_head) }}</a></th>
@elseif($valexpense->head_id == 88)
<th><a href="{{ URL::to('admin/profit-loss/detailed/fuel_charge/' . '?head=' . $valexpense->head_id . '&date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ strtoupper($valexpense->sub_head) }}</a></th>
@else
<td><a href="{{ URL::to('admin/profit-loss/detail/branch_wise/' . $valexpense->head_id . '/' . $valexpense->labels . '?date=' . $date . '&to_date=' . $to_date . '&financial_year=' . $finacialCurrentYear) }}" target="_blank">{{ strtoupper($valexpense->sub_head) }}</a></td>
@endif
                                   @if (!is_null(Auth::user()->branch_ids))
<td>&#X20B9; {{ number_format((float) headTotalNew($valexpense->head_id, $date, $to_date, $branch_id), 2, '.', '') }}</td>
                                  <td style="padding-right:150px"></td>
@else
<td style="padding-right:150px"></td>
                                  @php
                                      $headAmount = getHeadClosingNew($valexpense->head_id, $date);
                                  @endphp
                                  <td>&#X20B9; {{ $headAmount != null ? number_format((float) $headAmount, 2, '.', '') : number_format((float) headTotalNew($valexpense->head_id, $date, $to_date, $branch_id, 1), 2, '.', '') }}</td>
@endif
                                   
                                  
                                 
                                </tbody>
@endforeach
@endif
@endforeach

                        </table>

                      </div>

                    </div>

                  </div>

              </div>

              <div class="row d-flex align-items-center">

                  <div class="col-md-6">

                      <div class="card">

                        <div class="">

                          <table id="total_liability" class="table datatable-show-all">

                              <thead>

                                 <tr class="d-flex justify-content-between">

                                  <th>Total Income</th>

                                  <th></th>
                                  @php
                                      $headAmount = getHeadClosingNew(3, $date);
                                  @endphp
                                  <th>&#X20B9;  {{ $headAmount != null ? number_format((float) $headAmount, 2, '.', '') : number_format((float) headTotalNew(3, $date, $to_date, $branch_id, 1), 2, '.', '') }}</th>

                                 </tr>

                              </thead>

                          </table>

                        </div>

                      </div>

                   </div>

                   <div class="col-md-6 ">

                      <div class="card">

                        <div class="">

                          <table id="total_assets" class="table datatable-show-all">

                            <thead>

                              <tr class="d-flex justify-content-between">

                              <th>Total Expenses</th>

                              <th></th>
                              @php
                                  $headAmount = getHeadClosingNew(4, $date);
                              @endphp
                              <th>&#X20B9;  {{ $headAmount != null ? number_format((float) $headAmount, 2, '.', '') : number_format((float) headTotalNew(4, $date, $to_date, $branch_id, 1), 2, '.', '') }}</th>

                             </tr>

                            </thead>

                          </table>

                        </div>

                      </div>

                    </div>

              </div>-->

    </div>

</div>

@include('templates.admin.profit_loss.partials.script')



@stop
