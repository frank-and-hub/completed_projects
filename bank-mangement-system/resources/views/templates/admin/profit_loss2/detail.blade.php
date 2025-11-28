@extends('templates.admin.master')



@section('content')


<?php
$date='';
$branch_id = '';
$to_date='';
if(isset($_GET['date']))
{
    $date=trim($_GET['date']);
    if($date!=''){
        $date= date("d/m/Y", strtotime(convertDate($date)));
    }
  
}
if(isset($_GET['to_date']))
{
    $to_date=trim($_GET['to_date']);
    if($to_date!=''){
        $to_date=date("d/m/Y", strtotime(convertDate($to_date)));
    }
  
}
if(isset($_GET['branch_id']))
{
    $branch_id=trim($_GET['branch_id']);
    if($branch_id!=''){
        $branch_id=$branch_id;
    }
  
    
  
}

$t_amount = 0;
$finacialYear=getFinacialYear();
$branchIddd = 33;
$globalDate1 = headerMonthAvailability(date('d'),date('m'),date('Y'),$branchIddd);
$startDate = date("d/m/Y", strtotime($finacialYear['dateStart']));
$endDatee = date("d/m/Y", strtotime(convertDate($globalDate1)));
$currentYear = date('Y');
    $endYear = date('Y') +1;
    $finacialCurrentYear = $_GET['financial_year'] ;
