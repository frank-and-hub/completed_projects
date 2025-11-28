@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
				
				<div class="modal fade" id="commissionLoanModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="commissionLoanModelLabel">Loan Commission </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <!--<div class="modal-body">-->
                                {{Form::open(['url'=>'','method'=>'POST','name'=>'loancommissionform','id'=>'loancommissionform'])}}
									<div class="card">
										<div class="card-body">
											<div class="form-group row col-sm-12 col-lg-12">
												<div class="col-sm-6 col-lg-6">
													{{Form::hidden("loan_type_id",$loan_type_id,['class'=>'loan_type_id'])}}
													{{Form::label("tenure_type",'Tenure Type')}}
												@foreach($commission as $value)	{{Form::text('tenure_type',$value->tenure_type,['class'=>'form-control model_tenure_type','readonly'=>true])}}
												@break
												@endforeach
													
												</div>
												<div class="col-sm-6 col-lg-6">
												
													{{Form::label("tenure",'Tenure')}}
												@foreach($commission as $value)
													{{Form::text('tenure',$value->tenure,['class'=>'form-control model_tenure','readonly'=>true])}}
													@break
												@endforeach
													
														
												</div>
												<!--<div class="col-sm-6 col-lg-3">
													{{Form::label("effect_from",'Effective From')}}
													{{Form::text('effect_from','',['id'=>'effect_from','class'=>'form-control','autoComplete'=>'off'])}}
												</div>-->
											</div>
											<!--<div class="text-right">
												{{Form::button('NEXT',['class'=>'btn bg-dark','id'=>'next'])}}
											</div>-->    
										<!--</div>
									</div>-->
									<!--<div class="card " id="collectorPercentageForm">
										<div class="card-body">-->
												<div class="form-group row col-sm-12 col-lg-12">
													<div class="col-sm-6 col-lg-12">
														<table class="col-12">
															<tr>
																<th>Carder</th>
																<th class="text-center">Collector Per.</th>
																<th class="text-center">Effective From</th>
																<th class="text-center">Action</th>
															</tr>
															@foreach($commission as $value)
															<tr>
																<td>{{Form::hidden('carder_id',$value->carder->id)}}{{Form::text('carder_id',$value->carder->name,['class'=>'form-control','readonly'=>true])}}
																</td>
																<td>
																{{Form::text('collector_per',$value->collector_per,['class'=>'form-control text-center collector_per','readonly'=>true])}}
																</td>
																<td>										{{Form::text('effective_from',date('d/m/Y',strtotime($value->effective_from)),['class'=>'form-control text-center','readonly'=>true,'autoComplete'=>'off'])}}
																</td>
																<td style="text-align : center;">
																{{Form::button('<i class="fa fa-edit"></i>', ['class' => 'btn btn-block btn-secondary btn-sm text-center edit', 'type'=>'submit','title'=>'Edit','id'=>'edit','data-id'=>$value->id]) }}
																{{Form::button('<i class="fa fa-upload"></i>', ['class' => 'btn btn-block btn-primary btn-sm d-none update', 'type'=>'submit','title'=>'Update','data-id'=>$value->id]) }}
																</td>
															</tr>
															@endforeach
														</table>
													</div>
												</div>
												<!--<div class="text-right">
													<button type="submit" class="btn bg-dark">Submit<i class="icon-paperplane ml-2"></i></button>
													{{Form::submit('SUBMIT',['class'=>'btn bg-dark','id'=>'submit'])}}
												</div>-->    
										</div>
									</div>
								{{Form::close()}}                
                            <!--</div>-->
                        </div>
                    </div>
                </div>
				
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Loan Commission - {{getLoanData($loan_type_id)->name}}</h6>
						{{Form::hidden('loan_type_id',$loan_type_id,['id'=>'loan_type_id'])}}
                        <a href="{{route('admin.loan.commission.create',['id'=>$loan_type_id])}}"><i class="fa fa-plus"></i> Create New Commission</a>
                    </div>
                    <div class="">
                        <table id="commissionLoanDetails_table" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
									<th>Tenure Name</th>
                                    <th>Tenure Type</th>                                              
									<th>Tenure</th>                                              
                                    <th>Status</th>
                                    <th>Effective From</th>
                                    <th>Effective To</th>
                                    <th class="text-center">Action</th>    
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.py-scheme.partials.scriptcommisssion')
@stop