<?php $p=$p+25; $no=1?>

@foreach($subcategories->sortBy('sub_head')  as $subcategory)
 <?php 
 $style="color:#021b02;font-weight: 700;";
 if($subcategory->labels==1)
 {
    $style="color:#089a08;font-weight: 900;";
 }
 if($subcategory->labels==2)
 {
    $style="color:#224be1;font-weight: 700;";
 }
 if($subcategory->labels==3)
 {
    $style="color: #ffa500;font-weight: 700;";
 }
 if($subcategory->labels==4)
 {
    $style="color: #6dcef5;font-weight: 700;";
 }
 if($subcategory->labels==5)
 {
    $style="color: #17ef05;font-weight: 700;";
 }
 if($subcategory->labels==6)
 {
    $style="color: #e43636;font-weight: 700;";
 } 

 $subCount=count($subcategory->subcategory);

                                                if($subCount>0)
                                                {
                                                    $idHead1=$subcategory->head_id;
                                                    $getChildID1 = DB::select('call getAllHead(?)',[$idHead1]);

                                                 $childID1=$getChildID1[0]->headVal; 

                                                 $amountDR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'DR');
                                                 $amountCR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'CR');
                                                }
                                                else
                                                {
                                                    $idHead1=$subcategory->head_id; 

                                                     $childID1=$idHead1; 

                                                     $amountDR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'DR');
                                                     $amountCR1=headSumType($idHead1,$childID1,$branch_id,$start_y,$end_y,'CR');
                                                }
 ?>
<tr > 
   <td style="padding-left: {{$p}}px;{{$style}}">{{$no}}. {{$subcategory->sub_head}}</td>
      
     <td>{{  number_format((float)$amountCR1, 2, '.', '') }}</td>
     <td>{{  number_format((float)$amountDR1, 2, '.', '') }}</td>
      
</tr>


if($subCount>0)
            @include('templates.admin.trailbalance.partials.head_tree',['subcategories' => $subcategory->subcategory,'p'=>$p,'start_y' =>$start_y, 'end_y'=>$end_y ])
         @endif
  <?php $no++;?>
@endforeach 