;
?>

	<div class="content">

		<div class="row">
			<div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                    @csrf
                     <input type="hidden" class="form-control  " name="default_date" id="default_date"  value="{{ $startDate }}" > 
                         <input type="hidden" class="form-control " name="default_end_date" id="default_end_date" value="{{$endDatee}}"> 

                      <div class="row">
					  <div class="col-md-4">
                            <div class="form-group row">
                              <label class="col-form-label col-lg-12">Financial Year </label>
                              <div class="col-lg-12 error-msg">
                                <select class="form-control" id="financial_year" name="financial_year">
                                  @foreach( getFinancialYear() as $key => $value )
                                  <option value="{{ $value }}" @if( $value == $finacialCurrentYear) selected @endif >{{ $value }} </option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
						</div>
                          <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">From Date </label>
                                  <div class="col-lg-12 error-msg">
                                       <div class="input-group">
                                           <input type="text" class="form-control  " name="date" id="date"  value="{{$date}}"> 
                                         </div>
                                  </div>
                              </div>
                          </div>
                         <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">To Date </label>
                                  <div class="col-lg-12 error-msg">
                                       <div class="input-group">
                                           <input type="text" class="form-control  " name="to_date" id="to_date"  value="{{$to_date}}"> 
                                         </div>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                          <input type="hidden" name="export" id="export" value="">
                                            <input type="hidden" name="head" id="head" value="{{$headDetail->head_id}}">
                                             <input type="hidden" name="labels" id="labels" value="{{$headDetail->label}}">
                                        <button type="button" class=" btn bg-dark legitRipple submit"onClick="searchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                </div>
            </div> 
            <div class="data container-fluid">
              <div class="container">
                <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>

                <button type="button" class="btn bg-dark legitRipple export" data-extension="1"  style="float: right;">Export PDF</button>
              </div>
            </div> <br/><br/><br/>
			<div class="col-md-12 mt-2">

				<div class="card">
					<!-- label 2 -->
					<div class="card-header header-elements-inline">
						@if(in_array(86,$ids) && in_array(40,$ids) == false  && in_array(53,$ids) == false  && in_array(87,$ids) == false  && in_array(88,$ids) == false)
							<th><a href="{{ URL::to('admin/profit-loss/head_detail_report/'.$headDetail->head_id.'/'.$headDetail->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$headDetail->sub_head}}</a></th>
						@elseif(in_array(97,$ids))
							<th><a href="{{ URL::to('admin/profit-loss/detailed/interest_on_loan_taken/'.$headDetail->head_id.'/'.$headDetail->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$headDetail->sub_head}}</a></th>
						@elseif(in_array(40,$ids)  && in_array(86,$ids) )
							<th><a href="{{ URL::to('admin/profit-loss/detailed/depreciation/'.'?head='.$headDetail->head_id.'&date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">	{{$headDetail->sub_head}}</a></th>		
						@elseif(in_array(87,$ids)  && in_array(86,$ids) )
							<th><a href="{{ URL::to('admin/profit-loss/detailed/commission/'.'?head='.$headDetail->head_id.'&date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">	{{$headDetail->sub_head}}</a></th>
						@elseif(in_array(88,$ids)  && in_array(86,$ids) )
							<th><a href="{{ URL::to('admin/profit-loss/detailed/fuel_charge/'.'?head='.$headDetail->head_id.'&date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">	{{$headDetail->sub_head}}</a></th>				 
						@else	  
						 	<th><a href="{{ URL::to('admin/profit-loss/detail/branch_wise/'.$headDetail->head_id.'/'.$headDetail->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$headDetail->sub_head}}</a></th> 
					 	@endif
                       
						
						
							<th>&#X20B9;{{ number_format((float)headTotalNew($headDetail->head_id,$date,$to_date,$branch_id), 2, '.', '')}}</th>
					
                    </div>

                   
                    		
								

							
					<table class="table  datatable-show-all">
						
						@if(count($childHead)>0)

		                    @foreach ($childHead as $val1) 

		                    <?php  $head4= getHead($val1->head_id,4);
		                            $headIDS = array($headDetail->head_id);

		                    	$subHeadsIDS =App\Models\AccountHeads::where('head_id',$headDetail->head_id)->where('status',0)->pluck('head_id')->toArray();

				                if( count($subHeadsIDS) > 0 ){
				                    $headIDS=  array_merge($headIDS,$subHeadsIDS);
				                   $record= get_account_head_ids($headIDS,$subHeadsIDS,true);
				                
				                }
				                 
				                 foreach ($record as $key => $value) {
				                $ids[] = $value;
				               }
				               $ID = $ids;
				               
		                    ?>
								<thead>
									 

									<tr>
										@if(count($head4)>0)
											<th><a href="{{ URL::to('admin/profit-loss/detail/'.$val1->head_id.'/'.$val1->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$val1->sub_head}}</a></th>
													
										 	
											@elseif($val1->head_id ==40)
											<th><a href="{{ URL::to('admin/profit-loss/detailed/depreciation/'.'?head='.$val1->head_id.'&date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">	{{$val1->sub_head}}</a></th>

											@elseif(in_array(87,$ID))
											<th><a href="{{ URL::to('admin/profit-loss/detailed/commission/'.'?head='.$val1->head_id.'&date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">	{{$val1->sub_head}}</a></th>
											@elseif($val1->head_id ==88)
											<th><a href="{{ URL::to('admin/profit-loss/detailed/fuel_charge/'.'?head='.$val1->head_id.'&date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">	{{$val1->sub_head}}</a></th>			
										
											@elseif($val1->head_id ==97)
												<th><a href="{{ URL::to('admin/profit-loss/detailed/interest_on_loan_taken/'.$val1->head_id.'/'.$val1->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$val1->sub_head}}</a></th>	
											@elseif($val1->head_id ==86)
											<th><a href="{{ URL::to('admin/profit-loss/head_detail_report/'.$val1->head_id.'/'.$val1->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$val1->
											sub_head}}</a></th>	
											@elseif(in_array(92,$ID))
											<th><a href="{{ URL::to('admin/profit-loss/head_detail_report/'.$val1->head_id.'/'.$val1->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$val1->
											sub_head}}</a></th>	
												
											
										@else
										<th><a href="{{ URL::to('admin/profit-loss/detail/branch_wise/'.$val1->head_id.'/'.$val1->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$val1->sub_head}}</a></th>	

										@endif
										<th></th>

										<th></th>
										<?php $t_amount =$t_amount + headTotalNew($val1->head_id,'head3',$date,$branch_id,$to_date); ?>
										
                                         <th>&#X20B9;
                                        {{ number_format((float)headTotalNew($val1->head_id,$date,$to_date,$branch_id), 2, '.', '')}}
                                         </th>
                                        
									</tr>
								</thead>
								<?php  $head4= getHead($val1->head_id,5);
									$head4Id = array();
					               if($val1->head_id == 92)
					               {
						               	$parentID = array($val1->head_id);
										$subHeadsParentIDS =App\Models\AccountHeads::where('head_id',$val1->head_id)->where('status',0)->pluck('head_id')->toArray();
						                if( count($subHeadsParentIDS) > 0 ){
						                    $parentID=  array_merge($parentID,$subHeadsParentIDS);
						                   $record= get_change_sub_account_head($parentID,$subHeadsParentIDS,true);
						                }
						                foreach ($record as $key => $value) {
						                $head4Id[] = $value;
						               }
						              // dd($head4Id);
					               }
				               		// $Dataid = $head4Id;


								?>
								
		                        @if(count($head4)>0)

		                          	@foreach ($head4 as $val4)  
		                          	<?php  $count= getHead($val4->head_id,4);
		                            
		                          	?>
										<tbody>

					                        <tr>

					                          <td style=" width:25%;"> </td>
					                            @if(count($count)>0)
					                            	<th><a href="{{ URL::to('admin/profit-loss/detail/'.$val4->head_id.'/5?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$val4->sub_head}} </a> </th>
					                            @elseif(in_array(92,$head4Id)) <td><a href="{{ URL::to('admin/profit-loss/head_detail_report/'.$val4->head_id.'/'.$val4->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$val4->sub_head}}</a></td>
					                            @elseif(in_array(87,$ID))
													<th><a href="{{ URL::to('admin/profit-loss/detailed/commission/'.'?head='.$val4->head_id.'&date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">	{{$val4->sub_head}}</a></th>	
					                            @else
					                           <td><a href="{{ URL::to('admin/profit-loss/detail/branch_wise/'.$val4->head_id.'/'.$val4->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank"> {{$val4->sub_head}}</a></td>
					                         	@endif

					                  
                                         <th>&#X20B9;{{ number_format((float)headTotalNew($val4->head_id,$date,$to_date,$branch_id), 2, '.', '')}}

                                         </th>
                                      
					                          
					                          <td> </td>

					                        </tr> 
				                      </tbody> 
		                      		@endforeach
		                      		@endif
							@endforeach	

							<thead>

							@elseif(count($subchildHead)  > 0)	
							@foreach ($subchildHead as $val2) 
							  <?php  $head5= getHead($val2->head_id,5);?>
							<tr>
								@if(count($head5)>0)
									<th><a href="{{ URL::to('admin/profit-loss/detail/'.$val2->head_id.'/4?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$val2->sub_head}} </a> </th>
								 @elseif(in_array(86,$ids)  && in_array(92,$ids) )
										<th><a href="{{ URL::to('admin/profit-loss/head_detail_report/'.$val2->head_id.'/'.$val2->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$val2->sub_head}}</a></th>
								<!-- @elseif($val2->parent_id ==96)
										<th><a href="{{ URL::to('admin/eli-loan/')}}" target="_blank">{{$val2->sub_head}}</a></th>	 -->
								@elseif(in_array(97,$ids))
										<th><a href="{{ URL::to('admin/profit-loss/detailed/interest_on_loan_taken/'.$val2->head_id.'/'.$val2->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$val2->sub_head}}</a></th>			
								@elseif(in_array(40,$ids) && in_array(86,$ids) )
										<th><a href="{{ URL::to('admin/profit-loss/detailed/depreciation/'.'?head='.$val1->head_id.'&date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">				{{$val2->sub_head}}</a></th>
								@elseif(in_array(53,$ids))
		                     		<td class="text_upper"><a href="{{ URL::to('admin/profit-loss/detail/branch_wise/'.$val2->head_id.'/'.$val2->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank"> {{$val2->sub_head}}</a></td>	
		                     				
								@else
								<th><a href="{{ URL::to('admin/profit-loss/detail/branch_wise/'.$val2->head_id.'/'.$val2->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">{{$val2->sub_head}}</a></th>	

								@endif
								<th></th>

								<th></th>
								<?php $t_amount =$t_amount + headTotalNew($val2->head_id,$date,$branch_id,$to_date,$branch_id); ?>
								 
                                    <th>&#X20B9;{{ number_format((float)headTotalNew($val2->head_id,$date,$to_date,$branch_id), 2, '.', '')}}ss
									</th>
                                
								
									</tr>
								</thead>
								<?php  $headnew= getHead($val2->head_id,5);
								?>
		                        @if(count($headnew)>0)
		                          	@foreach ($headnew as $val4)  
										<tbody>
					                        <tr>
					                          <td> </td>					                           
					                           <td> {{$val4->sub_head}}</td>
					                           
                                         		<td>&#X20B9; {{ number_format((float)headTotalNew($val4->head_id,$date,$to_date,$branch_id), 2, '.', '')}}

                                         		</td>
                                        	 
					                          <td> </td>
					                        </tr> 
				                      </tbody> 
				                    @endforeach
				                @endif
							@endforeach	
									
                		@endif
                		<!-- Bank Charge oTHER hEAD Start -->
						@if($headDetail->head_id == 92)
						<tr>
							<th><a href="{{ URL::to('admin/profit-loss/head_detail_report/'.$headDetail->head_id.'/'.$headDetail->labels.'?date='.$date.'&to_date='.$to_date.'&branch_id='.$branch_id.'&financial_year='.$finacialCurrentYear)}}" target="_blank">Bank Charge Other</a></th>
							<th></th>

							<th></th>
						
							<th>&#X20B9;{{number_format((float)headTotalNew($headDetail->head_id,$date,$to_date,$branch_id), 2, '.', '')}}</th>
						</tr>
						@endif
						<!-- Bank Charge oTHER hEAD End -->
					</table>	
					<input type="hidden" value="{{number_format((float)$t_amount, 2, '.', '')}}" id="t_amount">
				</div>

			</div>	

		</div>

	</div>
@include('templates.admin.profit_loss.partials.search_script_detail')

@stop