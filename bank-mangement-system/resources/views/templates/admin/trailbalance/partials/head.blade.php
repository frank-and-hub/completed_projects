<?php 
$start_y=$start_y;
$end_y=$end_y; 
$start_m=$start_m;
$start_d=$start_d;
$end_m=$end_m;
$end_d=$end_d;
?>
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-semibold">Head Closing List ({{$start_y}} - {{$end_y}})</h6>
    </div> 
    <div class="card-body">
        <input type="hidden" name="start_y" id="start_y" value="{{$start_y}}" >
        <input type="hidden" name="end_y" id="end_y"  value="{{$end_y}}" >
        <input type="hidden" name="branch_id" id="branch_id"  value="{{$branch_id}}" >
        <table id="head_listing" class="table">
            <thead>
                <tr>
                    <th>Head Name</th>
                    <th style="width: 140px">DR </th> 
                    <th style="width: 140px">CR </th>  
                    <th style="width: 140px">Balance </th>
                </tr>
            </thead>  
            <tbody>
                <?php 
                // echo $branch_id;die();
                    $p=40;
                    $n=1;
                    $crTotal=0;
                    $drTotal=0;
                ?>
                @foreach($data as $head)
                    <?php 
                    $idHead=$head->head_id;
                    $data=headTree($idHead);
                    $getChildID = DB::select('call getAllHead(?)',[$idHead]);
                    $childID=$getChildID[0]->headVal; 
                    $amountDR=headSumType($idHead,$childID,$branch_id,$start_y,$end_y,'DR');
                    $amountCR=headSumType($idHead,$childID,$branch_id,$start_y,$end_y,'CR');
                    $crTotal=$crTotal+$amountCR;
                    $drTotal=$drTotal+$amountDR;
                    $balanceTotal=$crTotal-$drTotal;
                    ?>
                    <tr >
                        <td style="color:#089a08;font-weight: 900;">{{$n}}. {{$head->sub_head}}</td>    
                        <td>{{  number_format((float)$amountDR, 2, '.', '') }}</td>       
                        <td>{{  number_format((float)$amountCR, 2, '.', '') }}</td>
                        <td>{{  number_format((float)$amountCR-$amountDR, 2, '.', '') }}</td>
                    </tr>                                        
                    @if(count($data)>0)
                    <?php $n1=1;?>
                        @foreach($data as $taxonomy)
                        <?php $subCount =count($taxonomy->subcategory);
                            if($subCount>0)
                            {
                                $idHead1=$taxonomy->head_id;
                                $getChildID1 = DB::select('call getAllHead(?)',[$idHead1]);
                                $childID1=$getChildID1[0]->headVal; 
                                $amountDR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'DR');
                                $amountCR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'CR');
                            }
                            else
                            {
                                $idHead1=$taxonomy->head_id; 
                                $childID1=$idHead1; 
                                $amountDR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'DR');
                                $amountCR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'CR');
                            }
                        ?>

                        <tr> 
                            <td style="padding-left: {{$p}}px; color: #224be1;font-weight: 700;">{{$n1}}. {{$taxonomy->sub_head}}</td> 
                            <td>{{  number_format((float)$amountDR1, 2, '.', '') }}</td>
                            <td>{{  number_format((float)$amountCR1, 2, '.', '') }}</td>
                            <td>{{  number_format((float)$amountCR1-$amountDR1, 2, '.', '') }}</td>
                        </tr>
                        @if($subCount>0)
                            @include('templates.admin.trailbalance.partials.head_tree',['subcategories' => $taxonomy->subcategory,'p'=>$p,'start_y' =>$start_y, 'end_y'=>$end_y])
                        @endif
                        <?php $n1++;?>
                        @endforeach
                    @endif
                <?php $n++;?>
            @endforeach
            </tbody> 
            <tfoot>
                <tr style="font-weight: bold;">
                    <td>Total </td>
                    <td>{{  number_format((float)$drTotal, 2, '.', '') }}</td>
                    <td>{{  number_format((float)$crTotal, 2, '.', '') }}</td>
                    <td>{{  number_format((float)$balanceTotal, 2, '.', '') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>


