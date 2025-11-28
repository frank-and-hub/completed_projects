
<div class="col-md-12 mt-2">
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">
                @php
                $headCount = getHead($headDetail->head_id, $label = 4);
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
                @endphp
                @if (isset($headDetail->head_id))
                <th class="text_upper">
                    <a href="{{ route($href, $mergedArray) }}" target="_blank">{{ $headDetail->sub_head }}</a>
                </th>
                @else
                {{ number_format((float) headTotalNew($headDetail->head_id, $start_date, $end_date, $branch_id,
                $company_id), 2, '.', '') }}
                @endif
            </h6>
            <h6 class="card-title font-weight-semibold" id="afff">
                {{ number_format((float) headTotalNew($headDetail->head_id, $start_date, $end_date, $branch_id,
                $company_id), 2, '.', '') }}
            </h6>
        </div>
        <table class="table  datatable-show-all">
            @if (count($childHead) > 0)
            @foreach ($childHead as $value)
            @php
            $Head = getHead($value->head_id, 4);
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
            @endphp
            <thead>
                <tr @if ($value->status == 1) class ="child_inactive" @endif>
                    @if (count($Head) > 0)
                    <th class="text_upper">
                        <a href="{{ route($href, $mergedArray) }}" target="_blank">{{ $value->sub_head }}</a>
                    </th>
                    @else
                    <td class="text_upper">
                        <a href="{{ route($href, $mergedArray) }}" target="_blank">{{ $value->sub_head }}</a>
                    </td>
                    @endif
                    <th></th>
                    <th></th>
                    @if (in_array(17, $child) || in_array(6, $child))
                    <th>&#X20B9;{{ number_format((float) $profit_loss, 2, '.', '') }}</th>
                    @else
                    @php $t_amount =+ headTotalNew($value->head_id, $start_date, $end_date, $branch_id,$company_id);
                    @endphp
                    <th>&#X20B9;{{ number_format((float) headTotalNew($value->head_id, $start_date, $end_date,
                        $branch_id, $company_id), 2, '.', '') }}
                    </th>
                    @endif
                </tr>
            </thead>
            @endforeach
            @php
            $head6 = getHead($headDetail->head_id, $headDetail->labels + 1);
            @endphp
            @elseif(isset($head6) && count($head6) > 0)
            @foreach ($head6 as $headLabel)
            <td class="text_upper">
                @php
                $headArray = [
                'head_id' => $headLabel->head_id,
                'branch_id' => $branch_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'label' => $headLabel->labels,
                'company_id' => $company_id,
                ];
                $mergedArray = Crypt::encrypt($headArray, $key);
                @endphp
                <a href="{{ route($href, $mergedArray) }}" target="_blank">{{ $headLabel->sub_head }}</a>
            </td>
            @endforeach
            @endif
        </table>
    </div>
</div>