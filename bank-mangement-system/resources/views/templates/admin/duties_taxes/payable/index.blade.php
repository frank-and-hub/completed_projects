@extends('templates.admin.master')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="content">
<style>
#form_body{
	display:none;
}
</style>
    <div class="row">
		{{Form::open([
			'url'=>route('admin.duties_taxes.pay'),
			'method'=>'POST',
			'id'=>'payable_from',
			'name'=>'payable_from',
			'enctype'=>'multipart/form-data',
			'class'=>'col-md-12 form-control'
		])}}
			<div class="card my-4">
				<div class="card-header header-elements-inline">						
					<div class="card-body">
						<div class="row ">
							<div class="col-md-6">
								<div class="form-group row">
									<label class="col-form-label col-lg-4">Payable Head Type <sup>*</sup></label>
									<div class="col-lg-8">
										<select class="form-control form-select" id="head_type" name="head_type">
											<option value="">---- Please Select Payable Type----</option>
											@foreach($head_type as $k => $v)
											<option value="{{$k}}">{{ucwords($v)}}</option>
											@endforeach											
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
            <div class="card my-4" id="form_body">			
				<div class="card-header header-elements-inline">
					<h6 class="card-title font-weight-semibold">Create Transfer Request</h6>
					<div class="col-md-8">
					</div>
				</div>
				<div class="card-body" id="">					
					<input type="hidden" name="created_at" class="created_at" id="created_at">
					<div class="row ">
						{{--<div class="col-md-6">
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
						</div>--}}
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Company <sup>*</sup></label>
								<div class="col-lg-8">
										<select class="form-control form-select" id="company_id" name="company_id">
											<option value="">---- Please Select Company----</option>
											@foreach($company as $key => $val)
											<option value="{{ $key }}">{{ ucwords($val) }}</option>
											@endforeach
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Head <sup>*</sup></label>
								<div class="col-lg-8">
									<select class="form-control" id="payable_head_id" name="payable_head_id">
										<option value="">---- Please Select ----</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Select Bank <sup>*</sup></label>
								<div class="col-lg-8"> 
									<select class="form-control form-select" id="bank_id" name="bank_id">
										<option value="">---- Please Select ----</option>
										{{-- @foreach ($SamraddhBanks as $key => $val) --}}
											{{-- <option value="{{ $key }}" {{ isset($bank_id) ? ($key == $bank_id ? 'selected' : '') : '' }}> {{ $val }}</option> --}}
										{{-- @endforeach --}}
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Select A/C <sup>*</sup></label>
								<div class="col-lg-8">
									<select class="form-control form-select" id="account_id" name="account_id">
										@if ($view == 0)
											<option value="">---- Please Select ----</option>
											@foreach ($SamraddhBankAccounts as $bankAccounts)
												<option data-bank-id="{{ $bankAccounts->bank_id }}" value="{{ $bankAccounts->id }}" class="bank-account {{ $bankAccounts->bank_id }}-bank-account" style="display:none;">{{ $bankAccounts->account_no }}</option>
											@endforeach
										@else
											<option>{{ $account_no ?? '' }}</option>
										@endif
									</select>
								</div>
							</div>
						</div>
						{{-- {{Form::hidden('payable_paid_amount',$amount??0,['id'=>'payable_paid_amount','class'=>'form-control'])}} --}}
						
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Payment Date <sup>*</sup></label>
								<div class="col-lg-8">
									{{ Form::text('payable_payment_date', '', ['id' => 'payable_payment_date', 'class' => 'form-control','readonly'=>true]) }}
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Bank Available Balance </label>
								<div class="col-lg-8">
									{{ Form::text('bank_available_balance', '', ['id' => 'bank_available_balance', 'class' => 'form-control', 'readonly' => true]) }}
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Payable Amount <sup>*</sup></label>
								<div class="col-lg-8">
									{{Form::text('payable_amount','',['id'=>'payable_amount','class'=>'form-control'])}}
								</div>
							</div>
						</div>
						
						
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Late Penalty <sup>*</sup></label>
								<div class="col-lg-8">
									{{ Form::text('payable_late_penalty','', ['id' => 'payable_late_penalty', 'class' => 'form-control']) }}
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Total Paid Amount <sup>*</sup></label>
								<div class="col-lg-8">
									{{ Form::text('total_paid_amount','', ['id' => 'total_paid_amount', 'class' => 'form-control','readonly'=>true]) }}
								</div>
							</div>
						</div>
						
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">RTGS/NEFT Charge</label>
								<div class="col-lg-8">
									{{ Form::text('neft_charge', '', ['id' => 'neft_charge', 'class' => 'form-control']) }}
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Final Payable Amount <sup>*</sup></label>
								<div class="col-lg-8">
									{{ Form::text('final_payable_amount', '', ['id' => 'final_payable_amount', 'class' => 'form-control', 'readonly' => true]) }}
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">UTR / Transaction Number <sup>*</sup></label>
								<div class="col-lg-8">
									{{ Form::text('transaction_number', '', ['id' => 'transaction_number', 'class' => 'form-control removeSpaceInput','required'=>true]) }}
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Upload Challan <sup>*</sup></label>
								<div class="col-lg-8">
									@if ($view == 0)
										{{ Form::file('upload_challan', ['id' => 'upload_challan', 'class' => 'form-control', 'accept' => 'image/jpeg, image/png, image/jpg, image/ico, image/gif, image/svg, image/pdf, image/webp']) }}
									@endif
									<a href="{{ $ChalanSrc ?? '' }}" style="vertical-align: text-top" class="text-primary h-100 w-100 text-left" title="Vew File" target="_blank" class="">{{ $ChalanFile ?? '' }}</a>
									@if ($view == 1)
									@endif
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group row">
								<label class="col-form-label col-lg-4">Remark <sup>*</sup></label>
								<div class="col-lg-8">
									{{ Form::text('remark', '', ['id' => 'remark', 'class' => 'form-control']) }}
									{{-- {{ Form::hidden('id', '', ['id' => 'id', 'class' => 'form-control']) }} --}}
								</div>
							</div>
						</div>
					</div>
					<div class="text-right">
						<input type="button" name="submitform" value="Submit" class="btn btn-primary submit-payable">
					</div>
				</div>
            </div>
		{{Form::close()}}
    </div>
</div>
@stop
@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.duties_taxes.payable.partials.script')
@endsection