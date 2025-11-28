<?php
    $libalityMainAmount = 0;
    $AssetMainAmount = 0;
    $dataArray = [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'branch_id' => $branch_id,
        'company_id' => $companyId,
    ];
    $key = '123456789987654321';
?>
<div class="row">
    <div class="col-md-6">
        <div class="d-flex justify-content-between mx-2 mb-2">
            <span class="font-weight-bold text_upper">INCOME</span>
            <span class="font-weight-bold text_upper">Amount</span>
        </div>
        <div class="card">
            <div class="">
                <table id="capital" class="table datatable-show-all">
                    <tbody>
                        @forelse ($incomeHead[3] as $lib)
                            <?php
                                $mainHead = 0;
                                $childHeadIds = explode(',', ($lib->child_id));
                                array_push($childHeadIds, $lib->head_id);
                                $childHeadIds = array_unique($childHeadIds);
                                $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));                                
                                $mainHead += array_sum($headTwoTotal);
                                $libalityMainAmount += $mainHead;
                                $href = 'admin.profit_losss.page';
                                $headArray = [
                                    'head_id' => $lib->head_id,
                                    'label' => $lib->labels,
                                ];
                                $mergedArray = $dataArray + $headArray;
                            ?>
                            <tr>
                                <td class="text_upper">
                                    <a href="{{ route($href, $mergedArray) }}" target="_blank" class="{{ $lib->labels }}"> {{ $lib->sub_head }} </a>
                                </td>
                                <th class="text-dark" style="float:right;"> &#x20B9; {{ $lib->head_id == 6 ? $expenseAmount : $mainHead }}</th>
                            </tr>
                            @forelse ($labelThreeHead->get($lib->head_id, []) as $labelThree)
                                <?php
                                    $headAmouhnt = 0;                                    
                                    $childHeadIds = explode(',', ($labelThree->child_id));
                                    array_push($childHeadIds, $labelThree->head_id);    
                                    $childHeadIds = array_unique($childHeadIds);
                                    $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));                                    
                                    $headAmouhnt += array_sum($headTwoTotal);
                                    $headArray = [
                                        'head_id' => $labelThree->head_id,
                                        'label' => $labelThree->labels,
                                    ];
                                    $mergedArray = $dataArray + $headArray;
                                ?>
                                <tr class="child_inactive" style="background-color: #dff9fb;">
                                    <td class="text_upper">
                                        <a href="{{ route($href, $mergedArray) }}" target="_blank">{{ $labelThree->sub_head }} </a>
                                    </td>
                                    <th class="text-dark" style="float:right;"> &#x20B9; {{  $headAmouhnt }}</th>
                                </tr>
                            @empty
                            @endforelse
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="d-flex justify-content-between mx-2 mb-2">
            <span class="font-weight-bold text_upper">EXPENSE</span>
            <span class="font-weight-bold text_upper ">Amount</span>
        </div>
        <div class="card">
            <div class="">
                <table id="capital" class="table datatable-show-all">
                    <tbody>
                        @forelse ($expenseHead[4] as $lib)
                            <?php
                                $mainHead = 0;
                                $childHeadIds = explode(',', ($lib->child_id));
                                array_push($childHeadIds, $lib->head_id);
                                $childHeadIds = array_unique($childHeadIds);
                                $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));                                
                                $mainHead += array_sum($headTwoTotal);
                                $AssetMainAmount += $mainHead;
                                $headArray = [
                                    'head_id' => $lib->head_id,
                                    'label' => $lib->labels,
                                ];
                                $mergedArray = $dataArray + $headArray;
                            ?>
                            <tr>
                                <td class="text_upper">
                                    <a href="{{ route($href, $mergedArray) }}" target="_blank"> {{ $lib->sub_head }} </a>
                                </td>
                                <th class="text-dark" style="float:right;"> &#x20B9; {{ $mainHead }}</th>
                            </tr>
                            @forelse ($labelThreeHead->get($lib->head_id, []) as $labelThree)
                                <?php
                                    $headAmouhnt = 0;                                    
                                    $childHeadIds = explode(',', ($labelThree->child_id));
                                    array_push($childHeadIds, $labelThree->head_id);    
                                    $childHeadIds = array_unique($childHeadIds);
                                    $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));                                    
                                    $headAmouhnt += array_sum($headTwoTotal);
                                    $headArray = [
                                        'head_id' => $labelThree->head_id,
                                        'label' => $labelThree->labels,
                                    ];
                                    $mergedArray = $dataArray + $headArray;                                  
                                ?>
                                <tr class="child_inactive" style="background-color: #dff9fb;">
                                    <td class="text_upper">
                                        <a href="{{ route($href, $mergedArray) }}" target="_blank"> {{ $labelThree->sub_head }} </a>
                                    </td>
                                    <th class="text-dark" style="float:right;"> &#x20B9; {{ $headAmouhnt }} </th>
                                </tr>
                            @empty
                            @endforelse
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.profit_loss.footer', [
    'libalityMainAmount' => $libalityMainAmount,
    'AssetMainAmount' => $AssetMainAmount,
])
