<div class="card pt-3 pb-3">
    <div class="card-header">
        <h3 class="card-title font-weight-semibold"><strong>Name of the Company:</strong> Samraddh Bestwin Micro Finance Association</h3>
        <h6 class="card-title font-weight-semibold pt-2"><strong>Address:</strong> Corp. Office: 114-115, Pushp Enclave, Sector -5, Pratap Nagar, Tonk Road</h6>
    </div> 
    
</div>

<?php 
$start_y=$start_y;
$end_y=$end_y; 
$start_m=$start_m;
$start_d=$start_d;
$end_m=$end_m;
$end_d=$end_d;
// $record  = DB::select('call headOpeningBalance(?,?)',[$newstartDate,$newEndDate]);
// $records = array_column($record, "amount", "head_id");

$records = $oldheadclosing;

?>
<style>
    table thead tr:nth-child(1){
        border: 1px solid #a1a1a1;
    }

    table thead tr:nth-child(1) th{
        border: 1px solid #a1a1a1;
    }
   
    table thead tr:nth-child(1) th{
        border-bottom: hidden;
    }

    table thead tr:nth-child(2){
        border: 1px solid #a1a1a1;
    }

    table thead tr:nth-child(2) th{
        border: 1px solid #a1a1a1;
    }
   
    
    
</style>
<div class="card">
    <div class="card-header ">
        <h3 class="card-title font-weight-semibold"><strong>Trial Balance</strong></h3>
        <h6 class="card-title font-weight-semibold pt-2">From {{$start_y}} to {{$end_y}}</h6>
    </div> 
    <div class="card-body">
        <form id="trialbalanceformdata" method="POST" enctype="multipart/form-data" action="{{route('admin.trail_balance.updatechangedata')}}">
             @csrf
            <input type="hidden" name="start_y" id="start_y" value="{{$start_y}}" >
            <input type="hidden" name="end_y" id="end_y"  value="{{$end_y}}" >
            <input type="hidden" name="branch_id" id="branch_id"  value="{{$branch_id}}" >
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
                    // echo $branch_id;die();
                        $p=40;
                        $n=1;
                        $crTotal=0;
                        $drTotal=0;
                    ?>
                    @foreach($data as $key => $head)
                        <?php 
                        //dd($record[$key]->amount,$head);
                        $idHead=$head->head_id;
                        $data=headTree($idHead);
                        $getChildID = DB::select('call getAllHead(?)',[$idHead]);
                        $childID=$getChildID[0]->headVal; 
                        $amountDR=headSumType($idHead,$childID,$branch_id,$start_y,$end_y,'DR');
                        $amountCR=headSumType($idHead,$childID,$branch_id,$start_y,$end_y,'CR');
                        $crTotal=$crTotal+$amountCR;
                        $drTotal=$drTotal+$amountDR;
                        $balanceTotal=$crTotal-$drTotal;

                        // New Procedure Called
                        // $getChildHeadData = DB::select('call getChildHeadAccountHead(?)', [$idHead]);
                        ?>
                        <tr >
                            <td style="color:#089a08;font-weight: 900;">{{$n}}. {{$head->sub_head}}</td>    
                            <td>0</td>
                            <td>0</td>       
                            <td>0</td>
                            <td>0</td>
                        </tr>                                        
                        @if(count($data)>0)
                        <?php $n1=1;?>
                            @foreach($data as $key=>$taxonomy)
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
                                <td> 0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                            @if($subCount>0)
                                @include('templates.admin.trailbalance.partials.head_tree',['subcategories' => $taxonomy->subcategory,'p'=>$p,'start_y' =>$start_y, 'end_y'=>$end_y,'records'=>$records])
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
                        <td>{{number_format((float)$headopeningsum, 2, '.', '')}} </td>
                        <td>{{  number_format((float)$drTotal, 2, '.', '') }}</td>
                        <td>{{  number_format((float)$crTotal, 2, '.', '') }}</td>
                        <td>{{  number_format((float)$headclosingtotalsum, 2, '.', '') }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="container-fluid">
                <div class="btn-block">         
                <button type="button" class="btn bg-dark legitRipple export ml-2 closefinancialyear" style="float: right;">Close Financial Year</button> 
                <button type="submit" class="btn bg-dark legitRipple export ml-2 updateheaddata" style="float: right;">Update Head Data</button>
                </div>
            </div>
        </form>
    </div>
</div>


