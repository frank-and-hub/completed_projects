@php
    $liabilityMainAmount = 0;
    $assetMainAmount = 0;
@endphp
<style>
    .table-wrapper {
        display: flex;
    }

    .table1,
    .table2 {
        width: 100%;
        /* Adjust the width as needed */
        margin: 10px;
        /* Adjust the margin as needed */
    }
</style>
<div class="table-wrapper" style=" display: flex;flex-direction: column;">

    @if (isset($data['branchName']))
        <table>
            <tr>
                <th>Branch : {{ $data['branchName'] }}</th>
            </tr>
            
        </table>
    @endif

    <table class="table1">
        <table>
            <thead>
                <tr>
                    <th colspan="4" style="font-weight: bold; text-transform: uppercase;">LIABILITIES &amp; OWNERS' EQUITY</th>
                    <th colspan="4" style="font-weight: bold; text-transform: uppercase;">Opening</th>
                    <th style="font-weight: bold; text-transform: uppercase;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['libalityHead'][1] as $liability)
                    @php
                        $mainHead = 0;
                        $childHeadIds = explode(',', $liability->child_id);
                        array_push($childHeadIds, $liability->head_id);
                        $childHeadIds = array_unique($childHeadIds);
                        $headTwoTotal = array_intersect_key($data['totalAmount'], array_flip($childHeadIds));
                        $mainHead += array_sum($headTwoTotal);
                        $liabilityMainAmount += $mainHead;    
                        $headArray = [
                                'head_id'=>$liability->head_id,
                                'label'=>$liability->labels,
                                'direct'=>true
                            ];
                    @endphp
                    <tr>
                        <td style="text-transform: uppercase;background-color: #dff9fb;" colspan="4">{{ $liability->sub_head }}</td>
                        <td class="text-dark" colspan="4">{{ isset($data['oldheadclosing'][$liability->head_id]) ? $data['oldheadclosing'][$liability->head_id]['amount'] : $data['amountNew'][$liability->head_id] ?? 0 }}</td>
                        <th class="text-dark text-center">{{ $liability->head_id == 6 ? $data['expenseAmount'] + (isset($data['oldheadclosing'][$liability->head_id]) ? $data['oldheadclosing'][$liability->head_id]['amount'] : $data['amountNew'][$liability->head_id] ?? 0) : $mainHead + (isset($data['oldheadclosing'][$liability->head_id]) ? $data['oldheadclosing'][$liability->head_id]['amount'] : $data['amountNew'][$liability->head_id] ?? 0) }}</th>
                    </tr>
                    @forelse ($data['labelThreeHead']->get($liability->head_id, []) as $labelThree)
                        @php
                            $headAmount = 0;
                            $childHeadIds = explode(',', $labelThree->child_id);
                            array_push($childHeadIds, $labelThree->head_id);
                            $childHeadIds = array_unique($childHeadIds);
                            $headTwoTotal = array_intersect_key($data['totalAmount'], array_flip($childHeadIds));
                            $headAmount += array_sum($headTwoTotal);
                        @endphp
                        <tr style="background-color: #dff9fb;">
                            <td style="text-transform: uppercase;" colspan="4">{{ $labelThree->sub_head }}</td>
                            <td class="text-dark" colspan="4">
                                {{ isset($data['oldheadclosing'][$labelThree->head_id]) ? $data['oldheadclosing'][$labelThree->head_id]['amount'] : $data['amountNew'][$labelThree->head_id] ?? 0 }}
                            </td>

                            <td style="text-align: center;">
                                {{ $labelThree->head_id == 17 ? $data['expenseAmount'] + (isset($data['oldheadclosing'][$labelThree->head_id]) ? $data['oldheadclosing'][$labelThree->head_id]['amount'] : $data['amountNew'][$labelThree->head_id] ?? 0) : $headAmount + (isset($data['oldheadclosing'][$labelThree->head_id]) ? $data['oldheadclosing'][$labelThree->head_id]['amount'] : $data['amountNew'][$labelThree->head_id] ?? 0) }}
                            </td>
                        </tr>
                    @empty
                        <!-- No labelThreeHead items -->
                    @endforelse
                @empty
                    <!-- No liabilityHead items -->
                @endforelse
            </tbody>
        </table>
        <table>
            <thead>
                <tr>
                    <th colspan="4" style="font-weight: bold; text-transform: uppercase;">ASSETS</th>
                    <th colspan="4" style="font-weight: bold; text-transform: uppercase;">Opening</th>

                    <th style="font-weight: bold; text-transform: uppercase;">Amount</th>

                </tr>

            </thead>
            <tbody>
                @forelse ($data['assetHead'][2] as $asset)
                    @php
                        $mainHeadAsset = 0;
                        $childHeadIds = explode(',', $asset->child_id);
                        array_push($childHeadIds, $asset->head_id);
                        $childHeadIds = array_unique($childHeadIds);
                        
                        $headTwoTotal = array_intersect_key($data['totalAmount'], array_flip($childHeadIds));
                        $mainHeadAsset += array_sum($headTwoTotal);
                        $assetMainAmount += $mainHeadAsset;
                    @endphp
                    <tr>
                        <td style="text-transform: uppercase;background-color: #dff9fb;" colspan="4">
                            {{ $asset->sub_head }}</td>
                        <td class="text-dark" colspan="4">
                            {{ isset($data['oldheadclosing'][$asset->head_id]) ? $data['oldheadclosing'][$asset->head_id]['amount'] : $data['amountNew'][$asset->head_id] ?? 0 }}
                        </td>

                        <td style="text-align: center;background-color: #dff9fb;">
                            {{ $mainHeadAsset + (isset($data['oldheadclosing'][$asset->head_id]) ? $data['oldheadclosing'][$asset->head_id]['amount'] : $data['amountNew'][$asset->head_id] ?? 0) }}
                        </td>
                    </tr>
                    @forelse ($data['labelThreeHead']->get($asset->head_id, []) as $labelThree)
                        @php
                            $assetHeadAmount = 0;
                            $childHeadIds = explode(',', $labelThree->child_id);
                            array_push($childHeadIds, $labelThree->head_id);
                            $childHeadIds = array_unique($childHeadIds);
                            array_push($childHeadIds, $labelThree->head_id);
                            $headTwoTotal = array_intersect_key($data['totalAmount'], array_flip($childHeadIds));
                            $assetHeadAmount += array_sum($headTwoTotal);
                        @endphp
                        <tr style="background-color: #dff9fb;">
                            <td style="text-transform: uppercase;" colspan="4">{{ $labelThree->sub_head }}</td>
                            <td class="text-dark" colspan="4">
                                {{ isset($data['oldheadclosing'][$labelThree->head_id]) ? $data['oldheadclosing'][$labelThree->head_id]['amount'] : $data['amountNew'][$labelThree->head_id] ?? 0 }}
                            </td>

                            <td style="text-align: center;">
                                {{ $assetHeadAmount + (isset($data['oldheadclosing'][$labelThree->head_id]) ? $data['oldheadclosing'][$labelThree->head_id]['amount'] : $data['amountNew'][$labelThree->head_id] ?? 0) }}
                            </td>
                        </tr>
                    @empty
                        <!-- No labelThreeHead items -->
                    @endforelse
                @empty
                    <!-- No assetHead items -->
                @endforelse
            </tbody>
        </table>
    </table>

    <table id="total_liability" class="table datatable-show-all">
        <thead>
            <tr>
                <th class="text_upper" colspan="4">Total Liabilities</th>
                <th>{{ $liabilityMainAmount + $data['expenseAmount'] }}</th>
            </tr>
        </thead>
    </table>

    <table id="total_assets" class="table datatable-show-all">
        <thead>
            <tr>
                <th class="text_upper" colspan="4">Total Assets[E]</th>
                <th>{{ $assetMainAmount }}</th>
            </tr>
        </thead>
    </table>


</div>
