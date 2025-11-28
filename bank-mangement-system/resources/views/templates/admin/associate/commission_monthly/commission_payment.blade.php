@extends('templates.admin.master')

@section('content')
<div class="content">
    <div class="row">  
        @if($errors->any())
            <div class="col-md-12">
				<div class="alert alert-danger">
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
            </div>
        @endif
		 
        
        <div class="col-md-12" id='hide_div'>
			<div class="card">
				<div class="card-header header-elements-inline">
					<h6 class="card-title font-weight-semibold">Commission Payment  -- Monthly  -- {{ $company->name }} </h6>
				</div>
				<form action="{!! route('admin.associate.commission.commissionPaymentSave') !!}" method="post" enctype="multipart/form-data" id="transfer" name="transfer">
					@csrf 
					<input type="hidden" name="created_at" class="created_at">
					<input type="hidden" name="company" class="company " id="company" value="{{ $company->id }}">
					<div class="card-body">
						<div class="row">
							<div class="col-md-12">
								<div class="table-responsive">
									<table id="member_listing" class="table table-flush">
										<thead>
											<tr>                                    
											<th>S.No</th>
											 <th></th>
											<th>Associate code</th> 
											<th>Associate Name</th> 
											<th>Associate Carder </th>
											<th>PAN No </th>
											<th>SSB Account No </th>
											<th>Total Amount</th>
											<th>Total TDS</th>
											<th>Final Payable Amount</th>
											<th>Collection Amount</th>
											<th>Fuel Amount</th>
											 
											</tr>
										</thead> 
										<tbody>
										@if(count($comm)>0)
											 @foreach($comm as $index =>  $val) 
										 
											  
                                         <tr>
                                             <td>{{ $index+1 }}</td>
                                             <td style="border: 1px solid #ddd;">
                                               
                                                <div class="col-lg-12 error-msg">
                                                   
                                                  <div class="custom-control custom-checkbox mb-3 ">
												    <input type="checkbox" id="rent_transfer_{{$val->id}}" name="rent_transfer[{{$index}}]" class="custom-control-input rent_transfer rent_transferSum" value="{{ $val->amount }}" >

												   
                                                    <label class="custom-control-label" for="rent_transfer_{{$val->id}}" ></label>
                                                  </div>
                                                  
                                                </div> 
                                              </td>
                                             <td>{{ $val['member']->associate_no }}
                                             	 

                                             </td>
                                             <td>{{ $val['member']->first_name }} {{$val['member']->last_name  }}</td>
                                             <td>{{ $val['member']['getCarderNameCustom']->name}}({{ $val['member']['getCarderNameCustom']->short_name}})</td>
                                             <td>
												<?php
												$getPan='';
													if($val['member']['memberIdProof']->first_id_type_id==5)
													{
														$getPan =$val['member']['memberIdProof']->first_id_no;
													}
													if($val['member']['memberIdProof']->second_id_type_id==5)
													{
														$getPan =$val['member']['memberIdProof']->second_id_no;
													} 
												?>
												{{ $getPan }}
											 </td>

                                             <td>{{ $val['SavingAcount'] ? $val['SavingAcount'][0] ? $val['SavingAcount'][0]->account_no : 'N/A' : 'N/A'}}</td>
                                             <td>{{ $val->amount_tds}}</td>
                                             	<td> {{ $val->total_tds}} </td>
												<td> {{ $val->amount}} </td>
												<td> {{ $val->collection}} </td>												 
												<td> {{ $val->fuel}} </td>
												 
												 <input type="hidden" name="com_id[]" id="com_id" value="{{ $val->id }}"  class="id_get"> 
												  
												  
			
											 </tr>                                      
											 @endforeach
											 <tfoot>
		                                        <tr>		                          
                                         <input type="hidden" name="select_id"  id='select_id'>


		                                            <td colspan="3" align="right" style="border: 1px solid #ddd;"><strong>Total Transfer Amount</strong> </td>
		                                            <td colspan="10" align="left" style="border: 1px solid #ddd;"><span  id='total_transfer_amount'><strong >0.00</strong> </span> </td>

		                                           <!-- <td colspan="3" align="right" style="border: 1px solid #ddd;"><strong>Total Fule</strong> </td>
		                                            <td colspan="2" align="left" style="border: 1px solid #ddd;"><span ><strong id='total_transfer_fule'>0.00</strong> </span> </td>

		                                            <td colspan="3" align="right" style="border: 1px solid #ddd;"><strong>Total Tds</strong> </td>
		                                            <td colspan="2" align="left" style="border: 1px solid #ddd;"><span ><strong id='total_rds'>0.00</strong> </span> </td>-->
		                                        </tr>
		                                    </tfoot>
										@else
											<tr>
												<td colspan="6" align="center">No record </td>
											</tr>
										@endif
										</tbody>
									</table>
								</div>
							</div>
							 
						</div>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-12 text-center">
								@if(count($comm)>0)
								<button type="submit" class=" btn bg-dark legitRipple"  id="submit_transfer" >Submit</button>
								@endif
							 </div>
						 </div>
					 </div>
				</form> 
			</div>           
        </div>
        
    </div>
</div>
@stop

@section('script')
@include('templates.admin.associate.commission_monthly.payment_script')
@stop
