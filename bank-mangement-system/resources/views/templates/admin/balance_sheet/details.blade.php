@extends('templates.admin.master')
@section('content')
    <style type="text/css">
        .text_upper {
            text-transform: uppercase;
        }
    </style>
    <?php
        $key = '123456789987654321';
    ?>
    <div class="content">
        <div class="row">
            @include($filter)
            <div class="data container-fluid">
                <div class="container ">
                    <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                    {{-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1" style="float: right;">Export PDF</button> --}}
                </div>
            </div> <br /><br />
            <div class="col-md-12 mt-2" id="filter_data">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">
                            <?php
                                $headCount = getHead($headDetail->head_id, $label +1 ,$company_id);
                                $dataArray = [
                                    'start_date' => $start_date,
                                    'end_date' => $end_date,
                                    'company_id' => $company_id,
                                    'branch_id' => $branch_id,
                                ];
                                $href = 'balance-sheet.head';
                                $headArray = [
                                    'head_id' => $headDetail->head_id,
                                    'label' => $headDetail->labels,
                                    'direct' => true,
                                ];
                                $mergedArray = $dataArray + $headArray;
                                // $mergedArray = Crypt::encrypt($mergedArray, $key);
                            ?>
                            @if (isset($headDetail->head_id))
                                <th class="text_upper">
                                    <a href="{{ route($href, $mergedArray) }}" target="_blank">{{ $headDetail->sub_head }}</a>
                                </th>
                            @else
                                {{ number_format((float) headTotalNew($headDetail->head_id, $start_date, $end_date, $branch_id, $company_id), 2, '.', '') }}
                            @endif
                        </h6>
                        <h6>opening balance - &#X20B9;  {{ number_format((float) (isset($oldheadclosing[$headDetail->head_id]) ? ($oldheadclosing[$headDetail->head_id]['amount']) : ($amountNew[$headDetail->head_id] ?? 0)) , 2, '.', '')}}</h6>
                        <h6 class="card-title font-weight-semibold">&#X20B9; 
                            {{ number_format((float) (headTotalNew($headDetail->head_id, $start_date, $end_date, $branch_id, $company_id) + (isset($oldheadclosing[$headDetail->head_id]) ? $oldheadclosing[$headDetail->head_id]['amount']: $amountNew[$headDetail->head_id] ?? 0)), 2, '.', '') }}
                        </h6>
                    </div>
                    <table class="table datatable-show-all">
                        @if (count($childHead) > 0)
                            @foreach ($childHead as $value)
                                <?php
                                    $Head = getHead($value->head_id, $value->labels + 1,$company_id);
                                    $t_amount = 0;
                                    $profit_loss = 0;
                                    $t_amount += $profit_loss;
                                    $headArray = [
                                        'head_id' => $value->head_id,
                                        'branch_id' => $branch_id,
                                        'start_date' => $start_date,
                                        'end_date' => $end_date,
                                        'label' => $value->labels,
                                    ];
                                    $mergedArray = $dataArray + $headArray;
                                    // $mergedArray = Crypt::encrypt($mergedArray, $key);
                                ?>
                                <thead>
                                    <tr @if ($value->status == 1) class ="child_inactive" @endif>
                                        @if ($Head->count() > 0)
                                            <th class="text_upper"> <a href="{{ route($href, $mergedArray) }}" target="_blank" >{{ $value->sub_head }}</a> </th>
                                        @else
                                            <td class="text_upper">
                                                <a href="{{ route($href, $mergedArray) }}" target="_blank">{{ $value->sub_head }}</a>
                                            </td>
                                        @endif
                                        <th>{{ number_format((float) (isset($oldheadclosing[$value->head_id]) ? ($oldheadclosing[$value->head_id]['amount']) : ($amountNew[$value->head_id] ?? 0)), 2, '.', '')}}</th>
                                        <th></th>
                                        @if (in_array(17, $child) || in_array(6, $child))
                                            <th>&#X20B9;{{ number_format((float) $profit_loss, 2, '.', '') }}</th>
                                        @else
                                            <?php $t_amount =+ headTotalNew($value->head_id, $start_date, $end_date, $branch_id,$company_id); ?>
                                            <th>&#X20B9;{{ number_format((float) headTotalNew($value->head_id, $start_date, $end_date, $branch_id, $company_id), 2, '.', '') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                            @endforeach
                            <?php
                                $head6 = getHead($headDetail->head_id, $headDetail->labels + 1,$company_id);
                            ?>
                        @elseif(isset($head6) && count($head6) > 0)
                            @foreach ($head6 as $headLabel)
                                <td class="text_upper">
                                    <?php
                                        $headArray = [
                                            'head_id' => $headLabel->head_id,
                                            'branch_id' => $branch_id,
                                            'start_date' => $start_date,
                                            'end_date' => $end_date,
                                            'label' => $headLabel->labels,
                                            'company_id' => $company_id,
                                        ];
                                        $mergedArray = Crypt::encrypt($headArray, $key);
                                    ?>
                                    <a href="{{ route($href, $mergedArray) }}" target="_blank">{{ $headLabel->sub_head }}</a>
                                </td>
                            @endforeach
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include($script)
@stop
