<div class="card pt-3 pb-3">
    <div class="card-header">
        <h3 class="card-title font-weight-semibold"><strong>Name of the Company:</strong> {{ $companyDetail->name }}</h3>
        <h6 class="card-title font-weight-semibold pt-2"><strong>Address:</strong> {{ $companyDetail->address }}</h6>
        @if ($head_id)
            <button type="button" class="btn bg-gray text-dark legitRipple ml-2" onclick='goBack()' data-extension="0" style="float: right;">Previous</button>
        @endif
        <button type="button" class="btn bg-dark legitRipple export ml-2" target="_blank" data-extension="0" style="float: right;">Export xslx</button>
    </div>
</div>
<?php
$key = '123456789987654321';
$start_y = $start_y;
$end_y = $end_y;
$start_m = $start_m;
$start_d = $start_d;
$end_m = $end_m;
$end_d = $end_d;
$hidden = [
    'start_y' => $start_y,
    'end_y' => $end_y,
    'branch_id' => $branch_id,
    'company_id' => $company_id,
];
$records = $oldheadclosing;
?>
<style>
    table thead tr:nth-child(1) {
        border: 1px solid #a1a1a1;
    }
    table thead tr:nth-child(1) th {
        border: 1px solid #a1a1a1;
    }
    table thead tr:nth-child(1) th {
        border-bottom: hidden;
    }
    table thead tr:nth-child(2) {
        border: 1px solid #a1a1a1;
    }
    table thead tr:nth-child(2) th {
        border: 1px solid #a1a1a1;
    }
