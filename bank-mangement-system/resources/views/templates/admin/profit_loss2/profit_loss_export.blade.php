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
  width: 100%; /* Adjust the width as needed */
  margin: 10px; /* Adjust the margin as needed */
}
    </style>

        <div class="table-wrapper" style=" display: flex;flex-direction: column;">
        <table  class="table1" >
            <table>
                <thead>
                    <tr>
                        <th colspan="4" style="font-weight: bold; text-transform: uppercase;">INCOME</th>
                        <th  style="font-weight: bold; text-transform: uppercase;">Amount</th>
                    </tr>
                 
                </thead>
                <tbody>
                    @forelse ($data['libalityHead'][3] as $liability)
                        @php
                            $mainHead = 0;
                            $childHeadIds = explode(',', ($liability->child_id));
                                array_push($childHeadIds, $liability->head_id);    
                                $childHeadIds = array_unique($childHeadIds);  

                            $headTwoTotal = array_intersect_key($data['totalAmount'], array_flip($childHeadIds));
                            $mainHead += array_sum($headTwoTotal);
                            $liabilityMainAmount += $mainHead;
                        @endphp
                        <tr>
                            <td style="text-transform: uppercase;background-color: #dff9fb;" colspan="4">{{ $liability->sub_head }}</td>
                            <td style="text-align: center;background-color: #dff9fb;">&#x20B9;{{  $mainHead }}</td>
                        </tr>
                        @forelse ($data['labelThreeHead']->get($liability->head_id, []) as $labelThree)
                            @php
                                $headAmount = 0;
                                $childHeadIds = explode(',', ($labelThree->child_id));
                                array_push($childHeadIds, $labelThree->head_id);    
                                $childHeadIds = array_unique($childHeadIds);  


                                $headTwoTotal = array_intersect_key($data['totalAmount'], array_flip($childHeadIds));
                                $headAmount += array_sum($headTwoTotal);
                            @endphp
                            <tr style="background-color: #dff9fb;">
                                <td style="text-transform: uppercase;"  colspan="4">{{ $labelThree->sub_head }}</td>
                                <td style="text-align: center;">&#x20B9;{{  $headAmount }}</td>
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
                        <th colspan="4" style="font-weight: bold; text-transform: uppercase;">EXPENSE</th>
                        <th style="font-weight: bold; text-transform: uppercase;">Amount</th>

                    </tr>
                    
                </thead>
                <tbody>
                    @forelse ($data['assetHead'][4] as $asset)
                        @php
                            $mainHeadAsset = 0;
                            $childHeadIds = explode(',', ($asset->child_id));
                                array_push($childHeadIds, $asset->head_id);    
                                $childHeadIds = array_unique($childHeadIds);  
                            $headTwoTotal = array_intersect_key($data['totalAmount'], array_flip($childHeadIds));
                            $mainHeadAsset += array_sum($headTwoTotal);
                            $assetMainAmount += $mainHeadAsset;
                        @endphp
                        <tr>
                            <td style="text-transform: uppercase;background-color: #dff9fb;"  colspan="4">{{ $asset->sub_head }}</td>
                            <td style="text-align: center;background-color: #dff9fb;" >&#x20B9;{{ $mainHeadAsset }}</td>
                        </tr>
                        @forelse ($data['labelThreeHead']->get($asset->head_id, []) as $labelThree)
                            @php
                                $assetHeadAmount = 0;
                                $childHeadIds = explode(',', ($labelThree->child_id));
                                array_push($childHeadIds, $labelThree->head_id);    
                                $childHeadIds = array_unique($childHeadIds);  
                                $headTwoTotal = array_intersect_key($data['totalAmount'], array_flip($childHeadIds));
                                $assetHeadAmount += array_sum($headTwoTotal);
                            @endphp
                            <tr style="background-color: #dff9fb;">
                                <td style="text-transform: uppercase;" colspan="4">{{ $labelThree->sub_head }}</td>
                                <td style="text-align: center;">&#x20B9;{{  $assetHeadAmount }}</td>
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
                  <th class="text_upper"   colspan="4">Total Income</th>
                  <th>&#X20B9;{{$liabilityMainAmount  }}</th>                
                </tr>
            </thead>
        </table>

        <table id="total_assets" class="table datatable-show-all">
          <thead>
            <tr>
              <th class="text_upper"   colspan="4">Total Expense[E]</th>
              <th>&#X20B9;{{$assetMainAmount}}</th>              
            </tr>
          </thead>
        </table>

 
</div>
