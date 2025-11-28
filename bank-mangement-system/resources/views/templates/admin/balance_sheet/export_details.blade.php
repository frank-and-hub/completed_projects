<h4>{{ $title }}</h4>
<?php
$finacialYear = getFinacialYear();
$startDatee = date('Y-m-d', strtotime($finacialYear['dateStart']));
$branchIddd = 33;
$globalDate1 = headerMonthAvailability(date('d'), date('m'), date('Y'), $branchIddd);
$endDatee = date('Y-m-d', strtotime(convertDate($globalDate1)));
$head_ids = [$headDetail->head_id];
$subHeadsIDS = App\Models\AccountHeads::where('head_id', $headDetail->head_id)
    ->where('status', 0)
    ->pluck('head_id')
    ->toArray();
if (count($subHeadsIDS) > 0) {
    $head_ids = array_merge($head_ids, $subHeadsIDS);
    $record = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
}
foreach ($record as $key => $value) {
    $HeadIds[] = $value;
}
?>
<div>
    <h6>{{--  $headDetail->sub_head --}}</h6>
</div>
<table>
    <thead>
        @php
            $headCount = getHead($headDetail->head_id, 3);
        @endphp
        <th>{{ $headDetail->sub_head }}</th>
        <th></th>
        <th></th>
        <th></th>
        <th>&#X20B9;{{ number_format((float) (headTotalNew($headDetail->head_id, $start_date, $end_date, $branch_id, $company_id)), 2, '.', '') }}</th>
    </thead>
</table>
<table>
    @if (count($childHead) > 0)
        @foreach ($childHead as $val1)
            @php
                $head4 = getHead($val1->head_id, 4);
                $head5 = getHead($val1->head_id, 5);
            @endphp
            <thead>
                <tr>
                    <th>{{ $val1->sub_head }}</th>
                    <th></th>
                    <th></th>
                    @php $headAmount = getHeadClosingNew($val1->head_id, $start_date); @endphp
                    <th>&#X20B9;{{ number_format((float) ($headAmount ?? headTotalNew($val1->head_id, $start_date, $end_date, $branch_id, $company_id)), 2, '.', '') }}</th>
                    {{-- <th>&#X20B9;{{ number_format((float) ((in_array(17, $HeadIds) || in_array(6, $HeadIds)) ? $profit_loss : headTotalNew($val1->head_id, $start_date, $end_date, $branch_id, $company_id)), 2, '.', '') }}</th> --}}
                </tr>
            </thead>
            @if (count($head4) > 0)
                @foreach ($head4 as $val4)
                    @php
                        $head5 = getHead($val4->head_id, 5);
                        $head_ids = [$val4->head_id];
                        $subHeadsIDS = App\Models\AccountHeads::where('head_id', $val4->parent_id)
                            ->where('status', 0)
                            ->pluck('head_id')
                            ->toArray();
                        if (count($subHeadsIDS) > 0) {
                            $head_ids = array_merge($head_ids, $subHeadsIDS);
                            $record = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
                        }
                        foreach ($record as $key => $value) {
                            $IDSS[] = $value;
                        }
                    @endphp
                    @if ($val4->head_id != '75' && $val4->head_id != '76')
                        <tbody>
                            <tr @if ($val4->status == 1) class="child_inactive" @endif>
                                <td> </td>
                                @if (count($head5) > 0)
                                    <td>{{ $val4->sub_head }}</td>
                                @elseif(in_array(18, $IDSS) || in_array(15, $IDSS))
                                    <td>{{ $val4->sub_head }} </td>
                                @elseif($val4->head_id == 16)
                                    <td> {{ $val4->sub_head }}</td>
                                @elseif(in_array(27, $IDSS))
                                    <th>{{ $val4->sub_head }}</th>
                                @else
                                    <td>{{ $val4->sub_head }} </td>
                                @endif
                                @php $headAmount = getHeadClosingNew($val4->head_id, $start_date); @endphp
                                <th>&#X20B9;{{ number_format((float) ($headAmount??headTotalNew($val4->head_id, $start_date, $end_date, $branch_id, $company_id)), 2, '.', '') }}</th>
                                <td> </td>
                            </tr>
                        </tbody>
                    @endif
                @endforeach
            @endif
        @endforeach
    @endif
</table>
