@php
    $libalityMainAmount = 0;
    $AssetMainAmount = 0;
    $dataArray = [
                'start_date'=>$start_date,
                'end_date'=>$end_date,
                'branch_id'=>$branch_id,
                'company_id'=>$companyId,
            ];
    $key = '123456789987654321';  
@endphp
<div class="row">
    <div class="col-md-6">
        <div class="d-flex justify-content-between mx-2 mb-2">
            <span class="font-weight-bold text_upper">LIABILITIES &amp; OWNERS' EQUITY</span>
            <span class="font-weight-bold text_upper ">Amount</span>
        </div>
        <div class="card">
            <div class="">
                <table id="capital" class="table datatable-show-all">
                    <tbody>
                        @forelse ($libalityHead[1] as $lib)
                        @php
                            $mainHead = 0;
                            $childHeadIds = explode(',', ($lib->child_id));
                            $childHeadIds = array_unique(explode(',', ($lib->child_id)));

                            $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));
                            $mainHead += array_sum($headTwoTotal);
                            $libalityMainAmount += $totalAmount[$lib->head_id] + $mainHead;
                            $href = 'balance-sheet.head';
                            $headArray = [
                                        'head_id'=>$lib->head_id,
                                        'label'=>$lib->labels,
                                        'direct'=>true
                                    ];
                            $mergedArray = $dataArray + $headArray;                           
                        @endphp
                            <tr>
                                <td class="text_upper">
                                    <a href="{{  route($href = (($lib->head_id == 6 || $lib->head_id == 17) ? 'admin.profit&loss' : 'balance-sheet.head'),$mergedArray) }}" target="_blank" class="{{$lib->labels}}">{{ $lib->sub_head }}</a>
                                </td>
                                <td class="text-dark " style="float:right;">&#x20B9;{{($lib->head_id == 6) ? $expenseAmount :   $mainHead }}</td>
                            </tr>
                            @forelse ($labelThreeHead->get($lib->head_id, []) as $labelThree)
                            @php
                            $headAmouhnt = 0;
                            $childHeadIds = explode(',', ($labelThree->child_id));
                            array_push($childHeadIds, $labelThree->head_id);    
                            $childHeadIds = array_unique($childHeadIds);  
                            $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));
                            $headAmouhnt += array_sum($headTwoTotal);
                            $headArray = [
                                        'head_id'=>$labelThree->head_id,
                                        'label'=>$labelThree->labels,
                                    ];
                            $mergedArray = $dataArray + $headArray;
                            @endphp
                                <tr class="child_inactive" style="background-color: #dff9fb; border: 1px solid #ddd;">
                                    <td class="text_upper">
                                        <a href="{{  route($href,$mergedArray) }}" target="_blank">{{ $labelThree->sub_head }}</a>
                                    </td>
                                    <td class="text-dark " style="float:right;">&#x20B9; {{( $labelThree->head_id == 17) ? $expenseAmount :  $headAmouhnt }}</td>
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
            <span class="font-weight-bold text_upper">ASSETS</span>
            <span class="font-weight-bold text_upper ">Amount</span>
        </div>
        <div class="card">
            <div class="">
                <table id="capital" class="table datatable-show-all">
                    <tbody>
                        @forelse ($assetHead[2] as $lib)
                        @php
                            $mainHeadAsset= 0;
                            $childHeadIds = explode(',', ($lib->child_id));
                            array_push($childHeadIds, $lib->head_id);    
                            $childHeadIds = array_unique($childHeadIds);  


                            $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));
                            $mainHeadAsset += array_sum($headTwoTotal);
                            $AssetMainAmount += $mainHeadAsset;
                            $headArray = [
                                        'head_id'=>$lib->head_id,
                                        'label'=>$lib->labels,
                                    ];
                            $mergedArray = $dataArray + $headArray;
                        @endphp
                            <tr>
                                <td class="text_upper">
                                    <a href="{{  route($href,$mergedArray) }}" target="_blank">{{ $lib->sub_head }}</a>
                                </td>
                                <td class="text-dark " style="float:right;">&#x20B9;{{ $totalAmount[$lib->head_id] + $mainHeadAsset }}</td>
                            </tr>
                            @forelse ($labelThreeHead->get($lib->head_id, []) as $labelThree)
                            @php
                            $assetHeadAmount= 0;
                            $childHeadIds = explode(',', ($labelThree->child_id));
                            array_push($childHeadIds, $labelThree->head_id);  
                           
                            $childHeadIds = array_unique($childHeadIds);  
                          
                            $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));
                            $assetHeadAmount  += array_sum($headTwoTotal);
                            $headArray = [
                                        'head_id'=>$labelThree->head_id,
                                        'label'=>$labelThree->labels,
                                    ];
                            $mergedArray = $dataArray + $headArray;
                            
                            @endphp
                                <tr class="child_inactive" style="background-color: #dff9fb; border: 1px solid #ddd;">
                                    <td class="text_upper">
                                        <a href="{{  route($href,$mergedArray) }}" target="_blank">{{ $labelThree->sub_head }}</a>
                                    </td>
                                    <td class="text-dark" style="float:right;">&#x20B9;{{   $assetHeadAmount}}</td>
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
@include('templates.admin.balance_sheet.footer',['libalityMainAmount'=>$libalityMainAmount,'AssetMainAmount'=>$AssetMainAmount])