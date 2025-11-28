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
            <span class="font-weight-bold text_upper  mr-5">Opening</span>
            <span class="font-weight-bold text_upper  mr-5">Amount</span>
        </div>
        <div class="card">
            <div class="">
                <table id="capital" class="table datatable-show-all">
                    <tbody>
                        @forelse ($libalityHead[1] as $lib)
                        @php
                            $mainHead = 0;
                            $childHeadIds = explode(',', ($lib->child_id));
                            array_push($childHeadIds, $lib->head_id);                             
                            $childHeadIds = array_unique($childHeadIds);                            
                            $headTwoTotal = array_intersect_key($totalAmount, array_flip($childHeadIds));                            
                            $mainHead += array_sum($headTwoTotal);                            
                            $libalityMainAmount +=   $mainHead;                            
                            $href = 'balance-sheet.head';
                            $headArray = [
                                        'head_id'=>$lib->head_id,
                                        'label'=>$lib->labels,
                                        'direct'=>true
                                    ];
                            $mergedArray = $dataArray + $headArray;                           
                        @endphp
                            <tr>
                                <td class="text_upper"><a href="{{  route($href = (($lib->head_id == 6 || $lib->head_id == 17) ? 'admin.profit&loss' : 'balance-sheet.head'),$mergedArray) }}" target="_blank" class="{{$lib->labels}}">{{ $lib->sub_head }}</a></td>
                                <td class="text-dark ">{{(isset($oldheadclosing[$lib->head_id]) ? $oldheadclosing[$lib->head_id]['amount']: $amountNew[$lib->head_id] ?? 0)}}</td>
                                <th class="text-dark text-center">{{($lib->head_id == 6) ? $expenseAmount + (isset($oldheadclosing[$lib->head_id]) ? $oldheadclosing[$lib->head_id]['amount']: $amountNew[$lib->head_id] ?? 0) :   $mainHead + (isset($oldheadclosing[$lib->head_id]) ? $oldheadclosing[$lib->head_id]['amount']: $amountNew[$lib->head_id] ?? 0) }}</th>
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
                            if($labelThree->head_id == 31)
                            {
                                
                            }
                          
                            @endphp
                                <tr class="child_inactive" style="background-color: #dff9fb;">
                                    <td class="text_upper"><a href="{{  route($href,$mergedArray) }}" target="_blank">{{ $labelThree->sub_head }}</a></td>
                                    <td class="text-dark">{{(isset($oldheadclosing[$labelThree->head_id]) ? $oldheadclosing[$labelThree->head_id]['amount']: $amountNew[$labelThree->head_id] ?? 0)}}</td>
                                    <th class="text-dark text-center"> 
                                        {{( $labelThree->head_id == 17) 
                                        ? $expenseAmount + (isset($oldheadclosing[$labelThree->head_id]) 
                                            ? $oldheadclosing[$labelThree->head_id]['amount'] 
                                            : $amountNew[$labelThree->head_id] ?? 0) 
                                        : $headAmouhnt + (isset($oldheadclosing[$labelThree->head_id]) 
                                            ? $oldheadclosing[$labelThree->head_id]['amount'] 
                                            : $amountNew[$labelThree->head_id] ?? 0) }}
                                    </th>
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
            <span class="font-weight-bold text_upper" style="padding-left:148px;">Opening </span>
            <span class="font-weight-bold text_upper  mr-5">Amount</span>
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
                                <td class="text-dark">{{(isset($oldheadclosing[$lib->head_id]) ? $oldheadclosing[$lib->head_id]['amount']: $amountNew[$lib->head_id] ?? 0)}}</td>
                                <th class="text-dark text-center">{{ $totalAmount[$lib->head_id] + $mainHeadAsset + (isset($oldheadclosing[$lib->head_id]) ? $oldheadclosing[$lib->head_id]['amount']: $amountNew[$lib->head_id] ?? 0) }}</th>
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
                                <tr class="child_inactive" style="background-color: #dff9fb;">
                                    <td class="text_upper">
                                        <a href="{{  route($href,$mergedArray) }}" target="_blank">{{ $labelThree->sub_head }}</a>
                                    </td>
                                    <td class="text-dark ">{{(isset($oldheadclosing[$labelThree->head_id]) ? $oldheadclosing[$labelThree->head_id]['amount']: $amountNew[$labelThree->head_id] ?? 0)}}</td>

                                    <th class="text-dark text-center">{{   $assetHeadAmount + (isset($oldheadclosing[$labelThree->head_id]) ? $oldheadclosing[$labelThree->head_id]['amount']: $amountNew[$labelThree->head_id] ?? 0)}}</th>
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