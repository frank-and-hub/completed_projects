<div class="card pt-3 pb-3">
    <div class="card-header">
        <h3 class="card-title font-weight-semibold"><strong>Name of the Company:</strong> {{$companyDetail->name}} </h3>
        <h6 class="card-title font-weight-semibold pt-2"><strong>Address:</strong> {{$companyDetail->address}}</h6>
@if($branchName)
        <h6 class="card-title font-weight-semibold pt-2"><strong>Branch:</strong> {{$branchName}}</h6>
@endif
    </div>
</div>

<?php 
$start_y=$start_y;
$end_y=$end_y; 
$start_m=$start_m;
$start_d=$start_d;
$end_m=$end_m;
$end_d=$end_d;
$records = $oldheadclosing;
?>

<div class="card">
    <div class="card-header ">
        <h3 class="card-title font-weight-semibold"><strong>Trial Balance</strong></h3>
        <h6 class="card-title font-weight-semibold pt-2">From {{$start_y}} to {{$end_y}}</h6>
    </div> 
    <div class="card-body">     
       
            <table id="head_listing" class="table">
                <thead>
                    <tr>
                        <th>Particulars</th>
                        <th >Opening </th>
                        <th colspan=2 style="text-align:center">Transactions </th>  
                        <th >Closing </th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Balance </th> 
                        <th>Debit </th> 
                        <th>Credit </th>
                        <th>Balance </th>
                    </tr>
                </thead>  
                <tbody>
                    <?php 
                        $p=40;
                        $n=1;
                        $crTotal=0;
                        $drTotal=0;
                        $headArray = [];  
                    ?>
                    @foreach($data as $key => $head)
                        @php                        
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
                            $headclosingtotalsum += $amount; 
                        @endphp
                         <tr>
                            <td style="color:#089a08;font-weight: 900;" class="text_upper">{{ $key + 1 }}. {{ strtoupper($head->sub_head) }}</td>    
                            <td>{{($head->is_trial == 1) ? 0 : $oldHeadClosingAmount}}</td>
                            <td>{{ $drAmount }}</td>
                            <td>{{ $crAmount }}</td>
                            <td>{{ $amount }}</td>
                        </tr>                               
                        @if(count($data)>0)
                        <?php $n1=1; 
                                  $data = $data->reject(function($item) {
                                    return $item->head_id ===6 ;
                                });
                        
                        
                        ?>
                            @foreach($data as $key => $taxonomy)
                                @php
                                    $subCount = count($taxonomy['subcategory']);

                                    $headId = $taxonomy->head_id;
                                    $oldClosingAmount = ( !isset($oldheadclosing[$headId]) ? (isset($previousData[$headId]) ? $previousData[$headId] : 0) : $oldheadclosing[$headId]);
                                                                      

                                    $headBalance = $HeadBalance[$headId] ?? ['dr_amount' => 0, 'cr_amount' => 0];
                                    $drAmount = $headBalance['dr_amount'];
                                    $crAmount = $headBalance['cr_amount'];
                                    $crNature = $taxonomy->cr_nature;
                                    $computedValue =(($taxonomy->is_trial ==1) ? 0 :  $oldClosingAmount) + (($crNature == 1) ? $crAmount - $drAmount : $drAmount - $crAmount);
                                    $cAmlunt = isset($oldheadclosing[$headId]) ?  $oldheadclosing[$headId] :  $previousData[$headId] ?? 0;
                                @endphp

                                    <tr> 
                                        <td style="padding-left: {{ $p }}px; color: #224be1;font-weight: 700;">{{$n1}}. {{strtoupper($taxonomy->sub_head)}}</td> 
                                        <td>{{($taxonomy->is_trial ==1) ? 0 : $cAmlunt}}</td>
                                        <td>{{$drAmount}}</td>
                                        <td>{{$crAmount}}</td>
                                        <td>{{ $computedValue }}</td>
                                    </tr>
                                @if(!$head_id)
                                <?php $subcategories = $taxonomy['subcategory']; $p=$p+25; $no=1 ?>
                                    @foreach($subcategories->sortBy('sub_head')  as $subcategory)
                                        @php
                                            $style = 'color:#021b02;font-weight: 700;';
                                            if ($subcategory->labels == 1) {
                                                $style = 'color:#089a08;font-weight: 900;';
                                            }
                                            if ($subcategory->labels == 2) {
                                                $style = 'color:#224be1;font-weight: 700;';
                                            }
                                            if ($subcategory->labels == 3) {
                                                $style = 'color: #ffa500;font-weight: 700;';
                                            }
                                            if ($subcategory->labels == 4) {
                                                $style = 'color: #6dcef5;font-weight: 700;';
                                            }
                                            if ($subcategory->labels == 5) {
                                                $style = 'color: #17ef05;font-weight: 700;';
                                            }
                                            if ($subcategory->labels == 6) {
                                                $style = 'color: #e43636;font-weight: 700;';
                                            }                                  

                                            $subCount=count($subcategory->subcategory);

                                            if($subCount>0){
                                                $idHead1=$subcategory->head_id;
                                                $getChildID1 = DB::select('call getAllHead(?)',[$idHead1]);

                                                $childID1=$getChildID1[0]->headVal; 

                                                $amountDR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'DR');
                                                $amountCR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'CR');
                                            }else {
                                                $idHead1=$subcategory->head_id; 

                                                $childID1=$idHead1; 

                                                $amountDR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'DR');
                                                $amountCR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'CR');
                                            }
                                        
                                        
                                            $closingAmount = (isset($oldheadclosing[$subcategory->head_id])) ? $oldheadclosing[$subcategory->head_id] : $previousData[$subcategory->head_id] ?? 0;
                                            $drAmount =(isset($HeadBalance[$subcategory->head_id]['dr_amount'])) ? $HeadBalance[$subcategory->head_id]['dr_amount'] : 0;
                                            $crAmount = (isset($HeadBalance[$subcategory->head_id]['cr_amount'])) ? $HeadBalance[$subcategory->head_id]['cr_amount'] : 0;
                                            $lastAmt = ($subcategory->cr_nature == 1) ? ($closingAmount + $crAmount - $drAmount) : ($closingAmount + $drAmount - $crAmount);
                                            $closingAmount = $subcategory->is_trial == 1  ? 0 : $closingAmount;

                                        @endphp
                                        <tr>
                                            <td style="padding-left: {{ $p }}px;{{ $style }}">{{$no}}. {{strtoupper($subcategory->sub_head)}}</td>
                                            <td>{{ $closingAmount }}</td>
                                            <td>{{ $drAmount }}</td>
                                            <td>{{ $crAmount }}</td>
                                            <td>{{ $lastAmt }}</td>
                                        </tr>
                                        <?php $no++;?>
                                    @endforeach 
                                <?php $n1++;?>
                                @endif
                            @endforeach
                        @endif
                        <?php $n++;?>
                    @endforeach
                </tbody> 
                <tfoot>
                    <tr>
                        <td>Total </td>
                        <td>{{  number_format((float)($head_id ? ((count($headArray[$head_id]) > 1) ? ($headopeningsum/2) : $headopeningsum) : $headopeningsum), 2, '.', '')}}</td>
                        
                        <td>{{  number_format((float)$drTotal, 2, '.', '') }}</td>
                        <td>{{  number_format((float)$crTotal, 2, '.', '') }}</td>
                        <td>{{  number_format((float)$headclosingtotalsum, 2, '.', '') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
</div>


