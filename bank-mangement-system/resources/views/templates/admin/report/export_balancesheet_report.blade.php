<h4 class="card-title font-weight-semibold">Balance sheet | Report</h4>

<?php

$finacialYear=getFinacialYear();
$startDatee=date("Y-m-d", strtotime($finacialYear['dateStart']));
$branchIddd = 33;
$globalDate1 = headerMonthAvailability(date('d'),date('m'),date('Y'),$branchIddd);
$endDatee = date("Y-m-d", strtotime(convertDate($globalDate1)));

 ?>



	
		  <div class="">
			<table >
			  @foreach ($libalityHead as $lib) 
			
				<thead >
				  <tr>
					<th> <b>{{$lib->sub_head}}</b></th>
					<th></th>
					<th></th>
				  </tr>  
				</thead> 
				 <?php  $libalityHeadThree = getHead($lib->head_id,3);?>
				@if(count($libalityHeadThree)>0)
				  @foreach ($libalityHeadThree as $valLib) 
								  
					<tbody>
					  <tr @if($valLib->status==1) class="child_inactive" @endif>
						<td>{{$valLib->sub_head}}</td>
						<td></td>
						@if($valLib->head_id==17)
							<td>{{ number_format((float)$profit_loss, 2, '.', '')}}</td>
						@else
							<td>{{ number_format((float)headTotal($valLib->head_id,'head3'), 2, '.', '')}}</td>
						@endif
					  </tr>    
					</tbody> 
				  @endforeach
				@endif
			  @endforeach 
										  
			</table>
		  
		 
			
		 
			<div class="">
			  <table >
				@foreach ($assestHead as $assest) 

				<thead >
				  <tr >
					<th><b>{{$assest->sub_head}}</b></th>
					<th></th>
					<th></th>
				  </tr>  
				</thead>
				<?php  $assestHeadThree = getHead($assest->head_id,3);?>
				@if(count($assestHeadThree)>0)
				  @foreach ($assestHeadThree as $val1)  
			 	
					<tbody>
					 <tr @if($val1->status==1) class="child_inactive" @endif>
						<td>{{$val1->sub_head}}</td>
						<td></td>
						<td>{{ number_format((float)headTotal($val1->head_id,'head3'), 2, '.', '')}}</td>
					  </tr>    
					</tbody> 
				  @endforeach
				@endif
			  @endforeach   
			  </table>
			</div>
		  

		   <h4 >Interbranch transactions </h4>
			<span >Total Amount:0000</span>
			 
		  
	
				<table>
					<thead>
					   <tr>
						<th>Total Liabilities</th>
						<th></th>
						
						<th>{{ number_format((float)$totalLibality, 2, '.', '')}}</th>
					   </tr>  
					</thead> 
				</table>
			 
	
				<table>
				  <thead>
					<tr>
					<th>Total Assets[E]</th>
					<th></th>
					<th>{{ number_format((float)$totalAssest, 2, '.', '')}}</th>
				   </tr>  
				  </thead> 
				</table>
			  
  </div>
  