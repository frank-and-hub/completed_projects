<h4 class="card-title font-weight-semibold">{{$title}}</h4>

<?php

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
                    </div>
					<table class="table  datatable-show-all">
				@if(count($childHead)>0)

                    @foreach ($childHead as $val1) 
						<thead>
						  <tr @if($val1->status==1) class="child_inactive" @endif >
								
              <?php  $head4= getHead($val1->head_id,5);?>
              @if(count($head4)>0)
                <th class="text_upper 22">{{$val1->sub_head}}</th>

                 @elseif( $val1->head_id==89 || $val1->parent_id == 27 )
                 <th class="text_upper 33">{{$val1->sub_head}} </th> 

                @else
                  <th class="text_upper 44">
                  {{$val1->sub_head}}</th>
                  @endif

								<th></th>
								<th></th>

								@if($val1->head_id==17)
                    <th>{{ number_format((float)$profit_loss, 2, '.', '')}}</th>
                @else
                    <th>{{ number_format((float)headTotalFilterData($val1->head_id,'head4',$date_filter,$branch_filter,$end_date_filter), 2, '.', '')}}</th>
                @endif

							</tr>
						</thead>
						<?php  $head4= getHead($val1->head_id,5);?>
                    @if(count($head4)>0)
                      	@foreach ($head4 as $val4)  
						<tbody>
                <tr @if($val4->status==1) class="child_inactive" @endif >
                <td> </td>
                @if($val1->head_id==57 || $val1->head_id == 59 )
                  <td class="text_upper  55">{{$val4->sub_head}}</td> 

                @else
                <td class="text_upper"> {{$val4->sub_head}}</td>
                @endif
                <td>{{ number_format((float)headTotalFilterData($val4->head_id,'head5',$date_filter,$branch_filter,$end_date_filter), 2, '.', '')}}</td> 
                <td> </td>
              </tr> 
              
            </tbody> 
            		@endforeach
                @endif
            @endforeach
             @elseif(count($subchildHead)>0)
                    @foreach ($subchildHead as $val5)
                    <tbody>
                        <tr @if($val5->status==1) class="child_inactive" @endif >
                     
                      <td class="text_upper  66">{{$val5->sub_head}}</td>

                      <td align="center">{{ number_format((float)headTotalFilterData($val5->head_id,'head5',$date_filter,$branch_filter,$end_date_filter), 2, '.', '')}}</td> 
               
                    </tr>
                    </tbody>
                    @endforeach
                @endif
					</table>	
				</div>
			</div>	
		</div>
	</div>