</style>
<div class="card">
    <div class="card-header ">
        <h3 class="card-title font-weight-semibold"><strong>Trial Balance{{$name ? ' - '. $name : ''}}</strong></h3>
        <h6 class="card-title font-weight-semibold pt-2">From {{ $start_y }} to {{ $end_y }}</h6>
        @if (!$head_id)
            <input type="submit" name="save" value="Run Cron Again" class="btn btn-primary" id="myformsubmit" style="float:right">
        @endif
    </div>
    <div class="card-body">
        {{ Form::open(['url' => route('admin.trail_balance.updatechangedata'), 'method' => 'post', 'id' => 'trialbalanceformdata', 'class' => '', 'enctype' => 'multipart/form-data']) }}
        @foreach ($hidden as $key => $val)
            {{ Form::hidden($key, $val, ['id' => $key]) }}
        @endforeach
        <table id="head_listing" class="table">
            <thead>
                <tr>
                    <th>Particulars</th>
                    <th style="width: 140px;">Opening </th>
                    <th style="width: 140px; vertical-align: top;text-align:center; border-bottom: 1px solid #a1a1a1;!important" colspan="2">Transactions </th>
                    <th style="width: 140px;">Closing </th>
                </tr>
                <tr>
                    <th></th>
                    <th style="width: 140px">Balance </th>
                    <th style="width: 140px">Debit </th>
                    <th style="width: 140px">Credit </th>
                    <th style="width: 140px">Balance </th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $p = 40;
                    $n = 1;
                    $crTotal = 0;
                    $drTotal = 0;
                    $childHead = [];
                    $drTotalAmount = 0;
                    $crTotalAmount = 0;  
                    $headArray = [];              
                ?>
                @foreach ($data as $key => $head)
                <?php   
                    $headArray[$head->head_id] = $head->child_head;
                    $idHead = $head->head_id;
                    $data = headTree($idHead, $company_id);
                    $headBalance = $HeadBalance[$head->head_id] ?? 0;
                    $crTotal += $headBalance['cr_amount'] ?? 0;
                    $drTotal += $headBalance['dr_amount'] ?? 0;
                    $balanceTotal = $crTotal - $drTotal;
                    $oldHeadClosingAmount = $oldheadclosing[$head->head_id] ?? $previousData[$head->head_id] ?? 0;
                    $oldHeadClosingAmount = ($head->is_trial ==1) ? 0 : $oldHeadClosingAmount;
                    $drAmount = $headBalance['dr_amount'] ?? 0;
                    $crAmount = $headBalance['cr_amount'] ?? 0;
                    $crNature = $head->cr_nature;
                    $amount = $crNature == 1 ? $oldHeadClosingAmount + $crAmount - $drAmount : $oldHeadClosingAmount + $drAmount - $crAmount;
                    $headclosingtotalsum += $amount ;
                    ?>
                    <tr>
                        <td style="color:#089a08;font-weight: 900;">
                            <?
                                $array = [
                                    'branch_id'=>$branch_id,
                                    'company_id'=>$company_id,
                                    'financial_year'=>$financial_year,
                                    'name'=>$head->sub_head,
                                    'head_id'=>$head->head_id,
                                    ];
                                    $array = encrypt($array);
                                    $href = route('admin.trail_balance.sub_head',$array);
                                ?>
                            {{--<a href="{{ $href }}" style="color:#089a08;font-weight: 900;">--}}
                                {{ $key + 1 }}. {{ strtoupper($head->sub_head) }}
                            {{--</a>--}}
                        </td>
                        <td>{{ number_format((float) ($head->is_trial == 1) ? 0 : $oldHeadClosingAmount, 2, '.', '')}} </td>
                        <td>{{ number_format((float) $drAmount, 2, '.', '')}}</td>
                        <td>{{ number_format((float) $crAmount, 2, '.', '')}}</td>
                        <td>{{ number_format((float) $amount, 2, '.', '') }}</td>
                    </tr>
                    @if (count($data) > 0)
                        <?php 
                            $n1 = 1;
                            $data = $data->reject(function ($item) {
                            return $item->head_id === 6;
                            });
                        ?>
                        @foreach ($data as $key => $taxonomy)
                        @php
                                array_push($childHead,$taxonomy->head_id);
                                $subCount = count($taxonomy['subcategory']);
                                $headId = $taxonomy->head_id;
                                $oldClosingAmount = ( !isset($oldheadclosing[$headId]) ? (isset($previousData[$headId]) ? $previousData[$headId] : 0) : $oldheadclosing[$headId]);
                                $headBalance = (isset($HeadBalance[$headId] )) ? $HeadBalance[$headId] : ['dr_amount' => 0, 'cr_amount' => 0];
                                $drAmount = $headBalance['dr_amount'];
                                $crAmount = $headBalance['cr_amount'];
                                $crNature = $taxonomy->cr_nature;
                                $computedValue =(($taxonomy->is_trial ==1) ? 0 :  $oldClosingAmount) + (($crNature == 1) ? $crAmount - $drAmount : $drAmount - $crAmount);
                                $cAmlunt = isset($oldheadclosing[$headId]) ?  $oldheadclosing[$headId] :  $previousData[$headId] ?? 0;
                            @endphp
                            <tr>
                                <td>
                                    <?
                                        $array = [
                                            'branch_id'=>$branch_id,
                                            'company_id'=>$company_id,
                                            'financial_year'=>$financial_year,
                                            'name'=>$taxonomy->sub_head,
                                            'head_id'=>$taxonomy->head_id,
                                            ];
                                        $array = encrypt($array);
                                        $href = route('admin.trail_balance.sub_head',$array);
                                    ?>
                                    <a href="{{ $href }}" style="padding-left: {{ $p }}px; color: #224be1;font-weight: 700;">
                                        {{ $n1 }}. {{ strtoupper($taxonomy->sub_head) }}
                                    </a>
                                </td>
                                <td>{{ number_format((float)(($taxonomy->is_trial ==1) ? 0 : $cAmlunt) , 2, '.', '') }}</td>
                                <td>{{ number_format((float)$drAmount, 2, '.', '')}}</td>
                                <td>{{ number_format((float)$crAmount, 2, '.', '')}}</td>
                                <td>{{ number_format((float) $computedValue, 2, '.', '') }}</td>
                            </tr>
                            @if(!$head_id)
                            @includeWhen($subCount > 0, 'templates.admin.trailbalance.partials.head_tree', [
                                'subcategories' => $taxonomy['subcategory'],
                                'p' => $p,
                                'start_y' => $start_y,
                                'end_y' => $end_y,
                                'records' => $records,
                                'HeadBalance' => $HeadBalance,
                                'oldheadclosing' => $oldheadclosing,
                                'previousData' => $previousData,                            
                                'branch_id' => $branch_id,
                                'company_id' => $company_id,
                                'financial_year' => $financial_year,
                                'name' => $taxonomy->sub_head,
                                'head_id' => $headId,
                            ])
                            @endif
                            <?php $n1++; ?>
                        @endforeach
                    @endif
                    <?php $n++; ?>
                @endforeach
                @php
                @endphp
            </tbody>
            <tfoot>
                <tr style="font-weight: bold;">
                <td>Total </td>
                        <td>{{  number_format((float)($head_id ? ((count($headArray[$head_id]) > 1) ? ($headopeningsum/2) : $headopeningsum) : $headopeningsum), 2, '.', '')}}</td>
                        <td>{{  number_format((float)$drTotal, 2, '.', '') }}</td>
                        <td>{{  number_format((float)$crTotal, 2, '.', '') }}</td>
                        <td>{{  number_format((float)$headclosingtotalsum, 2, '.', '') }}</td>
                </tr>
            </tfoot>
        </table>
        <div class="container-fluid">
            <div class="btn-block">
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>