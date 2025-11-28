@extends('templates.admin.master')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- Basic layout-->
            <div class="card my-4">
                <div class="card-header header-elements-inline">
                    <div class="card-body" id="bank-to-bank">
                    	@if(count($errors))
				            <div class="form-group">
				                <div class="alert alert-danger">
				                    <ul>
				                        @foreach($errors->all() as $error)
				                            <li>{{$error}}</li>
				                        @endforeach
				                    </ul>
				                </div>
				            </div>
				        @endif
						{{Form::open(['url'=>route('admin.paygstTransferAmount'),'method'=>'POST','id'=>'gst_transfer_payable_from','name'=>'gst_transfer_payable_from','enctype'=>'multipart/form-data'])}}
						   	<div class="row ">
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">From Date <sup>*</sup></label>
										<div class="col-lg-8">
											{{Form::text('payable_start_date','',['id'=>'payable_start_date','class'=>'form-control','readonly'=>true])}}
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">To Date <sup>*</sup></label>
										<div class="col-lg-8">
											{{Form::text('payable_end_date','',['id'=>'payable_end_date','class'=>'form-control','readonly'=>true])}}
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Company <sup>*</sup></label>
										<div class="col-lg-8">
												<select class="form-control" id="company_id" name="company_id" required>
													<option value="">---- Please Select Company----</option>
													@foreach($company as $key => $val)
													<option value="{{ $key }}">{{ $val }}</option>
													@endforeach
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">State <sup>*</sup></label>
										<div class="col-lg-8">
												<select class="form-control" id="state" name="state" required>
													<option value="">---- Please Select ----</option>													
												@foreach($allState as $key => $val)
													<option value="{{ $key }}" >{{ $val[0]['state']['name'] . ' - ' . $key }}</option>
												@endforeach													
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Igst Amount CR<sup class=""></sup></label>
										<div class="col-lg-8">
											{{Form::text('payable_igst_amount_cr','',['id'=>'payable_igst_amount_cr','class'=>'form-control','readonly'=>true])}}
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Igst Amount DR<sup class=""></sup></label>
										<div class="col-lg-8">
											{{Form::text('payable_igst_amount_dr','',['id'=>'payable_igst_amount_dr','class'=>'form-control','readonly'=>true])}}
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Cgst Amount CR<sup class=""></sup></label>
										<div class="col-lg-8">
											{{Form::text('payable_cgst_amount_cr','',['id'=>'payable_cgst_amount_cr','class'=>'form-control','readonly'=>true])}}
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Cgst Amount DR<sup class=""></sup></label>
										<div class="col-lg-8">
											{{Form::text('payable_cgst_amount_dr','',['id'=>'payable_cgst_amount_dr','class'=>'form-control','readonly'=>true])}}
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Sgst Amount CR<sup class=""></sup></label>
										<div class="col-lg-8">
											{{Form::text('payable_sgst_amount_cr','',['id'=>'payable_sgst_amount_cr','class'=>'form-control','readonly'=>true])}}
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group row">
										<label class="col-form-label col-lg-4">Sgst Amount DR<sup class=""></sup></label>
										<div class="col-lg-8">
											{{Form::text('payable_sgst_amount_dr','',['id'=>'payable_sgst_amount_dr','class'=>'form-control','readonly'=>true])}}
										</div>
									</div>
								</div>
							</div>	
                            <div class="text-right">
								{{Form::hidden('payable_total_dr_amount','',['id'=>'payable_total_dr_amount','class'=>'form-control'])}}
								{{Form::hidden('payable_total_cr_amount','',['id'=>'payable_total_cr_amount','class'=>'form-control'])}}
								{{Form::hidden('created_at','',['id'=>'created_at','class'=>'form-control created_at'])}}
                                <input type="submit" name="submitform" value="Transfer" class="btn btn-primary submit-payable">
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
                <!-- /basic layout -->
            </div>
        </div>
		<!-- Table -->
    </div>
</div>
@stop
@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.gst.partials.gst_payable_script')
@endsection