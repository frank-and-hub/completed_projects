<h4 class="card-title font-weight-semibold">{{$title}}</h4>

<?php
$t_amount = 0;
$finacialYear=getFinacialYear();
$startDatee=date("Y-m-d", strtotime($finacialYear['dateStart']));
$branchIddd = 33;
$globalDate1 = headerMonthAvailability(date('d'),date('m'),date('Y'),$branchIddd);
$endDatee = date("Y-m-d", strtotime(convertDate($globalDate1)));
 ?>
 
 
 <div class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header header-elements-inline">
					<h6 class="card-title font-weight-semibold">{{$headDetail->sub_head}}</h6> 
					<h6 class="card-title font-weight-semibold" id="a"></h6> 
					
				</div>
				<table class="table  datatable-show-all">
				
			@if(count($childHead)>0)
				@foreach ($childHead as $val1) 
					<thead>
						<tr @if($val1->status==1) class="child_inactive" @endif >
							<?php  $head4= getHead($val1->head_id,4);?>
							@if($val1->head_id==17)
				  <th class="text_upper"><b>{{$val1->sub_head}}</b></th>

				@elseif($val1->head_id==15)
					<th class="text_upper"><b>{{$val1->sub_head}}</b></th>

				@elseif($val1->head_id==18)
					<th class="text_upper"><b>{{$val1->sub_head}}</b></th>   

				@elseif($val1->head_id==19)
					<th class="text_upper"  target="_blank"><b>{{$val1->sub_head}}</b></th> 

					
				

				@elseif($val1->head_id==20 || $val1->head_id == 21 || $val1->head_id==22)
						<th class="text_upper">{{$val1->sub_head}}</th> 
				@elseif($val1->head_id==23 || $val1->head_id == 24 || $val1->head_id==112 || $val1->head_id==25 ||$val1->head_id==27 || $val1->head_id==28 || $val1->head_id==29 || $val1->head_id==89)
				  
				  
					@if(count($head4)>0)
					<th class="text_upper">{{$val1->sub_head}}</th>
					@elseif($val1->head_id == 28)
					  <th class="text_upper">{{$val1->sub_head}}</th>
					@else
					  <th class="text_upper">{{$val1->sub_head}}</th>
					@endif

				@else
				
					<th class="text_upper" target="_blank">{{$val1->sub_head}}</th>

				@endif
				<!--  -->
							<th></th>
							<th></th>

							@if($val1->head_id==17)
								<?php $t_amount =$t_amount + $profit_loss; ?>
								<th>{{ number_format((float)$profit_loss, 2, '.', '')}}</th>
							@else
								<?php $t_amount = $t_amount +  headTotalFilterData($val1->head_id,'head3',$date_filter,$branch_filter,$end_date_filter); ?>
								<th>{{ number_format((float)headTotalFilterData($val1->head_id,'head3',$date_filter,$branch_filter,$end_date_filter), 2, '.', '')}}</th>
							@endif

							
						</tr>
					</thead>
					<?php  $head4= getHead($val1->head_id,4);?>
	   
					@if(count($head4)>0)
						@foreach ($head4 as $val4)  
						 <?php  $head5= getHead($val4->head_id,5);
						   ;
						  ?>
					@if( ($val4->head_id!= "75") &&  ($val4->head_id!= "76"))	  
					<tbody>
					<tr  @if($val4->status==1) class="child_inactive" @endif >
					  <td> </td>
					  @if($val1->head_id==15)
						<td class="text_upper">{{$val4->sub_head}}</td>

					   @elseif($val1->head_id==18)
						<td class="text_upper">{{$val4->sub_head}}</td> 	

						@elseif($val1->head_id==19)
						<td class="text_upper">{{$val4->sub_head}}</td>

						@elseif($val1->head_id==20 || $val1->head_id == 21 || $val1->head_id==22 || $val1->head_id==25)
						@if(count($head5)>0)
						<td class="text_upper">{{$val4->sub_head}}</td>
						@else
						<td class="text_upper">{{$val4->sub_head}}</td>
						@endif


						 @elseif($val1->head_id==23 || $val1->head_id == 24 || $val1->head_id==112  || $val1->head_id == 29 )
						<td class="text_upper">{{$val4->sub_head}}</td>

						
					  @else
					  <td class="text_upper"> {{$val4->sub_head}}</td>
					  @endif
						
					  <td> {{ number_format((float)headTotalFilterData($val4->head_id,'head4',$date_filter,$branch_filter,$end_date_filter), 2, '.', '')}}</td> 
					  <td> </td>
					</tr> 
				   
				  </tbody>
					@endif
						@endforeach
					@endif
				@endforeach
			@endif
			 
				</table>	
				
				<input type="hidden" value="{{number_format((float)$t_amount, 2, '.', '')}}" id="t_amount">
			</div>
			
		</div>	
	</div>
</div>